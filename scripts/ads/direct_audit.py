# -*- coding: utf-8 -*-
"""Полный аудит кампаний: каждый элемент, а не выборочно.

Поводом стала череда пропусков: организация не проставилась в одной
кампании, уточнения не были привязаны нигде, счётчик профиля организации
не попал в новые кампании, набор быстрых ссылок оказался общим и куцым.
Каждый раз это находилось после вопроса заказчика, а не при проверке.

Скрипт обходит **все** уровни — кампанию, группы, объявления, фразы,
корректировки — и по каждому пункту говорит, что настроено, а что нет.
Список полей взят у самого API (неверное значение в `FieldNames` заставляет
Директ перечислить допустимые), поэтому пропустить элемент нельзя.

    python scripts/ads/direct_audit.py
    python scripts/ads/direct_audit.py --campaigns 714272467,714275030
"""
import argparse
import collections
import json
import os
import sys

import requests

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

ROOT = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", ".."))
DATA = os.path.join(ROOT, "perf-reports", "ads", "2026-09-09")
API = "https://api.direct.yandex.com/json/"
ALLOWED = {"campaigns.get", "adgroups.get", "ads.get", "keywords.get", "bidmodifiers.get",
           "sitelinks.get", "adextensions.get", "audiencetargets.get", "businesses.get"}

NEW = [714272467, 714272480, 714272489, 714272498, 714272511, 714275030, 714267536]

CAMPAIGN_FIELDS = ["Id", "Name", "State", "Status", "StatusPayment", "StartDate", "EndDate",
                   "TimeZone", "TimeTargeting", "DailyBudget", "ExcludedSites", "BlockedIps",
                   "NegativeKeywords", "Notification", "Currency", "Funds", "Type"]
UNIFIED_FIELDS = ["CounterIds", "Settings", "BiddingStrategy", "PriorityGoals", "TrackingParams",
                  "AttributionModel", "PackageBiddingStrategy", "NegativeKeywordSharedSetIds",
                  "DefaultBusinessId", "DefaultPhoneId", "WeeklyBudgetRollover",
                  "CanBeUsedAsPackageBiddingStrategySource"]
AD_FIELDS = ["AdImageHash", "LogoExtensionHash", "DisplayDomain", "Href", "SitelinkSetId",
             "Text", "Title", "Title2", "Mobile", "VCardId", "DisplayUrlPath", "AdExtensions",
             "VideoExtension", "TurboPageId", "BusinessId", "TrackingPhoneId",
             "PreferVCardOverBusiness", "ButtonExtension"]
# TrackingParams есть у кампании, но не у объявления — с ним ads.get падает.
GROUP_FIELDS = ["Id", "Name", "CampaignId", "RegionIds", "NegativeKeywords", "TrackingParams",
                "Status", "Type", "Subtype", "ServingStatus"]


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


def call(service, method, params, ver="v5"):
    full = f"{service}.{method}"
    if full not in ALLOWED:
        sys.exit(f"ОТКАЗ: {full} вне списка разрешённых")
    r = requests.post(API + ver + "/" + service, headers=HEADERS,
                      data=json.dumps({"method": method, "params": params}).encode("utf-8"),
                      timeout=300)
    d = r.json()
    if "error" in d:
        # Молчаливый пустой результат — худшее, что может сделать аудит:
        # таблица покажет нули, и решишь, что настройки нет. Падаем громко.
        sys.exit(f"ОШИБКА {full}: {(d['error'].get('error_detail') or '')[:200]}")
    return d.get("result", {})


def paged(service, params, key, ver="v5"):
    out, offset = [], 0
    while True:
        p = dict(params)
        p["Page"] = {"Limit": 10000, "Offset": offset}
        r = call(service, "get", p, ver=ver)
        out.extend(r.get(key, []))
        if not r.get("LimitedBy"):
            return out
        offset = r["LimitedBy"]


