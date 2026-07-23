#!/usr/bin/env python3
"""Parse OST 34.10.700-97 size table from competitor HTML (teo97.ru) → import CSV.

Source: https://teo97.ru/perehody/ost-34-10-700-97/
DN pairs cross-checked with https://pg-gefest.ru/product/perexody-ost-34-10-700-97/

Usage:
  curl -sS 'https://teo97.ru/perehody/ost-34-10-700-97/' -o /tmp/teo700.html
  python3 scripts/_parse_ost_34_10_700_teo97.py /tmp/teo700.html \\
      -o products-csv/products_ost_34_10_700_fill.csv
  wp promen import --file=/data/products-csv/products_ost_34_10_700_fill.csv
"""
from __future__ import annotations

import argparse
import csv
import re
from pathlib import Path

GEFEST_DN = {
	(45, 32): (40, 25),
	(57, 45): (50, 40),
	(57, 38): (50, 32),
	(76, 57): (65, 50),
	(76, 45): (65, 40),
	(89, 76): (80, 65),
	(89, 57): (80, 50),
	(108, 89): (100, 80),
	(108, 76): (100, 65),
	(133, 108): (125, 100),
	(133, 89): (125, 80),
	(159, 133): (150, 125),
	(159, 108): (150, 100),
	(219, 159): (200, 150),
	(219, 133): (200, 125),
	(273, 219): (250, 200),
	(325, 273): (300, 250),
	(325, 219): (300, 200),
	(377, 325): (350, 300),
	(377, 273): (350, 250),
	(377, 219): (350, 200),
	(426, 377): (400, 350),
	(426, 325): (400, 300),
}
OD_DN = {
	45: 40, 57: 50, 76: 65, 89: 80, 108: 100, 133: 125, 159: 150,
	219: 200, 273: 250, 325: 300, 377: 350, 426: 400, 32: 25, 38: 32,
}
STEELS = [
	'08Х18Н10Т', '09Г2С', '10', '10Г2', '10Г2С1', '10Х17Н13М2Т',
	'12Х18Н10Т', '13ХФА', '17Г1С', '17Г1С-У', '17ГС', '20',
]
DESIG_RE = re.compile(
	r'([КЭKE])\s*(\d+)\s*[хx×]\s*([\d,]+)\s*-\s*(\d+)\s*[хx×]\s*([\d,]+)',
	re.I,
)


def num(s: str) -> str:
	return str(s).replace(',', '.').strip()


def slug_part(v: str) -> str:
	return str(v).replace('.', '-').replace(',', '-').lower()


def steel_slug(st: str) -> str:
	table = str.maketrans({'Х': 'х', 'Г': 'г', 'С': 'с', 'Ф': 'ф', 'А': 'а', 'Н': 'н', 'М': 'м', 'Т': 'т', 'У': 'у'})
	return st.translate(table).lower()


def parse_rows(html: str) -> list[dict]:
	rows: list[dict] = []
	seen: set[tuple] = set()
	for m in DESIG_RE.finditer(html):
		kind = m.group(1).upper().replace('K', 'К').replace('E', 'Э')
		D, s, D2, s2 = num(m.group(2)), num(m.group(3)), num(m.group(4)), num(m.group(5))
		key = (kind, D, s, D2, s2)
		if key in seen:
			continue
		seen.add(key)
		dn_pair = GEFEST_DN.get((int(float(D)), int(float(D2))))
		if dn_pair:
			dn, dn2 = map(str, dn_pair)
		else:
			dn = str(OD_DN.get(int(float(D)), ''))
			dn2 = str(OD_DN.get(int(float(D2)), ''))
		rows.append({'kind': kind, 'D': D, 's': s, 'D2': D2, 's2': s2, 'dn': dn, 'dn2': dn2})
	return rows


