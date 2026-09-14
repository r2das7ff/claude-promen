# -*- coding: utf-8 -*-
"""Wordstat: каких запросов не хватает в рекламе.

Берёт затравки по ассортименту, собирает через Wordstat API (Yandex Cloud
Search API v2, метод topRequests) похожие запросы с частотой за 30 дней и
сверяет их с тем, что крутится в Директе, **по каждой кампании отдельно**:

* **не наш** — нет нашего изделия, бытовое (пластик, резьба в дюймах,
  шланги) или то, что пока не рекламируем (фланцы, крепёж, изоляция,
  арматура);
* **нет в каталоге** — названный в запросе ГОСТ или ОСТ не встречается
  в фасетах каталога (например, опоры ОСТ 36-146-88 — у нас только 36-17-85);
* **покрыт** — есть фраза, все слова которой входят в запрос, и ни её
  минусы, ни минус-слова кампании (с учётом общих наборов) его не режут;
* **отрезан минусами кампании** — фраза есть, но кампания запрос отсекает;
* **общий стоп-лист** — фразы нет, и запрос режет общий набор минус-слов;
* **пробел** — промышленный запрос по нашему изделию, который никто не
  ловит. Кандидаты, а не готовые фразы.

Правило Директа, без которого сверка врёт: минус-слово не применяется
к фразе, в которой есть это же слово. Поэтому «17375» в минусах «Отводов»
не глушит фразу «отводы гост 17375 2001», но отрезает запрос «отвод
крутоизогнутый гост 17375» от общих фраз.

Частота Wordstat — широкая: «отвод 90» включает все запросы с этими
словами. Сравнивать кандидатов между собой можно, складывать — нет.

Доступ: сервисный аккаунт с ролью `search-api.webSearch.user`, API-ключ со
scope `yc.search-api.execute`. В `.env`:

    YANDEX_CLOUD_API_KEY=...        # или YANDEX_SEARCH_API_KEY
    YANDEX_CLOUD_FOLDER_ID=...

    python scripts/ads/wordstat_gaps.py --check          # проверить ключ
    python scripts/ads/wordstat_gaps.py                  # сбор (сырые ответы кешируются на день)
    python scripts/ads/wordstat_gaps.py --refresh        # игнорировать кеш
    python scripts/ads/wordstat_gaps.py --seeds "отвод 12х1мф" "тройник для аэс"
"""
import argparse
import collections
import csv
import datetime as dt
import glob
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


def env(key):
    if os.environ.get(key):
        return os.environ[key]
    with open(os.path.join(ROOT, ".env"), encoding="utf-8") as f:
        for line in f:
            if line.strip().startswith(key + "="):
                return line.split("=", 1)[1].strip().strip("\"'")
    return ""


# Затравки: изделие × признак, по которому ищут промышленные детали.
# Голую марку «20» не берём: Wordstat понимает её как диаметр 20 мм и
# приносит полипропилен — только «сталь 20».
TYPES = ["отвод", "тройник", "переход", "заглушка", "днище", "опора трубопровода", "штуцер", "бобышка",
         "труба бесшовная", "труба электросварная"]
MARKERS = ["для аэс", "для тэс", "ост 34", "ост 108", "ост 24.125", "сто цкти", "высокого давления",
           "12х1мф", "15гс", "09г2с", "08х18н10т", "сталь 20", "производитель", "завод"]
EXTRA = ["детали трубопроводов аэс", "детали трубопроводов тэс", "детали трубопроводов высокого давления",
         "соединительные детали трубопроводов", "гнутые отводы", "крутоизогнутые отводы гост 17375",
         "тройники гост 17376", "переходы гост 17378", "заглушки гост 17379", "отводы гост 30753",
         "гост 22790", "детали трубопроводов гост 22790", "опоры ост 36-146", "опоры скользящие",
         "опоры неподвижные", "опоры пружинные", "подвески трубопроводов", "трубы для котлов",
         "трубы гост 8731", "трубы гост 8732", "трубы гост 10704", "трубы гост 10705",
         "трубы гост 550", "трубы 12х1мф", "трубы 15гс", "трубы 09г2с", "трубы вгп"]

