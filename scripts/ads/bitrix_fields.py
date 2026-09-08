# -*- coding: utf-8 -*-
"""Создаёт в Битрикс24 поля под идентификаторы визита.

Метрика связывает загруженные из CRM лиды и сделки с визитами по ClientID,
и ищет его в поле со специальным **названием**: `metrika_client_id` и ещё
17 вариантов написания. Через интерфейс облачного Битрикса символьный код
задать нельзя — он генерируется сам; через REST можно задать и код, и
название, поэтому поля заводим отсюда.

Второе поле, `yclid`, Метрике в этой связке не нужно: оно для карточки
(менеджер видит, что человек пришёл с рекламы) и для запасного пути
загрузки офлайн-конверсий через scripts/ads/offline_conversions.py.

    python scripts/ads/bitrix_fields.py --dry-run
    python scripts/ads/bitrix_fields.py
"""
import argparse
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))

# Скрипт заводит поля и только. Всё остальное — правка сделок, смена стадий,
# удаление — до сети не доходит: имя метода сверяется со списком.
ALLOWED = {
    "crm.lead.userfield.add", "crm.deal.userfield.add", "crm.contact.userfield.add",
    "crm.lead.userfield.list", "crm.deal.userfield.list", "crm.contact.userfield.list",
}

ENTITIES = ("lead", "deal", "contact")

# name — то, что увидит Метрика; code — символьный код, Битрикс требует
# префикс UF_CRM_. Совпадение по обоим сразу — страховка на случай, если
# Метрика смотрит не на название, а на код.
FIELDS = [
    {"code": "UF_CRM_METRIKA_CLIENT_ID", "name": "metrika_client_id",
     "hint": "ClientID Яндекс.Метрики. Заполняется сайтом автоматически, вручную не трогать."},
    {"code": "UF_CRM_YCLID", "name": "yclid",
     "hint": "Идентификатор клика по объявлению Яндекс.Директа. Заполняется сайтом."},
]


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
        sys.exit(f"ОТКАЗ: {method} вне списка разрешённых. Скрипт только заводит поля.")
    r = requests.post(f"{HOOK}/{method}.json", json=params or {}, timeout=90)
    try:
        d = r.json()
    except ValueError:
        sys.exit(f"{method}: не JSON, HTTP {r.status_code}\n{r.text[:300]}")
    if "error" in d:
        return {"error": d.get("error"), "detail": d.get("error_description")}
    return {"result": d.get("result")}


def payload(field):
    """Название дублируем во все три подписи: Метрика может смотреть любую."""
    label = {"ru": field["name"], "en": field["name"]}
    return {"fields": {
        "FIELD_NAME": field["code"],
        "USER_TYPE_ID": "string",
        "SORT": 900,
        "MULTIPLE": "N",
        "MANDATORY": "N",
        "SHOW_FILTER": "N",
        "SHOW_IN_LIST": "Y",
        "EDIT_IN_LIST": "Y",
        "IS_SEARCHABLE": "N",
        "EDIT_FORM_LABEL": label,
        "LIST_COLUMN_LABEL": label,
        "LIST_FILTER_LABEL": label,
        "ERROR_MESSAGE": label,
        "HELP_MESSAGE": {"ru": field["hint"], "en": field["hint"]},
    }}


def existing(entity):
    res = call(f"crm.{entity}.userfield.list", {"filter": {}})
    if "error" in res:
        return {}, res
    out = {}
    for f in res.get("result") or []:
        out[f.get("FIELD_NAME")] = f.get("ID")
    return out, None


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true",
                    help="показать, что уйдёт, и ничего не создавать")
    a = ap.parse_args()
    if not HOOK:
        sys.exit("Нет BITRIX_WEBHOOK в site/.env")

    print(f"портал: {HOOK.split('/rest/')[0]}\n")
    for entity in ENTITIES:
        have, err = existing(entity)
        if err:
            print(f"{entity}: не прочитать список полей — {err['error']}: {err['detail']}")
            continue
        print(f"=== {entity.upper()}: пользовательских полей сейчас {len(have)} ===")
        for field in FIELDS:
            if field["code"] in have:
                print(f'  {field["code"]:28} уже есть (ID {have[field["code"]]}) — пропускаем')
                continue
            body = payload(field)
            if a.dry_run:
                print(f'  {field["code"]:28} будет создано:')
                print("     ", json.dumps(body, ensure_ascii=False)[:260])
                continue
            res = call(f"crm.{entity}.userfield.add", body)
            if "error" in res:
                print(f'  {field["code"]:28} ОШИБКА {res["error"]}: {res["detail"]}')
            else:
                print(f'  {field["code"]:28} создано, ID {res["result"]}')
        print()


if __name__ == "__main__":
    main()
