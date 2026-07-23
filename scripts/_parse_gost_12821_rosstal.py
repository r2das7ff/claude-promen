#!/usr/bin/env python3
"""Parse ГОСТ 12821-80 collar flange tables → import CSV.

Source tables (D, D1, d, Dm, Dn, d1, b, h, h4, n, mass):
  https://rosstal74.ru/products/flancy-vorotnikovye/
  cross-checked with http://www.nzhms.ru/gost-12821-80-flancy-vorotnikovye

Fixes the catalog bug where Dm/Dn were mapped into outer_diameter / stud_count.

Usage:
  # markdown dump from WebFetch / saved HTML text with pipe tables:
  python3 scripts/_parse_gost_12821_rosstal.py \\
      /path/to/tables.md -o products-csv/products_gost_12821_fill.csv
"""
from __future__ import annotations

import argparse
import csv
import re
from pathlib import Path

STEELS = [
	'08Х18Н10Т',
	'09Г2С',
	'10',
	'12Х18Н10Т',
	'13ХФА',
	'17Г1С',
	'20',
]

# Known OCR / mirror typos in competitor tables.
DN_NECK = {
	15: 19,
	20: 26,
	25: 33,
	32: 39,
	40: 46,
	50: 58,
	65: 77,
	80: 90,
	100: 110,
	125: 135,
	150: 161,
	200: 222,
	250: 278,
	300: 330,
	350: 382,
	400: 432,
	500: 535,
	600: 636,
	800: 826,
	1000: 1028,
	1200: 1228,
}

# Skip Ру100+ here: competitor PN100 tables omit D2/h and often mis-align columns.
PN_MPA = {
	'10': '1',
	'16': '1.6',
	'25': '2.5',
	'40': '4',
	'63': '6.3',
}

# Allow both 0,58 and 0.58 decimal forms in competitor tables.
N = r'([\d]+(?:[.,]\d+)?)'
Nopt = r'([\d.—\-]+(?:[.,]\d+)?)'

ROW_RE = re.compile(
	rf'\|\s*(1-(\d+)-(\d+))\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{Nopt}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{Nopt}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}\s*'
	rf'\|\s*{N}'
)


def num(s: str) -> str:
	s = str(s).replace(',', '.').replace('—', '').replace('–', '').strip()
	if s in ('', '-', '.'):
		return ''
	# ".3" → "0.3"
	if s.startswith('.'):
		s = '0' + s
	return s


def steel_slug(st: str) -> str:
	table = str.maketrans({
		'Х': 'х', 'Г': 'г', 'С': 'с', 'Ф': 'ф', 'А': 'а',
		'Н': 'н', 'М': 'м', 'Т': 'т', 'У': 'у',
	})
	return st.translate(table).lower()


def pn_sku(pn: str) -> str:
	return pn.replace('.', 'p')


def fix_row(rec: dict) -> dict | None:
	dn = int(rec['dn'])
	pn = rec['pn_kgf']
	if pn not in PN_MPA:
		return None
	# Prefer commercial range used on site previously (was DN≤350).
	if dn < 15 or dn > 350:
		return None

	D, D1 = float(rec['D']), float(rec['D1'])
	d1 = float(rec['d1']) if rec['d1'] else 0
	n = int(float(rec['n'])) if rec['n'] else 0
	if n not in (4, 8, 12, 16, 20, 24, 28, 32):
		return None
	if not (D > D1 > d1 > 0):
		# PN100 1-200-100 has swapped D — drop bad rows.
		return None

	# Neck OD is stable across PN for a given DN.
	expect_dn = DN_NECK.get(dn)
	if expect_dn and rec['Dn'] and abs(float(rec['Dn']) - expect_dn) > 1:
		rec['Dn'] = str(expect_dn)

	rec['pn_mpa'] = PN_MPA[pn]
	return rec


def parse_tables(text: str) -> dict[str, dict]:
	seen: dict[str, dict] = {}
	for m in ROW_RE.finditer(text):
		desig, dn, pn = m.group(1), m.group(2), m.group(3)
		rec = {
			'desig': desig,
			'dn': dn,
			'pn_kgf': pn,
			'D': num(m.group(4)),
			'D1': num(m.group(5)),
			'D2': num(m.group(6)),
			'd': num(m.group(7)),
			'Dm': num(m.group(8)),
			'Dn': num(m.group(9)),
			'd1': num(m.group(10)),
			'b': num(m.group(11)),
			'h': num(m.group(12)),
			'h4': num(m.group(13)),
			'n': num(m.group(14)),
			'mass': num(m.group(15)),
		}
		fixed = fix_row(rec)
		if fixed:
			seen[desig] = fixed
	return seen


def empty_row(fieldnames: list[str]) -> dict:
	return {k: '' for k in fieldnames}


