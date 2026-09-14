# -*- coding: utf-8 -*-
"""Пробелы по соединительным деталям: что добавить в рекламу.

Заказчик 14.09.2026 доверил выбор; акцент — детали трубопроводов. Кандидаты
взяты из Wordstat-сверок (разделы 30–31 PLAN-DIRECT.md), только изделия
из каталога: отводы, тройники, переходы, заглушки, днища. Трубы, фланцы,
крепёж, изоляция — нет.

Для каждого кандидата скрипт проверяет:

* спрос (Wordstat, топ-формулировки);
* долю поиска документа (скачать, pdf, текст, статус);
* сколько частоты топ-10 формулировок уже ловят активные фразы с учётом
  минусов (та же сверка, что в wordstat_aes.py).

Добавляется только то, что ловится меньше чем на 50%. Фраза кладётся в
группу с подходящей посадочной; где такой нет — новая группа с объявлением,
скопированным с объявления той же кампании (быстрые ссылки, уточнения,
организация), с другим заголовком и ссылкой.

    python scripts/ads/direct_detail_gaps.py          # план
    python scripts/ads/direct_detail_gaps.py apply
"""
import json
import os
import re
import sys
import time

import requests

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from wordstat_gaps import ROOT, env, wordstat, load_direct, tokens, same  # noqa: E402

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

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


OTVODY, TROYNIKI, PEREKHODY, AES = 714272467, 714272480, 714272489, 714275030
SITE = "https://prom-en.com"
UTM = ("utm_source=yandex&utm_medium=cpc&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{phrase_id}_{retargeting_id}&utm_term={keyword}")
DOC = re.compile(r"скачат|pdf|пдф|текст|читать|статус|действу|взамен|редакц|документ")

# (фраза, кампания, группа — имя существующей или спецификация новой)
CANDIDATES = [
    # Голые основные ГОСТы: «гост 17375» ловит и «отвод гост 17375 2001 90»
    ("гост 17375", OTVODY, "ГОСТ 17375"),
    ("гост 17376", TROYNIKI, "ГОСТ 17376"),
    ("гост 17378", PEREKHODY, "ГОСТ 17378"),
    ("гост 30753", OTVODY, {"name": "ГОСТ 30753", "href": "/catalog/sdt/otvody/?gost=gost-30753-2001",
                            "title": "Отводы ГОСТ 30753-2001. Завод-изготовитель"}),
    # Производитель / завод — прямой сигнал «ищу завод»
    # Отсеяны по формулировкам Wordstat: «переходы производитель» — «переход на
    # отечественного производителя», «днища завод/производитель» — днища машин,
    # лодок и квадроциклов, «тройники завод» — рыболовные тройники.
    ("отводы производитель", OTVODY, "общие"),
    ("отводы завод", OTVODY, "общие"),
    ("тройники производитель -эра -бренд -рыболовный -пластиковый", TROYNIKI, "общие"),
    ("заглушки производитель -пластиковый -мебельный -копыто -пвх", AES, "Заглушки: прочие стандарты"),
    # «ст 20» — Директ не считает его «сталью 20», а кампания ловит только «сталь 20»
    ("отводы ст 20", AES, "Отводы · сталь 20"),
    ("тройники ст 20", AES, "Тройники · сталь 20"),
    ("переход ст 20", AES, {"name": "Переходы · сталь 20", "href": "/catalog/sdt/perekhody/?steel=20",
                            "title": "Переходы из стали 20. Челябинский завод"}),
    ("заглушки ст 20", AES, {"name": "Заглушки · сталь 20", "href": "/catalog/sdt/zaglushki/?steel=20",
                             "title": "Заглушки из стали 20. Челябинский завод"}),
    # Эллиптические — это и есть ГОСТ 6533 и ГОСТ 17379
    ("днище эллиптическое", AES, "Норматив · ГОСТ 6533-78"),
    ("заглушка эллиптическая", AES, "Норматив · ГОСТ 17379-2001"),
    # Семейства энергетических нормативов без номера
    ("отводы ост 34", AES, "Отводы: прочие стандарты"),
    ("тройники ост 34", AES, "Тройники: прочие стандарты"),
    ("переходы ост 34", AES, "Переходы: прочие стандарты"),
    # «заглушки ост 34» не берём: запросы про ОСТ 34-10-758, его нет в каталоге
    ("отводы сто цкти", AES, "Отводы: прочие стандарты"),
]


