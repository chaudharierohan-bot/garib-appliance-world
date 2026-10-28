# Validation evidence

Validated in the managed Linux development environment on 28 October 2026.

## Passed

- Fresh Docker Compose installation into separate disposable MySQL/WordPress volumes, followed by an idempotent setup rerun. Twelve demo SKUs and eleven categories remained intact without duplication.
- Final Playwright/Chromium run: **11 checks passed** with zero browser JavaScript errors. Covered desktop, 390px mobile and 768px tablet layouts without horizontal overflow; promotional slide controls; all eleven category listing pages; product search; AJAX cart count; descriptions/specifications/gallery; quantity updates/removal; COD and bank-transfer order submission; account/password-reset forms; complete registration/logout/login/account-editing flow; newsletter request and CSRF rejection.
- WordPress-side assertions verify twelve demo products with images, eleven categories, SRD currency, persisted COD orders in processing and transfer orders on hold, SRD 150 demonstration shipping, correct totals, and private newsletter request storage.
- All theme/setup PHP files passed `php -l`; shell scripts passed Bash syntax checks.
- The primary services were stopped and restarted; the catalogue readiness check and retained database/order assertions passed afterward.
- Desktop and mobile screenshots were inspected. Screenshots are in `docs/screenshots/`; full-page test images are generated under ignored `test-results/`.

Repeat checkout tests consume genuine local WooCommerce stock. During diagnosis, fictional demonstration stock was replenished before the final run. Assertions were retained, including normal stock limits, consent, order totals, account success feedback and persistence.

## Issues resolved during development

- Corrected a promotional slider control stacking issue that blocked clicks.
- Explicitly disabled WooCommerce's delayed coming-soon mode for the local demo while retaining search-engine indexing protection.
- Renewed the WooCommerce session cookie after guest-to-customer migration via its public hook; reproduced a lost-account-notice failure with a guest cart, then verified success notices and profile persistence after the fix.
- Used a standalone first-install bootstrap, non-logging admin-password input and readable shared theme/artifact permissions. No TLS or artifact verification was disabled.

## Limits and unrun checks

The reference site was blocked by network policy; no visual comparison against it was possible. Real Android/iOS hardware, Safari, outbound email delivery/password-reset emails, real WhatsApp contact, real bank instructions, tax/legal policies, production hosting, production load/security review and a restored future cloud task were not validated. No real payments, deliveries or marketing messages were sent. Hosting has not been configured. The demonstration store must not be used for real orders.

Git native read/write access was verified. The GitHub API endpoint was denied by network policy, so automatic pull-request creation is blocked independently of branch pushes. The environment draft requests access to `api.github.com` for this operation; saving that draft does not apply or publish it.

## Retail refresh validation

The existing theme was updated in place on 28 October 2026; no new project or commerce stack was created. The updated setup completed in the separate `gaw-fresh` Docker project, including the photo importer. Re-running the importer on the primary store succeeded without replacing owner imagery or changing business data.

The extended **11-check** Chromium suite passed against the isolated store. It additionally checked all four named WooCommerce sections, product-detail links, SRD prices, cart buttons, original/sale prices, three promotional slides, previous/next controls, arrow-key operation, footer placeholders, photo-backed product cards, and layouts at 320px, 390px and 768px. Both COD and bank-transfer order flows, cart editing, search, registration/login, persisted account edits and newsletter consent continue to pass. WordPress-side assertions confirmed persisted order statuses, SRD totals, demo shipping and catalogue/media integrity. PHP syntax, Bash syntax, whitespace checks and primary catalogue readiness passed.

Final full-page screenshots were captured from the primary store at 1440px desktop and 390px mobile widths. The capture loaded all visible lazy images before saving and asserted no broken visible images or horizontal overflow. The mobile hero was visually adjusted to align its copy at the top after the initial responsive check.

Known limitations: the reference site and external photo host remain blocked by environment policy. The replacement images are clearly disclosed AI-generated **temporary placeholders**, not actual inventory photographs; provenance is documented in `image-sources.md`. Real contact details, social URLs, bank details, email delivery, tax/legal review and hosting remain owner configuration tasks. Physical iOS/Android and Safari testing was not performed. The original WooCommerce `.htaccess` regeneration advisory appeared during setup; product/category permalinks and complete checkout passed functional tests.
