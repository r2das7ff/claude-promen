# -*- coding: utf-8 -*-
"""Извлечение b (толщина фланца) и массы исп.1 из PDF ГОСТ 12820-80 / 12821-80.

У этих PDF честный текст и сетка (тонкие rect). Алгоритм: ячейки по сетке
(объединённые ячейки сами растягиваются на свои строки), секции «Ру X МПа»,
колонка b — по ячейке-заголовку «b», масса — первая подколонка «Масса».

Выход: _gost1282x_ref.json  {"12820|50|16": {"b": 13, "mass": 2.22}, ...}
Запуск: python scripts/_gost1282x_extract.py <каталог с PDF>
"""
import fitz, json, re, sys, pathlib

DY = [10, 15, 20, 25, 32, 40, 50, 65, 80, 100, 125, 150, 200, 250, 300, 350,
      400, 450, 500, 600, 700, 800, 900, 1000, 1200, 1400, 1600, 1800, 2000, 2200, 2400]
# «Py 0,1 и 0,25 МПа» — Р/P и у/y бывают латиницей; сдвоенные секции.
RU_HDR = re.compile(r'[РP][уy]\s*([\d,.]+(?:\s*и\s*[\d,.]+)*)\s*МПа')

def ru_list(expr):
    return [float(x.replace(',', '.')) for x in re.split(r'\s*и\s*', expr)]

def page_cells(p):
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
                if r.width < 1.6 and r.height > 4:
                    vx.append((r.x0, r.y0, r.y1))
                elif r.height < 1.6 and r.width > 4:
                    hy.append((r.y0, r.x0, r.x1))
                else:  # заполненные клетки-рамки: берём границы
                    vx.append((r.x0, r.y0, r.y1)); vx.append((r.x1, r.y0, r.y1))
                    hy.append((r.y0, r.x0, r.x1)); hy.append((r.y1, r.x0, r.x1))
    cells = {}
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
        cells.setdefault(key, []).append((y0, x0, txt))
    return {k: ' '.join(t for _, _, t in sorted(v)) for k, v in cells.items()}

def to_num(t):
    t = t.strip().strip('()').replace(',', '.')
    try:
        return float(t)
    except ValueError:
        return None

def parse(pdf, norm):
    d = fitz.open(pdf)
    res = {}
    ru = None
    for p in d:
        cells = page_cells(p)
        # секции Ру: строки текста с «Ру … МПа» (могут быть ячейками таблицы)
        sec = []
        text_rows = {}
        for w in p.get_text('words'):
            text_rows.setdefault(round(w[1] / 3), []).append((w[0], w[4]))
        for k in sorted(text_rows):
            line = ' '.join(t for _, t in sorted(text_rows[k]))
            m = RU_HDR.search(line)
            if m:
                sec.append((k * 3, ru_list(m.group(1))))
        def ru_at(y):
            nonlocal ru
            cand = [v for yy, v in sec if yy <= y + 2]
            if cand:
                ru = cand[-1]
            return ru
        # колонки b и Масса: ячейки-заголовки
        b_cols, m_cols = [], []
        for (l, t, r, b_), txt in cells.items():
            tt = txt.strip()
            if tt == 'b':
                b_cols.append((l, r, b_))
            elif tt.startswith('Масса'):
                m_cols.append((l, r, b_))
        if not b_cols:
            continue
        # Dy-ячейки: текст из ряда DY в левой колонке таблицы (x≈65);
        # артефактные ячейки на x=0 не в счёт
        lefts = [k[0] for k in cells if 40 <= k[0] <= 120]
        if not lefts:
            continue
        min_l = min(lefts)
        for (l, t, r, b_), txt in sorted(cells.items(), key=lambda kv: kv[0][1]):
            if l < min_l - 2 or l > min_l + 30:
                continue
            first = txt.strip().strip('()')
            if not re.fullmatch(r'\d+', first) or int(first) not in DY:
                continue
            dy = int(first)
            band = (t, b_)
            cur_ru = ru_at(t)
            if cur_ru is None:
                continue
            # b: ячейка b-колонки, чей интервал пересекает полосу строки Dy
            # (объединённые по диапазону Dy ячейки покрывают полосу целиком)
            bv = None
            for cl, cr, _ in b_cols:
                for (l2, t2, r2, b2), tx in cells.items():
                    if abs(l2 - cl) > 6:
                        continue
                    ov = min(b2, band[1]) - max(t2, band[0])
                    if ov < (band[1] - band[0]) * 0.5:
                        continue
                    v = to_num(tx)
                    if v is not None:
                        bv = v
                        break
                if bv is not None:
                    break
            mass = None
            if m_cols:
                # первая подколонка группы «Масса»; у DN с суб-рядами (А/Б/В)
                # берём верхнюю суб-строку
                gl, gr, gb = sorted(m_cols)[0]
                cand = []
                for (l2, t2, r2, b2), tx in cells.items():
                    if l2 < gl - 6 or r2 > gr + 6 or t2 < gb - 8:
                        continue
                    ov = min(b2, band[1]) - max(t2, band[0])
                    if ov <= 2:
                        continue
                    v = to_num(tx)
                    if v is not None:
                        cand.append((l2, t2, v))
                if cand:
                    lmin = min(c[0] for c in cand)
                    tops = sorted([c for c in cand if abs(c[0] - lmin) < 6], key=lambda c: c[1])
                    mass = tops[0][2]
            if bv is None or not (5 <= bv <= 130):
                continue
            for rv in cur_ru:
                key = f'{norm}|{dy}|%g' % (rv * 10)
                if key not in res:
                    res[key] = {'b': bv, 'mass': mass}
    return res

if __name__ == '__main__':
    base = pathlib.Path(sys.argv[1]) if len(sys.argv) > 1 else pathlib.Path('normatives/Фланцы')
    res = {}
    res.update(parse(base / 'ГОСТ 12820-1980.pdf', '12820'))
    res.update(parse(base / 'ГОСТ 12821-1980.pdf', '12821'))
    out = pathlib.Path(__file__).parent / '_gost1282x_ref.json'
    out.write_text(json.dumps(res, ensure_ascii=False, indent=1), encoding='utf-8')
    from collections import Counter
    print('записей:', len(res), Counter(k.split('|')[0] for k in res))
    for k in ['12820|50|16', '12820|100|10', '12821|50|16', '12821|15|10', '12821|500|25']:
        print(k, res.get(k))
