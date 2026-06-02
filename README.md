# Eksait

Eksait is a Laravel e-commerce backend with a JSON API, Filament admin panel, cart and checkout flows, product variants, Econt delivery integration, Stripe card payments, and Bulgarian localization.

## Features

- Product catalog with categories, images, related products, variants, stock state, dimensions, and weights.
- Guest and authenticated carts with session-based cart recovery and merge-on-login behavior.
- Checkout flow with bank transfer, cash on delivery, and optional Stripe payment sessions.
- Econt shipping support for address, office, and automatic post station delivery.
- Order, shipment, settings, product, category, banner, message, user, and sales management through Filament.
- Shipment creation, tracking emails, order confirmation emails, and admin notifications through queued jobs and listeners.
- Public API endpoints for products, cart, checkout, authentication, contact messages, orders, and home banners.
- PHPUnit coverage for cart behavior, checkout cleanup, stock consistency, settings, and weight calculations.

## Tech Stack

- PHP 8.3
- Laravel 13
- Laravel Sanctum
- Filament 4
- Spatie Laravel Permission
- Stripe PHP SDK
- Maatwebsite Excel
- Vite 7
- Tailwind CSS 4
- PHPUnit 12

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL or another Laravel-supported database
- Stripe account credentials, if Stripe payments are enabled
- Econt credentials, if live Econt shipping is enabled

## Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/demirdemirev101/eksait.git
cd eksait
composer install
npm install
```

Create the environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then run migrations:

```bash
php artisan migrate
```

Build frontend assets:

```bash
npm run build
```

The project also includes a Composer setup script that installs dependencies, creates `.env`, generates the key, runs migrations, installs npm packages, and builds assets:

```bash
composer run setup
```

## Environment Configuration

Important `.env` values:

```env
APP_NAME=Eksait
APP_URL=http://localhost
FRONTEND_URL=http://localhost:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eksait
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
SESSION_DRIVER=database

STRIPE_SK=
STRIPE_PK=
STRIPE_WEBHOOK_SECRET=

ECONT_ENABLED=false
ECONT_SANDBOX=true
ECONT_VERIFY_SSL=true
ECONT_BASE_URL=https://demo.econt.com/ee/services
ECONT_TRACK_URL=
ECONT_MAX_PACK_WEIGHT_KG=30
ECONT_CARGO_DIMENSION_FROM_CM=60
ECONT_USERNAME=
ECONT_PASSWORD=
ECONT_SENDER_NAME="Eksait"
ECONT_SENDER_PHONE=
ECONT_SENDER_OFFICE=
ECONT_SENDER_CITY=
ECONT_SENDER_POSTCODE=
ECONT_SENDER_STREET=
ECONT_SENDER_NUM=

BANK_TRANSFER_COMPANY=
BANK_TRANSFER_IBAN=
BANK_TRANSFER_BANK=
BANK_TRANSFER_BIC=
BANK_TRANSFER_CURRENCY=EUR

SEED_ADMIN_EMAIL=
SEED_ADMIN_PASSWORD=
SEED_ADMIN_NAME=
SEED_ADMIN_PHONE=
```

Set `FRONTEND_URL` to the URL of the React or storefront client that consumes this API. Stripe checkout success and cancel URLs are built from this value.

Use one of these values for `ECONT_BASE_URL` depending on the environment:

- `https://demo.econt.com/ee/services` for sandbox testing
- `https://ee.econt.com/services` for production credentials

`php artisan test:econt-api` reads `ECONT_BASE_URL`, `ECONT_VERIFY_SSL`, `ECONT_USERNAME`, and `ECONT_PASSWORD` through `config/services.php`. It does not print the configured username or password.

## Running Locally

Start the Laravel server, queue worker, and Vite dev server together:

```bash
composer run dev
```

Or run them separately:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

The default API server runs at `http://127.0.0.1:8000`. The Filament admin panel is registered at `/admin`.

## Testing

Run the test suite:

```bash
composer test
```

Run Laravel Pint formatting:

```bash
./vendor/bin/pint
```

Check dependencies for known advisories:

```bash
composer audit
npm audit
```

## API Overview

Public endpoints:

```text
GET    /api/products
GET    /api/products/search
GET    /api/home-banner
GET    /api/checkout/payment-methods
POST   /api/contact
POST   /api/login
POST   /api/register
POST   /api/forgot-password
POST   /api/reset-password
POST   /api/stripe/webhook
```

Cart and checkout endpoints support authenticated users through Sanctum and guests through a cart session id:

