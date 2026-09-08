# -*- coding: utf-8 -*-
"""Отдельная кампания ретаргетинга по клиентской базе из CRM.

Зачем отдельная. Сегменты «база» и «лал база» сидели группами внутри
РСЯ-кампании «Детали для АЭС» и делили с автотаргетингом один недельный
лимит. Директ льёт бюджет туда, где клик дешевле, поэтому за год на базу
из 7 019 закупщиков ушло 278 ₽ — 0,04% бюджета. Собственный кошелёк
решает это одним движением.

Кампания создаётся **остановленной**: после создания её надо посмотреть
глазами в интерфейсе и запустить руками.

    python scripts/ads/direct_retargeting.py            # показать план
    python scripts/ads/direct_retargeting.py --apply    # создать
"""
import argparse
import datetime as dt
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
API = "https://api.direct.yandex.com/json/v5/"

# Только то, что нужно для сборки кампании. Ничего не удаляем и не правим
# в существующих кампаниях.
ALLOWED = {
    "sitelinks.add", "retargetinglists.add", "campaigns.add", "campaigns.suspend",
    "adgroups.add", "audiencetargets.add", "ads.add",
    "sitelinks.get", "retargetinglists.get", "campaigns.get",
}

SEGMENT_ID = 59511283          # «База клиентов из CRM от 08.09.2026 (MD5)»
COUNTER_ID = 62844301
REGION_RUSSIA = 225
WEEKLY_BUDGET = 5_000          # ₽
LANDING = "https://prom-en.com/catalog/sdt/"
CAMPAIGN_NAME = "Ретаргетинг: база клиентов CRM | РСЯ | Россия"

# Разметка без roistat: его скрипт при переезде на новый сайт не перенесён,
# метки в адресе остались бы мусором. Макрос retargeting_id вместо keyword —
# в ретаргетинге ключевых фраз нет.
UTM = ("?utm_source=yandex&utm_medium=cpc"
       "&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{retargeting_id}")


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


def call(service, method, params):
    full = f"{service}.{method}"
    if full not in ALLOWED:
        sys.exit(f"ОТКАЗ: {full} вне списка разрешённых")
    r = requests.post(API + service, headers=HEADERS,
                      data=json.dumps({"method": method, "params": params}).encode("utf-8"),
                      timeout=120)
    d = r.json()
    if "error" in d:
        e = d["error"]
        sys.exit(f"{full} → {e.get('error_code')}: {e.get('error_string')} | {e.get('error_detail')}")
    res = d.get("result", {})
    for item in (res.get("AddResults") or []):
        for w in item.get("Warnings") or []:
            print(f"    предупреждение: {w.get('Message')} {w.get('Details','')}")
        for x in item.get("Errors") or []:
            sys.exit(f"    ошибка: {x.get('Message')} {x.get('Details','')}")
    return res


SITELINKS = [
    ("Каталог СДТ", "/catalog/sdt/", "Отводы, тройники, переходы, заглушки"),
    ("Калькуляторы", "/kalkulyatory/", "Вес СДТ, аналоги сталей, ДН и дюймы"),
    ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ-3"),
    ("Контакты", "/contacts/", "Отдел продаж, отгрузка со склада"),
]

AD = {
    "Title": "Детали трубопроводов для АЭС и ТЭС",
    "Title2": "Завод в Челябинске",
    "Text": "Отводы, тройники, переходы, заглушки по ГОСТ и ОСТ. Изготовление по чертежу.",
    "AdImageHash": "PLk5Qu9vFFXFRM-tH60AFw",
}


def plan():
    budget = f"{WEEKLY_BUDGET:,}".replace(",", " ")
    print(f"""ПЛАН

1. Быстрые ссылки — новый набор (старый ведёт на /prajs-list-truby/,
   /uslugi/, /trust-us/, которых больше нет):""")
    for title, path, desc in SITELINKS:
        print(f"     «{title}» → {path}  ({desc})")

    print(f"""
2. Условие ретаргетинга «База клиентов из CRM (08.09.2026)»
     сегмент {SEGMENT_ID}, срок жизни 540 дней

3. Кампания «{CAMPAIGN_NAME}»
     поиск:  выключен (ретаргетинг работает только в сетях)
     сети:   максимум кликов, недельный бюджет {budget} ₽
     счётчик Метрики: {COUNTER_ID}
     расширенный геотаргетинг: выключен
     состояние: ОСТАНОВЛЕНА — запускать руками после проверки

4. Группа «База клиентов из CRM», регион: Россия ({REGION_RUSSIA})

5. Объявление:
     {AD['Title']} · {AD['Title2']}
     {AD['Text']}
     → {LANDING}
     изображение: {AD['AdImageHash']}
""")


def segment_ready() -> bool:
    """Директ не видит сегмент, пока Аудитории не досчитают совпадения:
    попытка создать условие на необработанный сегмент падает с 8800
    «Объект не найден»."""
    r = requests.get("https://api-audience.yandex.ru/v1/management/segments",
                     headers={"Authorization": f"OAuth {env('YANDEX_METRIKA_TOKEN')}"},
                     timeout=60)
    for seg in r.json().get("segments", []):
        if seg.get("id") == SEGMENT_ID:
            status = seg.get("status")
            matched = seg.get("matched_quantity")
            print(f"   сегмент {SEGMENT_ID}: статус {status}, "
                  f"сматчено {matched if matched is not None else 'ещё считается'}")
            return status == "processed"
    print(f"   сегмент {SEGMENT_ID} не найден в Аудиториях")
    return False


