#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Данные для текста карточек крепежа → wp-content/themes/promen/inc/data/fastener-facts.php

- названия стандартов крепежа — из реестра normatives_master.csv (сверены по
  титульным листам);
- где применяется резьба во фланцевых соединениях — из таблиц 3 и 6
  ГОСТ 33259-2015, ряд 1 (scripts/flanges-0930/g33259_table.json): резьба M
  → { PN: [DN, …] }.
"""
import csv, io, json, os, re, sys

sys.stdout.reconfigure(encoding='utf-8')
BASE = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
REG = os.path.join(os.path.dirname(BASE), 'normatives', 'registry', 'normatives_master.csv')
TAB = os.path.join(BASE, 'scripts', 'flanges-0930', 'g33259_table.json')
OUT = os.path.join(BASE, 'wp-content', 'themes', 'promen', 'inc', 'data', 'fastener-facts.php')

FASTENER_NORMS = ['ГОСТ 7798', 'ГОСТ 7805', 'ГОСТ 7795', 'ГОСТ 7796', 'ГОСТ 7808', 'ГОСТ 10602', 'ГОСТ 15590', 'ГОСТ 15591',
                  'ГОСТ 5915', 'ГОСТ 5916', 'ГОСТ 5927', 'ГОСТ 5929', 'ГОСТ 10605', 'ГОСТ 10607', 'ГОСТ 9064', 'ГОСТ 9066',
                  'ГОСТ 22032', 'ГОСТ 22043', 'ГОСТ 10494', 'ГОСТ 11371', 'ГОСТ 6402', 'ГОСТ 11738', 'ОСТ 26-2040']
titles = {}
for r in csv.DictReader(io.open(REG, encoding='utf-8-sig')):
    code = r['full_designation'].strip()
    for n in FASTENER_NORMS:
        if code.startswith(n + '-') or code.startswith(n.replace('ОСТ 26-2040', 'ОСТ 26-2040')):
            t = re.sub(r'\.\s*(Конструкция и размеры|Технические условия|Основные размеры|Типы и основные размеры)\.?$', '', r['title'].strip())
            titles[n] = t
# ГОСТ 11738-84 в реестре нет — название по титулу стандарта.
titles.setdefault('ГОСТ 11738', 'Винты с цилиндрической головкой и шестигранным углублением под ключ класса точности А')
T = json.load(open(TAB, encoding='utf-8'))
usage = {}
for k, v in T.items():
    typ, dn, pn, row = k.split('|')
    if typ != '11' or row != '1' or not v.get('M'):
        continue
    usage.setdefault(int(v['M']), {}).setdefault(pn, set()).add(int(dn))

def php(v, ind=1):
    t = '\t' * ind
    if isinstance(v, dict):
        return '[\n' + ''.join(f"{t}{php(k) if isinstance(k, str) else k} => {php(x, ind + 1)},\n" for k, x in v.items()) + '\t' * (ind - 1) + ']'
    if isinstance(v, (list, tuple)):
        return '[ ' + ', '.join(php(x) for x in v) + ' ]'
    if isinstance(v, str):
        return "'" + v.replace('\\', '\\\\').replace("'", "\\'") + "'"
    return str(v)

use = {m: {pn: sorted(d) for pn, d in sorted(u.items(), key=lambda t: float(t[0]))} for m, u in sorted(usage.items())}
os.makedirs(os.path.dirname(OUT), exist_ok=True)
body = ("<?php\n/**\n * Данные текста карточек крепежа. Сгенерировано scripts/seo/gen_fastener_facts.py —\n"
        " * руками не править.\n *\n * titles — названия стандартов по реестру нормативов;\n"
        " * flange_usage — резьба M → { PN: [DN] } по таблице 6 ГОСТ 33259-2015, ряд 1.\n */\n\n"
        "defined( 'ABSPATH' ) || exit;\n\nreturn [\n\t'titles' => " + php(titles, 2) + ",\n\t'flange_usage' => " + php(use, 2) + ",\n];\n")
io.open(OUT, 'w', encoding='utf-8', newline='\n').write(body)
print('стандартов', len(titles), '| резьб во фланцах', len(use))
for n in FASTENER_NORMS:
    print(' ', n, '—', titles.get(n, 'НЕТ В РЕЕСТРЕ'))
print('M16:', use.get(16))
