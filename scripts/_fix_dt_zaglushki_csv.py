# -*- coding: utf-8 -*-
"""
Тот же разбор мусорного батча «ДТ», что и _fix_dt_zaglushki.php, но в источнике —
products_master.csv / products_variable.csv. Иначе при следующем импорте всё
вернётся.

Что делает:
  1. Убирает 13 строк-дублей «ДТ» по ОСТ 24.125.22-89 / 24.125.23-89.
  2. Две строки 33х2 (единственный типоразмер, которого нет в правильном наборе)
     переводит в заглушки: product_type ЗЭ, семейство «Заглушки», нормальный title.
  3. Чистит склейку «диаметр+стенка» в колонке pn у comp-строк (3776 = 377х6,
     27311 = 273х11). Правило: pn > 100 МПа у comp-строки — мусор.
     ГОСТ 33259-2015 не трогаем: там pn — номинал в кгс/см² (PN160 → 16 МПа),
     канон делит на 10 сам (promen_pn_nominal_norms).

Правки построчные: нетронутые строки уходят в файл байт в байт.

Запуск: python scripts/_fix_dt_zaglushki_csv.py [--dry]
"""
import csv
import io
import os
import sys

csv.field_size_limit(10 ** 7)

BASE = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'products-csv')
FILES = ['products_master.csv', 'products_variable.csv']

DROP = {
    'comp-ост-24-125-22-89-01--20x1-5',
    'comp-ост-24-125-22-89-02--20x1-5',
    'comp-ост-24-125-22-89-03--22x1-5',
    'comp-ост-24-125-22-89-04--22x1-5',
    'comp-ост-24-125-22-89-05--27x2',
    'comp-ост-24-125-22-89-06--27x2',
    'comp-ост-24-125-22-89-07--27x1-5',
    'comp-ост-24-125-22-89-08--27x1-5',
    'comp-ост-24-125-22-89-09--33x2',
    'comp-ост-24-125-23-89-01--20x1-5',
    'comp-ост-24-125-23-89-02--22x1-5',
    'comp-ост-24-125-23-89-03--27x1-5',
    'comp-ост-24-125-23-89-04--27x2',
}

KEEP = {
    'comp-ост-24-125-22-89-10--33x2': {
        'title': 'Заглушка 33х2 ОСТ 24.125.22-1989',
        'execution': '10',
        'designation': '33х2',
    },
    'comp-ост-24-125-23-89-05--33x2': {
        'title': 'Заглушка 33х2 ОСТ 24.125.23-1989',
        'execution': '5',
        'designation': '33х2',
    },
}


def parse_line(line):
    return next(csv.reader(io.StringIO(line)))


def build_line(fields):
    out = io.StringIO(newline='')
    csv.writer(out, lineterminator='\n').writerow(fields)
    return out.getvalue()


def fix_attributes(value, execution):
    if not value:
        return value
    parts = []
    for chunk in value.split('; '):
        if chunk.startswith('execution='):
            chunk = 'execution=' + execution
        elif chunk.startswith('pn='):
            continue
        parts.append(chunk)
    return '; '.join(parts)


def process(path, dry):
    with open(path, encoding='utf-8-sig', newline='') as fh:
        raw = fh.read()
    bom = open(path, 'rb').read(3) == b'\xef\xbb\xbf'
    lines = raw.splitlines(keepends=True)
    header = parse_line(lines[0])
    idx = {name: i for i, name in enumerate(header)}
    n = len(header)

    out = [lines[0]]
    dropped = renamed = pn_cleared = 0

    for line in lines[1:]:
        if not line.strip():
            out.append(line)
            continue
        fields = parse_line(line)
        if len(fields) != n:
            out.append(line)
            continue
        sku = fields[idx['sku']]

        if sku in DROP:
            dropped += 1
            continue

        changed = False

        if sku in KEEP:
            spec = KEEP[sku]
            fields[idx['product_type']] = 'ЗЭ'
            fields[idx['product_family']] = 'Заглушки'
            fields[idx['category']] = 'заглушки'
            fields[idx['title']] = spec['title']
            fields[idx['execution']] = spec['execution']
            if 'gost_designation' in idx:
                fields[idx['gost_designation']] = spec['designation']
            if 'attributes' in idx:
                fields[idx['attributes']] = fix_attributes(
                    fields[idx['attributes']], spec['execution']
                )
            if 'description' in idx:
                fields[idx['description']] = fields[idx['description']].replace(
                    'Исполнение 0' + spec['execution'] + '.',
                    'Исполнение ' + spec['execution'] + '.',
                )
            renamed += 1
            changed = True

        if sku.startswith('comp-') and 'pn' in idx:
            pn = fields[idx['pn']]
            try:
                bad = pn != '' and float(pn) > 100
            except ValueError:
                bad = False
            if bad:
                fields[idx['pn']] = ''
                if 'attributes' in idx:
                    fields[idx['attributes']] = '; '.join(
                        c for c in fields[idx['attributes']].split('; ')
                        if not c.startswith('pn=')
                    )
                pn_cleared += 1
                changed = True

        out.append(build_line(fields) if changed else line)

    print('%-26s снято %2d, переименовано %d, pn вычищено %d'
          % (os.path.basename(path), dropped, renamed, pn_cleared))

    if dry:
        return
    text = ''.join(out)
    with open(path, 'w', encoding='utf-8-sig' if bom else 'utf-8', newline='') as fh:
        fh.write(text)


if __name__ == '__main__':
    dry = '--dry' in sys.argv
    for name in FILES:
        process(os.path.join(BASE, name), dry)
    print('dry-run, файлы не тронуты' if dry else 'готово')
