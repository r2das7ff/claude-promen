"""Сводка присоединительных размеров фланцев по таблицам → план правки карточек."""
import json, re, sys, collections
sys.stdout.reconfigure(encoding='utf-8')
F = json.load(open('flanges.json', encoding='utf-8'))
T = json.load(open('g33259_table.json', encoding='utf-8'))
G = json.load(open('garant_table.json', encoding='utf-8'))
S = json.load(open('g1282x_table.json', encoding='utf-8'))['ГОСТ 12820-1980']  # скан: «|16» — это таблица 5, PN 25

def num(v):
    m = re.match(r'\d+(?:[.,]\d+)?', str(v or ''))
    return m.group(0).replace(',', '.') if m else None

def conn(typ, dn, pn):
    for row in (1, 2):
        r = T.get(f'{typ}|{dn}|{pn:g}|{row}')
        if r and all(num(r[k]) for k in ('D', 'D1', 'n', 'd')) and r['M']:
            return r, row
    return None, None

plan, skipped, stats = {}, [], collections.Counter()
for x in F:
    d = x['dims']; dn = int(float(d['dn'])); pn = float(d['pn']); norm = x['norm']
    typ = {'gost-33259-2015': d.get('flange_type'), 'gost-12821-1980': '11', 'gost-12820-1980': '01'}[norm]
    r, row = conn(typ, dn, pn)
    if not r:
        skipped.append((x['id'], norm, dn, pn, 'нет присоединительных размеров')); continue
    b = num(r['b']) if norm == 'gost-33259-2015' else None
    if norm == 'gost-12821-1980':
        b = num((G['ГОСТ 12821-1980'].get(f'{dn}|{pn:g}') or {}).get('b'))
    if norm == 'gost-12820-1980':
        if pn == 25:
            s = S.get(f'{dn}|16')
            b = num(s['b']) if s and s['b'] and ',' not in s['b'] else None
            if dn == 15: b = '14'  # в скане на месте b масса 0,70; «Гарант»: 14, как у DN 10
            # карточка с данными другого DN (масса строки DN 350 у DN 32) — не трогаем
            if s and num(s['mass']) and x['w'] and abs(float(num(s['mass'])) - float(x['w'])) > 0.05 * float(num(s['mass'])) + 0.05:
                skipped.append((x['id'], norm, dn, pn, f"масса карточки {x['w']} ≠ таблицы {s['mass']} — данные другого DN")); continue
        else:
            b = num((G['ГОСТ 12820-1980'].get(f'{dn}|{pn:g}') or {}).get('b'))
    new = {'outer_diameter': num(r['D']), 'bolt_circle_d': num(r['D1']), 'stud_count': num(r['n']),
           'bolt_d': str(r['M']), 'bolt_hole_d': num(r['d'])}
    if b:
        new['flange_thickness'] = b
    else:
        stats['без толщины'] += 1
    changed = {k: (d.get(k), v) for k, v in new.items() if str(d.get(k) or '') != str(v)}
    plan[x['id']] = {'set': new, 'row': row, 'norm': norm, 'dn': dn, 'pn': pn, 'type': typ, 'changed': changed}
    stats[f'{norm} ряд {row}'] += 1
    stats[f'{norm} меняется' if changed else f'{norm} без изменений'] += 1

json.dump(plan, open('flange_plan.json', 'w', encoding='utf-8'), ensure_ascii=False)
print(dict(stats))
print('пропущено:', len(skipped))
for s in skipped: print('  ', s)
for pid in list(plan)[:3] + [p for p, v in plan.items() if v['dn'] == 100 and v['pn'] == 16][:3]:
    v = plan[pid]; print(pid, v['norm'], v['type'], v['dn'], v['pn'], 'ряд', v['row'], v['changed'])
