# SEO-отчёт: https://prom-en.forgotaboutdre.ru

Обойдено страниц: **16847**, из них 200: **16326**. База: `perf-reports/seo/crawl-after.db`.

| Уровень | Проверка | Найдено |
|---|---|---|
| 🔴 1 | Ответ 4xx/5xx (`http_error`) | 0 |
| 🔴 1 | Сетевая ошибка при загрузке (`net_error`) | 1529 |
| 🔴 1 | Внутренние ссылки на битые страницы (`broken_link`) | 0 |
| 🔴 1 | Цепочки редиректов (2+ хопа) (`redirect_chain`) | 0 |
| 🔴 1 | Редирект ведёт на 4xx/5xx (`redirect_to_error`) | 0 |
| 🟡 2 | Внутренние ссылки на редиректы (`link_to_redirect`) | 521 |
| 🔴 1 | Пустой title (`no_title`) | 0 |
| 🔴 1 | Дубли title (`dup_title`) | 116 |
| ⚪ 3 | Короткий title (<30) (`short_title`) | 0 |
| ⚪ 3 | Длинный title (>65) (`long_title`) | 4137 |
| 🟡 2 | Пустой description (`no_desc`) | 2 |
| 🟡 2 | Дубли description (`dup_desc`) | 225 |
| ⚪ 3 | Длинный description (>180) (`long_desc`) | 0 |
| 🔴 1 | Нет H1 (`no_h1`) | 1 |
| 🟡 2 | Несколько H1 (`multi_h1`) | 0 |
| 🟡 2 | Дубли H1 (`dup_h1`) | 57 |
| 🟡 2 | Нет canonical (`no_canonical`) | 2 |
| 🔴 1 | Canonical указывает на другой URL (`cross_canonical`) | 5 |
| 🔴 1 | Canonical ведёт на не-200 (`canonical_broken`) | 0 |
| 🟡 2 | Страницы с noindex (`noindex`) | 0 |
| 🔴 1 | В sitemap, но не отдаёт 200 (`sitemap_not200`) | 4 |
| 🔴 1 | В sitemap, но закрыта от индексации (`sitemap_noindex`) | 0 |
| 🟡 2 | Сироты: в sitemap, но без внутренних ссылок (`orphan`) | 416 |
| 🟡 2 | Глубина больше 3 кликов (`deep`) | 12862 |
| 🟡 2 | Тонкий контент (<150 слов) (`thin`) | 0 |
| 🟡 2 | Нет микроразметки JSON-LD (`no_jsonld`) | 0 |
| ⚪ 3 | Медленный ответ (>1.5 c) (`slow`) | 1452 |

## 🔴 Сетевая ошибка при загрузке — 1529

Таймаут или обрыв. Если воспроизводится — робот увидит то же самое.

| error | url |
|---|---|
| ParserError: Document is empty | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m14h200-10-9-gost-7808-70/ |
| ParserError: Document is empty | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m14h220-10-9-gost-7808-70/ |
| ParserError: Document is empty | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m22h200-10-9-gost-7795-70/ |

_…ещё 1526, полный список в CSV_

## 🟡 Внутренние ссылки на редиректы — 521

Внутри сайта надо ссылаться на конечный адрес, а не через редирект.

| dst | cnt | example_src |
|---|---|---|
| https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-900-11-1-b-iv-gost-33259-2015/ | 398 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-01/flanec-10-01-1-b-iv-pn1-gost-33259-2015/ |
| https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-900-11-1-b-iv-gost-33259-2015-8/ | 398 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-01/flanec-10-01-1-b-iv-pn1-gost-33259-2015/ |
| https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-900-11-1-b-iv-gost-33259-2015-7/ | 398 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-01/flanec-10-01-1-b-iv-pn1-gost-33259-2015/ |

_…ещё 518, полный список в CSV_

## 🔴 Дубли title — 116

На каталоге в 15k карточек это главный источник 'малополезных' страниц у Яндекса.

| title | cnt | example |
|---|---|---|
| Переход 630×8-530×8 ОСТ 34.10.424-1990 — Промышленная Энергетика | 5 | https://prom-en.forgotaboutdre.ru/catalog/sdt/perekhody/perehod-600-500-530-8-ost-34-10-424-1990-2/ |
| Переход 920×10-920×10 ОСТ 34.10.424-1990 — Промышленная Энергетика | 4 | https://prom-en.forgotaboutdre.ru/catalog/sdt/perekhody/perehod-1000-900-920-10-ost-34-10-424-1990-2/ |
| Переход 89×5-89×5 ОСТ 34.10.422-1990 — Промышленная Энергетика | 4 | https://prom-en.forgotaboutdre.ru/catalog/sdt/perekhody/perehod-k-89h5-89h5-ost-34-10-422-1990-2/ |

_…ещё 113, полный список в CSV_

## ⚪ Длинный title (>65) — 4137

Обрежется в выдаче. Важное — в первые 60 символов.