```text
GET    /api/cart
POST   /api/cart/add/{product}
PATCH  /api/cart/update/{product}
DELETE /api/cart/delete/{product}
DELETE /api/cart
GET    /api/checkout/econt-offices
POST   /api/checkout/calculate-shipping
POST   /api/checkout
```

Authenticated endpoints:

```text
GET    /api/me
GET    /api/user
POST   /api/logout
GET    /api/orders
```

For guest cart continuity, send the same session id in one of these places:

- `session_id` request body field
- `sessionId` request body field
- `session_id` query parameter
- `sessionId` query parameter
- `X-Cart-Session-Id` header

Treat guest cart session ids as bearer identifiers. The value must be 16-128 characters and contain only letters, numbers, underscores, or dashes.

Frontend guidance:

- Prefer `crypto.randomUUID()` or a cryptographically random token.
- Persist the value in local storage or equivalent guest-cart state.
- If the frontend does not have a session id yet, call `GET /api/cart`; the response includes a generated `session_id`.
- Send the same value on all guest cart, checkout, login, and register requests until the user authenticates.
- Invalid session ids return `422` validation errors.
- Cart endpoints are rate-limited and may return `429` when abused.
- Checkout and Econt lookup endpoints are rate-limited separately and may also return `429`.

## Checkout Flow

1. Add products or variants to the cart.
2. Fetch available payment methods with `GET /api/checkout/payment-methods`.
3. For Econt delivery, fetch offices with `GET /api/checkout/econt-offices?city=...`.
4. Estimate shipping with `POST /api/checkout/calculate-shipping`.
5. Submit the order with `POST /api/checkout`.
6. If the selected payment method is `stripe`, redirect the customer to the returned `checkout_url`.
7. Stripe webhook updates the order payment state after payment events.

## Admin Panel

Filament resources are available for:

- Products, variants, images, and related products
- Categories
- Orders, items, and shipments
- Sales
- Settings
- Home banners
- Contact messages
- Users

Admin access is protected by application middleware and permissions. Create or promote an admin user according to your deployment process before exposing the panel.

## Background Jobs and Events

The application uses queued listeners and jobs for order and shipment side effects:

- Order confirmation emails
- Admin order notifications
- Bank transfer shipping calculation failures
- Econt shipment creation
- Shipment tracking emails
- Shipment failure notifications
- Guest cart clearing after order placement

Use a queue worker in local development and production:

```bash
php artisan queue:work
```

## Useful Commands

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan test
php artisan queue:work
npm run dev
npm run build
```

There are also Econt-focused console commands for integration checks:

```bash
php artisan test:econt-api
php artisan test:econt-minimal
```

`test:econt-minimal` is a diagnostic command. Depending on options and credentials it can call the Econt API. Use `--dump` only when you intentionally need raw payload/response data, and avoid running it with production credentials unless you are testing a real integration path.

## Project Structure

```text
app/Filament/Resources   Admin resources and Filament screens
app/Http/Controllers     API and web controllers
app/Http/Requests        Request validation
app/Models               Eloquent models
app/Services             Cart, checkout, payment, settings, stock, shipping services
app/Services/Econt       Econt API adapter and payload mapping
app/Jobs                 Queue jobs
app/Listeners            Event listeners
database/migrations      Database schema
resources/views/emails   Mail templates
routes/api.php           JSON API routes
tests/Feature            Feature tests
```

## Production Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Use a real `APP_KEY`; never share or rotate it casually after encrypted data exists.
- Configure production `APP_URL`, `FRONTEND_URL`, mail, Stripe, Econt, and bank transfer values through environment secrets.
- Run `composer install --no-dev --optimize-autoloader`.
- Run `npm run build`.
- Run `php artisan migrate --force`.
- Cache framework metadata after configuration is final:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

- Run a queue worker or supervisor for queued mail, shipping, and notification jobs.
- Run `composer audit` and `npm audit` before deployment.
- Do not seed production with demo users. To create an admin through the seeder, set `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD`.

## Security Notes

- Do not commit real Stripe, Econt, bank, mail, or production database credentials.
- Keep `.env.example` as a safe template only. Store real values in `.env` or your deployment secret manager.
- Keep `APP_DEBUG=false` in production.
- Review `DatabaseSeeder` before using `php artisan migrate:fresh --seed`; demo users are only created outside production unless explicit seed admin credentials are provided.
- Guest cart `session_id` values should be random and treated as sensitive because they identify a guest cart.
- Public checkout, cart, and Econt lookup endpoints are rate-limited; tune the limits in `AppServiceProvider` for the deployment environment.
- Keep Composer and npm dependencies updated when `composer audit` or `npm audit` reports advisories.

## License

This project is open-sourced software licensed under the MIT license.
