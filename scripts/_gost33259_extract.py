# -*- coding: utf-8 -*-
"""Извлечение эталонных размеров фланцев из PDF ГОСТ 33259-2015 (ребренд-копия
с битым ToUnicode: буквы и часть цифр искажены OCR-ом; геометрия сетки точная).

Таблица 3 (тип 01, плоские приварные)  — стр. 29–36.
Таблица 6 (тип 11, приварные встык)    — стр. 50–63.

Ячейки привязываются к линиям сетки (объединённые ячейки растягиваются на свои
PN-строки), колонки мапятся по стабильным x-окнам, DN-блоки и PN-строки
нормализуются по порядку следования. Значения с нестандартной резьбой,
несогласованной парой «резьба/отверстие» или нарушением геометрии помечаются
флагами — их добивает ручная сверка с рендером страницы (_gost33259_overrides.json).

Выход: scripts/_gost33259_ref.json
    {"01|50|16": {"r1": {"D":165,"D2":125,"d":18,"n":4,"M":16,"b":...}, "r2": {...},
                  "flags": [...]}, ...}
Запуск: python scripts/_gost33259_extract.py <путь к ГОСТ 33259-2015.pdf>
"""
import fitz, json, re, sys, pathlib

DN_SEQ = [10, 15, 20, 25, 32, 40, 50, 65, 80, 100, 125, 150, 200, 250, 300, 350,
          400, 450, 500, 600, 700, 800, 900, 1000, 1200, 1400, 1600, 1800, 2000,
          2200, 2400, 2600, 2800, 3000, 3200, 3400, 3600, 3800, 4000]
PN_SEQ = [1, 2.5, 6, 10, 16, 25, 40, 63, 100, 160, 200, 250]

# x-окна колонок (левая грань ±10): поле -> (x_нominal, ряд)
T3_COLS = {165.6: ('d0', 1), 208.8: ('d0', 2), 251.1: ('b', 1), 293.4: ('b', 2),
           312.5: ('b', 2), 378.0: ('D', 1), 421.2: ('D', 2), 463.5: ('D2', 0),
           505.8: ('d', 1), 540.0: ('d', 2), 574.2: ('n', 1), 616.5: ('n', 2),
           658.8: ('M', 1), 702.0: ('M', 2)}
T6_COLS = {341.1: ('b', 1), 369.0: ('b', 2), 397.8: ('H', 1), 425.7: ('H', 2),
           482.4: ('D', 1), 511.2: ('D', 2), 539.1: ('D2', 0),
           567.9: ('d', 1), 595.8: ('d', 2), 624.6: ('n', 1), 652.5: ('n', 2),
           681.3: ('M', 1), 711.9: ('M', 2)}
JUNK = re.compile(r'\+7|\(351|prom-en|zakaz|ДЕТАЛИ|ТРУБЫ|АРМАТУРА')

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
        if JUNK.search(txt):
            continue
        cx, cy = (x0 + x1) / 2, (y0 + y1) / 2
        left = max((x for x, ya, yb in vx if x <= cx + 0.5 and ya - 1 <= cy <= yb + 1), default=None)
        right = min((x for x, ya, yb in vx if x >= cx - 0.5 and ya - 1 <= cy <= yb + 1), default=None)
        top = max((y for y, xa, xb in hy if y <= cy + 0.5 and xa - 1 <= cx <= xb + 1), default=None)
        bot = min((y for y, xa, xb in hy if y >= cy - 0.5 and xa - 1 <= cx <= xb + 1), default=None)
        if left is None or right is None or top is None or bot is None:
            continue
        key = (round(left, 1), round(top, 1), round(right, 1), round(bot, 1))
        out.setdefault(key, []).append((y0, x0, txt))
    return {k: ' '.join(t for _, _, t in sorted(v)) for k, v in out.items()}

DIGIT_FIX = str.maketrans({'О': '0', 'о': '0', 'З': '3', 'з': '3', 'б': '6',
                           'в': '6', 'l': '1', 'I': '1', 'i': '1', 'S': '5',
                           ',': '.'})

def parse_num(txt):
    """Число из ячейки; None если мусор/прочерк. Флаг при глиф-починке."""
    t = txt.strip()
    if t in ('', '—', '-', '–', '•', '«', '*', 'X'):
        return None, []
    clean = re.sub(r'[^0-9.]', '', t.translate(DIGIT_FIX))
    if clean in ('', '.'):
        return None, [f'unparsed:{txt}']
    try:
        v = float(clean)
    except ValueError:
        return None, [f'unparsed:{txt}']
    flags = [] if re.fullmatch(r'[\d.,\s]+', t) else [f'glyphfix:{txt}']
    return v, flags

THREADS = [10, 12, 16, 20, 24, 27, 30, 33, 36, 39, 42, 45, 48, 52, 56, 60, 64]

def parse_thread(txt):
    t = txt.strip()
    if t in ('', '—', '-', '–', '•'):
        return None, []
    tt = t.upper().replace('Ю', '10').replace('Э', '3').replace('M', 'М')
    m = re.search(r'М\s*([\d.,]+)', tt.translate(str.maketrans({'О': '0', 'З': '3', 'б': '6'})))
    if not m:
        return None, [f'thread_unparsed:{txt}']
    v = int(float(m.group(1).replace(',', '.')))
    flags = []
    if not re.fullmatch(r'[МM]\s?\d+', t):
        flags.append(f'thread_glyphfix:{txt}')
    if v not in THREADS:
        flags.append(f'thread_nonstd:{txt}->{v}')
    return v, flags