def collect(ids):
    """Всё, что API знает о кампаниях, группах, объявлениях и фразах."""
    camps = call("campaigns", "get", {"SelectionCriteria": {"Ids": ids},
                                      "FieldNames": CAMPAIGN_FIELDS,
                                      "UnifiedCampaignFieldNames": UNIFIED_FIELDS},
                 ver="v501").get("Campaigns", [])
    groups = call("adgroups", "get", {"SelectionCriteria": {"CampaignIds": ids},
                                      "FieldNames": GROUP_FIELDS}).get("AdGroups", [])
    ads = call("ads", "get", {"SelectionCriteria": {"CampaignIds": ids},
                              "FieldNames": ["Id", "CampaignId", "AdGroupId", "State", "Status",
                                             "Type", "Subtype"],
                              "TextAdFieldNames": AD_FIELDS}).get("Ads", [])
    kws = paged("keywords", {"SelectionCriteria": {"CampaignIds": ids},
                             "FieldNames": ["Id", "CampaignId", "AdGroupId", "Keyword", "Bid",
                                            "ServingStatus", "State", "Status",
                                            "AutotargetingSearchBidIsAuto"]}, "Keywords")
    mods = call("bidmodifiers", "get", {
        "SelectionCriteria": {"CampaignIds": ids, "Levels": ["CAMPAIGN", "AD_GROUP"]},
        "FieldNames": ["Id", "CampaignId", "Type", "Level"],
        "MobileAdjustmentFieldNames": ["BidModifier"],
        "DesktopAdjustmentFieldNames": ["BidModifier"],
        "DemographicsAdjustmentFieldNames": ["Age", "Gender", "BidModifier"],
        "RetargetingAdjustmentFieldNames": ["RetargetingConditionId", "BidModifier"],
        "RegionalAdjustmentFieldNames": ["RegionId", "BidModifier"],
    }).get("BidModifiers", [])
    audience = call("audiencetargets", "get", {
        "SelectionCriteria": {"CampaignIds": ids},
        "FieldNames": ["Id", "CampaignId", "AdGroupId", "RetargetingListId", "State"]}
    ).get("AudienceTargets", [])
    return camps, groups, ads, kws, mods, audience


