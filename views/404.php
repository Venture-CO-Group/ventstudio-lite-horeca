<?php
$META_TITLE = '404';
require __DIR__ . '/../inc/head.php';
?>
<main class="page"><section class="page-hero"><div class="wrap">
  <h1 class="display">404</h1>
  <p class="page-lead"><?= e(L('Page not found.','Az oldal nem található.','Página no encontrada.')) ?></p>
  <a class="btn btn-magenta" href="<?= url('') ?>"><?= e(L('Back home','Vissza a főoldalra','Volver al inicio')) ?></a>
</div></section></main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