def build_csv(rows: list[dict], fieldnames: list[str]) -> list[dict]:
	out: list[dict] = []
	for r in rows:
		kind = r['kind']
		ptype = 'ПК' if kind == 'К' else 'ПЭ'
		D, s, D2, s2, dn, dn2 = r['D'], r['s'], r['D2'], r['s2'], r['dn'], r['dn2']
		desig = f'{kind}-{D}х{s}-{D2}х{s2}'
		base_sku = (
			f"ост-34-10-700-1997-{slug_part(dn)}-{ptype.lower()}-"
			f"{slug_part(D)}-{slug_part(s)}-{slug_part(D2)}-{slug_part(s2)}"
		)
		title = (
			f"Переход {'концентрический' if kind == 'К' else 'эксцентрический'} "
			f"{D}×{s}-{D2}×{s2} ОСТ 34.10.700-1997"
		)
		attrs = (
			f'dn={dn}; dn_branch={dn2}; outer_diameter={D}; wall_thickness={s}; '
			f'outer_d_branch={D2}; wall_branch={s2}; gost_designation={desig}'
		)
		desc = (
			f"Переход {'концентрический' if kind == 'К' else 'эксцентрический'} "
			f'по ОСТ 34.10.700-1997. Тип: {ptype}. DN {dn}. DN1 {dn2}. '
			f'Размеры {D}х{s}-{D2}х{s2} мм. Условное обозначение: {desig}.'
		)
		parent = {k: '' for k in fieldnames}
		parent.update({
			'sku': base_sku,
			'full_designation': 'ОСТ 34.10.700-1997',
			'category': 'переходы',
			'product_family': 'Переходы',
			'product_type': ptype,
			'title': title,
			'dn': dn,
			'dn_branch': dn2,
			'outer_diameter': D,
			'wall_thickness': s,
			'outer_d_branch': D2,
			'wall_branch': s2,
			'gost_designation': desig,
			'normative_key': 'ОСТ 34.10.700-1997',
			'pdf_path': 'pdf/отводы/ОСТ_34.10.700-97.pdf',
			'standard_page': '1',
			'supervised': 'False',
			'base_sku': base_sku,
			'dim_gost_designation': desig,
			'description': desc,
			'attributes': attrs,
			'woo_type': 'variable',
			'attr_steel': '|'.join(STEELS),
			'attr_supervised': 'Нет',
			'stock_status': 'onbackorder',
			'manage_stock': 'no',
		})
		out.append(parent)
		for st in STEELS:
			var = {k: '' for k in fieldnames}
			var.update({
				'sku': f'{base_sku}-{steel_slug(st)}',
				'full_designation': 'ОСТ 34.10.700-1997',
				'category': 'переходы',
				'product_family': 'Переходы',
				'product_type': ptype,
				'title': f'{title[:-len(" ОСТ 34.10.700-1997")]} {st} ОСТ 34.10.700-1997',
				'dn': dn,
				'dn_branch': dn2,
				'outer_diameter': D,
				'wall_thickness': s,
				'outer_d_branch': D2,
				'wall_branch': s2,
				'material': st,
				'material_grade': st,
				'gost_designation': desig,
				'normative_key': 'ОСТ 34.10.700-1997',
				'pdf_path': 'pdf/отводы/ОСТ_34.10.700-97.pdf',
				'standard_page': '1',
				'supervised': 'False',
				'base_sku': base_sku,
				'dim_gost_designation': desig,
				'description': desc + f' Марка стали {st}.',
				'attributes': attrs + f'; material_grade={st}',
				'woo_type': 'variation',
				'parent_sku': base_sku,
				'attr_steel': st,
				'attr_supervised': 'Нет',
				'stock_status': 'onbackorder',
				'manage_stock': 'no',
			})
			out.append(var)
	return out


def main() -> None:
	ap = argparse.ArgumentParser()
	ap.add_argument('html')
	ap.add_argument('-o', '--output', required=True)
	ap.add_argument(
		'--header-from',
		default=str(Path(__file__).resolve().parents[1] / 'products-csv' / 'products_aes_tes_18new_import.csv'),
	)
	args = ap.parse_args()
	html = Path(args.html).read_text(encoding='utf-8', errors='ignore')
	rows = parse_rows(html)
	with Path(args.header_from).open(encoding='utf-8-sig', newline='') as f:
		fieldnames = [fn.lstrip('\ufeff') for fn in csv.DictReader(f).fieldnames or []]
	out_rows = build_csv(rows, fieldnames)
	out = Path(args.output)
	out.parent.mkdir(parents=True, exist_ok=True)
	with out.open('w', encoding='utf-8', newline='') as f:
		w = csv.DictWriter(f, fieldnames=fieldnames, extrasaction='ignore')
		w.writeheader()
		w.writerows(out_rows)
	parents = sum(1 for r in out_rows if r['woo_type'] == 'variable')
	print(f'parsed={len(rows)} parents={parents} rows={len(out_rows)} → {out}')


if __name__ == '__main__':
	main()