def build_rows(sizes: dict[str, dict], fieldnames: list[str]) -> list[dict]:
	out: list[dict] = []
	steels_pipe = '|'.join(STEELS)
	for desig in sorted(
		sizes.keys(),
		key=lambda d: (int(sizes[d]['pn_kgf']), int(sizes[d]['dn'])),
	):
		s = sizes[desig]
		dn, pn = s['dn'], s['pn_kgf']
		base = f"гост-12821-1980-{dn}-pn{pn_sku(pn)}-фв"
		title_parent = f"Фланец ФВ DN{dn} PN{pn} ГОСТ 12821-1980"
		desc = (
			f"Изделие по ГОСТ 12821-1980. Тип: ФВ. Обозначение: {desig}. "
			f"DN {dn}. PN {pn} кгс/см² ({s['pn_mpa']} МПа). Исполнение 1."
		)
		dims = {
			'outer_diameter': s['D'],
			'dim_outer_diameter': s['D'],
			'dim_bolt_circle_d': s['D1'],
			'dim_bolt_d': s['d'],
			'dim_stud_count': s['n'],
			'dim_d_inner': s['d1'],
			'dim_flange_thickness': s['b'],
			'dim_h4': s['h4'],
			'dim_pn': pn,
			'dim_pn_mpa': s['pn_mpa'],
			'dim_execution': '1',
			'dim_flange_type': 'ФВ',
			'dim_seal_face': '1',
			'dim_mass_kg': s['mass'],
			'dim_gost_designation': desig,
			'dim_page': '',
			'pn': pn,
			'dn': dn,
			'mass_kg': s['mass'],
			'execution': '1',
			'product_type': 'ФВ',
			'gost_designation': desig,
		}
		parent = empty_row(fieldnames)
		parent.update({
			'sku': base,
			'full_designation': 'ГОСТ 12821-1980',
			'category': 'фланцы',
			'product_family': 'Фланцы',
			'product_type': 'ФВ',
			'title': title_parent,
			'normative_key': 'ГОСТ 12821-1980',
			'pdf_path': 'pdf/отводы/ГОСТ_12821_1980.pdf',
			'supervised': 'False',
			'base_sku': base,
			'description': desc,
			'attributes': (
				f"dn={dn}; outer_diameter={s['D']}; pn={pn}; mass_kg={s['mass']}; "
				f"execution=1; d_inner={s['d1']}; flange_thickness={s['b']}; "
				f"pn_mpa={s['pn_mpa']}; bolt_circle_d={s['D1']}; stud_count={s['n']}; "
				f"bolt_d={s['d']}; h4={s['h4']}; flange_type=ФВ"
			),
			'woo_type': 'variable',
			'parent_sku': '',
			'attr_steel': steels_pipe,
			'attr_supervised': 'Нет',
			'stock_status': 'onbackorder',
			'manage_stock': 'no',
		})
		parent.update(dims)
		out.append(parent)

		for st in STEELS:
			var = empty_row(fieldnames)
			var.update(parent)
			var.update({
				'sku': f"{base}-{steel_slug(st)}",
				'title': f"Фланец ФВ DN{dn} PN{pn} Ст{st} ГОСТ 12821-1980",
				'material': st,
				'material_grade': st,
				'woo_type': 'variation',
				'parent_sku': base,
				'attr_steel': st,
				'description': f"{desc} Материал: {st}.",
			})
			out.append(var)
	return out


def main() -> None:
	ap = argparse.ArgumentParser()
	ap.add_argument('source', type=Path, help='Markdown/HTML with pipe tables')
	ap.add_argument('-o', '--output', type=Path, required=True)
	ap.add_argument(
		'--template',
		type=Path,
		default=Path('products-csv/products_ost_34_10_700_fill.csv'),
		help='CSV whose header to reuse',
	)
	args = ap.parse_args()

	text = args.source.read_text(encoding='utf-8', errors='ignore')
	sizes = parse_tables(text)
	if not sizes:
		raise SystemExit('no rows parsed')

	with args.template.open(encoding='utf-8-sig', newline='') as f:
		fieldnames = list(csv.DictReader(f).fieldnames or [])

	rows = build_rows(sizes, fieldnames)
	args.output.parent.mkdir(parents=True, exist_ok=True)
	with args.output.open('w', encoding='utf-8', newline='') as f:
		w = csv.DictWriter(f, fieldnames=fieldnames, extrasaction='ignore')
		w.writeheader()
		w.writerows(rows)

	parents = sum(1 for r in rows if r['woo_type'] == 'variable')
	vars_ = sum(1 for r in rows if r['woo_type'] == 'variation')
	print(f'wrote {args.output}: {parents} parents + {vars_} variations ({len(sizes)} sizes)')
	# sanity sample
	for key in ('1-15-10', '1-300-25', '1-20-63'):
		if key in sizes:
			print(key, sizes[key])


if __name__ == '__main__':
	main()
