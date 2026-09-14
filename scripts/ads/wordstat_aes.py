# -*- coding: utf-8 -*-
"""Спрос по нормативам АЭС и ТЭС: что искать, что уже крутится, чего нет.

Для каждого норматива из фасетов каталога (ОСТ 24.125, ОСТ 34, ОСТ 36,
СТО ЦКТИ, СТО 79814898, СТО 95, СТО СРО-П, ГОСТ 22790–22826 и базовые ГОСТ
СДТ) и для отраслевых запросов (АЭС, ТЭС, котлы, высокое давление, трубы
для энергетики, ОСТ 108, ПНАЭ, НП) берёт из Wordstat частоту и варианты
написания, а из Директа — есть ли фраза с этим нормативом и где.

Нормативы Wordstat пишет как попало: «ост 34 10.764 97», «ост 34-10-764»,
«ост34 10 764». Поэтому сравнение идёт по цепочке чисел после ГОСТ/ОСТ/СТО,
а не по словам.

    python scripts/ads/wordstat_aes.py
    python scripts/ads/wordstat_aes.py --refresh
"""
import argparse
import json
import os
import re
import sys
import time

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from wordstat_gaps import ROOT, env, wordstat, load_direct, tokens, same  # noqa: E402

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

# Отраслевые запросы без конкретного номера. Трубы — отдельно: по фасетам
# в каталоге только ГОСТ 8732/8734/10704/10705/3262 и марки 09Г2С, 10, 10Г2,
# 17Г1С-У, 20, Ст3сп — котельных труб (12Х1МФ, 15Х1М1Ф, 20-ПВ) там нет.
INDUSTRY = [
    "детали трубопроводов аэс", "детали трубопроводов для атомных станций", "трубопроводы аэс",
    "оборудование для аэс трубопроводы", "детали трубопроводов тэс", "детали трубопроводов тэц",
    "детали трубопроводов пара и горячей воды", "детали паропроводов", "детали трубопроводов котлов",
    "детали трубопроводов высокого давления", "соединительные детали трубопроводов высокого давления",
    "гост 22790", "детали трубопроводов ост", "детали трубопроводов сто", "сто цкти", "цкти детали трубопроводов",
    "ост 34 детали трубопроводов", "ост 24.125", "ост 108", "ост 108.321", "ост 108.318", "ост 108.720",
    "ост 108.462", "пнаэ г-7", "нп-089", "нп-068", "гост р 59115", "детали трубопроводов 12х1мф",
    "детали трубопроводов 15гс", "детали трубопроводов 08х18н10т", "детали трубопроводов 10гн2мфа",
    "детали трубопроводов категории 1", "детали трубопроводов 1 категории",
]
PIPES = [
    "трубы для аэс", "трубы для тэс", "трубы для котлов", "трубы котельные", "трубы котельные высокого давления",
    "трубы для паропроводов", "трубы высокого давления", "трубы ту 14-3р-55", "трубы ту 14-3-460",
    "трубы 12х1мф", "трубы 15х1м1ф", "трубы 20пв", "трубы 15гс", "трубы 16гс", "трубы 10гн2мфа",
    "трубы 08х18н10т", "трубы 12х18н10т", "трубы 09г2с", "трубы сталь 20 гост 8732", "трубы гост 8734",
]


def human(slug):
    """Слаг фасета → как норматив пишут в поиске."""
    p = slug.split("-")
    kind = p[0]
    if kind == "gost":
        return f"гост {p[1]}"
    if kind == "ost":
        if p[1] == "24" and p[2] == "125":
            return f"ост 24.125.{p[3]}"
        return f"ост {p[1]}-{p[2]}-{p[3]}"
    if kind == "sto":
        if p[1] in ("321", "318"):
            return f"сто цкти {p[1]}.{p[2]}"
        if p[1] == "sro":
            return f"сто сро-п {p[3]} {p[4]}"
        return f"сто {p[1]} {p[2]}"
    if kind == "seriya":
        return f"серия {p[1]}.{p[2]}-{p[3]}"
    return slug


