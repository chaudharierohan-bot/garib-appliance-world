# Open the store on Windows

Use Windows 10/11 with WSL2 and Docker Desktop's Linux containers. The cloud instance is a separate machine: you must start a local copy before Windows `localhost` can show this website. No hosting, DNS or public deployment is required.

## 1. Install Windows prerequisites

Open **PowerShell as Administrator** and run these commands if WSL/Ubuntu and Docker Desktop are not already installed:

```powershell
wsl --install -d Ubuntu
winget install --exact --id Docker.DockerDesktop
```

Restart Windows when installation requests it. Open **Ubuntu** from Start and finish creating its Linux username/password. Open **Docker Desktop**, complete its initial setup, and wait for the engine to start.

In Docker Desktop, enable **Settings → General → Use the WSL 2 based engine**, then **Settings → Resources → WSL Integration → Ubuntu** and apply/restart if requested. Use Linux containers. Setting names can vary slightly across Docker Desktop versions.

## 2. Download and set up the project

Run all remaining terminal commands inside the **Ubuntu terminal**, not PowerShell:

```bash
sudo apt-get update
sudo apt-get install -y git curl openssl ca-certificates

docker version
docker compose version

mkdir -p ~/Projects
cd ~/Projects
git clone --branch feat/storefront --single-branch https://github.com/chaudharierohan-bot/garib-appliance-world.git
cd garib-appliance-world

read -rsp "Choose a local admin password (16+ characters): " WP_ADMIN_PASSWORD
printf '\n'
export WP_ADMIN_PASSWORD
bash ./scripts/setup.sh
unset WP_ADMIN_PASSWORD

bash ./scripts/health.sh
docker compose ps
```

The password is hidden while typing. Remember it; it is the password for this new local installation, not the Ubuntu password or a cloud-instance password. First setup downloads container images and the verified WooCommerce archive, creates `.env` with random local database passwords, installs WordPress, activates WooCommerce and the theme, and imports demonstration products and photorealistic placeholder images. Allow several minutes on first use. Do not close Docker Desktop during setup.

`docker version` must show a Server section. If it cannot connect, start Docker Desktop and check Ubuntu WSL Integration before continuing. If you already cloned the repository, use that checkout instead of cloning over it; preserve your edits and switch/update `feat/storefront` as appropriate.

## 3. Open the website and administrator dashboard

After the health script says **Store catalogue is ready**, type these addresses in your Windows browser:

- Store: `http://localhost:8080/`
- Shop: `http://localhost:8080/shop/`
- Cart: `http://localhost:8080/cart/`
- Checkout: `http://localhost:8080/checkout/`
- Customer account: `http://localhost:8080/my-account/`
- WordPress administrator: `http://localhost:8080/wp-admin/`

The default administrator username is **storeadmin**; the password is the one you chose above. The setup script preserves an existing installation and does not reset its administrator password. To use another username/email, set `WP_ADMIN_USER` and `WP_ADMIN_EMAIL` before first setup. On first login, inspect **Products**, **WooCommerce → Orders**, **WooCommerce → Settings → Payments**, and **Appearance → Themes / Customize**. Additional WooCommerce onboarding prompts do not require connecting paid services for this demo.

The local stack runs on loopback only. Port 8080 must be free. If it is occupied, stop the other local app before installation. For a genuinely new installation on a different port, create `.env` from `.env.example`, replace both database password placeholders with unique values, set a free `PORT`, and set `WP_URL` to the matching local address before setup. Changing the port after installation also requires a WordPress URL migration; changing `.env` alone is insufficient.

## 4. Test shopping safely

Use fictional customer information and `@example.test` email addresses only:

1. Search for a demo product, open its details and specifications, and add an in-stock item to the cart.
2. Change its quantity, update the cart, and verify SRD prices/totals. Remove it and confirm the cart becomes empty.
3. Add an item again. At checkout enter fictional details, including country **Suriname**, city and **State / County** (for example Paramaribo).
4. Select **Cash on delivery**, place the local demonstration order, and check the confirmation page. In WordPress admin → WooCommerce → Orders, it should be **Processing**.
5. Repeat with **Direct bank transfer**. The local order should be **On hold**. Bank details are intentionally unconfigured: do not transfer money.
6. Register a customer at `/my-account/`, log out/in, edit account details and addresses, and review order history. Orders placed as a logged-in customer should appear in that account. Password-reset email delivery needs a configured mail service.

Sample shipping is SRD 150; taxes are disabled pending business configuration. Demo orders consume real local WooCommerce stock. Replenish demo stock in Products before repeated quantity/checkout tests when necessary.

The automated browser suite was verified in the cloud Linux environment. If you want to rerun it inside Ubuntu, install a supported Node.js LTS version, then run:

```bash
npm ci
npx playwright install --with-deps chromium
npm test
docker compose run --rm -T cli wp eval-file /setup/verify.php
```

Run automation only on this demonstration installation. It creates fictional orders, customer accounts and newsletter requests. The backend verification expects both test payment-method orders and a newsletter request, so run the complete browser suite first.

## 5. Stop and start later

Inside Ubuntu:

```bash
cd ~/Projects/garib-appliance-world
docker compose stop
```

Next time, first start Docker Desktop, then run:

```bash
cd ~/Projects/garib-appliance-world
docker compose up -d
bash ./scripts/health.sh
```

Your database, products, images, orders and users are retained in Docker volumes. Do not use `docker compose down -v` unless you intend to delete that local database and WordPress files. You do not need to rerun setup on every start.

If a page fails, inspect service state and logs:

```bash
docker compose ps
docker compose logs --tail=80 wordpress db
```

## Current branding and development limits

The official navy/green Garib logo shown in chat is **not installed**. The theme currently renders its original G/text placeholder in the header and text branding in the footer. The repository file `assets/images/garib-official-logo.png.png` is a webpage screenshot, not a standalone logo, and is not used as a logo. The real image file is still needed for that replacement.

Product photos are disclosed synthetic demo placeholders. Real contact details, social URLs, bank information, email delivery, tax/legal policies and hosting remain unconfigured. These instructions prepare a local demonstration site; Windows/WSL execution must be verified on your own computer. No hosting deployment is performed.
