# -*- coding: utf-8 -*-
"""Аудит рекламы Яндекс.Директа по данным Метрики.

Два пространства данных:

* `ym:s:*` — визитное: посадочные, устройства, пол/возраст. Расхода нет.
* `ym:ad:*` — рекламное: клики, **расход в рублях**, визиты, конверсии.
  Открывается только с параметром `direct_client_logins=<логин Директа>`;
  без него Метрика отвечает 403. Логин берётся из владельца счётчика
  (`owner_login`) и задаётся флагом `--client-login`.

Показов, CTR и ставок нет ни там, ни там — за ними нужен API самого
Директа, а он требует одобренной заявки на доступ для OAuth-приложения.

    python scripts/ads/direct_audit.py --days 365 --out perf-reports/ads/2026-09-03
"""
import argparse, datetime as dt, json, os, sys, time
import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
MET = "https://api-metrika.yandex.net/stat/v1/data"


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


TOKEN = env("YANDEX_METRIKA_TOKEN")
COUNTER = env("YANDEX_METRIKA_COUNTER")
AD = "ym:s:lastTrafficSource=='ad'"

# цели, по которым считаем заявки (id -> короткое имя)
GOALS = {
    "182776720": "форма",
    "229699797": "телефон",
    "180275119": "email",
    "174609040": "файл",
    "310490576": "CRM-заказ",
}
BASE = ["ym:s:visits", "ym:s:users", "ym:s:bounceRate", "ym:s:pageDepth",
        "ym:s:avgVisitDurationSeconds"]
CONV = [f"ym:s:goal{g}reaches" for g in GOALS]
METRICS = ",".join(BASE + CONV)
COLS = ["visits", "users", "bounce", "depth", "dur"] + list(GOALS.values())

# рекламное пространство: те же цели, но с кликами и расходом
AD_BASE = ["ym:ad:clicks", "ym:ad:RUBAdCost", "ym:ad:visits", "ym:ad:bounceRate"]
AD_METRICS = ",".join(AD_BASE + [f"ym:ad:goal{g}reaches" for g in GOALS])
AD_COLS = ["clicks", "cost", "visits", "bounce"] + list(GOALS.values())


def query(dimensions, metrics=METRICS, filters=AD, date1=None, date2=None,
          limit=1000, sort=None, client_login=None):
    p = {"ids": COUNTER, "metrics": metrics, "dimensions": dimensions,
         "date1": date1, "date2": date2, "limit": limit, "accuracy": "full"}
    if filters:
        p["filters"] = filters
    if client_login:
        # без этого параметра расходные метрики отдают 403 direct_client_logins
        p["direct_client_logins"] = client_login
    if sort:
        p["sort"] = sort
    for attempt in range(4):
        r = requests.get(MET, headers={"Authorization": f"OAuth {TOKEN}"},
                         params=p, timeout=120)
        if r.status_code == 429:
            time.sleep(5 * (attempt + 1)); continue
        if r.status_code >= 400:
            return {"error": r.status_code, "body": r.text[:400],
                    "dimensions": dimensions}
        d = r.json()
        return {"dimensions": dimensions, "totals": d.get("totals"),
                "rows": [{"key": [str(x.get("name")) for x in row["dimensions"]],
                          "m": row["metrics"]} for row in d.get("data", [])]}
    return {"error": 429, "dimensions": dimensions}


REPORTS = {
    "campaigns":      "ym:s:lastDirectClickOrderName,ym:s:lastDirectPlatformType",
    "groups":         "ym:s:lastDirectClickOrderName,ym:s:lastDirectBannerGroup",
    "banners":        "ym:s:lastDirectClickBanner",
    "conditions":     "ym:s:lastDirectClickOrderName,ym:s:lastDirectPhraseOrCond",
    "condition_type": "ym:s:lastDirectConditionType",
    "platforms":      "ym:s:lastDirectPlatform",
    "landing":        "ym:s:startURL",
    "landing_by_camp": "ym:s:lastDirectClickOrderName,ym:s:startURL",
    "regions":        "ym:s:regionCity",
    "devices":        "ym:s:deviceCategory",
    "months":         "ym:s:datePeriodMonth",
    "search_phrase":  "ym:s:lastDirectSearchPhrase",
    "gender_age":     "ym:s:gender,ym:s:ageInterval",
}

# отчёты с расходом; работают только при заданном --client-login
AD_REPORTS = {
    "ad_campaigns":      "ym:ad:lastDirectOrder",
    "ad_platform_type":  "ym:ad:lastDirectPlatformType",
    "ad_condition_type": "ym:ad:lastDirectConditionType",
    "ad_conditions":     "ym:ad:lastDirectOrder,ym:ad:lastDirectPhraseOrCond",
    "ad_groups":         "ym:ad:lastDirectOrder,ym:ad:lastDirectBannerGroup",
    "ad_banners":        "ym:ad:lastDirectBanner",
    "ad_platforms":      "ym:ad:lastDirectPlatform",
    "ad_regions":        "ym:ad:regionCity",
    "ad_months":         "ym:ad:datePeriodMonth",
    "ad_search_phrase":  "ym:ad:lastDirectSearchPhrase",
}


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--days", type=int, default=365)
    ap.add_argument("--out", default="perf-reports/ads/latest")
    ap.add_argument("--limit", type=int, default=1000)
    ap.add_argument("--only", help="через запятую: campaigns,ad_campaigns,...")
    ap.add_argument("--client-login", default=env("YANDEX_DIRECT_LOGIN"),
                    help="логин Директа; без него расходные отчёты недоступны")
    a = ap.parse_args()
    if not TOKEN or not COUNTER:
        sys.exit("Нет YANDEX_METRIKA_TOKEN / YANDEX_METRIKA_COUNTER в site/.env")
    d2 = dt.date.today().isoformat()
    d1 = (dt.date.today() - dt.timedelta(days=a.days)).isoformat()
    out = os.path.join(ROOT, a.out)
    os.makedirs(out, exist_ok=True)
    all_names = list(REPORTS) + (list(AD_REPORTS) if a.client_login else [])
    names = a.only.split(",") if a.only else all_names
    result = {"counter": COUNTER, "date1": d1, "date2": d2, "goals": GOALS,
              "columns": COLS, "ad_columns": AD_COLS,
              "client_login": a.client_login, "reports": {}}
    for name in names:
        if name in AD_REPORTS:
            if not a.client_login:
                print(f"{name:16} пропущен: нужен --client-login")
                continue
            res = query(AD_REPORTS[name], metrics=AD_METRICS, filters=None,
                        date1=d1, date2=d2, limit=a.limit,
                        client_login=a.client_login)
            dims = AD_REPORTS[name]
        else:
            dims = REPORTS[name]
            res = query(dims, date1=d1, date2=d2, limit=a.limit)
        result["reports"][name] = res
        n = len(res.get("rows", []))
        print(f"{name:16} {dims:70} {'ERR '+str(res['error']) if 'error' in res else str(n)+' строк'}")
    path = os.path.join(out, f"direct-metrika-{a.days}d.json")
    with open(path, "w", encoding="utf-8") as f:
        json.dump(result, f, ensure_ascii=False, indent=1)
    print("\nсохранено:", path)


if __name__ == "__main__":
    main()
