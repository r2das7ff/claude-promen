# -*- coding: utf-8 -*-
"""Чистка по итогам первых пяти дней (10–14.09.2026).

Что делает, по решению заказчика от 14.09:

* дописывает в общий стоп-лист чужие материалы и информационные запросы,
  которые приводил автотаргетинг; старым кампаниям (ТГО, «Стандарты») те
  же слова — в минус-слова кампании: сам стоп-лист к ним не подключить,
  в нём «прайс», а у них десятки фраз «… прайс»;
* останавливает ретаргетинг по клиентам: 85% расхода — клики в мобильных
  играх и на биржах, ни одной заявки;
* мобильная корректировка −30% в «Переходах» и «Тройниках» — отказы
  с телефонов 73% и 69% против 37% и 39% с компьютеров;
* останавливает три мусорные фразы «Стандартов» (штуцеры для гибкой
  подводки, пробковое дерево, пенопласт). Остановка, а не удаление, —
  возвращается одной командой.

«Хомут» в стоп-лист не идёт: в каталоге 12 опор хомутовых ОСТ 36-17-85.
Каждое слово сверено с каталогом на проде — совпадений 0.

    python scripts/ads/direct_cleanup_0914.py          # показать план
    python scripts/ads/direct_cleanup_0914.py apply
"""
import json
import os
import re
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))


def env(key):
    with open(os.path.join(ROOT, ".env"), encoding="utf-8") as f:
        for line in f:
            if line.strip().startswith(key + "="):
                return line.split("=", 1)[1].strip().strip("\"'")


H = {"Authorization": f"Bearer {env('YANDEX_DIRECT_TOKEN')}", "Client-Login": env("YANDEX_DIRECT_LOGIN"),
     "Accept-Language": "ru"}


def call(service, method, params, version="v5"):
    d = requests.post(f"https://api.direct.yandex.com/json/{version}/{service}", headers=H,
                      json={"method": method, "params": params}, timeout=180).json()
    if "error" in d:
        sys.exit(f"{service}.{method}: {d['error']}")
    return d["result"]


def errors(result, key):
    out = []
    for item in result.get(key, []):
        for e in item.get("Errors", []) or []:
            out.append(f'{e["Code"]} {e["Message"]} {e.get("Details", "")}')
    return out


SHARED_SET = 76097012
NEW_NEGATIVES = [
    # чужие материалы
    "полипропилен", "пропиленовый", "пвх", "rehau", "рехау", "чугун", "чугунный", "вчшг",
    "медь", "медный", "металлопластиковый", "резиновый", "пенополистирол",
    # чужое назначение и бытовая сантехника
    "вентиляция", "воздуховод", "клипса", "евроконус", "футорка", "сальник", "подводка",
    "елочка", "дренаж",
    # информационные запросы
    "как посчитать", "принцип действия",
]
OLD_CAMPAIGNS = [109052494, 709197084, 709723361]
RETARGETING = 714267536
MOBILE = {714272489: 70, 714272480: 70}  # Переходы, Тройники: было 85
JUNK_PHRASES = ["штуцеры трубопроводов -гост -купить", "производство пробок",
                "заглушки производство -атк -!ту -поворотный"]
STANDARDS = [709197084, 709723361]


def words(phrase):
    return {w for w in re.split(r"[\s!+\-]+", phrase.lower()) if w}


