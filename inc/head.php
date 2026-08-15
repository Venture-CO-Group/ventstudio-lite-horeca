<?php
/* expects: $META_TITLE, $META_DESC, optional $META_IMG, $PAGE */
$brand = settings('brandName', 'VentStudio Street Food');
$title = ($META_TITLE ?? '') !== '' ? $META_TITLE . ' — ' . $brand : $brand;
$desc  = $META_DESC ?? tg('seo.description');
$base  = 'https://example.com';
$img   = $META_IMG ?? '/assets/img/brand/logo.png';
$phone = settings('phone', '');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($base . '/' . ($GLOBALS['ROUTE_PATH'] ?? '')) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="<?= e($base . $img) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#E8431F">
<link rel="icon" type="image/svg+xml" href="/assets/img/brand/logo.svg">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/brand/favicon-32.png">
<link rel="apple-touch-icon" href="/assets/img/brand/favicon-180.png">
<link rel="stylesheet" href="/assets/css/site.css">
<?php $gtm = settings('gtmId',''); if ($gtm): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($gtm) ?>');</script>
<?php endif; ?>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"FoodEstablishment","name":"<?= e($brand) ?>","servesCuisine":"Street food, BBQ","url":"<?= $base ?>","telephone":"<?= e($phone) ?>","areaServed":"Your Region","image":"<?= $base . '/assets/img/brand/logo.png' ?>"}
</script>
</head>
<body data-page="<?= e($PAGE ?? '') ?>">
<?php if ($gtm): ?><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($gtm) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><?php endif; ?>

<header class="nav" id="nav">
  <div class="nav-inner">
    <a class="nav-logo" href="<?= url('') ?>" aria-label="<?= e($brand) ?> home">
      <img src="/assets/img/brand/logo.svg" alt="" class="nav-badge">
      <span class="nav-wordmark"><?= e(g('footer.companyName') ?: settings('siteName','VentStudio')) ?></span>
    </a>
    <nav class="nav-links" aria-label="Main">
      <a href="<?= url('menu') ?>"<?= ($PAGE==='menu'?' class="on"':'') ?>><?= e(tg('nav.menu')) ?></a>
      <a href="<?= url('about') ?>"<?= ($PAGE==='about'?' class="on"':'') ?>><?= e(tg('nav.about')) ?></a>
      <a href="<?= url('contact') ?>"<?= ($PAGE==='contact'?' class="on"':'') ?>><?= e(tg('nav.contact')) ?></a>
    </nav>
    <div class="nav-right">
      <a class="btn btn-primary btn-sm" href="<?= url('menu') ?>"><?= e(tg('nav.orderCta')) ?></a>
      <button class="cart-btn" id="cartBtn" type="button" aria-label="Your order">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6L5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        <span class="cart-count" id="cartCount" hidden>0</span>
      </button>
    </div>
    <button class="nav-burger" id="navBurger" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</header>