def check(camps, groups, ads, kws, mods, audience):
    """По каждому пункту: что настроено в каждой кампании."""
    by_group = collections.defaultdict(list)
    for g in groups:
        by_group[g["CampaignId"]].append(g)
    by_ad = collections.defaultdict(list)
    for a in ads:
        by_ad[a["CampaignId"]].append(a)
    by_kw = collections.defaultdict(list)
    for k in kws:
        by_kw[k["CampaignId"]].append(k)
    by_mod = collections.defaultdict(list)
    for m in mods:
        by_mod[m["CampaignId"]].append(m)
    by_aud = collections.defaultdict(list)
    for x in audience:
        by_aud[x["CampaignId"]].append(x)

    rows = []
    for c in camps:
        uc = c.get("UnifiedCampaign") or {}
        st = {s["Option"]: s["Value"] for s in (uc.get("Settings") or [])}
        gs, adl = by_group[c["Id"]], by_ad[c["Id"]]
        kwl = [k for k in by_kw[c["Id"]] if not k["Keyword"].startswith("---")]
        auto = [k for k in by_kw[c["Id"]] if k["Keyword"].startswith("---")]
        ml = by_mod[c["Id"]]
        bs = (uc.get("BiddingStrategy") or {})
        search = (bs.get("Search") or {}).get("BiddingStrategyType")
        net = (bs.get("Network") or {}).get("BiddingStrategyType")
        strat = (bs.get("Search") or {}).get("WbMaximumClicks") or \
                (bs.get("Network") or {}).get("WbMaximumClicks") or {}
        live_ads = [a for a in adl if a.get("State") != "SUSPENDED"]
        regions = {tuple(sorted(g.get("RegionIds") or [])) for g in gs}
        rows.append({
            "id": c["Id"], "name": c["Name"],
            "Состояние": f'{c.get("State")}/{c.get("Status")}',
            "Стратегия": f'поиск {search}, сети {net}',
            "Недельный лимит": f'{strat.get("WeeklySpendLimit", 0) / 1e6:.0f} ₽' if strat else "—",
            "Потолок ставки": f'{strat.get("BidCeiling", 0) / 1e6:.0f} ₽' if strat.get("BidCeiling") else "нет",
            "Дневной бюджет": c.get("DailyBudget") or "нет",
            "Часовой пояс": c.get("TimeZone"),
            "Расписание": "круглосуточно" if not (c.get("TimeTargeting") or {}).get("Schedule") else "по часам",
            "Регионы групп": ", ".join(str(r) for r in sorted(regions)[0]) if regions else "нет",
            "Групп": len(gs), "Фраз": len(kwl), "Автотаргетинг-записей": len(auto),
            "Объявлений": len(live_ads),
            "Минус-слов": len((c.get("NegativeKeywords") or {}).get("Items") or []),
            "Общий стоп-лист": bool((uc.get("NegativeKeywordSharedSetIds") or {}).get("Items")),
            "Счётчики": uc.get("CounterIds", {}).get("Items"),
            "Ключевые цели": len((uc.get("PriorityGoals") or {}).get("Items") or []),
            "Атрибуция": uc.get("AttributionModel"),
            "Организация": uc.get("DefaultBusinessId") or "нет",
            "Номер коллтрекинга": uc.get("DefaultPhoneId") or "нет",
            "Запрещённых площадок": len((c.get("ExcludedSites") or {}).get("Items") or []),
            "Заблокированных IP": len((c.get("BlockedIps") or {}).get("Items") or []),
            "Метки кампании": uc.get("TrackingParams") or "нет",
            "Перенос бюджета": uc.get("WeeklyBudgetRollover") or "нет",
            "Пакетная стратегия": uc.get("PackageBiddingStrategy") or "нет",
            "Мониторинг сайта": st.get("ENABLE_SITE_MONITORING"),
            "Метка для Метрики": st.get("ADD_METRICA_TAG"),
            "Альтернативные тексты": st.get("ALTERNATIVE_TEXTS_ENABLED"),
            "Приоритет по фразе": st.get("CAMPAIGN_EXACT_PHRASE_MATCHING_ENABLED"),
            "Область интересов": st.get("ENABLE_AREA_OF_INTEREST_TARGETING"),
            "Уведомления": "да" if c.get("Notification") else "нет",
            "Корректировки": ", ".join(sorted({m["Type"] for m in ml})) or "нет",
            "Условия ретаргетинга": len(by_aud[c["Id"]]),
            # объявления
            "Заголовок 2": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("Title2")),
            "Быстрые ссылки": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("SitelinkSetId")),
            "Уточнения": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("AdExtensions")),
            "Организация в объявл.": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("BusinessId")),
            "Отображаемая ссылка": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("DisplayUrlPath")),
            "Изображение": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("AdImageHash")),
            "Видео": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("VideoExtension")),
            "Кнопка": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("ButtonExtension")),
            "Визитка": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("VCardId")),
            "Турбо-страница": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("TurboPageId")),
            "Телефон в объявл.": sum(1 for a in live_ads if (a.get("TextAd") or {}).get("TrackingPhoneId")),
            "Фраз «мало показов»": sum(1 for k in kwl if k.get("ServingStatus") == "RARELY_SERVED"),
            "Ставки на фразах": sum(1 for k in kwl if k.get("Bid")),
        })
    return rows


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--campaigns", default="")
    a = ap.parse_args()
    ids = [int(x) for x in a.campaigns.split(",")] if a.campaigns else NEW

    camps, groups, ads, kws, mods, audience = collect(ids)
    rows = check(camps, groups, ads, kws, mods, audience)
    rows.sort(key=lambda r: r["id"])

    keys = [k for k in rows[0] if k not in ("id", "name")]
    short = [r["name"].split("|")[0].strip()[:13] for r in rows]
    print(f'{"элемент":24} ' + " ".join(f'{n:>14}' for n in short))
    print("-" * (24 + 15 * len(rows)))
    for k in keys:
        vals = [str(r[k]) for r in rows]
        # ровные строки прячут расхождения — помечаем их явно
        flag = " ←" if len(set(vals)) > 1 else ""
        print(f'  {k:22} ' + " ".join(f'{v[:14]:>14}' for v in vals) + flag)

    out = os.path.join(DATA, "audit-full.json")
    json.dump(rows, open(out, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
    print(f"\nсохранено: {os.path.relpath(out, ROOT)}")


if __name__ == "__main__":
    main()
