# Last-mile Delivery Desk

An authenticated dispatcher workspace for a small delivery fleet. Create delivery stops, assign them to drivers, and move each stop through an enforced delivery status flow from one board.

The demo is populated with fictional drivers and 30 sample addresses across Bucaramanga neighborhoods. Addresses are plain text only; the app does not use live maps, GPS, SMS, payments, or Redis.

## Features

- Driver management with contact, vehicle, and availability details.
- Stops with a delivery address and a time window.
- A dispatcher board grouped by driver with a separate unassigned lane.
- Per-status counts and assignment controls on each stop.
- Enforced transitions: `queued` → `out_for_delivery` → `delivered` or `failed`.
- Authenticated access for dispatchers.

## Requirements

- PHP 8.4+
- Composer
- Node.js and npm
- SQLite (default) or another Laravel-supported database

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

### Demo dispatcher

- Email: `dispatcher@example.com`
- Password: `password`

The demo account is created by the database seeder. Registration is also available for local testing.

### Reset the demo data

This command recreates the database, then seeds the dispatcher, four drivers, and 30 demo stops:

```bash
php artisan migrate:fresh --seed
```

## Tests

Run the PHPUnit and Pest suite with:

```bash
php artisan test
```

## Stack

Laravel 13, Inertia.js, Vue 3, Tailwind CSS, and SQLite/MySQL. No Redis service is required.
