<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$file = dirname(__DIR__) . '/data/admin-log.jsonl';
$lines = is_file($file) ? array_slice(array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), 0, 300) : [];
lt_admin_head('Change log'); lt_admin_sidebar('log');
lt_admin_top('Config', 'Change log');
?>
<div class="admin-body">
  <table class="data-table"><thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Detail</th></tr></thead><tbody>
  <?php if (!$lines): ?><tr><td colspan="4">No activity yet.</td></tr><?php endif; ?>
  <?php foreach ($lines as $l): $r = json_decode($l, true); if (!$r) continue; ?>
    <tr><td><?= htmlspecialchars(substr($r['ts'] ?? '', 0, 19)) ?></td><td><?= htmlspecialchars($r['admin'] ?? '') ?></td>
    <td><?= htmlspecialchars($r['action'] ?? '') ?></td><td><?= htmlspecialchars($r['detail'] ?? '') ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</div>
<?php lt_admin_foot();
