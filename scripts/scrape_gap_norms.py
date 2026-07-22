#!/usr/bin/env python3
"""
Сбор недостающих нормативов в отдельный CSV (products_gap_variable.csv).

Источники — только публичные HTML-таблицы:
  - gost.gtsever.ru  — СТО ЦКТИ 321.06
  - pkfdetal.ru      — СТО 79814898 112-2009
  - elbows.ru        — ОСТ 34.10.752-97 (типоразмеры)
  - meganorm.ru      — ГОСТ 12815-80 (присоединительные размеры фланцев)

Запуск из корня репозитория:
  python3 scripts/scrape_gap_norms.py
"""

from __future__ import annotations

import csv
import json
import re
import urllib.request
from dataclasses import dataclass, field
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[1]
OUT_CSV = ROOT / "products-csv" / "products_gap_variable.csv"
MANIFEST = ROOT / "products-csv" / "gap_norms_manifest.json"
TEMPLATE_CSV = ROOT / "products-csv" / "products_variable.csv"

UA = "Mozilla/5.0 (compatible; PromEnGapScraper/1.0)"


def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=60) as resp:
        return resp.read().decode("utf-8", errors="replace")


def slug(s: str) -> str:
    s = s.lower().strip()
    s = s.replace("х", "x").replace("×", "x")
    s = re.sub(r"[^a-z0-9а-яё]+", "-", s, flags=re.I)
    return re.sub(r"-+", "-", s).strip("-")


def num(s: str) -> str:
  if s is None:
    return ""
  s = str(s).strip().replace(",", ".")
  s = re.sub(r"[^\d.+-]", "", s)
  return s


def parse_pressure_line(line: str) -> tuple[str, str, str]:
    """Возвращает (pn_mpa, temp_c, pressure_group)."""
    line = line.replace("р", "p").replace("Р", "P").replace("t", "t").replace("С", "C")
    pn = ""
    temp = ""
    m_pn = re.search(r"p\s*=\s*([\d,]+)\s*МПа", line, re.I)
    if m_pn:
        pn = num(m_pn.group(1))
    m_t = re.search(r"t\s*=\s*([\d,]+)\s*°?C", line, re.I)
    if m_t:
        temp = num(m_t.group(1))
    group = re.sub(r"\s+", " ", line.strip())
    return pn, temp, group


@dataclass
class SourceReport:
    normative_key: str
    source_url: str
    status: str
    rows: int = 0
    note: str = ""
    skipped: list[str] = field(default_factory=list)


class RowBuilder:
    def __init__(self, headers: list[str]):
        self.headers = headers

    def blank(self) -> dict[str, str]:
        return {h: "" for h in self.headers}

    def build(
        self,
        *,
        sku: str,
        normative_key: str,
        full_designation: str,
        category: str,
        product_family: str,
        product_type: str,
        title: str,
        description: str,
        attributes: dict[str, Any],
        data_source: str,
        woo_type: str = "simple",
        pdf_path: str = "",
        material_grade: str = "",
        **fields: Any,
    ) -> dict[str, str]:
        row = self.blank()
        row.update(
            {
                "sku": sku,
                "normative_key": normative_key,
                "full_designation": full_designation,
                "category": category,
                "product_family": product_family,
                "product_type": product_type,
                "title": title,
                "woo_type": woo_type,
                "pdf_path": pdf_path,
                "material_grade": material_grade,
                "material": material_grade,
                "supervised": "False",
                "stock_status": "onbackorder",
                "manage_stock": "no",
                "description": description,
                "attributes": "; ".join(f"{k}={v}" for k, v in attributes.items() if v != ""),
                "dim_data_source": data_source,
            }
        )
        for k, v in fields.items():
            if k in row and v != "" and v is not None:
                row[k] = str(v)
            dim_key = f"dim_{k}"
            if dim_key in row and v != "" and v is not None:
                row[dim_key] = str(v)
        return row


def find_sto321_table(soup: BeautifulSoup) -> Any:
    for table in soup.find_all("table"):
        txt = table.get_text(" ", strip=True)
        if "Исполнение" in txt and "Масса" in txt and "D y" in txt.replace("  ", " "):
            return table
        if "Исполнение" in txt and "Масса гнутой" in txt:
            return table
    tables = soup.find_all("table")
    return tables[9] if len(tables) > 9 else None


