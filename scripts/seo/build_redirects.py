#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Карта 301 со старого сайта prom-en.com на новый.

Старый адрес несёт в слаге всё, что нужно для опознания изделия:
    /products/dnishhe-ellipticheskoe-otbortovannoe-133x8-gost-6533-78/
             тип                                   D×S    норматив
Второй формат — по условному проходу и давлению, им описаны фланцы и
заглушки по АТК:
    /products/zaglushka-du-10-ru-40-atk-24-200-02-90-ispolnenie-1/

Сопоставляем по паре «норматив + размеры», а не по названию: названия
переписаны, размеры и ГОСТ — нет. Год норматива приводим к общему виду,
старый сайт пишет его то двумя цифрами (gost-6533-78), то четырьмя.

Выход: migration/redirects.csv (old_path,new_path,how) и отчёт о непокрытых.
"""
import io, os, re, csv, sys, collections

BASE = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
MIG  = os.path.join(BASE, 'migration')


def norm_year(slug):
    """gost-6533-78 и gost-6533-1978 — один и тот же норматив."""
    m = re.match(r'^(.*?)-(\d{2}|\d{4})$', slug)
    if not m:
        return slug
    head, year = m.group(1), m.group(2)
    if len(year) == 4:
        return head + '-' + year[2:]
    return head + '-' + year


def num(x):
    """'21.3' -> 21.3; пустое и null -> None. Старый сайт пишет 21-3."""
    if x is None:
        return None
    x = str(x).strip().replace(',', '.')
    if x in ('', 'null', 'NULL'):
        return None
    try:
        return round(float(x), 3)
    except ValueError:
        return None


def load_new():
    """Канон нового сайта -> индексы для поиска."""
    by_ds   = collections.defaultdict(list)   # (норматив, D, S)     -> url
    by_dnpn = collections.defaultdict(list)   # (норматив, DN, PN)   -> url
    by_norm = collections.defaultdict(list)   # норматив             -> url
    real    = {}                              # нормализованный -> слаг термина
    cat_of  = {}                              # url -> категория
    path = os.path.join(MIG, 'new-catalog.tsv')
    with io.open(path, encoding='utf-8', errors='replace') as f:
        for line in f:
            p = line.rstrip('\n').split('\t')
            if len(p) < 11:
                continue
            ns, dn, dn2, pn, s, ang, d, d2, s2, cat, url = p[:11]
            real.setdefault(norm_year(ns), ns)   # в адресе страницы — исходный слаг
            ns = norm_year(ns)
            url = url.strip()
            if not url or url == 'null':
                continue
            cat_of[url] = cat
            by_norm[ns].append(url)
            D, S, DN, PN = num(d), num(s), num(dn), num(pn)
            if D is not None and S is not None:
                by_ds[(ns, D, S)].append(url)
            if DN is not None:
                by_dnpn[(ns, DN, PN)].append(url)
    return by_ds, by_dnpn, by_norm, real, cat_of


RE_DS   = re.compile(r'-(\d+(?:-\d+)?)x(\d+(?:-\d+)?)-')
RE_DUPU = re.compile(r'-du-(\d+)-ru-(\d+)-')


def old_dims(slug):
    """Из слага достаём (D,S) или (DN,PN) и остаток — норматив."""
    m = RE_DS.search(slug)
    if m:
        D = num(m.group(1).replace('-', '.'))
        S = num(m.group(2).replace('-', '.'))
        return ('ds', D, S, slug[m.end():])
    m = RE_DUPU.search(slug)
    if m:
        return ('dnpn', num(m.group(1)), num(m.group(2)), slug[m.end():])
    return (None, None, None, '')


def norm_from_tail(tail):
    """Хвост слага после размеров — обозначение норматива, иногда с исполнением."""
    tail = re.sub(r'-(ispolnenie|isp)-\d+$', '', tail)
    tail = re.sub(r'-(ispolnenie|isp)-\d+-', '-', tail)
    return norm_year(tail.strip('-'))


C = 'https://prom-en.com/catalog/'

# Старый слаг начинается с типа изделия — по нему и выбираем категорию-цель.
# Порядок важен: «trojnik» должен проверяться раньше «trub» не сталкивается,
# а вот «perexod» и «perehod» — две транслитерации одного слова.
TYPE_CAT = [
    ('otvod',     C + 'sdt/otvody/'),
    ('trojnik',   C + 'sdt/troyniki/'),
    ('perexod',   C + 'sdt/perekhody/'),
    ('perehod',   C + 'sdt/perekhody/'),
    ('zaglushka', C + 'sdt/zaglushki/'),
    ('dnishhe',   C + 'sdt/dnishcha/'),
    ('dnishche',  C + 'sdt/dnishcha/'),
    ('flanec',    C + 'flancy/'),
    ('flanc',     C + 'flancy/'),
    ('opor',      C + 'opory/'),
    ('zadvizhka', C + 'armatura/'),
    ('kran',      C + 'armatura/'),
    ('klapan',    C + 'armatura/'),
    ('bolt',      C + 'krepezh/bolty/'),
    ('gajk',      C + 'krepezh/gayki/'),
    ('shpilk',    C + 'krepezh/shpilki/'),
    ('shajb',     C + 'krepezh/shayby/'),
    ('vint',      C + 'krepezh/vinty/'),
    ('izol',      C + 'izolyatsiya/'),
    ('trub',      C + 'truby/'),
]


def category_for(slug):
    for pref, url in TYPE_CAT:
        if slug.startswith(pref):
            return url
    return rubric_target(slug)   # добор по вхождению (изоляция, арматура)



# Рубрики старого сайта и статика. У рубрик вес выше, чем у карточек:
# их всего 1 276, но именно они собирали категорийные запросы.
RUBRIC_CAT = [
    ('otvod',       C + 'sdt/otvody/'),
    ('trojnik',     C + 'sdt/troyniki/'),
    ('perexod',     C + 'sdt/perekhody/'),
    ('perehod',     C + 'sdt/perekhody/'),
    ('zaglushk',    C + 'sdt/zaglushki/'),
    ('dnishh',      C + 'sdt/dnishcha/'),
    ('dnishch',     C + 'sdt/dnishcha/'),
    ('flanc',       C + 'flancy/'),
    ('flanec',      C + 'flancy/'),
    ('opor',        C + 'opory/'),
    ('nepodvizh',   C + 'opory/opory-nepodv/'),
    ('zadvizhk',    C + 'armatura/armatura-zadvizhki/'),
    ('kran',        C + 'armatura/armatura-krany/'),
    ('klapan',      C + 'armatura/armatura-klapany/'),
    ('zatvor',      C + 'armatura/'),
    ('zaporn',      C + 'armatura/'),
    ('bolt',        C + 'krepezh/bolty/'),
    ('gajk',        C + 'krepezh/gayki/'),
    ('shpilk',      C + 'krepezh/shpilki/'),
    ('shajb',       C + 'krepezh/shayby/'),
    ('vint',        C + 'krepezh/vinty/'),
    ('vus',         C + 'izolyatsiya/'),
    ('izol',        C + 'izolyatsiya/'),
    ('ppu',         C + 'izolyatsiya/'),
    ('trub',        C + 'truby/'),
]

# Статические страницы — вручную, их восемь и каждая своя.
# Услуги отдельной страницей на новом сайте не живут — это разделы
# «Производства», туда и ведём; прайс-листы — в соответствующие разделы каталога.
STATIC = {
    '/kontakty/':          'https://prom-en.com/contacts/',
    '/uslugi/':            'https://prom-en.com/production/',
    '/trust-us/':          'https://prom-en.com/proekty/',
    '/prajs-list-truby/':  C + 'truby/',
    '/prajs-list-detali/': C + 'sdt/',
}


# Изоляция и арматура названы в рубриках по-своему и не с начала слага
# («стальная-труба-в-изоляции», «шаровые-краны»), плюс встречается вторая
# транслитерация: troyniki рядом с trojniki. Поэтому ищем по вхождению.
# Арматурная обвязка старого сайта, которой в новом ассортименте нет вовсе:
# фильтры, грязевики, дисковые затворы, штуцеры, шпиндели и удлинители
# штока. Ведём в ближайший по смыслу раздел, а не в корень каталога.
RUBRIC_ANY = [
    ('filtr',        C + 'armatura/'),
    ('gryazevik',    C + 'armatura/'),
    ('diskov',       C + 'armatura/'),
    ('shtucer',      C + 'armatura/'),
    ('shpindel',     C + 'armatura/'),
    ('udlinitel',    C + 'armatura/'),
    ('teleskopich',  C + 'armatura/'),
    ('shtok',        C + 'armatura/'),
    ('kondensatootvod', C + 'armatura/'),
    ('shumoglushitel',  C + 'armatura/'),
    ('ugolnik',      C + 'sdt/'),
    ('izolyac',   C + 'izolyatsiya/'),
    ('izolyats',  C + 'izolyatsiya/'),
    ('v-ppu',     C + 'izolyatsiya/'),
    ('z-obrazn',  C + 'izolyatsiya/'),
    ('ventil',    C + 'armatura/armatura-klapany/'),
    ('sharovye-kran', C + 'armatura/armatura-krany/'),
    ('elektroprivod', C + 'armatura/'),
    ('troynik',   C + 'sdt/troyniki/'),
    ('otvetvlen', C + 'sdt/troyniki/'),
    ('skolzyasch', C + 'opory/opory-skolz/'),
]


def rubric_target(slug):
    for pref, url in RUBRIC_CAT:
        if slug.startswith(pref):
            return url
    for part, url in RUBRIC_ANY:
        if part in slug:
            return url
    return None


def main():
    by_ds, by_dnpn, by_norm, real, cat_of = load_new()

    # Два источника старых адресов, и второй оказался богаче первого.
    # sitemap.xml старого сайта собирался вручную Netpeak Spider и знает
    # 10 174 адреса, а в логах доступа за неделю их 15 464: карта просто
    # отстала от сайта. Без логов 8 788 адресов, из них 1 098 с живым
    # трафиком, отдавали бы 404.
    seen, old = set(), []
    for name, parse in (('old-urls.txt', lambda l: l.strip()),
                        ('log-urls.txt', lambda l: (l.split(None, 1) + [''])[1].strip())):
        path = os.path.join(MIG, name)
        if not os.path.exists(path):
            continue
        for line in io.open(path, encoding='utf-8', errors='replace'):
            u = parse(line)
            if not u.startswith('/'):
                continue
            u = '/' + u.strip('/') + '/' if u != '/' else '/'
            if u not in seen:
                seen.add(u)
                old.append(u)

    rows, unmatched = [], []
    stat = collections.Counter()

    for path in old:
        if path == '/':
            continue                      # главная остаётся главной
        if path in ('/products/', '/rubric-products/'):
            rows.append((path, C, 'архив'))
            stat['архив'] += 1
            continue
        if path.startswith('/rubric-products/'):
            slug = path[len('/rubric-products/'):].strip('/')
            t = rubric_target(slug)
            rows.append((path, t or (C), 'рубрика'))
            stat['рубрика' if t else 'рубрика в каталог'] += 1
            continue
        if not path.startswith('/products/'):
            t = STATIC.get(path)
            if not t and path.startswith('/uslugi/'):
                t = 'https://prom-en.com/production/'
            if t:
                rows.append((path, t, 'статика'))
                stat['статика'] += 1
            else:
                unmatched.append((path, 'статика'))
                stat['не найдено'] += 1
            continue
        slug = path[len('/products/'):].strip('/')
        kind, a, b, tail = old_dims(slug)
        ns = norm_from_tail(tail) if tail else ''
        hit = []
        if kind == 'ds' and ns:
            hit = by_ds.get((ns, a, b), [])
            if not hit:                      # стенка могла округлиться
                for (n2, d2, s2), urls in by_ds.items():
                    if n2 == ns and d2 == a and abs((s2 or 0) - (b or 0)) < 0.11:
                        hit = urls
                        break
        elif kind == 'dnpn' and ns:
            hit = by_dnpn.get((ns, a, b), []) or by_dnpn.get((ns, a, None), [])
        if hit:
            rows.append((path, hit[0], 'товар'))
            stat['товар'] += 1
            continue
        # Точного изделия нет. Сначала пробуем страницу норматива: она
        # уже сузила выборку до нужного стандарта и по смыслу ближе к старому
        # адресу, чем целая категория. На главную не ведём никогда.
        if ns and by_norm.get(ns):
            rows.append((path, 'https://prom-en.com/normativy/%s/' % real.get(ns, ns),
                         'норматив'))
            stat['норматив'] += 1
            continue
        cat_url = category_for(slug)
        if cat_url:
            rows.append((path, cat_url, 'категория'))
            stat['категория'] += 1
        elif path.endswith('/feed/'):
            # Ленты WordPress — не страницы, веса у них нет, в карту не кладём.
            stat['ленты (пропущены)'] += 1
        else:
            # Ни тип, ни норматив не опознаны — корень каталога.
            # Это всё равно не главная: человек искал изделие и попадёт в каталог.
            rows.append((path, C, 'каталог'))
            stat['каталог'] += 1
            unmatched.append((path, ns or '?'))

    with io.open(os.path.join(MIG, 'redirects.csv'), 'w',
                 encoding='utf-8', newline='') as f:
        w = csv.writer(f)
        w.writerow(['old_path', 'new_url', 'how'])
        w.writerows(rows)
    with io.open(os.path.join(MIG, 'unmatched-products.txt'), 'w',
                 encoding='utf-8', newline='') as f:
        f.write('\n'.join('%s\t%s' % r for r in unmatched))

    total = sum(stat.values())
    print('всего старых URL в карте: %d' % total)
    for k, v in stat.most_common():
        print('  %-12s %5d  (%.1f%%)' % (k, v, 100.0 * v / total))


if __name__ == '__main__':
    main()
