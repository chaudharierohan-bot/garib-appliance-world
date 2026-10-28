# Preparing for future hosting

Hosting and a domain have not been purchased or configured. This project is a local development store and must not accept real orders yet.

1. Choose managed WordPress hosting with supported PHP, MySQL, HTTPS, backups and staging. Review the pinned WordPress/WooCommerce versions against current security releases, update in staging, and repeat functional tests.
2. Back up and migrate the WordPress database and uploads plus the custom theme. Move credentials through the host's secret configuration, never through Git. Use a WordPress-aware URL replacement tool for serialized database values; flush permalinks after migration.
3. Replace every `DEMO-*` product and original placeholder illustration with verified inventory and owned/licensed product photos. Verify specifications, stock, SRD pricing and sale schedules. Remove local test orders/accounts/newsletter requests and the development-store notice only after review.
4. Configure the real store address, delivery areas/rates, tax obligations, warranty, returns, privacy/retention policy and customer contact information. The local SRD 150 delivery rate and disabled taxes are development defaults.
5. Configure bank account information and test an on-hold transfer order. Review COD service areas and fulfillment. No credit-card gateway is installed.
6. Configure authenticated transactional email for order notices, customer registration and password resets. Test delivery to actual test inboxes with permission. Integrate newsletter double opt-in, unsubscribe and data-erasure/export with a chosen provider; the local request collector does not send campaigns.
7. Add the real WhatsApp number under the Customizer. Verify all banner links, image alt text, product image galleries, mobile navigation, cart, checkout and account behavior on actual Android/iOS devices and desktop browsers. Automated Chromium layout tests do not establish testing on physical devices.
8. Enforce HTTPS, least-privilege administrator accounts, automatic security updates where appropriate, off-site backups, restore tests, monitoring and cache exclusions for cart/checkout/account. Verify shipping and checkout under real operational conditions.
9. Enable indexing only once the production site and SEO/legal content are ready. Point DNS and deploy through the hosting provider after business approval.

Do not expose the provided local Docker stack as a production deployment without independent hardening and operations work.
