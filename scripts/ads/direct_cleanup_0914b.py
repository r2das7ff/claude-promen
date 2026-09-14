# -*- coding: utf-8 -*-
"""Вторая чистка 14.09.2026: старые кампании и планшеты в РСЯ.

По решению заказчика:

1. ТГО, группа «Купить трубу» — автотаргетинг только по целевым запросам.
   Это единственная группа кампании, где были открыты «альтернативные»,
   «конкуренты» и «сопутствующие»: она дала 28 из 30 кликов (650 из
   700 ₽) на справочные запросы — сортамент, прочность, металлокалькулятор.
2. ТГО — минус-слова «сортамент», «калькулятор», «металлокалькулятор»,
   «прочность», «тоннаж». В товарах и терминах каталога их нет; страницы
   калькуляторов есть, но объявления ТГО туда не ведут.
3. «Стандарты», группа «Штуцеры» в обеих кампаниях — автотаргетинг
   остановлен: одиночное «штуцер» приводит сантехников. Фразы по ОСТ и СТО
   на штуцеры остаются.
4. «Детали РСЯ» — планшеты −50% (93% кликов кампании, отказы 69%).
   Телефоны там уже −50%, не трогаем.

    python scripts/ads/direct_cleanup_0914b.py          # план
    python scripts/ads/direct_cleanup_0914b.py apply
"""
import json
import os
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
    return [f'{e["Code"]} {e["Message"]} {e.get("Details", "")}'
            for item in result.get(key, []) for e in (item.get("Errors") or [])]


TGO = 109052494
TGO_AUTOTARGETING = 51016691579          # группа «Купить трубу»
TGO_NEGATIVES = ["сортамент", "калькулятор", "металлокалькулятор", "прочность", "тоннаж"]
FITTINGS_AUTOTARGETING = [205743859554, 205748937501]   # «Штуцеры» в обоих «Стандартах»
RSYA = 705559963
TABLET = 50
EXACT_ONLY = [{"Category": c, "Value": "YES" if c == "EXACT" else "NO"}
              for c in ("EXACT", "ALTERNATIVE", "COMPETITOR", "BROADER", "ACCESSORY")]


def state():
    kw = call("keywords", "get", {"SelectionCriteria": {"Ids": [TGO_AUTOTARGETING] + FITTINGS_AUTOTARGETING},
              "FieldNames": ["Id", "CampaignId", "State", "AutotargetingCategories"]})["Keywords"]
    neg = call("campaigns", "get", {"SelectionCriteria": {"Ids": [TGO]}, "FieldNames": ["NegativeKeywords"]})["Campaigns"][0]
    bm = call("bidmodifiers", "get", {"SelectionCriteria": {"CampaignIds": [RSYA], "Levels": ["CAMPAIGN"],
              "Types": ["MOBILE_ADJUSTMENT", "TABLET_ADJUSTMENT"]}, "FieldNames": ["Id", "Type"],
              "MobileAdjustmentFieldNames": ["BidModifier"], "TabletAdjustmentFieldNames": ["BidModifier"]}).get("BidModifiers", [])
    return {"keywords": kw, "tgo_negatives": (neg.get("NegativeKeywords") or {}).get("Items") or [], "rsya_modifiers": bm}


def show(s):
    for k in s["keywords"]:
        cats = ", ".join(f'{x["Category"]}={x["Value"]}' for x in k["AutotargetingCategories"]["Items"])
        print(f'  автотаргетинг {k["CampaignId"]} {k["Id"]}: {k["State"]} | {cats}')
    print(f'  минус-слов ТГО: {len(s["tgo_negatives"])}, из новых есть: '
          f'{[w for w in TGO_NEGATIVES if w in s["tgo_negatives"]]}')
    for b in s["rsya_modifiers"]:
        adj = b.get("MobileAdjustment") or b.get("TabletAdjustment")
        print(f'  РСЯ {b["Type"]}: {adj["BidModifier"]}')


def main():
    before = state()
    print("сейчас:")
    show(before)
    if len(sys.argv) < 2 or sys.argv[1] != "apply":
        print("\nприменить: apply")
        return
    json.dump(before, open(os.path.join(ROOT, "perf-reports", "ads", "2026-09-14", "cleanup-b-before.json"), "w",
                           encoding="utf-8"), ensure_ascii=False, indent=1)

    r = call("keywords", "update", {"Keywords": [{"Id": TGO_AUTOTARGETING, "AutotargetingCategories": EXACT_ONLY}]})
    print("1. автотаргетинг ТГО:", errors(r, "UpdateResults") or "ок")

    items = before["tgo_negatives"] + [w for w in TGO_NEGATIVES if w not in before["tgo_negatives"]]
    r = call("campaigns", "update", {"Campaigns": [{"Id": TGO, "NegativeKeywords": {"Items": items}}]}, "v501")
    print("2. минус-слова ТГО:", errors(r, "UpdateResults") or "ок")

    r = call("keywords", "suspend", {"SelectionCriteria": {"Ids": FITTINGS_AUTOTARGETING}})
    print("3. автотаргетинг «Штуцеров»:", errors(r, "SuspendResults") or "ок")

    tablet = [b for b in before["rsya_modifiers"] if b["Type"] == "TABLET_ADJUSTMENT"]
    if tablet:
        r = call("bidmodifiers", "set", {"BidModifiers": [{"Id": tablet[0]["Id"], "BidModifier": TABLET}]})
        print("4. планшеты РСЯ:", errors(r, "SetResults") or "ок")
    else:
        r = call("bidmodifiers", "add", {"BidModifiers": [{"CampaignId": RSYA, "TabletAdjustment": {"BidModifier": TABLET}}]})
        print("4. планшеты РСЯ:", errors(r, "AddResults") or "ок")

    print("\nпосле:")
    show(state())


if __name__ == "__main__":
    main()
