#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
umask 022
find theme scripts -type d -exec chmod 755 {} +
find theme -type f -exec chmod 644 {} +
find theme scripts -type f -name "*.php" -exec chmod 644 {} +
if [[ ! -f .env ]]; then
  umask 077
  printf 'DB_PASSWORD=%s\nDB_ROOT_PASSWORD=%s\nPORT=8080\n' "$(openssl rand -hex 24)" "$(openssl rand -hex 24)" > .env
fi
: "${WP_ADMIN_USER:=storeadmin}"
: "${WP_ADMIN_EMAIL:=admin@example.test}"
: "${WP_URL:=http://localhost:8080}"
if [[ -z "${WP_ADMIN_PASSWORD:-}" ]]; then
  echo 'Set WP_ADMIN_PASSWORD to a unique local admin password before running setup.' >&2
  exit 1
fi
mkdir -p .runtime
chmod 755 .runtime
archive=.runtime/woocommerce-10.3.4.zip
if [[ ! -f "$archive" ]]; then
  curl -fL --retry 2 https://github.com/woocommerce/woocommerce/releases/download/10.3.4/woocommerce.zip -o "$archive"
fi
chmod 644 "$archive"
printf '%s  %s\n' '016748ab209cf12fe2612c377ae4d0ccb448e5c7fb6e7381f9b64d13db82c46d' "$archive" | sha256sum -c -
docker compose up -d
for attempt in {1..60}; do
  if docker compose exec -T wordpress test -f /var/www/html/wp-config.php; then break; fi
  if [[ $attempt == 60 ]]; then echo 'WordPress files did not initialize.' >&2; exit 1; fi
  sleep 2
done
export WP_ADMIN_PASSWORD WP_ADMIN_USER WP_ADMIN_EMAIL WP_URL
wp() { docker compose run --rm -T -e WP_ADMIN_PASSWORD -e WP_ADMIN_USER -e WP_ADMIN_EMAIL -e WP_URL cli wp "$@"; }
if ! wp core is-installed; then
  docker compose run --rm -T --entrypoint php -e WP_ADMIN_PASSWORD -e WP_ADMIN_USER -e WP_ADMIN_EMAIL -e WP_URL cli /setup/install.php
fi
if ! wp plugin is-installed woocommerce; then
  wp plugin install /artifacts/woocommerce-10.3.4.zip --activate
else
  wp plugin activate woocommerce
fi
wp theme activate garib-appliance-world
wp eval-file /setup/configure.php
wp eval-file /setup/seed.php
wp eval-file /setup/refresh-photos.php
wp rewrite flush --hard
printf 'Local store configured at %s. Demo products and delivery pricing only.\n' "$WP_URL"
