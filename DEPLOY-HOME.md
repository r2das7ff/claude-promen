# Развёртывание на второй машине — задание для агента

Ты разворачиваешь локальную копию сайта PROM-EN на новой машине, чтобы
продолжить на ней работу. Это не продакшен и не чистая установка: сайт
уже существует, его контент приезжает дампом БД.

Не пропускай проверки в конце каждого шага — большинство ошибок здесь
тихие: стек поднимается, страницы отдают 200, а каталог пустой.

## Что нужно от машины

Docker Desktop (или Docker Engine + Compose v2), git, git-lfs, ~6 GB
свободного места: клон ~500 MB, база после restore ~1 GB, образы ~2 GB.

## 1. Клон

**Сначала LFS, потом клон.** Дамп БД (24 MB) хранится через Git LFS.
Без установленного LFS вместо него приедет текстовая заглушка на ~130
байт, и restore упадёт с синтаксической ошибкой SQL.

```bash
git lfs install
```

Структура каталогов важна: `docker-compose.yml` монтирует `../normatives`,
то есть репозиторий должен лежать **вложенным** в родительскую папку, а
не быть ей.

```bash
mkdir -p "PROM-EN" && cd "PROM-EN"
git clone https://github.com/r2das7ff/claude-promen.git site
cd site
```

Рабочая ветка — `catalog-variativity-dedup`, она checkout'ится по
умолчанию. **`main` — это baseline-коммит, на нём работы нет.** Проверь:

```bash
git branch --show-current && git log --oneline -1
```

Убедись, что дамп приехал целиком, а не заглушкой — должно быть ~24 MB,
а не 130 байт:

```bash
ls -la handoff/wordpress-db.sql.gz && file handoff/wordpress-db.sql.gz
```

Если там текст вида `version https://git-lfs.github.com/spec/v1` — LFS не
отработал. Выполни `git lfs install && git lfs pull` и проверь снова.

## 2. Стек

```bash
cp .env.example .env
docker compose up -d
```

Первый запуск дольше обычного: образы тянутся, WP-ядро распаковывается в
named volume `wpcore`. Дождись, пока `db` станет healthy:

```bash
docker compose ps
```

Папка `../normatives` (PDF ГОСТов, 155 MB) в репозиторий не входит — она
монтируется в контейнер read-only. Docker создаст её пустой, и стек
поднимется нормально. Каталог и сайт от этого не страдают: нормативы
нужны только для повторного парсинга данных. Если предстоит работа с
нормативной базой — перенеси папку с рабочей машины отдельно.

## 3. Восстановление базы

Порядок именно такой: сначала пересоздать базу, потом залить дамп. Дамп
снят по одной БД и не содержит `CREATE DATABASE`, поэтому имя базы в
команде restore обязательно.

```bash
docker compose exec -T db mariadb -uroot -proot -e "DROP DATABASE IF EXISTS wordpress; CREATE DATABASE wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON wordpress.* TO 'wp'@'%'; FLUSH PRIVILEGES;"
```

```bash
gunzip -c handoff/wordpress-db.sql.gz | docker compose exec -T db mariadb -uroot -proot wordpress
```

Проверь, что контент на месте — должно быть порядка 15 000:

```bash
docker compose exec -T db mariadb -uwp -pwp wordpress -N -e "SELECT COUNT(*) FROM wp_posts WHERE post_type='product' AND post_status='publish';"
```

URL в дампе — `http://localhost:8080`. Если порт занят и ты поднял стек
на другом, замени адреса и сбрось кеш:

```bash
docker compose run --rm wpcli search-replace 'http://localhost:8080' 'http://localhost:ДРУГОЙ_ПОРТ' --all-tables --precise
```

## 4. Поиск

Meilisearch стартует с пустым индексом — его данные лежат в
`.docker/meili`, который в git не хранится. Без переиндексации поиск и
часть фильтров каталога будут молча пустыми.

```bash
docker compose run --rm wpcli promen search-reindex
```

```bash
docker compose run --rm wpcli promen catalog-rebuild
```

```bash
docker compose run --rm wpcli cache flush
```

## 5. Приёмка

Прогони и убедись, что фаталов нет:

```bash
docker compose run --rm wpcli promen catalog-verify
```

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/catalog/
```

Открой и посмотри глазами:

| URL | Что должно быть |
|-----|-----------------|
| `/catalog/` | реестр каталога, сайдбар групп, живые счётчики |
| `/catalog/sdt/otvody/` | секции, реестр, фильтр по ГОСТ скроллит к `#registry` |
| `/normativnaya-baza/` | реестр нормативов |
| карточка любого товара | паспорт, марки стали, «Запросить КП» |

Логин в админку — тот же, что на рабочей машине: учётки приехали в
дампе. Соли в `wp-config` генерируются заново, поэтому старая сессия не
подхватится, нужно войти заново.

Тесты, если понадобятся, требуют отдельной установки — `vendor/` в git
не хранится:

```bash
composer install && composer test
```

## Дальше: регламент работы

Прочитай [SYNC.md](SYNC.md) — там цикл синхронизации между машинами.
Два правила, которые нарушать нельзя:

**Дамп обновляется вместе с кодом.** Контент сайта — товары, категории,
страницы — живёт в базе, а не в файлах. Закоммитил правки темы, но не
снял дамп — на другой машине приедет новый код поверх старого каталога,
и никакой ошибки при этом не будет.

**Работать по очереди.** Restore перезаписывает базу целиком. Правки,
сделанные в админке на машине, где дамп не снимали, пропадут без следа.

Заканчивая сессию:

```bash
docker compose exec -T db mariadb-dump -uwp -pwp --single-transaction --default-character-set=utf8mb4 wordpress | gzip > handoff/wordpress-db.sql.gz
```

```bash
git add -A && git commit && git push
```

## Чего в репозитории нет

| Что | Размер | Зачем может понадобиться |
|-----|--------|--------------------------|
| `../normatives/` | 155 MB | PDF ГОСТов, повторный парсинг нормативной базы |
| `../html/` | 36 MB | макеты дизайна; нужное подмножество есть в `design-reference/` |
| `products-csv/new_products_from_az/az/` | 140 MB | прайсы поставщика, кросс-филл по ГОСТ 6533 (см. DATA-QUALITY-FIX.md) |

Всё это лежит только на рабочей машине. Если задача упирается в них —
не воспроизводи данные заново, а скажи об этом: файлы нужно перенести.

## Состояние проекта

Что сделано и что осталось — в [WORK-REMAINING.md](WORK-REMAINING.md),
[PLAN-CATALOG.md](PLAN-CATALOG.md), [PLAN-PERF.md](PLAN-PERF.md) и
[DATA-QUALITY-FIX.md](DATA-QUALITY-FIX.md). Полное описание архитектуры
и команд `wp promen *` — в [handoff/HANDOFF.md](handoff/HANDOFF.md).
