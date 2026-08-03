# -*- coding: utf-8 -*-
"""Валидация эталона _gost33259_ref.json перед ремонтом каталога.

Проверки:
  1. пара «резьба М ↔ отверстие d» из стандартного соответствия;
  2. геометрия: шаг болтов π·D2/n ≥ 2.2·M; D2 + d + 2 ≤ D; b в 6..130;
  3. согласованность типов: соединительные размеры (D, D2, d, n, M) типов 01 и 11
     обязаны совпадать для одного (DN, PN, ряд);
  4. монотонность D2 по DN при фиксированном (тип, PN, ряд).

Выход: _gost33259_issues.json — список проблем с координатами (страница),
для зум-арбитража. Запуск: python scripts/_gost33259_validate.py
"""
import json, math, pathlib, sys

HOLE = {10: 11, 12: 14, 16: 18, 20: 22, 24: 26, 27: 30, 30: 33, 33: 36,
        36: 39, 39: 42, 42: 45, 45: 48, 48: 52, 52: 56, 56: 62, 60: 66, 64: 70}

def main():
    here = pathlib.Path(__file__).parent
    ref = json.load(open(here / '_gost33259_ref.json', encoding='utf-8'))
    issues = []

    def add(key, ryad, kind, detail):
        issues.append({'key': key, 'ryad': ryad, 'kind': kind, 'detail': detail,
                       'page': ref[key]['page'] if key in ref else None})

    for key, rec in ref.items():
        for ryad in ('r1', 'r2'):
            row = rec.get(ryad)
            if not row:
                continue
            D, D2, d, n, M, b = (row.get(x) for x in ('D', 'D2', 'd', 'n', 'M', 'b'))
            if M and d and HOLE.get(int(M)) != int(d):
                add(key, ryad, 'hole_thread', f'M{int(M)} с отверстием {d} (ожид. {HOLE.get(int(M))})')
            if D2 and n and M and (math.pi * D2 / n) < 2.2 * M:
                add(key, ryad, 'pitch', f'π·{D2}/{n} < 2.2·{M}')
            if D and D2 and d and D2 + d + 2 > D:
                add(key, ryad, 'rim', f'D2={D2}+d={d} не помещается в D={D}')
            if b is not None and not (6 <= b <= 130):
                add(key, ryad, 'b_range', f'b={b}')
            if n is not None and (n < 4 or n > 96 or int(n) % 2):
                add(key, ryad, 'n_odd', f'n={n}')

    # согласованность 01 ↔ 11
    for key, rec in ref.items():
        typ, dn, pn = key.split('|')
        if typ != '01':
            continue
        other = ref.get(f'11|{dn}|{pn}')
        if not other:
            continue
        for ryad in ('r1', 'r2'):
            a, b_ = rec.get(ryad), other.get(ryad)
            if not a or not b_:
                continue
            for f in ('D', 'D2', 'd', 'n', 'M'):
                va, vb = a.get(f), b_.get(f)
                if va is not None and vb is not None and va != vb:
                    add(key, ryad, 'type_mismatch', f'{f}: 01={va} vs 11={vb}')

    # монотонность D2 по DN
    from collections import defaultdict
    series = defaultdict(list)
    for key, rec in ref.items():
        typ, dn, pn = key.split('|')
        for ryad in ('r1', 'r2'):
            v = rec.get(ryad, {}).get('D2')
            if v:
                series[(typ, pn, ryad)].append((float(dn), v, key))
    for (typ, pn, ryad), pts in series.items():
        pts.sort()
        for (dn1, v1, k1), (dn2, v2, k2) in zip(pts, pts[1:]):
            if v2 < v1:
                add(k2, ryad, 'monotonic', f'D2 {v2} < {v1} (DN{dn1:g}->{dn2:g}, PN{pn})')

    out = here / '_gost33259_issues.json'
    out.write_text(json.dumps(issues, ensure_ascii=False, indent=1), encoding='utf-8')
    from collections import Counter
    print('проблем:', len(issues), Counter(i['kind'] for i in issues))
    keys = sorted({i['key'] for i in issues})
    print('записей с проблемами:', len(keys))

if __name__ == '__main__':
    main()
