# -*- coding: utf-8 -*-
"""Карта фасетов каталога: что вообще можно рекламировать.

Прежде чем собирать семантику, надо знать, какие срезы каталога существуют
и чем наполнены. У PROM-EN нашлись `gost` (127 значений), `steel` (35),
`industry` (4: АЭС, ТЭС, ЖКХ, НГК), `angle` (6 углов отвода) и `group`
(33 подкатегории) — половину из них я в первый раз не заметил и потерял
на этом целый пласт спроса.

Обходит разделы, вытаскивает значения параметров фильтра из HTML и
сохраняет карту в JSON, который потом читают сборщики кампаний.

**Счётчик позиций из HTML брать нельзя** — он статичен и всегда показывает
общее число раздела. Реальное наполнение фасета видно только в браузере,
после отработки JS. Скрипт это и не пытается делать: он собирает состав
фасетов, а наполнение проверяется отдельно, выборочно.

    python scripts/ads/site_facets.py --site https://prom-en.com
    python scripts/ads/site_facets.py --site https://example.ru --roots /catalog/
"""
import argparse
import collections
import json
import os
import re
import sys
import urllib.parse as up

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
UA = {"User-Agent": "Mozilla/5.0 (compatible; promen-ads/1.0)"}

# Служебные параметры, которые фасетами не являются.
SKIP = {"utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term",
        "ver", "s", "p", "page", "sort", "order", "replytocom", "_"}


def fetch(url):
    try:
        r = requests.get(url, timeout=45, headers=UA)
        return r.status_code, r.text
    except Exception as e:
        return None, str(e)[:60]


# Пагинация, RSS и карточки товаров — не разделы. Карточку узнаём по
# цифрам в последнем сегменте («otvod-108h4-…») и по его длине: имена
# разделов короткие и без размеров.
NOT_SECTION = re.compile(r"/(page|feed|comment-page-\d+|attachment)/")


def is_section(path):
    if NOT_SECTION.search(path):
        return False
    last = [x for x in path.split("/") if x]
    if not last:
        return True
    tail = last[-1]
    return not re.search(r"\d", tail) and len(tail) <= 24


def section_links(html, site, root):
    """Ссылки на подразделы каталога — чтобы не перечислять их руками."""
    out = set()
    # Ищем пути по всему документу, а не только в href: на этом сайте
    # разделы каталога попадают в HTML через data-атрибуты и JSON, и
    # выборка по href нашла ровно одну ссылку из двенадцати разделов.
    pattern = re.escape(root) + r'[a-z0-9\-]+/(?:[a-z0-9\-]+/)?'
    for m in re.finditer(pattern, html):
        path = m.group(0)
        if (path.startswith(root) and path.endswith("/")
                and path.count("/") <= root.count("/") + 2 and is_section(path)):
            out.add(path)
    return out


def facets_of(html):
    params = collections.defaultdict(set)
    for m in re.finditer(r'[?&]([a-z_]+)=([a-z0-9\-\.]+)', html):
        key, value = m.group(1), m.group(2)
        if key in SKIP:
            continue
        params[key].add(value)
    return {k: sorted(v) for k, v in params.items()}


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--site", required=True, help="например https://prom-en.com")
    ap.add_argument("--roots", default="/catalog/", help="корни каталога через запятую")
    ap.add_argument("--out", default="")
    ap.add_argument("--limit", type=int, default=40,
                    help="потолок числа разделов: каталог может ссылаться сам на себя")
    a = ap.parse_args()

    site = a.site.rstrip("/")
    roots = [r if r.startswith("/") else "/" + r for r in a.roots.split(",")]

    # Сначала корни, потом найденные в них подразделы: так карта строится
    # сама и не зависит от того, помню ли я структуру конкретного сайта.
    todo, seen, out = list(roots), set(), {}
    while todo and len(out) < a.limit:
        path = todo.pop(0)
        if path in seen:
            continue
        seen.add(path)
        code, html = fetch(site + path)
        if code != 200:
            print(f'  {path:36} http {code}', flush=True)
            continue
        f = facets_of(html)
        out[path] = f
        found = section_links(html, site, path) - seen
        todo.extend(sorted(found))
        summary = ", ".join(f'{k}({len(v)})' for k, v in sorted(f.items())) or "фасетов нет"
        print(f'  {path:36} {summary}', flush=True)

    dest = a.out or os.path.join(ROOT, "perf-reports", "ads", "facets.json")
    os.makedirs(os.path.dirname(dest), exist_ok=True)
    json.dump({"site": site, "sections": out}, open(dest, "w", encoding="utf-8"),
              ensure_ascii=False, indent=1)
    print(f"\nразделов обойдено: {len(out)}")

    all_keys = collections.Counter()
    for f in out.values():
        for k, v in f.items():
            all_keys[k] += len(v)
    print("фасеты по всему каталогу:")
    for k, n in all_keys.most_common():
        print(f'  {k:12} значений суммарно {n}')
    print(f"\nсохранено: {os.path.relpath(dest, ROOT)}")
    print("\nДальше: наполнение фасета проверять в браузере — серверный HTML")
    print("отдаёт общий счётчик раздела и не меняется от фильтра.")


if __name__ == "__main__":
    main()
