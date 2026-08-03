# -*- coding: utf-8 -*-
"""Ремонт крепёжных dim_* у фланцев ГОСТ 33259/12820/12821 в products-csv.

Источник порчи: колонки dim_bolt_circle_d / dim_stud_count / dim_bolt_d /
dim_flange_thickness (и местами outer_diameter) в исходных CSV — склейки
парсера PDF (значения соседних строк/колонок таблиц, «36×М12 на DN50»).

Эталон: scripts/_flange_repair.json (см. _flange_repair_build.py).
Правила:
  - ряд: по совпадению outer_diameter c D ряда 1/2; иначе ряд 1 (предпочтительный
    по ГОСТ 33259), затем ряд 2; несовпавший outer_diameter чинится на D ряда;
  - bolt_d = НОМИНАЛ РЕЗЬБЫ (М), не диаметр отверстия;
  - b: 33259 — из Т3/Т6 своего ряда; 12820/12821 — из таблиц своего ГОСТа;
  - поля без эталона очищаются (склейку не оставляем);
  - строка attributes чинится синхронно (bolt_circle_d=…; stud_count=…; …).

Запуск: python scripts/_fix_flange_csv.py [--dry]
Файлы: products_variable.csv, products_фланцы.csv, products_master.csv (+ .bak).
"""
import csv, json, pathlib, re, shutil, sys

HERE = pathlib.Path(__file__).parent
CSVDIR = HERE.parent / 'products-csv'
FILES = ['products_variable.csv', 'products_фланцы.csv', 'products_master.csv']
NORMS = {'33259': '33259', '12820': '12820', '12821': '12821'}

def g(x):
    return ('%g' % float(x))

def fmt(v):
    if v is None:
        return ''
    s = '%g' % float(v)
    return s

def pick_row(conn, d_csv):
    """Выбор ряда по наружному диаметру; (ряд, данные, чинить_ли_D)."""
    r1, r2 = conn.get('r1'), conn.get('r2')
    if d_csv is not None:
        for name, row in (('r1', r1), ('r2', r2)):
            if row and row.get('D') is not None and abs(row['D'] - d_csv) < 0.51:
                return name, row, False
    for name, row in (('r1', r1), ('r2', r2)):
        if row and row.get('D') is not None:
            return name, row, d_csv is not None
    for name, row in (('r1', r1), ('r2', r2)):
        if row:
            return name, row, False
    return None, None, False

def main():
    dry = '--dry' in sys.argv
    rep = json.load(open(HERE / '_flange_repair.json', encoding='utf-8'))
    conn, b33, b20, b21 = rep['conn'], rep['b33259'], rep['b12820'], rep['b12821']

    for fn in FILES:
        path = CSVDIR / fn
        if not path.exists():
            print(f'-- нет файла {fn}')
            continue
        with open(path, encoding='utf-8-sig', newline='') as f:
            rd = csv.reader(f)
            header = next(rd)
            rows = list(rd)
        idx = {c: i for i, c in enumerate(header)}
        def col(r, name):
            i = idx.get(name)
            return r[i].strip() if i is not None and i < len(r) else ''
        def setcol(r, name, val):
            i = idx.get(name)
            if i is None:
                return
            while len(r) <= i:
                r.append('')
            r[i] = val
        fixed = skipped = noref = 0
        for r in rows:
            if col(r, 'category') != 'фланцы':
                continue
            nk = col(r, 'normative_key')
            norm = next((v for k, v in NORMS.items() if k in nk), None)
            if norm is None:
                continue
            typ = col(r, 'product_type') or col(r, 'dim_flange_type')
            dn, pn = col(r, 'dn'), col(r, 'pn')
            if not dn or not pn or not typ:
                skipped += 1
                continue
            ck = f'{g(dn)}|{g(pn)}'
            c = conn.get(ck)
            if not c:
                noref += 1
                continue
            d_csv = None
            try:
                d_csv = float(col(r, 'outer_diameter') or col(r, 'dim_outer_diameter'))
            except ValueError:
                pass
            ryad, row, fix_d = pick_row(c, d_csv)
            if not row:
                noref += 1
                continue
            # толщина по норме
            if norm == '33259':
                t33 = {'ФП': '01', 'ФВ': '11'}.get(typ, typ)
                bv = b33.get(f'{t33}|{ck}', {}).get(ryad)
            elif norm == '12820':
                bv = b20.get(ck)
            else:
                bv = b21.get(ck)
                if bv is None:
                    old = col(r, 'dim_flange_thickness')
                    try:
                        bv = float(old) if 6 <= float(old) <= 130 else None
                    except ValueError:
                        bv = None
            vals = {
                'dim_bolt_circle_d': fmt(row.get('D2')),
                'dim_stud_count': fmt(row.get('n')),
                'dim_bolt_d': fmt(row.get('M')),
                'dim_flange_thickness': fmt(bv),
            }
            if fix_d or d_csv is None:
                vals['outer_diameter'] = fmt(row.get('D'))
                vals['dim_outer_diameter'] = fmt(row.get('D'))
            for k, v in vals.items():
                setcol(r, k, v)
            # синхронно чиним строку attributes (key=value; ...)
            ai = idx.get('attributes')
            if ai is not None and ai < len(r) and r[ai]:
                a = r[ai]
                amap = {'bolt_circle_d': vals['dim_bolt_circle_d'],
                        'stud_count': vals['dim_stud_count'],
                        'bolt_d': vals['dim_bolt_d'],
                        'flange_thickness': vals['dim_flange_thickness']}
                if 'outer_diameter' in vals:
                    amap['outer_diameter'] = vals['outer_diameter']
                for k, v in amap.items():
                    if v:
                        a = re.sub(rf'(?<![a-z_]){k}=[^;]*', f'{k}={v}', a)
                    else:
                        a = re.sub(rf';?\s*(?<![a-z_]){k}=[^;]*', '', a)
                r[ai] = a
            fixed += 1
        print(f'{fn}: починено {fixed}, пропущено {skipped}, без эталона {noref}' + (' [dry]' if dry else ''))
        if not dry:
            shutil.copy2(path, str(path) + '.bak_flangefix')
            with open(path, 'w', encoding='utf-8-sig', newline='') as f:
                w = csv.writer(f)
                w.writerow(header)
                w.writerows(rows)

if __name__ == '__main__':
    main()