def parse_gtsever_sto321(
    html: str,
    *,
    norm_key: str,
    full_designation: str,
    pdf_path: str,
    product_type: str,
    data_tag: str,
    default_material: str = "",
) -> list[dict[str, str]]:
    soup = BeautifulSoup(html, "lxml")
    table = find_sto321_table(soup)
    if table is None:
        raise RuntimeError("Таблица СТО 321 не найдена")

    headers = load_headers()
    rb = RowBuilder(headers)
    rows_out: list[dict[str, str]] = []

    pn = temp = pressure_group = ""
    state: dict[str, str] = {}

    def flush_partial(exec_no: str, angle: str, l2: str, b: str, mass: str) -> None:
        if not state:
            return
        dy = state.get("dy", "")
        da = state.get("da", "")
        s_wall = state.get("s", "")
        r = state.get("r", "")
        mat = state.get("material", default_material) or default_material
        gost_des = f"{angle}°-{da}x{s_wall}-R{r}-{exec_no.zfill(3)}"
        sku = slug(f"сто-{data_tag}-{exec_no}-{dy}-{da}-{s_wall}-{angle}-{pn}-{mat}")
        attrs = {
            "dn": dy,
            "outer_diameter": da,
            "wall_thickness": s_wall,
            "pn": pn,
            "angle": angle,
            "radius": r,
            "mass_kg": mass,
            "material": mat,
            "execution": exec_no.zfill(3),
            "material_grade": mat,
            "gost_designation": gost_des,
            "inner_diameter": state.get("dp", ""),
            "straight_length": state.get("l", ""),
            "straight_length_1": state.get("l1", ""),
            "arc_length": l2,
            "developed_b": b,
            "wall_s1": state.get("s1", ""),
            "wall_sk": state.get("sk", ""),
            "temperature_c": temp,
            "pressure_group": pressure_group,
            "mass_method": "табличная",
            "data_source": f"html:{data_tag}",
        }
        title = (
            f"Отвод {angle}° {da}×{s_wall} исп. {exec_no.zfill(3)} {norm_key}"
        )
        desc = (
            f"Отвод по {full_designation}. Угол {angle}°. DN {dy}. "
            f"Наружный диаметр {da} мм. Толщина стенки {s_wall} мм. "
            f"Исполнение {exec_no.zfill(3)}. PN {pn} МПа. "
            f"Температура {temp}°C. R {r} мм. l₂ {l2} мм. b {b} мм. "
            f"Марка стали {mat}. Масса гнутой части {mass} кг."
        )
        rows_out.append(
            rb.build(
                sku=sku,
                normative_key=norm_key,
                full_designation=full_designation,
                category="отводы",
                product_family="Отводы",
                product_type=product_type,
                title=title,
                description=desc,
                attributes=attrs,
                data_source=f"html:{data_tag}",
                pdf_path=pdf_path,
                material_grade=mat,
                dn=dy,
                outer_diameter=da,
                wall_thickness=s_wall,
                pn=pn,
                angle=angle,
                radius=r,
                mass_kg=mass,
                execution=exec_no.zfill(3),
                gost_designation=gost_des,
            )
        )

    for tr in table.find_all("tr"):
        cells = [re.sub(r"\s+", " ", c.get_text(" ", strip=True)) for c in tr.find_all(["td", "th"])]
        cells = [c for c in cells if c]
        if not cells:
            continue
        joined = " ".join(cells)
        if re.search(r"[pр]\s*=\s*[\d,]+\s*МПа", joined, re.I):
            pn, temp, pressure_group = parse_pressure_line(joined)
            continue
        if cells[0] in {"номин.", "пред. откл.", "не менее"}:
            continue
        if cells[0].startswith("*"):
            continue

        # Полная строка исполнения
        if re.match(r"^\d{2}$", cells[0]) and len(cells) >= 10:
            exec_no = cells[0]
            state = {
                "exec": exec_no,
                "dy": cells[1] if len(cells) > 1 else "",
                "da": cells[2] if len(cells) > 2 else "",
                "dp": cells[3] if len(cells) > 3 else "",
                "dp_tol": cells[4] if len(cells) > 4 and cells[4].startswith(("+", "-")) else "",
                "r": "",
                "s": "",
                "s1": "",
                "sk": "",
                "l": "",
                "l1": "",
                "material": default_material,
            }
            idx = 4
            if state["dp_tol"]:
                idx = 5
            rest = cells[idx:]
            # R, s, s1, sk, l, [l1|angle], [angle], l2, b, [material], mass
            if len(rest) >= 9:
                state["r"], state["s"], state["s1"], state["sk"] = rest[0:4]
                state["l"] = rest[4]
                tail = rest[5:]
                angle = l2 = b = mass = ""
                material = state.get("material", default_material)
                if len(tail) >= 4 and tail[0] in {"30", "45", "60", "90"}:
                    # l1 опущен: l, angle, l2, b, mass
                    state["l1"] = ""
                    angle, l2, b, mass = tail[0], tail[1], tail[2], num(tail[3])
                elif len(tail) >= 5:
                    state["l1"] = tail[0]
                    angle, l2, b = tail[1], tail[2], tail[3]
                    if len(tail) >= 6 and re.search(r"[А-ЯA-Z0-9]", tail[4]):
                        material = tail[4]
                        state["material"] = material
                        mass = num(tail[5]) if len(tail) > 5 else num(tail[4])
                    else:
                        mass = num(tail[4])
                if angle:
                    state["material"] = material
                    flush_partial(exec_no, angle, l2, b, mass)
            continue

        # Продолжение: только угол / l2 / b / масса
        if state and re.match(r"^\d{2}$", cells[0]) and len(cells) <= 5:
            exec_no = cells[0]
            angle = cells[1] if len(cells) > 1 else ""
            l2 = cells[2] if len(cells) > 2 else ""
            b = cells[3] if len(cells) > 3 else ""
            mass = num(cells[4]) if len(cells) > 4 else ""
            flush_partial(exec_no, angle, l2, b, mass)

    return rows_out


