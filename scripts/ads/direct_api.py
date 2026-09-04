# -*- coding: utf-8 -*-
"""Выгрузка того, чего нет в Метрике: минус-слова, ставки, стратегии,
статусы кампаний и отчёт по поисковым запросам — через API Директа v5.

Токен берётся из `YANDEX_DIRECT_TOKEN` в site/.env. Он должен быть выпущен
приложением, у которого в Директе есть **одобренная заявка на доступ к API**
(вкладка «Мои заявки» в настройках API). Токен приложения без заявки
получает ошибку 58 «Незавершенная регистрация», даже если у приложения
выдано право direct:api и программный доступ аккаунта открыт.

    python scripts/ads/direct_api.py all --out perf-reports/ads/2026-09-03
    python scripts/ads/direct_api.py queries --days 90
"""
import argparse
import datetime as dt
import json
import os
import sys
import time

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
API = "https://api.direct.yandex.com/json/v5/"


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


TOKEN = env("YANDEX_DIRECT_TOKEN")
LOGIN = env("YANDEX_DIRECT_LOGIN")


def headers(extra=None):
    h = {"Authorization": f"Bearer {TOKEN}", "Accept-Language": "ru",
         "Content-Type": "application/json; charset=utf-8"}
    if LOGIN:
        h["Client-Login"] = LOGIN
    if extra:
        h.update(extra)
    return h


def call(service, method, params):
    body = json.dumps({"method": method, "params": params}).encode("utf-8")
    r = requests.post(API + service, headers=headers(), data=body, timeout=120)
    try:
        d = r.json()
    except ValueError:
        sys.exit(f"{service}.{method}: не JSON, HTTP {r.status_code}\n{r.text[:300]}")
    if "error" in d:
        e = d["error"]
        sys.exit(f"{service}.{method} → код {e.get('error_code')}: "
                 f"{e.get('error_string')} | {e.get('error_detail')}")
    print(f"  {service}.{method}: units {r.headers.get('Units', '?')}")
    return d.get("result", {})


def paged(service, method, params, key):
    """Директ отдаёт максимум 10 000 объектов за раз, дальше — по LimitedBy."""
    out, offset = [], 0
    while True:
        p = dict(params)
        p.setdefault("Page", {})["Offset"] = offset
        res = call(service, method, p)
        chunk = res.get(key, [])
        out.extend(chunk)
        limited = res.get("LimitedBy")
        if not limited or not chunk:
            return out
        offset = limited


def campaigns():
    return paged("campaigns", "get", {
        "SelectionCriteria": {},
        "FieldNames": ["Id", "Name", "Type", "State", "Status", "StatusPayment",
                       "StartDate", "EndDate", "DailyBudget", "Funds",
                       "NegativeKeywords", "TimeTargeting", "ClientInfo"],
        "TextCampaignFieldNames": ["BiddingStrategy", "Settings"],
    }, "Campaigns")


def by_campaigns(service, key, params, camp_ids):
    """adgroups/keywords/ads требуют CampaignIds и принимают не более 10 за раз."""
    out = []
    for i in range(0, len(camp_ids), 10):
        p = json.loads(json.dumps(params))
        p["SelectionCriteria"]["CampaignIds"] = camp_ids[i:i + 10]
        out.extend(paged(service, "get", p, key))
    return out


def adgroups(camp_ids):
    return by_campaigns("adgroups", "AdGroups", {
        "SelectionCriteria": {},
        "FieldNames": ["Id", "Name", "CampaignId", "Status", "Type",
                       "NegativeKeywords", "RegionIds", "TrackingParams"],
    }, camp_ids)


def keywords(camp_ids):
    return by_campaigns("keywords", "Keywords", {
        "SelectionCriteria": {},
        "FieldNames": ["Id", "Keyword", "AdGroupId", "CampaignId", "State",
                       "Status", "Bid", "ContextBid", "StrategyPriority"],
    }, camp_ids)


