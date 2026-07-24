#!/usr/bin/env bash
# Полный замер каталога: бэкенд (SQL/время) + HTTP TTFB + веса ассетов.
# Запуск с хоста из site/:  bash scripts/perf-audit.sh [label]
# Результат: perf-reports/<label>.txt (label по умолчанию — timestamp).

set -u
cd "$(dirname "$0")/.."

# Git Bash (Windows): не переписывать /scripts/... в C:/Program Files/Git/...
export MSYS_NO_PATHCONV=1

LABEL="${1:-$(date +%Y%m%d-%H%M%S)}"
BASE="${PROMEN_BASE_URL:-http://localhost:8080}"
OUT_DIR="perf-reports"
OUT="$OUT_DIR/$LABEL.txt"
mkdir -p "$OUT_DIR"

{
echo "== PROM-EN perf audit: $LABEL =="
echo "date: $(date -Iseconds)"
echo

echo "--- backend (wp eval-file, каждый компонент — холодный процесс) ---"
printf "%-22s %10s %10s\n" "component" "queries" "ms"
for comp in search_only registry_embed rest_catalog s01_series s04_norms sidebar category_page; do
  docker compose run --rm -T wpcli eval-file /scripts/perf-audit.php "$comp" 2>/dev/null \
    | grep -E "^$comp " || echo "$comp             FAILED"
done
echo

echo "--- HTTP TTFB (медиана из 3, dev-стек) ---"
for path in "/catalog/" "/catalog/sdt/otvody/" "/contacts/" "/wp-json/promen/v1/catalog?group=otvody"; do
  t=""
  for i in 1 2 3; do
    t="$t $(curl -s -o /dev/null -w '%{time_starttransfer}' "$BASE$path")"
  done
  med=$(echo "$t" | tr ' ' '\n' | grep -v '^$' | sort -n | sed -n 2p)
  printf "%-45s %ss\n" "$path" "$med"
done
echo

echo "--- страница категории: HTML и ассеты ---"
HTML=$(curl -s "$BASE/catalog/sdt/otvody/")
raw=$(printf '%s' "$HTML" | wc -c)
gz=$(printf '%s' "$HTML" | gzip -c | wc -c)
echo "HTML: ${raw} bytes raw, ${gz} bytes gzip"

echo "JS на странице:"
total_js=0; n_js=0
for src in $(printf '%s' "$HTML" | grep -o 'src="[^"]*\.js[^"]*"' | sed 's/src="//;s/"$//' | sort -u); do
  case "$src" in http*) url="$src";; /*) url="$BASE$src";; *) url="$BASE/$src";; esac
  sz=$(curl -s "$url" | wc -c)
  total_js=$((total_js + sz)); n_js=$((n_js + 1))
  printf "  %8d  %s\n" "$sz" "$(echo "$src" | sed "s|$BASE||;s|?.*||")"
done
echo "JS итого: $total_js bytes, $n_js файлов"

echo "Шрифты (файлы, на которые ссылается base.css):"
total_f=0
for f in $(grep -o "fonts/[^')]*" wp-content/themes/promen/assets/css/base.css | sort -u); do
  p="wp-content/themes/promen/assets/$f"
  if [ -f "$p" ]; then
    sz=$(wc -c < "$p")
    total_f=$((total_f + sz))
    printf "  %8d  %s\n" "$sz" "$f"
  fi
done
echo "Шрифты итого: $total_f bytes"
} | tee "$OUT"

echo
echo "saved: $OUT"
