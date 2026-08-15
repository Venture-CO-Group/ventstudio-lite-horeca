<?php
$META_TITLE = tg('nav.blog'); $META_DESC = tg('seo.description');
require __DIR__ . '/../inc/head.php';
$posts = lt_posts_published();
usort($posts, fn($a,$b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
$featured = array_values(array_filter($posts, fn($p) => !empty($p['featured'])));
function lt_trunc($s, $n) {
    $s = (string)$s;
    if (preg_match('/^.{0,' . (int)$n . '}/us', $s, $m)) { $cut = $m[0]; return strlen($cut) < strlen($s) ? rtrim($cut) . '…' : $cut; }
    return strlen($s) > $n ? substr($s, 0, $n) . '…' : $s;
}
function post_cover($p) {
    $c = trim($p['cover'] ?? '');
    if ($c === '') return '/assets/img/gallery/fans-1.webp';
    return (str_starts_with($c, 'http') || str_starts_with($c, '/')) ? $c : '/assets/img/' . $c;
}
?>
<main class="page blog-page">
  <section class="page-hero"><div class="wrap">
    <h1 class="display"><?= e(L('News & stories','Hírek és sztorik','Noticias e historias')) ?></h1>
  </div></section>

  <?php if ($featured): $loop = count($featured) > 2 ? array_merge($featured, $featured) : $featured; ?>
  <section class="feat-carousel" aria-label="Featured">
    <div class="feat-track <?= count($featured) > 2 ? 'anim' : '' ?>">
      <?php foreach ($loop as $p): ?>
        <a class="feat-card" href="<?= url('blog/' . $p['slug']) ?>" style="background-image:url('<?= e(post_cover($p)) ?>')">
          <span class="feat-badge"><?= e(L('Featured','Kiemelt','Destacado')) ?></span>
          <span class="feat-body">
            <span class="feat-title display"><?= e(t($p['title'] ?? '')) ?></span>
            <span class="feat-meta"><?= e(date('Y. m. d.', strtotime($p['date'] ?? 'now'))) ?><?= !empty($p['category']) ? ' · ' . e($p['category']) : '' ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="section" style="padding-top:40px"><div class="wrap">
    <?php if (!$posts): ?>
      <p class="muted"><?= e(L('No articles yet — check back soon.','Még nincsenek cikkek — nézz vissza hamarosan.','Aún no hay artículos.')) ?></p>
    <?php else: $PER = 9; ?>
    <h2 class="display section-title"><?= e(L('More posts from VentStudio','További bejegyzések a VentStudiotól','Más publicaciones de VentStudio')) ?></h2>
    <div class="blog-grid" id="blogGrid">
      <?php foreach ($posts as $i => $p): ?>
        <a class="post-card<?= $i >= $PER ? ' is-hidden' : '' ?>" data-idx="<?= $i ?>" href="<?= url('blog/' . $p['slug']) ?>">
          <div class="post-cover"><img src="<?= e(post_cover($p)) ?>" alt="" loading="lazy"></div>
          <div class="post-card-body">
            <div class="post-meta"><?= e(date('Y. m. d.', strtotime($p['date'] ?? 'now'))) ?><?= !empty($p['category']) ? ' · ' . e($p['category']) : '' ?><?= !empty($p['readMin']) ? ' · ' . (int)$p['readMin'] . ' min' : '' ?></div>
            <h3><?= e(t($p['title'] ?? '')) ?></h3>
            <p><?= e(lt_trunc(t($p['excerpt'] ?? ''), 150)) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (count($posts) > $PER): ?>
    <div class="load-more-wrap">
      <button class="btn btn-navy" id="loadMore" type="button" data-step="<?= $PER ?>"><?= e(L('Load more','Több betöltése','Cargar más')) ?></button>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div></section>
</main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
