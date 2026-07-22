#!/usr/bin/env python3
"""Генерация markdown-контента категорий и нормативов из агрегатов CSV + реестра.

Факты только из:
  content/aggregates.json
  content/norm_aggregates.json
  ../normatives/registry/normatives_master.csv

Пилот sdt.md / otvody.md не перезаписываем.
"""
from __future__ import annotations

import csv
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
AGG = json.loads((ROOT / "content/aggregates.json").read_text(encoding="utf-8"))
NAGG = json.loads((ROOT / "content/norm_aggregates.json").read_text(encoding="utf-8"))
REG_PATH = ROOT.parent / "normatives/registry/normatives_master.csv"

CAT_SLUGS = {
    "отводы": "otvody",
    "тройники": "troyniki",
    "переходы": "perekhody",
    "заглушки": "zaglushki",
    "днища": "dnishcha",
    "фланцы": "flancy",
    "крепеж": "krepezh",
}

# Пилоты уже написаны вручную — не трогаем.
SKIP_CATS = {"sdt", "otvody"}

FASTENER_CHILDREN = [
    ("bolty", "Болты", "болт"),
    ("gayki", "Гайки", "гайк"),
    ("shpilki", "Шпильки", "шпильк"),
    ("shayby", "Шайбы", "шайб"),
    ("vinty", "Винты", "винт"),
]

CAT_INTRO = {
    "тройники": "Стальные приварные тройники для ответвления от магистрали: равнопроходные и переходные исполнения по ГОСТ, ОСТ и отраслевым стандартам энергетики.",
    "переходы": "Концентрические и эксцентрические переходы для стыковки участков трубопровода разных диаметров — штампованные, точёные и сварные исполнения.",
    "заглушки": "Эллиптические и плоские заглушки для герметичного закрытия концов трубопровода и аппаратов на время монтажа, испытаний и эксплуатации.",
    "днища": "Эллиптические отбортованные днища для сосудов, аппаратов и коллекторов — геометрия и толщина стенки по ГОСТ 6533.",
    "фланцы": "Приварные и воротниковые фланцы для разъёмных соединений трубопроводов по ГОСТ 12820/12821, ГОСТ 33259 и смежным стандартам.",
    "крепеж": "Крепёжные изделия для фланцевых соединений и общепромышленного монтажа: болты, гайки, шпильки, шайбы и винты по ГОСТ и ОСТ.",
}

CAT_PURPOSE = {
    "тройники": "ответвление потока от магистрали без сварного вреза «по месту»",
    "переходы": "изменение условного прохода на участке трассы с сохранением прочности стыка",
    "заглушки": "глухое закрытие торца трубы или штуцера",
    "днища": "закрытие цилиндрической обечайки сосуда или коллектора",
    "фланцы": "разъёмное соединение труб и арматуры с возможностью обслуживания",
    "крепеж": "сборка фланцевых пар и крепление оборудования",
}


def fmt_n(n: float | int) -> str:
    if isinstance(n, float) and n == int(n):
        n = int(n)
    return f"{int(n):,}".replace(",", " ")


def fmt_dn(v) -> str:
    if v is None:
        return "—"
    if isinstance(v, float) and v == int(v):
        v = int(v)
    return str(v)


def steels_line(steels: dict) -> str:
    items = sorted(steels.items(), key=lambda x: -x[1])
    return ", ".join(f"{k} ({fmt_n(v)})" for k, v in items[:8])


def norms_table(norms: dict, limit: int = 10) -> str:
    rows = sorted(norms.items(), key=lambda x: -x[1])[:limit]
    lines = ["<tr><th>Норматив</th><th>Позиций</th></tr>"]
    for name, cnt in rows:
        slug = translit_norm(name)
        lines.append(
            f'<tr><td><a href="/normativy/{slug}/">{name}</a></td><td>{fmt_n(cnt)}</td></tr>'
        )
    return "<table>\n" + "\n".join(lines) + "\n</table>"


