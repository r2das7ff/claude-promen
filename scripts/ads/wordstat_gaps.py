# -*- coding: utf-8 -*-
"""Wordstat: каких запросов не хватает в рекламе.

Берёт затравки по ассортименту, собирает через Wordstat API (Yandex Cloud
Search API v2, метод topRequests) похожие запросы с частотой за 30 дней и
сверяет их с тем, что уже крутится в Директе:

* **покрыт** — есть активная фраза, все слова которой входят в запрос
  (широкое соответствие его и так поймает);
* **заминусован** — в запросе есть минус-слово из стоп-листа или кампании;
* **не наш** — фланцы, крепёж, изоляция, арматура (решение заказчика:
  пока не рекламируем) или вовсе не изделие;
* **пробел** — всё остальное. Это кандидаты, но не готовые фразы: каждый
  перед добавлением сверять с каталогом (есть ли такой ГОСТ, марка, размер).

Частота Wordstat — широкая: «отвод 90» включает все запросы с этими
словами. Сравнивать кандидатов между собой можно, складывать — нет.

Доступ (один раз, в AI Studio / консоли Yandex Cloud):
сервисный аккаунт с ролью `search-api.webSearch.user`, API-ключ со
scope `yc.search-api.execute`. В `.env`:

    YANDEX_SEARCH_API_KEY=...
    YANDEX_CLOUD_FOLDER_ID=...

    python scripts/ads/wordstat_gaps.py --check          # проверить ключ
    python scripts/ads/wordstat_gaps.py                  # полный сбор
    python scripts/ads/wordstat_gaps.py --seeds "отвод 12х1мф" "тройник для аэс"
"""
import argparse
import csv
import datetime as dt
import json
import os
import re
import sys
import time

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
WORDSTAT = "https://searchapi.api.cloud.yandex.net/v2/wordstat/topRequests"
DIRECT = "https://api.direct.yandex.com/json/v5"


def env(key):
    if os.environ.get(key):
        return os.environ[key]
    with open(os.path.join(ROOT, ".env"), encoding="utf-8") as f:
        for line in f:
            if line.strip().startswith(key + "="):
                return line.split("=", 1)[1].strip().strip("\"'")
    return ""


# Изделия, которые рекламируем, → кампания, куда пойдёт найденный запрос.
PRODUCTS = {
    "отвод": "Отводы", "колен": "Отводы", "полуотвод": "Отводы",
    "тройник": "Тройники",
    "переход": "Переходы",
    "опор": "Опоры", "подвеск": "Опоры",
    "труб": "Трубы",
    "заглушк": "АЭС поиск", "днищ": "АЭС поиск", "штуцер": "АЭС поиск", "бобышк": "АЭС поиск",
    "пробк": "АЭС поиск", "угольник": "АЭС поиск", "точен": "АЭС поиск",
    "детал": "АЭС поиск", "сдт": "АЭС поиск",
}
# Пока не рекламируем (решение заказчика) или вовсе не наше.
NOT_OURS = ["фланц", "фланец", "болт", "гайк", "шпильк", "шайб", "крепеж", "изоляц", "ппу", "задвижк",
            "кран", "клапан", "вентил", "затвор", "арматур", "профильн", "полипропилен", "пвх", "пнд",
            "металлопласт", "медн", "латун", "чугун", "канализ", "вентиляц", "сантехн", "гофр"]

# Затравки: изделие × признак, по которому ищут промышленные детали.
TYPES = ["отвод", "тройник", "переход", "заглушка", "днище", "опора трубопровода", "штуцер", "бобышка",
         "труба бесшовная", "труба электросварная"]
MARKERS = ["для аэс", "для тэс", "ост 34", "ост 108", "ост 24.125", "сто цкти", "высокого давления",
           "12х1мф", "15гс", "09г2с", "08х18н10т", "20", "производитель", "завод"]
EXTRA = ["детали трубопроводов аэс", "детали трубопроводов тэс", "детали трубопроводов высокого давления",
         "соединительные детали трубопроводов", "гнутые отводы", "крутоизогнутые отводы гост 17375",
         "тройники гост 17376", "переходы гост 17378", "заглушки гост 17379", "отводы гост 30753",
         "гост 22790", "детали трубопроводов гост 22790", "опоры ост 36-146", "опоры скользящие",
         "опоры неподвижные", "опоры пружинные", "подвески трубопроводов", "трубы для котлов",
         "трубы гост 8731", "трубы гост 8732", "трубы гост 10704", "трубы гост 10705",
         "трубы гост 550", "трубы 12х1мф", "трубы 15гс", "трубы 09г2с", "трубы вгп"]

STOP = {"для", "на", "в", "во", "и", "с", "со", "по", "из", "от", "до", "к", "под", "а", "или", "the"}


def stem(word):
    """Грубая основа: первые пять букв без конечной гласной — «отводы» и
    «отвод», «трубы» и «труба» совпадают. Числа и размеры не трогаем."""
    word = word.lower().replace("ё", "е")
    if re.search(r"\d", word):
        return word
    return word[:5].rstrip("аеиоуыэюяйь") or word


def words(phrase):
    # Минус-операторы фразы отрезаем, дефис в номерах ГОСТ режем на части —
    # так же Директ хранит «17375-2001» как «17375 2001».
    clean = re.sub(r"[\"\[\]!+()]", " ", phrase.split(" -")[0].lower())
    return {stem(w) for w in re.split(r"[\s,\-]+", clean) if w and w not in STOP}