# Кампания → разделы каталога, чьи ГОСТы она продаёт (для проверки минусов).
CAMPAIGN_SECTIONS = {
    "Отводы": ["/catalog/sdt/otvody/"], "Тройники": ["/catalog/sdt/troyniki/"],
    "Переходы": ["/catalog/sdt/perekhody/"], "Опоры": ["/catalog/opory/"], "Трубы": ["/catalog/truby/"],
    "Детали для АЭС и ТЭС": ["/catalog/sdt/", "/catalog/truby/", "/catalog/opory/", "/catalog/tochenye/"],
}

STOP = {"для", "на", "в", "во", "и", "с", "со", "по", "из", "от", "до", "к", "под", "а", "или", "как", "что", "это"}

# Промышленный признак — без него запрос в пробелы не попадает.
INDUSTRIAL = re.compile(
    r"\b(гост|ост|сто|ту|тс|серия|ду\s?\d|dn|pn|ру\s?\d|\d+\s?[хx*]\s?\d|стал|ст\s?\d|09г2с|12х1мф|15гс|"
    r"08х18н10т|12х18н10т|17г1с|13хфа|10г2|бесшовн|электросвар|приварн|крутоизогн|штампосвар|сварн|"
    r"эллиптич|концентрич|эксцентрич|равнопроходн|высокого давления|аэс|тэс|тэц|котл|энергет|атомн|"
    r"производ|завод|изготов|поставщ|купить|цена|стоимост|оптом|скользящ|неподвижн|пружинн|хомутов|"
    r"катков|корпусн|трубопровод)")
# Бытовое и чужое.
HOUSEHOLD = re.compile(
    r"(\bпп\b|ппр|\bpp\b|ppr|\bпэ\b|пнд|пвх|полипроп|металлопласт|латун|медн|резьб|дюйм|\b\d \d\b|"
    r"американ|вилк|шланг|тормоз|гидравл|манометр|мойк|канализ|водосточ|дымоход|сантех|душ|унитаз|"
    r"раковин|гофр|профильн|профилир|квадрат|прямоуг|стропил|прицеп|амортиз|подшипник|двигательн|"
    r"геншин|ярославл|мкад|фильм|газоотвод|пешеход|сложени|вычитани|таблиц|термопаст|ндс|этрн|"
    r"фланц|фланец|болт|гайк|шпильк|шайб|крепеж|изоляц|ппу|задвижк|\bкран|клапан|вентил|затвор|арматур|"
    r"чугун|пластик|резинов|оцинк|своими руками|что такое|\bэто\b|виды|сварка|испытани|монтаж|"
    r"тарков|таможн|лаборатор|кировск|метро|керхер|karcher|"
    r"(?<!\d)(?P<side>\d+)\s?[хx*]\s?(?P=side)(?!\d)|"   # профильные трубы 100х100х3, 20х20
    r"\b(1[0-9]|2[0-9]|3[0-2])\s?мм\b)")


def tokens(phrase):
    """Слова фразы без операторов и минусов. Дефис режем: «17375-2001» — две
    части, так же Директ хранит минус-слово."""
    clean = re.sub(r"[\"\[\]!+()]", " ", phrase.split(" -")[0].lower().replace("ё", "е"))
    return [w for w in re.split(r"[\s,/\-]+", clean) if w and w not in STOP]


def same(a, b):
    """Одно ли слово в разных формах: числа и короткие — только точно;
    остальные — общее начало не короче 4 букв и отличие не больше трёх
    последних. «отводы/отвод», «стальная/стальную» — да; «подводка/подвеска»,
    «труба/трубопровод» — нет."""
    if a == b:
        return True
    if re.search(r"\d", a + b) or len(a) < 4 or len(b) < 4:
        return False
    p = 0
    for x, y in zip(a, b):
        if x != y:
            break
        p += 1
    return p >= 4 and p >= max(len(a), len(b)) - 3


def has(toks, word):
    return any(same(t, word) for t in toks)


