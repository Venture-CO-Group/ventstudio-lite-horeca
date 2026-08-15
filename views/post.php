<?php
$slug = $GLOBALS['POST_SLUG'] ?? '';
$p = lt_post_by_slug($slug);
if (!$p || empty($p['published'])) { http_response_code(404); $GLOBALS['PAGE']='404'; require __DIR__.'/404.php'; return; }
$META_TITLE = t($p['title'] ?? ''); $META_DESC = t($p['excerpt'] ?? '');
$META_IMG = $p['cover'] ?? null;
require __DIR__ . '/../inc/head.php';
?>
<main class="page post-page">
  <article class="wrap post">
    <a class="back-link" href="<?= url('blog') ?>">&larr; <?= e(tg('nav.blog')) ?></a>
    <div class="post-meta"><time><?= e($p['date'] ?? '') ?></time><?php if (!empty($p['readMin'])): ?> · <?= (int)$p['readMin'] ?> <?= e(L('min read','perc','min')) ?><?php endif; ?></div>
    <h1 class="display"><?= e(t($p['title'] ?? '')) ?></h1>
    <?php if (!empty($p['cover'])): ?><div class="post-hero-img"><img src="<?= e($p['cover']) ?>" alt=""></div><?php endif; ?>
    <div class="post-body"><?= t($p['body'] ?? '') /* trusted HTML from admin */ ?></div>
  </article>
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"Article","headline":<?= json_encode(t($p['title'] ?? '')) ?>,"datePublished":<?= json_encode($p['date'] ?? '') ?>,"author":{"@type":"Organization","name":"VentStudio"}}
  </script>

  <section class="section cta-band"><div class="wrap">
    <h2 class="display on-dark"><?= e(tg('about.ctaTitle') ?: L('Ready to put your fans on screen?','Készen állsz, hogy a szurkolóid a képernyőre kerüljenek?','¿Listo para poner a tus fans en pantalla?')) ?></h2>
    <button class="btn btn-magenta btn-lg js-demo" type="button"><?= e(tg('nav.demoCta')) ?></button>
  </div></section>
</main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
