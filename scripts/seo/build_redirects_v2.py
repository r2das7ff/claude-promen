#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Точные 301 со старого сайта — второй проход (30.09.2026).

Первая карта (build_redirects.py, 01.09) довела до конкретного товара только
1 427 из 18 953 адресов, остальные ушли в раздел, на норматив или в 404.
Причины: фланцы старого сайта описаны через Ру в кгс/см² («ru-10»), а канон
хранит МПа (1,0); отводы старого сайта без угла; мусорные хвосты адресов
(«/data-src=…», «/feed/») не попадали в карту вовсе.

Здесь: свежий канон с прода (work-0930/canon.json — после правок размеров
фланцев и тройников 30.09), разбор слага по типу изделия, и правило
«улучшать можно, ухудшать нельзя»: адрес, который уже ведёт на товар, не
трогаем; раздел/норматив/404 заменяем товаром только при однозначной паре.

Выход: migration/redirects.csv (перезапись, how = «товар-2» у новых) и
wp-content/mu-plugins/promen-redirects-map.php.
"""
import csv, io, json, os, re, sys, collections

sys.stdout.reconfigure(encoding='utf-8')
BASE = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
MIG = os.path.join(BASE, 'migration')
WORK = os.path.join(os.path.dirname(BASE), 'work-0930')
AUD = os.path.join(os.path.dirname(BASE), 'prom-en.com-audit', 'data')

canon = json.load(open(os.path.join(WORK, 'canon.json'), encoding='utf-8'))


def fnum(v):
    try:
        return round(float(str(v).replace(',', '.')), 2)
    except (TypeError, ValueError):
        return None


def norm_key(slug):
    """gost-12820-1980 / gost-12820-80 → gost-12820; ost-34-10-753-97 → ost-34-10-753."""
    s = slug.lower().replace('.', '-')
    s = re.sub(r'-(\d{2}|\d{4})$', '', s)
    return s


# ── индексы канона ───────────────────────────────────────────────────────────
by_norm = collections.defaultdict(list)
for x in canon:
    if not x['url']:
        continue
    by_norm[norm_key(x['norm'])].append(x)

# ── вселенная старых адресов ─────────────────────────────────────────────────
old_map = {r['old_path']: (r['new_url'], r['how']) for r in csv.DictReader(io.open(os.path.join(MIG, 'redirects.csv'), encoding='utf-8'))}
extra = set()
for fn in ('ya-insearch-samples.json', 'ya-events-all.json'):
    p = os.path.join(AUD, fn)
    if os.path.exists(p):
        for x in json.load(open(p, encoding='utf-8')):
            path = re.sub(r'^https?://[^/]+', '', x['url'])
            if path.startswith('/products/'):
                extra.add(path)


def base_path(p):
    """Мусорный хвост после слага товара отрезаем: /products/<слаг>/data-src=…/ → /products/<слаг>/."""
    m = re.match(r'^(/products/[^/]+/)', p if p.endswith('/') else p + '/')
    return m.group(1) if m else None


universe = set(k for k in old_map if k.startswith('/products/')) | set(filter(None, (base_path(p) for p in extra)))

# ── разбор слага ─────────────────────────────────────────────────────────────
NORM_RE = re.compile(r'((?:gost|ost|sto|tu|atk)-[0-9a-z-]+?-(?:\d{2}|\d{4}))(?:-ispolnenie|-|$)')
SIZE_RE = re.compile(r'(?<!\d)(\d{1,4}(?:-\d)?)x(\d{1,4}(?:-\d)?)(?:x(\d{1,3}(?:-\d)?))?(?=-|$)')


def sizes(slug):
    out = []
    for a, b, c in SIZE_RE.findall(slug):
        f = lambda s: float(s.replace('-', '.'))
        out.append((f(a), f(b), f(c) if c else None))
    return out


def find_norm(slug):
    """Самое длинное обозначение из слага, которое есть в каноне: ost-34-10-753-97 → ost-34-10-753."""
    t = slug.split('-')
    best = None
    for i, w in enumerate(t):
        if w not in ('gost', 'ost', 'sto'):
            continue
        for j in range(i + 2, min(len(t), i + 8) + 1):
            k = norm_key('-'.join(t[i:j]))
            if k in by_norm and (best is None or len(k) > len(best)):
                best = k
    return best


def eq(a, b):
    return a is not None and b is not None and abs(float(a) - float(b)) < 0.051


def pick(cands):
    urls = sorted({c['url'] for c in cands})
    return urls[0] if len(urls) == 1 else None


def match(path):
    slug = path.split('/')[2]
    kind = slug.split('-')[0]
    nk = find_norm(slug)
    # фланцы: Ду/Ру (кгс/см²)
    m = re.search(r'du-(\d+)-ru-(\d+(?:-\d)?)', slug)
    if kind == 'flanec' and m and nk:
        dn, pn = float(m.group(1)), float(m.group(2).replace('-', '.'))
        def fl(key, ftype=None):
            return [c for c in by_norm.get(key, []) if c['cat'].startswith('flancy') and eq(c['dn'], dn)
                    and eq(c['pn_raw'], pn) and (ftype is None or str(c['ftype']) == ftype)]
        c = fl(nk)
        if not c:  # ГОСТ 12820/12821 заменены ГОСТ 33259: тип 01 / 11, ряд 1 (прил. Г)
            alt = {'gost-12820': '01', 'gost-12821': '11'}.get(nk)
            if alt:
                c = fl('gost-33259', alt)
        u = pick(c)
        return (u, 'фланец Ду/Ру') if u else None
    if not nk:
        return None
    sz = sizes(slug)
    cands = by_norm[nk]
    if kind in ('dnishhe', 'truba', 'otvod', 'otvody') and sz:
        d, s, _ = sz[0]
        c = [x for x in cands if eq(x['od'] or x['d'], d) and eq(x['wt'] or x['s'], s)]
        if kind.startswith('otvod'):
            c = [x for x in c if x['cat'] == 'otvody']
            ang = re.search(r'-(45|60|90|180)(?:-gradus|-)', slug)
            want = float(ang.group(1)) if ang else 90.0
            ca = [x for x in c if eq(x['angle'], want)]
            c = ca or c
        u = pick(c)
        return (u, kind) if u else None
    if kind.startswith(('perexod', 'trojnik')) and sz:
        cat = 'perekhody' if kind.startswith('perexod') else 'troyniki'
        c = [x for x in cands if x['cat'] == cat]
        if len(sz) >= 2:
            (d1, s1, _), (d2, s2, _) = sz[0], sz[1]
            c = [x for x in c if eq(x['od'] or x['d'], d1) and eq(x['wt'] or x['s'], s1) and eq(x['d2'], d2) and eq(x['s2'], s2)]
        elif sz[0][2] is not None:  # 1420x1020x14 — D1×D2×S
            d1, d2, s = sz[0]
            c = [x for x in c if eq(x['od'] or x['d'], d1) and eq(x['d2'], d2) and eq(x['wt'] or x['s'], s)]
        else:
            return None
        if kind.startswith('perexod'):
            word = 'эксцентр' if 'ekscentr' in slug else ('концентр' if 'koncentr' in slug else None)
            if word:
                # вид перехода указан — другой вид не подставляем: это другое изделие
                c = [x for x in c if word in x['title'].lower() or word in x['url'].replace('koncentr', 'концентр').replace('ekscentr', 'эксцентр')]
        u = pick(c)
        return (u, kind) if u else None
    return None


# Товару пары нет — раздел по типу изделия, как у 12 тыс. адресов первой карты.
SECTION = {
    'truba': '/catalog/truby/', 'truby': '/catalog/truby/',
    'zadvizhka': '/catalog/armatura/', 'zadvizhki': '/catalog/armatura/', 'kran': '/catalog/armatura/', 'krany': '/catalog/armatura/',
    'klapan': '/catalog/armatura/', 'klapany': '/catalog/armatura/', 'zatvor': '/catalog/armatura/', 'zatvory': '/catalog/armatura/',
    'filtr': '/catalog/armatura/', 'filtry': '/catalog/armatura/', 'diskovye': '/catalog/armatura/', 'elektroprivod': '/catalog/armatura/',
    'teleskopicheskij': '/catalog/armatura/', 'shpindel': '/catalog/armatura/', 'udlinitel': '/catalog/armatura/', 'gryazevik': '/catalog/armatura/',
    'opory': '/catalog/opory/', 'opora': '/catalog/opory/',
    'flanec': '/catalog/flancy/', 'flancy': '/catalog/flancy/',
    'perexod': '/catalog/sdt/perekhody/', 'perexody': '/catalog/sdt/perekhody/',
    'trojnik': '/catalog/sdt/troyniki/', 'trojniki': '/catalog/sdt/troyniki/',
    'otvod': '/catalog/sdt/otvody/', 'otvody': '/catalog/sdt/otvody/',
    'zaglushka': '/catalog/sdt/zaglushki/', 'zaglushki': '/catalog/sdt/zaglushki/',
    'dnishhe': '/catalog/sdt/dnishcha/', 'dnishha': '/catalog/sdt/dnishcha/',
    'shtucer': '/catalog/sdt/', 'ugolnik': '/catalog/sdt/',
}
miss404 = collections.Counter()
stats = collections.Counter()
new_map = dict((k, v[0]) for k, v in old_map.items())
how = dict((k, v[1]) for k, v in old_map.items())
upgraded = []
for p in sorted(universe):
    cur = old_map.get(p)
    if cur and cur[1] == 'товар':
        stats['уже на товар'] += 1
        continue
    r = match(p)
    if r:
        new_map[p] = r[0]; how[p] = 'товар-2'
        stats['→ товар (' + ('было ' + cur[1] if cur else 'было 404') + ')'] += 1
        upgraded.append((p, r[0], r[1]))
    elif not cur:
        sec = SECTION.get(p.split('/')[2].split('-')[0])
        if sec:
            new_map[p] = 'https://prom-en.com' + sec; how[p] = 'раздел-2'
            stats['→ раздел (было 404)'] += 1
        else:
            stats['404 без пары'] += 1
            miss404[p.split('/')[2].split('-')[0]] += 1
    else:
        stats['без изменений: ' + cur[1]] += 1

ins = set(filter(None, (base_path(re.sub(r'^https?://[^/]+', '', x['url'])) for x in json.load(open(os.path.join(AUD, 'ya-insearch-samples.json'), encoding='utf-8')) if '/products/' in x['url'])))
cov = collections.Counter(('товар' if how.get(p) in ('товар', 'товар-2') else (how.get(p) or '404')) for p in ins)
print('старые /products/ в поиске (по слагу):', len(ins), dict(cov))
for k, v in sorted(stats.items(), key=lambda t: -t[1]):
    print(f'{v:6}  {k}')
print('\nпримеры:')
for u in upgraded[:: max(1, len(upgraded) // 15)][:15]:
    print('  ', u[2], '|', u[0], '→', u[1].replace('https://prom-en.com', ''))

if '--write' in sys.argv:
    with io.open(os.path.join(MIG, 'redirects.csv'), 'w', encoding='utf-8', newline='') as f:
        w = csv.writer(f); w.writerow(['old_path', 'new_url', 'how'])
        for k in sorted(new_map):
            w.writerow([k, new_map[k], how[k]])
    esc = lambda s: s.replace('\\', '\\\\').replace("'", "\\'")
    body = ''.join(f"\t'{esc(k)}' => '{esc(new_map[k])}',\n" for k in sorted(new_map))
    n_prod = sum(1 for h in how.values() if h in ('товар', 'товар-2'))
    php = ("<?php\n/**\n * Карта 301: старый адрес => новый. Сгенерировано scripts/seo/build_redirects.py\n"
           f" * и уточнено build_redirects_v2.py (30.09.2026): {len(new_map)} адресов, из них на товар — {n_prod}.\n */\nreturn [\n" + body + "];\n")
    io.open(os.path.join(BASE, 'wp-content', 'mu-plugins', 'promen-redirects-map.php'), 'w', encoding='utf-8', newline='\n').write(php)
    print('\nзаписано:', len(new_map), 'адресов, на товар', n_prod)
