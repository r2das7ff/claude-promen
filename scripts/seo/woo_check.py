#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Проверка специфики WordPress + WooCommerce на B2B-каталоге без цен.

То, что общие SEO-инструменты либо не проверяют, либо проверяют неверно,
потому что ждут магазин с ценами и корзиной.

    python woo_check.py https://prom-en.forgotaboutdre.ru
    python woo_check.py https://prom-en.forgotaboutdre.ru --products 20 --json out.json
"""
import argparse
import json
import re
import sys
from urllib.parse import urljoin

import requests
from lxml import html as lhtml

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

UA = "Mozilla/5.0 (compatible; PromenSEOBot/1.0; +local audit)"
S = requests.Session()
S.headers["User-Agent"] = UA

RESULTS = []


def add(sev, name, ok, detail=""):
    RESULTS.append({"sev": sev, "name": name, "ok": ok, "detail": detail})


def get(url, **kw):
    kw.setdefault("timeout", 30)
    try:
        return S.get(url, **kw)
    except Exception as e:
        return None


def meta_of(resp):
    """canonical, meta robots, title, JSON-LD одной страницы."""
    if resp is None or "html" not in (resp.headers.get("Content-Type") or ""):
        return {}
    doc = lhtml.fromstring(resp.content)
    one = lambda xp: (doc.xpath(xp) or [None])[0]
    blobs = []
    for s in doc.xpath("//script[@type='application/ld+json']/text()"):
        try:
            data = json.loads(s)
            blobs += data if isinstance(data, list) else [data]
        except Exception:
            blobs.append({"@type": "PARSE_ERROR"})
    return {
        "status": resp.status_code,
        "title": (one("//title/text()") or "").strip(),
        "canonical": one("//link[@rel='canonical']/@href"),
        # Тегов robots на странице бывает несколько (ядро отдаёт свой
        # max-image-preview, тема — свой noindex). Берём все: по первому
        # проверка врала, что страница открыта.
        "robots": ", ".join(doc.xpath("//meta[translate(@name,'ROBTS','robts')='robots']/@content")),
        "x_robots": resp.headers.get("X-Robots-Tag"),
        "jsonld": blobs,
    }


def noindexed(m):
    return "noindex" in ((m.get("robots") or "") + (m.get("x_robots") or "")).lower()


# ---------------------------------------------------------------- robots.txt
def check_robots(root):
    r = get(root + "/robots.txt")
    if r is None or r.status_code != 200:
        add(1, "robots.txt отдаётся", False, f"status={getattr(r, 'status_code', 'нет ответа')}")
        return ""
    body = r.text
    add(1, "robots.txt отдаётся", True)
    add(1, "В robots.txt указан Sitemap", "sitemap:" in body.lower(),
        "" if "sitemap:" in body.lower() else "робот ищет карту наугад")
    add(2, "Clean-param для Яндекса", "clean-param" in body.lower(),
        "нет директивы — фасеты и utm плодят дубли в индексе Яндекса")
    add(2, "Закрыт add-to-cart", "add-to-cart" in body,
        "параметр корзины должен быть закрыт")
    add(3, "Закрыт поиск по сайту (?s=)", bool(re.search(r"Disallow:.*\?s=|Disallow:.*/search", body)),
        "страницы поиска — классический источник мусора в индексе")
    return body


# ------------------------------------------------------------------ sitemap
def check_sitemap(root, robots_body):
    m = re.search(r"(?im)^\s*sitemap:\s*(\S+)", robots_body or "")
    sm_url = m.group(1) if m else root + "/wp-sitemap.xml"
    r = get(sm_url)
    if r is None or r.status_code != 200:
        add(1, "Sitemap доступен", False, sm_url)
        return []
    add(1, "Sitemap доступен", True, sm_url)
    body = r.text
    subs = re.findall(r"<loc>\s*([^<\s]+)\s*</loc>", body)
    urls, cats, lastmods = [], [], 0
    if "<sitemapindex" in body:
        add(3, "Sitemap разбит на индекс", True, f"{len(subs)} подкарт")
        for s in subs:
            rr = get(s)
            if rr is None:
                continue
            found = re.findall(r"<loc>\s*([^<\s]+)\s*</loc>", rr.text)
            urls += found
            # категории берём только из карты таксономии — по виду URL их
            # не отличить от товаров: во «фланцах» товары лежат прямо в корне раздела
            if "product_cat" in s:
                cats += found
            lastmods += rr.text.count("<lastmod>")
    else:
        urls = subs
        lastmods = body.count("<lastmod>")
    add(1, "URL в sitemap", bool(urls), f"{len(urls)} адресов, из них категорий {len(cats)}")
    add(2, "Есть lastmod", lastmods > 0,
        "без lastmod робот не понимает, что обновилось — на 15k URL это дорого")
    junk = [u for u in urls if re.search(r"/(cart|checkout|my-account|sample-page)/", u)]
    add(1, "Служебных страниц Woo нет в sitemap", not junk, ", ".join(junk[:5]))
    return urls, cats


# ---------------------------------------------------- служебные страницы WP/Woo
SERVICE = ["/cart/", "/checkout/", "/my-account/", "/sample-page/",
           "/feed/", "/wp-json/", "/?s=test", "/comments/feed/"]


def check_service(root, robots_body=""):
    for path in SERVICE:
        r = get(root + path, allow_redirects=False)
        if r is None:
            continue
        st = r.status_code
        if st in (301, 302, 404, 410, 401, 403):
            add(3, f"Служебная {path}", True, f"{st} — в индекс не попадёт")
            continue
        m = meta_of(r) if st == 200 else {}
        # Закрыта либо мета-тегом, либо строкой Disallow в robots.txt —
        # иначе получаем ложную тревогу по /feed/ и /wp-json/.
        rule = path.split("?")[0].rstrip("/") or "/"
        in_robots = any(
            line.strip().lower().startswith("disallow:") and rule.strip("/") in line
            for line in (robots_body or "").splitlines())
        closed = noindexed(m) or in_robots
        how = "noindex" if noindexed(m) else ("robots.txt" if in_robots else "")
        add(2, f"Служебная {path}", closed,
            f"{st}, {how or 'открыта: ни noindex, ни Disallow'}")


# ------------------------------------------------------------- пагинация
def check_pagination(root, category):
    p1, p2 = category, category.rstrip("/") + "/page/2/"
    m1, m2 = meta_of(get(p1)), meta_of(get(p2))
    if not m2 or m2.get("status") != 200:
        add(3, "Пагинация категории", True, "второй страницы нет — проверять нечего")
        return
    can = m2.get("canonical") or ""
    add(1, "Canonical пагинации не схлопнут на первую", can.rstrip("/") == p2.rstrip("/"),
        f"page/2 canonical -> {can or 'нет'}; склейка на первую страницу прячет товары со 2+")
    add(2, "Title пагинации отличается от первой страницы",
        (m1.get("title") or "") != (m2.get("title") or ""),
        "одинаковый заголовок на 109 страницах пагинации = 109 дублей")
    add(3, "Пагинация не закрыта noindex", not noindexed(m2),
        "закрытая пагинация обрывает роботу путь к карточкам")


# ------------------------------------------------------------- параметры/фасеты
PARAMS = ["?orderby=popularity", "?utm_source=test", "?add-to-cart=1", "?unknownparam=1"]


def check_params(root, category):
    base = category.rstrip("/") + "/"
    for q in PARAMS:
        m = meta_of(get(base + q))
        if not m:
            continue
        can = (m.get("canonical") or "").rstrip("/")
        ok = can == base.rstrip("/") or noindexed(m)
        add(1 if q.startswith("?utm") else 2, f"Параметр {q} не плодит дубль", ok,
            f"canonical -> {m.get('canonical') or 'нет'}, robots={m.get('robots') or 'нет'}")


# ------------------------------------------------- гигиена <head> на выборке
def count_tags(url):
    r = get(url)
    if r is None or r.status_code != 200:
        return None
    head = r.text.split("</head>")[0]
    return {
        "canonical": len(re.findall(r"<link[^>]+rel=[\"']canonical[\"']", head, re.I)),
        "description": len(re.findall(r"<meta[^>]+name=[\"']description[\"']", head, re.I)),
        "title": len(re.findall(r"<title", head, re.I)),
        "robots": len(re.findall(r"<meta[^>]+name=[\"']robots[\"']", head, re.I)),
    }


def check_head(root, cats, urls, n=8):
    """Один и тот же тег, выведенный дважды, — типовая беда самописной темы:
    один вывод в шаблоне, второй в хуке wp_head. Проверяем по типам страниц."""
    prods = [u for u in urls if re.search(r"/catalog/[^/]+/[^/]+/[^/]+/?$", u)][:n]
    groups = {"главная": [root + "/"], "категории": cats[:n], "товары": prods}
    for label, sample in groups.items():
        stats = [count_tags(u) for u in sample]
        stats = [s for s in stats if s]
        if not stats:
            continue
        for tag, want in [("canonical", 1), ("description", 1), ("title", 1)]:
            bad = [s[tag] for s in stats if s[tag] != want]
            if not bad:
                add(3, f"<{tag}> на страницах «{label}»", True, f"по одному на {len(stats)} страницах")
            else:
                kind = "отсутствует" if all(b == 0 for b in bad) else f"выводится {max(bad)} раза"
                add(1 if tag == "canonical" else 2, f"<{tag}> на страницах «{label}»", False,
                    f"{kind} на {len(bad)} из {len(stats)} проверенных")


# ------------------------------------------------------- микроразметка товара
def check_products(root, urls, n):
    prods = [u for u in urls if re.search(r"/catalog/[^/]+/[^/]+/[^/]+/?$", u)][:n]
    if not prods:
        add(2, "Товарные карточки найдены", False, "не нашёл в sitemap — проверить вручную")
        return
    stats = {"product": 0, "breadcrumb": 0, "sku": 0, "brand": 0, "image": 0,
             "offers": 0, "zero_price": 0, "no_price": 0, "parse_error": 0, "desc": 0}
    for u in prods:
        m = meta_of(get(u))
        types = []
        for b in m.get("jsonld", []):
            t = b.get("@type")
            types += t if isinstance(t, list) else [t]
            if t == "PARSE_ERROR":
                stats["parse_error"] += 1
            if t == "Product":
                stats["product"] += 1
                for k, f in [("sku", "sku"), ("brand", "brand"), ("image", "image"),
                             ("desc", "description")]:
                    if b.get(f):
                        stats[k] += 1
                off = b.get("offers")
                if off:
                    stats["offers"] += 1
                    o = off[0] if isinstance(off, list) else off
                    price = o.get("price")
                    if price is None and not o.get("priceSpecification"):
                        stats["no_price"] += 1
                    elif str(price) in ("0", "0.0", "0.00", ""):
                        stats["zero_price"] += 1
        if "BreadcrumbList" in types:
            stats["breadcrumb"] += 1
    t = len(prods)
    add(1, "Product-разметка на карточках", stats["product"] == t, f"{stats['product']}/{t}")
    add(2, "BreadcrumbList на карточках", stats["breadcrumb"] == t, f"{stats['breadcrumb']}/{t}")
    add(1, "JSON-LD парсится без ошибок", stats["parse_error"] == 0, f"битых: {stats['parse_error']}")
    add(2, "sku заполнен", stats["sku"] == t, f"{stats['sku']}/{t}")
    add(3, "brand заполнен", stats["brand"] == t, f"{stats['brand']}/{t}")
    add(2, "image в разметке", stats["image"] == t, f"{stats['image']}/{t}")
    add(2, "description в разметке", stats["desc"] == t, f"{stats['desc']}/{t}")
    add(1, "Нет offers с нулевой ценой", stats["zero_price"] == 0,
        f"{stats['zero_price']}/{t} — price=0 читается как «бесплатно»")
    add(2, "Offer без price не отдаётся", stats["no_price"] == 0,
        f"{stats['no_price']}/{t} — Offer обязан нести price или priceSpecification; "
        f"без них валидатор ругается, а расширенный сниппет всё равно не собирается. "
        f"Для каталога без цен offers лучше не выводить вовсе")


# ------------------------------------------------------------- зеркала главной
def check_mirrors(root):
    from urllib.parse import urlparse
    host = urlparse(root).netloc
    variants = {
        "http": root.replace("https://", "http://"),
        "www": f"https://www.{host}/" if not host.startswith("www.") else root,
        "index.php": root + "/index.php",
    }
    for name, u in variants.items():
        r = get(u, allow_redirects=False)
        if r is None:
            add(2, f"Зеркало {name}", False, "нет ответа / нет DNS")
            continue
        ok = r.status_code in (301, 308) or (name == "index.php" and r.status_code == 404)
        add(1 if name != "index.php" else 3, f"Зеркало {name} склеено", ok,
            f"{r.status_code} -> {r.headers.get('Location', '')}")


def pick_category(cats):
    """Первая категория, у которой есть вторая страница пагинации."""
    for u in cats:
        r = get(u.rstrip("/") + "/page/2/", allow_redirects=False)
        if r is not None and r.status_code == 200:
            return u
    return cats[0] if cats else None


def main():
    p = argparse.ArgumentParser(description="Проверка WP+WooCommerce специфики")
    p.add_argument("url")
    p.add_argument("--category", help="категория для проверки пагинации и параметров")
    p.add_argument("--products", type=int, default=10)
    p.add_argument("--json", help="выгрузить результат в JSON")
    a = p.parse_args()
    root = a.url.rstrip("/")

    robots_body = check_robots(root)
    urls, cats = check_sitemap(root, robots_body)
    check_service(root, robots_body)
    check_mirrors(root)
    cat = a.category or pick_category(cats) or root + "/catalog/"
    check_pagination(root, cat)
    check_params(root, cat)
    check_head(root, cats, urls)
    check_products(root, urls, a.products)

    bad = [r for r in RESULTS if not r["ok"]]
    print(f"\n# WooCommerce/WP проверка: {root}\n")
    print(f"Проверок: {len(RESULTS)}, проблем: {len(bad)}. Категория для проб: {cat}\n")
    print("| | Проверка | Детали |")
    print("|---|---|---|")
    for r in sorted(RESULTS, key=lambda x: (x["ok"], x["sev"])):
        mark = "✅" if r["ok"] else {1: "🔴", 2: "🟡", 3: "⚪"}[r["sev"]]
        print(f"| {mark} | {r['name']} | {r['detail'][:110]} |")
    if a.json:
        with open(a.json, "w", encoding="utf-8") as f:
            json.dump(RESULTS, f, ensure_ascii=False, indent=2)
        print(f"\n-> {a.json}")


if __name__ == "__main__":
    main()