def parse_pkfdetal_sto112(html: str) -> list[dict[str, str]]:
    soup = BeautifulSoup(html, "lxml")
    table = soup.find("table")
    if table is None:
        raise RuntimeError("Таблица СТО 79814898 112 не найдена")

    headers = load_headers()
    rb = RowBuilder(headers)
    out: list[dict[str, str]] = []

    angle = ""
    carry_pn = ""
    carry_s1 = ""

    for tr in table.find_all("tr"):
        tds = tr.find_all("td")
        if not tds:
            continue
        strong = tr.find("strong")
        row_text = tr.get_text(" ", strip=True)
        if ("угл" in row_text.lower() and "разворота" in row_text.lower()) or (
            strong and "угл" in strong.get_text(" ", strip=True).lower()
        ):
            m = re.search(r"(\d+)\s*°", row_text)
            angle = m.group(1) if m else ""
            continue

        texts = [td.get_text(" ", strip=True) for td in tds]
        if not texts or not re.match(r"^\d{2}\s", texts[0]):
            continue

        exec_m = re.match(r"^(\d{2})", texts[0])
        if not exec_m:
            continue
        execution = exec_m.group(1)

        # Разбор с учётом rowspan: [exec, pn?, dn, pipe, d1, s1?, mass]
        col = 1
        pn_val = ""
        if col < len(texts) and texts[col].isdigit() and int(texts[col]) <= 40:
            pn_val = texts[col]
            col += 1
        else:
            pn_val = carry_pn
        dn = texts[col] if col < len(texts) else ""
        col += 1
        pipe = texts[col].replace("х", "x").replace(" ", "") if col < len(texts) else ""
        col += 1
        d1 = texts[col] if col < len(texts) else ""
        col += 1
        s1 = ""
        if col < len(texts) and re.match(r"^[\d,]+$", texts[col]):
            s1 = num(texts[col])
            col += 1
        else:
            s1 = carry_s1
        mass = num(texts[col]) if col < len(texts) else ""

        if pn_val:
            carry_pn = pn_val
        if s1:
            carry_s1 = s1

        m_pipe = re.match(r"^([\d.]+)x([\d.]+)$", pipe, re.I)
        od = m_pipe.group(1) if m_pipe else d1
        wall = m_pipe.group(2) if m_pipe else s1

        gost_des = f"{angle}°-{pipe}-исп{execution}"
        sku = slug(f"сто-79814898-112-{execution}-{dn}-{pipe}-{angle}")
        attrs = {
            "dn": dn,
            "pn": pn_val,
            "outer_diameter": od,
            "wall_thickness": wall,
            "angle": angle,
            "mass_kg": mass,
            "execution": execution,
            "gost_designation": gost_des,
            "pipe_od": od,
            "pipe_wall": wall,
            "pressure_group": f"PN {pn_val}" if pn_val else "",
            "mass_method": "табличная",
            "data_source": "html:79814898-112",
        }
        title = f"Колено секторное {angle}° {pipe} исп. {execution} СТО 79814898 112-2009"
        desc = (
            f"Колено секторное по СТО 79814898 112-2009. Угол {angle}°. "
            f"DN {dn}. Труба {pipe} мм. Dн1 {d1} мм. S1 {s1} мм. "
            f"PN {pn_val}. Масса {mass} кг."
        )
        out.append(
            rb.build(
                sku=sku,
                normative_key="СТО 79814898 112-2009",
                full_designation="СТО 79814898 112-2009",
                category="отводы",
                product_family="Отводы",
                product_type="КС",
                title=title,
                description=desc,
                attributes=attrs,
                data_source="html:79814898-112",
                pdf_path="pdf/отводы/СТО_79814898_112-2009.pdf",
                dn=dn,
                pn=pn_val,
                outer_diameter=od,
                wall_thickness=wall,
                angle=angle,
                mass_kg=mass,
                execution=execution,
                gost_designation=gost_des,
            )
        )

    return out


