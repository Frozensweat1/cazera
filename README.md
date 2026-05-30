# Cazera Hospitality ERP/POS and Website Platform

Cazera is a Laravel 12 and Livewire 4 hospitality system for running a multi-branch restaurant, bar, lounge, events, inventory, finance, and public showcase experience from one codebase.

It is not currently an online booking, cart, checkout, or ecommerce platform. The public website is designed to make guests want to visit, contact a branch, call, send WhatsApp messages, view menus/services, browse galleries, read reviews, submit testimonials, and discover events.

The backoffice is an operational ERP/POS system for branch-aware hospitality teams.

## Tech Stack

- Laravel 12
- Livewire 4
- Laravel Blade
- Alpine.js
- TailwindCSS 4
- Vite
- Laravel Fortify authentication
- Spatie Laravel Permission
- Livewire Alert
- Blade Heroicons
- MySQL/MariaDB friendly migrations

## Main Areas

### Public Website

The website is a premium hospitality showcase with:

- Homepage with cinematic hero, featured branches, services/categories, menu highlights, testimonials, gallery, events, and contact CTA
- Branch listing and branch detail pages
- Menu/service item detail pages
- About, mission, vision, and brand story content
- Gallery with category support
- Events and promotions
- Careers and public application interest form
- Reviews and testimonial submission
- Contact page with branch contact actions
- SEO metadata and structured page content
- Fallback images/avatars when records do not have uploaded images

Website content is managed through database records and `website_settings.content` overrides. The `WebsiteContentSeeder` provides polished default copy and starter featured content.

### Backoffice

The backoffice is available under:

```text
/backoffice/home
```

It includes:

- Live sales dashboard
- Quantitative, financial, and analytical dashboards
- Branch and module management
- User, role, permission, branch staff, and module staff management
- Customer management and customer history
- Category and menu item management
- Trackable menu item adjustments
- POS ordering
- Kitchen display
- Sales, refunds, debtors, split payments, and transactions
- Cash register accountability and history-oriented reporting
- Inventory categories, items, stock balances, locations, adjustments, movements, and transfers
- Suppliers
- Daily production costs
- Expenses and expense categories
- Net revenue analysis
- Maintenance requests
- Reports for sales, inventory, finance, maintenance, production costs, and cash registers
- Website management for pages, settings, events, gallery, careers, testimonials, reviews, and contact messages
- Activity logs and audit logs

## Access Model

The system uses Spatie Permission with branch and module-aware helpers.

### Super Admin

- Full access to all branches
- Full access to all modules
- Full access to all records
- Can manage branches, roles, permissions, website settings, page content, activity logs, and audit logs

### Branch Manager

- Access is limited to assigned branches
- Has access to all modules inside assigned branches
- Can manage operational branch data, website branch content, POS, inventory, sales, finance, maintenance, and reports according to route permissions

### Regular Role Examples

- POS Operator: POS access
- Kitchen Staff: Kitchen display access
- Accountant: sales, transactions, dashboards, production costs, expenses, reports
- Inventory Manager: inventory and stock operations

Regular users are restricted to assigned branches and assigned modules where module-level access applies.

## Branch and Module Rules

- The first branch record is treated as the default/fallback branch.
- Users can belong to multiple branches through `branch_staff`.
- Regular users can be assigned to modules through `module_staff`.
- Branch managers inherit all modules inside their assigned branches.
- Super admins bypass branch and module restrictions.
- Shared data such as customers can be visible across branches where the business rule requires it.

## Important Business Rules

- No public booking engine
- No public cart
- No checkout system
- Trackable menu items can increase/decrease quantity through adjustments and POS sales
- Only trackable menu items affect menu stock quantity
- Inventory stock movements are wired through stock adjustments and transfers
- Stock transfers are completed by the destination branch
- Refunds affect register expectations according to cash register logic
- Maintenance records can be locked; locked records cannot be deleted
- Activity/audit logs are Super Admin-only

## Installation

Requirements:

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL/MariaDB

Install dependencies:

```bash
composer install
npm install
```

Create environment file and app key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure `.env` database settings, then run:

```bash
php artisan migrate
php artisan db:seed
npm run build
```

For local development:

```bash
composer run dev
```

Or run Laravel and Vite separately:

```bash
php artisan serve
npm run dev
```

## Seeded Users

The default seeder creates common operational users:

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

## Useful Seeders

Seed the full demo dataset:

```bash
php artisan db:seed
```

Seed only website content:

```bash
php artisan db:seed --class=WebsiteContentSeeder
```

`WebsiteContentSeeder` populates:

- Website settings
- Homepage section copy
- Website pages
- Events
- Gallery items
- Career openings
- Testimonials

## Frontend Assets

Development:

```bash
npm run dev
```

Production build:

```bash
npm run build
```

## Validation and Maintenance Commands

Clear and rebuild cached views:

```bash
php artisan view:clear
php artisan view:cache
```

Run tests:

```bash
php artisan test
```

Check routes:

```bash
php artisan route:list
```

## Project Structure

Key folders:

```text
app/Livewire/Backoffice      Backoffice Livewire components
app/Livewire/Website         Public website Livewire components
app/Models                   Domain models and access helpers
app/Observers                Audit/activity observers
app/Support                  Shared website content helpers
database/migrations          Schema definitions
database/seeders             Demo and website content seeders
resources/views/components   Reusable Blade components
resources/views/livewire     Livewire views
routes/web.php               Public and backoffice routes
```

## Notes for Future Development

- Preserve branch/module access rules when adding operational records.
- Use centralized helpers such as `accessible()`, `accessibleBranches()`, and `accessibleModules()`.
- Keep website content database-driven where possible.
- Do not introduce booking, cart, or checkout behavior unless the business scope changes.
- Use Livewire Alert for confirmations and user feedback.
- Keep public website copy guest-facing and hospitality-focused.

## License

This project is proprietary application code for the Cazera hospitality platform unless a separate license is provided by the project owner.
