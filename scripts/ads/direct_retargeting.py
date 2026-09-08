# -*- coding: utf-8 -*-
"""Кампания ретаргетинга по клиентской базе: сборка по частям.

Зачем отдельная кампания. Сегменты «база» и «лал база» сидели группами
внутри РСЯ-кампании «Детали для АЭС» и делили недельный лимит с
автотаргетингом. Директ льёт бюджет туда, где клик дешевле, поэтому за год
на базу из 7 019 закупщиков ушло 278 ₽ — 0,04% расхода. Свой кошелёк
решает это одним движением.

Две группы с разной логикой:

* **Клиенты Промэнергетики** — узкий сегмент из CRM (только отделы 23, 25,
  47): те, кто уже покупал именно эти изделия.
* **Похожие на клиентов** — look-alike по широкой выгрузке всей группы
  «Титан». Для поиска похожих чем больше исходных данных, тем лучше
  модель, поэтому здесь широкая база уместна, а в прямом ретаргетинге нет.

Сборка идёт этапами и каждый раз продолжается с того места, где встала:
Яндекс считает совпадения часами, а look-alike строится только по готовому
сегменту. Запускайте повторно — уже созданное переиспользуется.

Кампания создаётся **остановленной**: смотреть глазами и запускать руками.

    python scripts/ads/direct_retargeting.py            # состояние
    python scripts/ads/direct_retargeting.py --apply    # собрать, что уже можно
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
AUDIENCE = "https://api-audience.yandex.ru/v1/management"

ALLOWED = {
    "sitelinks.add", "retargetinglists.add", "campaigns.add", "campaigns.suspend",
    "adgroups.add", "audiencetargets.add", "ads.add",
    "sitelinks.get", "retargetinglists.get", "campaigns.get", "adgroups.get", "ads.get",
}

SEGMENT_NARROW = 59513096   # клиенты Промэнергетики, отделы 23/25/47
SEGMENT_WIDE = 59511283     # вся группа «Титан» — только как основа для look-alike
LOOKALIKE_VALUE = 3         # 1 — точнее и уже, 5 — шире; у прошлого стояла 1
COUNTER_ID = 62844301
REGION_RUSSIA = 225
WEEKLY_BUDGET = 5_000       # ₽
LANDING = "https://prom-en.com/catalog/sdt/"
CAMPAIGN_NAME = "Ретаргетинг: клиенты Промэнергетики | РСЯ | Россия"
LOOKALIKE_NAME = "Похожие на клиентов группы (08.09.2026)"

GROUPS = [
    ("Клиенты Промэнергетики", "Клиенты Промэнергетики из CRM (08.09.2026)", "narrow"),
    ("Похожие на клиентов", "Похожие на клиентов группы (08.09.2026)", "lookalike"),
]

# Без roistat: его скрипт при переезде не перенесён, метки были бы мусором.
# retargeting_id вместо keyword — в ретаргетинге ключевых фраз нет.
UTM = ("?utm_source=yandex&utm_medium=cpc"
       "&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{retargeting_id}")

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


DIRECT_HEADERS = {
    "Authorization": f"Bearer {env('YANDEX_DIRECT_TOKEN')}",
    "Client-Login": env("YANDEX_DIRECT_LOGIN") or "",
    "Accept-Language": "ru",
    "Content-Type": "application/json; charset=utf-8",
}
AUD_HEADERS = {"Authorization": f"OAuth {env('YANDEX_METRIKA_TOKEN')}"}


def call(service, method, params):
    full = f"{service}.{method}"
    if full not in ALLOWED:
        sys.exit(f"ОТКАЗ: {full} вне списка разрешённых")
    r = requests.post(API + service, headers=DIRECT_HEADERS,
                      data=json.dumps({"method": method, "params": params}).encode("utf-8"),
                      timeout=120)
    d = r.json()
    if "error" in d:
        e = d["error"]
        sys.exit(f"{full} → {e.get('error_code')}: {e.get('error_string')} | {e.get('error_detail')}")
    res = d.get("result", {})
    for item in (res.get("AddResults") or []):
        for w in item.get("Warnings") or []:
            print(f"    предупреждение: {w.get('Message')} {w.get('Details', '')}")
        for x in item.get("Errors") or []:
            sys.exit(f"    ошибка {x.get('Code')}: {x.get('Message')} {x.get('Details', '')}")
    return res


def segments():
    r = requests.get(f"{AUDIENCE}/segments", headers=AUD_HEADERS, timeout=60)
    if r.status_code >= 400:
        sys.exit(f"Аудитории → {r.status_code}: {r.text[:200]}")
    return r.json().get("segments", [])


def segment(seg_id):
    for s in segments():
        if s.get("id") == seg_id:
            return s
    return None


def find_lookalike():
    """Ищем по исходному сегменту, а не по имени: имя могли поменять руками."""
    for s in segments():
        if s.get("type") == "lookalike" and s.get("lookalike_link") == SEGMENT_WIDE:
            return s
    return None


def create_lookalike():
    body = {"segment": {
        "name": LOOKALIKE_NAME,
        "lookalike_link": SEGMENT_WIDE,
        "lookalike_value": LOOKALIKE_VALUE,
        "maintain_device_distribution": True,
        "maintain_geo_distribution": True,
    }}
    r = requests.post(f"{AUDIENCE}/segments/create_lookalike",
                      headers={**AUD_HEADERS, "Content-Type": "application/json"},
                      data=json.dumps(body).encode("utf-8"), timeout=120)
    if r.status_code >= 400:
        sys.exit(f"создание look-alike → {r.status_code}: {r.text[:300]}")
    return r.json().get("segment", {})


# ─────────────────────────────────────────── идемпотентные шаги сборки

def ensure_sitelinks():
    res = call("sitelinks", "get", {"SelectionCriteria": {},
                                    "FieldNames": ["Id", "Sitelinks"], "Limit": 500})
    want = {t for t, _, _ in SITELINKS}
    for st in res.get("SitelinksSets", []):
        if want == {l.get("Title") for l in st.get("Sitelinks", [])}:
            return st["Id"], False
    res = call("sitelinks", "add", {"SitelinksSets": [{"Sitelinks": [
        {"Title": t, "Href": "https://prom-en.com" + p + UTM, "Description": d}
        for t, p, d in SITELINKS
    ]}]})
    return res["AddResults"][0]["Id"], True


def ensure_retargeting_list(name, seg_id, description):
    res = call("retargetinglists", "get", {"SelectionCriteria": {},
                                           "FieldNames": ["Id", "Name"]})
    for l in res.get("RetargetingLists", []):
        if l.get("Name") == name:
            return l["Id"], False
    res = call("retargetinglists", "add", {"RetargetingLists": [{
        "Name": name,
        "Description": description,
        "Type": "RETARGETING",
        "Rules": [{"Operator": "ALL", "Arguments": [
            {"MembershipLifeSpan": 540, "ExternalId": seg_id}
        ]}],
    }]})
    return res["AddResults"][0]["Id"], True


def ensure_campaign():
    res = call("campaigns", "get", {"SelectionCriteria": {}, "FieldNames": ["Id", "Name"]})
    for c in res.get("Campaigns", []):
        if c.get("Name") == CAMPAIGN_NAME:
            return c["Id"], False
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
    cid = res["AddResults"][0]["Id"]
    call("campaigns", "suspend", {"SelectionCriteria": {"Ids": [cid]}})
    return cid, True


def ensure_adgroup(campaign_id, name):
    res = call("adgroups", "get", {"SelectionCriteria": {"CampaignIds": [campaign_id]},
                                   "FieldNames": ["Id", "Name"]})
    for g in res.get("AdGroups", []):
        if g.get("Name") == name:
            return g["Id"], False
    res = call("adgroups", "add", {"AdGroups": [{
        "Name": name, "CampaignId": campaign_id, "RegionIds": [REGION_RUSSIA],
    }]})
    return res["AddResults"][0]["Id"], True


def ensure_ad(adgroup_id, sitelink_id):
    res = call("ads", "get", {"SelectionCriteria": {"AdGroupIds": [adgroup_id]},
                              "FieldNames": ["Id"]})
    if res.get("Ads"):
        return res["Ads"][0]["Id"], False
    res = call("ads", "add", {"Ads": [{
        "AdGroupId": adgroup_id,
        "TextAd": {
            "Title": AD["Title"], "Title2": AD["Title2"], "Text": AD["Text"],
            "Mobile": "NO", "Href": LANDING + UTM,
            "AdImageHash": AD["AdImageHash"], "SitelinkSetId": sitelink_id,
        },
    }]})
    return res["AddResults"][0]["Id"], True


def state():
    narrow = segment(SEGMENT_NARROW) or {}
    wide = segment(SEGMENT_WIDE) or {}
    lal = find_lookalike() or {}

    def line(title, s):
        st = s.get("status", "нет")
        m = s.get("matched_quantity")
        tail = f"сматчено {m}" if m is not None else "совпадения считаются"
        return f"  {title:36} {st:14} {tail}"

    print("СОСТОЯНИЕ СЕГМЕНТОВ")
    print(line(f"узкий (Промэнергетика) {SEGMENT_NARROW}", narrow))
    print(line(f"широкий (вся группа) {SEGMENT_WIDE}", wide))
    print(line(f"look-alike {lal.get('id', '—')}", lal))
    return narrow, wide, lal


def apply():
    narrow, wide, lal = state()
    print()

    if wide.get("status") == "processed" and not lal:
        print("создаю look-alike по широкой базе…")
        lal = create_lookalike()
        print(f"   сегмент {lal.get('id')}, точность {LOOKALIKE_VALUE} из 5")
    elif not lal:
        print("look-alike ждёт: широкий сегмент ещё обрабатывается")

    ready = {"narrow": narrow.get("status") == "processed",
             "lookalike": lal.get("status") == "processed"}
    seg_ids = {"narrow": SEGMENT_NARROW, "lookalike": lal.get("id")}

    if not any(ready.values()):
        print("\nНи один сегмент не готов — Директ их пока не видит (ошибка 8800).")
        print("Запустите позже: всё созданное переиспользуется.")
        return

    sitelink_id, made = ensure_sitelinks()
    print(f"быстрые ссылки: {sitelink_id}{' — создан' if made else ''}")
    campaign_id, made = ensure_campaign()
    print(f"кампания: {campaign_id}{' — создана и остановлена' if made else ''}")

    for group_name, list_name, kind in GROUPS:
        if not ready.get(kind):
            print(f"группа «{group_name}»: пропущена, сегмент не готов")
            continue
        description = ("Хеши MD5, выгрузка из Битрикс24, отделы 23/25/47"
                       if kind == "narrow" else f"Look-alike по сегменту {SEGMENT_WIDE}")
        rid, _ = ensure_retargeting_list(list_name, seg_ids[kind], description)
        gid, made_g = ensure_adgroup(campaign_id, group_name)
        if made_g:
            call("audiencetargets", "add", {"AudienceTargets": [
                {"AdGroupId": gid, "RetargetingListId": rid}
            ]})
        ad_id, made_a = ensure_ad(gid, sitelink_id)
        print(f"группа «{group_name}»: {gid}, условие {rid}, "
              f"объявление {ad_id}{' — создано' if made_a else ''}")

    print(f"\nhttps://direct.yandex.ru/dna/grid/campaign?cid={campaign_id}")
    print("Кампания остановлена — запускать руками после проверки.")


if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("--apply", action="store_true", help="собрать то, что уже возможно")
    a = ap.parse_args()
    if a.apply:
        apply()
    else:
        state()
        print(f"\nбюджет {WEEKLY_BUDGET} ₽/нед · поиск выключен · посадочная {LANDING}")
        print("для сборки: --apply")
