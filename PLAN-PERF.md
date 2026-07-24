# PROM-EN — план оптимизации каталога и повторный аудит

Дата: 2026-07-24. Основание: аудит скорости (терм-шторм SQL, фронт-балласт, шрифты)
+ архитектурный разбор (синхронизация Meili, версии стека, дублирование шаблонов).

Решения заказчика: рефакторинг шаблонов — **да, полный** (цифры + partials);
Meilisearch — **обновить сейчас**; прод-конфиги — **не готовить**; dev-стек — **починить**.

## Этапы

### Этап 0. Подготовка и baseline
- WIP-коммит текущих правок; `mysqldump` в `.backups/`.
- `scripts/perf-audit.php` (wp eval-file) — повторяемые замеры:
  SQL-запросы и время: живой реестр (embed), REST `/promen/v1/catalog`, s01, s04;
  плюс TTFB страниц (curl) и веса ассетов (JS/шрифты/HTML gzip).
- Снять baseline, прогнать `composer test`.

### Этап 1. Dev-стек
- WP-ядро из bind-mount `./.docker/wp` → named volume (тема и mu-plugins остаются
  bind-mount — live-правки сохраняются). Миграция данных `docker cp`.
- Пин `mariadb:11` → `mariadb:11.8` (LTS).
- Цель: TTFB dev ~4с → сотни мс; проверить до/после.

### Этап 2. Синхронизация Meilisearch (корректность данных)
- `promen_catalog_upsert()` пушит документ в движок (параметр `$sync_search`,
  rebuild-цикл не делает 15k HTTP — после него batch `reindex_all`).
- `ensure_index()` — static-кэш (не дёргать 2 HTTP на каждый upsert).
- Убрать `health()` из горячего пути поиска: движок выбирается без пре-чека,
  fallback по исключению + negative-cache «meili down» 60с.
- WP-Cron ежедневно: `search-reconcile`, при drift>0 — автопереиндексация + лог.

### Этап 3. Терм-шторм (F1+F2+F3 аудита)
- `promen_term_label_map($tax)`: slug→name одним `get_terms` (static + transient
  с `promen_filters_cache_version`). Заменить `get_term_by` в:
  `promen_rest_facet_label`, `promen_catalog_facet_label`, REST steel-enrich,
  `promen_active_summary`, сайдбар, `promen_catalog_group_views`.
- `promen_catalog_group_views_js` (termUrl 33 категорий) — transient.
- `promen_catalog_group_slugs` (get_term_children в горячем пути REST) — transient.
- Цель: реестр 360 → ≤20 SQL; REST 117 → ≤12.

### Этап 4. Фронт
- Dequeue на витрине: jquery, jquery-migrate, woocommerce, sourcebuster-js,
  js-cookie, jquery-blockui, wc-order-attribution, wc-add-to-cart (−139KB, −7 req).
- 7 OTF DINPro → WOFF2 с сабсетом (fonttools; кириллица+латиница+пунктуация+
  стрелки/гео-символы), `@font-face` в base.css → woff2, preload двух критичных
  начертаний (CondBlack — LCP-заголовки, Regular — body). Экономия ~0.9MB.

### Этап 5. Meilisearch 1.11 → 1.48
- Новый образ в compose, чистый volume, `ensure_index` + `promen search-reindex`.
- Смоук: поиск «отвод 90 гост 17375», фасеты steel/gost/angle, `search-reconcile` ok.
- Причина: 1.11 снята с поддержки, CVE-2026-57823/57824 закрыты в 1.47.1/1.48.2.

### Этап 6. Шаблоны категорий (18 шт.)
- Снять эталонные рендеры всех категорий (нормализовать nonce/ver), сохранить.
- Общий каркас → `woocommerce/parts/category-page.php`; уникальный контент →
  конфиги `inc/category-content/<slug>.php`. Тонкие обёртки taxonomy-*.php остаются
  (template hierarchy). Флагманы (sdt/otvody/troyniki 1000+ строк) — допустимы
  собственные секции через конфиг, без форсированной унификации.
- Миграция по одной (пилот bolty), после каждой — HTML-диф с эталоном (только
  whitespace-отличия допустимы).
- Затем отдельным шагом: захардкоженные счётчики hero/HUD → живые
  (`promen_catalog_group_count`, `promen_catalog_group_norm_stats`); диф — только цифры.

### Этап 7. Полный smoke
- `composer test`; `wp promen catalog-verify`, `search-reconcile`;
- curl-обход всех категорий + ключевых страниц (200, без «критическая ошибка»);
- REST-фильтры, карточка, форма КП; визуальная проверка в браузере.

### Этап 8. Повторный аудит
- Прогон `scripts/perf-audit` тем же методом; таблица «до → после» в этот файл;
- обновить память проекта; финальный коммит.

## Прогноз
Реестр: 360 → ≤20 SQL; REST: 117 → ≤12; JS: −139KB и −7 запросов; шрифты: −~0.9MB;
дубли шаблонов: −~10 тыс. строк; данные поиска — консистентны без ручного reindex.
