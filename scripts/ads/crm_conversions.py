# -*- coding: utf-8 -*-
"""Выгрузка сделок и качественных лидов из Битрикса для Метрики.

Замыкает цепочку «клик по объявлению → заявка → сделка». Берёт из CRM
события, которые считаются конверсией, находит у них идентификаторы
рекламного визита и складывает в CSV для `offline_conversions.py`.

**Что считается конверсией:**

* лид на стадии `CONVERTED` («Качественный лид») — ранний сигнал, приходит
  в течение дня-двух, годится для обучения автостратегий;
* сделка на стадии `WON` («Сделка успешна») — деньги, приходит через
  недели или месяцы, годится для оценки окупаемости.

**Откуда берутся идентификаторы.** Сайт кладёт `yclid` и ClientID в поля
лида (`UF_CRM_YCLID`, `UF_CRM_METRIKA_CLIENT_ID`). У сделки те же поля есть,
но заполняются они только если сделка создана из лида — тогда сохраняется
`LEAD_ID`, и скрипт дотягивает идентификаторы по нему.

**Важное наблюдение на 09.09.2026:** в этом портале сделки заводят вручную,
минуя лиды — из 50 сделок за три месяца ни одна не имеет `LEAD_ID`. Пока
так, атрибутировать сделки не к чему, и в отчёте они попадут в графу «без
идентификаторов». Лечится процессом: заявка с сайта должна конвертироваться
в сделку через лид, а не заводиться заново.

Компания в портале определяется по ответственному: контакты и сделки общие
на всю группу «Титан», отдельного поля «компания группы» нет.

    python scripts/ads/crm_conversions.py --from 2026-09-01
    python scripts/ads/crm_conversions.py --from 2026-09-01 --to 2026-09-30
    python scripts/ads/crm_conversions.py --from 2026-09-01 --all-owners
"""
import argparse
import collections
import csv
import datetime as dt
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from crm_audience import PROMEN_OWNERS  # noqa: E402  список ответственных Промэнергетики

# Цели в счётчике 62844301. Идентификаторы, а не числовые id: офлайн-конверсии
# грузятся по идентификатору цели.
GOAL_LEAD = "crm_qualified"      # 608772488 «CRM: квалифицированная заявка»
GOAL_DEAL = "crm_deal_won"       # заводится один раз, см. --check-goals

LEAD_STAGE = "CONVERTED"
DEAL_STAGE = "WON"
UF_CLIENT = "UF_CRM_METRIKA_CLIENT_ID"
UF_YCLID = "UF_CRM_YCLID"


def env(key, default=None):
    if os.environ.get(key):
        return os.environ[key]
    try:
        with open(os.path.join(ROOT, ".env"), encoding="utf-8") as f:
            for line in f:
                if line.strip().startswith(key + "="):
                    return line.split("=", 1)[1].strip().strip("\"'")
    except OSError:
        pass
    return default


HOOK = (env("BITRIX_WEBHOOK") or "").rstrip("/")


def call(method, payload=None):
    if not HOOK:
        sys.exit("BITRIX_WEBHOOK не задан в .env")
    r = requests.post(f"{HOOK}/{method}.json", json=payload or {}, timeout=120)
    d = r.json()
    if "error" in d:
        sys.exit(f"{method}: {d.get('error_description') or d['error']}")
    return d


def fetch_all(method, params):
    """Битрикс отдаёт по 50 записей за раз — идём по страницам."""
    out, start = [], 0
    while True:
        d = call(method, dict(params, start=start))
        out.extend(d.get("result") or [])
        nxt = d.get("next")
        if nxt is None:
            return out
        start = nxt


