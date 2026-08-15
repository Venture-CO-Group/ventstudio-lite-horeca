<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
lt_admin_head('Help & docs');
lt_admin_sidebar('docs');
lt_admin_top('Help', 'Help & documentation', '<button class="btn-studio primary" type="button" data-tour-start>▶ Take the guided tour</button>');
?>
<div class="admin-body">
  <div class="docs">
    <div class="st-hint">New here? Click <strong>Take the guided tour</strong> above for a quick highlighted walkthrough. Below is the full reference for running the VentStudio webshop.</div>

    <nav class="docs-toc">
      <a href="#start">Getting started</a>
      <a href="#dashboard">Dashboard</a>
      <a href="#menu">Menu &amp; allergens</a>
      <a href="#orders">Orders</a>
      <a href="#cancel">Cancel &amp; refunds</a>
      <a href="#invoices">Invoices &amp; PDFs</a>
      <a href="#emails">Email templates</a>
      <a href="#media">Media</a>
      <a href="#settings">Settings, delivery &amp; payments</a>
      <a href="#users">Users &amp; roles</a>
      <a href="#data">Data, backups &amp; MySQL</a>
      <a href="#account">My account</a>
    </nav>

    <section class="docs-sec" id="start">
      <h3>Getting started</h3>
      <p>This admin runs the VentStudio online shop. The left sidebar is your main menu; the top bar shows the page title, quick actions and your account menu (top-right). The <strong>&amp; logo</strong> always returns you to the Dashboard. The site is English-only.</p>
      <p class="tip">Most changes go live the moment you save. Take a backup (<strong>Maintenance → Backups</strong>) before big edits.</p>
    </section>

    <section class="docs-sec" id="dashboard">
      <h3>Dashboard</h3>
      <p>Your webshop at a glance: total orders, revenue (excluding cancelled), orders &amp; takings today, average order value, the split of delivery vs collection, a bar chart of orders by status, your top-selling items, and a quick setup checklist (Stripe, email, menu).</p>
    </section>

    <section class="docs-sec" id="menu">
      <h3>Menu, stock &amp; pre-orders</h3>
      <p><strong>Ordering → Menu</strong> is where you build the menu. Group dishes, and for each item set the <strong>name, description, price, tags</strong> and the new controls below each row.</p>
      <ul>
        <li><strong>Show on website</strong> — untick to hide an item from the site without deleting it. Handy for seasonal or sold-out lines.</li>
        <li><strong>Stock</strong> — leave blank for unlimited. Set a number to track it; when it hits <strong>0 the item disappears from the site automatically</strong> (the stock figure is never shown to customers).</li>
        <li><strong>Pre-order only</strong> + <strong>Lead time (hrs)</strong> — tick for items like the BBQ specials. At checkout the earliest slot is forced that many hours ahead (48 = 2 days) and the item shows a "Pre-order" badge. Both the site and the payment step block anything sooner.</li>
        <li>Allergens are kept on each item for your records but are <strong>currently hidden from the public site</strong>. The checkout shows a general allergy notice instead. (The Allergens page can be switched back on later via a <code>showAllergens</code> setting.)</li>
        <li>Add or remove items and groups; press <strong>Save menu</strong> and it's live instantly. Dish photos live in <code>/assets/img/menu/&lt;slug&gt;.webp</code>.</li>
      </ul>
    </section>

    <section class="docs-sec" id="orders">
      <h3>Orders</h3>
      <p><strong>Ordering → Orders</strong> shows every online order in real time, newest first, with items, customer details, delivery address (with a Google Maps directions link), requested time and payment.</p>
      <p>Move an order through its lifecycle — each step is one click and sends the right email automatically:</p>
      <ul>
        <li><strong>Approve</strong> → emails the customer "we're cooking".</li>
        <li><strong>Preparing</strong> → internal status.</li>
        <li><strong>Out for delivery</strong> (delivery orders) → emails the <strong>courier</strong> the address, a Google-Maps button and an embedded map.</li>
        <li><strong>Delivered / Collected</strong> → emails the customer a thank-you.</li>
      </ul>
      <p>New orders also fire an <strong>"order received"</strong> email to the customer and a <strong>kitchen ticket</strong> to your kitchen address the moment payment succeeds.</p>
      <p class="tip">Every order row has a <strong>⬇ Invoice PDF</strong> download.</p>
    </section>

    <section class="docs-sec" id="cancel">
      <h3>Cancel &amp; refunds</h3>
      <p>The <strong>Cancel / refund</strong> button on an order (you'll be asked to confirm):</p>
      <ul>
        <li>marks the order <strong>cancelled</strong>;</li>
        <li>if it was paid by card, asks <strong>Stripe to refund the full amount</strong> automatically (to the customer's card);</li>
        <li>issues a <strong>credit note</strong> — downloadable as <strong>⬇ Credit note PDF</strong> on the order and in <strong>Invoices</strong>.</li>
      </ul>
      <p>If the Stripe refund can't be completed (e.g. keys not set), you'll see a message asking you to refund manually in Stripe — the credit note is still generated.</p>
    </section>

    <section class="docs-sec" id="invoices">
      <h3>Invoices &amp; PDFs</h3>
      <p><strong>Ordering → Invoices</strong> lists every invoice. Filter by a <strong>date range</strong> (from / to) and see period totals: <strong>gross</strong> and the <strong>VAT included</strong> — handy for your bookkeeper. Download any <strong>Invoice</strong> or <strong>Credit note</strong> as a branded PDF.</p>
      <p>Invoices show your business details, VAT number, a sequential number (the order number), the customer, itemised lines, delivery, the total and the VAT included at your set rate. Customers can also download their own invoice PDF from the order-confirmation page and the confirmation email.</p>
    </section>

    <section class="docs-sec" id="emails">
      <h3>Email templates</h3>
      <p><strong>Ordering → Email templates</strong> lets you edit every transactional email with a visual editor — subject line and message body, wrapped automatically in the branded VentStudio layout. Placeholders like <code>{{number}}</code>, <code>{{name}}</code>, <code>{{items_table}}</code>, <code>{{address_oneline}}</code>, <code>{{map_button}}</code> and <code>{{invoice_button}}</code> are filled per order. Templates: customer order received / cooking / delivered, kitchen ticket and courier job.</p>
    </section>

    <section class="docs-sec" id="media">
      <h3>Media</h3>
      <p>Upload and manage images (dish photos, etc.). New files land in your Uploads folder and can be picked wherever a Browse button appears.</p>
    </section>

    <section class="docs-sec" id="settings">
      <h3>Settings, delivery &amp; payments</h3>
      <p><strong>Settings</strong> holds business details, contact numbers, socials and delivery rules. Delivery settings (fee, minimum order, free-over threshold, city and accepted postcodes) currently: <strong>£2.99 delivery, £15 minimum, free over £30, Your City (IP1–IP5)</strong>. Both delivery and collection are enabled.</p>
      <ul>
        <li><strong>Stripe (payments):</strong> add your keys to the <code>.env</code> file in the web root — <code>LT_STRIPE_SECRET</code> and <code>LT_STRIPE_PUBLISHABLE</code>. Use <code>sk_test_…</code>/<code>pk_test_…</code> to test, then swap to live keys. With no keys, ordering runs in pay-on-delivery mode.</li>
        <li><strong>Emails (SMTP):</strong> set <code>LT_SMTP_HOST/PORT/USER/PASS</code> and <code>LT_MAIL_FROM</code> in <code>.env</code>. The kitchen and courier addresses are <code>LT_KITCHEN_EMAIL</code> / <code>LT_COURIER_EMAIL</code>.</li>
        <li><strong>VAT:</strong> VAT number and rate are in Settings and appear on invoices.</li>
      </ul>
    </section>

    <section class="docs-sec" id="users">
      <h3>Users &amp; roles</h3>
      <p>The <strong>Owner</strong> and <strong>Super admins</strong> manage accounts under <strong>Users</strong>. Roles: Super admin (full access incl. users), Admin (all content), Editor (content only). Change a role, reset a password, deactivate or remove a user. The owner account is fixed in configuration and protected.</p>
    </section>

    <section class="docs-sec" id="data">
      <h3>Data, backups &amp; MySQL</h3>
      <p><strong>Where your orders live:</strong> with MySQL enabled (it is — <code>LT_DB_ENABLED=1</code> in <code>.env</code>), orders, content and blog posts are stored in the <strong>database</strong> (<code>lt_orders</code> table). Admin accounts, email templates and uploaded images live in the <code>data/</code> and <code>assets/</code> folders.</p>
      <p class="tip"><strong>Re-uploading the site does NOT delete your orders</strong> — the database is separate from the code files. When you deploy an update, upload the PHP/CSS/JS files but <strong>do not overwrite or delete the <code>data/</code> folder</strong> (it holds <code>orders.json</code> as a fallback, <code>admins.json</code>, <code>email-templates.json</code>) and leave the database untouched. If you ever run in flat-file mode instead of MySQL, your orders are in <code>data/orders.json</code> — back that folder up before re-deploying.</p>
      <ul>
        <li><strong>Maintenance → Backups</strong> — download a snapshot of your content before big changes.</li>
        <li><strong>MySQL migration</strong> — copies default content into the database on first setup.</li>
        <li><strong>Change log</strong> — an audit trail of admin actions (status changes, refunds, saves).</li>
      </ul>
    </section>

    <section class="docs-sec" id="account">
      <h3>My account</h3>
      <p>From the avatar menu (top-right) → <strong>My account</strong>: change your name, sign-in email and password. <strong>Sign out</strong> is in the same menu.</p>
      <p class="tip">You can replay the guided tour any time with the button at the top of this page.</p>
    </section>
  </div>
</div>
<?php lt_admin_foot();