def parse_elbows_ost752(html: str) -> list[dict[str, str]]:
    soup = BeautifulSoup(html, "lxml")
    headers = load_headers()
    rb = RowBuilder(headers)
    out: list[dict[str, str]] = []
    angles = ["30", "45", "60", "90"]
    steels = ["20", "09Г2С", "12Х18Н10Т"]

    for table in soup.find_all("table"):
        for tr in table.find_all("tr"):
            cells = [c.get_text(" ", strip=True) for c in tr.find_all(["td", "th"])]
            if len(cells) < 2:
                continue
            blob = " ".join(cells)
            m = re.search(r"(\d{3,4})\s*[xх×]\s*(\d{1,2})", blob)
            if not m:
                m = re.search(r"(\d{3,4})х(\d{1,2})", cells[1], re.I)
            if not m:
                continue
            d_mm, t_mm = m.group(1), m.group(2)
            for angle in angles:
                for steel in steels:
                    gost_des = f"{angle}°-{d_mm}x{t_mm}"
                    sku = slug(f"ост-3410752-{d_mm}-{t_mm}-{angle}-{steel}")
                    attrs = {
                        "outer_diameter": d_mm,
                        "wall_thickness": t_mm,
                        "angle": angle,
                        "material": steel,
                        "material_grade": steel,
                        "gost_designation": gost_des,
                        "pn": "2.5",
                        "pressure_group": "22 кгс/см² (2,5 МПа)",
                        "construction": "секторный сварной",
                        "connection": "сварка встык",
                        "data_source": "html:ost-34.10.752",
                    }
                    title = f"Отвод сварной {angle}° {d_mm}×{t_mm} {steel} ОСТ 34.10.752-97"
                    desc = (
                        f"Отвод секторный сварной по ОСТ 34.10.752-97. "
                        f"D {d_mm} мм × s {t_mm} мм. Угол {angle}°. Сталь {steel}. "
                        f"Рраб до 2,5 МПа. Масса — по запросу (в источнике не указана)."
                    )
                    out.append(
                        rb.build(
                            sku=sku,
                            normative_key="ОСТ 34.10.752-97",
                            full_designation="ОСТ 34.10.752-97",
                            category="отводы",
                            product_family="Отводы",
                            product_type="ОС",
                            title=title,
                            description=desc,
                            attributes=attrs,
                            data_source="html:ost-34.10.752",
                            pdf_path="pdf/отводы/ОСТ_34.10.752-97.pdf",
                            material_grade=steel,
                            outer_diameter=d_mm,
                            wall_thickness=t_mm,
                            angle=angle,
                            pn="2.5",
                            gost_designation=gost_des,
                        )
                    )
        if out:
            break

  # dedupe
    seen: set[str] = set()
    deduped = []
    for r in out:
        if r["sku"] in seen:
            continue
        seen.add(r["sku"])
        deduped.append(r)
    return deduped


