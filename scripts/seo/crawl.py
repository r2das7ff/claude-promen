#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Краулер каталога PROM-EN: обходит сайт и складывает всё в SQLite.

Рассчитан на 15k+ страниц: обход по уровням, пул потоков, докачка (resume).
Ничего не чинит и не советует — только собирает факты. Разбор — report.py.

    python crawl.py http://localhost:8080 --db catalog.db --workers 8
    python crawl.py https://prom-en.com --sitemap --limit 12000
    python crawl.py http://localhost:8080 --resume        # продолжить обход
"""
import argparse
import re
import sqlite3
import sys
import time
from concurrent.futures import ThreadPoolExecutor
from urllib.parse import urljoin, urldefrag, urlparse
from urllib.robotparser import RobotFileParser

import requests
from lxml import html as lhtml

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

UA = "Mozilla/5.0 (compatible; PromenSEOBot/1.0; +local audit)"
SKIP_EXT = re.compile(
    r"\.(jpe?g|png|gif|webp|avif|svg|ico|css|js|pdf|zip|rar|docx?|xlsx?|mp4|woff2?|ttf)$", re.I)

DDL = """
CREATE TABLE IF NOT EXISTS pages (
  url TEXT PRIMARY KEY, status INTEGER, redirect_to TEXT, final_url TEXT,
  depth INTEGER, content_type TEXT, bytes INTEGER, elapsed_ms INTEGER,
  title TEXT, title_len INTEGER, description TEXT, desc_len INTEGER,
  h1 TEXT, h1_count INTEGER, h2_count INTEGER,
  canonical TEXT, meta_robots TEXT, x_robots TEXT, lang TEXT,
  word_count INTEGER, jsonld_types TEXT, og_title TEXT, og_image TEXT,
  in_sitemap INTEGER DEFAULT 0, crawled_at TEXT, error TEXT
);
CREATE TABLE IF NOT EXISTS links (
  src TEXT, dst TEXT, anchor TEXT, rel TEXT, internal INTEGER
);
CREATE INDEX IF NOT EXISTS idx_links_dst ON links(dst);
CREATE INDEX IF NOT EXISTS idx_links_src ON links(src);
CREATE INDEX IF NOT EXISTS idx_pages_status ON pages(status);
CREATE TABLE IF NOT EXISTS meta (k TEXT PRIMARY KEY, v TEXT);
"""

PAGE_COLS = ["url", "status", "redirect_to", "final_url", "depth", "content_type",
             "bytes", "elapsed_ms", "title", "title_len", "description", "desc_len",
             "h1", "h1_count", "h2_count", "canonical", "meta_robots", "x_robots",
             "lang", "word_count", "jsonld_types", "og_title", "og_image",
             "crawled_at", "error"]


def norm(u):
    """Убираем якорь и хвостовой '?' — иначе один адрес попадёт в базу дважды."""
    u, _ = urldefrag(u)
    if u.endswith("?"):
        u = u[:-1]
    return u


class Crawler:
    def __init__(self, args):
        self.a = args
        self.root = args.url.rstrip("/")
        self.host = urlparse(self.root).netloc
        self.db = sqlite3.connect(args.db, check_same_thread=False)
        self.db.executescript(DDL)
        self.seen = set()
        self.sess = requests.Session()
        self.sess.headers["User-Agent"] = args.user_agent or UA
        if args.auth:
            self.sess.auth = tuple(args.auth.split(":", 1))
        self.rp = None
        if not args.ignore_robots:
            self.rp = RobotFileParser()
            try:
                self.rp.set_url(self.root + "/robots.txt")
                self.rp.read()
            except Exception:
                self.rp = None

    # ---------- фильтры ----------
    def internal(self, u):
        return urlparse(u).netloc == self.host

    def crawlable(self, u):
        if u in self.seen or not u.startswith(("http://", "https://")):
            return False
        if not self.internal(u) or SKIP_EXT.search(urlparse(u).path):
            return False
        if self.a.skip_query and urlparse(u).query:
            return False
        if self.a.exclude and re.search(self.a.exclude, u):
            return False
        if self.a.include and not re.search(self.a.include, u):
            return False
        if self.rp and not self.rp.can_fetch(self.sess.headers["User-Agent"], u):
            return False
        return True

    # ---------- загрузка ----------
    def fetch(self, url, depth):
        row = {"url": url, "depth": depth, "status": None, "redirect_to": None,
               "final_url": None, "content_type": None, "bytes": 0, "elapsed_ms": 0,
               "error": None}
        links = []
        t0 = time.time()
        try:
            r = self.sess.get(url, timeout=self.a.timeout, allow_redirects=False)
            row["status"] = r.status_code
            row["elapsed_ms"] = int((time.time() - t0) * 1000)
            row["content_type"] = (r.headers.get("Content-Type") or "").split(";")[0]
            row["x_robots"] = r.headers.get("X-Robots-Tag")
            row["bytes"] = len(r.content)
            if 300 <= r.status_code < 400:
                row["redirect_to"] = norm(urljoin(url, r.headers.get("Location", "")))
                return row, [(row["redirect_to"], "", "redirect")]
            if "html" not in (row["content_type"] or ""):
                return row, []
            row.update(self.parse(r, url, links))
        except Exception as e:
            row["error"] = f"{type(e).__name__}: {e}"[:200]
        return row, links

    def parse(self, r, url, links):
        doc = lhtml.fromstring(r.content)
        doc.make_links_absolute(url, resolve_base_href=True)

        def one(xp):
            got = doc.xpath(xp)
            return got[0] if got else None

        out = {}
        out["title"] = (one("//title/text()") or "").strip()
        out["description"] = (one(
            "//meta[translate(@name,'DESCRIPTION','description')='description']/@content") or "").strip()
        h1s = [x.strip() for x in doc.xpath("//h1//text()") if x.strip()]
        out["h1"] = " ".join(h1s[:1])
        out["h1_count"] = len(doc.xpath("//h1"))
        out["h2_count"] = len(doc.xpath("//h2"))
        out["canonical"] = one("//link[@rel='canonical']/@href")
        out["meta_robots"] = one("//meta[translate(@name,'ROBTS','robts')='robots']/@content")
        out["lang"] = one("//html/@lang")
        out["og_title"] = one("//meta[@property='og:title']/@content")
        out["og_image"] = one("//meta[@property='og:image']/@content")
        types = []
        for s in doc.xpath("//script[@type='application/ld+json']/text()"):
            types += re.findall(r'"@type"\s*:\s*"([^"]+)"', s)
        out["jsonld_types"] = ",".join(sorted(set(types)))
        for a in doc.xpath("//a[@href]"):
            href = norm(a.get("href"))
            if href.startswith(("http://", "https://")):
                links.append((href, " ".join(a.text_content().split())[:120], a.get("rel") or ""))
        for bad in doc.xpath("//script|//style|//noscript"):
            bad.getparent().remove(bad)
        out["word_count"] = len(doc.text_content().split())
        return out

    # ---------- запись ----------
    def save(self, row, links):
        row["title_len"] = len(row.get("title") or "")
        row["desc_len"] = len(row.get("description") or "")
        row["crawled_at"] = time.strftime("%Y-%m-%dT%H:%M:%S")
        vals = [row.get(c) for c in PAGE_COLS]
        self.db.execute(
            "INSERT INTO pages ({}) VALUES ({}) ON CONFLICT(url) DO UPDATE SET {}".format(
                ",".join(PAGE_COLS),
                ",".join("?" * len(PAGE_COLS)),
                ",".join(f"{c}=excluded.{c}" for c in PAGE_COLS if c != "url")),
            vals)
        if links:
            self.db.execute("DELETE FROM links WHERE src=?", (row["url"],))
            self.db.executemany(
                "INSERT INTO links (src,dst,anchor,rel,internal) VALUES (?,?,?,?,?)",
                [(row["url"], d, a, rl, 1 if self.internal(d) else 0) for d, a, rl in links])

    # ---------- sitemap ----------
    def from_sitemap(self, url, depth=0):
        urls = []
        if depth > 3:
            return urls
        try:
            body = self.sess.get(url, timeout=self.a.timeout).text
        except Exception as e:
            print(f"  sitemap {url}: {e}")
            return urls
        locs = re.findall(r"<loc>\s*([^<\s]+)\s*</loc>", body)
        if "<sitemapindex" in body:
            for sub in locs:
                urls += self.from_sitemap(sub, depth + 1)
        else:
            urls = [norm(u) for u in locs]
        return urls

    # ---------- главный цикл ----------
    def run(self):
        frontier = []
        if self.a.sitemap is not None:
            sm = self.a.sitemap or (self.root + "/sitemap.xml")
            sitemap_urls = self.from_sitemap(sm)
            print(f"sitemap: {len(sitemap_urls)} URL")
            for u in sitemap_urls:
                self.db.execute(
                    "INSERT INTO pages (url,in_sitemap,depth) VALUES (?,1,0) "
                    "ON CONFLICT(url) DO UPDATE SET in_sitemap=1", (u,))
            self.db.commit()
            frontier = [(u, 0) for u in sitemap_urls]
        if self.a.resume:
            self.seen = set(r[0] for r in
                            self.db.execute("SELECT url FROM pages WHERE status IS NOT NULL"))
            # Фронтир восстанавливаем из графа ссылок, а не от корня: корень уже
            # обойдён, и обход оборвался бы на первом же шаге. Берём внутренние
            # цели ссылок без ответа и наследуем глубину от ближайшего родителя.
            pending = self.db.execute(
                "SELECT l.dst, MIN(p.depth)+1 FROM links l "
                "JOIN pages p ON p.url = l.src "
                "LEFT JOIN pages d ON d.url = l.dst "
                "WHERE l.internal = 1 AND d.status IS NULL "
                "GROUP BY l.dst").fetchall()
            frontier = [(u, dep or 1) for u, dep in pending if self.crawlable(u)] + frontier
            print(f"resume: обойдено {len(self.seen)}, в очереди {len(frontier)}")
        if not frontier:
            frontier = [(self.root + "/", 0)]
        total = 0
        while frontier and total < self.a.limit:
            batch, dedup = [], set()
            for u, d in frontier:
                if u in self.seen or u in dedup or len(batch) + total >= self.a.limit:
                    continue
                dedup.add(u)
                batch.append((u, d))
            if not batch:
                break
            for u, _ in batch:
                self.seen.add(u)
            with ThreadPoolExecutor(max_workers=self.a.workers) as ex:
                results = list(ex.map(lambda p: self.fetch(*p), batch))
            nxt = {}
            for (url, depth), (row, links) in zip(batch, results):
                self.save(row, links)
                total += 1
                if depth + 1 <= self.a.max_depth:
                    for dst, _, _ in links:
                        if dst not in nxt and self.crawlable(dst):
                            nxt[dst] = depth + 1
            self.db.commit()
            print(f"  обойдено {total}, в очереди {len(nxt)}")
            frontier = list(nxt.items())
            if self.a.delay:
                time.sleep(self.a.delay)
        for k, v in [("root", self.root), ("finished_at", time.strftime("%Y-%m-%dT%H:%M:%S"))]:
            self.db.execute("INSERT OR REPLACE INTO meta (k,v) VALUES (?,?)", (k, v))
        self.db.commit()
        print(f"\nГотово: {total} страниц -> {self.a.db}")


def main():
    p = argparse.ArgumentParser(description="SEO-краулер PROM-EN")
    p.add_argument("url")
    p.add_argument("--db", default="seo-crawl.db")
    p.add_argument("--workers", type=int, default=8)
    p.add_argument("--limit", type=int, default=20000)
    p.add_argument("--max-depth", type=int, default=10)
    p.add_argument("--timeout", type=int, default=30)
    p.add_argument("--delay", type=float, default=0)
    p.add_argument("--sitemap", nargs="?", const="",
                   help="взять URL из sitemap (по умолчанию /sitemap.xml)")
    p.add_argument("--resume", action="store_true", help="не перезагружать уже обойдённое")
    p.add_argument("--skip-query", action="store_true", default=True)
    p.add_argument("--with-query", dest="skip_query", action="store_false",
                   help="обходить и URL с ?параметрами")
    p.add_argument("--include")
    p.add_argument("--exclude")
    p.add_argument("--user-agent")
    p.add_argument("--auth", help="basic-авторизация user:pass для закрытого стенда")
    p.add_argument("--ignore-robots", action="store_true")
    Crawler(p.parse_args()).run()


if __name__ == "__main__":
    main()
