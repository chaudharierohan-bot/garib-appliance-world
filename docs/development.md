# Development notes

## Structure and responsibilities

- `compose.yaml`: local database, web server and on-demand WP-CLI services; data lives in named Docker volumes.
- `scripts/setup.sh`: reproducible installation, downloaded artifact checksum verification, activation, configuration and explicit demo seed.
- `scripts/install.php`: first-install API wrapper; reads the administrator password from the container environment and does not echo it.
- `scripts/configure.php`: development-only store configuration, pages, payment methods and one idempotent shipping zone.
- `scripts/seed.php`: idempotent fictional catalogue creation keyed by `DEMO-*` SKUs; generates original PNG illustrations with PHP GD. Does not replace images or products that already exist.
- `theme/garib-appliance-world`: native WordPress templates and WooCommerce theme integration, local CSS/JS and original artwork.
- `tests/store.test.cjs`: functional browser checks against a running local store.

WooCommerce retains ownership of prices, stock reservations, customer sessions, password hashing, login, CSRF-protected cart/checkout forms, order persistence and account capabilities. The theme adds no custom payment processing or password storage. Cart fragments refresh the bag count after WooCommerce AJAX actions and are not unnecessarily enqueued on account pages. The theme renews the session cookie through `woocommerce_guest_session_to_user_id` to address the pinned WooCommerce 10.3.4 guest-to-user migration behavior; otherwise an old guest cookie can overwrite customer session notices on subsequent requests. A browser regression check performs guest shopping followed by registration and account editing. Standard gallery/zoom/lightbox support accepts additional real photos via admin.

Newsletter requests use a private admin-only post type, sanitized email, nonce validation, explicit consent, a honeypot and a short per-IP rate limit. No marketing email is sent. Administrators can remove requests from the WordPress UI; production privacy retention, export/erasure integration and provider double opt-in must be defined before launch. Consent records remain in the database if the theme changes, although their administration UI requires this theme to be active. A production mailing integration should move this data flow into a dedicated plugin/provider.

## Environment and security

No database or admin credentials belong in Git. `.env`, `.runtime`, browser results and dependencies are ignored. The web port binds to loopback; MySQL is not published to the host. Development disables indexing and uses fictional business policies. Enable HTTPS, supported security updates, production mail and reviewed policies before deploying.

Do not run `configure.php` or `setup.sh` against an established production store: they intentionally set local development defaults. Real bank account information is edited only in WooCommerce → Settings → Payments → Direct bank transfer. Confirm shipping zones, tax rules and inventory separately. Changing host/port on an existing database requires WordPress URL migration, not just a new Compose port.

## Cloud startup

Use the existing isolated checkout under `/workspace/garib-appliance-world`; do not create worktrees unless explicitly requested. Start retained containers with `docker compose up -d`, then check an HTTP response, catalogue and checkout. Docker processes do not survive environment snapshots; startup instructions must restart them. If this managed runtime requires network-aware containers for future downloads, retain its proxy configuration and mount its CA as documented by the cloud runtime skill. No TLS-verification bypass is part of this project.

## Retail homepage refresh

The existing theme and Docker stack are retained. The homepage now uses a three-slide, manually operated promotional carousel with previous/next buttons, dot selection and arrow-key support. There is no auto-rotation to interrupt reading or require an autoplay pause control. Featured Products, Special Offers, New Arrivals and Best Sellers still use native WooCommerce queries/shortcodes; prices, stock and add-to-cart actions are live store data. Sale badges compute a saving percentage for simple products. Sales history is not fabricated.

The category menu uses two columns on desktop and a bounded scrollable list on mobile. Additional tech/audio discovery panels link to actual product categories. The footer contains customer service links, payment information and editable contact/social fields under Appearance → Customize → Store promotions & contact. Until real values are supplied, contact fields explicitly say they are to be confirmed and social labels are non-clickable placeholders. No fictional phone number, address or social profile is published.

The demo photo importer runs after the existing seed in `setup.sh`. To update only demonstration imagery on an existing local installation without changing business settings, run `docker compose run --rm -T cli wp eval-file /setup/refresh-photos.php`. It preserves owner-supplied product images. See `docs/image-sources.md` for provenance and limitations.