def moment(raw):
    """Дата события из Битрикса → «ГГГГ-ММ-ДД ЧЧ:ММ:СС» для загрузчика."""
    if not raw:
        return ""
    try:
        return dt.datetime.fromisoformat(str(raw)).strftime("%Y-%m-%d %H:%M:%S")
    except ValueError:
        return str(raw)[:19].replace("T", " ")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--from", dest="date_from", required=True, help="ГГГГ-ММ-ДД")
    ap.add_argument("--to", dest="date_to", default="")
    ap.add_argument("--all-owners", action="store_true",
                    help="без фильтра по Промэнергетике — вся группа «Титан»")
    ap.add_argument("--out", default="")
    a = ap.parse_args()
    date_to = a.date_to or dt.date.today().isoformat()

    def mine(entity):
        if a.all_owners:
            return True
        return int(entity.get("ASSIGNED_BY_ID") or 0) in PROMEN_OWNERS

    print(f"период: {a.date_from} — {date_to}"
          + ("" if a.all_owners else f", ответственных Промэнергетики: {len(PROMEN_OWNERS)}"))

    leads = [x for x in fetch_all("crm.lead.list", {
        "filter": {">=DATE_CREATE": a.date_from, "<=DATE_CREATE": date_to,
                   "STATUS_ID": LEAD_STAGE},
        "select": ["ID", "TITLE", "DATE_CREATE", "DATE_MODIFY", "ASSIGNED_BY_ID",
                   "SOURCE_ID", UF_CLIENT, UF_YCLID],
        "order": {"ID": "DESC"}}) if mine(x)]

    deals = [x for x in fetch_all("crm.deal.list", {
        "filter": {">=DATE_CREATE": a.date_from, "<=DATE_CREATE": date_to,
                   "STAGE_ID": DEAL_STAGE},
        "select": ["ID", "TITLE", "DATE_CREATE", "CLOSEDATE", "ASSIGNED_BY_ID",
                   "OPPORTUNITY", "LEAD_ID", "CATEGORY_ID", UF_CLIENT, UF_YCLID],
        "order": {"ID": "DESC"}}) if mine(x)]

    print(f"качественных лидов: {len(leads)}, успешных сделок: {len(deals)}")

    # Идентификаторы сделки: свои поля, иначе — из лида, из которого она выросла.
    need_leads = {int(d["LEAD_ID"]) for d in deals
                  if d.get("LEAD_ID") and not (d.get(UF_YCLID) or d.get(UF_CLIENT))}
    source = {}
    if need_leads:
        for chunk in [list(need_leads)[i:i + 50] for i in range(0, len(need_leads), 50)]:
            for l in fetch_all("crm.lead.list", {
                    "filter": {"ID": chunk},
                    "select": ["ID", UF_CLIENT, UF_YCLID]}):
                source[int(l["ID"])] = l
        print(f"дотянуто лидов под сделки: {len(source)}")

    rows, missing = [], collections.Counter()

    for l in leads:
        yclid, client = l.get(UF_YCLID) or "", l.get(UF_CLIENT) or ""
        if not yclid and not client:
            missing["лид без идентификаторов"] += 1
            continue
        rows.append({"yclid": yclid, "ym_client_id": client,
                     "datetime": moment(l.get("DATE_MODIFY") or l.get("DATE_CREATE")),
                     "price": "", "target": GOAL_LEAD,
                     "comment": f'лид {l["ID"]}'})

    for d in deals:
        yclid, client = d.get(UF_YCLID) or "", d.get(UF_CLIENT) or ""
        if not yclid and not client and d.get("LEAD_ID"):
            src = source.get(int(d["LEAD_ID"]), {})
            yclid, client = src.get(UF_YCLID) or "", src.get(UF_CLIENT) or ""
        if not yclid and not client:
            missing["сделка без лида и без идентификаторов"
                    if not d.get("LEAD_ID") else "сделка из лида, но лид пустой"] += 1
            continue
        price = str(d.get("OPPORTUNITY") or "").split(".")[0]
        rows.append({"yclid": yclid, "ym_client_id": client,
                     "datetime": moment(d.get("CLOSEDATE") or d.get("DATE_CREATE")),
                     "price": price, "target": GOAL_DEAL,
                     "comment": f'сделка {d["ID"]}'})

    print(f"\nк загрузке: {len(rows)} событий")
    for reason, n in missing.most_common():
        print(f"  пропущено {n:>4} — {reason}")

    if not rows:
        print("\nЗагружать нечего. Это не поломка: пока заявки с сайта не дошли")
        print("до CRM с заполненными полями, привязать конверсию не к чему.")
        print("Проверить: приходят ли лиды с SOURCE_ID=WEB и заполнены ли у них")
        print(f"{UF_YCLID} и {UF_CLIENT}.")
        return

    dest = a.out or os.path.join(ROOT, "perf-reports", "ads",
                                 f"crm-conversions-{date_to}.csv")
    os.makedirs(os.path.dirname(dest), exist_ok=True)
    with open(dest, "w", encoding="utf-8", newline="") as f:
        w = csv.DictWriter(f, fieldnames=["yclid", "ym_client_id", "datetime",
                                          "price", "target", "comment"])
        w.writeheader()
        w.writerows(rows)
    print(f"файл: {os.path.relpath(dest, ROOT)}")
    print(f"\nЗагрузить:\n  python scripts/ads/offline_conversions.py upload "
          f"{os.path.relpath(dest, ROOT)} --dry-run")


if __name__ == "__main__":
    main()
