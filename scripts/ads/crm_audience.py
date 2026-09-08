# -*- coding: utf-8 -*-
"""Выгрузка клиентской базы из Битрикс24 в файл для Яндекс.Аудиторий.

Зачем. Сегмент «база от 09.12.2025» не обновлялся девять месяцев, и все
клиенты, появившиеся в CRM с декабря, в ретаргетинг не попадают. Плюс та
выгрузка сделана без хеширования: 7 814 адресов и телефонов лежат в Яндексе
открытым текстом. Здесь и то, и другое лечится.

Контакты уходят **хешированными MD5** — Яндекс.Аудитории принимают такой
формат, матчинг работает точно так же, а персональные данные наружу не
уезжают. Телефоны перед хешированием нормализуются к виду 79991234567:
Яндекс матчит строку в строку, и «+7 (999) 123-45-67» не совпадёт с тем же
номером, записанным иначе.

    python scripts/ads/crm_audience.py --out perf-reports/ads/audience
    python scripts/ads/crm_audience.py --only-clients   # только те, у кого есть сделки
"""
import argparse
import csv
import hashlib
import os
import re
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))

# Только чтение CRM: выгрузка не имеет права ничего менять.
ALLOWED = {"crm.contact.list", "crm.deal.list", "crm.company.list"}


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


def call(method, params=None):
    if method not in ALLOWED:
        sys.exit(f"ОТКАЗ: {method} вне списка чтения")
    r = requests.post(f"{HOOK}/{method}.json", json=params or {}, timeout=120)
    d = r.json()
    if "error" in d:
        sys.exit(f"{method}: {d.get('error')} — {d.get('error_description')}")
    return d


def paged(method, params):
    """Битрикс отдаёт по 50 записей, дальше — по next."""
    out, start = [], 0
    while True:
        p = dict(params)
        p["start"] = start
        d = call(method, p)
        out.extend(d.get("result") or [])
        nxt = d.get("next")
        if nxt is None:
            return out
        start = nxt


def norm_phone(raw: str) -> str:
    """79991234567 — единственный вид, в котором Яндекс совпадёт с нашим."""
    digits = re.sub(r"\D+", "", raw or "")
    if len(digits) == 11 and digits[0] == "8":
        digits = "7" + digits[1:]
    if len(digits) == 10:
        digits = "7" + digits
    return digits if len(digits) == 11 and digits[0] == "7" else ""


def norm_email(raw: str) -> str:
    e = (raw or "").strip().lower()
    return e if re.match(r"^[^@\s]+@[^@\s]+\.[^@\s]+$", e) else ""


def md5(value: str) -> str:
    return hashlib.md5(value.encode("utf-8")).hexdigest()


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--out", default="perf-reports/ads/audience")
    ap.add_argument("--only-clients", action="store_true",
                    help="только контакты, у которых есть хотя бы одна сделка")
    a = ap.parse_args()
    if not HOOK:
        sys.exit("Нет BITRIX_WEBHOOK в site/.env")

    print("читаю контакты…")
    contacts = paged("crm.contact.list", {
        "select": ["ID", "EMAIL", "PHONE", "DATE_CREATE"],
        "order": {"ID": "ASC"},
    })
    print(f"  контактов в CRM: {len(contacts)}")

    keep = None
    if a.only_clients:
        print("читаю сделки, чтобы отобрать тех, у кого они есть…")
        deals = paged("crm.deal.list", {"select": ["ID", "CONTACT_ID"], "order": {"ID": "ASC"}})
        keep = {str(d.get("CONTACT_ID")) for d in deals if d.get("CONTACT_ID")}
        print(f"  сделок: {len(deals)}, контактов с ними: {len(keep)}")

    emails, phones, skipped = set(), set(), 0
    for c in contacts:
        if keep is not None and str(c.get("ID")) not in keep:
            continue
        got = False
        for item in c.get("EMAIL") or []:
            e = norm_email(item.get("VALUE", ""))
            if e:
                emails.add(md5(e))
                got = True
        for item in c.get("PHONE") or []:
            p = norm_phone(item.get("VALUE", ""))
            if p:
                phones.add(md5(p))
                got = True
        if not got:
            skipped += 1

    print(f"\nуникальных email:    {len(emails)}")
    print(f"уникальных телефонов: {len(phones)}")
    print(f"контактов без пригодных контактных данных: {skipped}")

    out_dir = os.path.join(ROOT, a.out)
    os.makedirs(out_dir, exist_ok=True)
    path = os.path.join(out_dir, "audience-crm.csv")
    with open(path, "w", encoding="utf-8", newline="") as f:
        w = csv.writer(f)
        # Формат Яндекс.Аудиторий: заголовок из имён типов, по строке на запись.
        w.writerow(["email", "phone"])
        for h in sorted(emails):
            w.writerow([h, ""])
        for h in sorted(phones):
            w.writerow(["", h])
    total = len(emails) + len(phones)
    print(f"\nфайл: {path}")
    print(f"строк к загрузке: {total} (хеши MD5, исходных данных в файле нет)")
    if total < 100:
        print("ВНИМАНИЕ: Яндекс не запускает сегмент меньше 100 совпавших — этого мало.")


if __name__ == "__main__":
    main()