def main():
    apply = len(sys.argv) > 1 and sys.argv[1] == "apply"
    before = {}

    # 1. Стоп-лист
    ss = call("negativekeywordsharedsets", "get", {"SelectionCriteria": {"Ids": [SHARED_SET]},
              "FieldNames": ["Id", "Name", "NegativeKeywords"]})["NegativeKeywordSharedSets"][0]
    current = ss["NegativeKeywords"]
    before["shared_set"] = current
    add = [w for w in NEW_NEGATIVES if w not in current]
    merged = current + add
    print(f"стоп-лист {SHARED_SET}: было {len(current)}, добавляю {len(add)}: {', '.join(add)}")

    # Конфликты стоп-листа с фразами всех кампаний, которые будут к нему подключены
    camps = call("campaigns", "get", {"SelectionCriteria": {"Ids": OLD_CAMPAIGNS + list(MOBILE) + [714275030, 714272511, 714272467, 714272498]},
                 "FieldNames": ["Id", "Name"], "UnifiedCampaignFieldNames": ["NegativeKeywordSharedSetIds"]}, "v501")["Campaigns"]
    kws = call("keywords", "get", {"SelectionCriteria": {"CampaignIds": [c["Id"] for c in camps], "States": ["ON"]},
               "FieldNames": ["Id", "CampaignId", "Keyword"]}).get("Keywords", [])
    neg_single = {w for w in merged if " " not in w.replace("!", "")}
    conflicts = {}
    for k in kws:
        if k["Keyword"].startswith("---"):
            continue
        base = k["Keyword"].split(" -")[0]
        hit = words(base) & neg_single
        if hit:
            conflicts.setdefault(k["CampaignId"], []).append((k["Keyword"], sorted(hit)))
    for cid, items in conflicts.items():
        print(f"  конфликт в {cid}: {len(items)} фраз, например {items[:3]}")
    # Старые кампании к стоп-листу не подключаем: в нём «прайс», а у ТГО и
    # «Стандартов» по 60–100 фраз вида «труба … прайс» — они бы замолчали.
    # Им дописываем только новые слова, на уровне кампании, и только если
    # эти новые слова не пересекаются с их фразами.
    new_single = {w for w in add if " " not in w}
    old_negs = {c["Id"]: (c.get("NegativeKeywords") or {}).get("Items") or []
                for c in call("campaigns", "get", {"SelectionCriteria": {"Ids": OLD_CAMPAIGNS},
                              "FieldNames": ["Id", "NegativeKeywords"]})["Campaigns"]}
    before["old_campaign_negatives"] = old_negs
    extend = {}
    for cid in OLD_CAMPAIGNS:
        hits = [(k["Keyword"], sorted(words(k["Keyword"].split(" -")[0]) & new_single))
                for k in kws if k["CampaignId"] == cid and not k["Keyword"].startswith("---")
                and words(k["Keyword"].split(" -")[0]) & new_single]
        if hits:
            print(f"  {cid}: новые слова конфликтуют с фразами {hits[:3]} — пропускаю")
            continue
        extend[cid] = old_negs[cid] + [w for w in NEW_NEGATIVES if w not in old_negs[cid]]
        print(f"минус-слова кампании {cid}: {len(old_negs[cid])} → {len(extend[cid])}")

    # 3. Мобильные корректировки
    bm = call("bidmodifiers", "get", {"SelectionCriteria": {"CampaignIds": list(MOBILE), "Levels": ["CAMPAIGN"], "Types": ["MOBILE_ADJUSTMENT"]},
              "FieldNames": ["Id", "CampaignId"], "MobileAdjustmentFieldNames": ["BidModifier"]})["BidModifiers"]
    before["mobile"] = bm
    for b in bm:
        print(f"мобильная корректировка {b['CampaignId']}: {b['MobileAdjustment']['BidModifier']} → {MOBILE[b['CampaignId']]}")

    # 4. Мусорные фразы
    junk = [k for k in call("keywords", "get", {"SelectionCriteria": {"CampaignIds": STANDARDS},
            "FieldNames": ["Id", "CampaignId", "Keyword", "State"]}).get("Keywords", [])
            if k["Keyword"] in JUNK_PHRASES and k["State"] == "ON"]
    before["junk"] = junk
    for k in junk:
        print(f"останавливаю фразу {k['CampaignId']} {k['Id']}: {k['Keyword']}")
    print(f"останавливаю кампанию {RETARGETING} (ретаргетинг по клиентам)")

    if not apply:
        print("\nплан показан; применить: apply")
        return

    dest = os.path.join(ROOT, "perf-reports", "ads", "2026-09-14", "cleanup-before.json")
    json.dump(before, open(dest, "w", encoding="utf-8"), ensure_ascii=False, indent=1)

    r = call("negativekeywordsharedsets", "update", {"NegativeKeywordSharedSets": [{"Id": SHARED_SET, "NegativeKeywords": merged}]})
    print("стоп-лист:", errors(r, "UpdateResults") or "ок")
    for cid, items in extend.items():
        r = call("campaigns", "update", {"Campaigns": [{"Id": cid, "NegativeKeywords": {"Items": items}}]}, "v501")
        print(f"минус-слова {cid}:", errors(r, "UpdateResults") or "ок")
    if bm:
        r = call("bidmodifiers", "set", {"BidModifiers": [{"Id": b["Id"], "BidModifier": MOBILE[b["CampaignId"]]} for b in bm]})
        print("корректировки:", errors(r, "SetResults") or "ок")
    if junk:
        r = call("keywords", "suspend", {"SelectionCriteria": {"Ids": [k["Id"] for k in junk]}})
        print("фразы:", errors(r, "SuspendResults") or "ок")
    r = call("campaigns", "suspend", {"SelectionCriteria": {"Ids": [RETARGETING]}})
    print("ретаргетинг:", errors(r, "SuspendResults") or "ок")

    # Проверка чтением
    print("\n--- проверка ---")
    ss = call("negativekeywordsharedsets", "get", {"SelectionCriteria": {"Ids": [SHARED_SET]}, "FieldNames": ["NegativeKeywords"]})["NegativeKeywordSharedSets"][0]
    print(f"слов в стоп-листе: {len(ss['NegativeKeywords'])}")
    for c in call("campaigns", "get", {"SelectionCriteria": {"Ids": OLD_CAMPAIGNS + [RETARGETING]}, "FieldNames": ["Id", "Name", "State", "NegativeKeywords"]}, "v501")["Campaigns"]:
        items = (c.get("NegativeKeywords") or {}).get("Items") or []
        norm = {w.replace("!", "") for w in items}
        missing = [w for w in NEW_NEGATIVES if w not in norm] if c["Id"] in extend else []
        print(f"  {c['Id']} {c['State']:9} минус-слов {len(items)}"
              + (f", не нашёл после записи: {missing}" if missing else "") + f"  {c['Name'][:40]}")
    for b in call("bidmodifiers", "get", {"SelectionCriteria": {"CampaignIds": list(MOBILE), "Levels": ["CAMPAIGN"], "Types": ["MOBILE_ADJUSTMENT"]},
                  "FieldNames": ["CampaignId"], "MobileAdjustmentFieldNames": ["BidModifier"]})["BidModifiers"]:
        print(f"  мобильная {b['CampaignId']}: {b['MobileAdjustment']['BidModifier']}")
    if junk:
        for k in call("keywords", "get", {"SelectionCriteria": {"Ids": [k["Id"] for k in junk]}, "FieldNames": ["Id", "State", "Keyword"]})["Keywords"]:
            print(f"  фраза {k['Id']} {k['State']}: {k['Keyword']}")


if __name__ == "__main__":
    main()
