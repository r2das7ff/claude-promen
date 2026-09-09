# -*- coding: utf-8 -*-
"""Пересборка товарных кампаний: одиннадцать штук в пять.

**Что не так со старыми.** Семантика разложена по одной фразе на группу:
838 фраз в 838 группах у «ПЭ_Трубы», 855 в 855 у «Тройников». Группа с
единственным запросом «Труба 1220 12 ГОСТ 20295 85» набирает единицы
показов в год и не может накопить статистику. Все одиннадцать кампаний за
год потратили 5% бюджета аккаунта.

Плюс три дефекта, которые видно только в данных:

* 345 фраз несут минус-слова внутри себя, и 70 вычитают слово «гост» из
  фразы с номером ГОСТа — отсекая ровно те запросы, ради которых заводились;
* 725 фраз ведут на трубы ГОСТ 20295-85, 9940-81 и 9941-81, которых в
  каталоге нет вовсе — там пять серий: 8732, 8734, 10704, 10705, 3262;
* объявления написаны под те же несуществующие ГОСТы.

**Куда ведём.** Фасет каталога применяется только там, где норматив назван
в самом запросе. Размерные запросы («тройник 108х9») идут на витрину
раздела: фасет ГОСТ 17376 отдаёт 96 позиций из 1 398, и загонять туда весь
размерный спрос значит спрятать 93% ассортимента.

    python scripts/ads/direct_rebuild.py plan
    python scripts/ads/direct_rebuild.py plan --verbose
    python scripts/ads/direct_rebuild.py apply
"""
import argparse
import collections
import json
import os
import re
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
DATA = os.path.join(ROOT, "perf-reports", "ads", "2026-09-09")
API = "https://api.direct.yandex.com/json/v5/"
ALLOWED = {"campaigns.add", "adgroups.add", "keywords.add", "ads.add", "campaigns.get"}

SITE = "https://prom-en.com"
COUNTER = 62844301
SITELINK_SET_ID = 1508803715      # актуальный набор быстрых ссылок, см. память проекта
KEYWORDS_PER_GROUP = 200          # потолок Директа
WEEKLY = 2_000                    # ₽/нед на кампанию — это потолок, а не план траты
BID_CEILING = 60                  # ₽, от фактического CPC товарных кампаний (26–47 ₽)

UTM = ("utm_source=yandex&utm_medium=cpc"
       "&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{phrase_id}_{retargeting_id}"
       "&utm_term={keyword}")

# Раздел каталога: ключ, название, путь, как узнать в тексте запроса.
# Порядок важен — «отвод гнутый для трубопровода» должен опознаться
# отводом, а не трубой.
CATEGORIES = [
    ("otvody", "Отводы", "/catalog/sdt/otvody/", r"отвод|колен|угольник"),
    ("troyniki", "Тройники", "/catalog/sdt/troyniki/", r"тройник"),
    ("perekhody", "Переходы", "/catalog/sdt/perekhody/", r"переход"),
    ("opory", "Опоры", "/catalog/opory/", r"опор"),
    ("truby", "Трубы", "/catalog/truby/", r"труб"),
]

# Подтипы внутри раздела: по ним режем фразы без явного норматива.
SUBTYPES = {
    "otvody": [("крутоизогнутые", r"крутоизогнут"), ("гнутые", r"гнут"),
               ("сварные и секторные", r"сварн|секторн"), ("по размеру", r"\d")],
    "troyniki": [("равнопроходные", r"равнопроходн"), ("переходные", r"переходн"),
                 ("сварные", r"сварн|штампованн"), ("по размеру", r"\d")],
    "perekhody": [("концентрические", r"концентрическ"), ("эксцентрические", r"эксцентрическ"),
                  ("сварные", r"сварн|штампованн"), ("по размеру", r"\d")],
    "opory": [("скользящие", r"скольз"), ("неподвижные", r"неподвиж"),
              ("подвижные", r"подвиж"), ("хомутовые и катковые", r"хомут|катков")],
    "truby": [("бесшовные", r"бесшовн|горячедеформир|холоднодеформир"),
              ("электросварные", r"электросварн|сварн|прямошовн"),
              ("водогазопроводные", r"водогазопровод|вгп"), ("по размеру", r"\d")],
}

# Трубы, которых в каталоге нет: пять серий против трёх этих номеров.
# Фразы про них в новые кампании не переносим — платить за спрос на товар,
# которого нет, и есть причина нулевых заявок «ПЭ_Трубы».
ABSENT_TUBES = {"20295", "9940", "9941", "550", "632", "633", "8731", "1381", "1698"}

