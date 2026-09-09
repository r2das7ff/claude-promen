# -*- coding: utf-8 -*-
"""Чёрный список площадок для РСЯ-кампании.

Кампания «Детали для АЭС/РСЯ/Россия» за год: 113 374 ₽, 8 357 площадок.
Формально у неё лучший CPA аккаунта, но данные Метрики этого не
подтверждают: глубина визита из сетей ровно 1.0 страницы при 29 секундах,
и на этом фоне 162 «отправки формы». Для B2B-заявки, где надо указать
позицию, объём и контакты, столько времени не хватает. Для сравнения
поиск: глубина 1.8, 103 формы на вчетверо меньшем трафике.

Отдельной настройки «не показывать в мобильных приложениях» в API нет —
единственный рычаг — список запрещённых площадок кампании (ExcludedSites,
до 1000 записей). Скрипт отбирает мусор по расходу и отсутствию заявок.

Приоритет отбора, если 1000 не хватает: сначала приложения и ТВ (там
заявок почти нет вовсе), потом биржи без единой заявки, потом сайты.

    python scripts/ads/direct_placements.py --campaign 705559963
    python scripts/ads/direct_placements.py --campaign 705559963 --apply
"""
import argparse
import csv
import io
import json
import os
import re
import sys
import time

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
API = "https://api.direct.yandex.com/json/"
ALLOWED = {"campaigns.get", "campaigns.update", "reports"}
LIMIT = 1000                      # потолок Директа на список запрещённых площадок

# Приложения и ТВ-приставки: пакет вида com.*/ru.*, суффикс .ctv, video.like.
APP = re.compile(r"^(com|ru|org|net|io|app|de|jp|kr|cn)\.|\.ctv$|^video\.")

# Автоцели формы, телефона и почты. Цели «Заявка: …» заведены в сентябре
# 2026 и за прошедший год пусты, CRM-цели пусты по всему аккаунту.
GOALS = ["182776720", "229699797", "180275119"]


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


HEADERS = {
    "Authorization": f"Bearer {env('YANDEX_DIRECT_TOKEN')}",
    "Client-Login": env("YANDEX_DIRECT_LOGIN") or "",
    "Accept-Language": "ru",
    "Content-Type": "application/json; charset=utf-8",
}


def call(service, method, params, ver="v5"):
    full = f"{service}.{method}"
    if full not in ALLOWED:
        sys.exit(f"ОТКАЗ: {full} вне списка разрешённых")
    r = requests.post(API + ver + "/" + service, headers=HEADERS,
                      data=json.dumps({"method": method, "params": params}).encode("utf-8"),
                      timeout=180)
    d = r.json()
    if "error" in d:
        e = d["error"]
        sys.exit(f"{full} → {e.get('error_code')}: {e.get('error_string')} | {e.get('error_detail')}")
    res = d.get("result", {})
    for item in (res.get("UpdateResults") or []):
        for x in (item.get("Errors") or []):
            print(f"    ошибка {x.get('Code')} у {item.get('Id')}: "
                  f"{x.get('Message')} {x.get('Details', '')}")
    return res


def num(v):
    try:
        return float(v)
    except (TypeError, ValueError):
        return 0.0


def placements(campaign_id, date_from, date_to):
    """Расход и заявки по площадкам за период."""
    h = dict(HEADERS, processingMode="auto", returnMoneyInMicros="false",
             skipReportHeader="true", skipReportSummary="true")
    body = {"params": {
        "SelectionCriteria": {"DateFrom": date_from, "DateTo": date_to,
                              "Filter": [{"Field": "CampaignId", "Operator": "EQUALS",
                                          "Values": [str(campaign_id)]}]},
        "FieldNames": ["Placement", "Impressions", "Clicks", "Cost", "Conversions"],
        "Goals": GOALS, "AttributionModels": ["LSC"],
        "ReportName": f"placements-{campaign_id}-{date_to}", "ReportType": "CUSTOM_REPORT",
        "DateRangeType": "CUSTOM_DATE", "Format": "TSV", "IncludeVAT": "YES"}}
    while True:
        r = requests.post(API + "v5/reports", headers=h,
                          data=json.dumps(body).encode("utf-8"), timeout=300)
        if r.status_code in (201, 202):
            time.sleep(10)
            continue
        break
    if r.status_code != 200:
        sys.exit(f"отчёт не отдался: {r.status_code} {r.text[:200]}")
    rows = list(csv.DictReader(io.StringIO(r.text), delimiter="\t"))
    goal_cols = [c for c in rows[0] if c.startswith("Conversions_")] if rows else []
    return [{"name": x["Placement"], "cost": num(x["Cost"]), "clicks": num(x["Clicks"]),
             "goals": sum(num(x[c]) for c in goal_cols)} for x in rows]