| title_len | title | url |
|---|---|---|
| 100 | Контроль качества СДТ — от входного контроля до паспорта изделия — PROM-EN — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/stati/statya-kontrol-kachestva/ |
| 99 | Калькулятор веса отводов, переходов, тройников, заглушек и днищ — PROM-EN — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/kalkulyatory/ves-sdt/ |
| 94 | Отвод 15° 108х12.0 R325 исп. 01 08Х18Н10Т PN19.62 ОСТ 24.125.04-1989 — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/otvod-15-108h12-0-r325-isp-01-08h18n10t-ost-24-125-04-1989/ |

_…ещё 4134, полный список в CSV_

## 🟡 Пустой description — 2

Сниппет соберётся сам и обычно хуже, чем написанный.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/category/uncategorized/ |
| https://prom-en.forgotaboutdre.ru/author/admin/ |

## 🟡 Дубли description — 225

Шаблон без подстановки размеров/ГОСТа. Признак генерации по одному лекалу.

| description | cnt | example |
|---|---|---|
| Неподвижная хомутовая опора для фиксации трубопровода. Поставка под заказ с паспортом. | 12 | https://prom-en.forgotaboutdre.ru/catalog/opory/opory-nepodv/opora-nepodvizhnaya-homutovaya-dn100-ost-36-17-85/ |
| Кран шаровой полнопроходный стальной. Поставка под заказ. | 12 | https://prom-en.forgotaboutdre.ru/catalog/armatura/armatura-krany/kran-sharovoy-polnoprohodnyy-dn100-pn4-0-mpa/ |
| Клапан обратный подъёмный стальной. Поставка под заказ. | 12 | https://prom-en.forgotaboutdre.ru/catalog/armatura/armatura-klapany/klapan-obratnyy-podemnyy-dn100-pn2-5-mpa/ |

_…ещё 222, полный список в CSV_

## 🔴 Нет H1 — 1

H1 задаёт тему страницы. Частая беда конструкторных шаблонов.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/production/ |

## 🟡 Дубли H1 — 57

Разные товары с одинаковым заголовком — кандидаты на склейку у поисковика.

| h1 | cnt | example |
|---|---|---|
| Каталогпродукции | 538 | https://prom-en.forgotaboutdre.ru/author/admin/ |
| Соединительныедеталитрубопровода | 21 | https://prom-en.forgotaboutdre.ru/catalog/sdt/ |
| Тройникистальныеприварные | 19 | https://prom-en.forgotaboutdre.ru/catalog/sdt/troyniki/ |

_…ещё 54, полный список в CSV_

## 🟡 Нет canonical — 2

На каталоге с фильтрами canonical обязателен, иначе дубли плодятся сами.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/category/uncategorized/ |
| https://prom-en.forgotaboutdre.ru/author/admin/ |

## 🔴 Canonical указывает на другой URL — 5

Страница добровольно отдаёт вес другой. Проверить, что это осознанно.

| url | canonical |
|---|---|
| https://prom-en.forgotaboutdre.ru/catalog/izolyatsiya/izolyatsiya-troyniki/seriya/gost-30732-2020/ | https://prom-en.forgotaboutdre.ru/catalog/izolyatsiya/izolyatsiya-truby/seriya/gost-30732-2020/ |
| https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-01/seriya/gost-33259-2015/ | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/seriya/gost-33259-2015/ |
| https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/seriya/ost-34-10-418-1990/ | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/seriya/ost-34-10-418-1990-45/ |

_…ещё 2, полный список в CSV_

## 🔴 В sitemap, но не отдаёт 200 — 4

Sitemap врёт роботу. Для Яндекса это прямой минус к доверию карте.

| status | url |
|---|---|
|  | https://prom-en.forgotaboutdre.ru/catalog/tochenye/perehod-tochenyy-isp-1-89h76-gost-22826-1983/ |
|  | https://prom-en.forgotaboutdre.ru/catalog/tochenye/perehod-tochenyy-isp-1-114h76-gost-22826-1983/ |
|  | https://prom-en.forgotaboutdre.ru/catalog/tochenye/perehod-tochenyy-isp-1-114h89-gost-22826-1983/ |

_…ещё 1, полный список в CSV_

## 🟡 Сироты: в sitemap, но без внутренних ссылок — 416

До страницы нельзя дойти по ссылкам — веса она не получает.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m10h120-10-9-gost-7796-70/ |
| https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m10h125-10-9-gost-7796-70/ |
| https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m10h130-10-9-gost-7796-70/ |

_…ещё 413, полный список в CSV_

## 🟡 Глубина больше 3 кликов — 12862

Чем глубже, тем реже обход. Для каталога критично.

| depth | url |
|---|---|
| 12 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h180-10-9-gost-22032-76/ |
| 12 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h190-10-9-gost-22032-76/ |
| 12 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h200-10-9-gost-22032-76/ |

_…ещё 12859, полный список в CSV_

## ⚪ Медленный ответ (>1.5 c) — 1452

Время до первого байта. На каталоге обычно упирается в отсутствие кэша.

| elapsed_ms | url |
|---|---|
| 23405 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-vorot/flanec-fv-dn100-pn25-gost-12821-1980/ |
| 21353 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-vorot/flanec-fv-dn100-pn40-gost-12821-1980/ |
| 20062 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m48h18-10-9-gost-7805-70/ |

_…ещё 1449, полный список в CSV_