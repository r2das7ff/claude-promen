# Выкладка правок ОТК на прод

Коммит `0cdd5a3`. Прод — `prom-en.com`, докрут `/home/p/promen9917/prom-en.com/public_html`,
`wp` в `/usr/local/bin/wp`, CLI-PHP `/opt/php8.3/bin/php`.

**Одно SSH-подключение за раз.** Каждый шаг — отдельная команда, следующую
запускать только после завершения предыдущей. Параллельные сессии Timeweb
расценивает как злоупотребление.

## 0. Дрейф — проверено 10.09.2026

`wp-content/themes/promen/inc/product-data.php` на проде = `12faa50` (предыдущий
коммит), правок коллеги нет. `wp-content/mu-plugins/promen-catalog-moves*.php` и
`assets/img/products/types/` на проде отсутствуют — файлы новые.

Если между подготовкой и выкладкой пройдёт время — повторить:

```bash
ssh promen-prod 'cd prom-en.com/public_html && md5sum wp-content/themes/promen/inc/product-data.php'
```

Ожидаем `ec88e804f42d2728dbef41c718cea7da`. Другое значение — сначала забрать
чужую правку в git, потом выкладывать.

## 1. Бэкап

База меняется у 739 карточек, три удаляются — дамп обязателен.

```bash
ssh promen-prod 'cd prom-en.com/public_html && wp db export ~/backup-20260910-pre-otk.sql'
```

```bash
ssh promen-prod 'cd prom-en.com/public_html && cp -p wp-content/themes/promen/inc/product-data.php ~/backup-20260910-product-data.php'
```

## 2. Заливка файлов

```bash
ssh promen-prod 'cat > prom-en.com/deploy.tar' < scripts/otk-fix/deploy.tar
```

```bash
ssh promen-prod 'cd prom-en.com && md5sum -c -' < scripts/otk-fix/deploy.tar.md5
```

```bash
ssh promen-prod 'cd prom-en.com/public_html && tar xf ../deploy.tar'
```

```bash
ssh promen-prod 'cd prom-en.com/public_html && md5sum -c --quiet -' < scripts/otk-fix/deploy-files.md5
```

```bash
ssh promen-prod 'cd prom-en.com/public_html && php -l wp-content/themes/promen/inc/product-data.php && php -l wp-content/mu-plugins/promen-catalog-moves.php && php -l wp-content/mu-plugins/promen-catalog-moves-map.php'
```

## 3. Данные

Скрипт идемпотентен и работает от состояния базы, а не от локального слепка, —
поэтому данные не переносим дампом, а пересчитываем на месте.

```bash
ssh promen-prod 'cat > ~/otk-fix.php' < scripts/otk-fix/fix.php
```

```bash
ssh promen-prod 'cd prom-en.com/public_html && wp eval-file ~/otk-fix.php dry'
```

Сверить числа с локальным прогоном: 739 изменённых, 29 смен слага, 3 удаления.
Расхождение — остановиться и разобраться, а не применять.

```bash
ssh promen-prod 'cd prom-en.com/public_html && wp eval-file ~/otk-fix.php apply'
```

Прогнать `apply` **дважды**: первый проход снимает флаг равнопроходности, второй
дочищает заголовки и обозначения, где пара осталась. Локально сходилось за
четыре прохода; признак завершения — «изменено: 0».

```bash
ssh promen-prod 'rm -f ~/otk-fix.php ~/report.tsv ~/needs-size.tsv ~/moves.tsv ~/gone.tsv'
```

## 4. Кеш и правила

```bash
ssh promen-prod 'cd prom-en.com/public_html && wp promen cache-purge && wp rewrite flush'
```

## 5. Проверка

```bash
curl -sS -o /dev/null -w "%{http_code} -> %{redirect_url}\n" "https://prom-en.com/catalog/sdt/zaglushki/zaglushka-20h1-5-ost-24-125-22-1989/"
```
Ожидаем `301 -> …/bobyshka-20h1-5-ost-24-125-22-1989/`.

```bash
curl -sS -o /dev/null -w "%{http_code}\n" "https://prom-en.com/catalog/sdt/troyniki/troynik-1320h14-ost-34-10-764-1997/"
```
Ожидаем `410`.

```bash
curl -sS "https://prom-en.com/catalog/sdt/zaglushki/bobyshka-20h1-5-ost-24-125-22-1989/?x=1" | grep -o "products/types/[a-z-]*\.webp"
```
Ожидаем `products/types/zaglushki-bb-bobyshka.webp`.

## 6. Переобход

29 адресов сменились, 3 удалены. Отправить их в IndexNow и в Яндекс.Вебмастер
(см. скилл `seo-yandex`); список — `moves.tsv` и `gone.tsv`.

## Откат

```bash
ssh promen-prod 'cd prom-en.com/public_html && wp db import ~/backup-20260910-pre-otk.sql'
```

```bash
ssh promen-prod 'cd prom-en.com/public_html && cp -p ~/backup-20260910-product-data.php wp-content/themes/promen/inc/product-data.php && rm -f wp-content/mu-plugins/promen-catalog-moves.php wp-content/mu-plugins/promen-catalog-moves-map.php && wp promen cache-purge'
```

Снимки в `assets/img/products/types/` при откате можно оставить: без правки темы
они просто не используются.
