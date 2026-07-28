# PROM-EN → WordPress: хендофф для полной миграции сайта

Дата пакета: **2026-07-23**  
Ветка: `catalog-variativity-dedup`  
Тема: `promen` **0.20.10** · WP **7.0.1** · WooCommerce **10.9.4**

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
- ~4 GB свободного места (дамп ~23 MB gzip; после restore БД ~1 GB)
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

---

## 3. Что уже сделано (каталог)

### Данные (снимок на дату пакета)
- ~**15 372** опубликованных товара WooCommerce  
- ~**15 371** строк в каноне `wp_promen_catalog_rows`  
- Группы (примерно): СДТ ~5.8k · отводы ~3.2k · тройники ~1.4k · крепёж ~6.7k · …

> На ветке `catalog-variativity-dedup` проходила дедупликация вариативности —
> цифры могут отличаться от более ранних отчётов (~19k). Ориентир — `promen catalog-verify`
> и счётчики на живых страницах.

### Тема `wp-content/themes/promen/`
- Шаблоны категорий: `woocommerce/taxonomy-product_cat-*.php` (18 шт.: sdt, otvody, …)
- Карточка: `woocommerce/single-product.php` + `woocommerce/parts/`
- Архив/реестр: `woocommerce/archive-product.php`
- Каталог: `inc/catalog-*.php` (schema, search Meili+SQL fallback, store, API, filters, taxonomy, render)
- CSS/JS: `assets/css/{base,catalog,category-sdt,product}.css`, `assets/js/{catalog,category-sdt,product}.js`
- Версия ассетов: константа `PROMEN_VERSION` в `functions.php` (сейчас **0.20.10**)

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
| `WORK-REMAINING.md` | ночной чеклист (часть устарела — сверяйте с кодом) |

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
