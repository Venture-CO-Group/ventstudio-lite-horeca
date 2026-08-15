<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();

/* collection key -> path in the content tree */
function &gal_ref(&$content, $coll) {
    if ($coll === 'carousel1') { if (!isset($content['carousels']['one']['items'])) $content['carousels']['one']['items'] = []; return $content['carousels']['one']['items']; }
    if ($coll === 'carousel2') { if (!isset($content['carousels']['two']['items'])) $content['carousels']['two']['items'] = []; return $content['carousels']['two']['items']; }
    if (!isset($content['gallery']['items'])) $content['gallery']['items'] = [];
    return $content['gallery']['items'];
}
$COLLS = ['gallery'=>'Home gallery grid', 'carousel1'=>'Photo carousel — one', 'carousel2'=>'Photo carousel — two'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $coll = isset($COLLS[$_POST['coll'] ?? '']) ? $_POST['coll'] : 'gallery';
    $i = (int)($_POST['i'] ?? -1);
    $arr = &gal_ref($content, $coll);
    $act = $_POST['act'] ?? '';
    if ($act === 'delete' && isset($arr[$i])) { array_splice($arr, $i, 1); $msg = 'Image removed.'; }
    elseif ($act === 'up' && $i > 0) { $t=$arr[$i]; $arr[$i]=$arr[$i-1]; $arr[$i-1]=$t; }
    elseif ($act === 'down' && isset($arr[$i+1])) { $t=$arr[$i]; $arr[$i]=$arr[$i+1]; $arr[$i+1]=$t; }
    $arr = array_values($arr);
    lt_content_save($content);
    lt_audit('gallery.' . $act, $coll);
    unset($arr);
}

lt_admin_head('Gallery');
lt_admin_sidebar('gallery');
lt_admin_top('Vent Studio', 'Gallery & carousels', '');
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <div class="st-hint">Drag order with ↑ ↓, remove with ✕, or click a photo to change the image and its alt text. Upload happens straight into the media library.</div>

  <?php foreach ($COLLS as $coll => $label): $items = ($coll==='carousel1') ? ($content['carousels']['one']['items'] ?? []) : (($coll==='carousel2') ? ($content['carousels']['two']['items'] ?? []) : ($content['gallery']['items'] ?? [])); ?>
  <div class="section-card"><h2><?= htmlspecialchars($label) ?> <span style="color:#999;font-weight:400;font-size:14px"><?= count($items) ?></span></h2>
    <div class="card-body">
      <div class="thumb-grid">
        <?php foreach ($items as $i => $it): ?>
          <div class="thumb-card cover">
            <a href="gallery-edit.php?coll=<?= $coll ?>&i=<?= $i ?>" style="display:block">
              <div class="thumb-img"><img src="/assets/img/<?= htmlspecialchars($it['image'] ?? '') ?>" alt="" loading="lazy"></div>
              <div class="thumb-cap"><?= htmlspecialchars(mb_strimwidth((string)($it['alt']['en'] ?? ($it['image'] ?? '')), 0, 26, '…')) ?></div>
            </a>
            <div class="thumb-actions">
              <form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="coll" value="<?= $coll ?>"><input type="hidden" name="i" value="<?= $i ?>"><input type="hidden" name="act" value="up"><button type="submit" title="Move up">↑</button></form>
              <form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="coll" value="<?= $coll ?>"><input type="hidden" name="i" value="<?= $i ?>"><input type="hidden" name="act" value="down"><button type="submit" title="Move down">↓</button></form>
              <form method="post" onsubmit="return confirm('Remove this image?')"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="coll" value="<?= $coll ?>"><input type="hidden" name="i" value="<?= $i ?>"><input type="hidden" name="act" value="delete"><button type="submit" class="danger" title="Remove">✕</button></form>
            </div>
          </div>
        <?php endforeach; ?>
        <a class="thumb-add" href="gallery-edit.php?coll=<?= $coll ?>">+ Add image</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php lt_admin_foot();
