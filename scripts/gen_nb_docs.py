# -*- coding: utf-8 -*-
"""Пересборка реестра DOCS в assets/js/nb.js из normatives/registry/normatives_master.csv.

Реестр страницы «Нормативная база» = документы архива нормативов завода
(сверены по титульным листам PDF) + блок обязательных документов, которые
архивом не покрываются, но действуют для всей продукции (ТР ТС, НП, контроль,
марки стали, сортамент труб).

Блок DOCS в nb.js ограничен маркерами:
  /* ==NB-DOCS-START== */ ... /* ==NB-DOCS-END== */
"""
from __future__ import annotations

import csv
import re
import sys
from pathlib import Path

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

ROOT = Path(__file__).resolve().parents[1]
CSV_PATH = ROOT.parent / "normatives" / "registry" / "normatives_master.csv"
NB_JS = ROOT / "wp-content/themes/promen/assets/js/nb.js"

START = "/* ==NB-DOCS-START== */"
END = "/* ==NB-DOCS-END== */"

# cat из реестра → (cat, sub) для nb.js
CAT_MAP = {
    "sdt": lambda sub: ("sdt", sub or "obshchie"),
    "fl": lambda sub: ("sdt", "flanci"),
    "op": lambda sub: ("op", ""),
    "zra": lambda sub: ("zra", ""),
    "upl": lambda sub: ("upl", ""),
    "krep": lambda sub: ("krep", ""),
    "iz": lambda sub: ("iz", ""),
    "ss": lambda sub: ("ss", ""),
}

# Порядок групп в выдаче — крупные семейства СДТ первыми.
SUB_ORDER = ["otvody", "troyniki", "perehody", "dnishcha", "zaglushki",
             "shtutsery", "flanci", "obshchie"]
CAT_ORDER = ["sdt", "op", "zra", "upl", "krep", "iz", "ss", "tr", "mat", "qc"]

# Документы вне архива нормативов: обязательные требования и общие стандарты,
# на которые ссылаются карточки товаров и разделы «База знаний».
EXTRA = [
    dict(cat="qc", type="tr", code="ТР ТС 032/2013",
         title="О безопасности оборудования, работающего под избыточным давлением",
         desc="Технический регламент Таможенного союза. Обязателен при PN > 0,05 МПа; основание декларации соответствия завода."),
    dict(cat="qc", type="decl", code="RU С-RU.АБ53.В.08323/23",
         title="Декларация о соответствии продукции завода требованиям ТР ТС 032/2013",
         desc="Серия RU 0418908. Действует на продукцию, изготовленную заводом «Промышленная Энергетика»."),
    dict(cat="qc", type="tu", code="ТУ 24.20.40-001-13842829-2023",
         title="Технические условия завода на детали трубопроводов",
         desc="Применяются при изготовлении по конструкторской документации заказчика и для позиций вне сортамента ГОСТ."),
    dict(cat="qc", type="np", code="НП-089-15",
         title="Правила устройства и безопасной эксплуатации оборудования и трубопроводов атомных энергетических установок",
         desc="Определяет категории трубопроводов I–IV и общие требования к оборудованию АЭУ."),
    dict(cat="qc", type="np", code="НП-045-18",
         title="Правила контроля сварных соединений оборудования и трубопроводов атомных энергетических установок",
         desc="Расширенный объём неразрушающего контроля для объектов АЭС."),
    dict(cat="qc", type="np", code="НП-068-05",
         title="Трубопроводная арматура для атомных станций. Общие технические требования",
         desc="Обязательный документ для арматуры, поставляемой на объекты АЭС."),
    dict(cat="qc", type="gost", code="ГОСТ ISO 10474-2016",
         title="Прокат стальной и стальная продукция. Документы о контроле",
         desc="Паспорт качества 3.1 с плавочными данными — входит в стандартный комплект поставки."),
    dict(cat="qc", type="gost", code="ГОСТ Р 55724-2013",
         title="Контроль неразрушающий. Соединения сварные. Ультразвуковой контроль. Методы",
         desc="Методика и оценка результатов УЗК сварных швов при приёмке деталей."),
    dict(cat="qc", type="gost", code="ГОСТ 16037-80",
         title="Соединения сварные стальных трубопроводов. Основные типы, конструктивные элементы и размеры",
         desc="Типовые сварные соединения при изготовлении узлов и монтаже трубопроводов."),
    dict(cat="tr", type="gost", code="ГОСТ 8732-78",
         title="Трубы стальные бесшовные горячедеформированные. Сортамент",
         desc="Базовый сортамент трубных заготовок для штамповки деталей трубопроводов."),
    dict(cat="tr", type="gost", code="ГОСТ 8734-75",
         title="Трубы стальные бесшовные холоднодеформированные. Сортамент",
         desc="Точные размеры малых диаметров, повышенная точность стенки."),
    dict(cat="tr", type="gost", code="ГОСТ 10704-91",
         title="Трубы стальные электросварные прямошовные. Сортамент",
         desc="Сортамент сварных прямошовных труб общего назначения."),
    dict(cat="tr", type="gost", code="ГОСТ 10705-80",
         title="Трубы стальные электросварные. Технические условия",
         desc="Общие технические требования к электросварным трубам."),
    dict(cat="tr", type="gost", code="ГОСТ 3262-75",
         title="Трубы стальные водогазопроводные. Технические условия",
         desc="Трубы для систем ЖКХ, водо- и газоснабжения низкого давления."),
    dict(cat="mat", type="gost", code="ГОСТ 1050-2013",
         title="Металлопродукция из нелегированных конструкционных качественных сталей",
         desc="Марки 10, 20, 35, 45 — основной материал общепромышленных деталей, до +425 °C."),
    dict(cat="mat", type="gost", code="ГОСТ 19281-2014",
         title="Прокат повышенной прочности. Общие технические условия",
         desc="Марка 09Г2С — хладостойкое и северное исполнение, −70…+475 °C."),
    dict(cat="mat", type="gost", code="ГОСТ 5632-2014",
         title="Нержавеющие стали и сплавы коррозионно-стойкие, жаростойкие и жаропрочные. Марки",
         desc="12Х18Н10Т, 08Х18Н10Т, 10Х17Н13М2Т — АЭС и химически агрессивные среды."),
    dict(cat="mat", type="ost", code="ОСТ 108.030.118-78",
         title="Трубы бесшовные для паровых котлов и трубопроводов. Технические условия",
         desc="Марки 15ГС, 12Х1МФ, 15Х1М1Ф для паропроводов ТЭС до +570 °C."),
]


