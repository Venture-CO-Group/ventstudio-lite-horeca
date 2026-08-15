<?php
require_once __DIR__ . '/../inc/blocks.php';
$cp = $GLOBALS['CUSTOM_PAGE'] ?? [];
$META_TITLE = t($cp['title'] ?? '');
$META_DESC  = t($cp['metaDesc'] ?? '') ?: tg('seo.description');
require __DIR__ . '/../inc/head.php';
?>
<main class="page custom-page">
  <div class="wrap builder-canvas-front">
    <?= lt_render_blocks($cp['blocks'] ?? []) ?>
  </div>
</main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
