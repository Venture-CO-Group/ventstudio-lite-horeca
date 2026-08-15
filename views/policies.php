<?php
$META_TITLE = 'Privacy Policy';
require __DIR__ . '/../inc/head.php';
$email = settings('email','hello@yourvenue.co.uk');
$legal = settings('legalName','Example Trading Ltd');
$form  = settings('legalForm','sole trader');
$addr  = settings('address','1 Example Street, Your City, AB1 2CD');
?>
<section class="page-hero"><div class="wrap">
  <p class="eyebrow" style="color:var(--griddle)">Legal</p>
  <h1>Privacy Policy</h1>
  <p>How we collect, use and protect your personal data, in line with UK GDPR and the Data Protection Act 2018. Last updated <?= date('F Y') ?>.</p>
</div></section>
<section class="section"><div class="wrap"><div class="prose">

  <h2>1. Data controller &amp; processors</h2>
  <p>The data controller is <strong><?= e($legal) ?></strong>, trading as <strong>VentStudio Street Food</strong> (<?= e($form) ?>), <?= e($addr) ?>. For any privacy matter contact <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>.</p>
  <p>The website is designed, built and managed on our behalf by <strong>Example Company Ltd</strong>, and is hosted by <strong>Your Hosting Provider</strong> (Hungary, EU). Both act as our data processors under a written agreement and only process personal data on our documented instructions.</p>

  <h2>2. The data we collect</h2>
  <ul class="ticks">
    <li><strong>Order &amp; contact data:</strong> your name, phone number, email address and (for delivery) your delivery address and any notes/access instructions.</li>
    <li><strong>Order details:</strong> the items you buy, amounts, fulfilment choice, requested time and order history.</li>
    <li><strong>Payment data:</strong> handled by Stripe. We receive confirmation of payment and a transaction reference — we never see or store your full card number.</li>
    <li><strong>Technical data:</strong> device/browser information, and — only with your consent — analytics data about how you use the site.</li>
    <li><strong>Communications:</strong> messages you send us by email, phone or WhatsApp.</li>
  </ul>

  <h2>3. How &amp; why we use it (legal bases)</h2>
  <ul class="ticks">
    <li><strong>To perform our contract:</strong> take payment, prepare and deliver your order, and send order updates (received, confirmed, delivered).</li>
    <li><strong>Legitimate interests:</strong> to keep business and order records, prevent fraud and abuse, and improve our service (balanced against your rights).</li>
    <li><strong>Consent:</strong> optional analytics cookies and any marketing messages — you can withdraw consent at any time.</li>
    <li><strong>Legal obligation:</strong> to meet tax, accounting and food-safety record-keeping duties.</li>
  </ul>

  <h2>4. Who we share it with</h2>
  <p>We share data only as needed to run the business, with the following processors and recipients:</p>
  <ul class="ticks">
    <li><strong>Example Company Ltd</strong> — designs, builds and manages this website and its order system on our behalf.</li>
    <li><strong>Your Hosting Provider</strong> (Hungary, EU) — hosts the website and stores order data on our behalf.</li>
    <li><strong>Stripe Payments UK Ltd</strong> — processes payments (see the Stripe Privacy Policy).</li>
    <li><strong>Our email/SMTP provider</strong> — delivers order confirmations and updates.</li>
    <li><strong>Our kitchen and delivery driver</strong> — receive your name, phone and delivery address so they can prepare and bring your order.</li>
  </ul>
  <p>We may also share data with professional advisers or authorities where legally required. We do <strong>not</strong> sell your personal data.</p>

  <h2>5. International transfers</h2>
  <p>Some providers process data outside the UK — for example, our website and order data are hosted by <strong>Your Hosting Provider</strong> in Hungary (EU), and Stripe operates internationally. Where personal data is transferred outside the UK we rely on appropriate safeguards, such as UK adequacy regulations (the EU/EEA benefits from a UK adequacy decision) or the International Data Transfer Agreement/Addendum.</p>

  <h2>6. How long we keep it</h2>
  <p>We keep order and transaction records for as long as needed to run the business and to meet legal and tax obligations (generally up to 6 years), after which we delete or anonymise them. Marketing consents are kept until you withdraw them.</p>

  <h2>7. Security</h2>
  <p>We use appropriate technical and organisational measures to protect your data, including encrypted payment handling via Stripe, access controls and secure hosting. No transmission over the internet is completely secure, but we take reasonable steps to protect information sent to us.</p>

  <h2>8. Your rights</h2>
  <p>Under UK data protection law you have the right to: be informed; access your data; have inaccurate data corrected; have data erased; restrict or object to processing; data portability; and to withdraw consent at any time. To exercise any right, email <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> — we will respond within one month. You can also complain to the Information Commissioner's Office (ICO) at <a href="https://ico.org.uk" target="_blank" rel="noopener">ico.org.uk</a> or 0303 123 1113.</p>

  <h2>9. Marketing</h2>
  <p>We will only send you marketing messages if you have opted in, and every message includes an easy way to opt out. We will never share your details with third parties for their own marketing.</p>

  <h2>10. Children</h2>
  <p>Our service is intended for adults. We do not knowingly collect data from children under 16 without appropriate consent.</p>

  <h2>11. Cookies</h2>
  <p>See our <a href="<?= url('legal/cookies') ?>">Cookie Policy</a> for details of the cookies and local storage we use and how to manage them.</p>

  <h2>12. Changes</h2>
  <p>We may update this policy from time to time; the current version is always published here with its "last updated" date.</p>

  <p style="color:#6b5f57;font-size:.9rem;margin-top:26px"><?= e($legal) ?>, trading as VentStudio Street Food · <?= e($addr) ?> · <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
</div></div></section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
