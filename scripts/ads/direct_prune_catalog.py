# -*- coding: utf-8 -*-
"""Убрать из рекламы всё, чего нет в каталоге.

Решение заказчика 14.09.2026: реклама не должна вести на товар, которого
нет на сайте, — это съедает бюджет. Скрипт проходит все активные фразы
работающих кампаний и останавливает (не удаляет) те, где:

* **норматив не из каталога** — ГОСТ/ОСТ/СТО/серия не встречается в
  фасетах раздела этого изделия (ОСТ 108, СТО ЦКТИ 720, ОСТ 34-10-752…).
  Норматив проверяется в разделе своего изделия: «опоры серия 4.903» уходят,
  хотя тройники этой серии в каталоге есть;
* **нормативы, которых в фасетах нет вовсе** — ТС, МН, ТУ, АТК, МВН,
  серия 5.903;
* **изделие не из каталога** — угольники, полуотводы, подвески, муфты,
  патрубки, компенсаторы, сгоны, ниппели (сверено с товарами на проде);
* **то, что не рекламируем** — фланцы, крепёж, изоляция и ППУ, арматура.

Если в группе не остаётся ни одной активной фразы, останавливаются и её
объявления: иначе группа продолжит показываться по автотаргетингу, а его
в ЕПК не выключить.

    python scripts/ads/direct_prune_catalog.py          # план → perf-reports/ads/<дата>/prune-plan.csv
    python scripts/ads/direct_prune_catalog.py apply
"""
import collections
import csv
import json
import os
import re
import sys
import time

import requests

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from wordstat_aes import chain  # noqa: E402

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


# Изделие → разделы каталога, где искать его нормативы.
PRODUCT_SECTIONS = [
    (r"\bотвод|\bколен", ["/catalog/sdt/otvody/"]),
    (r"\bтройник", ["/catalog/sdt/troyniki/"]),
    (r"\bпереход(?!ник)", ["/catalog/sdt/perekhody/"]),
    (r"\bзаглушк|\bбобышк|\bпробк|\bштуцер", ["/catalog/sdt/zaglushki/", "/catalog/tochenye/"]),
    (r"\bднищ|\bдонышк", ["/catalog/sdt/dnishcha/"]),
    (r"\bопор", ["/catalog/opory/"]),
    (r"\bтруб(?!опровод)", ["/catalog/truby/"]),
]
# Нормативы, которых в фасетах каталога нет как класса.
FOREIGN_NORMS = re.compile(r"(?<![а-я])(тс[\s-]?\d|мн\s?\d{3}|\+?ту\s?\d|атк\b|мвн\b|серия\s*5[\s.]?903)")
# Изделия, которых нет среди товаров (проверено по названиям на проде 14.09.2026).
FOREIGN_PRODUCTS = re.compile(r"\b(угольник|полуотвод|подвеск|муфт|патрубк|патрубок|компенсатор|сгон|ниппел|седелк|катушк)")
# Не рекламируем по решению заказчика.
NOT_ADVERTISED = re.compile(r"\b(фланец|фланцы|фланца|фланцев|болт|гайк|шпильк|шайб|крепеж|"
                            r"изоляц|ппу|задвижк|кран|клапан|арматур)")
# Кампании, где опустевшие группы не останавливаем: автотаргетинг в них
# приводит запросы по трубам из каталога (ГОСТ 8732, 10704) — лучший трафик
# аккаунта, а фразы там были по ТУ и магистральным трубам, которых нет.
KEEP_EMPTY_GROUPS = {714272511}   # Трубы | Поиск | Россия

# ОСТ, записанный слитно: «ост 3410699-97» = ОСТ 34-10-699.
COMPACT_OST = re.compile(r"\bост\s*(34)(10|42)(\d{3})\b|\bост\s*(24)(125)(\d{2})\b|\bост\s*(36)(\d{2})(\d{2})\b")


def expand_compact(low):
    return COMPACT_OST.sub(lambda m: "ост " + "-".join(g for g in m.groups() if g), low)


