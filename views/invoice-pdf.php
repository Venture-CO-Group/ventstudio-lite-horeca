<?php
/* PDF invoice / credit note download — verified by per-order token. */
require_once __DIR__ . '/../inc/orders.php';
require_once __DIR__ . '/../inc/invoice_pdf.php';
$id   = (int)($_GET['order'] ?? 0);
$t    = (string)($_GET['t'] ?? '');
$type = ($_GET['type'] ?? 'invoice') === 'credit' ? 'credit' : 'invoice';
$o    = $id ? lt_order_get($id) : null;
if (!$o || $t === '' || !hash_equals((string)($o['token'] ?? ''), $t)) {
    http_response_code(404); header('Content-Type: text/plain'); echo 'Not found'; return;
}
if ($type === 'credit' && ($o['status'] ?? '') !== 'cancelled') $type = 'invoice';
lt_invoice_pdf($o, $type, 'D');
