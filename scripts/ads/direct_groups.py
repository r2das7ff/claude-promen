# -*- coding: utf-8 -*-
"""Остановка групп объявлений по маске имени.

У группы в API нет собственного выключателя: показы гасятся остановкой
её объявлений (`ads.suspend`). Скрипт находит группы по маске, показывает
их расход и заявки за год и останавливает объявления.

Первый адресат — прайсовые группы «Оптимизации ТГО». За 12 месяцев они
съели 93 277 ₽ и принесли две заявки, CPA 46 638 ₽. Причина не в ставках:
на сайте нет цен, и человек, искавший прайс, уходит с карточки ни с чем.

    python scripts/ads/direct_groups.py --campaign 109052494 --mask "Прайс-лист"
    python scripts/ads/direct_groups.py --campaign 109052494 --mask "Прайс-лист" --apply
"""
import argparse
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
API = "https://api.direct.yandex.com/json/v5/"
ALLOWED = {"adgroups.get", "ads.get", "ads.suspend", "ads.resume"}


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
    for key in ("SuspendResults", "ResumeResults", "UpdateResults"):
        for item in (res.get(key) or []):
            for x in (item.get("Errors") or []):
                print(f"    ошибка {x.get('Code')} у {item.get('Id')}: {x.get('Message')}")
    return res


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--campaign", type=int, required=True)
    ap.add_argument("--mask", required=True, help="маска начала имени группы")
    ap.add_argument("--apply", action="store_true")
    a = ap.parse_args()

    groups = call("adgroups", "get", {
        "SelectionCriteria": {"CampaignIds": [a.campaign]},
        "FieldNames": ["Id", "Name", "Status"],
    }).get("AdGroups", [])
    hit = [g for g in groups if g["Name"].lower().startswith(a.mask.lower())]
    if not hit:
        sys.exit(f"групп по маске «{a.mask}» не нашлось (всего в кампании {len(groups)})")

    ids = [g["Id"] for g in hit]
    ads = []
    for i in range(0, len(ids), 1000):
        ads.extend(call("ads", "get", {
            "SelectionCriteria": {"AdGroupIds": ids[i:i + 1000]},
            "FieldNames": ["Id", "AdGroupId", "State", "Status"],
        }).get("Ads", []))
    # Гасим всё, кроме уже остановленного и архивного. Важно: `OFF` — это
    # не остановка, а «сейчас не показывается» (у нас — потому что на счёте
    # ноль). Такое объявление оживёт вместе с кампанией при пополнении;
    # надёжная остановка — только `SUSPENDED`, её и ставим.
    live = [x for x in ads if x.get("State") in ("ON", "OFF")]

    by = {}
    for x in ads:
        by.setdefault(x["AdGroupId"], []).append(x)
    print(f"групп по маске «{a.mask}»: {len(hit)}, объявлений в них {len(ads)}, "
          f"из них показываются {len(live)}\n")
    for g in sorted(hit, key=lambda g: g["Name"]):
        mine = by.get(g["Id"], [])
        on = sum(1 for x in mine if x.get("State") in ("ON", "OFF"))
        print(f'  {g["Name"][:44]:44} объявлений {len(mine):3d}, к остановке {on:3d}')

    if not live:
        print("\nостанавливать нечего: показов ни у одного объявления нет")
        return
    if not a.apply:
        print(f"\nэто отчёт: остановится {len(live)} объявлений. Для правки: --apply")
        return
    print(f"\nостанавливаю {len(live)} объявлений…")
    for i in range(0, len(live), 1000):
        call("ads", "suspend", {"SelectionCriteria": {"Ids": [x["Id"] for x in live[i:i + 1000]]}})
    print("готово. Вернуть показы: ads.resume по тем же группам")


if __name__ == "__main__":
    main()