def translit_norm(name: str) -> str:
    """Совпадает с promen_translit логикой для типовых ключей."""
    m = {
        "а": "a", "б": "b", "в": "v", "г": "g", "д": "d", "е": "e", "ё": "e",
        "ж": "zh", "з": "z", "и": "i", "й": "y", "к": "k", "л": "l", "м": "m",
        "н": "n", "о": "o", "п": "p", "р": "r", "с": "s", "т": "t", "у": "u",
        "ф": "f", "х": "h", "ц": "c", "ч": "ch", "ш": "sh", "щ": "sch",
        "ъ": "", "ы": "y", "ь": "", "э": "e", "ю": "yu", "я": "ya",
    }
    t = name.lower()
    out = []
    for ch in t:
        out.append(m.get(ch, ch))
    s = "".join(out)
    s = re.sub(r"[^a-z0-9]+", "-", s)
    return re.sub(r"-+", "-", s).strip("-")


def load_registry() -> dict[str, dict]:
    if not REG_PATH.exists():
        return {}
    by_key: dict[str, dict] = {}
    with REG_PATH.open(encoding="utf-8-sig") as f:
        for row in csv.DictReader(f):
            key = (row.get("full_designation") or row.get("normalized_key") or "").strip()
            if key:
                by_key[key] = row
            nk = (row.get("normalized_key") or "").strip()
            if nk:
                by_key[nk] = row
    return by_key


def write_category(slug: str, ru_key: str, data: dict) -> None:
    if slug in SKIP_CATS:
        return
    path = ROOT / "content/category" / f"{slug}.md"
    dn_min, dn_max = data.get("dn_min"), data.get("dn_max")
    pn_min, pn_max = data.get("pn_min"), data.get("pn_max")
    count = data.get("count", 0)
    steels = data.get("steels") or {}
    norms = data.get("norms") or {}
    types = data.get("types") or {}
    angles = data.get("angles") or {}

    intro = CAT_INTRO.get(ru_key, f"Изделия категории «{ru_key}» в реестре завода.")
    purpose = CAT_PURPOSE.get(ru_key, "применение в трубопроводных системах")

    dn_line = ""
    if ru_key != "крепеж" and dn_min is not None and dn_max is not None and float(dn_max) > 0 and float(dn_max) < 5000:
        dn_line = f"DN {fmt_dn(dn_min)}–{fmt_dn(dn_max)}"
    pn_line = ""
    if ru_key != "крепеж" and pn_min is not None and pn_max is not None and float(pn_max) > 0:
        pn_line = f"PN {fmt_dn(pn_min)}–{fmt_dn(pn_max)}"

    range_bits = [b for b in [dn_line, pn_line, f"{fmt_n(count)} типоразмеров"] if b]
    lead = f"{intro} В каталоге — {', '.join(range_bits)}."

    type_block = ""
    if types:
        rows = ["<tr><th>Тип / код</th><th>Позиций</th></tr>"]
        for t, c in sorted(types.items(), key=lambda x: -x[1]):
            rows.append(f"<tr><td>{t}</td><td>{fmt_n(c)}</td></tr>")
        type_block = (
            "<h2>Типы и исполнения</h2>\n<table>\n"
            + "\n".join(rows)
            + "\n</table>\n"
        )

    angle_block = ""
    if angles:
        ang = ", ".join(
            f"{k}° ({fmt_n(v)})"
            for k, v in sorted(angles.items(), key=lambda x: -x[1])
            if float(k) < 360  # отсекаем явные артефакты
        )
        if ang:
            angle_block = f"<p>Углы в реестре: {ang}.</p>\n"

    body = f"""---
taxonomy: product_cat
slug: {slug}
---
{lead}

<h2>Назначение</h2>
<p>Группа закрывает задачу: {purpose}. Подбор ведётся по условному проходу, давлению, марке стали и нормативу изготовления; в карточке типоразмера — полный размерный ряд серии и переключатель исполнения.</p>

{type_block}{angle_block}<h2>Диапазоны параметров</h2>
<table>
<tr><th>Параметр</th><th>Значение</th></tr>
<tr><td>Типоразмеров</td><td>{fmt_n(count)}</td></tr>
{f'<tr><td>DN</td><td>{dn_line}</td></tr>' if dn_line else ''}
{f'<tr><td>PN</td><td>{pn_line}</td></tr>' if pn_line else ''}
<tr><td>Марки стали</td><td>{steels_line(steels) if steels else '—'}</td></tr>
</table>

<h2>Нормативная база</h2>
<p>Изделия поставляются по действующим ГОСТ, ОСТ и СТО. Страница каждого норматива связана со всеми типоразмерами в каталоге:</p>
{norms_table(norms)}

<h2>Материалы и контроль</h2>
<p>Базовые марки — углеродистые и низколегированные стали для тепловых сетей и технологических трубопроводов; для коррозионных сред и высоких параметров пара — нержавеющие и теплоустойчивые. Каждая поставка сопровождается сертификатом на металл. Для объектов по ТР ТС 032/2013 доступно поднадзорное исполнение с расширенным объёмом контроля.</p>

<h2>Как заказать</h2>
<p>Выберите типоразмер в реестре (фильтры DN, PN, сталь, ГОСТ — над списком), в карточке укажите марку стали и поднадзорность и нажмите «Запросить КП». Нестандартные параметры — по чертежу заказчика через ту же форму.</p>
"""
    path.write_text(body.strip() + "\n", encoding="utf-8")
    print(f"wrote {path.relative_to(ROOT)}")