def existing_sitelinks():
    """Набор мог остаться от прерванного запуска — второй такой же не нужен."""
    res = call("sitelinks", "get", {"SelectionCriteria": {},
                                    "FieldNames": ["Id", "Sitelinks"], "Limit": 500})
    want = {t for t, _, _ in SITELINKS}
    for st in res.get("SitelinksSets", []):
        titles = {l.get("Title") for l in st.get("Sitelinks", [])}
        if want == titles:
            return st["Id"]
    return None


def existing_campaign():
    res = call("campaigns", "get", {"SelectionCriteria": {},
                                    "FieldNames": ["Id", "Name"]})
    for c in res.get("Campaigns", []):
        if c.get("Name") == CAMPAIGN_NAME:
            return c["Id"]
    return None


def apply():
    print("0. проверяю готовность сегмента…")
    if not segment_ready():
        print("\nСегмент ещё обрабатывается — Яндекс считает совпадения.")
        print("Обычно это занимает несколько часов. Запустите скрипт повторно,")
        print("когда статус станет processed: всё уже созданное переиспользуется.")
        return

    if existing_campaign():
        print(f"\nКампания «{CAMPAIGN_NAME}» уже существует — выходим, чтобы не плодить дубли.")
        return

    print("1. быстрые ссылки…")
    sitelink_id = existing_sitelinks()
    if sitelink_id:
        print(f"   набор уже есть: {sitelink_id}")
    else:
        res = call("sitelinks", "add", {"SitelinksSets": [{"Sitelinks": [
            {"Title": t, "Href": "https://prom-en.com" + p + UTM, "Description": d}
            for t, p, d in SITELINKS
        ]}]})
        sitelink_id = res["AddResults"][0]["Id"]
        print(f"   набор {sitelink_id}")

    print("2. условие ретаргетинга…")
    res = call("retargetinglists", "add", {"RetargetingLists": [{
        "Name": "База клиентов из CRM (08.09.2026)",
        "Description": "Хешированная выгрузка контактов, 16 236 записей",
        "Type": "RETARGETING",
        "Rules": [{"Operator": "ALL", "Arguments": [
            {"MembershipLifeSpan": 540, "ExternalId": SEGMENT_ID}
        ]}],
    }]})
    retargeting_id = res["AddResults"][0]["Id"]
    print(f"   условие {retargeting_id}")

    print("3. кампания…")
    res = call("campaigns", "add", {"Campaigns": [{
        "Name": CAMPAIGN_NAME,
        "StartDate": dt.date.today().isoformat(),
        "TextCampaign": {
            "BiddingStrategy": {
                "Search": {"BiddingStrategyType": "SERVING_OFF"},
                "Network": {
                    "BiddingStrategyType": "WB_MAXIMUM_CLICKS",
                    "WbMaximumClicks": {"WeeklySpendLimit": WEEKLY_BUDGET * 1_000_000},
                },
            },
            "Settings": [
                {"Option": "ADD_METRICA_TAG", "Value": "YES"},
                {"Option": "ADD_OPENSTAT_TAG", "Value": "NO"},
                {"Option": "ENABLE_AREA_OF_INTEREST_TARGETING", "Value": "NO"},
            ],
        },
        "CounterIds": {"Items": [COUNTER_ID]},
    }]})
    campaign_id = res["AddResults"][0]["Id"]
    print(f"   кампания {campaign_id}")

    print("4. останавливаем до проверки…")
    call("campaigns", "suspend", {"SelectionCriteria": {"Ids": [campaign_id]}})

    print("5. группа…")
    res = call("adgroups", "add", {"AdGroups": [{
        "Name": "База клиентов из CRM",
        "CampaignId": campaign_id,
        "RegionIds": [REGION_RUSSIA],
    }]})
    adgroup_id = res["AddResults"][0]["Id"]
    print(f"   группа {adgroup_id}")

    print("6. привязка аудитории…")
    call("audiencetargets", "add", {"AudienceTargets": [{
        "AdGroupId": adgroup_id,
        "RetargetingListId": retargeting_id,
    }]})

    print("7. объявление…")
    res = call("ads", "add", {"Ads": [{
        "AdGroupId": adgroup_id,
        "TextAd": {
            "Title": AD["Title"],
            "Title2": AD["Title2"],
            "Text": AD["Text"],
            "Mobile": "NO",
            "Href": LANDING + UTM,
            "AdImageHash": AD["AdImageHash"],
            "SitelinkSetId": sitelink_id,
        },
    }]})
    print(f"   объявление {res['AddResults'][0]['Id']}")

    print(f"\nГОТОВО. Кампания {campaign_id} создана и остановлена.")
    print(f"https://direct.yandex.ru/dna/grid/campaign?cid={campaign_id}")


if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("--apply", action="store_true", help="создать (без флага только показывает план)")
    a = ap.parse_args()
    plan()
    if a.apply:
        print("=" * 60)
        apply()
    else:
        print("это только план. Для создания: --apply")
