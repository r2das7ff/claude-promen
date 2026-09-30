"""ГОСТ 12820-80 и 12821-80: толщина фланца b (и масса исп. 1) по таблицам на каждое Ру.

Присоединительные размеры (D, D1, отверстия) эти стандарты берут из ГОСТ 12815 —
они совпадают с рядом 1 ГОСТ 33259 (проверено на 69 из 75 карточек 12821).
Таблицы книжные, объединения только вертикальные: пустая ячейка = как выше,
в том числе через перенос таблицы на следующую страницу.
"""
import glob, json, os, re, sys, fitz
sys.stdout.reconfigure(encoding='utf-8')
base = r'C:\Users\User\Documents\promen site\normatives'
SPEC = {
    'ГОСТ 12820-1980': {'b_off': 3, 'pn': {1: [1, 2.5], 2: [6], 3: [10], 4: [16], 5: [25]}},
    'ГОСТ 12821-1980': {'b_off': 2, 'pn': {1: [1, 2.5], 2: [6], 3: [10], 4: [16], 5: [25], 6: [40], 7: [63], 8: [100], 9: [160], 10: [200]}},
}
out = {}
for name, spec in SPEC.items():
    f = [x for x in glob.glob(os.path.join(base, '*', '*.pdf')) if os.path.basename(x) == name + '.pdf'][0]
    doc = fitz.open(f)
    cur_tab, last_b = None, None
    res = {}
    for page in doc:
        caps = []
        for b in page.get_text('blocks'):
            m = re.search(r'Таблица\s+(\d+)', b[4])
            if m:
                caps.append((b[1], int(m.group(1))))
        for t in page.find_tables().tables:
            above = [n for y, n in caps if y < t.bbox[1] + 5]
            if above and above[-1] != cur_tab:
                cur_tab, last_b = above[-1], None
            if cur_tab not in spec['pn']:
                continue
            for r in t.extract():
                cells = [(c or '').strip() if c is not None else None for c in r]
                idx = next((i for i, c in enumerate(cells) if c and re.fullmatch(r'\(?\d{2,4}\)?', c)), None)
                if idx is None:
                    continue
                dn = int(re.sub(r'\D', '', cells[idx]))
                bi = idx + spec['b_off']
                bv = cells[bi] if bi < len(cells) else None
                if bv:
                    last_b = bv
                b = last_b
                mass = next((c for c in cells[bi + 1:] if c and re.fullmatch(r'[\d,.]+', c)), None) if name.endswith('12820-1980') else None
                for pn in spec['pn'][cur_tab]:
                    res[f'{dn}|{pn:g}'] = {'b': b, 'mass': mass}
    out[name] = res
    print(name, 'записей', len(res), '| пример DN100:', {k: v for k, v in res.items() if k.startswith('100|')})
json.dump(out, open('g1282x_table.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=0)
