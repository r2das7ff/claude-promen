#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Упаковка снимков изделий в формат каталога.

Вход  — RGBA-PNG прямо из Higgsfield (seedream_v5_pro, remove_bg: true).
Выход — WebP с альфой: обрезка по непрозрачным пикселям, вписывание в бокс
1200x860, качество 82. Цвет не трогаем: сталь уже нейтральная, а коррекция
портит жёлтый ППУ и оцинковку.

    python scripts/pack-product-photos.py <src-dir> <dst-dir>
    python scripts/pack-product-photos.py <src.png> <dst-dir>

Метод генерации самих кадров — в assets/img/products/README.txt.
"""
import os
import sys
import glob

from PIL import Image

BOX = (1200, 860)
QUALITY = 82
ALPHA_FLOOR = 8  # ниже — считаем пикселем фона, иначе кайма тянет бокс


def pack(src, dst_dir):
    im = Image.open(src).convert("RGBA")
    bbox = im.getchannel("A").point(lambda a: 255 if a > ALPHA_FLOOR else 0).getbbox()
    if bbox is None:
        raise SystemExit("пустая альфа: " + src)
    im = im.crop(bbox)
    im.thumbnail(BOX, Image.LANCZOS)
    name = os.path.splitext(os.path.basename(src))[0] + ".webp"
    out = os.path.join(dst_dir, name)
    im.save(out, "WEBP", quality=QUALITY, method=6, exact=True)
    return out, im.size, os.path.getsize(out)


def main():
    if len(sys.argv) != 3:
        raise SystemExit(__doc__)
    src, dst_dir = sys.argv[1], sys.argv[2]
    os.makedirs(dst_dir, exist_ok=True)
    files = sorted(glob.glob(os.path.join(src, "*.png"))) if os.path.isdir(src) else [src]
    for f in files:
        out, size, nbytes = pack(f, dst_dir)
        print("%-52s %4dx%-4d %6.1f KB" % (os.path.basename(out), size[0], size[1], nbytes / 1024))


main()
