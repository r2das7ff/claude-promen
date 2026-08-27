# PROM-EN → WordPress: хендофф для полной миграции сайта

Дата пакета: **2026-07-23** · дамп обновлён **2026-07-28**  
Ветка: `catalog-variativity-dedup`  
Тема: `promen` **0.35.7** · WP **7.0.2** · WooCommerce **10.9.4**

---

## 1. Задача для вас

1. Развернуть этот проект у себя (Docker).
2. Убедиться, что **каталог** (реестр, фильтры, карточки, страницы категорий) работает.
3. Довести **весь сайт** до продакшена: главная, производство, прочие маркетинговые
   страницы, SEO, формы, деплой — по дизайн-референсу 1:1.

Каталог как продукт **уже в рабочем состоянии** (данные + тема + поиск).  
Остальное — миграция витринных страниц и прод-обвязка.

---

## 2. Быстрый старт (30–60 мин)

### Требования
- Docker Desktop / Docker Engine + Compose v2
- git-lfs — **до клона**: дамп хранится через LFS, без него приедет
  текстовая заглушка на ~130 байт и restore упадёт
- ~6 GB свободного места (клон ~500 MB; дамп ~23 MB gzip; после restore БД ~1 GB; образы ~2 GB)
- Опционально: соседняя папка `PROM-EN/normatives/` (PDF ГОСТ, исходный CSV-пайплайн) —
  для повторного импорта и фактов. В архиве сайта её **нет** (тяжёлая).

### Структура после распаковки

```
PROM-EN/
  site/                 ← этот репозиторий (корень docker-compose)
    handoff/            ← дамп БД + эта инструкция
    wp-content/themes/promen/
    wp-content/mu-plugins/
    design-reference/   ← HTML-эталон дизайна
    products-csv/       ← CSV для WP-CLI импорта
    scripts/promen-cli.php
    docker-compose.yml
  normatives/           ← (желательно рядом) PDF + исходники данных
```

### Поднять стек

```bash
cd site
cp .env.example .env   # при необходимости

docker compose up -d
# сервисы: db (MariaDB 11), wordpress (:8080), meilisearch (:7700), wpcli
```

