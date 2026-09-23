# -*- coding: utf-8 -*-
"""Собрать карту 301 для mu-plugins/promen-catalog-moves-map.php.

Источники: scripts/otk-fix/moves.tsv (правки ОТК 10.09.2026) и
scripts/krepezh-fix/moves.tsv (шпильки ГОСТ 22032/22043, 23.09.2026).
Блок gone из прежней карты сохраняется как есть.
"""
import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
MAP = os.path.join(ROOT, 'wp-content', 'mu-plugins', 'promen-catalog-moves-map.php')

HEADER = """<?php
/**
 * Карта переездов каталога. Генерируется scripts/otk-fix/ и
 * scripts/krepezh-fix/build_map.py. Руками не править.
 *
 *   moved — товар сменил адрес: род изделия в слаге не совпадал с нормативом
 *           (бобышка и пробка лежали под zaglushka-, донышко под dnische-;
 *           шпильки ГОСТ 22032/22043 — под bolty/bolt-).
 *   gone  — типоразмера нет в нормативе, товар удалён; отдаём 410, чтобы
 *           поисковик выбросил адрес сразу, а не ждал повторных обходов.
 */
return [
'moved' => [
"""


def read_pairs(path):
    pairs = []
    if not os.path.exists(path):
        return pairs
    for line in io.open(path, encoding='utf-8'):
        line = line.rstrip('\n')
        if not line.strip():
            continue
        parts = line.split('\t')
        if len(parts) == 2 and parts[0] != parts[1]:
            pairs.append((parts[0], parts[1]))
    return pairs


def existing_block(src, name):
    """Вытащить готовый блок (moved/gone) из прежней карты."""
    m = re.search(r"'" + name + r"' => \[(.*?)\n\],", src, re.S)
    return m.group(1) if m else ''


old_src = io.open(MAP, encoding='utf-8').read() if os.path.exists(MAP) else ''
old_moved = existing_block(old_src, 'moved')
gone = existing_block(old_src, 'gone')

pairs = {}
for line in old_moved.split('\n'):
    m = re.match(r"\s*'([^']+)' => '([^']+)',", line)
    if m:
        pairs[m.group(1)] = m.group(2)

added = 0
for src_file in ['otk-fix/moves.tsv', 'krepezh-fix/moves.tsv']:
    for a, b in read_pairs(os.path.join(ROOT, 'scripts', src_file)):
        if a not in pairs:
            added += 1
        pairs[a] = b

out = HEADER
for a in sorted(pairs):
    out += "\t'%s' => '%s',\n" % (a, pairs[a])
out += "],\n'gone' => [" + gone + "\n],\n];\n"

io.open(MAP, 'w', encoding='utf-8', newline='').write(out)
print('Карта: %d переездов (добавлено %d), gone-блок сохранён' % (len(pairs), added))
