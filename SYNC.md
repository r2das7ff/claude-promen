# Работа с двух машин

Развёртывание с нуля описано в [handoff/HANDOFF.md](handoff/HANDOFF.md).
Здесь — только то, что касается синхронизации через git.

## Что синхронизируется, а что нет

| | Где живёт | Как переносится |
|---|---|---|
| Тема, mu-plugins, скрипты, тексты | `wp-content/`, `scripts/`, `content/` | git |
| CSV каталога | `products-csv/` | git |
| **Контент сайта** (товары, категории, страницы) | MariaDB | `handoff/wordpress-db.sql.gz` через **Git LFS** |
| PDF нормативов | `../normatives/` (155 MB) | **вне git**, один раз скопировать вручную |
| Прайсы поставщика «az» | `products-csv/new_products_from_az/az/` (140 MB) | **вне git**, только на рабочей машине |
| HTML-макеты дизайна | `../html/` (36 MB) | **вне git**, нужное подмножество лежит в `design-reference/` |
| WP-ядро, `vendor/`, `.docker/` | volumes | пересоздаются локально |

Контент сайта — в базе, не в файлах. Поэтому дамп обновляется вместе с
кодом: закоммитили правки, но не дамп — на второй машине увидите старый
каталог.

## Первый запуск на новой машине

```bash
git lfs install
git clone <URL> "promen site/site"
cd "promen site/site"
cp .env.example .env
docker compose up -d
```

Дальше — restore БД и переиндексация Meilisearch по HANDOFF.md, разделы
«Восстановить БД из дампа» и «Meilisearch».

`git lfs install` — один раз на машину. Без него вместо дампа приедет
текстовая заглушка на ~130 байт, и restore упадёт.

Тесты (`composer test`) требуют `composer install` — `vendor/` в git не
хранится.

## Обычный цикл

Перед тем как уйти от машины:

```bash
docker compose exec -T db mariadb-dump -uwp -pwp --single-transaction --default-character-set=utf8mb4 wordpress | gzip > handoff/wordpress-db.sql.gz
```

```bash
git add -A && git commit && git push
```

На второй машине:

```bash
git pull
```

```bash
gunzip -c handoff/wordpress-db.sql.gz | docker compose exec -T db mariadb -uroot -proot wordpress
```

```bash
docker compose run --rm wpcli promen search-reindex
```

Дамп перезаписывает базу целиком, так что правки в админке на машине,
где вы не сняли дамп, теряются. Работать по очереди, а не параллельно.

## Ограничения

Дамп ~23 MB на версию, бесплатная квота LFS — 1 GB хранилища и 1 GB
трафика в месяц. Это примерно 40 коммитов дампа. Дальше — чистить
историю LFS или платить за квоту.

Прайсы поставщика `new_products_from_az/az/` (140 MB) вычищены из
истории перед первым push: сырьё уже импортированного каталога, ни один
скрипт репозитория их не читает. Лежат только на рабочей машине. Для
кросс-филла по ГОСТ 6533 (см. DATA-QUALITY-FIX.md) брать оттуда.

Крупнейший файл в репозитории теперь `products_variable.csv.bak_*`
(43 MB) при лимите GitHub в 100 MB. Запас есть, но `.bak_*` — снимок
от 22 июля, кандидат на удаление при следующей чистке.
