# -*- coding: utf-8 -*-
"""Извлечение эталона из ЧИСТОЙ копии ГОСТ 33259-2015 (официальная вёрстка,
честный текст; таблицы транспонированы: поля — горизонтальные полосы,
DN/PN — вертикальные колонки; «Ряд 2» полоса выше «Ряд 1»).

Таблица 3 (тип 01) — стр. 28–34; Таблица 6 (тип 11) — стр. 49–61.
Болтовая окружность здесь обозначена D1.

Выход: _gost33259_ref_clean.json — формат как у _gost33259_extract.py.
Запуск: python scripts/_gost33259_extract_clean.py <путь к чистому PDF>
"""
import fitz, json, re, sys, pathlib

FIELD_MAP = {'болт': 'M', 'n': 'n', 'd': 'd', 'D1': 'D2', 'D': 'D', 'b': 'b',
             'PN': 'PN', 'DN': 'DN'}

def page_grid(p):
    vx, hy = [], []
    for dr in p.get_drawings():
        for it in dr['items']:
            if it[0] == 'l':
                a, b = it[1], it[2]
                if abs(a.x - b.x) < 0.7 and abs(a.y - b.y) > 4:
                    vx.append((a.x, min(a.y, b.y), max(a.y, b.y)))
                elif abs(a.y - b.y) < 0.7 and abs(a.x - b.x) > 4:
                    hy.append((a.y, min(a.x, b.x), max(a.x, b.x)))
            elif it[0] == 're':
                r = it[1]
                if r.width < 1.2 and r.height > 4:
                    vx.append((r.x0, r.y0, r.y1))
                elif r.height < 1.2 and r.width > 4:
                    hy.append((r.y0, r.x0, r.x1))
    return vx, hy

def cells_of_page(p):
    vx, hy = page_grid(p)
    out = {}
    for w in p.get_text('words'):
        x0, y0, x1, y1, txt = w[0], w[1], w[2], w[3], w[4]
        cx, cy = (x0 + x1) / 2, (y0 + y1) / 2
        left = max((x for x, ya, yb in vx if x <= cx + 0.5 and ya - 1 <= cy <= yb + 1), default=None)
        right = min((x for x, ya, yb in vx if x >= cx - 0.5 and ya - 1 <= cy <= yb + 1), default=None)
        top = max((y for y, xa, xb in hy if y <= cy + 0.5 and xa - 1 <= cx <= xb + 1), default=None)
        bot = min((y for y, xa, xb in hy if y >= cy - 0.5 and xa - 1 <= cx <= xb + 1), default=None)
        if None in (left, right, top, bot):
            continue
        key = (round(left, 1), round(top, 1), round(right, 1), round(bot, 1))
        out.setdefault(key, []).append((y0, x0, txt))
    return {k: ' '.join(t for _, _, t in sorted(v)) for k, v in out.items()}

def field_of(label):
    t = label.replace(',', ' ').strip()
    if 'болт' in t or 'шпил' in t or 'Номинальн' in t:
        return 'M'
    if re.search(r'\bDN\b', t):
        return 'DN'
    if re.search(r'\bPN\b', t):
        return 'PN'
    for pat, f in (('D1', 'D2'), ('D', 'D'), ('b', 'b'), ('n', 'n'), ('d', 'd')):
        if t == pat:
            return f
    return None

def to_num(t):
    t = t.strip().replace(',', '.')
    if t in ('', '—', '-', '–'):
        return None
    m = re.fullmatch(r'[\d.]+', t)
    return float(t) if m else None

