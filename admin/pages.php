<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_pages.php';
lt_require_login();
lt_admin_head('Pages');
lt_admin_sidebar('pages');
lt_admin_top('Vent Studio', 'Pages', '<a class="btn-studio" href="/" target="_blank">View website &nearr;</a>');
?>
<div class="admin-body">
  <div class="st-hint">Pick a page to edit. Switch language with the tabs inside the editor — changes go live the moment you save.</div>
  <div class="pages-grid">
    <?php foreach (lt_admin_pages() as $id => $p): ?>
      <a class="page-card" href="edit-page.php?id=<?= $id ?>">
        <h3><?= htmlspecialchars($p['label']) ?></h3>
        <p><?= htmlspecialchars($p['desc']) ?></p>
        <span class="page-card-cta">Edit page →</span>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php lt_admin_foot();
