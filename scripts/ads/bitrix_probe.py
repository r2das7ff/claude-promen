# -*- coding: utf-8 -*-
"""Разведка Битрикс24 перед настройкой сквозной аналитики. Только чтение.

Показывает воронки, стадии, пользовательские поля лидов и сделок и то,
есть ли уже куда положить ClientID Метрики и yclid Директа. По результату
решаем: заводить свои UF-поля или использовать существующие.

Доступ — входящий вебхук с правом `crm`, строкой в site/.env:

    BITRIX_WEBHOOK=https://<портал>.bitrix24.ru/rest/<id>/<код>/

Вебхук равносилен доступу к CRM, поэтому живёт только в .env
(он в .gitignore) и в переписку не попадает.

    python scripts/ads/bitrix_probe.py
"""
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))


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


# Скрипт только смотрит. Вебхуку с правом `crm` технически разрешена и
# запись, поэтому единственная гарантия здесь — белый список: всё, что не
# читает, до сети не доходит. Ошибка в коде или опечатка в имени метода
# упрётся в эту проверку, а не в живую CRM.
READ_ONLY_SUFFIXES = (".list", ".get", ".fields", ".stage.list")
READ_ONLY_METHODS = {"profile"}


def is_read_only(method: str) -> bool:
    return method in READ_ONLY_METHODS or method.endswith(READ_ONLY_SUFFIXES)


def call(method, params=None):
    if not is_read_only(method):
        sys.exit(f"ОТКАЗ: {method} меняет данные. Скрипт только читает CRM.")
    r = requests.post(f"{HOOK}/{method}.json", json=params or {}, timeout=90)
    try:
        d = r.json()
    except ValueError:
        sys.exit(f"{method}: не JSON, HTTP {r.status_code}\n{r.text[:300]}")
    if "error" in d:
        print(f"  {method} → {d.get('error')}: {d.get('error_description')}")
        return None
    return d.get("result")


def show_fields(entity, title):
    fields = call(f"crm.{entity}.fields")
    if not fields:
        return {}
    uf = {k: v for k, v in fields.items() if k.startswith("UF_")}
    print(f"\n=== {title}: {len(fields)} полей, из них пользовательских {len(uf)} ===")
    for k, v in uf.items():
        print(f'  {k:26} {v.get("type", ""):10} {v.get("title") or v.get("formLabel") or ""}')
    hints = [k for k, v in list(fields.items())
             if any(w in (k + " " + str(v.get("title") or "")).lower()
                    for w in ("client", "yclid", "utm", "metrika", "метрик", "источник"))]
    if hints:
        print("  похожее на аналитику:", ", ".join(hints[:12]))
    return fields


def main():
    if not HOOK:
        sys.exit("Нет BITRIX_WEBHOOK в site/.env.\n"
                 "Битрикс24 → Разработчикам → Другое → Входящий вебхук, право crm.")

    who = call("profile")
    if who:
        print(f"подключились как: {who.get('NAME', '')} {who.get('LAST_NAME', '')} "
              f"(id {who.get('ID')}), портал {HOOK.split('/rest/')[0]}")

    for entity, title in (("lead", "ЛИДЫ"), ("deal", "СДЕЛКИ")):
        show_fields(entity, title)

    print("\n=== ВОРОНКИ И СТАДИИ СДЕЛОК ===")
    cats = call("crm.dealcategory.list", {"select": ["ID", "NAME"]}) or []
    print(f"воронок: {len(cats) + 1} (включая основную)")
    for c in [{"ID": 0, "NAME": "Основная"}] + list(cats):
        stages = call("crm.dealcategory.stage.list", {"id": c["ID"]}) or []
        names = " → ".join(s.get("NAME", "") for s in stages)
        print(f'  [{c["ID"]}] {c["NAME"]}: {names[:150]}')

    print("\n=== СТАДИИ ЛИДОВ ===")
    for s in (call("crm.status.list", {"filter": {"ENTITY_ID": "STATUS"}}) or []):
        print(f'  {s.get("STATUS_ID"):16} {s.get("NAME")}')

    print("\n=== ОБЪЁМ ===")
    for entity in ("lead", "deal"):
        total = call(f"crm.{entity}.list", {"select": ["ID"], "start": -1})
        cnt = call(f"crm.{entity}.list", {"select": ["ID"]})
        print(f"  {entity}: в выборке {len(cnt or [])} (первая страница)")

    print("\n=== ПОСЛЕДНИЕ 5 ЛИДОВ ===")
    leads = call("crm.lead.list", {
        "select": ["ID", "TITLE", "STATUS_ID", "SOURCE_ID", "DATE_CREATE", "EMAIL", "PHONE"],
        "order": {"ID": "DESC"},
    }) or []
    for l in leads[:5]:
        print(f'  #{l.get("ID")} {str(l.get("TITLE"))[:44]:44} стадия={l.get("STATUS_ID")} '
              f'источник={l.get("SOURCE_ID")} {l.get("DATE_CREATE")}')

    print("\n=== ИСТОЧНИКИ ===")
    for s in (call("crm.status.list", {"filter": {"ENTITY_ID": "SOURCE"}}) or []):
        print(f'  {s.get("STATUS_ID"):16} {s.get("NAME")}')


if __name__ == "__main__":
    main()
