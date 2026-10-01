#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Pexels API CLI для PROM-EN: поиск и скачивание фото и видео.

Ключ: --key > $PEXELS_API_KEY > site/.env (строка PEXELS_API_KEY=...).
Скачанное ложится в site/media-src/pexels/, источник и автор каждого файла
дописываются в credits.jsonl там же (он версионируется, бинарники — нет).

TLS — через certifi: штатный ssl на этой машине не собирает цепочку
Let's Encrypt YE2 у pexels.com и падает с «certificate has expired».
Запросы — urllib, а не curl: локальный curl портит кириллицу в запросе.

Лимит API — 200 запросов в час и 20 000 в месяц. Картинки и превью с
images.pexels.com в лимит не входят, это CDN.
"""
import argparse
import datetime
import io
import json
import os
import re
import shutil
import ssl
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

API = "https://api.pexels.com"
SITE = Path(__file__).resolve().parents[2]
ENV_FILE = SITE / ".env"
OUT_DIR = SITE / "media-src" / "pexels"
LEDGER = "credits.jsonl"
UA = "promen-pexels-cli/1.0 (+https://prom-en.com)"
LICENSE = "Pexels License — https://www.pexels.com/license/"

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")
    sys.stderr.reconfigure(encoding="utf-8")


def _ssl_context():
    try:
        import certifi
        return ssl.create_default_context(cafile=certifi.where())
    except ImportError:
        return ssl.create_default_context()


# build_opener сам добавляет ProxyHandler из HTTPS_PROXY — VPN-прокси работает.
OPENER = urllib.request.build_opener(urllib.request.HTTPSHandler(context=_ssl_context()))
RATE = {}


class PexelsError(Exception):
    pass


# ---------------------------------------------------------------- доступ

def read_env_key():
    if not ENV_FILE.exists():
        return None
    for line in ENV_FILE.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if line.startswith("PEXELS_API_KEY="):
            return line.split("=", 1)[1].strip().strip("'\"") or None
    return None


def get_key(cli_key=None):
    key = cli_key or os.environ.get("PEXELS_API_KEY") or read_env_key()
    if not key:
        raise PexelsError(
            "Нет ключа Pexels. Добавьте в site/.env строку PEXELS_API_KEY=<ключ> "
            "(ключ — на https://www.pexels.com/api/key/ после входа в аккаунт)."
        )
    return key


def api(key, path, params=None):
    url = API + path
    if params:
        clean = {k: v for k, v in params.items() if v not in (None, "")}
        if clean:
            url += "?" + urllib.parse.urlencode(clean)
    req = urllib.request.Request(url, headers={
        "Authorization": key, "User-Agent": UA, "Accept": "application/json"})
    try:
        with OPENER.open(req, timeout=60) as resp:
            for h in ("X-Ratelimit-Limit", "X-Ratelimit-Remaining", "X-Ratelimit-Reset"):
                if resp.headers.get(h):
                    RATE[h] = resp.headers[h]
            return json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", "replace")
        try:
            msg = json.loads(body).get("message") or json.loads(body).get("error") or body
        except ValueError:
            msg = body[:300]
        if e.code == 401:
            raise PexelsError(f"401: {msg}. Проверьте PEXELS_API_KEY в site/.env.")
        if e.code == 429:
            reset = e.headers.get("X-Ratelimit-Reset")
            when = _fmt_reset(reset) if reset else "неизвестно когда"
            raise PexelsError(f"429: лимит запросов исчерпан, сброс {when}.")
        if e.code == 404:
            raise PexelsError(f"404: не найдено ({path}).")
        raise PexelsError(f"HTTP {e.code}: {msg}")
    except urllib.error.URLError as e:
        raise PexelsError(f"Сеть: {e.reason}")


def fetch(url, timeout=120):
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with OPENER.open(req, timeout=timeout) as resp:
        return resp.read()


def stream_to(url, dest, label=""):
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with OPENER.open(req, timeout=300) as resp, open(dest, "wb") as fh:
        total = int(resp.headers.get("Content-Length") or 0)
        done = 0
        while True:
            chunk = resp.read(1 << 20)
            if not chunk:
                break
            fh.write(chunk)
            done += len(chunk)
            if total:
                print(f"\r  {label} {done / 1e6:.1f} / {total / 1e6:.1f} МБ",
                      end="", file=sys.stderr)
    if total:
        print(file=sys.stderr)


def _fmt_reset(ts):
    try:
        return datetime.datetime.fromtimestamp(int(ts)).strftime("%d.%m %H:%M")
    except (TypeError, ValueError):
        return str(ts)


def rate_note(force=False):
    if not RATE:
        return
    left = int(RATE.get("X-Ratelimit-Remaining", 0) or 0)
    if force or left < 20:
        print(f"[лимит] осталось {left} из {RATE.get('X-Ratelimit-Limit')}, "
              f"сброс {_fmt_reset(RATE.get('X-Ratelimit-Reset'))}", file=sys.stderr)


# ---------------------------------------------------------------- вывод

def has_cyrillic(text):
    return bool(re.search("[а-яА-ЯёЁ]", text or ""))


def slug_from_url(url):
    """https://www.pexels.com/photo/brown-rocks-2014422/ -> brown-rocks"""
    tail = (url or "").rstrip("/").rsplit("/", 1)[-1]
    tail = re.sub(r"-?\d+$", "", tail)
    return tail[:60].strip("-") or "pexels"


