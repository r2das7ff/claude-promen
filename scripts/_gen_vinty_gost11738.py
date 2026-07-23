#!/usr/bin/env python3
"""Generate clean GOST 11738-84 screw catalog (vinty) from competitor size tables."""
from __future__ import annotations

import csv
from pathlib import Path

# Metizsnab / industry common matrix for GOST 11738-84 (DIN 912 analogue).
THREADS = [3, 4, 5, 6, 8, 10, 12, 14, 16, 20]
LENGTHS = {
    3: [6, 8, 10, 12, 16, 20],
    4: [8, 10, 12, 16, 20, 25],
    5: [10, 12, 16, 20, 25, 30],
    6: [10, 12, 16, 20, 25, 30, 40],
    8: [12, 16, 20, 25, 30, 40, 50],
    10: [16, 20, 25, 30, 40, 50, 60],
    12: [20, 25, 30, 40, 50, 60, 70],
    14: [25, 30, 40, 50, 60, 70, 80],
    16: [30, 40, 50, 60, 70, 80, 90],
    20: [40, 50, 60, 70, 80, 90, 100],
}
STRENGTHS = ["8.8", "10.9"]
FIELDS = [
    "sku",
    "normative_key",
    "full_designation",
    "category",
    "product_family",
    "product_type",
    "title",
    "dn",
    "dn_branch",
    "outer_diameter",
    "wall_thickness",
    "outer_d_branch",
    "wall_branch",
    "pn",
    "angle",
    "radius",
    "mass_kg",
    "material",
    "standard_page",
    "pdf_path",
    "thread_size",
    "length_mm",
    "thread_pitch",
    "strength_class",
    "accuracy_class",
    "execution",
    "coating",
    "material_grade",
    "washer_type",
    "nominal_d_mm",
    "gost_designation",
    "supervised",
    "base_sku",
    "dim_thread_size",
    "dim_length_mm",
    "description",
    "attributes",
    "dim_strength_class",
    "woo_type",
]


def main() -> None:
    out = Path(__file__).resolve().parents[1] / "products-csv" / "products_vinty_gost11738.csv"
    rows: list[dict[str, str]] = []
    for m in THREADS:
        for L in LENGTHS[m]:
            for sc in STRENGTHS:
                sku = f"11738-В-{m}-{L}-{sc}"
                title = f"Винт M{m}×{L} {sc} ГОСТ 11738-84"
                row = {k: "" for k in FIELDS}
                row.update(
                    {
                        "sku": sku,
                        "normative_key": "ГОСТ 11738-1984",
                        "full_designation": "ГОСТ 11738-84",
                        "category": "крепеж",
                        "product_family": "Винт",
                        "product_type": "В",
                        "title": title,
                        "dn": str(m),
                        "thread_size": str(m),
                        "length_mm": str(L),
                        "strength_class": sc,
                        "accuracy_class": "A",
                        "supervised": "False",
                        "base_sku": sku,
                        "dim_thread_size": str(m),
                        "dim_length_mm": str(L),
                        "dim_strength_class": sc,
                        "description": (
                            f"Винт с цилиндрической головкой и внутренним шестигранником "
                            f"по ГОСТ 11738-84 (аналог DIN 912). Резьба M{m}, длина {L} мм, "
                            f"класс прочности {sc}."
                        ),
                        "attributes": f"thread_size={m}; length_mm={L}; strength_class={sc}",
                        "woo_type": "simple",
                    }
                )
                rows.append(row)

    with out.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=FIELDS)
        w.writeheader()
        w.writerows(rows)
    print(f"wrote {out} rows={len(rows)}")


if __name__ == "__main__":
    main()
