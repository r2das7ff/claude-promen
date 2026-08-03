# -*- coding: utf-8 -*-
"""Сборка ремонтного датасета фланцев из эталонов:
  - _gost33259_ref_clean.json (чистая копия ГОСТ 33259-2015, Т3/Т6);
  - _gost1282x_ref.json (b и масса из ГОСТ 12820-80 / 12821-80).

Соединительные размеры (D, D2, d, n, M) в 33259 общие для всех типов —
объединяем тип 01 и тип 11 по (DN, PN); расхождения логируются (после чистой
копии их не ожидается), приоритет — более полному набору.

Точечные правки (зум-арбитраж печати стандарта):
  - 11|80|63: резьба М20, не М16 (пара к отверстию 22; мердж-граница «М16»
    в вёрстке накрыла чужую строку; у DN100 PN63 явно М20).

Выход: _flange_repair.json
Запуск: python scripts/_flange_repair_build.py
"""
import json, pathlib

OVERRIDES_M = {('11', '80', '63'): 20}
# 250|10 r2 D: Т3 даёт 390, Т6 — 395; ряд 2 EN-совместимый (EN 1092 DN250
# PN10: D=395), на чистом рендере Т6 значение 395 читается однозначно.
OVERRIDES_CONN = {('250|10', 'r2', 'D'): 395.0}
# Опечатка вёрстки ГОСТ 12820 (Ру2,5 МПа, Ду15): в колонке b напечатана масса
# «0,70»; по стандарту b=14 (как у Ду10; масса 0,71 согласуется).
OVERRIDES_B12820 = {'15|25': 14.0}

def main():
    here = pathlib.Path(__file__).parent
    g33 = json.load(open(here / '_gost33259_ref_clean.json', encoding='utf-8'))
    g12 = json.load(open(here / '_gost1282x_ref.json', encoding='utf-8'))

    conn, b33 = {}, {}
    conflicts = []
    for key, rec in g33.items():
        typ, dn, pn = key.split('|')
        if (typ, dn, pn) in OVERRIDES_M:
            for ry in ('r1', 'r2'):
                if rec.get(ry, {}).get('M') is not None:
                    rec[ry]['M'] = OVERRIDES_M[(typ, dn, pn)]
        ck = f'{dn}|{pn}'
        slot = conn.setdefault(ck, {})
        for ry in ('r1', 'r2'):
            row = rec.get(ry)
            if not row:
                continue
            got = {f: row[f] for f in ('D', 'D2', 'd', 'n', 'M') if row.get(f) is not None}
            if ry not in slot:
                slot[ry] = got
            else:
                for f, v in got.items():
                    if f in slot[ry] and slot[ry][f] != v:
                        conflicts.append(f'{ck} {ry} {f}: {slot[ry][f]} vs {v} (тип {typ})')
                    else:
                        slot[ry].setdefault(f, v)
            bv = row.get('b')
            if bv is not None:
                b33.setdefault(f'{typ}|{dn}|{pn}', {})[ry] = bv

    b20 = {k.split('|', 1)[1]: v['b'] for k, v in g12.items() if k.startswith('12820|')}
    b20.update(OVERRIDES_B12820)
    b21 = {k.split('|', 1)[1]: v['b'] for k, v in g12.items() if k.startswith('12821|')}

    for (ck, ry, f), v in OVERRIDES_CONN.items():
        conn.setdefault(ck, {}).setdefault(ry, {})[f] = v

    # Дозаполнение пропусков между рядами: мердж-ячейки вёрстки («4» на оба
    # ряда) не всегда покрывают обе полосы при извлечении. Копируем недостающее
    # поле из другого ряда ТОЛЬКО если все общие для рядов поля болтовой
    # картины (D2, d, M) совпадают — при разных М (напр. 600|16: М36/М33)
    # ничего не переносится.
    filled = 0
    for ck, slot in conn.items():
        r1, r2 = slot.get('r1'), slot.get('r2')
        if not r1 or not r2:
            continue
        shared = [f for f in ('D2', 'd', 'M') if f in r1 and f in r2]
        if not shared or any(r1[f] != r2[f] for f in shared):
            continue
        for f in ('d', 'n', 'M'):
            if f in r1 and f not in r2:
                r2[f] = r1[f]; filled += 1
            elif f in r2 and f not in r1:
                r1[f] = r2[f]; filled += 1
    print(f'дозаполнено полей между рядами: {filled}')

    out = {'conn': conn, 'b33259': b33, 'b12820': b20, 'b12821': b21}
    path = here / '_flange_repair.json'
    path.write_text(json.dumps(out, ensure_ascii=False, indent=1), encoding='utf-8')
    print(f'conn: {len(conn)}, b33259: {len(b33)}, b12820: {len(b20)}, b12821: {len(b21)} -> {path}')
    if conflicts:
        print('КОНФЛИКТЫ 01↔11 (приоритет первому полному):')
        for c in conflicts[:20]:
            print('  ', c)

if __name__ == '__main__':
    main()