# Тексты объявлений. Никаких обещаний, которых нет на сайте: сроки,
# наличие и цены не заявляем — цен в каталоге нет вовсе.
# Первый элемент — короткое имя изделия для заголовка, второй — полное
# для групп без подтипа, дальше второй заголовок и текст.
COPY = {
    "otvody": ("Отводы", "Отводы стальные приварные", "Каталог 3 247 позиций",
               "Ду, угол, сталь и толщина стенки в каталоге. Запрос КП с сайта."),
    "troyniki": ("Тройники", "Тройники стальные приварные", "Каталог 1 398 позиций",
                 "Равнопроходные и переходные, Ду и сталь в каталоге. Запрос КП."),
    "perekhody": ("Переходы", "Переходы стальные приварные", "Каталог 558 позиций",
                  "Концентрические и эксцентрические, Ду и сталь. Запрос КП с сайта."),
    "opory": ("Опоры", "Опоры трубопроводов", "Каталог 32 позиции",
              "Скользящие, неподвижные, хомутовые. Подбор по серии и Ду."),
    "truby": ("Трубы", "Трубы стальные", "Каталог 1 468 позиций",
              "Бесшовные и электросварные ГОСТ 8732, 8734, 10704, 10705, 3262."),
}

# Размерные запросы режем по условному диаметру, а не механически по 200:
# так у каждой группы свой осмысленный заголовок, и «Тройники 1220х22»
# не лежат вперемешку с «Тройники 57».
DIAMETERS = [(0, 108, "Ду до 100"), (108, 325, "Ду 108–325"),
             (325, 720, "Ду 325–720"), (720, 10 ** 9, "Ду 720 и выше")]


# Откуда брать минус-слова для новой кампании: объединяем списки старых
# кампаний этого раздела — там накоплена отраслевая специфика (полипропилен,
# rehau, сантехника), которую заново не придумаешь.
NEGATIVE_SOURCES = {
    "otvody": [52152799, 52152805],
    "troyniki": [31557286, 52152819, 52152829, 52152837],
    "perekhody": [52152847, 52152855, 52152866],
    "opory": [52152889],
    "truby": [31876825],
}
NEGATIVES_LIMIT = 65_535          # потолок Директа в символах на кампанию


def negatives_for(cat):
    """Минус-слова раздела: объединение старых списков, дедуп по лемме."""
    backup = json.load(open(os.path.join(DATA, "negatives-backup.json"), encoding="utf-8"))
    by_id = {c["Id"]: ((c.get("NegativeKeywords") or {}).get("Items") or []) for c in backup}
    out, seen = [], set()
    for cid in NEGATIVE_SOURCES.get(cat, []):
        for w in by_id.get(cid, []):
            low = w.lower().replace("ё", "е").replace("-", " ")
            low = " ".join(low.replace("!", "").replace("+", "").split())
            if low and low not in seen:
                seen.add(low)
                out.append(w)
    size = 0
    kept = []
    for w in out:
        size += len(w) + 1
        if size > NEGATIVES_LIMIT:
            break
        kept.append(w)
    return kept


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
                      timeout=300)
    d = r.json()
    if "error" in d:
        e = d["error"]
        sys.exit(f"{full} → {e.get('error_code')}: {e.get('error_string')} | {e.get('error_detail')}")
    res = d.get("result", {})
    for key in ("AddResults", "UpdateResults"):
        for item in (res.get(key) or []):
            for x in (item.get("Errors") or []):
                print(f"    ошибка {x.get('Code')}: {x.get('Message')} {x.get('Details', '')}")
    return res


def facet_map():
    """Путь раздела → {номер норматива: значение фасета}."""
    data = json.load(open(os.path.join(DATA, "facets.json"), encoding="utf-8"))
    out = {}
    for info in data.values():
        m = {}
        for slug in info.get("gost_params", []):
            nums = re.findall(r"\d+", slug)
            if nums:
                m[nums[0]] = slug
        out[info["path"]] = m
    return out


def normalize(phrase):
    """Чиним фразу: снимаем минусовку, которая била по собственному запросу."""
    p = phrase.strip()
    if re.search(r"\b\d{4,5}\b", p):
        p = re.sub(r"\s-гост\w*\b", "", p, flags=re.I)
    return " ".join(p.split())


def category_of(phrase):
    low = phrase.lower()
    for key, name, path, pattern in CATEGORIES:
        if re.search(pattern, low):
            return key, name, path
    return None, None, None