def catalog_chains():
    files = sorted(__import__("glob").glob(os.path.join(ROOT, "perf-reports", "ads", "*", "facets-full.json")))
    data = json.load(open(files[-1], encoding="utf-8"))
    sections = data.get("sections", data)
    by_section = collections.defaultdict(set)
    for path, f in sections.items():
        for slug in f.get("gost", []):
            p = slug.split("-")
            if p[0] == "gost":
                ch = ("гост", p[1])
            elif p[0] == "ost":
                ch = ("ост", " ".join(p[1:4]))
            elif p[0] == "seriya":
                ch = ("серия", " ".join(p[1:4]))
            elif p[0] == "sto":
                rest = [x for x in p[1:] if x not in ("sro", "p") and not re.fullmatch(r"(19|20)\d\d", x)]
                ch = ("сто", " ".join(rest[:3]))
            else:
                continue
            by_section[path].add(ch)
    return by_section


def in_catalog(ch, allowed):
    kind, num = ch
    for k, full in allowed:
        if k == kind and (full == num or full.startswith(num + " ")):
            return True
    return False


def verdict(phrase, by_section, everything):
    low = expand_compact(phrase.split(" -")[0].lower().replace("ё", "е"))
    m = NOT_ADVERTISED.search(low)
    # «заглушка фланцевая» — заглушка из каталога (ГОСТ 22815, ОСТ 34-10-428), не фланец
    if m and not (m.group(1).startswith("фланц") and "заглушк" in low):
        return "не рекламируем: " + m.group(1)
    if FOREIGN_PRODUCTS.search(low):
        return "нет изделия в каталоге: " + FOREIGN_PRODUCTS.search(low).group(1)
    m = FOREIGN_NORMS.search(low)
    if m:
        return "норматива нет в каталоге: " + m.group(1).strip()
    # «гост 2001 тройники» — год, а не номер ГОСТа
    chains = [c for c in chain(low) if not (c[0] == "гост" and re.fullmatch(r"(19|20)\d\d", c[1]))]
    if not chains:
        return ""
    sections = [s for pat, secs in PRODUCT_SECTIONS if re.search(pat, low) for s in secs]
    allowed = set().union(*(by_section[s] for s in sections)) if sections else everything
    for ch in chains:
        if not in_catalog(ch, allowed):
            where = "в разделе изделия" if sections and in_catalog(ch, everything) else "в каталоге"
            return f"норматива нет {where}: {ch[0]} {ch[1]}"
    return ""


