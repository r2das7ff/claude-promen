---
name: seo-yandex
description: "Работа с Яндексом для PROM-EN: Вебмастер API (ИКС, страницы в поиске, исключённые с причинами, топ-запросы, история обхода, внешние ссылки, диагностика, отправка на переобход), Метрика API, IndexNow, плюс яндексовская специфика — Clean-param, зеркала, региональность. Использовать при вопросах про индексацию в Яндексе, позиции и запросы, выпавшие страницы, переобход, ИКС, трафик из Метрики. Триггеры: Яндекс, Вебмастер, ИКС, страницы в поиске, исключённые, переобход, Метрика, Clean-param, IndexNow."
user-invocable: true
argument-hint: "[команда] [домен]"
---

# Яндекс: индексация, запросы, трафик

Для B2B на русском рынке Яндекс — основной канал, а готовый плагин
`claude-seo` про него не знает почти ничего (5 упоминаний на весь плагин,
и те про IndexNow). Отсюда свой инструмент: `scripts/seo/yandex.py`.

## Доступ

В `site/.env` (файл в `.gitignore`, в git не едет):

```
YANDEX_WEBMASTER_TOKEN=y0_...
YANDEX_METRIKA_TOKEN=y0_...
YANDEX_METRIKA_COUNTER=12345678
INDEXNOW_KEY=...
```

Токен получает владелец сам: приложение на https://oauth.yandex.ru/client/new
(платформа «Веб-сервисы», Redirect URI `https://oauth.yandex.ru/verification_code`,
права `webmaster:hostinfo` и `webmaster:verify`, для Метрики — `metrika:read`),
затем `https://oauth.yandex.ru/authorize?response_type=token&client_id=<ID>`.
**Токен в чат не просить и в код не вписывать** — только строкой в `.env`.

## Команды

```bash
python scripts/seo/yandex.py hosts                       # какие сайты доступны
python scripts/seo/yandex.py summary prom-en.com         # ИКС, проблемы, страницы в поиске
python scripts/seo/yandex.py queries prom-en.com --days 28 --limit 500
python scripts/seo/yandex.py insearch prom-en.com --days 90
python scripts/seo/yandex.py indexing prom-en.com --days 90
python scripts/seo/yandex.py events prom-en.com          # что вошло и что выпало, с причинами
python scripts/seo/yandex.py diagnostics prom-en.com
python scripts/seo/yandex.py links prom-en.com           # внешние ссылки
python scripts/seo/yandex.py quota prom-en.com           # остаток квоты переобхода
python scripts/seo/yandex.py recrawl prom-en.com <url> <url>
python scripts/seo/yandex.py baseline prom-en.com --out perf-reports/seo/baseline-ГГГГ-ММ-ДД/
python scripts/seo/yandex.py metrika pages --days 30     # sources|engines|pages|queries|devices
python scripts/seo/yandex.py indexnow prom-en.com <url>
```

Домен подставляется в `host_id` (`https:prom-en.com:443`) автоматически.

## Что тут важно знать

**Данные живут на том домене, где история.** Вес, запросы и ИКС — у
`prom-en.com`. Тестовый `prom-en.forgotaboutdre.ru` в Вебмастере может
быть вообще не добавлен, и это нормально: с него снимать нечего, кроме
факта индексации. Проверить, не попал ли тестовый домен в индекс, —
отдельная задача: если попал, после переезда получим два одинаковых сайта.

**`baseline` — перед любыми изменениями.** Слепок в JSON по датам, с ним
потом сравнивать. Без снятого «до» разговор про «просело или нет»
беспредметен.

**Квота переобхода мала** (обычно десятки URL в сутки). Тратить на
категории и важные посадочные, а не на товарные карточки — карточки
подтянутся сами через sitemap.

**«Исключённые» — главный отчёт после изменений.** Смотреть `events` и
причины: «малополезная страница», «дубль», «ошибка HTTP». Для каталога
на 15k карточек с шаблонной мета первые две причины — норма жизни, за
ними следить постоянно.

## Специфика Яндекса, которой нет в общих чеклистах

**Clean-param важнее canonical.** Яндекс официально рекомендует
директиву в robots.txt, а не только canonical. У нас её нет, а
параметрические виды каталога открыты — см. `seo-woo`. Формат:
`Clean-param: utm_source&utm_medium&orderby /catalog/`.

**Зеркала** — `www`/без `www`, `http`/`https` — задаются в Вебмастере,
не только редиректом. При переезде на боевой домен проверять раздел
«Переезд сайта»: домен тот же, поэтому инструмент не нужен, но зеркала
пересмотреть стоит.

**Региональность.** Завод в Челябинске, продажи по РФ. Регион сайта
задаётся через Яндекс.Бизнес и в Вебмастере; для B2B с доставкой по
стране обычно ставят регион производства, но проверять по запросам —
геозависимые они или нет.

**ИКС обновляется раз в 2–3 месяца** — как быстрый индикатор он
бесполезен, смотреть на «страницы в поиске» и показы.
