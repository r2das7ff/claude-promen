# -*- coding: utf-8 -*-
"""Готовит repairs.tsv — построчный план ремонта карточек по замечаниям ОТК.

Второй типоразмер уцелел в слаге, но записан то наружным диаметром, то условным
проходом. Сводим через сортамент Ду↔Dн и только потом решаем, переходное изделие
или действительно равнопроходное.

    python build_repairs.py products.tsv plan.tsv repairs.tsv
"""
import csv
import io
import re
import sys

PROD, PLAN, OUT = sys.argv[1], sys.argv[2], sys.argv[3]

DU = [6, 10, 15, 20, 25, 32, 40, 50, 65, 80, 100, 125, 150, 200, 250, 300, 350,
      400, 500, 600, 700, 800, 900, 1000, 1200, 1400, 1600]
DN = [10, 14, 18, 25, 32, 38, 45, 57, 76, 89, 108, 133, 159, 219, 273, 325, 377,
      426, 530, 630, 720, 820, 920, 1020, 1220, 1420, 1620]
DU2DN = dict(zip(DU, DN))
DN_SET = set(DN)


def to_dn(v):
    """Приводит число из слага к наружному диаметру."""
    try:
        f = float(v.replace('-', '.'))
    except ValueError:
        return None
    i = int(round(f))
    if i in DN_SET:
        return f
    if i in DU2DN:
        return float(DU2DN[i])
    return f  # нестандартное — оставляем как есть


SLUG = re.compile(r'(\d+(?:-\d+)?)h(\d+(?:-\d+)?)-(\d+(?:-\d+)?)h(\d+(?:-\d+)?)')


def num(v):
    return v.replace('-', '.')


plan = {}
for r in csv.DictReader(io.open(PLAN, encoding='utf-8'), delimiter='\t'):
    plan[r['id']] = r

out = io.open(OUT, 'w', encoding='utf-8', newline='')
w = csv.writer(out, delimiter='\t')
w.writerow(['id', 'slug', 'action', 'D1', 's1', 'D2', 's2', 'заметка'])

stat = {}
for r in plan.values():
    if r['action'] != 'delete':
        continue
    m = SLUG.search(r['slug'])
    if not m and 'стенка' in r['причина']:
        w.writerow([r['id'], r['slug'], 'clearwall', r['D'], '', '', '', r['причина']])
        stat['clearwall'] = stat.get('clearwall', 0) + 1
        continue
    if not m:
        w.writerow([r['id'], r['slug'], 'hold', r['D'], r['s'], '', '', 'из слага не восстановить'])
        stat['hold'] = stat.get('hold', 0) + 1
        continue
    a, sa, b, sb = m.groups()
    dn1, dn2 = to_dn(a), to_dn(b)
    if dn1 is None or dn2 is None:
        w.writerow([r['id'], r['slug'], 'hold', r['D'], r['s'], '', '', 'слаг не разобран'])
        stat['hold'] = stat.get('hold', 0) + 1
        continue
    if abs(dn1 - dn2) < 0.01:
        w.writerow([r['id'], r['slug'], 'hold', r['D'], r['s'], '', '',
                    f'после сведения Ду→Dн концы равны ({dn1:g})'])
        stat['hold'] = stat.get('hold', 0) + 1
        continue
    fmt = lambda x: f'{x:g}'
    w.writerow([r['id'], r['slug'], 'restore', fmt(dn1), num(sa), fmt(dn2), num(sb),
                f'второй конец восстановлен из слага ({b}→{fmt(dn2)})'])
    stat['restore'] = stat.get('restore', 0) + 1
out.close()

print(OUT)
for k in sorted(stat, key=lambda x: -stat[x]):
    print(f'  {k:<10} {stat[k]}')