def js(s: str) -> str:
    return s.replace("\\", "\\\\").replace("'", "\\'")


def main() -> None:
    rows = list(csv.DictReader(CSV_PATH.open(encoding="utf-8-sig")))
    docs = []
    for r in rows:
        cat_key = (r.get("cat") or "").strip()
        if cat_key not in CAT_MAP:
            continue  # cat=none — посторонние файлы в реестр сайта не попадают
        cat, sub = CAT_MAP[cat_key]((r.get("sub") or "").strip())
        desc = (r.get("products") or "").strip()
        note = (r.get("note") or "").strip()
        # В карточку выносим только содержательные примечания, а не служебные
        # заметки о раскладке файлов в архиве.
        if note and not note.startswith(("ОШИБКА РАСКЛАДКИ", "Две копии", "Четыре файла",
                                         "Пять файлов", "Два файла", "Семь файлов",
                                         "Имя файла")):
            desc = f"{desc}. {note}" if desc and desc != "—" else note
        docs.append(dict(cat=cat, sub=sub, type=(r.get("doc_type") or "gost").strip(),
                         code=r["full_designation"].strip(),
                         title=r["title"].strip(), desc=desc,
                         status=(r.get("status") or "active").strip(),
                         replacement=(r.get("replacement") or "").strip()))
    docs.extend(dict(sub="", status="active", replacement="", **e) for e in EXTRA)

    def key(d):
        c = CAT_ORDER.index(d["cat"]) if d["cat"] in CAT_ORDER else 99
        s = SUB_ORDER.index(d["sub"]) if d["sub"] in SUB_ORDER else 99
        return (c, s, d["code"])

    docs.sort(key=key)

    lines = ["const DOCS=["]
    for d in docs:
        parts = [f"cat:'{d['cat']}'"]
        if d["sub"]:
            parts.append(f"sub:'{d['sub']}'")
        parts.append(f"type:'{d['type']}'")
        parts.append(f"code:'{js(d['code'])}'")
        parts.append(f"title:'{js(d['title'])}'")
        parts.append(f"desc:'{js(d['desc'])}'")
        if d["status"] == "replaced" and d["replacement"]:
            parts.append(f"replacedBy:'{js(d['replacement'])}'")
        elif d["status"] == "missing":
            parts.append("noFile:true")
        lines.append("  {" + ",".join(parts) + "},")
    lines.append("];")
    block = "\n".join(lines)

    src = NB_JS.read_text(encoding="utf-8")
    new = re.sub(
        re.escape(START) + r".*?" + re.escape(END),
        START + "\n" + block + "\n" + END,
        src, flags=re.S,
    )
    if new == src:
        print("МАРКЕРЫ NB-DOCS НЕ НАЙДЕНЫ — nb.js не изменён")
        sys.exit(1)
    NB_JS.write_text(new, encoding="utf-8")
    print(f"OK: {len(docs)} документов записано в {NB_JS.name}")


if __name__ == "__main__":
    main()