def write_fastener_child(slug: str, name: str, needle: str, parent_data: dict) -> None:
    if slug in SKIP_CATS:
        return
    path = ROOT / "content/category" / f"{slug}.md"
    # Приближаем долю по семействам из family-агрегатов.
    fams = AGG.get("family") or {}
    count = 0
    steels: dict[str, int] = {}
    norms: dict[str, int] = {}
    for key, data in fams.items():
        if not key.startswith("крепеж/"):
            continue
        fam_name = key.split("/", 1)[-1].lower()
        if needle not in fam_name:
            continue
        count += int(data.get("count") or 0)
        for s, c in (data.get("steels") or {}).items():
            steels[s] = steels.get(s, 0) + int(c)
        for n, c in (data.get("norms") or {}).items():
            norms[n] = norms.get(n, 0) + int(c)

    if count == 0:
        count = int(parent_data.get("count") or 0)

    lead = (
        f"{name} для фланцевых соединений и общепромышленного крепежа. "
        f"В реестре — {fmt_n(count)} позиций; подбор по размеру резьбы/длине, классу прочности и нормативу."
    )
    body = f"""---
taxonomy: product_cat
slug: {slug}
---
{lead}

<h2>Назначение</h2>
<p>{name} применяются в сборке фланцевых пар трубопроводов и креплении оборудования на объектах ТЭС, АЭС и промышленности. Исполнение выбирается по нагрузке, среде и требованиям нормативного документа на соединение.</p>

<h2>Параметры подбора</h2>
<table>
<tr><th>Параметр</th><th>Значение</th></tr>
<tr><td>Позиций в группе</td><td>{fmt_n(count)}</td></tr>
<tr><td>Марки / материалы</td><td>{steels_line(steels) if steels else 'по карточке позиции'}</td></tr>
</table>

<h2>Нормативы</h2>
{norms_table(norms) if norms else '<p>Нормативы указаны в карточке каждой позиции и на страницах терминов «Нормативы».</p>'}

<h2>Как заказать</h2>
<p>Откройте нужную позицию в реестре и отправьте «Запросить КП» с указанием количества и срока. Для комплекта фланцевого соединения укажите DN/PN фланца — подберём согласованный крепёж.</p>
"""
    path.write_text(body.strip() + "\n", encoding="utf-8")
    print(f"wrote {path.relative_to(ROOT)}")


def status_ru(status: str, replacement: str) -> str:
    s = (status or "").lower()
    if s in ("active", "found", "ok", ""):
        base = "Действует"
    elif s in ("replaced", "replaced_by", "cancelled", "canceled"):
        base = "Заменён / не действует"
    elif s == "missing":
        base = "В реестре каталога (публичный PDF не требуется)"
    else:
        base = status or "По данным реестра"
    if replacement:
        base += f"; заменён на {replacement}"
    return base