def kind(name):
    if name.startswith("dsp") and ".yandex.ru" in name:
        return "биржа"
    if APP.search(name):
        return "приложение"
    return "сайт"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--campaign", type=int, required=True)
    ap.add_argument("--from", dest="date_from", default="2025-09-09")
    ap.add_argument("--to", dest="date_to", default="2026-09-09")
    ap.add_argument("--apply", action="store_true")
    a = ap.parse_args()

    rows = placements(a.campaign, a.date_from, a.date_to)
    total = sum(r["cost"] for r in rows)
    print(f"площадок в отчёте: {len(rows)}, расход {total:,.0f} ₽".replace(",", " "))

    # Держим только то, что тратило деньги: площадка с нулевым расходом
    # занимает место в списке, но ничего не экономит.
    spent = [r for r in rows if r["cost"] > 0]
    for r in spent:
        r["kind"] = kind(r["name"])
    # Заявки на площадке — повод оставить её даже при слабых цифрах.
    # Решение о самих биржах принимается отдельно, не этим скриптом.
    trash = [r for r in spent if r["goals"] == 0]
    order = {"приложение": 0, "биржа": 1, "сайт": 2}
    trash.sort(key=lambda r: (order[r["kind"]], -r["cost"]))
    keep = trash[:LIMIT]

    by = {}
    for r in spent:
        b = by.setdefault(r["kind"], {"n": 0, "cost": 0.0, "goals": 0.0,
                                      "block": 0, "saved": 0.0})
        b["n"] += 1
        b["cost"] += r["cost"]
        b["goals"] += r["goals"]
    for r in keep:
        by[r["kind"]]["block"] += 1
        by[r["kind"]]["saved"] += r["cost"]

    head = f'{"тип":14} {"площадок":>9} {"расход":>10} {"заявок":>7} {"в стоп":>7} {"экономия":>10}'
    print("\n" + head)
    for k, b in sorted(by.items(), key=lambda kv: -kv[1]["cost"]):
        print(f'  {k:12} {b["n"]:>9,.0f} {b["cost"]:>10,.0f} {b["goals"]:>7,.0f} '
              f'{b["block"]:>7,.0f} {b["saved"]:>10,.0f}'.replace(",", " "))
    saved = sum(r["cost"] for r in keep)
    print(f'\nв чёрный список: {len(keep)} площадок, они съели {saved:,.0f} ₽ '
          f'без единой заявки'.replace(",", " "))
    if len(trash) > LIMIT:
        tail = trash[LIMIT]["cost"]
        print(f'(не поместилось {len(trash) - LIMIT} — хвост по {tail:.0f} ₽ и мельче)')

    if not a.apply:
        print("\nэто отчёт. Для правки: --apply")
        return

    cur = call("campaigns", "get", {"SelectionCriteria": {"Ids": [a.campaign]},
                                    "FieldNames": ["Id", "ExcludedSites"]})
    was = ((cur.get("Campaigns") or [{}])[0].get("ExcludedSites") or {}).get("Items") or []
    # Директ считает площадки без учёта регистра и отвергает список целиком
    # (ошибка 9802), если один пакет пришёл в двух написаниях. В отчёте такое
    # встречается, поэтому дедуп идёт по нижнему регистру.
    merged, seen = [], set()
    for name in was + [r["name"] for r in keep]:
        low = name.strip().lower()
        if low and low not in seen:
            seen.add(low)
            merged.append(low)
    merged = merged[:LIMIT]
    print(f"\nбыло в списке {len(was)}, станет {len(merged)}")
    call("campaigns", "update", {"Campaigns": [
        {"Id": a.campaign, "ExcludedSites": {"Items": merged}}]})
    chk = call("campaigns", "get", {"SelectionCriteria": {"Ids": [a.campaign]},
                                    "FieldNames": ["Id", "ExcludedSites"]})
    now = ((chk.get("Campaigns") or [{}])[0].get("ExcludedSites") or {}).get("Items") or []
    print(f"в кампании сейчас запрещено площадок: {len(now)}")


if __name__ == "__main__":
    main()
