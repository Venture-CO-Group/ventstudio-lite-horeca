# Setup guide — VentStudio Lite HoReCa

## 1. Upload
Copy all files to your web root (e.g. `public_html/`). Keep the dotfiles (`.htaccess`, and your `.env` if you create one).

## 2. Permissions (flat-file mode)
So PHP can persist changes, make these writable:
- `content.json` (created on first save) and the project root — `664` / `775`
- `data/` folder and its `.json` files — `664` / `775`

## 3. First run
- Open the site — it shows the demo menu.
- Go to `/admin/`, log in with `admin@example.com` / `changeme123`.
- **Admin → Users:** change the password / add your own owner account, remove the demo.
- **Admin → Menu, Hours, Settings:** replace the demo content with yours.

## 4. Payments (Stripe)
1. Create a Stripe account, grab your API keys.
2. In `.env`: set `LT_STRIPE_SECRET` and `LT_STRIPE_PUBLISHABLE` (test keys first, then live).
3. Currency follows `currencySymbol` / `LT_CURRENCY`.
(PayPal is optional via `LT_PAYPAL_*`.)

## 5. Email (SMTP)
Fill the `LT_SMTP_*`, `LT_MAIL_FROM*` and `LT_MAIL_TO` values in `.env` from your mail provider.
Set `LT_KITCHEN_EMAIL` and `LT_COURIER_EMAIL` to route kitchen tickets / delivery jobs.
Templates are editable in **Admin → Emails**.

## 6. MySQL (optional)
Use this when you want the data in a database instead of flat files.
1. Create a database + user at your host.
2. Import `schema.sql` (phpMyAdmin → Import, or `mysql db < schema.sql`). This creates the `lt_*` tables.
3. In `.env`: `LT_DB_ENABLED=1` plus `LT_DB_HOST/NAME/USER/PASS`.
4. **Admin → Config → Migrate → Run migration** — copies content, posts and existing orders into the DB.

If the DB is down or a table is missing, the site automatically falls back to flat files, so it never hard-fails.

## 7. Go live checklist
- [ ] Real admin account created, demo removed
- [ ] Logo, colours, favicons replaced
- [ ] Menu, prices, photos, hours, delivery area set
- [ ] Business/legal details filled (Terms, Privacy, Cookies read from settings)
- [ ] Stripe **live** keys in `.env`
- [ ] SMTP tested (place a test order)
- [ ] `.env` is NOT world-readable and NOT committed
