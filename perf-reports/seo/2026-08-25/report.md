# SEO-отчёт: https://prom-en.forgotaboutdre.ru

Обойдено страниц: **16395**, из них 200: **16390**. База: `perf-reports/seo/crawl-new.db`.

| Уровень | Проверка | Найдено |
|---|---|---|
| 🔴 1 | Ответ 4xx/5xx (`http_error`) | 3 |
| 🔴 1 | Сетевая ошибка при загрузке (`net_error`) | 0 |
| 🔴 1 | Внутренние ссылки на битые страницы (`broken_link`) | 3 |
| 🔴 1 | Цепочки редиректов (2+ хопа) (`redirect_chain`) | 0 |
| 🔴 1 | Редирект ведёт на 4xx/5xx (`redirect_to_error`) | 0 |
| 🟡 2 | Внутренние ссылки на редиректы (`link_to_redirect`) | 0 |
| 🔴 1 | Пустой title (`no_title`) | 0 |
| 🔴 1 | Дубли title (`dup_title`) | 298 |
| ⚪ 3 | Короткий title (<30) (`short_title`) | 0 |
| ⚪ 3 | Длинный title (>65) (`long_title`) | 3200 |
| 🟡 2 | Пустой description (`no_desc`) | 546 |
| 🟡 2 | Дубли description (`dup_desc`) | 288 |
| ⚪ 3 | Длинный description (>180) (`long_desc`) | 6315 |
| 🔴 1 | Нет H1 (`no_h1`) | 1 |
| 🟡 2 | Несколько H1 (`multi_h1`) | 0 |
| 🟡 2 | Дубли H1 (`dup_h1`) | 93 |
| 🟡 2 | Нет canonical (`no_canonical`) | 815 |
| 🔴 1 | Canonical указывает на другой URL (`cross_canonical`) | 6 |
| 🔴 1 | Canonical ведёт на не-200 (`canonical_broken`) | 0 |
| 🟡 2 | Страницы с noindex (`noindex`) | 1 |
| 🔴 1 | В sitemap, но не отдаёт 200 (`sitemap_not200`) | 4 |
| 🔴 1 | В sitemap, но закрыта от индексации (`sitemap_noindex`) | 1 |
| 🟡 2 | Сироты: в sitemap, но без внутренних ссылок (`orphan`) | 0 |
| 🟡 2 | Глубина больше 3 кликов (`deep`) | 12628 |
| 🟡 2 | Тонкий контент (<150 слов) (`thin`) | 0 |
| 🟡 2 | Нет микроразметки JSON-LD (`no_jsonld`) | 73 |
| ⚪ 3 | Медленный ответ (>1.5 c) (`slow`) | 1554 |

## 🔴 Ответ 4xx/5xx — 3

Страница отдаёт ошибку. В индексе такие держать нельзя.

| status | url |
|---|---|
| 404 | https://prom-en.forgotaboutdre.ru/catalog/seriya/ost-34-10-422-1990/ |
| 500 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/shpilka-m206-8h130-4-6-gost-15591-70/ |
| 500 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/shpilka-m206-8h150-4-6-gost-15591-70/ |

## 🔴 Внутренние ссылки на битые страницы — 3

Ссылки ведут в 404. Слив краулингового бюджета и веса.

| dst | cnt | example_src |
|---|---|---|
| https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/shpilka-m206-8h130-4-6-gost-15591-70/ | 212 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/page/221/ |
| https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/shpilka-m206-8h150-4-6-gost-15591-70/ | 212 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/page/221/ |
| https://prom-en.forgotaboutdre.ru/catalog/seriya/ost-34-10-422-1990/ | 2 | https://prom-en.forgotaboutdre.ru/catalog/seriya/ost-34-10-422-1990/ |

## 🔴 Дубли title — 298

На каталоге в 15k карточек это главный источник 'малополезных' страниц у Яндекса.

| title | cnt | example |
|---|---|---|
| PROM-EN — Промышленная Энергетика | 45 | https://prom-en.forgotaboutdre.ru/ |
| Фланец 80-11-1-B-IV ГОСТ 33259-2015 — Промышленная Энергетика | 12 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-80-11-1-b-iv-gost-33259-2015-10/ |
| Фланец 50-11-1-B-IV ГОСТ 33259-2015 — Промышленная Энергетика | 12 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-50-11-1-b-iv-gost-33259-2015-10/ |
| Фланец 40-11-1-B-IV ГОСТ 33259-2015 — Промышленная Энергетика | 12 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-40-11-1-b-iv-gost-33259-2015-10/ |
| Фланец 200-11-1-B-IV ГОСТ 33259-2015 — Промышленная Энергетика | 12 | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/flanec-200-11-1-b-iv-gost-33259-2015-10/ |

