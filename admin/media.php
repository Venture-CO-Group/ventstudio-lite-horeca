<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$root = dirname(__DIR__) . '/assets/img';
$uploadDir = $root . '/uploads';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    if (!empty($_FILES['file']['name'])) {
        $f = $_FILES['file'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif','svg','avif'];
        if ($f['error'] !== UPLOAD_ERR_OK) $err = 'Upload failed (error ' . $f['error'] . ').';
        elseif (!in_array($ext, $allowed, true)) $err = 'Only image files are allowed (' . implode(', ', $allowed) . ').';
        elseif ($f['size'] > 8 * 1024 * 1024) $err = 'Max file size is 8 MB.';
        else {
            $name = preg_replace('/[^a-z0-9._-]/', '-', strtolower(basename($f['name'])));
            $name = trim($name, '-.') ?: ('img-' . time() . '.' . $ext);
            if (is_file("$uploadDir/$name")) $name = pathinfo($name, PATHINFO_FILENAME) . '-' . time() . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], "$uploadDir/$name")) { $msg = "Uploaded to your Uploads folder: $name"; lt_audit('media.upload', $name); }
            else $err = 'Could not move the uploaded file — check that assets/img/uploads is writable.';
        }
    } elseif (!empty($_POST['del'])) {
        $del = basename($_POST['del']); // uploads only
        if (is_file("$uploadDir/$del") && @unlink("$uploadDir/$del")) { $msg = "Image deleted: $del"; lt_audit('media.delete', $del); }
        else $err = 'Delete failed.';
    }
}

$folders = ['uploads' => 'Uploads', 'gallery' => 'Gallery photos', 'logos' => 'Partner logos',
            'team' => 'Team photos', 'photos' => 'Product photos', 'brand' => 'Brand assets'];
$lib = [];
foreach ($folders as $dir => $label) {
    $p = "$root/$dir"; if (!is_dir($p)) continue;
    $files = array_values(array_filter(scandir($p), fn($x) => preg_match('/\.(jpe?g|png|webp|gif|svg|avif)$/i', $x)));
    sort($files);
    $lib[$dir] = ['label' => $label, 'files' => $files];
}
lt_admin_head('Media');
lt_admin_sidebar('media');
lt_admin_top('Vent Studio', 'Media library', '');
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <?php if ($err): ?><div class="notice" style="border-left-color:#b3261e"><strong><?= htmlspecialchars($err) ?></strong></div><?php endif; ?>

  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0">Upload a new image</h2>
    <form method="post" enctype="multipart/form-data" class="form-row">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.avif" required>
      <button class="btn-studio primary" type="submit">Upload</button>
      <span style="color:#888;font-size:13px">New images land in your <strong>Uploads</strong> folder — pick them anywhere with the <em>Browse…</em> button.</span>
    </form>
  </div></div>

  <?php foreach ($lib as $dir => $set): $files = $set['files']; ?>
  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0"><?= htmlspecialchars($set['label']) ?> <span style="color:#999;font-weight:400;font-size:14px"><?= count($files) ?> <?= count($files) === 1 ? 'image' : 'images' ?></span></h2>
    <?php if (!$files): ?><p style="color:#888">No images here yet.</p><?php else: ?>
    <div class="media-grid">
      <?php foreach ($files as $f): $rel = "$dir/$f"; ?>
      <div class="media-item">
        <button type="button" class="media-thumb media-seo" data-path="<?= htmlspecialchars($rel, ENT_QUOTES) ?>" title="Edit SEO details"><img src="/assets/img/<?= htmlspecialchars($rel) ?>" loading="lazy" alt=""><span class="media-seo-badge">SEO</span></button>
        <div class="media-meta">
          <input class="media-path" value="<?= htmlspecialchars($rel) ?>" readonly onclick="this.select();document.execCommand('copy');this.nextElementSibling.textContent='copied!'">
          <small class="copy-note">Click the image to edit SEO · click path to copy</small>
          <?php if ($dir === 'uploads'): ?>
          <form method="post" onsubmit="return confirm('Delete <?= htmlspecialchars($f) ?>?')">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="del" value="<?= htmlspecialchars($f, ENT_QUOTES) ?>">
            <button class="btn-studio btn-mini btn-danger" type="submit">Delete</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div></div>
  <?php endforeach; ?>
</div>

<!-- SEO metadata popup -->
<div class="mp-overlay" id="seoOverlay">
  <div class="mp-card" style="width:min(560px,94vw)">
    <div class="mp-head"><strong>Image SEO details</strong><button class="mp-close" type="button" id="seoClose">&times;</button></div>
    <div class="mp-body">
      <div class="seo-preview"><img id="seoImg" src="" alt=""><code id="seoPath"></code></div>
      <form id="seoForm">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="path" id="seoPathInput">
        <div class="field"><div class="field-label">Alt text</div><input class="txt" name="alt" id="seoAlt" placeholder="Describes the image for screen readers &amp; SEO"></div>
        <div class="field"><div class="field-label">Title</div><input class="txt" name="title" id="seoTitle"></div>
        <div class="field"><div class="field-label">Description</div><textarea name="description" id="seoDesc" style="min-height:70px"></textarea></div>
        <div class="field"><div class="field-label">Caption</div><input class="txt" name="caption" id="seoCaption"></div>
        <button class="btn-studio primary" type="submit" id="seoSave">Save details</button>
        <span id="seoMsg" style="margin-left:10px;font-weight:600;color:var(--success)"></span>
      </form>
    </div>
  </div>
</div>
<script>
(function(){
  var ov=document.getElementById('seoOverlay');
  function open(path){
    document.getElementById('seoImg').src='/assets/img/'+path;
    document.getElementById('seoPath').textContent=path;
    document.getElementById('seoPathInput').value=path;
    document.getElementById('seoMsg').textContent='';
    ['alt','title','desc','caption'].forEach(function(id){var e=document.getElementById('seo'+id.charAt(0).toUpperCase()+id.slice(1));if(e)e.value='';});
    ov.classList.add('open');
    fetch('media-meta.php?path='+encodeURIComponent(path)).then(function(r){return r.json();}).then(function(j){
      var m=j.meta||{}; document.getElementById('seoAlt').value=m.alt||''; document.getElementById('seoTitle').value=m.title||'';
      document.getElementById('seoDesc').value=m.description||''; document.getElementById('seoCaption').value=m.caption||'';
    });
  }
  function close(){ ov.classList.remove('open'); }
  document.querySelectorAll('.media-seo').forEach(function(b){ b.addEventListener('click', function(){ open(b.getAttribute('data-path')); }); });
  document.getElementById('seoClose').addEventListener('click', close);
  ov.addEventListener('click', function(e){ if(e.target===ov) close(); });
  document.getElementById('seoForm').addEventListener('submit', function(e){
    e.preventDefault();
    var fd=new FormData(this);
    fetch('media-meta.php',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
      document.getElementById('seoMsg').textContent = j.ok ? 'Saved ✓' : (j.error||'Failed');
    }).catch(function(){ document.getElementById('seoMsg').textContent='Failed'; });
  });
})();
</script>
<?php lt_admin_foot();