def main():
    apply = len(sys.argv) > 1 and sys.argv[1] == "apply"
    key = env("YANDEX_SEARCH_API_KEY") or env("YANDEX_CLOUD_API_KEY")
    folder = env("YANDEX_CLOUD_FOLDER_ID")

    cache = os.path.join(ROOT, "perf-reports", "ads", "2026-09-14", "wordstat-details-raw.json")
    raw = json.load(open(cache, encoding="utf-8")) if os.path.exists(cache) else {}
    for phrase, _, _ in CANDIDATES:
        seed = phrase.split(" -")[0]
        if seed not in raw:
            total, rows = wordstat(seed, key, folder, 50)
            raw[seed] = {"total": total, "rows": rows}
            time.sleep(0.25)
    json.dump(raw, open(cache, "w", encoding="utf-8"), ensure_ascii=False)

    camps, kws, camp_sets, sets = load_direct()

    def singles(items):
        out = []
        for n in items or []:
            w = [x for x in re.split(r"[\s\-]+", re.sub(r"[!+\"\[\]]", "", n.lower())) if x]
            if len(w) == 1:
                out.append(w[0])
        return out

    camp_neg = {c["Id"]: singles((c.get("NegativeKeywords") or {}).get("Items"))
                + [w for sid in camp_sets.get(c["Id"], []) for w in singles(sets.get(sid))] for c in camps}
    phrases = []
    for k in kws:
        if k["Keyword"].startswith("---"):
            continue
        base = tokens(k["Keyword"])
        own = [tokens(m)[0] for m in re.findall(r"\s-([^\s]+)", k["Keyword"].lower()) if tokens(m)]
        if base:
            phrases.append((k["CampaignId"], base, own))

    def caught(query):
        toks = tokens(query.replace(".", " "))
        for cid, base, own in phrases:
            if all(any(same(t, w) for t in toks) for w in base) and not any(any(same(t, m) for t in toks) for m in own):
                if not any(any(same(t, n) for t in toks) and not any(same(n, b) for b in base) for n in camp_neg[cid]):
                    return True
        return False

    groups = {}
    for cid in (OTVODY, TROYNIKI, PEREKHODY, AES):
        for g in call("adgroups", "get", {"SelectionCriteria": {"CampaignIds": [cid]}, "FieldNames": ["Id", "Name"]})["AdGroups"]:
            groups[(cid, g["Name"])] = g["Id"]

    plan = []
    print(f"{'фраза':44} {'спрос':>6} {'док':>4} {'ловим':>5}  решение")
    for phrase, cid, target in CANDIDATES:
        seed = phrase.split(" -")[0]
        total, rows = raw[seed]["total"], raw[seed]["rows"]
        top = rows[:10]
        tsum = sum(c for _, c in top) or 1
        cov = round(100 * sum(c for p, c in top if caught(p)) / tsum)
        doc = round(100 * sum(c for p, c in rows[1:] if DOC.search(p)) / max(total, 1))
        name = target if isinstance(target, str) else target["name"]
        exists = (cid, name) in groups
        if cov >= 50:
            decision = "не нужно — уже ловим"
        elif isinstance(target, str) and not exists:
            decision = f"НЕТ ГРУППЫ «{name}»"
        else:
            decision = f"добавить → «{name}»" + ("" if exists else " (новая группа)")
            plan.append((phrase, cid, target, exists))
        print(f"{phrase[:44]:44} {total:>6} {doc:>3}% {cov:>4}%  {decision}")
        print(f"{'':44}        {'; '.join(f'{p} ({c})' for p, c in rows[1:5])[:110]}")

    print(f"\nк добавлению: {len(plan)}")
    if not apply:
        print("применить: apply")
        return

    ref_ads = {}
    for cid in (OTVODY, TROYNIKI, PEREKHODY, AES):
        a = call("ads", "get", {"SelectionCriteria": {"CampaignIds": [cid], "States": ["ON"], "Types": ["TEXT_AD"]},
                                "FieldNames": ["Id"], "TextAdFieldNames": ["Text", "DisplayUrlPath", "BusinessId", "SitelinkSetId",
                                                                            "AdExtensions", "AdImageHash"], "Page": {"Limit": 1}})["Ads"][0]
        ref_ads[cid] = a["TextAd"]

    new_ads, added = [], []
    for phrase, cid, target, exists in plan:
        name = target if isinstance(target, str) else target["name"]
        if (cid, name) not in groups:
            r = call("adgroups", "add", {"AdGroups": [{"Name": name, "CampaignId": cid, "RegionIds": [225]}]})
            e = errors(r, "AddResults")
            if e:
                print(f"  группа «{name}»: {e}")
                continue
            gid = r["AddResults"][0]["Id"]
            groups[(cid, name)] = gid
            ref = ref_ads[cid]
            ad = {"Title": target["title"], "Text": ref["Text"], "Href": f"{SITE}{target['href']}{'&' if '?' in target['href'] else '?'}{UTM}",
                  "Mobile": "NO", "DisplayUrlPath": ref.get("DisplayUrlPath"), "BusinessId": ref.get("BusinessId"),
                  "PreferVCardOverBusiness": "NO", "SitelinkSetId": ref.get("SitelinkSetId"),
                  "AdExtensionIds": [x["AdExtensionId"] for x in ref.get("AdExtensions") or []]}
            if ref.get("AdImageHash"):
                ad["AdImageHash"] = ref["AdImageHash"]
            ad = {k: v for k, v in ad.items() if v not in (None, [])}
            r = call("ads", "add", {"Ads": [{"AdGroupId": gid, "TextAd": ad}]})
            e = errors(r, "AddResults")
            if e:
                print(f"  объявление «{name}»: {e}")
                continue
            new_ads.append(r["AddResults"][0]["Id"])
            print(f"  новая группа «{name}» ({gid}), объявление «{target['title']}»")
        gid = groups[(cid, name)]
        r = call("keywords", "add", {"Keywords": [{"AdGroupId": gid, "Keyword": phrase}]})
        e = errors(r, "AddResults")
        print(f"  «{phrase}» → «{name}»:", e or "ок")
        if not e:
            added.append(r["AddResults"][0]["Id"])

    if new_ads:
        r = call("ads", "moderate", {"SelectionCriteria": {"Ids": new_ads}})
        print(f"на модерацию объявлений: {len(new_ads)}", errors(r, "ModerateResults") or "ок")
    if added:
        got = call("keywords", "get", {"SelectionCriteria": {"Ids": added}, "FieldNames": ["Keyword", "State", "Status"]})["Keywords"]
        print(f"проверка: {len(got)} из {len(added)} фраз на месте; статусы {sorted({(k['State'], k['Status']) for k in got})}")
    json.dump({"keywords": added, "ads": new_ads}, open(os.path.join(ROOT, "perf-reports", "ads", "2026-09-14",
              "detail-gaps-applied.json"), "w"), indent=1)


if __name__ == "__main__":
    main()
