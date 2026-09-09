# -*- coding: utf-8 -*-
"""Диагностика и починка посадочных в объявлениях Яндекс.Директа.

После переезда 01.09.2026 все объявления ведут на адреса старого сайта.
Большинство спасают 301, но редирект сам по себе плох: теряется якорь,
добавляется лишний хоп, а главное — 70% ссылок сваливаются в общую витрину
`/catalog/` вместо профильной категории. Человек, кликнувший по объявлению
про отводы, попадает в каталог из 15 407 позиций и ищет их заново.

Скрипт делает две вещи:

1. **Диагностика** (по умолчанию): для каждого уникального адреса смотрит,
   что отдаёт сервер, и подбирает осмысленную замену. Где 301 ведёт в общую
   витрину, целевая категория определяется по самому адресу — в старых
   слагах есть тип изделия (`otvody`, `trojniki`, `perexody`…).
2. **Починка** (`--apply`): переписывает Href через `ads.update`.

Что не трогаем: архивные кампании, кампанию «Запорная арматура» (в каталоге
12 задвижек, рекламировать нечего) и нашу новую кампанию ретаргетинга.

    python scripts/ads/direct_fix_links.py
    python scripts/ads/direct_fix_links.py --apply
"""
import argparse
import json
import os
import re
import sys
import urllib.parse as up

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
API = "https://api.direct.yandex.com/json/v5/"
SITE = "https://prom-en.com"

ALLOWED = {"ads.get", "ads.update", "campaigns.get", "adgroups.get"}

SKIP_CAMPAIGNS = ("Запорная арматура",)      # ассортимента нет, рекламировать нечего
SKIP_PREFIXES = ("Ретаргетинг: клиенты",)    # наша новая, ссылки уже актуальные

# Единая разметка. roistat убран намеренно: его скрипт при переезде не
# перенесён, метки остались бы мусором в адресе.
UTM = ("utm_source=yandex&utm_medium=cpc"
       "&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{phrase_id}_{retargeting_id}"
       "&utm_term={keyword}")

# Тип изделия в старом слаге → категория нового каталога. Порядок важен:
# «otvody-krutoizognutye» должно совпасть раньше, чем «otvody».
BY_SLUG = [
    ("izoljacij", "/catalog/izolyatsiya/"),
    ("izolyacij", "/catalog/izolyatsiya/"),
    ("otvod", "/catalog/sdt/otvody/"),
    ("trojnik", "/catalog/sdt/troyniki/"),
    ("troynik", "/catalog/sdt/troyniki/"),
    ("perexod", "/catalog/sdt/perekhody/"),
    ("perehod", "/catalog/sdt/perekhody/"),
    ("zaglush", "/catalog/sdt/zaglushki/"),
    ("dnishh", "/catalog/sdt/dnishcha/"),
    ("donysh", "/catalog/sdt/dnishcha/"),
    ("opor", "/catalog/opory/"),
    ("flanc", "/catalog/flancy/"),
    ("flanec", "/catalog/flancy/"),
    ("bolt", "/catalog/krepezh/bolty/"),
    ("shpilk", "/catalog/krepezh/shpilki/"),
    ("gayk", "/catalog/krepezh/gayki/"),
    ("shajb", "/catalog/krepezh/shayby/"),
    ("trub", "/catalog/truby/"),
    ("shtucer", "/catalog/tochenye/"),   # отдельной категории штуцеров нет
    ("bobyshk", "/catalog/tochenye/"),
    ("zadvizh", "/catalog/armatura/"),
    # Колено по ОСТ 24.125 — тот же отвод, только под другим названием.
    ("kolen", "/catalog/sdt/otvody/"),
    ("probk", "/catalog/tochenye/"),
    ("ugolnik", "/catalog/sdt/"),
    ("detali-dlya-tes", "/catalog/sdt/"),
]

# Страницы, у которых на новом сайте есть прямой смысловой аналог.
STATIC = {
    "/prajs-list-truby/": "/catalog/truby/",
    "/prajs-list-detali/": "/catalog/sdt/",
    "/uslugi/": "/production/",
    "/trust-us/": "/proekty/",
    "/kontakty/": "/contacts/",
    "/zapornaya-armatura/": "/catalog/armatura/",
    "/izoljacija/": "/catalog/izolyatsiya/",
    "/products/": "/catalog/",
    "/": "/",
}


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


HEADERS = {
    "Authorization": f"Bearer {env('YANDEX_DIRECT_TOKEN')}",
    "Client-Login": env("YANDEX_DIRECT_LOGIN") or "",
    "Accept-Language": "ru",
    "Content-Type": "application/json; charset=utf-8",
}


def call(service, method, params):
    full = f"{service}.{method}"
    if full not in ALLOWED:
        sys.exit(f"ОТКАЗ: {full} вне списка разрешённых")
    r = requests.post(API + service, headers=HEADERS,
                      data=json.dumps({"method": method, "params": params}).encode("utf-8"),
                      timeout=180)
    d = r.json()
    if "error" in d:
        e = d["error"]
        sys.exit(f"{full} → {e.get('error_code')}: {e.get('error_string')} | {e.get('error_detail')}")
    res = d.get("result", {})
    # Директ не падает на пакетной правке: ошибки приходят по каждому
    # объекту отдельно. Без этой проверки неудачные обновления проходят молча.
    for key in ("UpdateResults", "AddResults"):
        for item in (res.get(key) or []):
            for x in (item.get("Errors") or []):
                print(f"    ошибка {x.get('Code')} у {item.get('Id')}: "
                      f"{x.get('Message')} {x.get('Details', '')}")
            for w in (item.get("Warnings") or []):
                print(f"    предупреждение у {item.get('Id')}: {w.get('Message')}")
    return res