def product_of(toks, low):
    """Наше изделие в запросе → кампания. Проверка по началу слова, чтобы
    «переходник», «трубка», «опорник» не считались изделиями."""
    for t in toks:
        if t.startswith("отвод") and not t.startswith("отводн") or t.startswith("полуотвод"):
            return "Отводы"
        if t.startswith("тройник"):
            return "Тройники"
        if t.startswith("переход") and not t.startswith("переходн"):
            return "Переходы"
        if re.fullmatch(r"опор(а|ы|у|ой|е|ам|ами|ах)?", t):
            return "Опоры"
        if t.startswith("подвеск") and "трубопровод" in low:
            return "Опоры"
        if t.startswith(("заглушк", "днищ", "штуцер", "бобышк", "угольник")):
            return "Детали для АЭС и ТЭС"
        if t.startswith("пробк") and re.search(r"гост|ост|сто|стал|трубопровод", low):
            return "Детали для АЭС и ТЭС"
    if "детал" in low and "трубопровод" in low:
        return "Детали для АЭС и ТЭС"
    for t in toks:
        if re.fullmatch(r"труб(а|ы|у|ой|е|ам|ами|ах)?", t):
            return "Трубы"
    return None


def catalog_norms():
    """Номера ГОСТ/ОСТ из фасетов каталога по разделам: {раздел: {'17375', '24 125 03', ...}}."""
    files = sorted(glob.glob(os.path.join(ROOT, "perf-reports", "ads", "*", "facets-full.json")))
    if not files:
        return {}
    data = json.load(open(files[-1], encoding="utf-8"))
    sections = data.get("sections", data)   # у ранних выгрузок разделы лежат на верхнем уровне
    out = {}
    for path, facets in sections.items():
        nums = set()
        for slug in facets.get("gost", []):
            parts = slug.split("-")[1:]
            if slug.startswith("gost"):
                nums.add(parts[0])
            else:
                # ОСТ 24-125-03-89 → «24», «24 125», «24 125 03»: в запросах номер
                # пишут и целиком, и началом («ост 24.125», «ост 34»).
                for n in (1, 2, 3):
                    nums.add(" ".join(parts[:n]))
        out[path] = nums
    return out


def norms_in(low):
    """ГОСТ/ОСТ из запроса: ('гост', '17375'), ('ост', '36 146 88')."""
    found = []
    for kind, num in re.findall(r"\b(гост|ост)\s*(?:р\s*)?([\d][\d.\s-]*\d)", low):
        parts = [p for p in re.split(r"[.\s-]+", num) if p]
        found.append((kind, parts))
    return found


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
        # associations — «похожие» по мнению Wordstat, на деле часто мусор
        # («все умрут а я останусь»); берём только results.
        return int(d.get("totalCount") or 0), [(x["phrase"], int(x["count"])) for x in d.get("results") or []]
    sys.exit("Wordstat: 429 пять раз подряд")


def direct(service, method, params, version="v5"):
    h = {"Authorization": f"Bearer {env('YANDEX_DIRECT_TOKEN')}", "Client-Login": env("YANDEX_DIRECT_LOGIN"),
         "Accept-Language": "ru"}
    url = f"https://api.direct.yandex.com/json/{version}/{service}"
    d = requests.post(url, headers=h, json={"method": method, "params": params}, timeout=180).json()
    if "error" in d:
        sys.exit(f"{service}.{method}: {d['error']}")
    return d["result"]


