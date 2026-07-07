# Cazera Hospitality ERP, POS, and Website Platform

Cazera is a Laravel and Livewire hospitality operations platform for restaurants, bars, lounges, event venues, and multi-branch hospitality businesses.

The application combines:

- a branch-aware backoffice ERP
- a multi-module POS
- inventory and menu stock control
- sales, payments, debtors, and cash register tracking
- accounting-oriented dashboards and reports
- website content management
- a public hospitality website
- progressive web app support

The public website is a showcase and contact channel. It is not an ecommerce checkout, public cart, or online booking engine unless that scope is added later.

## Tech Stack

- PHP 8.2+
- Laravel 12
- Livewire 4
- Laravel Blade
- Alpine.js
- Tailwind CSS 4
- Vite
- MySQL or MariaDB
- Laravel Fortify authentication
- Spatie Laravel Permission
- Livewire Alert
- Blade Heroicons

## Core Features

### Backoffice

The backoffice is available under:

```text
/backoffice
```

It includes:

- quantitative, financial, analytical, and home dashboards
- branch management
- module management
- role and permission management
- user and staff management
- branch-to-staff assignment
- module-to-staff assignment
- customer records and customer history
- menu categories and menu items
- trackable menu item quantity control
- POS sales
- kitchen display
- daily sales records
- sales list and sale editing
- split payments
- outstanding balance collection
- debtors
- refunds
- cash register transactions
- table management and table release flow
- inventory categories, items, stocks, locations, adjustments, movements, and transfers
- suppliers
- daily production costs
- expenses and expense categories
- net revenue analysis
- maintenance requests
- reports for sales, inventory, finance, maintenance, production costs, and cash registers
- website pages, settings, gallery, events, careers, testimonials, reviews, and contact messages
- activity logs and audit logs

### POS

The POS is module-aware but uses a shared cart, shared order summary, and shared payment block.

Each cart line keeps its module identity, so mixed-module sales can be processed in one order while the system still splits taxes, discounts, payments, cash register transactions, and module-level reporting correctly behind the scenes.

Important POS behavior:

- users only see modules they can access
- one cart can contain items from multiple POS modules
- each item line stores its module
- payment allocation is split across sale item modules
- table orders can reserve/occupy dining tables
- completed or fully paid table orders can release tables
- unpaid sales can be edited
- paid sales are protected from POS edit flow
- trackable menu items are stock-checked before cart updates and sale completion
- stock movements are recorded through menu item adjustments

### Tables

Dining tables are managed in the backoffice and can be attached to dine-in POS orders.

Table release rules:

- tables are occupied when attached to active dine-in orders
- tables are released when an order is completed or fully paid
- unreleased eligible tables can be manually released from daily sales or sales lists
- historical orders using a table do not make the release button appear unless the table is still unreleased for an eligible sale

### Inventory and Stock

Cazera has two related stock concepts:

- menu item stock for trackable sold items
- inventory item stock for broader inventory operations

Trackable menu items:

- use quantity for stock availability
- reduce quantity when sold
- use cost price in profit calculations when sold
- use selling price for unsold holdings valuation

Inventory operations include:

- stock adjustments
- stock movements
- stock transfers
- purchase order requests
- branch/module-aware inventory reports

### Finance and Reporting

Reports and dashboards are branch-aware and module-aware.

Financial calculations are designed to use:

- sale totals from sales and sale items
- module allocation from sale items
- cost price for sold trackable items
- selling price for unsold trackable holdings
- cedi-formatted monetary values in the UI
- expenses, production costs, refunds, payments, and cash register transactions where applicable

### Website

The public website includes:

- homepage
- branches
- branch detail pages
- menus/services
- menu item detail pages
- about page
- gallery
- events
- careers
- reviews
- testimonials
- contact page
- WhatsApp/contact calls to action
- SEO metadata and structured content support

Website content is managed from the backoffice.

### PWA Support

The app includes progressive web app support:

- `public/manifest.webmanifest`
- `public/sw.js`
- offline fallback page at `public/offline.html`
- PWA icons under `public/pwa`
- service worker registration in the backoffice, auth, and website layouts

The service worker caches static assets and serves an offline page for failed navigations. It does not cache authenticated Laravel/Livewire HTML screens, which avoids serving stale private backoffice data.

Production PWA installation requires HTTPS. Localhost is normally accepted by browsers for development.

## Access Model

Cazera uses Spatie Laravel Permission plus branch/module access helpers.

### Super Admin

- can access every branch
- can access every module
- can manage users, roles, permissions, branches, modules, website settings, logs, and operational records

### Branch Manager

- can access assigned branches
- inherits module access inside assigned branches
- can manage branch operations according to assigned permissions

### Regular Staff