def parse_meganorm_gost12815(html: str) -> list[dict[str, str]]:
    """Парсит табл. 2–11 ГОСТ 12815-80 (присоединительные размеры, HTML)."""
    soup = BeautifulSoup(html, "lxml")
    headers = load_headers()
    rb = RowBuilder(headers)
    out: list[dict[str, str]] = []

    py_by_table_idx = {
        2: ("0.1;0.25", "Py 0,1 и 0,25 МПа"),
        3: ("0.6", "Py 0,6 МПа"),
        4: ("1.0", "Py 1,0 МПа"),
        5: ("1.6", "Py 1,6 МПа"),
        6: ("2.5", "Py 2,5 МПа"),
        7: ("4.0", "Py 4,0 МПа"),
        8: ("6.3", "Py 6,3 МПа"),
        9: ("10", "Py 10 МПа"),
        10: ("16", "Py 16 МПа"),
        11: ("20", "Py 20 МПа"),
    }

    col_keys = [
        "d",
        "d1",
        "d2",
        "d3",
        "d4",
        "d5",
        "d6",
        "d_center",
        "bolt_count",
        "h",
        "h1",
        "h2",
        "b",
    ]

    tables = soup.find_all("table")
    for idx, table in enumerate(tables):
        if idx not in py_by_table_idx:
            continue
        py_mpa, py_label = py_by_table_idx[idx]
        trs = table.find_all("tr")
        for tr in trs[2:]:
            cells = [c.get_text(" ", strip=True) for c in tr.find_all(["td", "th"])]
            if not cells or not re.match(r"^\d+$", cells[0]):
                continue
            dy = cells[0]
            dims = {col_keys[i]: cells[i + 1] for i in range(min(len(col_keys), len(cells) - 1))}
            bolt_cols = cells[len(col_keys) + 1 :] if len(cells) > len(col_keys) + 1 else []
            sku = slug(f"гост-12815-80-py{py_mpa}-dy{dy}")
            attrs = {
                "dn": dy,
                "pn": py_mpa,
                "outer_diameter": dims.get("d", ""),
                "flange_type": "присоединительные размеры",
                "pressure_group": py_label,
                "data_source": "html:gost-12815",
                "bolt_circle_d": dims.get("d6", ""),
                "bolt_count": dims.get("bolt_count", ""),
                "flange_thickness": dims.get("h", ""),
            }
            for k, v in dims.items():
                attrs[f"dim_{k}"] = v
            if bolt_cols:
                attrs["bolt_d"] = bolt_cols[0]
            title = f"Фланец Dy {dy} Py {py_mpa} МПа ГОСТ 12815-80"
            desc = (
                f"Присоединительные размеры по ГОСТ 12815-80, {py_label}. "
                f"Dy {dy} мм. D {dims.get('d', '')} мм. D1 {dims.get('d1', '')} мм. "
                f"D2 {dims.get('d2', '')} мм. n {dims.get('bolt_count', '')}."
            )
            out.append(
                rb.build(
                    sku=sku,
                    normative_key="ГОСТ 12815-80",
                    full_designation="ГОСТ 12815-80",
                    category="фланцы",
                    product_family="Фланцы",
                    product_type="ПР",
                    title=title,
                    description=desc,
                    attributes=attrs,
                    data_source="html:gost-12815",
                    pdf_path="pdf/фланцы/ГОСТ_12815-80.pdf",
                    dn=dy,
                    pn=py_mpa,
                    outer_diameter=dims.get("d", ""),
                )
            )

    seen: set[str] = set()
    deduped = []
    for r in out:
        if r["sku"] in seen:
            continue
        seen.add(r["sku"])
        deduped.append(r)
    return deduped