def load_direct():
    camps = direct("campaigns", "get", {"SelectionCriteria": {"States": ["ON"]},
                                        "FieldNames": ["Id", "Name", "NegativeKeywords"]})["Campaigns"]
    ids = [c["Id"] for c in camps]
    # keywords.get: не больше 10 кампаний за раз, ответ страницами.
    kws = []
    for i in range(0, len(ids), 10):
        offset = 0
        while True:
            res = direct("keywords", "get", {"SelectionCriteria": {"CampaignIds": ids[i:i + 10], "States": ["ON"]},
                                             "FieldNames": ["Keyword", "CampaignId"],
                                             "Page": {"Limit": 10000, "Offset": offset}})
            kws += res.get("Keywords", [])
            if "LimitedBy" not in res:
                break
            offset = res["LimitedBy"]
    # Общие наборы: get требует явные Ids — берём их из кампаний (поле только в v501).
    camp_sets = {c["Id"]: ((c.get("UnifiedCampaign") or {}).get("NegativeKeywordSharedSetIds") or {}).get("Items") or []
                 for c in direct("campaigns", "get", {"SelectionCriteria": {"Ids": ids}, "FieldNames": ["Id"],
                                 "UnifiedCampaignFieldNames": ["NegativeKeywordSharedSetIds"]}, "v501")["Campaigns"]}
    set_ids = sorted({s for v in camp_sets.values() for s in v})
    sets = {s["Id"]: s["NegativeKeywords"] for s in
            (direct("negativekeywordsharedsets", "get", {"SelectionCriteria": {"Ids": set_ids},
             "FieldNames": ["Id", "NegativeKeywords"]}).get("NegativeKeywordSharedSets", []) if set_ids else [])}
    return camps, kws, camp_sets, sets


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--check", action="store_true", help="один запрос — проверить ключ и папку")
    ap.add_argument("--seeds", nargs="*", help="свои затравки вместо встроенных")
    ap.add_argument("--num", type=int, default=300, help="сколько запросов брать на затравку (до 2000)")
    ap.add_argument("--min-count", type=int, default=10, help="отсечь редкие запросы")
    ap.add_argument("--refresh", action="store_true", help="не брать сырые ответы из кеша")
    a = ap.parse_args()

    # В проектах группы ключ записан по-разному.
    key = env("YANDEX_SEARCH_API_KEY") or env("YANDEX_CLOUD_API_KEY")
    folder = env("YANDEX_CLOUD_FOLDER_ID")
    if not key or not folder:
        sys.exit("Нужны YANDEX_CLOUD_API_KEY (или YANDEX_SEARCH_API_KEY) и YANDEX_CLOUD_FOLDER_ID в .env")

    if a.check:
        total, rows = wordstat("отвод крутоизогнутый", key, folder, 5)
        print(f"ключ работает: «отвод крутоизогнутый» — {total} показов за 30 дней")
        for p, c in rows:
            print(f"  {c:>7}  {p}")
        return

    seeds = a.seeds or ([f"{t} {m}" for t in TYPES for m in MARKERS] + EXTRA)
    camps, kws, camp_sets, sets = load_direct()
    names = {c["Id"]: c["Name"] for c in camps}

    def singles(items):
        # Однословные минус-слова. Служебные слова здесь не выбрасываем:
        # «гост +на» — минус-фраза из двух слов, она режет только «гост на …».
        out_ = []
        for n in items or []:
            raw = [w for w in re.split(r"[\s\-]+", re.sub(r"[!+\"\[\]]", "", n.lower().replace("ё", "е"))) if w]
            if len(raw) == 1:
                out_.append(raw[0])
        return out_

    shared_neg = {sid: singles(items) for sid, items in sets.items()}
    camp_neg = {}
    for c in camps:
        neg = singles((c.get("NegativeKeywords") or {}).get("Items"))
        for sid in camp_sets.get(c["Id"], []):
            neg += shared_neg.get(sid, [])
        camp_neg[c["Id"]] = neg
    all_shared = sorted({w for v in shared_neg.values() for w in v})

    phrases = []   # (кампания, слова фразы, её минус-слова)
    for k in kws:
        if k["Keyword"].startswith("---"):
            continue
        base = tokens(k["Keyword"])
        own = [tokens(m)[0] for m in re.findall(r"\s-([^\s]+)", k["Keyword"].lower()) if tokens(m)]
        if base:
            phrases.append((k["CampaignId"], base, own))
    print(f"работающих кампаний {len(camps)}, активных фраз {len(phrases)}, слов в общих стоп-листах {len(all_shared)}")

    day = dt.date.today().isoformat()
    dest_dir = os.path.join(ROOT, "perf-reports", "ads", day)
    os.makedirs(dest_dir, exist_ok=True)
    cache = os.path.join(dest_dir, "wordstat-raw.json")
    found = {}
    if os.path.exists(cache) and not a.seeds and not a.refresh:
        found = {p: tuple(v) for p, v in json.load(open(cache, encoding="utf-8")).items()}
        print(f"Wordstat из кеша: {len(found)} запросов")
    else:
        print(f"затравок: {len(seeds)}")
        for i, seed in enumerate(seeds, 1):
            total, rows = wordstat(seed, key, folder, a.num)
            for phrase, count in rows:
                if count >= a.min_count and count > found.get(phrase, (0, ""))[0]:
                    found[phrase] = (count, seed)
            print(f"  [{i}/{len(seeds)}] {seed}: {total}", flush=True)
            time.sleep(0.25)
        if not a.seeds:
            json.dump(found, open(cache, "w", encoding="utf-8"), ensure_ascii=False)

    catalog = catalog_norms()
    all_catalog = set().union(*catalog.values()) if catalog else set()

    out, stats = [], collections.Counter()
    for phrase, (count, seed) in found.items():
        low = phrase.lower().replace("ё", "е")
        toks = tokens(phrase)
        product = product_of(toks, low)
        where = ""
        if not product or HOUSEHOLD.search(low) or (re.search(r"\b20\b", low) and not re.search(r"(ст|стал\w*)\s*20", low)):
            status = "не наш"
        else:
            missing = []
            for kind, parts in norms_in(low):
                cands = {parts[0]} if kind == "гост" or len(parts) == 1 else {" ".join(parts[:3]), " ".join(parts[:2])}
                if all_catalog and not (cands & all_catalog):
                    missing.append(f"{kind} {' '.join(parts)}")
            matched = [(cid, base, own) for cid, base, own in phrases
                       if all(has(toks, w) for w in base) and not any(has(toks, m) for m in own)]
            live, cut = set(), collections.defaultdict(set)
            for cid, base, own in matched:
                # Правило Директа: минус-слово не действует на фразу, где есть это слово.
                blockers = [n for n in camp_neg[cid] if has(toks, n) and not any(same(n, b) for b in base)]
                if blockers:
                    cut[cid] |= set(blockers)
                else:
                    live.add(cid)
            if missing:
                status, where = "нет в каталоге", ", ".join(missing)
            elif live:
                status, where = "покрыт", "; ".join(sorted({names[c][:30] for c in live}))
            elif cut:
                status = "отрезан минусами кампании"
                where = "; ".join(f"{names[c][:25]}: {', '.join(sorted(v))}" for c, v in sorted(cut.items()))
            elif any(has(toks, n) for n in all_shared):
                status, where = "общий стоп-лист", ", ".join(n for n in all_shared if has(toks, n))
            elif INDUSTRIAL.search(low):
                status = "пробел"
            else:
                status = "не наш"
        stats[status] += 1
        out.append({"status": status, "campaign": product or "", "count": count, "phrase": phrase,
                    "where": where, "seed": seed})

    dest = os.path.join(dest_dir, "wordstat-gaps.csv")
    order = ["пробел", "отрезан минусами кампании", "общий стоп-лист", "нет в каталоге", "покрыт", "не наш"]
    out.sort(key=lambda r: (order.index(r["status"]), r["campaign"], -r["count"]))
    with open(dest, "w", encoding="utf-8-sig", newline="") as f:
        wr = csv.DictWriter(f, fieldnames=["status", "campaign", "count", "phrase", "where", "seed"])
        wr.writeheader()
        wr.writerows(out)

    print(f"\nуникальных запросов: {len(found)} — " + ", ".join(f"{k} {stats[k]}" for k in order))

    # Минус-слова кампаний, совпадающие с ГОСТами её же раздела каталога.
    print("\nминус-слова кампаний, совпадающие с номерами ГОСТ своего раздела:")
    any_flag = False
    for c in camps:
        sections = next((v for k, v in CAMPAIGN_SECTIONS.items() if c["Name"].startswith(k)), [])
        own_norms = set().union(*(catalog.get(s, set()) for s in sections)) if sections else set()
        flags = [n for n in singles((c.get("NegativeKeywords") or {}).get("Items")) if n in own_norms]
        if flags:
            any_flag = True
            print(f"  {c['Name'][:40]}: {', '.join(flags)}")
    if not any_flag:
        print("  нет")

    for st in ("пробел", "отрезан минусами кампании"):
        rows = [r for r in out if r["status"] == st]
        for camp in sorted({r["campaign"] for r in rows}):
            part = [r for r in rows if r["campaign"] == camp]
            print(f"\n[{st}] {camp} — {len(part)}, топ:")
            for r in part[:25]:
                print(f"  {r['count']:>6}  {r['phrase']}" + (f"   ← {r['where']}" if r["where"] else ""))
    print(f"\nфайл: {os.path.relpath(dest, ROOT)}")


if __name__ == "__main__":
    main()
