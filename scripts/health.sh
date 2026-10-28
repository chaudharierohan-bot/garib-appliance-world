#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p .runtime
store_url=${STORE_URL:-http://localhost:8080}
for attempt in {1..30}; do
  if curl -fsS --max-time 10 "$store_url/shop/" -o .runtime/health.html && grep -q 'woocommerce-loop-product__title' .runtime/health.html; then
    echo 'Store catalogue is ready.'
    exit 0
  fi
  sleep 1
done
echo 'Store catalogue did not become ready; inspect docker compose logs.' >&2
exit 1