def main():
    apply = len(sys.argv) > 1 and sys.argv[1] == "apply"
    by_section = catalog_chains()
    everything = set().union(*by_section.values())

    camps = call("campaigns", "get", {"SelectionCriteria": {"States": ["ON"]}, "FieldNames": ["Id", "Name"]})["Campaigns"]
    names = {c["Id"]: c["Name"] for c in camps}
    ids = list(names)
    kws = []
    for i in range(0, len(ids), 10):
        offset = 0
        while True:
            res = call("keywords", "get", {"SelectionCriteria": {"CampaignIds": ids[i:i + 10], "States": ["ON"]},
                                           "FieldNames": ["Id", "Keyword", "CampaignId", "AdGroupId"],
                                           "Page": {"Limit": 10000, "Offset": offset}})
            kws += res.get("Keywords", [])
            if "LimitedBy" not in res:
                break
            offset = res["LimitedBy"]

    plan, keep_by_group = [], collections.Counter()
    for k in kws:
        if k["Keyword"].startswith("---"):
            continue
        why = verdict(k["Keyword"], by_section, everything)
        if why:
            plan.append({"campaign": names[k["CampaignId"]], "campaign_id": k["CampaignId"], "group_id": k["AdGroupId"],
                         "keyword_id": k["Id"], "keyword": k["Keyword"], "reason": why})
        else:
            keep_by_group[k["AdGroupId"]] += 1

    # Группы, в которых не останется ни одной фразы.
    pruned_groups = {(p["group_id"], p["campaign_id"]) for p in plan}
    empty = sorted(g for g, cid in pruned_groups if keep_by_group[g] == 0 and cid not in KEEP_EMPTY_GROUPS)
    group_names = {}
    for i in range(0, len(empty), 1000):
        for g in call("adgroups", "get", {"SelectionCriteria": {"Ids": empty[i:i + 1000]}, "FieldNames": ["Id", "Name", "CampaignId"]})["AdGroups"]:
            group_names[g["Id"]] = (names.get(g["CampaignId"], ""), g["Name"])

    day = time.strftime("%Y-%m-%d")
    dest_dir = os.path.join(ROOT, "perf-reports", "ads", day)
    os.makedirs(dest_dir, exist_ok=True)
    with open(os.path.join(dest_dir, "prune-plan.csv"), "w", encoding="utf-8-sig", newline="") as f:
        w = csv.DictWriter(f, fieldnames=["campaign", "reason", "keyword", "keyword_id", "group_id", "campaign_id"])
        w.writeheader()
        w.writerows(sorted(plan, key=lambda p: (p["campaign"], p["reason"], p["keyword"])))

    print(f"активных фраз: {sum(1 for k in kws if not k['Keyword'].startswith('---'))}, к остановке: {len(plan)}")
    by_camp = collections.defaultdict(collections.Counter)
    for p in plan:
        by_camp[p["campaign"]][re.sub(r":.*", "", p["reason"])] += 1
    for camp, c in sorted(by_camp.items(), key=lambda x: -sum(x[1].values())):
        print(f"  {camp[:45]:45} {sum(c.values()):>4}  " + ", ".join(f"{k} {v}" for k, v in c.most_common()))
    detail = collections.Counter(p["reason"] for p in plan)
    print("\nпричины подробно (топ-40):")
    for r, n in detail.most_common(40):
        ex = next(p["keyword"] for p in plan if p["reason"] == r)
        print(f"  {n:>4}  {r:48} напр.: {ex[:60]}")
    print(f"\nгрупп, где не останется фраз (остановлю их объявления): {len(empty)}")
    for g in empty[:40]:
        print(f"  {group_names.get(g, ('', ''))[0][:30]:30} | {group_names.get(g, ('', ''))[1][:50]}")

    if not apply:
        print(f"\nплан: perf-reports/ads/{day}/prune-plan.csv; применить: apply")
        return

    ids_to_stop = [p["keyword_id"] for p in plan]
    errs = 0
    for i in range(0, len(ids_to_stop), 10000):
        r = call("keywords", "suspend", {"SelectionCriteria": {"Ids": ids_to_stop[i:i + 10000]}})
        errs += sum(len(x.get("Errors") or []) for x in r.get("SuspendResults", []))
    print(f"фразы остановлены: {len(ids_to_stop)}, ошибок {errs}")
    if empty:
        ads = []
        for i in range(0, len(empty), 1000):
            ads += [a["Id"] for a in call("ads", "get", {"SelectionCriteria": {"AdGroupIds": empty[i:i + 1000], "States": ["ON"]},
                                                        "FieldNames": ["Id"]}).get("Ads", [])]
        for i in range(0, len(ads), 10000):
            r = call("ads", "suspend", {"SelectionCriteria": {"Ids": ads[i:i + 10000]}})
            errs += sum(len(x.get("Errors") or []) for x in r.get("SuspendResults", []))
        print(f"объявления опустевших групп остановлены: {len(ads)}")
    json.dump({"keywords": ids_to_stop, "empty_groups": empty}, open(os.path.join(dest_dir, "prune-applied.json"), "w"), indent=1)

    left = call("keywords", "get", {"SelectionCriteria": {"Ids": ids_to_stop[:10000]}, "FieldNames": ["Id", "State"]})["Keywords"]
    print("проверка:", collections.Counter(k["State"] for k in left))


if __name__ == "__main__":
    main()
