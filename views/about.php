<?php
$META_TITLE = 'About'; $META_DESC = tg('about.lead');
require __DIR__ . '/../inc/head.php';
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow" style="color:var(--griddle)">The idea</p>
    <h1><?= e(tg('about.title')) ?></h1>
    <p><?= e(tg('about.lead')) ?></p>
  </div>
</section>
<section class="section">
  <div class="wrap" style="display:grid;grid-template-columns:1.3fr .7fr;gap:40px;align-items:start">
    <div class="prose">
      <p><?= e(tg('about.body')) ?></p>
      <ul class="ticks">
        <?php foreach ((array)g('about.points') as $p): ?><li><?= e($p) ?></li><?php endforeach; ?>
      </ul>
      <a class="btn btn-primary" href="<?= url('menu') ?>">See the menu</a>
    </div>
    <div class="hero-media ph" style="aspect-ratio:1/1;box-shadow:none;border:1px solid rgba(27,21,18,.1)">
      <img src="/assets/img/brand/logo.svg" alt="VentStudio" style="width:80%">
    </div>
  </div>
</section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
