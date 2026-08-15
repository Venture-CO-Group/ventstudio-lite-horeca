<?php
/* VentStudio — order storage. Uses MySQL (lt_orders) when LT_DB_ENABLED,
   otherwise a flat file (data/orders.json). Same API either way. */
require_once __DIR__ . '/store.php';

function lt_orders_file() { return lt_root() . '/data/orders.json'; }

/* ---------- flat-file helpers ---------- */
function lt_orders_load() {
    $f = lt_orders_file();
    if (!is_file($f)) return ['seq' => 0, 'orders' => []];
    $d = json_decode((string)file_get_contents($f), true);
    return is_array($d) ? $d + ['seq' => 0, 'orders' => []] : ['seq' => 0, 'orders' => []];
}
function lt_orders_save(array $all) {
    $f = lt_orders_file(); $tmp = $f . '.tmp';
    $json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return @rename($tmp, $f);
}

/* ---------- unified API ---------- */
function lt_orders_add(array $o) {
    $o['status']  = $o['status'] ?? 'received';
    $o['created'] = date('c');
    $db = lt_db();
    if ($db) {
        try {
            $db->exec("INSERT INTO lt_orders (number,status,fulfilment,payment,total,currency,data) VALUES ('',"
                . $db->quote($o['status']) . "," . $db->quote($o['fulfilment'] ?? 'delivery') . ","
                . $db->quote($o['payment'] ?? 'stripe') . "," . (int)($o['total'] ?? 0) . ","
                . $db->quote($o['currency'] ?? 'gbp') . "," . $db->quote('{}') . ")");
            $id = (int)$db->lastInsertId();
            $o['id'] = $id; $o['number'] = 'LT-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
            $st = $db->prepare("UPDATE lt_orders SET number=:n, data=:d WHERE id=:id");
            $st->execute([':n' => $o['number'], ':d' => json_encode($o, JSON_UNESCAPED_UNICODE), ':id' => $id]);
            return $o;
        } catch (Exception $e) { /* fall through to file */ }
    }
    $all = lt_orders_load();
    $all['seq'] = (int)$all['seq'] + 1;
    $o['id'] = $all['seq'];
    $o['number'] = 'LT-' . str_pad((string)$all['seq'], 4, '0', STR_PAD_LEFT);
    $all['orders'][] = $o;
    lt_orders_save($all);
    return $o;
}
function lt_order_get($id) {
    $db = lt_db();
    if ($db) {
        try {
            $st = $db->prepare("SELECT data FROM lt_orders WHERE id=:id");
            $st->execute([':id' => (int)$id]); $row = $st->fetch();
            if ($row) { $d = json_decode($row['data'], true); if (is_array($d)) return $d; }
            return null;
        } catch (Exception $e) {}
    }
    foreach (lt_orders_load()['orders'] as $o) if ((int)$o['id'] === (int)$id) return $o;
    return null;
}
function lt_order_update($id, array $patch) {
    $db = lt_db();
    if ($db) {
        try {
            $o = lt_order_get($id); if (!$o) return null;
            $o = array_merge($o, $patch);
            $st = $db->prepare("UPDATE lt_orders SET status=:s, total=:t, data=:d WHERE id=:id");
            $st->execute([':s' => $o['status'] ?? 'received', ':t' => (int)($o['total'] ?? 0),
                          ':d' => json_encode($o, JSON_UNESCAPED_UNICODE), ':id' => (int)$id]);
            return $o;
        } catch (Exception $e) {}
    }
    $all = lt_orders_load(); $out = null;
    foreach ($all['orders'] as &$o) if ((int)$o['id'] === (int)$id) { $o = array_merge($o, $patch); $out = $o; }
    unset($o);
    if ($out) lt_orders_save($all);
    return $out;
}
function lt_orders_all() {
    $db = lt_db();
    if ($db) {
        try {
            $rows = $db->query("SELECT data FROM lt_orders ORDER BY id DESC")->fetchAll();
            $out = [];
            foreach ($rows as $r) { $d = json_decode($r['data'], true); if (is_array($d)) $out[] = $d; }
            return $out;
        } catch (Exception $e) {}
    }
    return array_reverse(lt_orders_load()['orders']);
}