def ads(camp_ids):
    return by_campaigns("ads", "Ads", {
        "SelectionCriteria": {},
        "FieldNames": ["Id", "AdGroupId", "CampaignId", "State", "Status", "Type"],
        "TextAdFieldNames": ["Title", "Title2", "Text", "Href", "DisplayUrlPath",
                             "SitelinkSetId", "VCardId"],
    }, camp_ids)


def report(days, out_path):
    """Отчёт по поисковым запросам: что реально вводили и во что это обошлось."""
    d2 = dt.date.today()
    d1 = d2 - dt.timedelta(days=days)
    body = {"params": {
        "SelectionCriteria": {"DateFrom": d1.isoformat(), "DateTo": d2.isoformat()},
        "FieldNames": ["CampaignName", "AdGroupName", "Query", "MatchType",
                       "Criterion", "Impressions", "Clicks", "Cost", "Ctr",
                       "AvgCpc", "Conversions", "CostPerConversion"],
        "ReportName": f"queries-{d1}-{d2}-{int(time.time())}",
        "ReportType": "SEARCH_QUERY_PERFORMANCE_REPORT",
        "DateRangeType": "CUSTOM_DATE",
        "Format": "TSV",
        "IncludeVAT": "YES",
        "IncludeDiscount": "NO",
    }}
    h = headers({"processingMode": "auto", "returnMoneyInMicros": "false",
                 "skipReportHeader": "true", "skipReportSummary": "true"})
    for attempt in range(12):
        r = requests.post(API + "reports", headers=h,
                          data=json.dumps(body).encode("utf-8"), timeout=180)
        if r.status_code in (201, 202):
            wait = int(r.headers.get("retryIn", 10))
            print(f"  отчёт готовится, ждём {wait} с (попытка {attempt + 1})")
            time.sleep(wait)
            continue
        if r.status_code == 200:
            with open(out_path, "w", encoding="utf-8", newline="") as f:
                f.write(r.text)
            rows = max(0, r.text.count("\n") - 1)
            print(f"  отчёт сохранён: {out_path} ({rows} строк)")
            return
        sys.exit(f"reports → HTTP {r.status_code}: {r.text[:300]}")
    sys.exit("отчёт так и не собрался за 12 попыток")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("what", choices=["all", "campaigns", "adgroups", "keywords",
                                     "ads", "queries", "check"])
    ap.add_argument("--out", default="perf-reports/ads/latest")
    ap.add_argument("--days", type=int, default=90)
    a = ap.parse_args()
    if not TOKEN:
        sys.exit("Нет YANDEX_DIRECT_TOKEN в site/.env.\n"
                 "Токен должен быть выпущен приложением с одобренной заявкой "
                 "на доступ к API Директа, иначе будет ошибка 58.")
    out = os.path.join(ROOT, a.out)
    os.makedirs(out, exist_ok=True)

    if a.what == "check":
        res = call("clients", "get", {"FieldNames": ["Login", "ClientId", "Type",
                                                     "Currency", "Restrictions"]})
        print(json.dumps(res, ensure_ascii=False, indent=1)[:1200])
        return

    names = ["campaigns", "adgroups", "keywords", "ads"] if a.what == "all" else (
        [a.what] if a.what in ("campaigns", "adgroups", "keywords", "ads") else [])
    data, camp_ids = {}, []
    for name in names:
        print(f"{name}:")
        if name == "campaigns":
            data[name] = campaigns()
            camp_ids = [c["Id"] for c in data[name]]
        else:
            if not camp_ids:
                camp_ids = [c["Id"] for c in campaigns()]
            data[name] = {"adgroups": adgroups, "keywords": keywords,
                          "ads": ads}[name](camp_ids)
        print(f"  получено объектов: {len(data[name])}")
    if data:
        path = os.path.join(out, "direct-api.json")
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=1)
        print("сохранено:", path)
    if a.what in ("all", "queries"):
        print("поисковые запросы:")
        report(a.days, os.path.join(out, f"direct-queries-{a.days}d.tsv"))


if __name__ == "__main__":
    main()
