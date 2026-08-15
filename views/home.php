<?php
$META_TITLE = ''; $META_DESC = tg('seo.description');
require __DIR__ . '/../inc/head.php';
$hero = menu_img('hero') ?: (tg('home.heroImage') && is_file(LT_ROOT . tg('home.heroImage')) ? tg('home.heroImage') : '');
$idx = lt_menu_index();
$favs = ['pulled-pork-burger','pulled-pork-box','loaded-fries','chicken-box','whole-chicken','onion-rings'];
?>
<section class="hero">
  <div class="hero-inner">
    <div>
      <?php $ordOpen = (bool)settings('orderingOpen', true); ?>
      <div class="status-sign <?= $ordOpen?'open':'closed' ?>" role="status">
        <span class="status-top"><span class="status-dot"></span><span class="status-neon"><?= $ordOpen ? 'OPEN' : 'CLOSED' ?></span></span>
        <span class="status-sub"><?= $ordOpen ? "Ordering now — we're open" : 'Pre-order our BBQ specials' ?></span>
      </div>
      <p class="eyebrow" style="color:var(--batter)">The family street food co.</p>
      <h1><?= e(tg('home.heroTitle')) ?></h1>
      <p class="lead"><?= e(tg('home.heroLead')) ?></p>
      <div class="hero-cta">
        <a class="btn btn-primary" href="<?= url('menu') ?>"><?= e(tg('home.ctaPrimary')) ?></a>
        <a class="btn btn-ghost" href="<?= url('menu') ?>#burgers"><?= e(tg('home.ctaSecondary')) ?></a>
      </div>
    </div>
    <div class="hero-media<?= $hero ? '' : ' ph' ?>">
      <?php if ($hero): ?><img src="<?= e($hero) ?>" alt="VentStudio street food van">
      <?php else: ?><img src="/assets/img/brand/logo.svg" alt="VentStudio"><?php endif; ?>
    </div>
  </div>
</section>

<div class="strip"><?= e(tg('home.strip')) ?></div>

<section class="section features">
  <div class="wrap">
    <div class="feature-grid">
      <?php foreach ((array)g('home.features') as $i => $f): ?>
        <div class="feature">
          <div class="ic">&amp;</div>
          <h3><?= e($f['title'] ?? '') ?></h3>
          <p><?= e($f['text'] ?? '') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--paper-2)">
  <div class="wrap">
    <div class="sec-head">
      <div><p class="eyebrow"><?= e(tg('home.featuredNote')) ?></p><h2>Fresh from our kitchen.</h2></div>
      <a class="btn btn-dark btn-sm" href="<?= url('menu') ?>">Full menu</a>
    </div>
    <div class="menu-grid">
      <?php foreach ($favs as $slug): if (empty($idx[$slug])) continue; $it = $idx[$slug]; if (!lt_item_visible($it)) continue; $im = menu_img($slug); ?>
        <div class="dish">
          <div class="dish-img<?= $im ? '' : ' ph' ?>">
            <?php if ($im): ?><img src="<?= e($im) ?>" alt="<?= e($it['name']) ?>"><?php else: ?><img src="/assets/img/brand/logo.svg" alt="" style="width:46%;margin:auto;height:100%;object-fit:contain"><?php endif; ?>
          </div>
          <div class="dish-body">
            <h3><?= e($it['name']) ?></h3>
            <p class="dish-desc"><?= e($it['desc']) ?></p>
            <div class="dish-foot">
              <span class="pill"><?= e(money($it['price'])) ?></span>
              <button class="add-btn" data-add="<?= e($slug) ?>" data-name="<?= e($it['name']) ?>" data-price="<?= e($it['price']) ?>">Add +</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--griddle);color:var(--batter)">
  <div class="wrap" style="text-align:center">
    <h2 style="color:var(--batter);font-size:clamp(2rem,5vw,3.2rem)">See you soon.</h2>
    <p style="max-width:52ch;margin:14px auto 24px;opacity:.9">Order ahead for collection, or catch the van across Your Region.</p>
    <div class="hero-cta" style="justify-content:center">
      <a class="btn btn-primary" href="<?= url('menu') ?>">Order online</a>
      <a class="btn btn-honey" href="<?= url('contact') ?>">Find the van</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../inc/footer.php'; ?>
