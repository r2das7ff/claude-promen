#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Разбор базы краулера: 24 проверки, отчёт в markdown + CSV по каждой.

    python report.py --db catalog.db                  # сводка в консоль
    python report.py --db catalog.db --out reports/   # + markdown и CSV
    python report.py --db catalog.db --check dup_title --limit 200

Проверки не «по учебнику», а по тому, что реально ломается на WP+Woo
каталоге на 15k карточек. Severity: 1 — чинить до релиза, 2 — важно,
3 — гигиена.
"""
import argparse
import csv
import os
import sqlite3
import sys

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

HTML = "AND (p.content_type IS NULL OR p.content_type LIKE '%html%')"
# Страницы, которые не удалось разобрать (сервер отдал пустое тело под
# нагрузкой), из проверок содержимого исключаем: иначе они попадают в «нет
# title», «нет canonical» и «нет разметки» разом и портят все цифры.
INDEXABLE = ("AND p.status=200 AND p.error IS NULL "
             "AND COALESCE(p.meta_robots,'') NOT LIKE '%noindex%' "
             "AND COALESCE(p.x_robots,'') NOT LIKE '%noindex%'")

CHECKS = [
    ("http_error", 1, "Ответ 4xx/5xx",
     f"SELECT p.status, p.url FROM pages p WHERE p.status>=400 ORDER BY p.status",
     "Страница отдаёт ошибку. В индексе такие держать нельзя."),

    ("net_error", 1, "Сетевая ошибка при загрузке",
     "SELECT p.error, p.url FROM pages p WHERE p.error IS NOT NULL",
     "Таймаут или обрыв. Если воспроизводится — робот увидит то же самое."),

    ("broken_link", 1, "Внутренние ссылки на битые страницы",
     "SELECT l.dst, count(*) cnt, min(l.src) example_src FROM links l "
     "JOIN pages p ON p.url=l.dst WHERE l.internal=1 AND p.status>=400 "
     "GROUP BY l.dst ORDER BY cnt DESC",
     "Ссылки ведут в 404. Слив краулингового бюджета и веса."),

    ("redirect_chain", 1, "Цепочки редиректов (2+ хопа)",
     "SELECT a.url, a.redirect_to hop1, b.redirect_to hop2 FROM pages a "
     "JOIN pages b ON b.url=a.redirect_to "
     "WHERE a.status BETWEEN 300 AND 399 AND b.status BETWEEN 300 AND 399",
     "Каждый лишний хоп теряет вес и время робота. Схлопнуть в один 301."),

    ("redirect_to_error", 1, "Редирект ведёт на 4xx/5xx",
     "SELECT a.url, a.redirect_to, b.status FROM pages a JOIN pages b ON b.url=a.redirect_to "
     "WHERE a.status BETWEEN 300 AND 399 AND b.status>=400",
     "Редирект в никуда — хуже прямой 404."),

    ("link_to_redirect", 2, "Внутренние ссылки на редиректы",
     "SELECT l.dst, count(*) cnt, min(l.src) example_src FROM links l JOIN pages p ON p.url=l.dst "
     "WHERE l.internal=1 AND p.status BETWEEN 300 AND 399 GROUP BY l.dst ORDER BY cnt DESC",
     "Внутри сайта надо ссылаться на конечный адрес, а не через редирект."),

    ("no_title", 1, "Пустой title",
     f"SELECT p.url FROM pages p WHERE COALESCE(p.title,'')='' {HTML} {INDEXABLE}",
     "Без title страница в выдаче выглядит мусором."),

    ("dup_title", 1, "Дубли title",
     f"SELECT p.title, count(*) cnt, min(p.url) example FROM pages p "
     f"WHERE COALESCE(p.title,'')<>'' {HTML} {INDEXABLE} "
     f"GROUP BY p.title HAVING cnt>1 ORDER BY cnt DESC",
     "На каталоге в 15k карточек это главный источник 'малополезных' страниц у Яндекса."),

    ("short_title", 3, "Короткий title (<30)",
     f"SELECT p.title_len, p.title, p.url FROM pages p WHERE p.title_len BETWEEN 1 AND 29 "
     f"{HTML} {INDEXABLE} ORDER BY p.title_len",
     "Недобор — теряем вхождения запросов."),

    ("long_title", 3, "Длинный title (>65)",
     f"SELECT p.title_len, p.title, p.url FROM pages p WHERE p.title_len>65 {HTML} {INDEXABLE} "
     f"ORDER BY p.title_len DESC",
     "Обрежется в выдаче. Важное — в первые 60 символов."),

    ("no_desc", 2, "Пустой description",
     f"SELECT p.url FROM pages p WHERE COALESCE(p.description,'')='' {HTML} {INDEXABLE}",
     "Сниппет соберётся сам и обычно хуже, чем написанный."),

    ("dup_desc", 2, "Дубли description",
     f"SELECT p.description, count(*) cnt, min(p.url) example FROM pages p "
     f"WHERE COALESCE(p.description,'')<>'' {HTML} {INDEXABLE} "
     f"GROUP BY p.description HAVING cnt>1 ORDER BY cnt DESC",
     "Шаблон без подстановки размеров/ГОСТа. Признак генерации по одному лекалу."),

    ("long_desc", 3, "Длинный description (>180)",
     f"SELECT p.desc_len, p.url FROM pages p WHERE p.desc_len>180 {HTML} {INDEXABLE} "
     f"ORDER BY p.desc_len DESC",
     "Обрежется. 150–160 символов — рабочий диапазон."),

    ("no_h1", 1, "Нет H1",
     f"SELECT p.url FROM pages p WHERE p.h1_count=0 {HTML} {INDEXABLE}",
     "H1 задаёт тему страницы. Частая беда конструкторных шаблонов."),

    ("multi_h1", 2, "Несколько H1",
     f"SELECT p.h1_count, p.url FROM pages p WHERE p.h1_count>1 {HTML} {INDEXABLE} "
     f"ORDER BY p.h1_count DESC",
     "Размывает тему. Оставить один, остальное — H2."),

    ("dup_h1", 2, "Дубли H1",
     f"SELECT p.h1, count(*) cnt, min(p.url) example FROM pages p "
     f"WHERE COALESCE(p.h1,'')<>'' {HTML} {INDEXABLE} GROUP BY p.h1 HAVING cnt>1 ORDER BY cnt DESC",
     "Разные товары с одинаковым заголовком — кандидаты на склейку у поисковика."),

    ("no_canonical", 2, "Нет canonical",
     f"SELECT p.url FROM pages p WHERE p.canonical IS NULL {HTML} {INDEXABLE}",
     "На каталоге с фильтрами canonical обязателен, иначе дубли плодятся сами."),

    ("cross_canonical", 1, "Canonical указывает на другой URL",
     f"SELECT p.url, p.canonical FROM pages p WHERE p.canonical IS NOT NULL "
     f"AND p.canonical<>p.url AND p.canonical<>p.url||'/' {HTML} AND p.status=200",
     "Страница добровольно отдаёт вес другой. Проверить, что это осознанно."),

    ("canonical_broken", 1, "Canonical ведёт на не-200",
     "SELECT p.url, p.canonical, c.status FROM pages p JOIN pages c ON c.url=p.canonical "
     "WHERE p.canonical<>p.url AND (c.status IS NULL OR c.status<>200)",
     "Канонический адрес недоступен — поисковик проигнорирует указание."),

    ("noindex", 2, "Страницы с noindex",
     f"SELECT COALESCE(p.meta_robots,p.x_robots) robots, p.url FROM pages p "
     f"WHERE (COALESCE(p.meta_robots,'') LIKE '%noindex%' OR COALESCE(p.x_robots,'') LIKE '%noindex%') "
     f"{HTML}",
     "Проверить глазами: часть — намеренно, часть — случайно закрытые важные разделы."),

    ("sitemap_not200", 1, "В sitemap, но не отдаёт 200",
     "SELECT p.status, p.url FROM pages p WHERE p.in_sitemap=1 AND (p.status IS NULL OR p.status<>200)",
     "Sitemap врёт роботу. Для Яндекса это прямой минус к доверию карте."),

    ("sitemap_noindex", 1, "В sitemap, но закрыта от индексации",
     "SELECT p.url FROM pages p WHERE p.in_sitemap=1 AND "
     "(COALESCE(p.meta_robots,'') LIKE '%noindex%' OR COALESCE(p.x_robots,'') LIKE '%noindex%')",
     "Противоречивый сигнал: карта зовёт, мета запрещает."),

    ("orphan", 2, "Сироты: в sitemap, но без внутренних ссылок",
     "SELECT p.url FROM pages p WHERE p.in_sitemap=1 AND p.status=200 "
     "AND NOT EXISTS (SELECT 1 FROM links l WHERE l.dst=p.url AND l.internal=1)",
     "До страницы нельзя дойти по ссылкам — веса она не получает."),

    ("deep", 2, "Глубина больше 3 кликов",
     f"SELECT p.depth, p.url FROM pages p WHERE p.depth>3 {HTML} {INDEXABLE} ORDER BY p.depth DESC",
     "Чем глубже, тем реже обход. Для каталога критично."),

    ("thin", 2, "Тонкий контент (<150 слов)",
     f"SELECT p.word_count, p.url FROM pages p WHERE p.word_count<150 {HTML} {INDEXABLE} "
     f"ORDER BY p.word_count",
     "Кандидаты на 'малополезная страница' в Вебмастере."),

    ("no_jsonld", 2, "Нет микроразметки JSON-LD",
     f"SELECT p.url FROM pages p WHERE COALESCE(p.jsonld_types,'')='' {HTML} {INDEXABLE}",
     "Для товаров и категорий разметка даёт расширенный сниппет."),

    ("slow", 3, "Медленный ответ (>1.5 c)",
     f"SELECT p.elapsed_ms, p.url FROM pages p WHERE p.elapsed_ms>1500 {HTML} ORDER BY p.elapsed_ms DESC",
     "Время до первого байта. На каталоге обычно упирается в отсутствие кэша."),
]


def run(db, check, limit):
    try:
        rows = db.execute(check[3]).fetchall()
    except sqlite3.Error as e:
        return [], [], str(e)
    cols = [d[0] for d in db.execute(check[3]).description]
    return rows[:limit], cols, None


def main():
    p = argparse.ArgumentParser(description="Отчёт по базе SEO-краула")
    p.add_argument("--db", default="seo-crawl.db")
    p.add_argument("--out", help="папка для markdown-отчёта и CSV")
    p.add_argument("--check", help="только одна проверка по id")
    p.add_argument("--limit", type=int, default=10, help="примеров в консоль")
    p.add_argument("--severity", type=int, default=3, help="показывать до этого уровня включительно")
    a = p.parse_args()

    db = sqlite3.connect(a.db)
    total = db.execute("SELECT count(*) FROM pages WHERE status IS NOT NULL").fetchone()[0]
    ok = db.execute("SELECT count(*) FROM pages WHERE status=200").fetchone()[0]
    root = (db.execute("SELECT v FROM meta WHERE k='root'").fetchone() or ["?"])[0]

    checks = [c for c in CHECKS if (not a.check or c[0] == a.check) and c[1] <= a.severity]
    md = [f"# SEO-отчёт: {root}", "",
          f"Обойдено страниц: **{total}**, из них 200: **{ok}**. База: `{a.db}`.", "",
          "| Уровень | Проверка | Найдено |", "|---|---|---|"]
    details, found_any = [], False

    for c in checks:
        cid, sev, title, sql, hint = c
        rows, cols, err = run(db, c, 10 ** 9)
        if err:
            print(f"  ! {cid}: {err}")
            continue
        n = len(rows)
        mark = {1: "🔴", 2: "🟡", 3: "⚪"}[sev]
        md.append(f"| {mark} {sev} | {title} (`{cid}`) | {n} |")
        if not n:
            continue
        found_any = True
        details.append(f"\n## {mark} {title} — {n}\n\n{hint}\n")
        details.append("| " + " | ".join(cols) + " |")
        details.append("|" + "---|" * len(cols))
        for r in rows[:a.limit]:
            cells = [str(x)[:120].replace("|", "\\|") if x is not None else "" for x in r]
            details.append("| " + " | ".join(cells) + " |")
        if n > a.limit:
            details.append(f"\n_…ещё {n - a.limit}, полный список в CSV_")
        if a.out:
            os.makedirs(a.out, exist_ok=True)
            with open(os.path.join(a.out, f"{cid}.csv"), "w", encoding="utf-8-sig", newline="") as f:
                w = csv.writer(f, delimiter=";")
                w.writerow(cols)
                w.writerows(rows)

    report = "\n".join(md + details)
    if not found_any:
        report += "\n\nПроблем по выбранным проверкам не найдено.\n"
    print(report)
    if a.out:
        os.makedirs(a.out, exist_ok=True)
        path = os.path.join(a.out, "report.md")
        with open(path, "w", encoding="utf-8") as f:
            f.write(report)
        print(f"\n-> {path}")


if __name__ == "__main__":
    main()
