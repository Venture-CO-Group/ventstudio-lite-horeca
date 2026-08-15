<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$cleanMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'clean-posts') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $all = lt_posts_load_all(); $n = 0;
    foreach ($all as $p) {
        $before = json_encode($p);
        $clean = lt_post_clean($p);
        if (json_encode($clean) !== $before) { lt_post_save($clean); $n++; }
    }
    lt_audit('posts.clean', "cleaned=$n");
    $cleanMsg = "Cleaned $n post" . ($n === 1 ? '' : 's') . " — WordPress shortcodes and raw blocks removed.";
}

if (isset($_GET['dl'])) {
    $map = ['content' => dirname(__DIR__) . '/content.json', 'posts' => dirname(__DIR__) . '/data/posts.json'];
    $f = $map[$_GET['dl']] ?? '';
    if ($f && is_file($f)) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="ventstudio-' . $_GET['dl'] . '-backup-' . date('Ymd-His') . '.json"');
        readfile($f); exit;
    }
    http_response_code(404); exit('Not found');
}

$root = dirname(__DIR__);
$posts = lt_posts_load_all();
$dbMode = lt_db() ? 'MySQL (' . LT_DB_HOST . ')' : 'Flat files (content.json + data/)';
$checks = [
    'content.json writable'   => is_writable("$root/content.json"),
    'data/ writable'           => is_writable("$root/data"),
    'assets/img/uploads writable' => is_dir("$root/assets/img/uploads") ? is_writable("$root/assets/img/uploads") : @mkdir("$root/assets/img/uploads", 0755, true),
    'cURL extension'           => function_exists('curl_init'),
    'OpenSSL (SMTP TLS)'       => extension_loaded('openssl'),
];
lt_admin_head('Maintenance');
lt_admin_sidebar('maintenance');
lt_admin_top('Vent Studio', 'Maintenance', '');
?>
<div class="admin-body">
  <?php if ($cleanMsg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($cleanMsg) ?></strong></div><?php endif; ?>

  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0">Backups</h2>
    <p>Download a snapshot before bigger edits. Restoring = uploading the file back over <code>content.json</code> / <code>data/posts.json</code> via FTP.</p>
    <div class="form-row">
      <a class="btn-studio primary" href="maintenance.php?dl=content">Download content backup</a>
      <a class="btn-studio" href="maintenance.php?dl=posts">Download blog backup</a>
    </div>
  </div></div>

  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0">Tools</h2>
    <div class="form-row">
      <a class="btn-studio" href="migrate.php">MySQL migration</a>
      <a class="btn-studio" href="log.php">Change log</a>
    </div>
  </div></div>

  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0">System</h2>
    <table class="data-table">
      <tr><th>PHP version</th><td><?= htmlspecialchars(PHP_VERSION) ?></td></tr>
      <tr><th>Storage mode</th><td><?= htmlspecialchars($dbMode) ?></td></tr>
      <tr><th>Blog posts</th><td><?= count($posts) ?> (<?= count(array_filter($posts, fn($p)=>!empty($p['published']))) ?> published)</td></tr>
      <?php foreach ($checks as $label => $ok): ?>
      <tr><th><?= htmlspecialchars($label) ?></th><td><span class="badge <?= $ok ? 'on' : 'off' ?>"><?= $ok ? 'OK' : 'Problem' ?></span></td></tr>
      <?php endforeach; ?>
    </table>
  </div></div>
</div>
<?php lt_admin_foot();
