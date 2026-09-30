"""ГОСТ 33259-2015, таблицы 3 (тип 01) и 6 (тип 11) → присоединительные размеры по рядам.

Разбор по геометрии ячеек: колонки — по ячейкам «PN …», подряды — по ячейкам
«Ряд 1»/«Ряд 2», значение — текст ячейки, накрывающей точку (колонка × подряд).
Так учитываются объединения в обе стороны: по PN и через оба ряда сразу.
Ряд 1 — отечественный (совпадает с ГОСТ 12815: DN100 PN25 = 230/190/8×M20),
ряд 2 — ISO (Dn 114,3). В каталоге обозначение «100-11-1-…» — ряд 1.
"""
import json, re, sys, fitz
sys.stdout.reconfigure(encoding='utf-8')

PAGES = {'01': range(28, 36), '11': range(49, 62)}
FIELD = {'D': 'D', 'D1': 'D1', 'd': 'd', 'n': 'n', 'b': 'b'}
HOLE_TO_M = {11: 10, 14: 12, 18: 16, 22: 20, 26: 24, 30: 27, 33: 30, 36: 33, 39: 36, 42: 39, 45: 42, 48: 45, 52: 48, 56: 52, 62: 56, 66: 60, 70: 64}
norm = lambda s: re.sub(r'\s+', ' ', s).strip()

doc = fitz.open('g33259_owen.pdf')
out, problems = {}, []
for typ, pages in PAGES.items():
    for pno in pages:
        page = doc[pno - 1]
        tabs = page.find_tables().tables
        if not tabs:
            problems.append((typ, pno, 'нет таблицы')); continue
        cells = [fitz.Rect(c) for c in tabs[0].cells if c]
        txt = {id(c): norm(page.get_textbox(c)) for c in cells}
        cell_at = lambda x, y: min((c for c in cells if c.contains(fitz.Point(x, y))), key=lambda c: c.get_area(), default=None)
        # колонки PN
        pncols = sorted(((c, float(re.search(r'PN ([\d,]+)', txt[id(c)]).group(1).replace(',', '.'))) for c in cells if re.fullmatch(r'PN [\d,]+', txt[id(c)])), key=lambda t: t[0].x0)
        if not pncols:
            problems.append((typ, pno, 'нет колонок PN')); continue
        y_dn = next((c for c in cells if txt[id(c)] == 'DN' and c.x1 <= pncols[0][0].x0 + 1), None)
        # подряды и поля
        subs = [c for c in cells if txt[id(c)] in ('Ряд 1', 'Ряд 2') and c.x1 <= pncols[0][0].x0 + 1]
        labels = [c for c in cells if c.x1 <= pncols[0][0].x0 + 1 and txt[id(c)] not in ('Ряд 1', 'Ряд 2')]
        bands = []  # (field, row_no, y)
        for s in subs:
            yc = (s.y0 + s.y1) / 2
            lab = max((l for l in labels if l.y0 - 0.5 <= yc <= l.y1 + 0.5 and l.x1 <= s.x0 + 1), key=lambda l: l.x0, default=None)
            name = txt[id(lab)] if lab else ''
            key = 'M' if name.startswith('Номинальн') else FIELD.get(name.replace(' ', ''))
            if key:
                bands.append((key, 1 if txt[id(s)] == 'Ряд 1' else 2, yc))
        for l in labels:  # однорядные поля (D1) — подпись во всю ширину колонки меток
            name = txt[id(l)].replace(' ', '')
            if name in ('D1',):
                bands.append(('D1', 0, (l.y0 + l.y1) / 2))
        for pc, pn in pncols:
            xc = (pc.x0 + pc.x1) / 2
            dn_cell = cell_at(xc, (y_dn.y0 + y_dn.y1) / 2) if y_dn else None
            m = re.search(r'DN (\d+)', txt[id(dn_cell)]) if dn_cell else None
            if not m:
                continue
            dn = int(m.group(1))
            vals = {}
            for key, row, yc in bands:
                c = cell_at(xc, yc)
                vals[(key, row)] = txt[id(c)] if c else None
            for row in (1, 2):
                g = lambda k: vals.get((k, row), vals.get((k, 0)))
                D, d, n, b, M = g('D'), g('d'), g('n'), g('b'), g('M')
                if not D or D == '—':
                    continue
                hole = int(float(d.replace(',', '.'))) if d and d not in ('—',) and re.fullmatch(r'[\d,]+', d) else None
                m_tab = int(re.sub(r'\D', '', M)) if M and re.search(r'\d', M) else None
                m_d = HOLE_TO_M.get(hole)
                if m_tab and m_d and m_tab != m_d:
                    problems.append((typ, dn, pn, row, f'M{m_tab} ≠ отв. {hole}'))
                out[f'{typ}|{dn}|{pn:g}|{row}'] = {'D': D, 'D1': vals.get(('D1', 0)), 'd': d, 'n': n, 'M': m_tab or m_d, 'b': b}

# Поправка ИУС №11-2016 к таблице 6 (ряд 1): DN 65 и 80 на PN 63 — M20, отверстия 22.
for dn in (65, 80):
    k = f'11|{dn}|63|1'
    if k in out:
        out[k].update(M=20, d='22')

json.dump(out, open('g33259_table.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=0)
print('записей', len(out), '| проблем', len(problems))
for p in problems[:20]:
    print('  ', p)
for k in ('11|100|16|1', '11|100|25|1', '11|100|160|1', '11|100|16|2', '01|100|16|1', '01|150|16|1', '11|80|16|1', '11|65|63|1', '01|10|1|1', '11|250|16|1', '01|600|2.5|1', '11|1600|1|1'):
    print(k, out.get(k))