# Разделы нового сайта. Если ссылка уже ведёт сюда, её трогать нельзя:
# повторный прогон иначе «не узнает» собственную работу и свалит всё в
# общую витрину — слаг /catalog/sdt/perekhody/ не содержит маркера
# «perexod», по которому подбиралась категория.
ALREADY_NEW = ("/catalog/", "/proekty/", "/contacts/", "/production/",
               "/kalkulyatory/", "/normativnaya-baza/", "/podbor/", "/stati/")


def target_for(path: str) -> tuple:
    """Куда вести. Возвращает (новый путь, чем обосновано) или (None, причина)."""
    clean = path.split("#")[0]
    if not clean.endswith("/"):
        clean += "/"
    if clean != "/" and clean.startswith(ALREADY_NEW):
        return None, "уже актуальная"
    if clean in STATIC:
        return STATIC[clean], "прямой аналог"
    low = clean.lower()
    for marker, dest in BY_SLUG:
        if marker in low:
            return dest, f"по типу изделия «{marker}»"
    return "/catalog/", "не распознано, общая витрина"


def check(path: str) -> str:
    """Что сервер отдаёт сейчас — для отчёта, чтобы видеть 404 отдельно."""
    try:
        # Именно GET: mu-plugin редиректов не обрабатывает HEAD и отвечает
        # на него 404, хотя тот же адрес по GET отдаёт корректный 301.
        r = requests.get(SITE + path, allow_redirects=False, timeout=20, stream=True)
        if r.status_code in (301, 302):
            loc = r.headers.get("Location", "")
            return f"301 → {up.urlsplit(loc).path or loc}"
        return str(r.status_code)
    except Exception as e:
        return f"сбой: {str(e)[:30]}"


def load():
    camps = {c["Id"]: c for c in call("campaigns", "get", {
        "SelectionCriteria": {}, "FieldNames": ["Id", "Name", "State", "Status"]}).get("Campaigns", [])}
    live = {cid: c for cid, c in camps.items()
            if c.get("State") != "ARCHIVED"
            and not any(s in c["Name"] for s in SKIP_CAMPAIGNS)
            and not any(c["Name"].startswith(p) for p in SKIP_PREFIXES)}
    ids = list(live)
    ads = []
    for i in range(0, len(ids), 10):
        ads.extend(call("ads", "get", {
            "SelectionCriteria": {"CampaignIds": ids[i:i + 10]},
            "FieldNames": ["Id", "CampaignId", "AdGroupId", "State", "Status", "Type"],
            "TextAdFieldNames": ["Href", "Title"],
        }).get("Ads", []))
    return camps, live, ads


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--apply", action="store_true", help="переписать ссылки (без флага — только отчёт)")
    ap.add_argument("--limit", type=int, default=0, help="ограничить число правок (для пробы)")
    a = ap.parse_args()

    camps, live, ads = load()
    print(f"кампаний в работе: {len(live)} (архивные и «Запорная арматура» пропущены)")
    print(f"объявлений в них: {len(ads)}\n")

    plan, skipped = [], 0
    paths = {}
    for ad in ads:
        # Архивные объявления API обновлять запрещает (ошибка 8300), и это
        # правильно: они не показываются. В действующих кампаниях их 242.
        if ad.get("State") == "ARCHIVED":
            skipped += 1
            continue
        href = (ad.get("TextAd") or {}).get("Href")
        if not href:
            skipped += 1
            continue
        parts = up.urlsplit(href)
        old_path = parts.path or "/"
        dest, why = target_for(old_path + ("#" + parts.fragment if parts.fragment else ""))
        if dest is None:
            continue
        new_href = f"{SITE}{dest}?{UTM}"
        if href == new_href:
            continue
        plan.append((ad["Id"], ad["CampaignId"], old_path, dest, new_href))
        paths.setdefault((old_path, dest, why), 0)
        paths[(old_path, dest, why)] += 1

    print(f"=== ПЛАН: {len(plan)} объявлений, {len(paths)} уникальных адресов ===\n")
    print(f'{"было":52} {"станет":30} {"объявл.":>7}  сейчас отдаёт')
    for (old, dest, why), n in sorted(paths.items(), key=lambda x: -x[1]):
        print(f'  {old[:50]:50} → {dest:30} {n:6d}  {check(old)}   [{why}]')

    if not a.apply:
        print("\nэто отчёт. Для правки: --apply")
        return

    todo = plan[:a.limit] if a.limit else plan
    print(f"\nобновляю {len(todo)} объявлений…")
    for i in range(0, len(todo), 100):
        chunk = todo[i:i + 100]
        call("ads", "update", {"Ads": [
            {"Id": ad_id, "TextAd": {"Href": href}} for ad_id, _, _, _, href in chunk
        ]})
        print(f"  готово: {min(i + 100, len(todo))} из {len(todo)}")
    print("\nОбъявления уходят на повторную модерацию — это нормально.")


if __name__ == "__main__":
    main()