def title_of(item):
    return (item.get("alt") or "").strip() or slug_from_url(item.get("url")).replace("-", " ")


def cut(text, n):
    text = text or ""
    return text if len(text) <= n else text[: n - 1] + "…"


def tier_of(f):
    """У новых роликов Pexels отдаёт quality = null — выводим из ширины."""
    if f.get("quality"):
        return f["quality"]
    w = f.get("width") or 0
    return "uhd" if w >= 2560 else "hd" if w >= 1280 else "sd"


def video_tiers(item):
    files = [f for f in item.get("video_files") or [] if f.get("width")]
    best = {}
    for f in files:
        q = tier_of(f)
        if f["width"] > best.get(q, 0):
            best[q] = f["width"]
    order = {"uhd": 0, "hd": 1, "sd": 2}
    return " ".join(f"{q}:{w}" for q, w in sorted(best.items(), key=lambda kv: order.get(kv[0], 9)))


def print_photos(items):
    for i, p in enumerate(items, 1):
        size = f"{p['width']}×{p['height']}"
        print(f"{i:>2}. {p['id']:<10} {size:<11} {cut(p.get('photographer'), 22):<22} "
              f"{cut(title_of(p), 70)}")


def print_videos(items):
    for i, v in enumerate(items, 1):
        size = f"{v['width']}×{v['height']}"
        author = (v.get("user") or {}).get("name")
        print(f"{i:>2}. {v['id']:<10} {size:<11} {v.get('duration', '?'):>3}с "
              f"{cut(author, 20):<20} {cut(title_of(v), 48):<48} [{video_tiers(v)}]")


def print_page(res, key, kind):
    items = res.get(key) or []
    if kind == "photo":
        print_photos(items)
    else:
        print_videos(items)
    total = res.get("total_results")
    nxt = " · есть следующая (--page)" if res.get("next_page") else ""
    if total is not None:
        print(f"— страница {res.get('page')}, показано {len(items)} из {total}{nxt}")


# ---------------------------------------------------------------- контакт-лист

