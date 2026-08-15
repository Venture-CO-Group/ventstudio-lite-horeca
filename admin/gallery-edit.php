<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();
$COLLS = ['gallery'=>'Home gallery grid', 'carousel1'=>'Photo carousel — one', 'carousel2'=>'Photo carousel — two'];

function &gal_ref2(&$content, $coll) {
    if ($coll === 'carousel1') { if (!isset($content['carousels']['one']['items'])) $content['carousels']['one']['items'] = []; return $content['carousels']['one']['items']; }
    if ($coll === 'carousel2') { if (!isset($content['carousels']['two']['items'])) $content['carousels']['two']['items'] = []; return $content['carousels']['two']['items']; }
    if (!isset($content['gallery']['items'])) $content['gallery']['items'] = [];
    return $content['gallery']['items'];
}

$coll = isset($COLLS[$_GET['coll'] ?? '']) ? $_GET['coll'] : 'gallery';
$arr = &gal_ref2($content, $coll);
$idx = isset($_GET['i']) ? (int)$_GET['i'] : -1;
$isNew = !isset($arr[$idx]);
$it = $isNew ? ['image'=>'','alt'=>['en'=>'','hu'=>'','es'=>'']] : $arr[$idx];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $newColl = isset($COLLS[$_POST['coll'] ?? '']) ? $_POST['coll'] : $coll;
    $image = preg_replace('#^/?assets/img/#', '', trim($_POST['image'] ?? ''));
    $rec = ['image'=>$image, 'alt'=>['en'=>trim($_POST['alt_en']??''),'hu'=>trim($_POST['alt_hu']??''),'es'=>trim($_POST['alt_es']??'')]];

    if (!$isNew && $newColl === $coll) {
        $arr[$idx] = $rec; $arr = array_values($arr);       // edit in place, keep position
    } else {
        if (!$isNew) { array_splice($arr, $idx, 1); $arr = array_values($arr); }
        $dest = &gal_ref2($content, $newColl);
        $dest[] = $rec; $dest = array_values($dest);
    }
    lt_content_save($content);
    lt_audit('gallery.save', $newColl);
    header('Location: gallery.php'); exit;
}

$imgRel = $it['image'] ?? '';
$imgSrc = $imgRel ? (preg_match('#^https?:|^/#', $imgRel) ? $imgRel : '/assets/img/' . $imgRel) : '';
lt_admin_head($isNew ? 'Add image' : 'Edit image');
lt_admin_sidebar('gallery');
lt_admin_top('Vent Studio', $isNew ? 'Add image' : 'Edit image', '<a class="btn-studio" href="gallery.php">← Back to gallery</a>');
$LANGS = ['en'=>'English','hu'=>'Magyar','es'=>'Español'];
?>
<div class="admin-body">
  <div class="section-card"><div class="card-body" style="max-width:560px">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <div class="field"><div class="field-label">Collection</div>
        <select name="coll" class="txt">
          <?php foreach ($COLLS as $k=>$lb): ?><option value="<?= $k ?>" <?= $k===$coll?'selected':'' ?>><?= htmlspecialchars($lb) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><div class="field-label">Image</div>
        <div class="cover-prev<?= $imgSrc?'':' empty' ?>" id="galPrev" style="max-width:340px"><?php if ($imgSrc): ?><img src="<?= htmlspecialchars($imgSrc) ?>" alt=""><?php endif; ?></div>
        <input name="image" id="galInput" class="txt" style="margin-top:10px" value="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>" placeholder="/assets/img/gallery/…">
        <div class="form-row" style="margin-top:8px">
          <button type="button" class="btn-studio btn-mini" data-mediapick="#galInput" data-preview="#galPrev">Browse…</button>
          <button type="button" class="btn-studio btn-mini" data-mediaupload="#galInput" data-preview="#galPrev">Upload</button>
        </div>
      </div>

      <div class="lang-bar" id="gLangBar" style="margin-left:0">
        <span class="lang-lab">Alt text language:</span>
        <?php $i=0; foreach ($LANGS as $l=>$nm): ?><button class="lang-pill<?= $i===0?' on':'' ?>" type="button" data-lang="<?= $l ?>"><?= $nm ?></button><?php $i++; endforeach; ?>
      </div>
      <?php $i=0; foreach ($LANGS as $l=>$nm): ?>
      <div class="lang-pane<?= $i===0?' on':'' ?>" data-lang="<?= $l ?>">
        <div class="field"><div class="field-label">Alt text (<?= $l ?>)</div>
          <input name="alt_<?= $l ?>" class="txt" value="<?= htmlspecialchars($it['alt'][$l] ?? '', ENT_QUOTES) ?>"></div>
      </div>
      <?php $i++; endforeach; ?>

      <button class="btn-studio primary" type="submit">Save image</button>
    </form>
  </div></div>
</div>
<?php require __DIR__ . '/_media_picker.php'; ?>
<script>
(function(){var bar=document.getElementById('gLangBar');bar.addEventListener('click',function(e){var b=e.target.closest('[data-lang]');if(!b)return;var l=b.getAttribute('data-lang');
  bar.querySelectorAll('.lang-pill').forEach(function(x){x.classList.toggle('on',x===b);});
  document.querySelectorAll('.lang-pane').forEach(function(p){p.classList.toggle('on',p.getAttribute('data-lang')===l);});});})();
</script>
<?php lt_admin_foot();
