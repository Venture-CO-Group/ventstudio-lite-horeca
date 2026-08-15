<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$content = lt_content_load();
$sections = [
    'brands'   => $content['brands']['title']['en']   ?? 'Global brands',
    'partners' => $content['partners']['title']['en'] ?? 'Rights holders',
];
lt_admin_head('Logos');
lt_admin_sidebar('logos');
lt_admin_top('Vent Studio', 'Partner logos', '<a class="btn-studio primary" href="logo-edit.php">+ Add logo</a>');
?>
<div class="admin-body">
  <div class="st-hint">Click any logo to edit it — set its link, move it between sections, or replace the image from the media library.</div>
  <?php foreach ($sections as $key => $label): $items = $content[$key]['items'] ?? []; ?>
    <div class="section-card"><h2><?= htmlspecialchars($label) ?> <span style="color:#999;font-weight:400;font-size:14px"><?= count($items) ?></span></h2>
      <div class="card-body">
        <div class="thumb-grid">
          <?php foreach ($items as $i => $it): ?>
            <a class="thumb-card" href="logo-edit.php?section=<?= $key ?>&i=<?= $i ?>">
              <span class="thumb-sec"><?= $key === 'brands' ? 'Brand' : 'Rights' ?></span>
              <div class="thumb-img"><img src="/assets/img/<?= htmlspecialchars($it['logo'] ?? '') ?>" alt="<?= htmlspecialchars($it['name'] ?? '') ?>" loading="lazy"></div>
              <div class="thumb-cap"><?= htmlspecialchars($it['name'] ?? '(unnamed)') ?></div>
            </a>
          <?php endforeach; ?>
          <a class="thumb-add" href="logo-edit.php?section=<?= $key ?>">+ Add to <?= htmlspecialchars($label) ?></a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php lt_admin_foot();