_HEADERS: list[str] | None = None


def load_headers() -> list[str]:
    global _HEADERS
    if _HEADERS is None:
        with TEMPLATE_CSV.open(encoding="utf-8-sig") as f:
            _HEADERS = next(csv.reader(f))
    return _HEADERS


def write_csv(rows: list[dict[str, str]]) -> None:
    hdrs = load_headers()
    with OUT_CSV.open("w", encoding="utf-8", newline="") as f:
        w = csv.DictWriter(f, fieldnames=hdrs, extrasaction="ignore")
        w.writeheader()
        for row in rows:
            w.writerow(row)


def main() -> None:
    reports: list[SourceReport] = []
    all_rows: list[dict[str, str]] = []

    jobs = [
        {
            "norm": "СТО 321.06",
            "url": "http://gost.gtsever.ru/Data2/1/4293819/4293819596.htm",
            "parser": lambda html: parse_gtsever_sto321(
                html,
                norm_key="СТО 321.06",
                full_designation="СТО 321.06-2009",
                pdf_path="pdf/отводы/СТО_ЦКТИ_321.06-2009.pdf",
                product_type="ОК",
                data_tag="321.06",
                default_material="12Х1МФ",
            ),
        },
        {
            "norm": "СТО 79814898 112-2009",
            "url": "https://pkfdetal.ru/katalog/kolena-sto/kolena-sto-79814898-112-2009",
            "parser": parse_pkfdetal_sto112,
        },
        {
            "norm": "ОСТ 34.10.752-97",
            "url": "https://elbows.ru/otvody/stalnye-otvody/ost-3410752-97/",
            "parser": parse_elbows_ost752,
        },
        {
            "norm": "ГОСТ 12815-80",
            "url": "https://meganorm.ru/Data2/1/4294848/4294848686.htm",
            "parser": parse_meganorm_gost12815,
        },
    ]

    for job in jobs:
        rep = SourceReport(job["norm"], job["url"], "pending")
        try:
            html = fetch(job["url"])
            rows = job["parser"](html)
            all_rows.extend(rows)
            rep.status = "ok"
            rep.rows = len(rows)
        except Exception as exc:  # noqa: BLE001
            rep.status = "error"
            rep.note = str(exc)
        reports.append(rep)

    # Нормативы без публичных HTML-таблиц — фиксируем в манифесте
    skipped = [
        SourceReport(
            "СТО ЦКТИ 321.14-2011",
            "—",
            "skipped",
            note="Нет бесплатной HTML-таблицы (только PDF/платные базы). На лендинге указан как 321.14-2009 — уточнить обозначение.",
        ),
        SourceReport(
            "ГОСТ 17380-2001",
            "—",
            "skipped",
            note="Общие ТУ на СДТ, таблиц типоразмеров нет. Для плоских заглушек — ОСТ 34.10.758-97.",
        ),
        SourceReport(
            "ГОСТ 17381",
            "—",
            "skipped",
            note="В серии 17375–17379 отдельного ГОСТ 17381 нет.",
        ),
        SourceReport(
            "ГОСТ 12816-80",
            "—",
            "skipped",
            note="Общие технические требования к фланцам, без таблицы размеров. Размеры — ГОСТ 12820/12821.",
        ),
        SourceReport(
            "ОСТ 36-41-81",
            "—",
            "skipped",
            note="Справочный ОСТ на сварные детали, не сортамент.",
        ),
    ]
    reports.extend(skipped)

    write_csv(all_rows)

    manifest = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "output_csv": str(OUT_CSV.relative_to(ROOT)),
        "total_rows": len(all_rows),
        "sources": [rep.__dict__ for rep in reports],
        "row_counts_by_norm": {},
    }
    counts: dict[str, int] = {}
    for r in all_rows:
        nk = r.get("normative_key", "")
        counts[nk] = counts.get(nk, 0) + 1
    manifest["row_counts_by_norm"] = counts

    MANIFEST.write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"Wrote {len(all_rows)} rows -> {OUT_CSV}")
    for rep in reports:
        print(f"  {rep.status:7} {rep.rows:4}  {rep.normative_key}  {rep.note}")


if __name__ == "__main__":
    main()