def extract(pdf, pages, typ):
    d = fitz.open(pdf)
    recs = {}
    for pageno in pages:
        cells = cells_of_page(d[pageno - 1])
        if not cells:
            continue
        xs = sorted({k[0] for k in cells})
        label_x, sub_x = xs[0], xs[1]
        # полосы полей
        bands = []
        for (l, t, r, b), txt in cells.items():
            if l != label_x:
                continue
            f = field_of(txt)
            if f:
                bands.append({'f': f, 'top': t, 'bot': b})
        # ряд-полосы: подзаголовки «Ряд 1»/«Ряд 2»
        ryads = []
        for (l, t, r, b), txt in cells.items():
            if l != sub_x:
                continue
            m = re.search(r'([12])\s*Ряд|Ряд\s*([12])', txt)
            if m:
                ryads.append({'r': int(m.group(1) or m.group(2)), 'top': t, 'bot': b})
        def band_at(y):
            for bd in bands:
                if bd['top'] - 1 <= y <= bd['bot'] + 1:
                    return bd
            return None
        def ryad_at(y, bd):
            for rr in ryads:
                if bd['top'] - 1 <= rr['top'] and rr['bot'] <= bd['bot'] + 1 \
                        and rr['top'] - 1 <= y <= rr['bot'] + 1:
                    return rr['r']
            return 0
        # колонки DN и PN
        dn_cols, pn_cols = [], []
        for (l, t, r, b), txt in cells.items():
            bd = band_at((t + b) / 2)
            if not bd:
                continue
            if bd['f'] == 'DN':
                digits = re.sub(r'\D', '', txt)
                if digits:
                    dn_cols.append({'dn': int(digits), 'l': l, 'r': r})
            elif bd['f'] == 'PN':
                m = re.search(r'([\d,.]+)', txt)
                if m:
                    pn_cols.append({'pn': float(m.group(1).replace(',', '.')), 'l': l, 'r': r})
        # значения
        for (l, t, r, b), txt in cells.items():
            cy = (t + b) / 2
            bd = band_at(cy)
            if not bd or bd['f'] in ('DN', 'PN', None):
                continue
            f = bd['f']
            if f not in ('M', 'n', 'd', 'D2', 'D', 'b'):
                continue
            if f == 'M':
                m = re.search(r'М\s*(\d+)', txt.replace('M', 'М'))
                v = int(m.group(1)) if m else None
            else:
                v = to_num(txt)
            if v is None:
                continue
            # ряды, которые покрывает ячейка по высоте
            covered = set()
            for rr in ryads:
                if bd['top'] - 1 <= rr['top'] and rr['bot'] <= bd['bot'] + 1:
                    ov = min(b, rr['bot']) - max(t, rr['top'])
                    if ov > (rr['bot'] - rr['top']) * 0.5:
                        covered.add(rr['r'])
            if not covered:
                covered = {1, 2}  # поле без ряд-развилки — общее
            # PN-колонки, которые покрывает ячейка по ширине
            for pc in pn_cols:
                ov = min(r, pc['r']) - max(l, pc['l'])
                if ov < (pc['r'] - pc['l']) * 0.5:
                    continue
                dn = None
                for dc in dn_cols:
                    if dc['l'] - 1 <= pc['l'] and pc['r'] <= dc['r'] + 1:
                        dn = dc['dn']
                        break
                if dn is None:
                    continue
                key = (dn, pc['pn'])
                rec = recs.setdefault(key, {'page': pageno})
                for ry in covered:
                    tgt = f'{f}_r{ry}'
                    if tgt in rec and rec[tgt] != v:
                        rec.setdefault('conflicts', []).append(f'{tgt}:{rec[tgt]}|{v}')
                    rec[tgt] = v
    out = {}
    for (dn, pn), rec in sorted(recs.items()):
        item = {'page': rec['page'], 'flags': rec.get('conflicts', [])}
        for ry in (1, 2):
            row = {f: rec[f'{f}_r{ry}'] for f in ('D', 'D2', 'd', 'n', 'M', 'b')
                   if rec.get(f'{f}_r{ry}') is not None}
            if row:
                item[f'r{ry}'] = row
        out[f'{typ}|{dn}|%g' % pn] = item
    return out

def main():
    pdf = sys.argv[1]
    res = {}
    res.update(extract(pdf, range(28, 35), '01'))
    res.update(extract(pdf, range(49, 62), '11'))
    out = pathlib.Path(__file__).parent / '_gost33259_ref_clean.json'
    out.write_text(json.dumps(res, ensure_ascii=False, indent=1), encoding='utf-8')
    flagged = sum(1 for v in res.values() if v['flags'])
    print(f'записей: {len(res)}, с конфликтами: {flagged} -> {out}')

if __name__ == '__main__':
    main()