def wordstat(phrase, key, folder, num):
    body = {"phrase": phrase, "numPhrases": num, "folderId": folder, "devices": ["DEVICE_ALL"]}
    for attempt in range(5):
        r = requests.post(WORDSTAT, headers={"Authorization": f"Api-Key {key}"}, json=body, timeout=60)
        if r.status_code == 429:
            time.sleep(2 + attempt * 2)
            continue
        if r.status_code != 200:
            sys.exit(f"Wordstat {r.status_code}: {r.text[:300]}")
        d = r.json()
        rows = [(x["phrase"], int(x["count"])) for x in d.get("results") or []]
        rows += [(x["phrase"], int(x["count"])) for x in d.get("associations") or []]
        return int(d.get("totalCount") or 0), rows
    sys.exit("Wordstat: 429 пять раз подряд")


def direct(service, method, params):
    h = {"Authorization": f"Bearer {env('YANDEX_DIRECT_TOKEN')}", "Client-Login": env("YANDEX_DIRECT_LOGIN"),
         "Accept-Language": "ru"}
    d = requests.post(f"{DIRECT}/{service}", headers=h, json={"method": method, "params": params}, timeout=180).json()
    if "error" in d:
        sys.exit(f"{service}.{method}: {d['error']}")
    return d["result"]


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--check", action="store_true", help="один запрос — проверить ключ и папку")
    ap.add_argument("--seeds", nargs="*", help="свои затравки вместо встроенных")
    ap.add_argument("--num", type=int, default=300, help="сколько запросов брать на затравку (до 2000)")
    ap.add_argument("--min-count", type=int, default=10, help="отсечь редкие запросы")
    a = ap.parse_args()

    key, folder = env("YANDEX_SEARCH_API_KEY"), env("YANDEX_CLOUD_FOLDER_ID")
    if not key or not folder:
        sys.exit("Нужны YANDEX_SEARCH_API_KEY и YANDEX_CLOUD_FOLDER_ID в .env — см. шапку скрипта")

    if a.check:
        total, rows = wordstat("отвод крутоизогнутый", key, folder, 5)
        print(f"ключ работает: «отвод крутоизогнутый» — {total} показов за 30 дней")
        for p, c in rows:
            print(f"  {c:>7}  {p}")
        return

    seeds = a.seeds or ([f"{t} {m}" for t in TYPES for m in MARKERS] + EXTRA)
    print(f"затравок: {len(seeds)}")

    # Что уже крутится: активные фразы и минус-слова всех работающих кампаний.
    camps = direct("campaigns", "get", {"SelectionCriteria": {"States": ["ON"]},
                                        "FieldNames": ["Id", "Name", "NegativeKeywords"]})["Campaigns"]
    ids = [c["Id"] for c in camps]
    kws = direct("keywords", "get", {"SelectionCriteria": {"CampaignIds": ids, "States": ["ON"]},
                                     "FieldNames": ["Keyword", "CampaignId"]}).get("Keywords", [])
    have = [words(k["Keyword"]) for k in kws if not k["Keyword"].startswith("---")]
    have = [w for w in have if w]
    neg = set()
    for c in camps:
        for n in (c.get("NegativeKeywords") or {}).get("Items") or []:
            if " " not in n.strip():
                neg |= words(n)
    for s in direct("negativekeywordsharedsets", "get", {"SelectionCriteria": {},
                    "FieldNames": ["NegativeKeywords"]}).get("NegativeKeywordSharedSets", []):
        for n in s["NegativeKeywords"]:
            if " " not in n.strip():
                neg |= words(n)
    print(f"работающих кампаний {len(camps)}, активных фраз {len(have)}, однословных минус-слов {len(neg)}")

    found = {}
    for i, seed in enumerate(seeds, 1):
        total, rows = wordstat(seed, key, folder, a.num)
        for phrase, count in rows:
            if count >= a.min_count and count > found.get(phrase, (0, ""))[0]:
                found[phrase] = (count, seed)
        print(f"  [{i}/{len(seeds)}] {seed}: {total}", flush=True)
        time.sleep(0.25)

    out, stats = [], {"покрыт": 0, "заминусован": 0, "не наш": 0, "пробел": 0}
    for phrase, (count, seed) in found.items():
        low = phrase.lower().replace("ё", "е")
        w = words(phrase)
        product = next((camp for stem_, camp in PRODUCTS.items() if stem_ in low), None)
        if not product or any(x in low for x in NOT_OURS):
            status = "не наш"
        elif w & neg:
            status = "заминусован"
        elif any(k <= w for k in have):
            status = "покрыт"
        else:
            status = "пробел"
        stats[status] += 1
        out.append({"phrase": phrase, "count": count, "status": status, "campaign": product or "", "seed": seed})

    day = dt.date.today().isoformat()
    dest_dir = os.path.join(ROOT, "perf-reports", "ads", day)
    os.makedirs(dest_dir, exist_ok=True)
    dest = os.path.join(dest_dir, "wordstat-gaps.csv")
    out.sort(key=lambda r: (r["status"] != "пробел", r["campaign"], -r["count"]))
    with open(dest, "w", encoding="utf-8-sig", newline="") as f:
        wr = csv.DictWriter(f, fieldnames=["status", "campaign", "count", "phrase", "seed"])
        wr.writeheader()
        wr.writerows(out)

    print(f"\nуникальных запросов: {len(found)} — " + ", ".join(f"{k} {v}" for k, v in stats.items()))
    gaps = [r for r in out if r["status"] == "пробел"]
    for camp in sorted({r["campaign"] for r in gaps}):
        rows = [r for r in gaps if r["campaign"] == camp][:25]
        print(f"\n{camp} — пробелов {sum(1 for r in gaps if r['campaign'] == camp)}, топ:")
        for r in rows:
            print(f"  {r['count']:>6}  {r['phrase']}")
    print(f"\nфайл: {os.path.relpath(dest, ROOT)}")


if __name__ == "__main__":
    main()
