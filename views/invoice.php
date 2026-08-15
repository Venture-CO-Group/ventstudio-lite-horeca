<?php
/* Printable invoice — verified by per-order token. Renders a standalone page. */
require_once __DIR__ . '/../inc/orders.php';
require_once __DIR__ . '/../inc/invoice.php';
$id = (int)($_GET['order'] ?? 0);
$t  = (string)($_GET['t'] ?? '');
$o  = $id ? lt_order_get($id) : null;
if (!$o || $t === '' || !hash_equals((string)($o['token'] ?? ''), $t)) {
    http_response_code(404);
    echo 'Invoice not found.';
    return;
}
echo lt_invoice_html($o);
