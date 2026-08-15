<?php
$META_TITLE = 'Find us'; $META_DESC = tg('contact.lead');
require __DIR__ . '/../inc/head.php';
$phone = settings('phone',''); $wa = settings('whatsapp',''); $email = settings('email','');
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow" style="color:var(--griddle)">Your Region</p>
    <h1><?= e(tg('contact.title')) ?></h1>
    <p><?= e(tg('contact.lead')) ?></p>
  </div>
</section>
<section class="section">
  <div class="wrap">
    <div class="contact-cards">
      <div class="ccard">
        <span class="mono">Order online</span>
        <strong>Skip the queue</strong>
        <p>Order for collection and we'll text you when it's ready.</p>
        <a href="<?= url('menu') ?>">Order now →</a>
      </div>
      <div class="ccard">
        <span class="mono">Call</span>
        <strong><?= e($phone) ?></strong>
        <p>Call ahead and we'll get it on.</p>
        <?php if ($phone): ?><a href="tel:<?= e(str_replace(' ','',$phone)) ?>">Call the van →</a><?php endif; ?>
      </div>
      <div class="ccard">
        <span class="mono">WhatsApp</span>
        <strong>Message us</strong>
        <p>Send your order or ask where we are today.</p>
        <?php if ($wa): ?><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">Open WhatsApp →</a><?php endif; ?>
      </div>
    </div>
    <div class="notice" style="margin-top:26px"><strong>Book the van.</strong> <?= e(tg('contact.bookingNote')) ?> <?php if ($email): ?>Email <a href="mailto:<?= e($email) ?>" style="color:var(--griddle);font-weight:700"><?= e($email) ?></a>.<?php endif; ?></div>
    <div class="fhrs-strip">
      <img src="/assets/img/brand/fhrs-5.svg" alt="Food Hygiene Rating 5 — Very Good" class="fhrs-img">
      <p>We're proud of our <strong>5 out of 5 Food Hygiene Rating</strong> — the highest awarded by the Food Standards Agency. Clean kitchen, fresh food, every time.</p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
