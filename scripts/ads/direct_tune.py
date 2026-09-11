# -*- coding: utf-8 -*-
"""Настройка кампаний Директа: стратегии и минус-слова.

Две правки, которые дают больше всего и стоят дешевле всего.

**Стратегии.** «Оптимизация ТГО» стояла на `HIGHEST_POSITION` — наивысшая
позиция без потолка ставки, отсюда 99 ₽ за клик и 215 209 ₽ за год при
19 заявках. Одиннадцать товарных кампаний — на средней цене клика 100 ₽,
хотя фактический CPC у них 26–47 ₽: потолок не ограничивает, но и не
страхует от разгона. Ставим осмысленные значения.

**Минус-слова.** В аккаунте их 274–310 на кампанию, но собраны не там, где
нужно: `dwg`, `autocad`, марки чужих брендов есть, а «прайс» — ни в одной
кампании, хотя прайсовые запросы съели около 50 000 ₽ без единой заявки.
Добавляем недостающее, existing не трогаем.

Директ схлопывает минус-слова по лемме: «косые» исчезает, если в списке
уже есть «косой», «стоит» — если есть «стоящие». В сверке до/после это
выглядит как потеря, но минусация работает по всем формам слова.

Слова «цена» и «стоимость» намеренно НЕ минусуются: это намерение купить,
и раньше они не работали из-за посадочной — объявления вели на страницу
прайса, которой больше нет. Теперь ссылки исправлены, стоит перемерить.

    python scripts/ads/direct_tune.py strategies
    python scripts/ads/direct_tune.py strategies --apply
    python scripts/ads/direct_tune.py negatives --apply
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
ALLOWED = {"campaigns.get", "campaigns.update"}

# Не трогаем: ассортимента нет (решение заказчика) и наша новая кампания.
SKIP = ("Запорная арматура", "Ретаргетинг: клиенты Промэнергетики")

# Потолок средней цены клика. Фактический CPC товарных кампаний, где были
# заявки, — 26–47 ₽; 60 ₽ не режет объём, но страхует от разгона.
# «Оптимизация ТГО» получает 50 ₽ вместо ставки без потолка.
CPC_DEFAULT = 60
CPC_SPECIAL = {"Оптимизация ТГО": 50}
WEEKLY_DEFAULT = 3_000
WEEKLY_SPECIAL = {"Оптимизация ТГО": 5_000}

# Чего не было ни в одной кампании. Проверено по выгрузке 09.09.2026:
# «прайс» — 0 кампаний из 25, «теплица» — 0, «калькулятор» — 1.
# Только одна форма слова: Директ минусует по лемме, «теплицы» и «дачный»
# схлопнутся с «теплица» и «дача». Но «бу» и «б у» — РАЗНЫЕ минус-слова:
# 11.09.2026 запрос «трубы б у большого диаметра» прошёл при наличии «бу».
NEGATIVES = [
    "прайс", "прайслист", "прейскурант", "расценки",
    "бу", "б у", "бесплатно", "даром", "скачать", "чертеж",
    "реферат", "курсовая", "дипломная", "учебник", "википедия",
    "что такое", "своими руками", "как сделать", "самостоятельно",
    "вакансии", "работа", "резюме", "зарплата",
    "авито", "юла", "объявления",
    "теплица", "дача", "парник",
    "ремонт", "аренда", "прокат", "демонтаж", "утилизация", "лом",
]


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
    for item in (res.get("UpdateResults") or []):
        for x in (item.get("Errors") or []):
            print(f"    ошибка {x.get('Code')} у {item.get('Id')}: {x.get('Message')} {x.get('Details','')}")
        for w in (item.get("Warnings") or []):
            print(f"    предупреждение у {item.get('Id')}: {w.get('Message')}")
    return res


def campaigns():
    res = call("campaigns", "get", {
        "SelectionCriteria": {},
        "FieldNames": ["Id", "Name", "State", "NegativeKeywords"],
        "TextCampaignFieldNames": ["BiddingStrategy"],
    })
    return [c for c in res.get("Campaigns", [])
            if c.get("State") != "ARCHIVED"
            and not any(s in c["Name"] for s in SKIP)]


def search_strategy(c):
    return ((c.get("TextCampaign") or {}).get("BiddingStrategy") or {}).get("Search") or {}


def limits_for(name):
    cpc = next((v for k, v in CPC_SPECIAL.items() if k in name), CPC_DEFAULT)
    weekly = next((v for k, v in WEEKLY_SPECIAL.items() if k in name), WEEKLY_DEFAULT)
    return cpc, weekly


def do_strategies(apply):
    updates = []
    print(f'{"кампания":44} {"было":34} станет')
    for c in campaigns():
        s = search_strategy(c)
        kind = s.get("BiddingStrategyType")
        if kind in (None, "SERVING_OFF"):
            continue                      # чисто сетевые кампании не трогаем
        cpc, weekly = limits_for(c["Name"])
        # Существующий недельный лимит не трогаем: у «Стандартов» стоит
        # 5 000 и 4 000 ₽, и понизить их до дефолтных 3 000 значило бы
        # урезать лучшую по CPA кампанию аккаунта.
        current = next((v.get("WeeklySpendLimit") for v in s.values()
                        if isinstance(v, dict) and v.get("WeeklySpendLimit")), None)
        if current:
            weekly = int(current / 1_000_000)
        was = kind
        if kind == "AVERAGE_CPC":
            was = f'средняя {(s.get("AverageCpc") or {}).get("AverageCpc", 0) / 1e6:.0f} ₽'
        print(f'  {c["Name"][:42]:42} {was[:34]:34} средняя {cpc} ₽, лимит {weekly:,} ₽/нед'.replace(",", " "))
        updates.append({
            "Id": c["Id"],
            "TextCampaign": {"BiddingStrategy": {
                "Search": {
                    "BiddingStrategyType": "AVERAGE_CPC",
                    "AverageCpc": {"AverageCpc": cpc * 1_000_000,
                                   "WeeklySpendLimit": weekly * 1_000_000},
                },
                "Network": {"BiddingStrategyType": "MAXIMUM_COVERAGE"}
                if not (((c.get("TextCampaign") or {}).get("BiddingStrategy") or {}).get("Network") or {})
                else ((c.get("TextCampaign") or {}).get("BiddingStrategy") or {}).get("Network"),
            }},
        })
    if not apply:
        print(f"\nэто отчёт: {len(updates)} кампаний. Для правки: --apply")
        return
    print(f"\nобновляю {len(updates)} кампаний…")
    for i in range(0, len(updates), 10):
        call("campaigns", "update", {"Campaigns": updates[i:i + 10]})
    print("готово")


def norm(word):
    """Как Директ хранит минус-слово, чтобы повторный прогон не плодил дубли.

    При приёме списка Директ его переписывает: ставит `!` перед служебными
    словами («как сделать» → «!как сделать», «б у» → «б !у»), меняет дефис
    на пробел («18599-2001» → «18599 2001»), снимает кавычки и приводит `ё`
    к `е`. Без нормализации сверка не узнаёт собственную работу и каждый
    запуск добавляет одни и те же 6 слов заново.
    """
    w = word.lower().replace("ё", "е").replace("-", " ")
    w = w.replace("!", "").replace("+", "").replace('"', "").replace("[", "").replace("]", "")
    return " ".join(w.split())


def do_negatives(apply):
    updates, added_total = [], 0
    for c in campaigns():
        items = ((c.get("NegativeKeywords") or {}).get("Items") or [])
        have = {norm(w) for w in items}
        # Слово считается имеющимся и когда в списке лежит другая его форма:
        # «чертеж» не добавится к существующему «чертежи», Директ их схлопнет
        # по лемме. Сравниваем по общему началу от пяти букв — короче брать
        # опасно, «труба» и «трубка» разошлись бы в одно.
        def known(w):
            n = norm(w)
            return n in have or any(
                len(n) >= 5 and (h.startswith(n) or n.startswith(h)) and abs(len(h) - len(n)) <= 3
                for h in have)
        add = [w for w in NEGATIVES if not known(w)]
        if not add:
            continue
        merged = list(items) + add
        added_total += len(add)
        print(f'  {c["Name"][:44]:44} было {len(have):3d}, добавляем {len(add):2d} → {len(merged)}')
        updates.append({"Id": c["Id"], "NegativeKeywords": {"Items": merged}})
    print(f"\nвсего добавлений: {added_total} в {len(updates)} кампаниях")
    if not apply:
        print("это отчёт. Для правки: --apply")
        return
    for i in range(0, len(updates), 10):
        call("campaigns", "update", {"Campaigns": updates[i:i + 10]})
    print("готово")


if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("what", choices=["strategies", "negatives"])
    ap.add_argument("--apply", action="store_true")
    a = ap.parse_args()
    (do_strategies if a.what == "strategies" else do_negatives)(a.apply)
