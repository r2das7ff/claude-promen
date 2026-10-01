#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Ролик из фотографии: медленный наезд камеры с замкнутой петлёй.

Кадры считает Pillow с субпиксельной точностью (affine + bicubic) и шлёт
в ffmpeg сырыми. zoompan у ffmpeg округляет окно до целых пикселей —
на медленном наезде это дрожь, видимая глазом.

Движение туда-обратно по косинусу: последний кадр совпадает с первым,
поэтому loop у <video> не даёт скачка на стыке.

  python scripts/media/kenburns.py фото.jpg выход.mp4 --zoom 1.08 \
      --from 0.5,0.55 --to 0.42,0.5 --seconds 10 --width 1920 --crf 28
"""
import argparse
import math
import subprocess
import sys

from PIL import Image, ImageOps


def point(text):
    x, y = (float(v) for v in text.split(","))
    return x, y


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("src")
    ap.add_argument("out")
    ap.add_argument("--width", type=int, default=1920)
    ap.add_argument("--aspect", default="16:9")
    ap.add_argument("--zoom", type=float, default=1.08, help="наибольший наезд к середине петли")
    ap.add_argument("--from", dest="p0", type=point, default=(0.5, 0.5), help="центр кадра в начале, доли 0…1")
    ap.add_argument("--to", dest="p1", type=point, default=(0.5, 0.5), help="центр кадра в середине петли")
    ap.add_argument("--seconds", type=float, default=10)
    ap.add_argument("--fps", type=int, default=30)
    ap.add_argument("--crf", type=int, default=28)
    ap.add_argument("--vf", default="", help="доп. фильтр ffmpeg, например eq=gamma=0.9")
    args = ap.parse_args()

    aw, ah = (int(v) for v in args.aspect.split(":"))
    W, H = args.width, round(args.width * ah / aw / 2) * 2
    img = ImageOps.exif_transpose(Image.open(args.src)).convert("RGB")
    # базовое окно — наибольшее нужной пропорции
    bw = min(img.width, img.height * aw / ah)
    # исходник ужимаем так, чтобы на пике наезда было 1:1, — без алиасинга на тонких линиях
    k = W * args.zoom / bw
    if k < 1:
        img = img.resize((round(img.width * k), round(img.height * k)), Image.LANCZOS)
        bw *= k
    bh = bw * ah / aw
    n = round(args.seconds * args.fps)

    vf = f"format=yuv420p{',' + args.vf if args.vf else ''}"
    cmd = ["ffmpeg", "-v", "error", "-y", "-f", "rawvideo", "-pix_fmt", "rgb24", "-s", f"{W}x{H}",
           "-framerate", str(args.fps), "-i", "-", "-vf", vf, "-c:v", "libx264", "-profile:v", "high",
           "-crf", str(args.crf), "-preset", "slow", "-movflags", "+faststart", "-an", args.out]
    p = subprocess.Popen(cmd, stdin=subprocess.PIPE)
    for i in range(n):
        s = (1 - math.cos(2 * math.pi * i / n)) / 2          # 0 → 1 → 0 за петлю
        z = 1 + (args.zoom - 1) * s
        cw, ch = bw / z, bh / z
        cx = (args.p0[0] + (args.p1[0] - args.p0[0]) * s) * img.width
        cy = (args.p0[1] + (args.p1[1] - args.p0[1]) * s) * img.height
        cx = min(max(cx, cw / 2), img.width - cw / 2)
        cy = min(max(cy, ch / 2), img.height - ch / 2)
        frame = img.transform((W, H), Image.AFFINE,
                              (cw / W, 0, cx - cw / 2, 0, ch / H, cy - ch / 2),
                              resample=Image.BICUBIC)
        p.stdin.write(frame.tobytes())
    p.stdin.close()
    sys.exit(p.wait())


if __name__ == "__main__":
    main()