_…ещё 293, полный список в CSV_

## ⚪ Длинный title (>65) — 3200

Обрежется в выдаче. Важное — в первые 60 символов.

| title_len | title | url |
|---|---|---|
| 100 | Контроль качества СДТ — от входного контроля до паспорта изделия — PROM-EN — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/stati/statya-kontrol-kachestva/ |
| 99 | Калькулятор веса отводов, переходов, тройников, заглушек и днищ — PROM-EN — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/kalkulyatory/ves-sdt/ |
| 96 | Отвод гнутый 90° 76х11.5 исп. 020 PN 25.01 12Х1МФ СТО ЦКТИ 321.05-2009 — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/otvod-gnutyy-90-76h11-5-isp-020-pn-25-01-12h1mf-sto-321-05-2009/ |
| 96 | Отвод гнутый 90° 133х500 исп. 550 PN 13.73 12Х1МФ СТО ЦКТИ 321.05-2009 — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/otvod-gnutyy-90-133h500-isp-550-pn-13-73-12h1mf-sto-321-05-2009/ |
| 96 | Отвод гнутый 60° 76х11.5 исп. 019 PN 25.01 12Х1МФ СТО ЦКТИ 321.05-2009 — Промышленная Энергетика | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/otvod-gnutyy-60-76h11-5-isp-019-pn-25-01-12h1mf-sto-321-05-2009/ |

_…ещё 3195, полный список в CSV_

## 🟡 Пустой description — 546

Сниппет соберётся сам и обычно хуже, чем написанный.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/proekty/kurskaya-aes/ |
| https://prom-en.forgotaboutdre.ru/proekty/cherepetskaya-gres/ |
| https://prom-en.forgotaboutdre.ru/proekty/teploelektrocentral-tec-3/ |
| https://prom-en.forgotaboutdre.ru/proekty/aes-akkuyu/ |
| https://prom-en.forgotaboutdre.ru/proekty/aes-ruppur/ |

_…ещё 541, полный список в CSV_

## 🟡 Дубли description — 288

Шаблон без подстановки размеров/ГОСТа. Признак генерации по одному лекалу.

| description | cnt | example |
|---|---|---|
| Завод «Промышленная Энергетика» — производство деталей и сборочных единиц трубопроводов для объектов атомной и тепловой  | 45 | https://prom-en.forgotaboutdre.ru/ |
| Соединительные детали трубопроводов (СДТ) — отводы, переходы, тройники, днища и заглушки для сборки трубопроводных систе | 21 | https://prom-en.forgotaboutdre.ru/catalog/sdt/ |
| Каталог соединительных деталей трубопроводов, фланцев и крепежа. Запрос коммерческого предложения без корзины. | 21 | https://prom-en.forgotaboutdre.ru/catalog/ |
| Стальные приварные тройники для ответвления от магистрали: равнопроходные и переходные исполнения по ГОСТ, ОСТ и отрасле | 19 | https://prom-en.forgotaboutdre.ru/catalog/sdt/troyniki/ |
| Стальные приварные отводы для изменения направления трубопровода: крутоизогнутые штампованные по ГОСТ 17375-2001 и ГОСТ  | 19 | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/ |

_…ещё 283, полный список в CSV_

## ⚪ Длинный description (>180) — 6315

Обрежется. 150–160 символов — рабочий диапазон.

| desc_len | url |
|---|---|
| 301 | https://prom-en.forgotaboutdre.ru/catalog/sdt/ |
| 301 | https://prom-en.forgotaboutdre.ru/catalog/sdt/page/2/ |
| 301 | https://prom-en.forgotaboutdre.ru/catalog/sdt/page/193/ |
| 301 | https://prom-en.forgotaboutdre.ru/catalog/sdt/troyniki/ |
| 301 | https://prom-en.forgotaboutdre.ru/catalog/sdt/perekhody/ |

_…ещё 6310, полный список в CSV_

## 🔴 Нет H1 — 1

H1 задаёт тему страницы. Частая беда конструкторных шаблонов.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/production/ |

## 🟡 Дубли H1 — 93

Разные товары с одинаковым заголовком — кандидаты на склейку у поисковика.

| h1 | cnt | example |
|---|---|---|
| Болт | 4606 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m1-6h10-10-9-gost-7805-70/ |
| Шпилька | 1780 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/seriya/gost-10494-1980/ |
| Тройник | 1161 | https://prom-en.forgotaboutdre.ru/catalog/sdt/troyniki/seriya/gost-17376-2001/ |
| Труба э/с | 733 | https://prom-en.forgotaboutdre.ru/catalog/truby/truby-es/truba-e-s-1020h10-gost-10704-1991/ |
| Труба б/ш | 700 | https://prom-en.forgotaboutdre.ru/catalog/truby/truby-bsh/truba-b-sh-102h10-gost-8732-1978/ |

