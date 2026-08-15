<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_owner();
$csrf = lt_csrf(); $report = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && lt_check_csrf($_POST['csrf'] ?? '')) {
    $db = lt_db();
    if (!$db) $report[] = 'MySQL is not enabled. Set LT_DB_ENABLED=1 in .env and import schema.sql first.';
    else {
        try {
            // content (prefer content.json; fall back to content.default.json)
            $cf = dirname(__DIR__).'/content.json'; $df = dirname(__DIR__).'/content.default.json';
            $src = is_file($cf) ? $cf : $df;
            $c = json_decode((string)@file_get_contents($src), true);
            if (is_array($c)) { list($ok,$e)=lt_content_save($c); $report[] = $ok ? ('Content copied to lt_content (from '.basename($src).').') : ('Content: '.$e); }
            else { $report[] = 'No content.json / content.default.json found to import.'; }
            // posts
            $file = dirname(__DIR__).'/data/posts.json';
            $arr = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
            $n = 0; if (is_array($arr)) foreach ($arr as $p) { lt_post_save($p); $n++; }
            $report[] = "Posts copied to lt_posts: $n";
            // orders (from data/orders.json) — preserve ids & numbers
            $of = dirname(__DIR__).'/data/orders.json';
            $oarr = is_file($of) ? json_decode((string)file_get_contents($of), true) : null;
            $on = 0;
            if (is_array($oarr) && !empty($oarr['orders'])) {
                $ins = $db->prepare("INSERT INTO lt_orders (id,number,status,fulfilment,payment,total,currency,data)"
                    . " VALUES (:id,:num,:s,:f,:p,:t,:c,:d)"
                    . " ON DUPLICATE KEY UPDATE data=VALUES(data), status=VALUES(status), total=VALUES(total)");
                foreach ($oarr['orders'] as $o) {
                    if (empty($o['id'])) continue;
                    $ins->execute([
                        ':id'  => (int)$o['id'],
                        ':num' => $o['number'] ?? ('LT-'.str_pad((string)$o['id'],4,'0',STR_PAD_LEFT)),
                        ':s'   => $o['status'] ?? 'received',
                        ':f'   => $o['fulfilment'] ?? 'delivery',
                        ':p'   => $o['payment'] ?? 'stripe',
                        ':t'   => (int)($o['total'] ?? 0),
                        ':c'   => $o['currency'] ?? 'gbp',
                        ':d'   => json_encode($o, JSON_UNESCAPED_UNICODE),
                    ]);
                    $on++;
                }
                $maxId = max(array_map(fn($x)=>(int)($x['id'] ?? 0), $oarr['orders']));
                try { $db->exec("ALTER TABLE lt_orders AUTO_INCREMENT = ".($maxId+1)); } catch (Exception $e) {}
            }
            $report[] = "Orders copied to lt_orders: $on";
            lt_audit('migrate.run', "$n posts, $on orders");
        } catch (Exception $e) { $report[] = 'Error: '.$e->getMessage(); }
    }
}
lt_admin_head('Migrate'); lt_admin_sidebar('log');
lt_admin_top('Config', 'Migrate JSON → MySQL');
?>
<div class="admin-body">
  <div class="notice">This copies your site content (<strong>content.json</strong>, or <strong>content.default.json</strong> if that's missing) and <strong>data/posts.json</strong> into the database, overwriting the current DB content. Run this after uploading new content files to publish menu/settings changes. Safe to run repeatedly; the JSON files stay as a backup.</div>
  <?php foreach ($report as $r): ?><div class="notice"><?= htmlspecialchars($r) ?></div><?php endforeach; ?>
  <form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><button class="btn-studio primary" type="submit">Run migration</button></form>
</div>
<?php lt_admin_foot();