def diameter_of(phrase):
    """Условный диаметр запроса — самое большое число, похожее на Ду.

    В «Тройники 1220 820» размерность задаёт больший диаметр. Границу слова
    здесь использовать нельзя: в «Тройники 76х3» кириллическая «х» для
    Python часть слова, и `` внутри не срабатывает — потому смотрим на
    соседей-цифр.
    """
    nums = [int(n) for n in re.findall(r"(?<!\d)(\d{2,4})(?!\d)", phrase) if 10 <= int(n) <= 1620]
    if not nums:
        return "по размеру"
    d = max(nums)
    for lo, hi, label in DIAMETERS:
        if lo <= d < hi:
            return label
    return "по размеру"


def subtype_of(cat, phrase):
    low = phrase.lower()
    for label, pattern in SUBTYPES.get(cat, []):
        if re.search(pattern, low):
            return label
    return "общие"


def build():
    """Раскладка фраз по будущим кампаниям и группам."""
    src = json.load(open(os.path.join(DATA, "rebuild-source.json"), encoding="utf-8"))
    facets = facet_map()
    phrases, dropped, seen = [], collections.Counter(), set()

    for k in src["keywords"]:
        raw = k["Keyword"].strip()
        if raw.startswith("---autotargeting"):
            dropped["автотаргетинг"] += 1
            continue
        text = normalize(raw)
        cat, name, path = category_of(text)
        if not cat:
            dropped["раздел не опознан"] += 1
            continue
        low = text.lower()
        if low in seen:
            dropped["дубль"] += 1
            continue
        seen.add(low)
        nums = re.findall(r"\b(\d{3,5})\b", text)
        if cat == "truby" and any(n in ABSENT_TUBES for n in nums):
            dropped["труба вне каталога"] += 1
            continue
        gost = next((n for n in nums if n in facets.get(path, {})), None)
        slug = facets.get(path, {}).get(gost or "")
        group = f"ГОСТ {gost}" if slug else subtype_of(cat, text)
        if group == "по размеру":
            group = diameter_of(text)
        phrases.append({"text": text, "cat": cat, "name": name, "path": path,
                        "slug": slug, "group": group})

    tree = collections.defaultdict(lambda: collections.defaultdict(list))
    for p in phrases:
        tree[p["cat"]][p["group"]].append(p)
    # Группа из двух-трёх фраз не наберёт статистики и только дробит
    # кампанию: такие возвращаем в подтип, потеряв фасет.
    for cat, groups in tree.items():
        for group in [g for g, v in groups.items() if g != "общие" and len(v) < 5]:
            for p in groups.pop(group):
                p["slug"] = ""
                p["group"] = "общие"
                groups["общие"].append(p)
    return src, phrases, dropped, tree


def href_for(path, slug):
    tail = f"?gost={slug}&{UTM}" if slug else f"?{UTM}"
    return f"{SITE}{path}{tail}"


def title_for(cat, group):
    """Заголовок группы. Директ режет по 56 символам, поэтому склеиваем
    короткое имя изделия с названием группы, а не обрезаем длинное."""
    short, full = COPY[cat][0], COPY[cat][1]
    if group == "общие":
        return full[:56]
    if group.startswith("ГОСТ"):
        return f"{full} {group}"[:56] if len(full) + len(group) < 56 else f"{short} {group}"[:56]
    return f"{short} {group}"[:56]


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("what", choices=["plan", "apply"])
    ap.add_argument("--verbose", action="store_true")
    a = ap.parse_args()

    src, phrases, dropped, tree = build()
    print(f"исходных записей: {len(src['keywords'])}")
    for reason, n in dropped.most_common():
        print(f"  минус {n:>5} — {reason}")
    print(f"остаётся фраз: {len(phrases)}\n")

    total_groups = 0
    for cat, name, path, _ in CATEGORIES:
        if cat not in tree:
            continue
        print(f'{name} — {sum(len(v) for v in tree[cat].values())} фраз')
        for group, items in sorted(tree[cat].items(), key=lambda kv: -len(kv[1])):
            parts = (len(items) + KEYWORDS_PER_GROUP - 1) // KEYWORDS_PER_GROUP
            total_groups += parts
            slug = items[0]["slug"]
            where = f'фасет {slug}' if slug else 'витрина раздела'
            tail = f' → {parts} групп по 200' if parts > 1 else ''
            print(f'    {len(items):>5}  {group:26} {where}{tail}')
            if a.verbose:
                for p in items[:3]:
                    print(f'             · {p["text"][:64]}')
        print()
    print(f"итого: {len(tree)} кампаний, {total_groups} групп, {len(phrases)} фраз "
          f"(было 11 кампаний и {len(src['adgroups'])} групп)\n")

    print("минус-слова новых кампаний:")
    for cat, name, path, _ in CATEGORIES:
        if cat in tree:
            print(f'  {name:10} {len(negatives_for(cat)):>4} слов из старых кампаний раздела')

    print()
    for cat, name, path, _ in CATEGORIES:
        if cat not in tree:
            continue
        group = next(iter(sorted(tree[cat], key=lambda g: -len(tree[cat][g]))))
        t1, t2, tx = title_for(cat, group), COPY[cat][2], COPY[cat][3]
        print(f'  {name}: «{t1}» / «{t2}»')
        print(f'          {tx}')
        print(f'          {href_for(path, tree[cat][group][0]["slug"])[:96]}…')

    json.dump({c: {g: [p["text"] for p in v] for g, v in gs.items()} for c, gs in tree.items()},
              open(os.path.join(DATA, "rebuild-plan.json"), "w", encoding="utf-8"),
              ensure_ascii=False, indent=1)

    if a.what == "plan":
        print("\nэто отчёт. Создание кампаний: apply")
        return

    create(tree)


