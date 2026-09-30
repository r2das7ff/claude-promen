"""ГОСТ 12820/12821 из HTML «Гаранта»: толщина b по таблицам на каждое Ру (rowspan учтён)."""
import json, re, sys
from html.parser import HTMLParser
sys.stdout.reconfigure(encoding='utf-8')

class T(HTMLParser):
    def __init__(s):
        super().__init__(); s.tables = []; s.stack = []
    def handle_starttag(s, tag, a):
        a = dict(a)
        if tag == 'table': s.stack.append([]);
        elif tag == 'tr' and s.stack: s.stack[-1].append([])
        elif tag in ('td', 'th') and s.stack and s.stack[-1]:
            s.stack[-1][-1].append([int(a.get('rowspan', 1) or 1), int(a.get('colspan', 1) or 1), ''])
    def handle_endtag(s, tag):
        if tag == 'table' and s.stack: s.tables.append(s.stack.pop())
    def handle_data(s, d):
        if s.stack and s.stack[-1] and s.stack[-1][-1]: s.stack[-1][-1][-1][2] += d

def grid(rows):
    g, span = [], {}
    for i, r in enumerate(rows):
        line, j, cells = [], 0, list(r)
        while cells or (i, j) in span:
            if (i, j) in span:
                line.append(span[(i, j)]); j += 1; continue
            rs, cs, txt = cells.pop(0); txt = re.sub(r'\s+', ' ', txt).strip()
            for dj in range(cs):
                line.append(txt)
                for di in range(1, rs): span[(i + di, j + dj)] = txt
                j += 1
        g.append(line)
    return g

PN = {'ГОСТ 12821-1980': [[1, 2.5], [6], [10], [16], [25], [40], [63], [100]],
      'ГОСТ 12820-1980': [[1, 2.5], [6], [10], [16], [25]]}
out = {}
for name, fn in (('ГОСТ 12821-1980', 'garant12821.html'), ('ГОСТ 12820-1980', 'garant12820.html')):
    p = T(); p.feed(open(fn, encoding='cp1251', errors='replace').read())
    tabs = [t for t in p.tables if any('Проход' in c[2] for r in t[:2] for c in r)]
    res = {}
    for k, t in enumerate(tabs):
        g = grid(t)
        hdr = next(r for r in g if 'b' in r)
        bi = hdr.index('b')
        # колонки массы: первая после D_n (12821) или после b (12820)
        for r in g:
            if not r or not re.fullmatch(r'\(?\d{2,4}\)?', r[0]): continue
            dn = int(re.sub(r'\D', '', r[0]))
            for pn in PN[name][k] if k < len(PN[name]) else []:
                res[f'{dn}|{pn:g}'] = {'b': r[bi] if bi < len(r) else None, 'row': r[:9]}
    out[name] = res
    print(name, 'таблиц', len(tabs), 'записей', len(res), '| DN100:', {k: v['b'] for k, v in res.items() if k.startswith('100|')})
json.dump(out, open('garant_table.json', 'w', encoding='utf-8'), ensure_ascii=False, indent=0)