def chain(text):
    """Цепочка чисел норматива без года: «ОСТ 34-10-764-97» → ('ост', '34 10 764').

    Год отрезается по структуре, а не по виду числа: у ГОСТ номер — одно
    число, у ОСТ — три («36-22-77»: 22 — часть номера, 77 — год), у СТО
    отбрасываются только четырёхзначные годы («сто 95 115 2013»).
    """
    low = text.lower().replace("ё", "е")
    out = []
    for kind, num in re.findall(r"\b(гост|ост|сто(?:\s*цкти)?|сто\s*сро\s*-?п|серия)\s*(?:р\s*)?(\d[\d.\s-]*\d|\d)", low):
        parts = [x for x in re.split(r"[.\s-]+", num) if x]
        if kind == "гост":
            parts = parts[:1]
        elif kind in ("ост", "серия"):
            parts = parts[:3]
        else:
            kind = "сто"
            parts = [x for x in parts if not re.fullmatch(r"(19|20)\d\d", x)][:3]
        out.append((kind, " ".join(parts)))
    return out


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--refresh", action="store_true")
    ap.add_argument("--num", type=int, default=100)
    a = ap.parse_args()

    key = env("YANDEX_SEARCH_API_KEY") or env("YANDEX_CLOUD_API_KEY")
    folder = env("YANDEX_CLOUD_FOLDER_ID")
    if not key or not folder:
        sys.exit("Нужны YANDEX_CLOUD_API_KEY и YANDEX_CLOUD_FOLDER_ID в .env")

    facets_file = os.path.join(ROOT, "perf-reports", "ads", "2026-09-09", "facets-full.json")
    sections = json.load(open(facets_file, encoding="utf-8"))
    sections = sections.get("sections", sections)
    norms = {}   # человеческое имя → разделы каталога
    for path, f in sections.items():
        if not path.startswith(("/catalog/sdt/", "/catalog/truby/", "/catalog/tochenye/", "/catalog/opory/")):
            continue
        for slug in f.get("gost", []):
            name = human(slug)
            norms.setdefault(name, {"slug": slug, "sections": set()})["sections"].add(path)
    print(f"нормативов в каталоге: {len(norms)}")

    day = time.strftime("%Y-%m-%d")
    dest_dir = os.path.join(ROOT, "perf-reports", "ads", day)
    os.makedirs(dest_dir, exist_ok=True)
    cache = os.path.join(dest_dir, "wordstat-aes-raw.json")
    seeds = sorted(norms) + INDUSTRY + PIPES
    raw = {} if a.refresh or not os.path.exists(cache) else json.load(open(cache, encoding="utf-8"))
    todo = [s for s in seeds if s not in raw]
    for i, s in enumerate(todo, 1):
        total, rows = wordstat(s, key, folder, a.num)
        raw[s] = {"total": total, "rows": rows}
        print(f"  [{i}/{len(todo)}] {s}: {total}", flush=True)
        time.sleep(0.25)
        if i % 20 == 0:
            json.dump(raw, open(cache, "w", encoding="utf-8"), ensure_ascii=False)
    json.dump(raw, open(cache, "w", encoding="utf-8"), ensure_ascii=False)

    camps, kws, camp_sets, sets = load_direct()
    names = {c["Id"]: c["Name"] for c in camps}

    def singles(items):
        out_ = []
        for n in items or []:
            w = [x for x in re.split(r"[\s\-]+", re.sub(r"[!+\"\[\]]", "", n.lower())) if x]
            if len(w) == 1:
                out_.append(w[0])
        return out_

    camp_neg = {}
    for c in camps:
        neg = singles((c.get("NegativeKeywords") or {}).get("Items"))
        for sid in camp_sets.get(c["Id"], []):
            neg += singles(sets.get(sid))
        camp_neg[c["Id"]] = neg
    phrases, have = [], {}
    for k in kws:
        if k["Keyword"].startswith("---"):
            continue
        base = tokens(k["Keyword"])
        own = [tokens(m)[0] for m in re.findall(r"\s-([^\s]+)", k["Keyword"].lower()) if tokens(m)]
        if base:
            phrases.append((k["CampaignId"], base, own))
        for ch in chain(k["Keyword"].split(" -")[0]):
            have.setdefault(ch, []).append(k["CampaignId"])

    def catches(query):
        """Кампании, которые поймают запрос: фраза целиком в запросе, её минусы
        и минус-слова кампании не режут (минус со словом самой фразы не действует)."""
        toks = tokens(query.replace(".", " "))
        live = set()
        for cid, base, own in phrases:
            if all(any(same(t, w) for t in toks) for w in base) and not any(any(same(t, m) for t in toks) for m in own):
                if not any(any(same(t, n) for t in toks) and not any(same(n, b) for b in base) for n in camp_neg[cid]):
                    live.add(cid)
        return live

    def coverage(rows):
        """Доля частоты вариантов (топ-10), которую ловит хоть одна кампания."""
        top = rows[:10]
        total = sum(c for _, c in top) or 1
        got = sum(c for p, c in top if catches(p))
        return round(100 * got / total)

    report = []
    print("\n=== НОРМАТИВЫ КАТАЛОГА ===")
    print(f"{'норматив':30} {'частота':>7} {'фраз':>4} {'ловим':>5}  кампании | варианты")
    for name in sorted(norms, key=lambda n: -raw[n]["total"]):
        total, rows = raw[name]["total"], raw[name]["rows"]
        key_chain = chain(name)[0] if chain(name) else None
        ours = have.get(key_chain, []) if key_chain else []
        cov = coverage(rows) if rows else 0
        where = ", ".join(sorted({names[c][:14] for c in ours})) or "—"
        report.append({"type": "norm", "seed": name, "total": total, "phrases": len(ours), "coverage": cov,
                       "where": where, "sections": sorted(norms[name]["sections"]), "variants": rows[:15]})
        print(f"{name:30} {total:>7} {len(ours):>4} {cov:>4}%  {where} | "
              f"{'; '.join(f'{p} ({c})' for p, c in rows[:3])[:100]}")

    for title, group in (("ОТРАСЛЕВЫЕ ЗАПРОСЫ", INDUSTRY), ("ТРУБЫ ДЛЯ ЭНЕРГЕТИКИ", PIPES)):
        print(f"\n=== {title} ===")
        for s in sorted(group, key=lambda x: -raw[x]["total"]):
            rows = raw[s]["rows"]
            cov = coverage(rows) if rows else 0
            where = ", ".join(sorted({names[c][:14] for c in catches(s)})) or "—"
            report.append({"type": title, "seed": s, "total": raw[s]["total"], "coverage": cov, "where": where,
                           "variants": rows[:15]})
            print(f"{s:42} {raw[s]['total']:>7} {cov:>4}%  {where} | "
                  f"{'; '.join(f'{p} ({c})' for p, c in rows[1:4])[:100]}")

    json.dump(report, open(os.path.join(dest_dir, "wordstat-aes.json"), "w", encoding="utf-8"), ensure_ascii=False, indent=1)
    print(f"\nфайл: perf-reports/ads/{day}/wordstat-aes.json")


if __name__ == "__main__":
    main()
