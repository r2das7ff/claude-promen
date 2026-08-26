#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Яндекс.Вебмастер API v4 + Метрика + IndexNow — один инструмент.

Токены берём из site/.env, в git они не едут:
    YANDEX_WEBMASTER_TOKEN=y0_...
    YANDEX_METRIKA_TOKEN=y0_...      # можно тот же, если у приложения есть скоуп метрики
    YANDEX_METRIKA_COUNTER=12345678
    INDEXNOW_KEY=...                 # необязательно

    python yandex.py hosts
    python yandex.py summary prom-en.com
    python yandex.py queries prom-en.com --days 28
    python yandex.py baseline prom-en.com --out perf-reports/seo/baseline-2026-08-25/
    python yandex.py recrawl prom-en.com https://prom-en.com/catalog/
    python yandex.py metrika pages --days 30

Хост можно задавать доменом — host_id вида `https:prom-en.com:443`
подставится сам.
"""
import argparse
import datetime as dt
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

WM = "https://api.webmaster.yandex.net/v4"
MET = "https://api-metrika.yandex.net/stat/v1/data"
ENV = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "..", ".env")


def env(key, default=None):
    if os.environ.get(key):
        return os.environ[key]
    try:
        with open(os.path.normpath(ENV), encoding="utf-8") as f:
            for line in f:
                if line.strip().startswith(key + "="):
                    return line.split("=", 1)[1].strip().strip("\"'")
    except OSError:
        pass
    return default


def need(key):
    v = env(key)
    if not v:
        sys.exit(f"Нет {key}. Добавьте строку {key}=... в {os.path.normpath(ENV)}")
    return v


def days_ago(n):
    return (dt.date.today() - dt.timedelta(days=n)).isoformat()


def today():
    return dt.date.today().isoformat()


# ------------------------------------------------------------------ Вебмастер
class Webmaster:
    def __init__(self):
        self.tok = need("YANDEX_WEBMASTER_TOKEN")
        self.s = requests.Session()
        self.s.headers["Authorization"] = f"OAuth {self.tok}"
        self._uid = None

    def call(self, path, method="GET", **kw):
        r = self.s.request(method, f"{WM}/{path.lstrip('/')}", timeout=60, **kw)
        if r.status_code >= 400:
            body = r.text[:400]
            if r.status_code in (401, 403):
                body += "\n  (проверьте, что у OAuth-приложения есть права webmaster:hostinfo)"
            sys.exit(f"API {r.status_code} на {path}:\n  {body}")
        return r.json() if r.text else {}

    @property
    def uid(self):
        if self._uid is None:
            self._uid = self.call("user/")["user_id"]
        return self._uid

    def hosts(self):
        return self.call(f"user/{self.uid}/hosts/").get("hosts", [])

    def host_id(self, ref):
        """Принимает host_id целиком либо домен — ищет совпадение."""
        if ref.startswith("http"):
            return ref
        for h in self.hosts():
            if ref in h.get("unicode_host_url", "") or ref in h.get("host_id", ""):
                return h["host_id"]
        sys.exit(f"Хост {ref} не найден в Вебмастере. Список: python yandex.py hosts")

    def h(self, ref, path, **kw):
        return self.call(f"user/{self.uid}/hosts/{self.host_id(ref)}/{path}", **kw)


# ------------------------------------------------------------------- команды
def cmd_hosts(wm, a):
    out = []
    for h in wm.hosts():
        out.append({
            "host_id": h.get("host_id"),
            "url": h.get("unicode_host_url"),
            "verified": h.get("verified"),
            "main_mirror": (h.get("main_mirror") or {}).get("unicode_host_url"),
        })
    return out


def cmd_summary(wm, a):
    return wm.h(a.host, "summary/")


def cmd_indexing(wm, a):
    return wm.h(a.host, "indexing/history/", params=[
        ("date_from", days_ago(a.days)), ("date_to", today()),
        ("indexing_indicator", "SEARCHABLE"), ("indexing_indicator", "DOWNLOADED"),
        ("indexing_indicator", "EXCLUDED"), ("indexing_indicator", "HTTP_2XX"),
        ("indexing_indicator", "HTTP_4XX"), ("indexing_indicator", "HTTP_5XX")])


def cmd_insearch(wm, a):
    return wm.h(a.host, "search-urls/in-search/history/",
                params={"date_from": days_ago(a.days), "date_to": today()})


def cmd_events(wm, a):
    """Какие URL появились в поиске и какие выпали — с причинами исключения."""
    # У этого эндпоинта потолок limit — 100, больше отдаёт 400.
    return wm.h(a.host, "search-urls/events/samples/", params={"limit": min(a.limit, 100)})


def cmd_queries(wm, a):
    return wm.h(a.host, "search-queries/popular/", params=[
        ("order_by", "TOTAL_SHOWS"),
        ("query_indicator", "TOTAL_SHOWS"), ("query_indicator", "TOTAL_CLICKS"),
        ("query_indicator", "AVG_SHOW_POSITION"), ("query_indicator", "AVG_CLICK_POSITION"),
        ("date_from", days_ago(a.days)), ("date_to", today()), ("limit", a.limit)])


def cmd_links(wm, a):
    return wm.h(a.host, "links/external/samples/", params={"limit": min(a.limit, 100)})


def cmd_sitemaps(wm, a):
    return wm.h(a.host, "sitemaps/")


def cmd_diagnostics(wm, a):
    return wm.h(a.host, "diagnostics/")


def cmd_quota(wm, a):
    return wm.h(a.host, "recrawl/quota/")


def cmd_recrawl(wm, a):
    """Отправка страниц на переобход. Квота ограничена — тратить на важное."""
    out = []
    for u in a.urls:
        out.append({u: wm.h(a.host, "recrawl/queue/", method="POST", json={"url": u})})
    return out


def cmd_important(wm, a):
    return wm.h(a.host, "important-urls/")


def cmd_addhost(wm, a):
    """Добавить сайт в Вебмастер. Данные пойдут только после верификации."""
    url = a.urls[0] if a.urls else a.host
    if not url.startswith("http"):
        url = "https://" + url
    return wm.call(f"user/{wm.uid}/hosts/", method="POST", json={"host_url": url})


def cmd_verification(wm, a):
    """Статус подтверждения прав и доступные способы."""
    return wm.h(a.host, "verification/")


def cmd_verify(wm, a):
    """Запустить проверку выбранным способом (по умолчанию HTML-файл)."""
    return wm.h(a.host, "verification/", method="POST",
                params={"verification_type": a.preset.upper()})


def cmd_baseline(wm, a):
    """Полный слепок «до»: то, с чем потом сравнивать после изменений."""
    parts = {
        "summary": lambda: wm.h(a.host, "summary/"),
        "indexing": lambda: cmd_indexing(wm, a),
        "insearch": lambda: cmd_insearch(wm, a),
        "queries": lambda: cmd_queries(wm, a),
        "links": lambda: cmd_links(wm, a),
        "sitemaps": lambda: cmd_sitemaps(wm, a),
        "diagnostics": lambda: cmd_diagnostics(wm, a),
        "events": lambda: cmd_events(wm, a),
    }
    res = {}
    for name, fn in parts.items():
        try:
            res[name] = fn()
            print(f"  ok   {name}")
        except SystemExit as e:
            res[name] = {"error": str(e)}
            print(f"  FAIL {name}: {e}")
    if a.out:
        os.makedirs(a.out, exist_ok=True)
        for name, data in res.items():
            with open(os.path.join(a.out, f"wm-{name}.json"), "w", encoding="utf-8") as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
        print(f"\n-> {a.out}")
    return {"saved": list(res)} if a.out else res


# -------------------------------------------------------------------- Метрика
def metrika(a):
    tok = env("YANDEX_METRIKA_TOKEN") or need("YANDEX_WEBMASTER_TOKEN")
    counter = need("YANDEX_METRIKA_COUNTER")
    presets = {
        "sources": ("ym:s:visits,ym:s:users,ym:s:bounceRate", "ym:s:lastTrafficSource"),
        "engines": ("ym:s:visits,ym:s:users", "ym:s:lastSearchEngine"),
        "pages": ("ym:pv:pageviews,ym:pv:users", "ym:pv:URL"),
        "queries": ("ym:s:visits", "ym:s:lastSearchPhrase"),
        "devices": ("ym:s:visits,ym:s:bounceRate", "ym:s:deviceCategory"),
    }
    if a.preset not in presets:
        sys.exit(f"Пресеты: {', '.join(presets)}")
    metrics, dims = presets[a.preset]
    params = {"ids": counter, "metrics": metrics, "dimensions": dims,
              "date1": days_ago(a.days), "date2": today(), "limit": a.limit,
              "accuracy": "full"}
    # Без фильтра в отчёт валится прямой заход ботов (у prom-en.com это 94%
    # отказов при 23k визитов) и реклама — органику за ними не видно.
    if a.organic:
        params["filters"] = "ym:s:lastTrafficSource=='organic'"
    elif a.filters:
        params["filters"] = a.filters
    r = requests.get(MET, timeout=60, headers={"Authorization": f"OAuth {tok}"}, params=params)
    if r.status_code >= 400:
        sys.exit(f"Метрика {r.status_code}: {r.text[:300]}")
    d = r.json()
    return [{"name": " / ".join(str(x.get("name")) for x in row["dimensions"]),
             "metrics": row["metrics"]} for row in d.get("data", [])]


# ------------------------------------------------------------------ IndexNow
def indexnow(a):
    key = need("INDEXNOW_KEY")
    host = a.host.replace("https://", "").replace("http://", "").strip("/")
    r = requests.post("https://yandex.com/indexnow", timeout=60,
                      json={"host": host, "key": key, "urlList": a.urls})
    return {"status": r.status_code, "body": r.text[:300]}


COMMANDS = {
    "hosts": cmd_hosts, "summary": cmd_summary, "indexing": cmd_indexing,
    "insearch": cmd_insearch, "queries": cmd_queries, "links": cmd_links,
    "sitemaps": cmd_sitemaps, "diagnostics": cmd_diagnostics, "quota": cmd_quota,
    "recrawl": cmd_recrawl, "important": cmd_important, "baseline": cmd_baseline,
    "addhost": cmd_addhost, "verification": cmd_verification, "verify": cmd_verify,
    "events": cmd_events,
}


def main():
    p = argparse.ArgumentParser(description="Яндекс.Вебмастер + Метрика")
    p.add_argument("command", choices=list(COMMANDS) + ["metrika", "indexnow"])
    p.add_argument("host", nargs="?", help="домен или host_id")
    p.add_argument("urls", nargs="*", help="URL для recrawl / indexnow")
    p.add_argument("--days", type=int, default=28)
    p.add_argument("--limit", type=int, default=500)
    p.add_argument("--out", help="папка для baseline")
    p.add_argument("--preset", default="sources", help="пресет метрики")
    p.add_argument("--organic", action="store_true", help="только поисковый трафик")
    p.add_argument("--filters", help="сырой фильтр Метрики")
    a = p.parse_args()

    if a.command == "metrika":
        a.preset = a.host or a.preset
        res = metrika(a)
    elif a.command == "indexnow":
        res = indexnow(a)
    else:
        if a.command != "hosts" and not a.host:
            sys.exit("Укажите домен: python yandex.py summary prom-en.com")
        res = COMMANDS[a.command](Webmaster(), a)
    print(json.dumps(res, ensure_ascii=False, indent=2)[:200000])


if __name__ == "__main__":
    main()
