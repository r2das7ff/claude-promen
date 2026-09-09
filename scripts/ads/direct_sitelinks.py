# -*- coding: utf-8 -*-
"""Тематические наборы быстрых ссылок под каждую кампанию.

Было: один набор 1508803715 на все кампании — каталог СДТ, калькуляторы,
проекты, контакты. Он рабочий, но общий: человек по запросу «отвод гост
17375» видит под объявлением ссылку на весь каталог, а не на свой раздел.

Директ разрешает до восьми ссылок, и заполнять их стоит: это площадь
объявления и дополнительные входы в нужный срез каталога. Ограничения —
заголовок 30 символов, описание 60, **описания внутри набора не должны
повторяться** (ошибка 6000).

Ссылки ведут на те же фасеты, что и сами группы: `gost`, `steel`, `angle`,
`industry` — проверенные срезы с реальным наполнением.

    python scripts/ads/direct_sitelinks.py plan
    python scripts/ads/direct_sitelinks.py apply
"""
import argparse
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
DATA = os.path.join(ROOT, "perf-reports", "ads", "2026-09-09")
API = "https://api.direct.yandex.com/json/v5/"
ALLOWED = {"sitelinks.add", "sitelinks.get", "ads.get", "ads.add", "ads.delete", "ads.suspend"}

SITE = "https://prom-en.com"
UTM = ("utm_source=yandex&utm_medium=cpc&utm_campaign=cid|{campaign_id}|{source_type}"
       "&utm_content=gid|{gbid}|aid|{ad_id}|{phrase_id}_{retargeting_id}&utm_term={keyword}")