def make_sheet(items, path, cols=4, cell=(320, 213), frames=False):
    """Склеивает превью в одну картинку с номерами и id — выбрать глазами за один взгляд."""
    from PIL import Image, ImageDraw, ImageFont, ImageOps

    def thumb_url(it):
        if frames:
            return it["picture"]
        if "src" in it:
            return it["src"]["medium"]
        return it.get("image")

    def load(it):
        try:
            img = Image.open(io.BytesIO(fetch(thumb_url(it), timeout=60)))
            return ImageOps.contain(img.convert("RGB"), cell)
        except Exception:
            return None

    with ThreadPoolExecutor(max_workers=8) as pool:
        thumbs = list(pool.map(load, items))

    label_h = 24
    rows = (len(items) + cols - 1) // cols
    sheet = Image.new("RGB", (cols * cell[0], rows * (cell[1] + label_h)), (24, 26, 30))
    draw = ImageDraw.Draw(sheet)
    try:
        font = ImageFont.load_default(size=15)
    except TypeError:
        font = ImageFont.load_default()
    for n, (it, th) in enumerate(zip(items, thumbs)):
        x = (n % cols) * cell[0]
        y = (n // cols) * (cell[1] + label_h)
        if th is not None:
            sheet.paste(th, (x + (cell[0] - th.width) // 2, y + (cell[1] - th.height) // 2))
        # Встроенный шрифт Pillow — только ASCII: «×» и кириллица выходят квадратами.
        if frames:
            label = f"#{it.get('nr', n)}"
        else:
            label = f"{n + 1}. {it['id']}  {it['width']}x{it['height']}"
            if "duration" in it:
                label += f"  {it['duration']}s"
        draw.text((x + 6, y + cell[1] + 4), label, fill=(235, 235, 235), font=font)
    path = Path(path)
    path.parent.mkdir(parents=True, exist_ok=True)
    sheet.save(path, "JPEG", quality=85)
    print(f"[лист] {path}", file=sys.stderr)


# ---------------------------------------------------------------- скачивание

def ledger_add(out_dir, entry):
    """Одна строка на набор файлов: повторное скачивание заменяет запись, а не дублирует."""
    out_dir.mkdir(parents=True, exist_ok=True)
    path = out_dir / LEDGER
    rows = []
    if path.exists():
        for line in path.read_text(encoding="utf-8").splitlines():
            if line.strip():
                row = json.loads(line)
                if not set(row.get("files") or []) & set(entry["files"]):
                    rows.append(row)
    rows.append(entry)
    path.write_text("".join(json.dumps(r, ensure_ascii=False) + "\n" for r in rows), encoding="utf-8")


def parse_size(text):
    m = re.fullmatch(r"(\d+)[xх×](\d+)", text or "")
    if not m:
        raise PexelsError(f"--crop ждёт ШxВ, например 1200x630, а получил «{text}».")
    return int(m.group(1)), int(m.group(2))


def download_photo(key, args):
    from PIL import Image, ImageOps

    p = api(key, f"/v1/photos/{args.id}")
    out_dir = Path(args.out) if args.out else OUT_DIR
    out_dir.mkdir(parents=True, exist_ok=True)
    stem = f"pexels-{p['id']}-{args.name or slug_from_url(p['url'])}"
    process = args.webp or args.jpg or args.width or args.crop

    raw = fetch(p["src"]["original"], timeout=300)
    saved = []
    if not process:
        ext = os.path.splitext(urllib.parse.urlparse(p["src"]["original"]).path)[1] or ".jpg"
        saved.append(out_dir / (stem + ext))
        saved[0].write_bytes(raw)
    else:
        img = ImageOps.exif_transpose(Image.open(io.BytesIO(raw))).convert("RGB")
        if args.crop:
            w, h = parse_size(args.crop)
            img = ImageOps.fit(img, (w, h), Image.LANCZOS, centering=(args.cx, args.cy))
        elif args.width and img.width > args.width:
            img = img.resize((args.width, round(img.height * args.width / img.width)), Image.LANCZOS)
        # В теме картинки идут парой <picture>: WebP + JPEG-запаска, отсюда --webp --jpg.
        if args.webp:
            saved.append(out_dir / (stem + ".webp"))
            img.save(saved[-1], "WEBP", quality=args.quality or 80, method=6)
        if args.jpg or not args.webp:
            saved.append(out_dir / (stem + ".jpg"))
            img.save(saved[-1], "JPEG", quality=args.quality or 85, optimize=True, progressive=True)
        print(f"  {img.width}×{img.height}", file=sys.stderr)

    ledger_add(out_dir, {
        "date": datetime.date.today().isoformat(), "type": "photo", "id": p["id"],
        "files": [f.name for f in saved], "page": p["url"], "author": p.get("photographer"),
        "author_url": p.get("photographer_url"), "alt": p.get("alt"),
        "source": f"{p['width']}×{p['height']}", "license": LICENSE,
    })
    for f in saved:
        print(f"{f}  ({f.stat().st_size / 1024:.0f} КБ)")


def pick_video_file(v, tier=None, max_width=1920):
    files = [f for f in v.get("video_files") or []
             if f.get("width") and (f.get("file_type") or "").endswith("mp4")]
    if tier:
        files = [f for f in files if tier_of(f) == tier] or files
    fit = [f for f in files if f["width"] <= max_width]
    pool = fit or files
    if not pool:
        raise PexelsError(f"У видео {v['id']} нет mp4-файлов.")
    return max(pool, key=lambda f: (f["width"], f.get("fps") or 0)) if fit else min(pool, key=lambda f: f["width"])


def probe_size(path):
    if not shutil.which("ffprobe"):
        return None
    res = subprocess.run(["ffprobe", "-v", "error", "-select_streams", "v:0", "-show_entries",
                          "stream=width,height", "-of", "csv=p=0", str(path)],
                         capture_output=True, text=True)
    try:
        w, h = res.stdout.strip().split(",")[:2]
        return int(w), int(h)
    except ValueError:
        return None


def download_video(key, args):
    v = api(key, f"/videos/videos/{args.id}")
    out_dir = Path(args.out) if args.out else OUT_DIR
    out_dir.mkdir(parents=True, exist_ok=True)
    stem = f"pexels-{v['id']}-{args.name or slug_from_url(v['url'])}"
    f = pick_video_file(v, args.tier, args.max_width)
    src = out_dir / (stem + f"-src-{f['width']}.mp4") if args.web else out_dir / (stem + ".mp4")
    print(f"  файл: {tier_of(f)} {f['width']}×{f['height']} {f.get('fps') or '?'}fps",
          file=sys.stderr)
    stream_to(f["link"], src, label=src.name)
    real = probe_size(src)
    if real and real[0] != f["width"]:
        print(f"  ! API обещал {f['width']}×{f['height']}, в файле {real[0]}×{real[1]}", file=sys.stderr)
    real_size = f"{real[0]}×{real[1]}" if real else f"{f['width']}×{f['height']}"
    dest = src
    saved = [src]

    if args.web:
        if not shutil.which("ffmpeg"):
            raise PexelsError("Для --web нужен ffmpeg в PATH.")
        dest = out_dir / (stem + ".mp4")
        cmd = ["ffmpeg", "-y", "-loglevel", "error", "-i", str(src)]
        if args.trim:
            cmd += ["-t", str(args.trim)]
        cmd += ["-vf", f"scale='min({args.max_width},iw)':-2", "-c:v", "libx264", "-crf", str(args.crf),
                "-preset", "slow", "-pix_fmt", "yuv420p", "-movflags", "+faststart", "-an", str(dest)]
        subprocess.run(cmd, check=True)
        poster = out_dir / (stem + "-poster.webp")
        subprocess.run(["ffmpeg", "-y", "-loglevel", "error", "-i", str(dest), "-frames:v", "1",
                        "-c:v", "libwebp", "-quality", "80", str(poster)], check=True)
        print(f"  web: {dest.stat().st_size / 1e6:.1f} МБ (исходник {src.stat().st_size / 1e6:.1f} МБ), "
              f"постер {poster.name}", file=sys.stderr)
        saved = [dest, poster]

    ledger_add(out_dir, {
        "date": datetime.date.today().isoformat(), "type": "video", "id": v["id"],
        "files": [f.name for f in saved], "page": v["url"], "author": (v.get("user") or {}).get("name"),
        "author_url": (v.get("user") or {}).get("url"), "duration": v.get("duration"),
        "source": real_size, "license": LICENSE,
    })
    for path in saved:
        print(f"{path}  ({path.stat().st_size / 1e6:.1f} МБ)")


# ---------------------------------------------------------------- команды

def locale_for(args):
    if getattr(args, "locale", None):
        return args.locale
    return "ru-RU" if has_cyrillic(getattr(args, "query", "")) else None


def run(args):
    if args.cmd == "download":
        key = get_key(args.key)
        (download_photo if args.kind == "photo" else download_video)(key, args)
        rate_note()
        return

    key = get_key(args.key)
    paging = {"page": getattr(args, "page", None), "per_page": getattr(args, "per_page", None)}

    if args.cmd == "photos":
        res = api(key, "/v1/search", dict(paging, query=args.query, orientation=args.orientation,
                                         size=args.size, color=args.color, locale=locale_for(args)))
        listing(args, res, "photos", "photo")
    elif args.cmd == "curated":
        listing(args, api(key, "/v1/curated", paging), "photos", "photo")
    elif args.cmd in ("videos", "popular"):
        if args.cmd == "videos":
            res = api(key, "/videos/search", dict(paging, query=args.query, orientation=args.orientation,
                                                 size=args.size, locale=locale_for(args)))
        else:
            res = api(key, "/videos/popular", dict(paging, min_width=args.min_width,
                                                   min_duration=args.min_duration,
                                                   max_duration=args.max_duration))
        vids = res.get("videos") or []
        if args.min_duration:
            vids = [v for v in vids if (v.get("duration") or 0) >= args.min_duration]
        if args.max_duration:
            vids = [v for v in vids if (v.get("duration") or 0) <= args.max_duration]
        if args.min_width:
            vids = [v for v in vids if (v.get("width") or 0) >= args.min_width]
        res["videos"] = vids
        listing(args, res, "videos", "video")
    elif args.cmd == "photo":
        p = api(key, f"/v1/photos/{args.id}")
        if args.json:
            print(json.dumps(p, ensure_ascii=False, indent=2))
        else:
            print(f"{p['id']}  {p['width']}×{p['height']}  цвет {p.get('avg_color')}")
            print(f"  {title_of(p)}")
            print(f"  автор: {p.get('photographer')} — {p.get('photographer_url')}")
            print(f"  страница: {p['url']}")
            print(f"  оригинал: {p['src']['original']}")
    elif args.cmd == "video":
        v = api(key, f"/videos/videos/{args.id}")
        if args.json:
            print(json.dumps(v, ensure_ascii=False, indent=2))
        else:
            u = v.get("user") or {}
            print(f"{v['id']}  {v['width']}×{v['height']}  {v.get('duration')}с")
            print(f"  {title_of(v)}")
            print(f"  автор: {u.get('name')} — {u.get('url')}")
            print(f"  страница: {v['url']}")
            print("  файлы:")
            for f in sorted(v.get("video_files") or [], key=lambda f: -(f.get("width") or 0)):
                print(f"    {tier_of(f):<4} {f.get('width')}×{f.get('height')} "
                      f"{f.get('fps') or '?'}fps {f.get('file_type')}")
        if args.sheet:
            make_sheet(v.get("video_pictures") or [], args.sheet, frames=True)
    elif args.cmd == "collections":
        path = "/v1/collections/featured" if args.featured else "/v1/collections"
        res = api(key, path, paging)
        if args.json:
            print(json.dumps(res, ensure_ascii=False, indent=2))
        else:
            for c in res.get("collections") or []:
                print(f"{c['id']:<10} фото {c.get('photos_count', 0):>4}  видео {c.get('videos_count', 0):>4}  "
                      f"{cut(c.get('title'), 60)}")
    elif args.cmd == "collection":
        res = api(key, f"/v1/collections/{args.id}", dict(paging, type=args.type, sort=args.sort))
        media = res.get("media") or []
        if args.json:
            print(json.dumps(res, ensure_ascii=False, indent=2))
        else:
            photos = [m for m in media if m.get("type") == "Photo"]
            videos = [m for m in media if m.get("type") == "Video"]
            if photos:
                print("Фото:")
                print_photos(photos)
            if videos:
                print("Видео:")
                print_videos(videos)
            print(f"— страница {res.get('page')}, всего {res.get('total_results')}")
        if args.sheet and media:
            make_sheet(media, args.sheet)
    elif args.cmd == "limits":
        api(key, "/v1/curated", {"per_page": 1})
        rate_note(force=True)
        return
    rate_note()


def listing(args, res, key, kind):
    if args.json:
        print(json.dumps(res, ensure_ascii=False, indent=2))
    else:
        print_page(res, key, kind)
    if args.sheet and res.get(key):
        sys.stdout.flush()
        make_sheet(res[key], args.sheet)


def build_parser():
    ap = argparse.ArgumentParser(description="Pexels: поиск и скачивание фото и видео для PROM-EN")
    ap.add_argument("--key", help="ключ API (по умолчанию из site/.env)")
    sub = ap.add_subparsers(dest="cmd", required=True)

    def listing_opts(p, sheet=True):
        p.add_argument("--page", type=int)
        p.add_argument("--per-page", type=int, default=15, help="до 80")
        p.add_argument("--json", action="store_true", help="сырой ответ API")
        if sheet:
            p.add_argument("--sheet", help="склеить превью в JPEG по этому пути")

    p = sub.add_parser("photos", help="поиск фото")
    p.add_argument("query")
    p.add_argument("--orientation", choices=["landscape", "portrait", "square"])
    p.add_argument("--size", choices=["large", "medium", "small"], help="минимум 24 / 12 / 4 Мп")
    p.add_argument("--color", help="red…white или hex без #")
    p.add_argument("--locale", help="ru-RU ставится сам, если в запросе кириллица")
    listing_opts(p)

    p = sub.add_parser("videos", help="поиск видео")
    p.add_argument("query")
    p.add_argument("--orientation", choices=["landscape", "portrait", "square"])
    p.add_argument("--size", choices=["large", "medium", "small"], help="4K / Full HD / HD")
    p.add_argument("--locale")
    p.add_argument("--min-duration", type=int)
    p.add_argument("--max-duration", type=int)
    p.add_argument("--min-width", type=int)
    listing_opts(p)

    p = sub.add_parser("curated", help="подборка редакции (фото)")
    listing_opts(p)

    p = sub.add_parser("popular", help="популярные видео")
    p.add_argument("--min-duration", type=int)
    p.add_argument("--max-duration", type=int)
    p.add_argument("--min-width", type=int)
    listing_opts(p)

    p = sub.add_parser("photo", help="одно фото по id")
    p.add_argument("id", type=int)
    p.add_argument("--json", action="store_true")

    p = sub.add_parser("video", help="одно видео по id: файлы, --sheet = раскадровка")
    p.add_argument("id", type=int)
    p.add_argument("--json", action="store_true")
    p.add_argument("--sheet", help="склеить кадры видео в JPEG")

    p = sub.add_parser("collections", help="свои коллекции (или --featured)")
    p.add_argument("--featured", action="store_true")
    listing_opts(p, sheet=False)

    p = sub.add_parser("collection", help="содержимое коллекции")
    p.add_argument("id")
    p.add_argument("--type", choices=["photos", "videos"])
    p.add_argument("--sort", choices=["asc", "desc"])
    listing_opts(p)

    p = sub.add_parser("download", help="скачать фото или видео в media-src/pexels")
    p.add_argument("kind", choices=["photo", "video"])
    p.add_argument("id", type=int)
    p.add_argument("--out", help="папка (по умолчанию site/media-src/pexels)")
    p.add_argument("--name", help="хвост имени файла вместо слага Pexels")
    g = p.add_argument_group("фото")
    g.add_argument("--width", type=int, help="уменьшить до ширины, пропорционально")
    g.add_argument("--crop", help="обрезать по центру до ШxВ, например 1200x630")
    g.add_argument("--cx", type=float, default=0.5, help="центр кропа по X, 0…1")
    g.add_argument("--cy", type=float, default=0.5, help="центр кропа по Y, 0…1")
    g.add_argument("--webp", action="store_true", help="сохранить WebP")
    g.add_argument("--jpg", action="store_true", help="сохранить JPEG (вместе с --webp — оба)")
    g.add_argument("--quality", type=int, help="по умолчанию 80 для WebP, 85 для JPEG")
    g = p.add_argument_group("видео")
    g.add_argument("--tier", choices=["uhd", "hd", "sd"])
    g.add_argument("--max-width", type=int, default=1920)
    g.add_argument("--web", action="store_true", help="пережать ffmpeg: H.264 без звука + постер WebP")
    g.add_argument("--crf", type=int, default=26)
    g.add_argument("--trim", type=float, help="оставить первые N секунд (с --web)")

    sub.add_parser("limits", help="остаток лимита запросов")
    return ap


def main():
    args = build_parser().parse_args()
    try:
        run(args)
    except PexelsError as e:
        print(f"Ошибка: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
