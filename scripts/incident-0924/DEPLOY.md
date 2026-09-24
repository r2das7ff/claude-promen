# Защита от перебора фасетов и отказа БД — выкладка 24.09.2026

Разбор инцидента — [INCIDENT-2026-09-24.md](../../INCIDENT-2026-09-24.md).
Прод — `promen-prod`, докрут `~/prom-en.com/public_html`. **Одно SSH-подключение
за раз**: параллельные сессии Timeweb расценивает как злоупотребление.

Выложено 24.09.2026 ~10:20 МСК. Отпечаток темы не меняется (ни одного файла
темы), поэтому полностраничный кеш при выкладке **не сбрасывается**.

| Файл | Что делает |
|---|---|
| `wp-content/advanced-cache.php` | проверка браузера для сочетаний фасетов; кеш для одиночного фасета и для адресов с utm/yclid; предохранитель базы — не больше 4 генераций одновременно (слоты `wp-content/cache/promen-slots/`, выложен вторым заходом ~10:45) |
| `wp-content/db-error.php` (новый) | при отказе MySQL: устаревшая копия из кеша, заявки в `~/prom-en.com/leads-spool/`, 503 вместо 500 |
| `wp-content/mu-plugins/promen-health.php` (новый) | `/wp-json/promen/v1/health`, уведомление и `wp promen leads-spool` |
| `.htaccess`, блок `promen-guard` | Bytespider, xmlrpc.php, readme/license, `*.bak/*.sql/*.log…` → 403 до PHP |

## 1. Заливка и проверка

```bash
tar cf /tmp/pe_guard.tar wp-content/advanced-cache.php wp-content/db-error.php wp-content/mu-plugins/promen-health.php scripts/incident-0924/htaccess-guard.conf
```

```bash
ssh promen-prod 'rm -rf ~/incident-0924 && mkdir -p ~/incident-0924 && cd ~/incident-0924 && tar xf -' < /tmp/pe_guard.tar
```

```bash
ssh promen-prod 'cd ~/incident-0924 && for V in 7.4 8.2 8.3; do for f in wp-content/advanced-cache.php wp-content/db-error.php wp-content/mu-plugins/promen-health.php; do /opt/php$V/bin/php -l $f; done; done'
```

## 2. Бэкап и установка

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && tar cf ../backup-2026-09-24-guard.tar .htaccess wp-content/advanced-cache.php'
```

Файлы — через `.new` и `mv`, чтобы параллельный запрос не прочитал половину.
Блок `.htaccess` вставляется перед `# BEGIN WordPress` (WordPress при сбросе
правил переписывает только свой блок, чужие не трогает):

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && S=~/incident-0924 && for f in wp-content/db-error.php wp-content/mu-plugins/promen-health.php wp-content/advanced-cache.php; do cp -p $S/$f $f.new && mv $f.new $f; done && grep -q "BEGIN promen-guard" .htaccess || { awk -v G="$S/scripts/incident-0924/htaccess-guard.conf" "/^# BEGIN WordPress/ && !d { while ((getline l < G) > 0) print l; print \"\"; d=1 } { print }" .htaccess > .htaccess.new && mv .htaccess.new .htaccess; }'
```

## 3. Проверка

```bash
curl -s -o /dev/null -w "%{http_code}\n" "https://prom-en.com/catalog/?gost=gost-10705-1980%2Cgost-17379-2001"
```
Ожидаем `403` (заголовок `X-Promen-Guard: challenge`). В браузере тот же адрес
открывается после мгновенной перезагрузки.

```bash
curl -s -w "  [%{http_code}]\n" https://prom-en.com/wp-json/promen/v1/health
```
Ожидаем `{"ok":true,…}` и `200`, при лежащей базе — `503`.

Главная, `/catalog/`, карточки — `200`; `/xmlrpc.php`, `/readme.html`,
`/wp-config.php.bak` — `403`.

## Откат

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && tar xf ../backup-2026-09-24-guard.tar && rm -f wp-content/db-error.php wp-content/mu-plugins/promen-health.php'
```

Откат возвращает прежние `.htaccess` и `advanced-cache.php` целиком.
Заявки из `~/prom-en.com/leads-spool/` при откате не пропадают.

Снять только предохранитель базы, оставив остальное, — `advanced-cache.php`
из первого захода:

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && tar xf ../backup-2026-09-24-guard2.tar'
```

Число слотов меняется без правки кода: `define( 'PROMEN_RENDER_SLOTS', 6 );`
в `wp-config.php`.
