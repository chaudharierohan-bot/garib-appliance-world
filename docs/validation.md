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