def norm_pn_labels(labels):
    """Список подписей PN сверху вниз -> значения из PN_SEQ по возрастанию."""
    cands = []
    for raw in labels:
        digits = re.sub(r'[^0-9.]', '', raw.translate(DIGIT_FIX).replace('Ю', '10'))
        cand = set()
        for pn in PN_SEQ:
            s = ('%g' % pn).replace('.', '')
            if digits.replace('.', '') == s:
                cand.add(pn)
        if digits.replace('.', '') == '25':
            cand.update((2.5, 25))
        if digits in ('', '.'):
            cand = set(PN_SEQ)  # совсем мусор — решает порядок
        cands.append(cand or set(PN_SEQ))
    out, prev = [], 0
    for cand in cands:
        ok = sorted(v for v in cand if v > prev)
        if not ok:
            return None
        out.append(ok[0])
        prev = ok[0]
    return out

def extract_table(pdf, pages, colmap, dn_start_idx):
    d = fitz.open(pdf)
    recs = {}
    dn_idx = dn_start_idx
    for pageno in pages:
        p = d[pageno - 1]
        cells = cells_of_page(p)
        xs = sorted({k[0] for k in cells})
        dn_col, pn_col = xs[0], xs[1]
        # DN-блоки: ячейки первой колонки с цифрами, сверху вниз. Высота ≥25pt
        # отсекает мусорные сливеры битых страниц (стр. 59 нечитаема целиком).
        dn_cells = sorted(((k[1], k[3], t) for k, t in cells.items()
                           if k[0] == dn_col and re.search(r'\d', t) and k[1] > 60
                           and k[3] - k[1] >= 25), key=lambda z: z[0])
        blocks = []
        for top, bot, label in dn_cells:
            if dn_idx >= len(DN_SEQ):
                break
            # Ресинк по чистой подписи: 'ON 700' -> 700. Защита: только вперёд
            # и не дальше +3 позиций (битые страницы съедают индекс).
            digits = re.sub(r'\D', '', label)
            if digits and int(digits) in DN_SEQ:
                j = DN_SEQ.index(int(digits))
                if dn_idx <= j <= dn_idx + 3:
                    dn_idx = j
            blocks.append({'dn': DN_SEQ[dn_idx], 'top': top, 'bot': bot})
            dn_idx += 1
        # PN-строки в каждом блоке
        for blk in blocks:
            pn_rows = sorted(((k[1], k[3], t) for k, t in cells.items()
                              if k[0] == pn_col and blk['top'] - 2 <= k[1] and k[3] <= blk['bot'] + 2
                              and re.search(r'\d', t)), key=lambda z: z[0])
            pns = norm_pn_labels([t for _, _, t in pn_rows])
            flags0 = []
            if pns is None:
                pns = PN_SEQ[:len(pn_rows)]
                flags0.append('pn_order_fallback:' + '|'.join(t for _, _, t in pn_rows))
            blk['rows'] = [(top, bot, pn) for (top, bot, _), pn in zip(pn_rows, pns)]
            blk['flags'] = flags0
        # значения
        for (l, t, r, b), txt in cells.items():
            field = None
            for xn, fr in colmap.items():
                if abs(l - xn) <= 10:
                    field = fr
                    break
            if field is None:
                continue
            name, row = field
            if name in ('d0', 'H'):
                continue
            for blk in blocks:
                if t >= blk['top'] - 2 and b <= blk['bot'] + 2:
                    for top, bot, pn in blk['rows']:
                        # ячейка покрывает PN-строку, если пересечение существенно
                        ov = min(b, bot) - max(t, top)
                        if ov < (bot - top) * 0.5:
                            continue
                        key = (blk['dn'], pn)
                        rec = recs.setdefault(key, {'flags': list(blk['flags']), 'page': pageno})
                        if name == 'M':
                            v, fl = parse_thread(txt)
                        else:
                            v, fl = parse_num(txt)
                        rec.setdefault('raw', {})[f'{name}_r{row}'] = txt
                        rec['flags'] += [f'{name}_r{row}:{f}' for f in fl]
                        if v is not None:
                            tgt = f'{name}_r{row}'
                            if tgt in rec and rec[tgt] != v:
                                rec['flags'].append(f'conflict:{tgt}={rec[tgt]}|{v}')
                            rec[tgt] = v
                    break
    return recs

def assemble(recs, typ):
    """recs[(dn,pn)] -> итоговые r1/r2 (D2 без ряда = общий)."""
    out = {}
    for (dn, pn), rec in sorted(recs.items()):
        item = {'flags': rec['flags'], 'page': rec['page'], 'raw': rec.get('raw', {})}
        for ryad in (1, 2):
            row = {}
            for f in ('D', 'D2', 'd', 'n', 'M', 'b'):
                v = rec.get(f'{f}_r{ryad}')
                if v is None and f == 'D2':
                    v = rec.get('D2_r0')
                if v is not None:
                    row[f] = v
            if row:
                item[f'r{ryad}'] = row
        out[f'{typ}|{dn}|{("%g" % pn)}'] = item
    return out

def main():
    pdf = sys.argv[1] if len(sys.argv) > 1 else 'normatives/Фланцы/ГОСТ 33259-2015.pdf'
    res = {}
    res.update(assemble(extract_table(pdf, range(29, 37), T3_COLS, 0), '01'))
    res.update(assemble(extract_table(pdf, range(50, 64), T6_COLS, 0), '11'))
    out = pathlib.Path(__file__).parent / '_gost33259_ref.json'
    out.write_text(json.dumps(res, ensure_ascii=False, indent=1), encoding='utf-8')
    flagged = sum(1 for v in res.values() if v['flags'])
    print(f'записей: {len(res)}, с флагами: {flagged}, -> {out}')

if __name__ == '__main__':
    main()
