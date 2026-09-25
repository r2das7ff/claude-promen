# «Контакты», сертификат ТР ТС 032, юрлицо — выкладка 25.09.2026

Коммиты `eed60e5` (контакты: лист реквизитов с банком, карта предприятия,
форма; гарантии над формами) и `43ce7ea` (сертификат вместо декларации,
юрлицо в Organization/llms.txt, площадка — Челябинск). 44 файла темы, список —
`git diff --name-only c80230e 43ce7ea -- wp-content`. База не менялась.

Прод — `promen-prod`, докрут `~/prom-en.com/public_html`. **Одно SSH-подключение
за раз**; параллельные сессии Claude на время выкладки предупреждены.

Выложено 25.09.2026 ~09:45 МСК.

## 1. Дрейф

Прод сверен с версиями файлов до правок (`c80230e`) — расхождений нет,
`object-cache.php` на проде нет:

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && md5sum -c - | grep -v ": OK$"' < base.md5
```

`base.md5` — `git show c80230e:<путь> | md5sum` по каждому файлу списка.

## 2. Заливка в стейджинг

Архив собран из коммита (`git archive HEAD <пути>`), а не из рабочего дерева:
в дереве лежали незакоммиченные правки других сессий. В архиве — `paths.txt`
и `files.md5`.

```bash
ssh promen-prod 'set -e; rm -rf ~/deploy-0925-contacts; mkdir -p ~/deploy-0925-contacts; cd ~/deploy-0925-contacts; tar xf -; md5sum -c --quiet files.md5 && echo MD5_OK; for V in 7.4 8.2 8.3; do while IFS= read -r f; do case "$f" in *.php) /opt/php$V/bin/php -l "$f" >/dev/null 2>&1 || echo "LINT_FAIL php$V $f";; esac; done < paths.txt; done' < deploy-0925.tar
```

## 3. Бэкап и установка

Каждый файл — через `.new` и `mv`; CSS/JS после установки получают свежий
mtime, чтобы сменилась `PROMEN_ASSET_VER` (она считается по mtime ассетов).

```bash
ssh promen-prod 'set -e; cd ~/prom-en.com/public_html; S=~/deploy-0925-contacts; B=../backup-2026-09-25-contacts-cert.tar; test ! -e "$B"; tar cf "$B" -T "$S/paths.txt"; while IFS= read -r f; do cp -p "$S/$f" "$f.new" && mv "$f.new" "$f"; done < "$S/paths.txt"; grep -E "\.(css|js)$" "$S/paths.txt" | xargs touch; md5sum -c --quiet "$S/files.md5" && echo INSTALLED'
```

**Права.** На хостинге umask 0077: файлы темы — 0600, а `contacts.css`,
`front.css`, `nb.js` — 0700; сайт так работает (веб-сервер от имени
пользователя). Делать `chmod 644` не нужно: это только открывает файлы на
чтение соседям по серверу. При этой выкладке 644 был выставлен по ошибке и
сразу возвращён к исходным правам по списку из бэкапа.

## 4. Кеш

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && /opt/php8.3/bin/php /usr/local/bin/wp promen cache-purge'
```

Транзиенты FAQ (`promen_faq_*`) вручную не чистим: ключ — md5 от пути и
mtime файла, новый файл сам даёт новый ключ.

## 5. Проверка

Без SSH, по HTTP: `/contacts/` — лист реквизитов с банком, `id="request"`,
галочки над формой; `/catalog/otvody/` — «Сертификат» в HUD и «Производственная
площадка — Челябинск»; главная — фильтра «Декларации» нет, в JSON-LD
`"name":"ООО Завод «Промышленная Энергетика»"`; `/llms.txt` — та же форма в
первой строке; `/production/` — «СЕРТИФИКАТ RU С-RU.АБ53…»; `/catalog/` —
«Нужен ли сертификат…». Все проверки прошли.

## Откат

Только если после 25.09 эти 44 файла никто не выкладывал — иначе сначала
сверить md5 с `~/deploy-0925-contacts/files.md5`, чтобы не затереть чужую
выкладку:

```bash
ssh promen-prod 'cd ~/prom-en.com/public_html && md5sum -c --quiet ~/deploy-0925-contacts/files.md5 && tar xpf ../backup-2026-09-25-contacts-cert.tar && /opt/php8.3/bin/php /usr/local/bin/wp promen cache-purge'
```

Откат возвращает и `inc/seo.php` к `1257717` — с прежней формой названия
юрлица.