WP-ядро живёт в named volume `wpcore` (не в `.docker/wp/` — bind-mount ядра на
Windows/NTFS давал ~4с TTFB из-за stat'ов opcache). Тема и mu-plugins
примонтированы поверх bind-mount'ом:

| Хост | Контейнер |
|------|-----------|
| `wp-content/themes/promen` | `/var/www/html/wp-content/themes/promen` |
| `wp-content/mu-plugins` | `/var/www/html/wp-content/mu-plugins` |
| `products-csv` | `/data/products-csv` (ro) |
| `scripts` (wpcli) | `/scripts` |

### Восстановить БД из дампа

```bash
# дождаться healthy у db
docker compose exec -T db mariadb -uroot -proot -e "DROP DATABASE IF EXISTS wordpress; CREATE DATABASE wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON wordpress.* TO 'wp'@'%'; FLUSH PRIVILEGES;"

gunzip -c handoff/wordpress-db.sql.gz | docker compose exec -T db mariadb -uroot -proot wordpress

# URL в дампе = http://localhost:8080 — если порт/хост другой:
docker compose run --rm wpcli search-replace 'http://localhost:8080' 'http://YOUR:PORT' --all-tables --precise
docker compose run --rm wpcli cache flush
```

Учётки БД по умолчанию (см. `docker-compose.yml`):  
`wordpress` / user `wp` / pass `wp` / root `root`.

### WooCommerce (обязательно на чистой машине)

WooCommerce живёт в named volume `wpcore`, а не в репозитории, — на новой
машине volume создаётся пустым. В дампе плагин активен и все 20 таблиц
`wp_wc*` на месте, поэтому сайт отдаёт 200 и выглядит рабочим, но
`wc_get_product()` не существует и `promen catalog-rebuild` падает с фаталом.

```bash
docker compose run --rm wpcli plugin install woocommerce --version=10.9.4
```

Версию брать строго из `woocommerce_db_version` в `wp_options` — более свежая
прогонит апгрейд БД по восстановленному каталогу.

### Meilisearch (поиск каталога)

После restore переиндексировать:

```bash
docker compose run --rm wpcli promen search-reindex
docker compose run --rm wpcli promen catalog-rebuild
docker compose run --rm wpcli cache flush
```

Команды `wp promen *` подключаются автоматически из mu-plugin
`promen-structure.php` → `require /scripts/promen-cli.php` (том `scripts` в сервисе `wpcli`).

```bash
docker compose run --rm wpcli help promen
```

Ключ Meili: `promen_dev_key` (`PROMEN_MEILI_URL` / `PROMEN_MEILI_KEY` в compose).

### Проверка

Откройте:

| URL | Ожидание |
|-----|----------|
| http://localhost:8080/catalog/ | реестр каталога, сайдбар групп |
| http://localhost:8080/catalog/sdt/ | страница категории СДТ |
| http://localhost:8080/catalog/sdt/otvody/ | отводы: серии → живой реестр → контент |
| карточка любого товара | паспорт, стали, «Запросить КП» |

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/catalog/
curl -s http://localhost:8080/catalog/sdt/otvody/ | grep -c 'критическая ошибка'  # → 0
```

**Реестры каталога и нормативной базы рендерятся на клиенте.** `curl` видит
плейсхолдеры `—` и ноль ссылок на товары даже на полностью исправной странице,
поэтому проверки выше годятся только для кода ответа и поиска фаталов.
Наполнение реестра, счётчики и фасеты смотрите браузером с исполнением JS.

---

## 3. Что уже сделано (каталог)

### Данные (снимок на 2026-07-28, сверено с живой базой)
- **15 407** опубликованных товаров WooCommerce
- **15 407** строк в каноне `wp_promen_catalog_rows`, drift 0

| Группа | Позиций |
|---|---|
| крепёж (болты 4598 · шпильки 1778 · гайки 144 · винты 134 · шайбы 83) | 6 737 |
| СДТ (отводы 3247 · тройники 1398 · переходы 557 · заглушки 337 · днища 230) | 5 769 |
| трубы (ЭС 733 · БШ 700 · ВГП 35) | 1 468 |
| фланцы (тип 11 — 261 · плоские 255 · тип 01 — 135 · воротниковые 75 · прочие 29) | 755 |
| изоляция (трубы 441 · тройники 72) | 513 |
| точёные | 99 |
| арматура (краны 12 · задвижки 12 · клапаны 9) | 33 |
| опоры (неподвижные 12 · скользящие 11 · пружинные 9) | 32 |

Суммы сверены со счётчиками на живых страницах и сходятся до единицы.
Одна строка канона имеет пустой `category` — единственная известная аномалия.

> На ветке `catalog-variativity-dedup` проходила дедупликация вариативности —
> цифры могут отличаться от более ранних отчётов (~19k). Ориентир — `promen catalog-verify`
> и счётчики на живых страницах.

### Тема `wp-content/themes/promen/`
- Шаблоны категорий: `woocommerce/taxonomy-product_cat-*.php` (18 шт.: sdt, otvody, …)
- Карточка: `woocommerce/single-product.php` + `woocommerce/parts/`
- Архив/реестр: `woocommerce/archive-product.php`
- Каталог: `inc/catalog-*.php` (schema, search Meili+SQL fallback, store, API, filters, taxonomy, render)
- CSS/JS: `assets/css/{base,catalog,category-sdt,product}.css`, `assets/js/{catalog,category-sdt,product}.js`
- Версия ассетов: константа `PROMEN_VERSION` в `functions.php` (сейчас **0.35.7**)

### MU-plugins
| Файл | Назначение |
|------|------------|
| `promen-catalog.php` | ядро каталога / загрузка |
| `promen-catalog-mode.php` | режим «без корзины / КП» |
| `promen-structure.php` | ЧПУ, структура категорий |
| `promen-requests.php` | заявки / форма запроса |

### Поведение каталога (важно)
- Страница категории = **редакционные секции** + **живой реестр** (`#registry`) на том же URL.
- Секция **01 «Реестр исполнений»** — группы типоисполнений + нормативы из канона
  (`promen_render_category_series_registry`), клик → soft-фильтр `?gost=` в реестре.
- Секция **04 «Нормативная база»** — максимум **6** карточек, остальные по кнопке
  «Показать ещё» (`promen_render_category_norms_section`).
- Поиск/фильтры: REST `/wp-json/promen/v1/catalog` + Meilisearch, SQL fallback.
- Термин: **страница категории**, не «лендинг».

### Дизайн-эталон
`design-reference/` — самодостаточный HTML (DINPro, токены CSS):

| HTML | Роль |
|------|------|
| `katalog.html` | архив каталога |
| `sdt.html` | страница категории |
| `product-otvod-90.html` | карточка товара |
| `production.html` | производство |
| `hero-variant-d(roma-glavnaya).html` | главная |

Перенос: разметка/CSS 1:1, данные — из WooCommerce / канона.

---

## 4. WP-CLI: команды PROM-EN

Файл: `scripts/promen-cli.php`.

```text
wp promen import [--file=…] [--category=…] [--limit=N] [--dry-run]
wp promen verify [--file=…] [--sample=N]
wp promen content
wp promen catalog-rebuild
wp promen catalog-verify
wp promen industry-sync
wp promen search-reindex
wp promen search-reconcile
```

Типичный цикл после правок данных:

```bash
docker compose run --rm wpcli promen catalog-rebuild
docker compose run --rm wpcli promen search-reindex
docker compose run --rm wpcli cache flush
```

Импорт CSV по умолчанию смотрит на `/data/products-csv/products_variable.csv`.

Доп. скрипты (ручные, `wp eval-file`):
- `scripts/_enrich_steel_lists.php` — обогащение марок стали по нормативам (карта пока узкая)
- `scripts/_dedup_catalog.php` — дедуп
- `scripts/verify-catalog-stack.sh` — смоук стека

PHPUnit: `phpunit.xml`, тесты в `tests/unit/` (схема, search text, Meili query builder, SQL fallback).

---

## 5. Что делать дальше (миграция всего сайта)

Приоритет сверху вниз:

### A. Витрина (ещё не «сайт целиком»)
1. **Главная** — перенос `hero-variant-d(roma-glavnaya).html` → шаблон WP / фронт-пейдж.
2. **Производство** — `production.html` → page template.
3. Общий **header/footer**, навигация, мобильное меню — сверить с дизайном на всех шаблонах.
4. Страницы нормативов (taxonomy `norm`), если нужны отдельные URL с PDF.

### B. Контент и качество данных
1. Обогащение **сталей** по нормативам (см. план в переписке / расширить `_enrich_steel_lists.php`).
2. Проверка текстов категорий / SEO (Yoast или аналог) — шаблоны мета в `inc/seo.php`.
3. Форма «Запросить КП» — mu-plugin `promen-requests.php`, довести до почты/CRM.

### C. Прод
1. VPS (в брифе: Timeweb, PHP 8.2+, MariaDB, nginx, Redis).
2. HTTPS, крон, бэкапы БД, object cache.
3. Meilisearch в проде (ключ, persistence, `maxTotalHits`).
4. Не коммитить `.docker/`, секреты; `.env` из примера.

### D. Документы в репо (контекст)
| Файл | Зачем |
|------|--------|
| `BRIEF.md` | исходный бриф проекта |
| `PLAYBOOK-CATEGORY.md` | как подключать категорию |
| `CATALOG-AUDIT.md` / `CATALOG-ROADMAP.md` | аудит и чек-лист каталога |
| `PLAN-CATALOG.md` | план унификации каталога |
| `WORK-REMAINING.md` | остаток работ по каталогу; актуализирован 2026-07-28 |

---

## 6. Архитектура каталога (кратко)

```
WooCommerce product (+ variations: steel, supervised)
        │
        ▼
promen_catalog_upsert → таблица wp_promen_catalog_rows (канон)
        │
        ├─► Meilisearch index (поиск/фасеты)
        └─► SQL fallback (если Meili недоступен)
        │
        ▼
REST /wp-json/promen/v1/catalog  +  PHP partials на страницах категорий
```

Ключевые функции:
- `promen_catalog_group_count( $slug )` — счётчик группы  
- `promen_catalog_group_norm_stats( $slug )` — нормативы группы  
- `promen_render_category_series_registry( $slug )` — s01  
- `promen_render_category_norms_section( $slug )` — s04 (≤6 + ещё)  
- `promen_render_category_catalog_embed( $slug )` — живой реестр  

После смены URL/порта — всегда `search-replace` + `cache flush` + при необходимости reindex.

### Модуль «Подбор» (надстройка над каталогом)

Детерминированный подборщик изделия: без ИИ и без внешних сервисов. Переводит
параметры задачи или строку спецификации в параметры запроса каталога и отдаёт
позиции из того же слоя поиска. Марки стали отбираются по `promen_steel_reference()`
(температура, PN, отрасль) и пересекаются с фактическим ассортиментом группы,
поэтому предложить марку, которой нет в каталоге, модуль не может.

```
строка «Отвод 90° 108х4 ст20 ГОСТ 17375»  ИЛИ  поля мастера (T, P, отрасль, DN)
        │
        ▼
promen_selector_parse() → promen_selector_steel_pick() → Promen_Catalog_Query
        │
        ▼
REST /wp-json/promen/v1/select → панель (кнопка в углу) / страница /podbor/
        │
        └─► отмеченные позиции → openRequestModal('solution') → форма КП
```

Второй режим — **запрос об объекте** («строительство котельной», «реконструкция
теплотрассы»). Изделия в такой строке нет, поэтому из неё выводится только
отрасль (`promen_selector_objects()`), а дальше подбор ТРЕБУЕТ параметры среды:
`promen_selector_required_params()` — температура и рабочее давление, без них
марку определить нельзя. Пока они не заданы, счётчики разделов показывают весь
ассортимент отрасли и прямо об этом пишут; после заполнения счётчики
пересчитываются с учётом отобранных марок. Типовых параметров объекта подбор НЕ
подставляет — у каждой котельной они свои. Значения вне пределов справочника
(`promen_selector_limits()`, сейчас −70…+650 °C и до 16 МПа) не дают пустой
выдачи, а прямо отправляют к ТЗ.

**Важно про PN.** Давление ограничивает выбор марок всегда, но позиции сужает
только у групп, где PN нормируется у самой детали (`promen_selector_group_uses_pn()`
— фланцы и арматура). У отвода по ГОСТ 17375 своего PN нет, и фильтр `pn >= X`
в поисковом движке выбрасывал документы с пустым полем: на запрос «1.6 МПа» из
выдачи молча исчезали все отводы и трубы ЖКХ (1641 → 0).

**Раскладка панели — три шага:** «что подбираем» (строка + кликабельные примеры) →
«условия» → «результат». Условия показаны метками, и они же ими управляют:
крестик снимает условие там же, где оно видно (параметр `drop` в REST — часть
условий приходит из строки запроса, и пустым полем их не убрать). Поля ввода
одни на всю панель — сетка «уточнить параметры», свёрнутая по умолчанию;
вторых полей для тех же данных заводить нельзя. Незаполненные обязательные
условия раскрывают сетку, подсвечиваются красным (единственный красный на
сайте, токен `--psel-req` объявлен в `selector.css`) и гасят кнопку подбора.

- Файлы: `inc/selector.php`, `assets/js/selector.js`, `assets/css/selector.css`,
  `page-podbor.php`; тесты — `tests/unit/SelectorParserTest.php`.
- **Два выключателя**, оба переопределяются из wp-config без правки файлов
  (`wp config set ИМЯ значение --raw`, затем `wp promen cache-purge`):
  - `PROMEN_SELECTOR` (по умолчанию `true`) — модуль целиком. `false`: нет
    REST-маршрута и ассетов, `/podbor/` отдаёт 404, сайт в исходном состоянии.
  - `PROMEN_SELECTOR_LAUNCHER` (по умолчанию `false`) — плавающая кнопка на всех
    страницах. Пока выключена, CSS+JS подбора (56 КБ) грузятся ТОЛЬКО на
    `/podbor/`; включение делает их глобальными — это плата за кнопку.
- После переключения любого из них — `wp promen cache-purge`: полностраничный
  кеш держит прежний ответ.
- Форма КП от модуля не зависит: `request-modal.js` не менялся.
- Журнал неразобранных запросов — опция `promen_selector_log` (кольцо на 300 записей):
  что люди пишут и чего парсер не понял.
- Счётчики разделов и карты марок лежат в transient'ах на 15 мин. При проверке
  правок логики их надо снести (`wp transient delete --all`): `wp cache flush`
  их не трогает, и результат будет выглядеть неисправленным.

---

## 7. Известные ограничения

- **Стали:** у части нормативов списки марок неполные / пустые (особенно крепёж).
  Есть задел `_enrich_steel_lists.php` — нужна расширенная карта norm→стали и прогон.
- В **s01** отображение стали раньше бралось с одной позиции (`MAX(steel_display)`);
  для UX можно агрегировать уникальные марки по `norm_slug` без изменения товаров.
- Дубли slug нормативов (`gost-22818` / `gost-22818-1983` / `gost-r-…`) — следы импорта;
  в сериях для фильтра показываются как отдельные facet-значения.
- Не храните в git каталог `.docker/` (БД/Meili/WP core) — только дамп в `handoff/`.

---

## 8. Контакты по контексту

- Локальный эталон у заказчика: `http://localhost:8080`
- Бренд: «Промышленная Энергетика» / PROM-EN — B2B, **без цен и корзины**, CTA «Запросить КП».
- Тон контента: инженерный, без маркетинговой воды; цифры только из фактов
  (`content/aggregates.json`, PDF в `normatives/`).

---

## 9. Чеклист приёмки после развёртывания

- [ ] `docker compose ps` — db healthy, wordpress :8080, meilisearch :7700  
- [ ] `/catalog/` отдаёт 200, есть строки товаров  
- [ ] `/catalog/sdt/otvody/` — секции + реестр, фильтр по ГОСТ из s01 скроллит к `#registry`  
- [ ] s04 показывает ≤6 норм до клика «ещё»  
- [ ] Карточка товара открывается, стали/паспорт на месте  
- [ ] `wp promen catalog-verify` / `search-reindex` без фаталов  
- [ ] Понятны следующие шаги: главная + производство + прод  

Удачи с миграцией. Если что-то в дампе не встаёт — сначала проверьте
`siteurl`/`home` и права на `.docker/wp`.