def create(tree):
    made = {}
    for cat, name, path, _ in CATEGORIES:
        if cat not in tree:
            continue
        camp = {
            "Name": f"{name} | Поиск | Россия",
            "StartDate": "2026-09-10",
            "TextCampaign": {
                "BiddingStrategy": {
                    "Search": {"BiddingStrategyType": "WB_MAXIMUM_CLICKS",
                               "WbMaximumClicks": {"WeeklySpendLimit": WEEKLY * 1_000_000,
                                                   "BidCeiling": BID_CEILING * 1_000_000}},
                    "Network": {"BiddingStrategyType": "SERVING_OFF"},
                },
                "CounterIds": {"Items": [COUNTER]},
                "Settings": [{"Option": "ADD_METRICA_TAG", "Value": "NO"}],
            },
            "NegativeKeywords": {"Items": negatives_for(cat)},
        }
        res = call("campaigns", "add", {"Campaigns": [camp]})
        cid = (res.get("AddResults") or [{}])[0].get("Id")
        if not cid:
            sys.exit(f"кампания «{name}» не создалась")
        made[cat] = cid
        print(f'кампания {name}: id {cid}')

        groups, plan = [], []
        for group, items in sorted(tree[cat].items(), key=lambda kv: -len(kv[1])):
            for i in range(0, len(items), KEYWORDS_PER_GROUP):
                chunk = items[i:i + KEYWORDS_PER_GROUP]
                part = "" if len(items) <= KEYWORDS_PER_GROUP else f" · {i // KEYWORDS_PER_GROUP + 1}"
                groups.append({"Name": f"{group}{part}"[:255], "CampaignId": cid,
                               "RegionIds": [225]})
                plan.append((group, chunk))
        ids = []
        for i in range(0, len(groups), 100):
            res = call("adgroups", "add", {"AdGroups": groups[i:i + 100]})
            ids.extend([x.get("Id") for x in (res.get("AddResults") or [])])
        print(f'  групп создано: {len([x for x in ids if x])}')

        kws, ads = [], []
        for gid, (group, chunk) in zip(ids, plan):
            if not gid:
                continue
            for p in chunk:
                kws.append({"Keyword": p["text"][:4096], "AdGroupId": gid})
            ads.append({"AdGroupId": gid, "TextAd": {
                "Title": title_for(cat, group), "Title2": COPY[cat][2][:30],
                "Text": COPY[cat][3][:81], "Href": href_for(path, chunk[0]["slug"]),
                "Mobile": "NO", "SitelinkSetId": SITELINK_SET_ID}})
        for i in range(0, len(kws), 1000):
            call("keywords", "add", {"Keywords": kws[i:i + 1000]})
        print(f'  фраз добавлено: {len(kws)}')
        for i in range(0, len(ads), 100):
            call("ads", "add", {"Ads": ads[i:i + 100]})
        print(f'  объявлений: {len(ads)}')

    print("\nкампании созданы остановленными. Старые не тронуты — сравните и запустите вручную.")
    json.dump(made, open(os.path.join(DATA, "rebuild-created.json"), "w", encoding="utf-8"),
              ensure_ascii=False, indent=1)


if __name__ == "__main__":
    main()
