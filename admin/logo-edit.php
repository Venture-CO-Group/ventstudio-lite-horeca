<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();
foreach (['brands','partners'] as $s) if (!isset($content[$s]['items']) || !is_array($content[$s]['items'])) $content[$s]['items'] = [];

$section = in_array($_GET['section'] ?? '', ['brands','partners'], true) ? $_GET['section'] : 'brands';
$idx = isset($_GET['i']) ? (int)$_GET['i'] : -1;
$isNew = !isset($content[$section]['items'][$idx]);
$it = $isNew ? ['name'=>'','logo'=>'','url'=>''] : $content[$section]['items'][$idx];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }

    if (($_POST['act'] ?? '') === 'delete') {
        if (!$isNew) { array_splice($content[$section]['items'], $idx, 1); $content[$section]['items'] = array_values($content[$section]['items']); lt_content_save($content); lt_audit('logo.delete', $section); }
        header('Location: logos.php'); exit;
    }

    $newSection = in_array($_POST['section'] ?? '', ['brands','partners'], true) ? $_POST['section'] : $section;
    $logo = trim($_POST['logo'] ?? '');
    // store logo path relative to /assets/img/ (strip the prefix if a full path was chosen)
    $logo = preg_replace('#^/?assets/img/#', '', $logo);
    $rec = ['name'=>trim($_POST['name'] ?? ''), 'logo'=>$logo, 'url'=>trim($_POST['url'] ?? '')];

    if (!$isNew && $newSection === $section) {
        $content[$section]['items'][$idx] = $rec;                 // edit in place, keep position
        $content[$section]['items'] = array_values($content[$section]['items']);
    } else {
        if (!$isNew) { array_splice($content[$section]['items'], $idx, 1); $content[$section]['items'] = array_values($content[$section]['items']); }
        $content[$newSection]['items'][] = $rec;
        $content[$newSection]['items'] = array_values($content[$newSection]['items']);
    }
    lt_content_save($content);
    lt_audit('logo.save', $rec['name']);
    header('Location: logos.php'); exit;
}

$logoPath = $it['logo'] ?? '';
$logoSrc = $logoPath ? (preg_match('#^https?:|^/#', $logoPath) ? $logoPath : '/assets/img/' . $logoPath) : '';
lt_admin_head($isNew ? 'Add logo' : 'Edit logo');
lt_admin_sidebar('logos');
lt_admin_top('Vent Studio', $isNew ? 'Add logo' : 'Edit logo', '<a class="btn-studio" href="logos.php">← All logos</a>');
?>
<div class="admin-body">
  <div class="section-card"><div class="card-body" style="max-width:560px">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <div class="field"><div class="field-label">Section</div>
        <select name="section" class="txt">
          <option value="brands"   <?= $section==='brands'?'selected':'' ?>>Global brands trusting us</option>
          <option value="partners" <?= $section==='partners'?'selected':'' ?>>Rights holders voting for us</option>
        </select>
      </div>
      <div class="field"><div class="field-label">Name</div>
        <input name="name" class="txt" value="<?= htmlspecialchars($it['name'] ?? '', ENT_QUOTES) ?>"></div>
      <div class="field"><div class="field-label">Link (URL, optional)</div>
        <input name="url" class="txt" placeholder="https://…" value="<?= htmlspecialchars($it['url'] ?? '', ENT_QUOTES) ?>"></div>
      <div class="field"><div class="field-label">Logo image</div>
        <div class="img-row">
          <div class="img-thumb<?= $logoSrc?'':' empty' ?>" id="logoPrev"><?php if ($logoSrc): ?><img src="<?= htmlspecialchars($logoSrc) ?>" alt=""><?php endif; ?></div>
          <input name="logo" id="logoInput" class="txt" value="<?= htmlspecialchars($logoSrc, ENT_QUOTES) ?>" placeholder="/assets/img/logos/…">
          <button type="button" class="btn-studio btn-mini" data-mediapick="#logoInput" data-preview="#logoPrev">Browse…</button>
          <button type="button" class="btn-studio btn-mini" data-mediaupload="#logoInput" data-preview="#logoPrev">Upload</button>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:6px">
        <button class="btn-studio primary" type="submit">Save logo</button>
        <?php if (!$isNew): ?>
        <button class="btn-studio btn-danger" type="submit" name="act" value="delete" onclick="return confirm('Remove this logo?')">Delete</button>
        <?php endif; ?>
      </div>
    </form>
  </div></div>
</div>
<?php require __DIR__ . '/_media_picker.php'; ?>
<?php lt_admin_foot();
