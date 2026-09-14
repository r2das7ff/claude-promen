# -*- coding: utf-8 -*-
"""Голые номера нормативов, днища ГОСТ 6533 и заглушки ГОСТ 17379 в АЭС-поиск.

Решение заказчика 14.09.2026 по разделу 31 PLAN-DIRECT.md:

1. **Голые номера нормативов каталога** с частотой от 20 в месяц
   (Wordstat). 60–70% запросов по нормативам — номер без изделия
   («ост 34 10 753 97»), а все фразы кампании содержат изделие и такой
   запрос не ловят. Фраза = номер в написании Wordstat; если группа под
   этот норматив уже есть — фраза добавляется в неё, иначе новая группа
   с посадочной на фасет норматива.
2. **Днища ГОСТ 6533** — 2 000 запросов в месяц, фраз не было.
3. **Заглушки ГОСТ 17379** — 4 100 запросов; в минусах кампании «17379»,
   а фраз с номером не было, запросы не ловил никто. Минус не мешает
   фразе, в которой этот номер есть.

Страх «по номеру ищут PDF» проверен (раздел 32 плана): явные «скачать /
pdf / текст» — 0–1% частоты, по голым номерам за 5 месяцев 10 кликов,
ни одного с документными словами, отказы 31% против 44% у фраз с
изделием. Остаток риска закрывается минус-словами документа на кампанию.

Трубы не добавляем (решение заказчика). Нормативы с двумя слагами фасета
(ОСТ 34-10-511, ОСТ 34-42-673) пропущены: непонятно, на какой вести.

    python scripts/ads/direct_aes_norms.py          # план
    python scripts/ads/direct_aes_norms.py apply
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
                      json={"method": method, "params": params}, timeout=300).json()
    if "error" in d:
        sys.exit(f"{service}.{method}: {d['error']}")
    return d["result"]


def errors(result, key):
    return [f'{e["Code"]} {e["Message"]} {e.get("Details", "")}'
            for item in result.get(key, []) for e in (item.get("Errors") or [])]


CAMPAIGN = 714275030
SITE = "https://prom-en.com"
UTM = ("utm_source=yandex&utm_medium=cpc&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{phrase_id}_{retargeting_id}&utm_term={keyword}")
BUSINESS = 131623088486
SITELINKS = 1509189628
CALLOUTS = [44350925, 44350926, 44350927, 44350928]
TEXT = "Изготовим по СТО, ОСТ и чертежу. Паспорт, сертификат, протокол ОТК."
MIN_DEMAND = 20

# Кто ищет сам документ, а не изделие. «pdf» и «скачать» в минусах уже есть.
DOC_NEGATIVES = ["текст", "читать", "статус", "действующий", "взамен", "заменен", "редакция", "файл",
                 "doc", "docx", "djvu", "онлайн"]
# Номера ОСТ 24.125 путаются с соседними сериями: «03 ост 24.125 114»,
# «блок пружинный 07 по ост 24.125 166», «опора 04 ост 24.125 154».
OST24_NEGATIVES = ["опора", "блок", "подвеска", "талреп", "тяга", "пружинный", "105", "114", "154", "158", "166"]
# ОСТ 36-17-85 — ещё и скобы.
OST36_17_NEGATIVES = ["скоба"]

LABEL = {"/catalog/sdt/otvody/": "Отводы", "/catalog/sdt/troyniki/": "Тройники", "/catalog/sdt/perekhody/": "Переходы",
         "/catalog/sdt/zaglushki/": "Заглушки", "/catalog/sdt/dnishcha/": "Днища", "/catalog/opory/": "Опоры",
         "/catalog/tochenye/": "Точёные детали"}
# Где в разделе лежит не то, что в его названии (ОТК, 10.09.2026).
LABEL_BY_SLUG = {"ost-24-125-11-1989": "Штуцеры", "ost-24-125-22-89": "Бобышки", "ost-24-125-23-89": "Пробки",
                 "ost-24-125-21-1989": "Донышки", "ost-24-125-53-1989": "Донышки"}
SKIP_DUPLICATE_SLUGS = {"34 10 511", "34 42 673", "34 10 432", "24 125 48", "24 125 49"}


def official(slug):
    p = slug.split("-")
    short = lambda y: y[-2:]
    if p[0] == "gost":
        return f"ГОСТ {p[1]}-{short(p[2])}"
    if p[0] == "ost" and p[1] == "24":
        return f"ОСТ 24.125.{p[3]}-{short(p[4])}"
    if p[0] == "ost":
        return f"ОСТ {p[1]}-{p[2]}-{p[3]}" + (f"-{short(p[4])}" if len(p) > 4 else "")
    if p[0] == "sto" and p[1] in ("321", "318"):
        return f"СТО ЦКТИ {p[1]}.{p[2]}"
    if p[0] == "sto":
        return f"СТО {p[1]} {p[2]}" + (f"-{p[3]}" if len(p) > 3 else "")
    return slug


def main():
    apply = len(sys.argv) > 1 and sys.argv[1] == "apply"
    sections = json.load(open(os.path.join(ROOT, "perf-reports", "ads", "2026-09-09", "facets-full.json"), encoding="utf-8"))
    sections = sections.get("sections", sections)
    raw = json.load(open(os.path.join(ROOT, "perf-reports", "ads", "2026-09-14", "wordstat-aes-raw.json"), encoding="utf-8"))
    sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
    from wordstat_aes import human, chain

    slug_section = {}
    for path in ("/catalog/sdt/otvody/", "/catalog/sdt/troyniki/", "/catalog/sdt/perekhody/", "/catalog/sdt/zaglushki/",
                 "/catalog/sdt/dnishcha/", "/catalog/opory/", "/catalog/tochenye/"):
        for slug in sections.get(path, {}).get("gost", []):
            slug_section.setdefault(slug, path)

    energy = re.compile(r"^(ost|sto)-|^gost-22[789]\d\d")
    todo = []
    seen_chain = set()
    for slug, path in sorted(slug_section.items()):
        if not energy.search(slug):
            continue
        seed = human(slug)
        info = raw.get(seed)
        if not info or info["total"] < MIN_DEMAND:
            continue
        ch = chain(seed)[0][1]
        if ch in SKIP_DUPLICATE_SLUGS or ch in seen_chain:
            continue
        seen_chain.add(ch)
        phrase = info["rows"][0][0] if info["rows"] else seed
        label = LABEL_BY_SLUG.get(slug, LABEL[path])
        negatives = OST24_NEGATIVES if slug.startswith("ost-24-125") else OST36_17_NEGATIVES if slug.startswith("ost-36-17") else []
        todo.append({"slug": slug, "section": path, "phrase": phrase, "demand": info["total"], "label": label,
                     "name": official(slug), "negatives": negatives})
    # Пункты 2 и 3
    todo.append({"slug": "gost-6533-1978", "section": "/catalog/sdt/dnishcha/", "phrase": "гост 6533", "demand": raw["гост 6533"]["total"],
                 "label": "Днища эллиптические", "name": "ГОСТ 6533-78", "negatives": []})
    todo.append({"slug": "gost-17379-2001", "section": "/catalog/sdt/zaglushki/", "phrase": "гост 17379", "demand": raw["гост 17379"]["total"],
                 "label": "Заглушки", "name": "ГОСТ 17379-2001", "negatives": []})

    groups = call("adgroups", "get", {"SelectionCriteria": {"CampaignIds": [CAMPAIGN]}, "FieldNames": ["Id", "Name", "NegativeKeywords"]})["AdGroups"]
    ads = call("ads", "get", {"SelectionCriteria": {"CampaignIds": [CAMPAIGN]}, "FieldNames": ["AdGroupId", "State"],
                              "TextAdFieldNames": ["Href"]})["Ads"]
    group_by_slug = {}
    for a in ads:
        m = re.search(r"[?&]gost=([a-z0-9\-]+)", (a.get("TextAd") or {}).get("Href", ""))
        if m and a["State"] != "ARCHIVED":
            group_by_slug.setdefault(m.group(1), a["AdGroupId"])
    kws = call("keywords", "get", {"SelectionCriteria": {"CampaignIds": [CAMPAIGN]}, "FieldNames": ["Keyword", "AdGroupId"]})["Keywords"]
    existing = {k["Keyword"].split(" -")[0].lower() for k in kws}
    gnames = {g["Id"]: g for g in groups}

    add_to_existing, new_groups = [], []
    print(f"{'норматив':28} {'частота':>7}  фраза → куда")
    for t in sorted(todo, key=lambda x: -x["demand"]):
        if t["phrase"].lower() in existing:
            print(f"{t['name']:28} {t['demand']:>7}  «{t['phrase']}» — уже есть")
            continue
        gid = group_by_slug.get(t["slug"])
        title = f"{t['label']} по {t['name']}. Челябинский завод"
        if len(title) > 56:
            title = f"{t['label']} по {t['name']}"
        t["title"] = title
        if gid:
            add_to_existing.append((gid, t))
            print(f"{t['name']:28} {t['demand']:>7}  «{t['phrase']}» → в группу «{gnames[gid]['Name']}»")
        else:
            new_groups.append(t)
            print(f"{t['name']:28} {t['demand']:>7}  «{t['phrase']}» → новая группа, «{title}» ({len(title)}), {t['section']}"
                  + (f", минусы группы: {len(t['negatives'])}" if t["negatives"] else ""))

    camp = call("campaigns", "get", {"SelectionCriteria": {"Ids": [CAMPAIGN]}, "FieldNames": ["NegativeKeywords"]})["Campaigns"][0]
    camp_neg = (camp.get("NegativeKeywords") or {}).get("Items") or []
    add_neg = [w for w in DOC_NEGATIVES if w not in camp_neg]
    print(f"\nфраз в существующие группы: {len(add_to_existing)}, новых групп: {len(new_groups)}, "
          f"минус-слов документа в кампанию: +{len(add_neg)} ({', '.join(add_neg)})")
    if not apply:
        print("применить: apply")
        return

    r = call("campaigns", "update", {"Campaigns": [{"Id": CAMPAIGN, "NegativeKeywords": {"Items": camp_neg + add_neg}}]})
    print("минус-слова кампании:", errors(r, "UpdateResults") or "ок")

    for gid, t in add_to_existing:
        r = call("keywords", "add", {"Keywords": [{"AdGroupId": gid, "Keyword": t["phrase"]}]})
        print(f"  фраза «{t['phrase']}» в группу {gid}:", errors(r, "AddResults") or "ок")
        if t["negatives"]:
            cur = (gnames[gid].get("NegativeKeywords") or {}).get("Items") or []
            r = call("adgroups", "update", {"AdGroups": [{"Id": gid, "NegativeKeywords": {"Items": cur + [n for n in t["negatives"] if n not in cur]}}]})
            print("    минусы группы:", errors(r, "UpdateResults") or "ок")

    new_ad_ids = []
    for t in new_groups:
        g = {"Name": f"Норматив · {t['name']}"[:255], "CampaignId": CAMPAIGN, "RegionIds": [225]}
        if t["negatives"]:
            g["NegativeKeywords"] = {"Items": t["negatives"]}
        r = call("adgroups", "add", {"AdGroups": [g]})
        e = errors(r, "AddResults")
        if e:
            print(f"  группа {t['name']}: {e}")
            continue
        gid = r["AddResults"][0]["Id"]
        href = f"{SITE}{t['section']}?gost={t['slug']}&{UTM}"
        r = call("ads", "add", {"Ads": [{"AdGroupId": gid, "TextAd": {
            "Title": t["title"], "Text": TEXT, "Href": href, "Mobile": "NO", "DisplayUrlPath": "aes-tes",
            "BusinessId": BUSINESS, "PreferVCardOverBusiness": "NO", "SitelinkSetId": SITELINKS, "AdExtensionIds": CALLOUTS}}]})
        e = errors(r, "AddResults")
        if e:
            print(f"  объявление {t['name']}: {e}")
            continue
        new_ad_ids.append(r["AddResults"][0]["Id"])
        r = call("keywords", "add", {"Keywords": [{"AdGroupId": gid, "Keyword": t["phrase"]}]})
        print(f"  {t['name']}: группа {gid}, фраза", errors(r, "AddResults") or "ок")

    # Новые объявления лежат черновиками — на модерацию только через ads.moderate.
    if new_ad_ids:
        r = call("ads", "moderate", {"SelectionCriteria": {"Ids": new_ad_ids}})
        print(f"на модерацию: {len(new_ad_ids)}", errors(r, "ModerateResults") or "ок")
    json.dump({"new_ads": new_ad_ids, "added": [t["phrase"] for _, t in add_to_existing] + [t["phrase"] for t in new_groups]},
              open(os.path.join(ROOT, "perf-reports", "ads", "2026-09-14", "aes-norms-applied.json"), "w", encoding="utf-8"),
              ensure_ascii=False, indent=1)

    kws = call("keywords", "get", {"SelectionCriteria": {"CampaignIds": [CAMPAIGN]}, "FieldNames": ["Keyword", "State", "Status"]})["Keywords"]
    added = {t["phrase"].lower() for _, t in add_to_existing} | {t["phrase"].lower() for t in new_groups}
    got = [k for k in kws if k["Keyword"].lower() in added]
    print(f"проверка: найдено {len(got)} из {len(added)} новых фраз; статусы {sorted({k['Status'] for k in got})}")


if __name__ == "__main__":
    main()
