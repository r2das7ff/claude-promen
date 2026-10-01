---
name: pexels
description: "Стоковые фото и видео с Pexels для сайта PROM-EN через API: поиск с контакт-листом превью, скачивание с кропом и WebP, видеофон с пережатием ffmpeg и постером, журнал авторов и лицензии. Использовать, когда странице, статье, баннеру, og-превью или фону секции нужна картинка или ролик, которых нет в своих фото завода, или когда пользователь просит подобрать изображение или видео. Триггеры: Pexels, стоковое фото, сток, картинка для страницы, фото для статьи, фон, обложка, hero, видеофон, ролик на фон, подобрать изображение."
user-invocable: true
argument-hint: "[что нужно: фото/видео и для какой страницы]"
---

# Pexels: стоковые фото и видео

Инструмент — `scripts/media/pexels.py` (Python, только stdlib + Pillow,
для видео — ffmpeg из scoop). Скачанное ложится в `site/media-src/pexels/`,
каждый файл записывается в `credits.jsonl` там же: id, страница, автор,
лицензия. Бинарники в git не едут, журнал едет.

## Доступ

Ключ — строкой в `site/.env` (в git не едет, между машинами его везёт OneDrive):

```
PEXELS_API_KEY=...
```

Получает владелец сам: вход на pexels.com → https://www.pexels.com/api/ →
короткая заявка (название, описание, сайт) → ключ на странице «Your API Key».
**Ключ в чат не просить и в код не вписывать.** Проверка: `python scripts/media/pexels.py limits`.

Лимит: по документации 200 запросов в час; заголовки ответа показывают
месячный остаток — у нашего ключа 25 000, сброс 25-го числа. Превью и
скачивание с `images.pexels.com` / `videos.pexels.com` в лимит не входят.

## Команды

Запускать из `site/`.

```bash
# поиск фото: таблица + контакт-лист превью с номерами и id
python scripts/media/pexels.py photos "industrial pipeline" --orientation landscape --per-page 16 --sheet <scratchpad>/sheet.jpg
python scripts/media/pexels.py photos "сварка труб"            # кириллица → locale ru-RU сам
python scripts/media/pexels.py photos "steel" --color gray --size large

# видео: в таблице длительность и лучшие файлы по качеству (uhd/hd/sd)
python scripts/media/pexels.py videos "power plant" --orientation landscape --max-duration 30 --sheet <scratchpad>/v.jpg
python scripts/media/pexels.py video 1234567 --sheet <scratchpad>/frames.jpg   # раскадровка одного ролика

# одно фото / коллекции (если пользователь собрал подборку на Pexels)
python scripts/media/pexels.py photo 2014422
python scripts/media/pexels.py collections
python scripts/media/pexels.py collection <id> --sheet <scratchpad>/c.jpg

# скачать фото: пара WebP + JPEG, как принято в теме
python scripts/media/pexels.py download photo 2014422 --width 1920 --webp --jpg --name hero-pipes
python scripts/media/pexels.py download photo 2014422 --width 800  --webp --jpg --name hero-pipes-sm
python scripts/media/pexels.py download photo 2014422 --crop 1200x630 --cy 0.4 --webp --jpg --name og-stati
python scripts/media/pexels.py download photo 2014422                  # оригинал как есть

# скачать видео: --web = H.264 без звука, faststart, постер WebP из первого кадра
python scripts/media/pexels.py download video 1234567 --max-width 1920 --web --trim 12 --crf 26
```

`--json` у любой команды чтения — сырой ответ API.

## Как подбирать

1. Запрос — **по-английски**: русская выдача у Pexels беднее в разы.
   Для профиля завода: `industrial pipeline`, `steel pipes`, `pipe welding`,
   `power plant`, `thermal power station`, `boiler room`, `oil refinery`,
   `metalworking`, `factory interior`, `steel structure`, `cooling tower`.
2. Всегда с `--sheet` в скретчпад, затем открыть лист через Read — один
   взгляд вместо десятка ссылок. Номер на листе = номер строки в таблице.
3. Перед скачиванием — `photo <id>`: размер оригинала, автор, страница.
4. Скачанный результат открыть через Read и проверить кроп глазами.
5. Показать пользователю 2–3 варианта, прежде чем ставить в тему.

## Чего нельзя

- **Выдавать сток за своё.** Чужой завод, станция или цех не может стоять
  подписью к «нашему производству», к объектам из «Проектов» или к
  конкретному ГОСТ-изделию из каталога. Сток — атмосфера: фоны, статьи,
  обложки, общие разделы. Фото изделий — только свои (`../product-photos-src/`).
- Узнаваемые лица, логотипы, бренды и надписи на технике — не брать: лицензия
  не даёт права на чужие товарные знаки и не подразумевает согласия людей
  на рекламу.
- Хотлинкать с `images.pexels.com` — только своя копия в теме.

Лицензия Pexels: бесплатно, можно менять, указывать автора не обязательно.
Нельзя продавать немодифицированные копии и выкладывать их как свой сток.
Журнал `credits.jsonl` и есть наш учёт источника — не удалять строки.

## Из media-src на сайт

- Картинки темы — `wp-content/themes/promen/assets/img/<раздел>/`, разметка
  как везде в теме: `<picture>` с WebP-источником и JPEG в `<img>`,
  `width`/`height` оригинала, `loading="lazy"` (кроме первого экрана),
  `alt` по-русски и по сути страницы, а не перевод alt с Pexels.
- Ширины: фон во всю ширину — 1920 + 800 (`-sm`), карточка — 800, og — 1200×630.
- Видео — `assets/video/<раздел>/`, `<video muted autoplay loop playsinline
  preload="none" poster="…-poster.webp">`. Ролик на фон держать до ~2–3 МБ:
  `--trim` и `--crf 28` раньше, чем уменьшать ширину.
- Правка CSS/JS/шаблонов — правила деплоя и сброс кеша страниц как обычно.

## Грабли

- **TLS:** штатный `ssl` Python на этой машине падает на pexels.com с
  `certificate has expired` (цепочка Let's Encrypt YE2). CLI ходит через
  `certifi` — если его нет, `pip install certifi`.
- CDN понимает `w`, `h`, `fit=crop`, `fm=webp|avif`, но **игнорирует `q`** —
  поэтому качество задаёт локальное перекодирование Pillow, а не URL.
- **Размеры файлов видео в API бывают враньём:** у 11795041 файл «hd 1920×1080»
  на деле 1280×720. CLI сверяет через ffprobe, пишет предупреждение и кладёт
  в журнал реальный размер — на фон во всю ширину такой ролик не годится.
- У роликов последних лет `quality` у файлов пустой — уровень uhd/hd/sd CLI
  выводит из ширины (≥2560 / ≥1280 / меньше).
- Встроенный шрифт Pillow — только ASCII, подписи листа на латинице намеренно.
- Ответы поиска по одному запросу меняются со временем — id фиксировать в
  журнале, а не надеяться найти «ту же» картинку повторным поиском.