- can access assigned branches
- can access assigned modules where module restrictions apply
- can perform only actions allowed by assigned roles/permissions

Common staff roles include:

- POS Operator
- Kitchen Staff
- Accountant
- Inventory Manager
- Branch Manager

## Branch and Module Rules

- Users can be assigned to multiple branches.
- Users can be assigned to multiple modules.
- Branch managers inherit all modules in their assigned branches.
- Super admins bypass branch and module restrictions.
- Operational records should always preserve branch and module scope where applicable.
- Module-based sales calculations should come from sale items, not from a single sale-level module field.

## Requirements

Install these before setup:

- PHP 8.2 or newer
- Composer
- Node.js and npm
- MySQL or MariaDB
- PHP extensions commonly required by Laravel, including `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, and `gd`

For XAMPP development, make sure Apache and MySQL are running.

## Installation

Clone or place the project in your web root, for example:

```bash
C:\xampp\htdocs\cazera
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Generate the app key:

```bash
php artisan key:generate
```

Configure database settings in `.env`:

```env
APP_NAME=Cazera
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/cazera/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cazera
DB_USERNAME=root
DB_PASSWORD=
```

Create the database:

```sql
CREATE DATABASE cazera;
```

Run migrations:

```bash
php artisan migrate
```

Seed default data:

```bash
php artisan db:seed
```

Create the storage symlink:

```bash
php artisan storage:link
```

Build frontend assets:

```bash
npm run build
```

## Development

Run the Laravel development stack:

```bash
composer run dev
```

That script starts:

- Laravel server
- queue listener
- Laravel Pail logs
- Vite dev server

You can also run services separately:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

For XAMPP, you may also serve through Apache and use Vite separately:

```bash
npm run dev
```

## Production Deployment

Typical production steps:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Recommended production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

Use HTTPS in production, especially for PWA install support and secure authentication.

## Queue and Scheduler

Run a queue worker in production if queued work is enabled:

```bash
php artisan queue:work --tries=3
```

Set the Laravel scheduler to run every minute:

```bash
php artisan schedule:run
```

Cron example:

```cron
* * * * * cd /path/to/cazera && php artisan schedule:run >> /dev/null 2>&1
```

## Default Users

The seeders create common operational users such as:

```text
admin@cazera.test
manager@cazera.test
pos@cazera.test
kitchen@cazera.test
accountant@cazera.test
```

Default password:

```text
password
```

Change seeded passwords before using the system outside development.

## Useful Commands

Run tests:

```bash
php artisan test
```

List routes:

```bash
php artisan route:list
```

Clear caches:

```bash
php artisan optimize:clear
```

Rebuild Blade cache:

```bash
php artisan view:clear
php artisan view:cache
```

Rebuild frontend assets:

```bash
npm run build
```

Run Laravel Pint:

```bash
vendor/bin/pint
```

## Project Structure

Important folders:

```text
app/Livewire/Backoffice      Backoffice Livewire components
app/Livewire/Website         Public website Livewire components
app/Models                   Domain models and relationships
app/Observers                Audit/activity observers
app/Support                  Shared domain helpers
database/migrations          Database schema
database/seeders             Seed data
public                       Public entry point, assets, PWA files
resources/views/components   Reusable Blade components
resources/views/livewire     Livewire views
resources/views/partials     Shared Blade partials
routes/web.php               Public and backoffice routes
```

PWA files:

```text
public/manifest.webmanifest
public/sw.js
public/offline.html
public/pwa/icon-192.png
public/pwa/icon-512.png
public/pwa/icon.svg
public/pwa/maskable-icon.svg
resources/views/partials/pwa-head.blade.php
resources/views/partials/pwa-register.blade.php
```

## Development Guidelines

- Preserve branch and module access rules.
- Use existing helpers such as `accessible()`, `accessibleBranches()`, and `accessibleModules()`.
- Keep sale module calculations based on sale items.
- Keep monetary values formatted consistently in Ghana cedis.
- Use transactions for multi-record sale, stock, payment, and accounting operations.
- Use Livewire Alert for confirmations and user-facing feedback.
- Avoid caching authenticated HTML in the service worker.
- Keep website-facing content hospitality-focused.
- Do not add public booking, cart, or checkout behavior unless the product scope changes.

## Troubleshooting

If assets are missing:

```bash
npm run build
php artisan view:clear
```

If uploaded files are not visible:

```bash
php artisan storage:link
```

If routes, config, or views look stale:

```bash
php artisan optimize:clear
```

If the PWA does not update immediately:

- hard refresh the browser
- unregister the service worker in browser developer tools
- clear site data
- reload over HTTPS or localhost

## License

This project is proprietary application code for the Cazera hospitality platform unless a separate license is provided by the project owner.
