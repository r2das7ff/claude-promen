# -*- coding: utf-8 -*-
"""Отправить переезды крепежа в IndexNow (Яндекс).

Шлём обе стороны переезда: новый адрес — чтобы попал в индекс, старый —
чтобы поисковик увидел 301 и выбросил его. Ключ и эндпоинт берём у
scripts/seo/yandex.py; там адреса передаются аргументами командной строки,
а 3 122 адреса в неё не помещаются.
"""
import io
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(HERE, '..', 'seo'))
import yandex  # noqa: E402

import requests  # noqa: E402

HOST = 'prom-en.com'
BASE = 'https://' + HOST

urls = []
for line in io.open(os.path.join(HERE, 'moves.tsv'), encoding='utf-8'):
    parts = line.rstrip('\n').split('\t')
    if len(parts) != 2:
        continue
    for path in parts:
        urls.append(BASE + path)
urls = list(dict.fromkeys(urls))  # без повторов, порядок сохраняем

key = yandex.need('INDEXNOW_KEY')
# У IndexNow потолок 10 000 адресов на запрос — у нас с запасом одна пачка.
r = requests.post('https://yandex.com/indexnow', timeout=60,
                  json={'host': HOST, 'key': key, 'urlList': urls})
print('адресов отправлено: %d, ответ: %s %s' % (len(urls), r.status_code, r.text[:200]))