def write_norm(key: str, data: dict, registry: dict) -> None:
    slug = translit_norm(key)
    path = ROOT / "content/norm" / f"{slug}.md"
    path.parent.mkdir(parents=True, exist_ok=True)

    reg = registry.get(key) or registry.get(data.get("full") or "") or {}
    title = (reg.get("title") or "").strip()
    status = status_ru(reg.get("status") or data.get("status") or "", (reg.get("replacement") or data.get("replacement") or "").strip())
    products = (reg.get("products") or "").strip()
    cats = data.get("cats") or {}
    fams = data.get("fams") or {}
    steels = data.get("steels") or {}
    angles = data.get("angles") or {}
    count = int(data.get("count") or 0)
    dn_min, dn_max = data.get("dn_min"), data.get("dn_max")

    cat_links = []
    for ru, c in cats.items():
        if ru == "фланцы":
            href = "/catalog/flancy/"
        elif ru == "крепеж":
            href = "/catalog/krepezh/"
        else:
            href = f"/catalog/sdt/{CAT_SLUGS.get(ru, translit_norm(ru))}/"
        cat_links.append(f'<a href="{href}">{ru.capitalize()}</a> — {fmt_n(c)} поз.')

    dn_line = ""
    if dn_min is not None and dn_max is not None:
        dn_line = f"DN {fmt_dn(dn_min)}–{fmt_dn(dn_max)}"

    what = ""
    if title and len(title) > 10 and "Ту 24" not in title and "ТУ 24" not in title:
        what = title
    fam_list = ", ".join(fams.keys()) or "изделия каталога"
    cat_list = ", ".join(cats.keys()) or "каталог"
    if not what:
        what = (
            f"Стандарт на {fam_list} ({cat_list}). "
            f"В каталоге завода по {key} представлено {fmt_n(count)} типоразмеров"
            f"{f' в диапазоне {dn_line}' if dn_line else ''}."
        )
    else:
        what = (
            f"{what}. В каталоге — {fmt_n(count)} типоразмеров группы «{fam_list}»"
            f"{f', {dn_line}' if dn_line else ''}."
        )

    steel_txt = steels_line(steels) if steels else "указаны в карточках типоразмеров"
    ang_txt = ""
    if angles:
        ang_txt = "<p>Углы в охвате: " + ", ".join(
            f"{k}°" for k, _ in sorted(angles.items(), key=lambda x: float(x[0])) if float(k) < 360
        ) + ".</p>\n"

    body = f"""---
taxonomy: norm
slug: {slug}
name: {key}
---
{key} — норматив изготовления изделий в каталоге «Промышленная Энергетика». В реестре по этому документу — {fmt_n(count)} типоразмеров{f', охват {dn_line}' if dn_line else ''}.

<h2>Статус</h2>
<p>{status}. PDF стандарта на сайт не выкладывается: страница служит для навигации по типоразмерам и сверки обозначений.</p>

<h2>Что регламентирует</h2>
<p>{what}</p>

<h2>Охват в каталоге</h2>
<table>
<tr><th>Показатель</th><th>Значение</th></tr>
<tr><td>Типоразмеров</td><td>{fmt_n(count)}</td></tr>
{f'<tr><td>DN</td><td>{dn_line}</td></tr>' if dn_line else ''}
<tr><td>Семейства</td><td>{', '.join(fams.keys()) or '—'}</td></tr>
<tr><td>Марки стали</td><td>{steel_txt}</td></tr>
</table>
{ang_txt}
<h2>Связанные разделы</h2>
<ul>
{''.join(f'<li>{x}</li>' for x in cat_links) or '<li>См. реестр товаров ниже на этой странице</li>'}
</ul>

<h2>Как выбрать позицию</h2>
<p>Откройте нужный типоразмер в списке ниже или перейдите в семейство, отфильтруйте по DN/стали и отправьте «Запросить КП». При заказе укажите обозначение {key}, марку стали и признак поднадзорности.</p>
"""
    path.write_text(body.strip() + "\n", encoding="utf-8")
    print(f"wrote {path.relative_to(ROOT)}")


def main() -> None:
    registry = load_registry()
    cats = AGG.get("category") or {}
    for ru, slug in CAT_SLUGS.items():
        if ru not in cats:
            continue
        write_category(slug, ru, cats[ru])

    if "крепеж" in cats:
        for slug, name, needle in FASTENER_CHILDREN:
            write_fastener_child(slug, name, needle, cats["крепеж"])

    for key, data in NAGG.items():
        write_norm(key, data, registry)

    print("done")


if __name__ == "__main__":
    main()
