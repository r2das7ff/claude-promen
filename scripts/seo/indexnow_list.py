# -*- coding: utf-8 -*-
"""Отправить в IndexNow (Яндекс) адреса из файла — по одному на строку.

Строка может быть полным адресом или путём от корня сайта («/catalog/…»).
Ключ берётся у yandex.py из site/.env. yandex.py принимает адреса
аргументами командной строки, а сотни адресов в неё не помещаются.

  python scripts/seo/indexnow_list.py список.txt [ещё.txt …]
"""
import io
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import yandex  # noqa: E402

import requests  # noqa: E402

HOST = 'prom-en.com'
BASE = 'https://' + HOST

urls = []
for path in sys.argv[1:]:
    for line in io.open(path, encoding='utf-8'):
        u = line.strip()
        if not u:
            continue
        urls.append(u if u.startswith('http') else BASE + u)
urls = list(dict.fromkeys(urls))  # без повторов, порядок сохраняем
if not urls:
    sys.exit('нет адресов')

key = yandex.need('INDEXNOW_KEY')
# У IndexNow потолок 10 000 адресов на запрос.
r = requests.post('https://yandex.com/indexnow', timeout=60,
                  json={'host': HOST, 'key': key, 'urlList': urls[:10000]})
print('адресов отправлено: %d, ответ: %s %s' % (min(len(urls), 10000), r.status_code, r.text[:200]))
