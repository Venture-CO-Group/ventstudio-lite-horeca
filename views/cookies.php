<?php
$META_TITLE = 'Cookie Policy';
require __DIR__ . '/../inc/head.php';
$email = settings('email','hello@yourvenue.co.uk');
$legal = settings('legalName','Example Trading Ltd');
$addr  = settings('address','1 Example Street, Your City, AB1 2CD');
?>
<section class="page-hero"><div class="wrap">
  <p class="eyebrow" style="color:var(--griddle)">Legal</p>
  <h1>Cookie Policy</h1>
  <p>How we use cookies and similar technologies on this website, under the Privacy and Electronic Communications Regulations (PECR) and UK GDPR. Last updated <?= date('F Y') ?>.</p>
</div></section>
<section class="section"><div class="wrap"><div class="prose">

  <h2>1. What cookies are</h2>
  <p>Cookies and similar technologies (such as browser "local storage") are small files or data stored on your device when you visit a website. They let a site remember your actions and preferences. We keep our use minimal and privacy-friendly.</p>

  <h2>2. Consent</h2>
  <p>Strictly necessary cookies/storage load automatically because the site cannot work without them. Non-essential (analytics) cookies are only set if you choose <strong>"Accept all"</strong> on our cookie banner. You can change your choice at any time via <strong>Cookie settings</strong> in the footer.</p>

  <h2>3. Cookies &amp; storage we use</h2>
  <div class="alg-scroll" style="margin:16px 0">
    <table class="alg-table" style="min-width:640px">
      <thead><tr><th class="alg-item">Name</th><th class="alg-item">Type</th><th class="alg-item">Purpose</th><th class="alg-item">Retention</th></tr></thead>
      <tbody>
        <tr><td class="alg-item">lt_cart_v1</td><td class="alg-item">Local storage (essential)</td><td class="alg-item">Remembers your basket while you browse and order.</td><td class="alg-item">Until cleared</td></tr>
        <tr><td class="alg-item">lt_cookie_choice</td><td class="alg-item">Local storage (essential)</td><td class="alg-item">Remembers your cookie consent choice.</td><td class="alg-item">Until cleared</td></tr>
        <tr><td class="alg-item">PHPSESSID</td><td class="alg-item">Cookie (essential)</td><td class="alg-item">Maintains a secure session (used mainly in the admin area).</td><td class="alg-item">Session</td></tr>
        <tr><td class="alg-item">Stripe cookies</td><td class="alg-item">Cookie (essential, third-party)</td><td class="alg-item">Set by Stripe during payment to process it securely and prevent fraud.</td><td class="alg-item">Per Stripe</td></tr>
        <tr><td class="alg-item">_ga / GTM</td><td class="alg-item">Cookie (analytics, consent)</td><td class="alg-item">Only if you accept — measures visits to help us improve the site.</td><td class="alg-item">Up to 2 years</td></tr>
      </tbody>
    </table>
  </div>

  <h2>4. Third-party services</h2>
  <p>When you pay, Stripe may set its own cookies to process the payment securely — see the Stripe Privacy Policy. If analytics are enabled with your consent, they are provided by Google (Google Analytics / Tag Manager) under Google's terms.</p>

  <h2>5. Managing cookies</h2>
  <p>You can withdraw or change consent any time via <strong>Cookie settings</strong> in the footer. You can also block or delete cookies and clear local storage in your browser settings — note that clearing storage will empty your basket, and blocking essential cookies may stop parts of the site working.</p>

  <h2>6. Changes &amp; contact</h2>
  <p>We may update this policy; the current version is published here. Questions? Email <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>.</p>

  <p style="color:#6b5f57;font-size:.9rem;margin-top:26px"><?= e($legal) ?>, trading as VentStudio Street Food · <?= e($addr) ?></p>
</div></div></section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