# По восемь ссылок на кампанию: четыре в свой раздел каталога и четыре
# общих. Заголовок ≤ 30, описание ≤ 60, описания в наборе уникальны.
SETS = {
    714272467: [  # Отводы
        ("Отводы ГОСТ 17375", "/catalog/sdt/otvody/?gost=gost-17375-2001", "Крутоизогнутые приварные по ГОСТ 17375-2001"),
        ("Отводы гнутые", "/catalog/sdt/otvody/?gost=gost-30753-2001", "Гнутые отводы по ГОСТ 30753-2001"),
        ("Отводы 90 градусов", "/catalog/sdt/otvody/?angle=90", "Самый ходовой угол, подбор по Ду и стенке"),
        ("Нержавеющие отводы", "/catalog/sdt/otvody/?steel=12h18n10t", "Сталь 12Х18Н10Т для агрессивных сред"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
    714272480: [  # Тройники
        ("Тройники ГОСТ 17376", "/catalog/sdt/troyniki/?gost=gost-17376-2001", "Приварные тройники по ГОСТ 17376-2001"),
        ("Тройники по ОСТ и СТО", "/catalog/sdt/troyniki/", "Отраслевые стандарты энергетики"),
        ("Нержавеющие тройники", "/catalog/sdt/troyniki/?steel=12h18n10t", "Сталь 12Х18Н10Т, агрессивные среды"),
        ("Тройники для АЭС", "/catalog/sdt/troyniki/?industry=aes", "Исполнение под требования атомных станций"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
    714272489: [  # Переходы
        ("Переходы ГОСТ 17378", "/catalog/sdt/perekhody/?gost=gost-17378-2001", "Концентрические и эксцентрические по ГОСТ"),
        ("Переходы по ОСТ и СТО", "/catalog/sdt/perekhody/", "Отраслевые стандарты энергетики"),
        ("Нержавеющие переходы", "/catalog/sdt/perekhody/?steel=12h18n10t", "Сталь 12Х18Н10Т, агрессивные среды"),
        ("Переходы для АЭС", "/catalog/sdt/perekhody/?industry=aes", "Исполнение под требования атомных станций"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
    714272498: [  # Опоры
        ("Опоры ОСТ 36-17-85", "/catalog/opory/?gost=ost-36-17-85", "Опоры трубопроводов по ОСТ 36-17-85"),
        ("Опоры для АЭС", "/catalog/opory/?industry=aes", "Исполнение под требования атомных станций"),
        ("Опоры для ТЭС", "/catalog/opory/?industry=tes", "Тепловые станции и котельные"),
        ("Каталог СДТ", "/catalog/sdt/", "Отводы, тройники, переходы, заглушки, днища"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
    714272511: [  # Трубы
        ("Трубы ГОСТ 8732", "/catalog/truby/?gost=gost-8732-1978", "Бесшовные горячедеформированные, 595 позиций"),
        ("Трубы ГОСТ 10704", "/catalog/truby/?gost=gost-10704-1991", "Электросварные прямошовные, 522 позиции"),
        ("Трубы сталь 20", "/catalog/truby/?steel=20", "Ходовая марка для трубопроводов"),
        ("Трубы для ТЭС", "/catalog/truby/?industry=tes", "Тепловые станции и котельные"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
    714275030: [  # Детали для АЭС и ТЭС
        ("Детали для АЭС", "/catalog/sdt/?industry=aes", "Срез каталога под атомные станции"),
        ("Детали для ТЭС", "/catalog/sdt/?industry=tes", "Срез каталога под тепловые станции"),
        ("Нормативная база", "/normativnaya-baza/", "ГОСТ, ОСТ, СТО и СТО ЦКТИ в одном месте"),
        ("Отводы по СТО", "/catalog/sdt/otvody/?gost=sto-95-115-2013", "Отводы по СТО 95 115-2013"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
    714267536: [  # Ретаргетинг по базе клиентов
        ("Каталог СДТ", "/catalog/sdt/", "Отводы, тройники, переходы, заглушки, днища"),
        ("Трубы", "/catalog/truby/", "Бесшовные и электросварные, 1 468 позиций"),
        ("Опоры трубопроводов", "/catalog/opory/", "Скользящие, неподвижные, хомутовые"),
        ("Нормативная база", "/normativnaya-baza/", "ГОСТ, ОСТ, СТО и СТО ЦКТИ в одном месте"),
        ("Калькуляторы", "/kalkulyatory/", "Масса деталей, комплект крепежа, метры в тонны"),
        ("Производство", "/production/", "Цех, станки, контроль и допуски"),
        ("Проекты", "/proekty/", "Курская АЭС, Аккую, Руппур, ТЭЦ"),
        ("Контакты", "/contacts/", "Отдел продаж и запрос коммерческого предложения"),
    ],
}

CALLOUTS = [44350925, 44350926, 44350927, 44350928]
BUSINESS = 131623088486


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
                      timeout=300)
    d = r.json()
    if "error" in d:
        sys.exit(f"{full} → {(d['error'].get('error_detail') or '')[:200]}")
    res = d.get("result", {})
    for key in ("AddResults", "DeleteResults", "SuspendResults"):
        for item in (res.get(key) or []):
            for e in (item.get("Errors") or []):
                print(f"    ошибка {e.get('Code')}: {e.get('Message')} {e.get('Details', '')}")
    return res


def check_limits():
    bad = []
    for cid, links in SETS.items():
        descs = [d for _, _, d in links]
        if len(set(descs)) != len(descs):
            bad.append(f"{cid}: описания повторяются")
        for title, href, desc in links:
            if len(title) > 30:
                bad.append(f"{cid}: заголовок «{title}» {len(title)} символов")
            if len(desc) > 60:
                bad.append(f"{cid}: описание «{desc[:30]}…» {len(desc)} символов")
    return bad


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("what", choices=["plan", "apply"])
    a = ap.parse_args()

    bad = check_limits()
    if bad:
        print("не проходит по ограничениям Директа:")
        for b in bad:
            print("  ", b)
        sys.exit(1)
    print(f"наборов: {len(SETS)}, в каждом по {len(next(iter(SETS.values())))} ссылок — лимиты в норме\n")
    for cid, links in SETS.items():
        print(f"кампания {cid}:")
        for t, h, d in links:
            print(f'   {t:24} {h}')
    if a.what == "plan":
        print("\nэто отчёт. Создание: apply")
        return

    created = {}
    for cid, links in SETS.items():
        res = call("sitelinks", "add", {"SitelinksSets": [{"Sitelinks": [
            {"Title": t, "Href": f"{SITE}{h}{'&' if '?' in h else '?'}{UTM}", "Description": d}
            for t, h, d in links]}]})
        sid = (res.get("AddResults") or [{}])[0].get("Id")
        if not sid:
            print(f"  кампания {cid}: набор не создался")
            continue
        created[cid] = sid
        print(f"  кампания {cid}: набор {sid}")

    # SitelinkSetId правится только при создании объявления — как и уточнения,
    # поэтому объявления пересоздаём: сначала новые, потом гасим старые.
    for cid, sid in created.items():
        old = call("ads", "get", {"SelectionCriteria": {"CampaignIds": [cid]},
                                  "FieldNames": ["Id", "AdGroupId", "State"],
                                  "TextAdFieldNames": ["Title", "Title2", "Text", "Href", "Mobile"]})["Ads"]
        old = [x for x in old if x.get("State") != "SUSPENDED"]
        fresh = []
        for x in old:
            t = x["TextAd"]
            ad = {"AdGroupId": x["AdGroupId"], "TextAd": {
                "Title": t["Title"], "Text": t["Text"], "Href": t["Href"],
                "Mobile": t.get("Mobile", "NO"), "SitelinkSetId": sid,
                "AdExtensionIds": CALLOUTS, "BusinessId": BUSINESS}}
            if t.get("Title2"):
                ad["TextAd"]["Title2"] = t["Title2"]
            fresh.append(ad)
        made = []
        for i in range(0, len(fresh), 100):
            r = call("ads", "add", {"Ads": fresh[i:i + 100]})
            made.extend([x.get("Id") for x in (r.get("AddResults") or []) if x.get("Id")])
        if len(made) == len(old):
            call("ads", "delete", {"SelectionCriteria": {"Ids": [x["Id"] for x in old]}})
            print(f"  кампания {cid}: пересоздано {len(made)} объявлений")
        else:
            print(f"  кампания {cid}: создано {len(made)} из {len(old)} — старые оставлены")
    json.dump(created, open(os.path.join(DATA, "sitelink-sets.json"), "w", encoding="utf-8"),
              ensure_ascii=False, indent=1)


if __name__ == "__main__":
    main()
