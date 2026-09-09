# -*- coding: utf-8 -*-
"""Кампания по атомной и энергетической продукции.

Профиль завода — детали трубопроводов для АЭС и ТЭС, и самая маржинальная
часть каталога это не ГОСТ-ширпотреб, а изделия по отраслевым стандартам:
СТО 95 (Росатом), СТО 79814898, СТО СРО-П 60542948, СТО 321 и 318,
энергетические ОСТ 34-10 и ОСТ 24-125. В рекламе они почти не представлены:
из 84 нормативов сайта семантикой закрыто 30.

Скрипт собирает фразы по каждому нормативу каталога, **проверяет спрос
через `keywordsresearch.hasSearchVolume`** и оставляет только те, по которым
показы есть. Это защита от повторения истории «ПЭ_Трубы», где 838 фраз
нулевой частотности дали 130 кликов за год.

    python scripts/ads/direct_atomic.py plan
    python scripts/ads/direct_atomic.py apply
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
ALLOWED = {"campaigns.add", "adgroups.add", "keywords.add", "ads.add",
           "campaigns.get", "keywordsresearch.hasSearchVolume"}

SITE = "https://prom-en.com"
COUNTERS = [62844301, 94555563]   # сайт + профиль организации (в нём цели коллтрекинга)
SITELINK_SET_ID = 1508803715
KEYWORDS_PER_GROUP = 200
WEEKLY = 3_000
BID_CEILING = 90                  # атомные детали дороже обычных СДТ, потолок выше

UTM = ("utm_source=yandex&utm_medium=cpc"
       "&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{phrase_id}_{retargeting_id}"
       "&utm_term={keyword}")

# Раздел каталога → как изделие называют в запросах. «Колено» для отводов
# обязательно: по ОСТ 24.125 и СТО именно так их и ищут.
SECTIONS = {
    "/catalog/sdt/otvody/": ("Отводы", ["отвод", "отводы", "колено", "колена"]),
    "/catalog/sdt/troyniki/": ("Тройники", ["тройник", "тройники"]),
    "/catalog/sdt/perekhody/": ("Переходы", ["переход", "переходы"]),
    "/catalog/sdt/zaglushki/": ("Заглушки", ["заглушка", "заглушки"]),
    "/catalog/sdt/dnishcha/": ("Днища", ["днище", "днища"]),
    "/catalog/opory/": ("Опоры", ["опора", "опоры"]),
    "/catalog/truby/": ("Трубы", ["труба", "трубы"]),
}

# Отраслевые стандарты: их и рекламируем. ГОСТы из выборки исключены —
# они уже закрыты товарными кампаниями.
INDUSTRY = re.compile(r"^(ost|sto|seriya)")

# Общие запросы отрасли — без привязки к номеру норматива.
GENERIC = [
    "детали трубопроводов для аэс", "детали трубопроводов аэс",
    "детали трубопроводов для тэс", "детали трубопроводов тэс",
    "детали трубопроводов для атомных станций", "сдт для аэс",
    "изготовление деталей трубопроводов по чертежу",
    "детали трубопроводов по сто", "детали трубопроводов на заказ",
    "трубопроводы аэс изготовление", "отводы для аэс", "отводы для тэс",
    "тройники для аэс", "переходы для аэс", "колена для аэс",
    "трубы для аэс", "трубы для тэс", "трубы для атомных станций",
    "детали трубопроводов атомной энергетики",
    "детали и сборочные единицы трубопроводов",
    "изготовление сдт по чертежам заказчика",
]

# Коммерческие добавки. «Цена» и «прайс» намеренно не берём: цен на сайте
# нет, и год назад такие запросы дали худший CPA в аккаунте.
SUFFIXES = ["", "купить", "заказать", "производство", "изготовление",
            "завод", "производитель", "поставщик"]

# Марки сталей энергетики: 12Х1МФ и 15Х1М1Ф — паропроводы ТЭС и АЭС,
# нержавеющие 12Х18Н10Т и 08Х18Н10Т — первый контур и агрессивные среды.
# Спрос по ним куда живее, чем по номерам стандартов: из проверки
# 254 фразы из 330 против 103 из 1 125.
STEELS = {
    "12х1мф": "12h1mf", "15х1м1ф": "", "12х18н10т": "12h18n10t",
    "08х18н10т": "08h18n10t", "15гс": "15gs", "16гс": "16gs", "20": "20",
    "09г2с": "09g2s", "15х5м": "15h5m", "10х9мфб": "", "17г1с": "17g1s",
    "13хфа": "13hfa",
}
STEEL_ITEMS = [("отвод", "/catalog/sdt/otvody/"), ("отводы", "/catalog/sdt/otvody/"),
               ("колено", "/catalog/sdt/otvody/"), ("тройник", "/catalog/sdt/troyniki/"),
               ("тройники", "/catalog/sdt/troyniki/"), ("переход", "/catalog/sdt/perekhody/"),
               ("заглушка", "/catalog/sdt/zaglushki/"), ("днище", "/catalog/sdt/dnishcha/"),
               ("труба", "/catalog/truby/"), ("трубы", "/catalog/truby/")]

# Котельная и отраслевая тематика — второй источник объёма.
EXTRA = [
    "детали трубопроводов", "детали трубопроводов купить", "детали трубопроводов завод",
    "сдт детали трубопроводов", "изготовление деталей трубопроводов",
    "детали трубопроводов пара", "детали паропроводов", "отводы для котлов",
    "трубы котельные", "трубы для котлов", "трубы паропроводов",
    "трубопроводы высокого давления детали", "детали трубопроводов высокого давления",
    "трубы для тэц", "отводы для тэц", "детали трубопроводов для тэц",
    "детали трубопроводов тэц", "трубы для энергетики", "трубы для атомной энергетики",
    "детали трубопроводов росатом", "поставка деталей трубопроводов",
    "завод деталей трубопроводов", "производство деталей трубопроводов",
    "детали трубопроводов по чертежам", "нестандартные детали трубопроводов",
    "детали трубопроводов нержавеющие", "отводы нержавеющие для аэс",
    "трубы бесшовные для аэс", "трубы электросварные для тэс",
    "трубы стальные для энергетики",
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


def human(slug):
    """Слаг фасета → как норматив пишут в поисковой строке.

    `sto-95-115-2013` → «сто 95 115», `ost-34-10-763-97` → «ост 34 10 763»,
    `sto-sro-p-60542948-00011-2013` → «сто сро п 60542948 00011». Год
    отбрасываем: с ним ищут реже, а фраза без года ловит и вариант с ним.
    """
    parts = slug.split("-")
    if parts and re.fullmatch(r"(19|20)\d{2}", parts[-1]):
        parts = parts[:-1]
    elif parts and re.fullmatch(r"\d{2}", parts[-1]) and len(parts) > 2:
        parts = parts[:-1]
    # Слаг латинский, а ищут кириллицей: без перевода получаются фразы
    # вида «отвод ost 24 125 03», по которым никто не приходит.
    RU = {"ost": "ост", "sto": "сто", "sro": "сро", "p": "п",
          "seriya": "серия", "gost": "гост"}
    return " ".join(RU.get(x, x) for x in parts)


def variants():
    """Все фразы-кандидаты: норматив × изделие × коммерческая добавка."""
    facets = json.load(open(os.path.join(DATA, "facets.json"), encoding="utf-8"))
    by_path = {info["path"]: info.get("gost_params", []) for info in facets.values()}
    out = []
    for path, (label, words) in SECTIONS.items():
        for slug in by_path.get(path, []):
            if not INDUSTRY.match(slug) or path == "/catalog/truby/":
                continue
            norm = human(slug)
            for word in words:
                for suffix in SUFFIXES:
                    text = f"{word} {norm} {suffix}".strip()
                    out.append({"text": " ".join(text.split()), "path": path,
                                "slug": slug, "kind": "gost",
                                "group": norm.upper(), "label": label})
    steel_by_path = {info["path"]: info.get("steel_params", []) for info in facets.values()}
    for word, path in STEEL_ITEMS:
        for mark, facet in STEELS.items():
            # фасет по стали ставим, только если он есть в этом разделе:
            # у труб их шесть против двадцати одной у отводов
            slug = facet if facet in steel_by_path.get(path, []) else ""
            # Марки нет в фасете раздела — значит такого товара в каталоге
            # нет. Это ровно та ошибка «ПЭ_Трубы»: 627 фраз вели на трубы
            # ГОСТ 20295, которых у нас никогда не было.
            if not slug:
                continue
            for tpl in (f"{word} {mark}", f"{word} из стали {mark}", f"{word} сталь {mark}"):
                label = SECTIONS.get(path, ("Трубы", []))[0]
                # группа обязана совпадать с разделом: иначе «труба 12х18н10т»
                # уедет на посадочную отводов — посадочная берётся у первой
                # фразы группы
                out.append({"text": tpl, "path": path, "slug": slug,
                            "kind": "steel", "group": f"{label} · сталь {mark.upper()}",
                            "label": label})
    for text in GENERIC + EXTRA:
        # общие отраслевые ведём на срез каталога по отрасли: АЭС — 2 075
        # позиций, ТЭС — 3 247, вместо витрины на 15 407
        ind = "aes" if re.search(r"аэс|атомн|росатом", text) else (
              "tes" if re.search(r"тэс|тэц|котл|пар|энергетик", text) else "")
        out.append({"text": text, "path": "/catalog/sdt/", "slug": ind,
                    "kind": "industry",
                    "group": "запросы АЭС" if ind == "aes" else
                             ("запросы ТЭС и котельные" if ind == "tes" else "общие отраслевые"),
                    "label": "Детали трубопроводов"})
    seen, uniq = set(), []
    for v in out:
        if v["text"] not in seen:
            seen.add(v["text"])
            uniq.append(v)
    return uniq


def with_demand(cands):
    """Оставляем только то, что люди действительно ищут."""
    alive, chunk = [], 1000
    for i in range(0, len(cands), chunk):
        part = cands[i:i + chunk]
        res = call("keywordsresearch", "hasSearchVolume", {
            "SelectionCriteria": {"Keywords": [c["text"] for c in part], "RegionIds": [225]},
            "FieldNames": ["Keyword", "AllDevices"]})
        # Директ отвечает строкой "YES"/"NO", а не булевым: проверка на
        # истинность пропускала бы всё подряд, включая "NO".
        ok = {x["Keyword"] for x in res.get("HasSearchVolumeResults", [])
              if x.get("AllDevices") == "YES"}
        alive.extend([c for c in part if c["text"] in ok])
        print(f"  проверено {min(i + chunk, len(cands))} из {len(cands)}, со спросом {len(alive)}")
    return alive


PARAM = {"gost": "gost", "steel": "steel", "industry": "industry"}


def href_for(item):
    slug = item.get("slug")
    if slug:
        tail = f'?{PARAM[item.get("kind", "gost")]}={slug}&{UTM}'
    else:
        tail = f"?{UTM}"
    return f'{SITE}{item["path"]}{tail}'


def headline(label, group):
    """Заголовок группы: «Отводы из стали 12Х1МФ», «Отводы по СТО 95 115»."""
    if group.startswith("запросы АЭС"):
        return "Детали трубопроводов для АЭС"
    if group.startswith("запросы ТЭС"):
        return "Детали трубопроводов для ТЭС и ТЭЦ"
    if group.startswith("общие отраслевые"):
        return "Детали трубопроводов на заказ"
    if " · сталь " in group:
        return f'{label} из стали {group.split(" · сталь ")[1]}'[:56]
    if group.endswith("прочие стандарты"):
        return f"{label} по ОСТ и СТО"[:56]
    return f"{label} по {group}"[:56]



def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("what", choices=["plan", "apply"])
    a = ap.parse_args()

    cands = variants()
    print(f"кандидатов собрано: {len(cands)}")
    cache = os.path.join(DATA, "atomic-demand.json")
    if os.path.exists(cache):
        alive = json.load(open(cache, encoding="utf-8"))
        print(f"спрос взят из кэша: {len(alive)} фраз")
    else:
        alive = with_demand(cands)
        json.dump(alive, open(cache, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
    print(f"фраз со спросом: {len(alive)} ({len(alive) * 100 // max(len(cands), 1)}%)\n")

    tree = collections.defaultdict(list)
    for v in alive:
        tree[v["group"]].append(v)
    # Норматив, по которому нашлась пара фраз, отдельной группы не стоит.
    small = [g for g, v in tree.items() if len(v) < 4 and g != "общие запросы отрасли"]
    for g in small:
        for v in tree.pop(g):
            v["group"] = f'{v["label"]}: прочие стандарты'
            v["slug"] = ""
            tree[v["group"]].append(v)

    print(f'{"группа":34} {"фраз":>5}  посадочная')
    for g, items in sorted(tree.items(), key=lambda kv: -len(kv[1])):
        it = items[0]
        dest = it["path"] + (f'?{PARAM[it.get("kind","gost")]}={it["slug"]}' if it["slug"] else "")
        print(f'  {g[:32]:32} {len(items):>5}  {dest}')
    print(f"\nитого: 1 кампания, {len(tree)} групп, {len(alive)} фраз")
    print()
    for g, items in list(sorted(tree.items(), key=lambda kv: -len(kv[1])))[:8]:
        print(f'    «{headline(items[0]["label"], g)}»')
    if small:
        print(f"(в «прочие стандарты» слито {len(small)} нормативов с двумя-тремя фразами)")

    if a.what == "plan":
        print("\nэто отчёт. Создание: apply")
        return
    create(tree)


def create(tree):
    neg = json.load(open(os.path.join(DATA, "negatives-backup.json"), encoding="utf-8"))
    words = ((next((c for c in neg if c["Id"] == 709197084), {}) or {})
             .get("NegativeKeywords") or {}).get("Items") or []
    res = call("campaigns", "add", {"Campaigns": [{
        "Name": "Детали для АЭС и ТЭС | СТО и ОСТ | Поиск | Россия",
        "StartDate": "2026-09-10",
        "TimeZone": "Asia/Yekaterinburg",
        "NegativeKeywords": {"Items": words},
        "TextCampaign": {
            "BiddingStrategy": {
                "Search": {"BiddingStrategyType": "WB_MAXIMUM_CLICKS",
                           "WbMaximumClicks": {"WeeklySpendLimit": WEEKLY * 1_000_000,
                                               "BidCeiling": BID_CEILING * 1_000_000}},
                "Network": {"BiddingStrategyType": "SERVING_OFF"}},
            "CounterIds": {"Items": COUNTERS},
            "Settings": [{"Option": "ADD_METRICA_TAG", "Value": "NO"},
                         {"Option": "ENABLE_SITE_MONITORING", "Value": "YES"},
                         {"Option": "CAMPAIGN_EXACT_PHRASE_MATCHING_ENABLED", "Value": "YES"}],
        }}]})
    cid = (res.get("AddResults") or [{}])[0].get("Id")
    if not cid:
        sys.exit("кампания не создалась")
    print(f"кампания создана: {cid}")

    groups, plan = [], []
    for group, items in sorted(tree.items(), key=lambda kv: -len(kv[1])):
        for i in range(0, len(items), KEYWORDS_PER_GROUP):
            chunk = items[i:i + KEYWORDS_PER_GROUP]
            part = "" if len(items) <= KEYWORDS_PER_GROUP else f" · {i // KEYWORDS_PER_GROUP + 1}"
            groups.append({"Name": f"{group}{part}"[:255], "CampaignId": cid, "RegionIds": [225]})
            plan.append((group, chunk))
    ids = []
    for i in range(0, len(groups), 100):
        r = call("adgroups", "add", {"AdGroups": groups[i:i + 100]})
        ids.extend([x.get("Id") for x in (r.get("AddResults") or [])])
    print(f"групп: {len([x for x in ids if x])}")

    kws, ads = [], []
    for gid, (group, chunk) in zip(ids, plan):
        if not gid:
            continue
        for c in chunk:
            kws.append({"Keyword": c["text"][:4096], "AdGroupId": gid})
        label = chunk[0]["label"]
        title = headline(label, group)
        ads.append({"AdGroupId": gid, "TextAd": {
            "Title": title, "Title2": "Челябинский завод"[:30],
            "Text": "Изготовим по СТО, ОСТ и чертежу. Паспорт, сертификат, протокол ОТК."[:81],
            "Href": href_for(chunk[0]),
            "Mobile": "NO", "SitelinkSetId": SITELINK_SET_ID}})
    for i in range(0, len(kws), 1000):
        call("keywords", "add", {"Keywords": kws[i:i + 1000]})
    print(f"фраз: {len(kws)}")
    for i in range(0, len(ads), 100):
        call("ads", "add", {"Ads": ads[i:i + 100]})
    print(f"объявлений: {len(ads)}")
    json.dump({"campaign": cid}, open(os.path.join(DATA, "atomic-created.json"), "w",
                                      encoding="utf-8"), ensure_ascii=False)
    print("\nкампания создана остановленной.")


if __name__ == "__main__":
    main()
