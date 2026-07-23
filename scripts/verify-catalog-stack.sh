#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "== PHPUnit =="
if [[ ! -d vendor ]]; then
  composer install --no-interaction
fi
composer test

echo "== Docker stack =="
docker compose up -d meilisearch db wordpress >/dev/null
sleep 3

echo "== Catalog rebuild =="
docker compose run --rm wpcli promen catalog-rebuild

echo "== Search reindex =="
docker compose run --rm wpcli promen search-reindex

echo "== Catalog verify =="
docker compose run --rm wpcli promen catalog-verify --category=troyniki

echo "== Search reconcile =="
docker compose run --rm wpcli promen search-reconcile

echo "== REST API smoke =="
API_URL="http://wordpress/wp-json/promen/v1/catalog?group=troyniki&steel=10&per_page=3"
docker compose run --rm wpcli eval "
\$r = wp_remote_get('${API_URL}');
\$body = json_decode(wp_remote_retrieve_body(\$r), true);
if (empty(\$body['total'])) { WP_CLI::error('API total=0'); }
foreach (\$body['hits'] as \$h) {
  if (strpos(\$h['steel_display'] ?? '', '10') === false && !in_array('10', \$h['steels'] ?? [], true)) {
    WP_CLI::error('Hit missing steel 10: ' . (\$h['sku'] ?? ''));
  }
}
WP_CLI::success('API steel=10 OK, engine=' . (\$body['engine'] ?? '?'));
"

echo "ALL CHECKS PASSED"