_…ещё 88, полный список в CSV_

## 🟡 Нет canonical — 815

На каталоге с фильтрами canonical обязателен, иначе дубли плодятся сами.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/ |
| https://prom-en.forgotaboutdre.ru/catalog/ |
| https://prom-en.forgotaboutdre.ru/catalog/sdt/ |
| https://prom-en.forgotaboutdre.ru/catalog/page/2/ |
| https://prom-en.forgotaboutdre.ru/catalog/page/514/ |

_…ещё 810, полный список в CSV_

## 🔴 Canonical указывает на другой URL — 6

Страница добровольно отдаёт вес другой. Проверить, что это осознанно.

| url | canonical |
|---|---|
| https://prom-en.forgotaboutdre.ru/catalog/izolyatsiya/izolyatsiya-troyniki/seriya/gost-30732-2020/ | https://prom-en.forgotaboutdre.ru/catalog/izolyatsiya/izolyatsiya-truby/seriya/gost-30732-2020/ |
| https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-01/seriya/gost-33259-2015/ | https://prom-en.forgotaboutdre.ru/catalog/flancy/flancy-11/seriya/gost-33259-2015/ |
| https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/seriya/ost-34-42-661-1984/ | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/seriya/ost-34-42-661-1984-30/ |
| https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/seriya/ost-34-10-418-1990/ | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/seriya/ost-34-10-418-1990-45/ |
| https://prom-en.forgotaboutdre.ru/catalog/armatura/armatura-klapany/seriya/gost-33257-2015/ | https://prom-en.forgotaboutdre.ru/catalog/armatura/armatura-zadvizhki/seriya/gost-33257-2015/ |

_…ещё 1, полный список в CSV_

## 🟡 Страницы с noindex — 1

Проверить глазами: часть — намеренно, часть — случайно закрытые важные разделы.

| robots | url |
|---|---|
| max-image-preview:large, noindex, follow | https://prom-en.forgotaboutdre.ru/my-account/ |

## 🔴 В sitemap, но не отдаёт 200 — 4

Sitemap врёт роботу. Для Яндекса это прямой минус к доверию карте.

| status | url |
|---|---|
| 500 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/shpilka-m206-8h130-4-6-gost-15591-70/ |
| 500 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/shpilki/shpilka-m206-8h150-4-6-gost-15591-70/ |
| 302 | https://prom-en.forgotaboutdre.ru/cart/ |
| 302 | https://prom-en.forgotaboutdre.ru/checkout/ |

## 🔴 В sitemap, но закрыта от индексации — 1

Противоречивый сигнал: карта зовёт, мета запрещает.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/my-account/ |

## 🟡 Глубина больше 3 кликов — 12628

Чем глубже, тем реже обход. Для каталога критично.

| depth | url |
|---|---|
| 11 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h180-10-9-gost-22032-76/ |
| 11 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h190-10-9-gost-22032-76/ |
| 11 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h200-10-9-gost-22032-76/ |
| 11 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h220-10-9-gost-22032-76/ |
| 11 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m42h240-10-9-gost-22032-76/ |

_…ещё 12623, полный список в CSV_

## 🟡 Нет микроразметки JSON-LD — 73

Для товаров и категорий разметка даёт расширенный сниппет.

| url |
|---|
| https://prom-en.forgotaboutdre.ru/ |
| https://prom-en.forgotaboutdre.ru/production/ |
| https://prom-en.forgotaboutdre.ru/proekty/ |
| https://prom-en.forgotaboutdre.ru/normativnaya-baza/ |
| https://prom-en.forgotaboutdre.ru/kalkulyatory/ |

_…ещё 68, полный список в CSV_

## ⚪ Медленный ответ (>1.5 c) — 1554

Время до первого байта. На каталоге обычно упирается в отсутствие кэша.

| elapsed_ms | url |
|---|---|
| 10287 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m22h65-10-9-gost-7805-70/ |
| 9730 | https://prom-en.forgotaboutdre.ru/catalog/sdt/otvody/otvod-gnutyy-45-245h48-isp-053-pn-25-01-12h1mf-sto-321-05-2009/ |
| 9669 | https://prom-en.forgotaboutdre.ru/catalog/sdt/troyniki/troynik-720h9-325h8-isp-165-st20-ost-34-42-676-1984/ |
| 9657 | https://prom-en.forgotaboutdre.ru/catalog/sdt/troyniki/troynik-720h9-377h9-isp-166-st20-ost-34-42-676-1984/ |
| 9572 | https://prom-en.forgotaboutdre.ru/catalog/krepezh/bolty/bolt-m5h16-10-9-gost-7805-70/ |

_…ещё 1549, полный список в CSV_