#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Приёмка переезда на prom-en.com.

Проверяет то, что ломается при смене домена и структуры URL, и то, что
проверить глазами нельзя: коды ответов на выборке из 10 173 старых адресов,
единственность хопа, целостность карты сайта, отсутствие следов стенда.

    python scripts/seo/verify_migration.py                    # прод
    python scripts/seo/verify_migration.py --base https://... # другой хост
    python scripts/seo/verify_migration.py --sample 300       # больше выборка

Код возврата 0 — всё зелёное, 1 — есть падения.
"""
import io, os, re, csv, sys, json, time, random, argparse
import urllib.request, urllib.error

BASE_DIR = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
UA = 'PromenMigrationTest/1.0'

results = []          # (статус, группа, название, подробность)


def check(group, name, ok, detail=''):
    results.append(('PASS' if ok else 'FAIL', group, name, detail))
    return ok


PAUSE = 0.25          # шаг между запросами


def fetch(url, redirect=True, timeout=45, tries=2):
    """Возвращает (код, заголовки, тело, конечный_адрес).

    Шаред-хостинг под сотней запросов подряд начинает рвать соединения:
    первый прогон дал пять падений, и все пять прошли при ручной проверке.
    Тест, падающий от собственной нагрузки, не отличает поломку от тормозов —
    поэтому пауза между запросами и одна повторная попытка на сетевую ошибку.
    Коды HTTP (404, 500) не повторяются — это ответ сайта, а не сбой связи.
    """
    class NoRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, *a, **kw):
            return None
    op = urllib.request.build_opener(*( [] if redirect else [NoRedirect] ))
    req = urllib.request.Request(url, headers={'User-Agent': UA})
    last = None
    for attempt in range(tries):
        try:
            with op.open(req, timeout=timeout) as r:
                body = r.read()
                time.sleep(PAUSE)
                return r.getcode(), dict(r.headers), body, r.geturl()
        except urllib.error.HTTPError as e:
            time.sleep(PAUSE)
            return e.code, dict(e.headers), e.read(), url
        except Exception as e:
            last = e
            time.sleep(1.5 * (attempt + 1))
    return 0, {}, str(last).encode(), url


def text(body):
    try:
        return body.decode('utf-8', 'replace')
    except Exception:
        return ''


# ─────────────────────────────────────────────────────────────────────────
def t_pages(base):
    """Ключевые страницы каждого типа обязаны отдавать 200."""
    pages = [
        ('главная',            '/'),
        ('каталог',            '/catalog/'),
        ('раздел СДТ',         '/catalog/sdt/'),
        ('категория отводов',  '/catalog/sdt/otvody/'),
        ('производство',       '/production/'),
        ('проекты',            '/proekty/'),
        ('статьи',             '/stati/'),
        ('статья',             '/stati/statya-kontrol-kachestva/'),
        ('нормативная база',   '/normativnaya-baza/'),
        ('контакты',           '/contacts/'),
        ('политика ПДн',       '/privacy-policy/'),
    ]
    for name, path in pages:
        code, _, body, _ = fetch(base + path)
        ok = code == 200 and len(body) > 2000
        check('страницы', name, ok, 'код %s, %d байт' % (code, len(body)))


def t_product(base):
    """Карточка товара: 200, canonical на себя, разметка Product без цены."""
    code, _, body, url = fetch(base + '/catalog/sdt/otvody/otvod-90-57h4-gost-17375-2001/')
    if not check('товар', 'карточка отдаёт 200', code == 200, 'код %s' % code):
        return
    h = text(body)
    m = re.search(r'<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)', h)
    check('товар', 'canonical на себя', bool(m) and m.group(1).rstrip('/') == url.rstrip('/'),
          m.group(1) if m else 'нет тега')
    blocks = re.findall(r'<script type="application/ld\+json">(.*?)</script>', h, re.S)
    types = []
    for b in blocks:
        try:
            d = json.loads(b)
        except Exception:
            check('товар', 'JSON-LD разбирается', False, b[:60])
            continue
        types.append(d.get('@type'))
    check('товар', 'есть разметка Product', 'Product' in types, str(types))
    check('товар', 'есть хлебные крошки', 'BreadcrumbList' in types, str(types))


def t_redirects(base, sample):
    """Старые адреса: 301 в один хоп, цель отдаёт 200, не на главную."""
    path = os.path.join(BASE_DIR, 'migration', 'redirects.csv')
    if not os.path.exists(path):
        check('редиректы', 'карта найдена', False, path)
        return
    rows = list(csv.reader(io.open(path, encoding='utf-8')))[1:]
    random.seed(20260901)                      # выборка воспроизводима
    pick = random.sample(rows, min(sample, len(rows)))
    bad_code, chains, to_home, dead = [], [], [], []
    for old, new, how in pick:
        code, hdr, _, _ = fetch(base + old, redirect=False)
        if code != 301:
            bad_code.append('%s -> %s' % (old, code))
            continue
        loc = hdr.get('Location', '')
        if loc.rstrip('/') in (base.rstrip('/'), ''):
            to_home.append(old)
        code2, hdr2, body2, _ = fetch(loc, redirect=False)
        if code2 in (301, 302, 307, 308):
            chains.append('%s -> %s -> %s' % (old, loc, hdr2.get('Location', '')))
        elif code2 != 200:
            dead.append('%s -> %s (%s)' % (old, loc, code2))
    n = len(pick)
    check('редиректы', 'все отдают 301 (выборка %d)' % n, not bad_code, '; '.join(bad_code[:3]))
    check('редиректы', 'один хоп, без цепочек', not chains, '; '.join(chains[:3]))
    check('редиректы', 'цель отдаёт 200', not dead, '; '.join(dead[:3]))
    check('редиректы', 'ни один не ведёт на главную', not to_home, '; '.join(to_home[:3]))


def t_sitemap(base):
    """Карта сайта: индекс и все чанки товаров, иначе каталог мимо индекса."""
    code, _, body, _ = fetch(base + '/wp-sitemap.xml')
    if not check('карта сайта', 'индекс отдаёт 200', code == 200, 'код %s' % code):
        return
    locs = re.findall(r'<loc>\s*([^<]+?)\s*</loc>', text(body))
    check('карта сайта', 'индекс не пуст', len(locs) > 5, '%d файлов' % len(locs))
    total, bad = 0, []
    for loc in locs:
        c, _, b, _ = fetch(loc)
        if c != 200:
            bad.append('%s -> %s' % (loc.rsplit('/', 1)[-1], c))
            continue
        total += len(re.findall(r'<loc>', text(b)))
    check('карта сайта', 'все файлы отдают 200', not bad, '; '.join(bad[:4]))
    check('карта сайта', 'адресов больше 15 000', total > 15000, '%d адресов' % total)


def t_no_staging(base):
    """Ни одного следа стенда: иначе склейка пойдёт не в ту сторону."""
    for path in ['/', '/catalog/', '/contacts/', '/wp-sitemap.xml']:
        _, _, body, _ = fetch(base + path)
        h = text(body)
        check('стенд', 'нет упоминаний стенда: %s' % path,
              'forgotaboutdre' not in h,
              'найдено %d' % h.count('forgotaboutdre'))


def t_robots(base):
    code, _, body, _ = fetch(base + '/robots.txt')
    h = text(body)
    check('robots', 'отдаётся 200', code == 200, 'код %s' % code)
    check('robots', 'указана карта сайта', '/wp-sitemap.xml' in h)
    check('robots', 'закрыты параметрические адреса', 'Disallow: /*?' in h)
    check('robots', 'нормативы открыты (решение заказчика)',
          'uploads/normativy' not in h)
    check('robots', 'нет ссылки на стенд', 'forgotaboutdre' not in h)


def t_pdf(base):
    """PDF нормативов: доступны и с правильным типом."""
    for slug in ['gost-17375-2001', 'gost-12820-1980', 'ost-36-21-77']:
        code, hdr, body, _ = fetch(base + '/wp-content/uploads/normativy/%s.pdf' % slug)
        ok = code == 200 and 'pdf' in hdr.get('Content-Type', '').lower() and len(body) > 10000
        check('нормативы', 'PDF %s' % slug, ok,
              'код %s, тип %s, %d байт' % (code, hdr.get('Content-Type', '?'), len(body)))


def t_mirrors(base):
    """Зеркала: www и http сводятся к одной канонической форме."""
    code, hdr, _, _ = fetch('http://prom-en.com/', redirect=False)
    check('зеркала', 'http → https', code in (301, 308),
          'код %s -> %s' % (code, hdr.get('Location', '')))
    code, hdr, _, _ = fetch('https://www.prom-en.com/', redirect=False)
    check('зеркала', 'www → без www', code in (301, 308),
          'код %s -> %s' % (code, hdr.get('Location', '')))


def t_404(base):
    """Несуществующий адрес обязан быть 404, а не мягкой копией главной."""
    code, _, body, _ = fetch(base + '/takogo-adresa-net-12345/')
    check('404', 'несуществующий адрес отдаёт 404', code == 404, 'код %s' % code)
    code, _, _, _ = fetch(base + '/products/nesuschestvuyuschiy-tovar-999x999-gost-0000-00/')
    check('404', 'неизвестный старый адрес отдаёт 404, а не редирект',
          code == 404, 'код %s' % code)


# ─────────────────────────────────────────────────────────────────────────
def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--base', default='https://prom-en.com')
    ap.add_argument('--sample', type=int, default=120,
                    help='сколько старых адресов проверять (из 10 173)')
    ap.add_argument('--skip', default='', help='пропустить группы через запятую')
    a = ap.parse_args()
    base = a.base.rstrip('/')
    skip = {s.strip() for s in a.skip.split(',') if s.strip()}

    t0 = time.time()
    for name, fn in [('pages', lambda: t_pages(base)),
                     ('product', lambda: t_product(base)),
                     ('redirects', lambda: t_redirects(base, a.sample)),
                     ('sitemap', lambda: t_sitemap(base)),
                     ('staging', lambda: t_no_staging(base)),
                     ('robots', lambda: t_robots(base)),
                     ('pdf', lambda: t_pdf(base)),
                     ('mirrors', lambda: t_mirrors(base)),
                     ('404', lambda: t_404(base))]:
        if name in skip:
            continue
        fn()

    failed = [r for r in results if r[0] == 'FAIL']
    group = None
    for st, g, name, detail in results:
        if g != group:
            print('\n%s' % g.upper())
            group = g
        mark = 'ok  ' if st == 'PASS' else 'FAIL'
        print('  %s %-46s %s' % (mark, name, detail))
    print('\n%d проверок, %d падений, %.0f с' % (len(results), len(failed), time.time() - t0))
    return 1 if failed else 0


if __name__ == '__main__':
    sys.exit(main())
