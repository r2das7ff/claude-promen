# -*- coding: utf-8 -*-
"""Загрузка офлайн-конверсий в Яндекс.Метрику.

Замыкает цепочку «клик по объявлению → заявка → сделка в CRM». Без неё
Директ видит только отправку формы и обучает стратегии на ней — вместе
со спамом и случайными тапами из мобильных игр.

Склейка идёт по идентификаторам, которые сайт кладёт в заявку
(`request-modal.js` → `_promen_yclid`, `_promen_ym_client_id`):

* **yclid** — идентификатор клика по объявлению. Самый точный вариант:
  конверсия ложится ровно на ту кампанию и фразу, что привели человека.
* **ClientID** — идентификатор браузера в Метрике. Работает для всех
  остальных, но привязывает к визиту, а не к клику.

Файл-источник — CSV с колонками (порядок не важен, лишние игнорируются):

    yclid,ym_client_id,datetime,price,comment
    987654321012345,,2026-09-01 14:30,180000,счёт 1245
    ,1712345678901234567,2026-09-02 09:15,,квалификация менеджером

`datetime` — момент события в CRM (не время заявки), в часовом поясе
счётчика. `price` необязателен. Строки без обоих идентификаторов
пропускаются: привязать их не к чему.

    python scripts/ads/offline_conversions.py upload deals.csv
    python scripts/ads/offline_conversions.py upload deals.csv --dry-run
    python scripts/ads/offline_conversions.py status
"""
import argparse
import csv
import datetime as dt
import io
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
BASE = "https://api-metrika.yandex.net/management/v1/counter"

# Цель создана в счётчике как «CRM: квалифицированная заявка» с условием
# «идентификатор JavaScript-события = crm_qualified». Метрика связывает
# загруженные события с целью именно по этой строке, а не по id цели.
# Идентификатор цели по умолчанию. Разные события CRM ложатся на разные
# цели: квалифицированный лид — одна, оплаченная сделка — другая, поэтому
# значение переопределяется ключом --target.
TARGET = "crm_qualified"


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


TOKEN = env("YANDEX_METRIKA_TOKEN")
COUNTER = env("YANDEX_METRIKA_COUNTER")


def parse_moment(raw: str) -> int:
    """Метрика принимает время события как unix timestamp."""
    raw = (raw or "").strip()
    for fmt in ("%Y-%m-%d %H:%M:%S", "%Y-%m-%d %H:%M", "%Y-%m-%d",
                "%d.%m.%Y %H:%M:%S", "%d.%m.%Y %H:%M", "%d.%m.%Y"):
        try:
            return int(dt.datetime.strptime(raw, fmt).timestamp())
        except ValueError:
            continue
    if raw.isdigit():
        return int(raw)
    raise ValueError(f"не разобрать дату: {raw!r}")


def read_rows(path):
    """Разделяет строки на две пачки: по yclid и по ClientID."""
    by_yclid, by_client, skipped = [], [], 0
    with open(path, encoding="utf-8-sig", newline="") as f:
        for row in csv.DictReader(f):
            row = {(k or "").strip().lower(): (v or "").strip() for k, v in row.items()}
            yclid = row.get("yclid", "")
            client = row.get("ym_client_id") or row.get("clientid") or ""
            if not yclid and not client:
                skipped += 1
                continue
            item = {
                "moment": parse_moment(row.get("datetime") or row.get("date") or ""),
                "price": row.get("price", ""),
                "comment": row.get("comment", ""),
                "target": row.get("target", ""),
            }
            if yclid:
                item["id"] = yclid
                by_yclid.append(item)
            else:
                item["id"] = client
                by_client.append(item)
    return by_yclid, by_client, skipped


def make_csv(rows, id_column):
    buf = io.StringIO()
    has_price = any(r["price"] for r in rows)
    header = [id_column, "Target", "DateTime"] + (["Price", "Currency"] if has_price else [])
    w = csv.writer(buf)
    w.writerow(header)
    for r in rows:
        line = [r["id"], r.get("target") or TARGET, r["moment"]]
        if has_price:
            line += [r["price"] or "", "RUB" if r["price"] else ""]
        w.writerow(line)
    return buf.getvalue()


def upload(rows, id_type, id_column, dry_run):
    if not rows:
        return
    payload = make_csv(rows, id_column)
    print(f"\n— {id_type}: {len(rows)} событий")
    preview = payload.splitlines()[:4]
    for line in preview:
        print("   ", line)
    if len(rows) > 3:
        print(f"    … ещё {len(rows) - 3}")
    if dry_run:
        print("    (dry-run, ничего не отправлено)")
        return
    r = requests.post(
        f"{BASE}/{COUNTER}/offline_conversions/upload",
        headers={"Authorization": f"OAuth {TOKEN}"},
        params={"client_id_type": id_type},
        files={"file": ("conversions.csv", payload.encode("utf-8"), "text/csv")},
        timeout=180,
    )
    if r.status_code >= 400:
        print(f"    ОШИБКА {r.status_code}: {r.text[:400]}")
        return
    up = r.json().get("uploading", {})
    print(f"    загружено: id={up.get('id')} строк={up.get('line_quantity')} "
          f"статус={up.get('status')}")


def status():
    r = requests.get(f"{BASE}/{COUNTER}/offline_conversions/uploadings",
                     headers={"Authorization": f"OAuth {TOKEN}"}, timeout=60)
    ups = r.json().get("uploadings", [])
    if not ups:
        print("загрузок ещё не было")
        return
    print(f'{"id":>10} {"создана":19} {"строк":>7} {"статус":14} {"тип id"}')
    for u in ups:
        print(f'{u.get("id", ""):>10} {u.get("create_time", ""):19} '
              f'{u.get("line_quantity", 0):>7} {u.get("status", ""):14} '
              f'{u.get("client_id_type", "")}')


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("command", choices=["upload", "status"])
    ap.add_argument("file", nargs="?", help="CSV со сделками")
    ap.add_argument("--dry-run", action="store_true",
                    help="показать, что уйдёт, и ничего не отправлять")
    a = ap.parse_args()
    if not TOKEN or not COUNTER:
        sys.exit("Нет YANDEX_METRIKA_TOKEN / YANDEX_METRIKA_COUNTER в site/.env")

    if a.command == "status":
        status()
        return
    if not a.file:
        sys.exit("Укажите CSV: offline_conversions.py upload deals.csv")

    by_yclid, by_client, skipped = read_rows(a.file)
    print(f"строк с yclid: {len(by_yclid)}, с ClientID: {len(by_client)}, "
          f"пропущено без идентификатора: {skipped}")
    upload(by_yclid, "YCLID", "Yclid", a.dry_run)
    upload(by_client, "CLIENT_ID", "ClientId", a.dry_run)
    if not a.dry_run and (by_yclid or by_client):
        print("\nМетрика обрабатывает загрузку до нескольких часов; "
              "проверить: offline_conversions.py status")


if __name__ == "__main__":
    main()
