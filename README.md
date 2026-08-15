# VentStudio Lite — HoReCa

A lightweight, self-hosted **ordering website for hospitality businesses** (cafés, restaurants, food vans, dark kitchens). Menu, cart, checkout with card payment, invoices, branded transactional emails and a simple admin — all editable without touching code.

This is a **reusable template**. The architecture and features are fixed; the branding, theme and products on top are meant to be swapped per project.

## Features

- **Menu system** — groups & items with description, price, tags, photo, per-item **visibility toggle** and **stock** (0 = auto-hidden from the site).
- **Cart & checkout** — delivery or collection, address + postcode gating, minimum order, delivery fee, free-over threshold.
- **Opening hours + open/closed switch** — day/time slot picker up to 3 weeks ahead; a homepage **OPEN / CLOSED** status the admin can flip.
- **Pre-order items** — mark an item as pre-order with a lead time (e.g. 48 h); checkout forces the earliest slot and the item shows a badge. Pre-orders still work while the shop is "closed".
- **Payments** — Stripe Checkout (PayPal optional).
- **Invoices** — HTML + real PDF, per-order and bulk ZIP, with credit notes (sztornó) on refund.
- **Branded emails** — order received / confirmed / ready-to-collect / delivered, plus kitchen ticket & courier job (with map), editable in a WYSIWYG template editor.
- **Allergen data** — kept per item for records; optional public allergen page (off by default) with a checkout allergy notice.
- **Admin** — dashboard, menu editor, orders lifecycle, hours, emails, invoices, users, docs + first-login tour.
- **Storage** — runs on **flat files** out of the box; switch to **MySQL** when you're ready (see below). No build step, no framework.

## Requirements

- PHP 8.0+ (with PDO MySQL if you use the database)
- Apache (an `.htaccess` is included; the site also works without mod_rewrite via physical route folders)

## Quick start (flat-file, zero config)

1. Copy the files to your web root.
2. Make the folder writable by PHP (so it can save `content.json` and `data/`):
   - `content.json`, `data/` → writable (e.g. `chmod 664` files, `775` folders).
3. Visit the site — it serves the demo menu from `content.default.json`.
4. Log into the admin at `/admin/` and change everything.

**Demo admin login:** `admin@example.com` / `changeme123` — change this immediately in Admin → Users.

## Configuration (`.env`)

Copy `.env.example` to `.env` and fill in only what you need (payments, SMTP email, MySQL). With no `.env`, the site still runs on flat files. See `SETUP-GUIDE.md`.

## Using MySQL

1. Import `schema.sql` into your database (creates the `lt_*` tables).
2. Set `LT_DB_ENABLED=1` and the `LT_DB_*` values in `.env`.
3. Admin → Config → **Migrate** copies your content, posts and orders into the database.

If the DB is unreachable or the tables are missing, the app transparently falls back to flat files.

## Customising per project

- **Logo:** replace `assets/img/brand/logo.svg` / `logo.png` (and `logo-pdf.png` for invoices, favicons).
- **Colours / theme:** edit the CSS variables at the top of `assets/css/site.css`.
- **Copy, menu, prices, business details, hours, delivery area:** all in the admin (stored in content), or seed defaults in `content.default.json`.
- **Menu photos:** drop `assets/img/menu/<item-slug>.webp` (or `.jpg`/`.png`).
- **Extras / pre-order rules:** the "add extras to a product" popups activate for groups named `extras` (add-ons) and `burgers` / `wraps` / `boxes` (bases); pre-order behaviour is per-item (`preorder` + `preorderHours`).

## Project structure

```
index.php, router.php, _app.php   entry + routing
inc/                              core: bootstrap, store, orders, emails, invoice, stripe/paypal, checkout
views/                            public pages (home, menu, checkout, about, contact, legal…)
admin/                            admin app (menu, orders, hours, emails, invoices, users, docs…)
assets/                           css, js, brand + menu images
data/                             flat-file store (admins.json + runtime content/orders)
content.default.json              seed content (menu + settings) used until you save/migrate
schema.sql                        optional MySQL schema
```

## Notes

- The internal code prefix is `lt_` / `LT_` (framework namespace, not a brand). Table names, function names and the cart storage key use it.
- Nothing here sends analytics or phones home.

Proprietary — see `LICENSE`.
