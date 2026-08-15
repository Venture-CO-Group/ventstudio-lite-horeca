<?php
/* Bulk-download all invoices (+ credit notes) for a date range as a ZIP. Admin only. */
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/inc/orders.php';
require_once dirname(__DIR__) . '/inc/invoice_pdf.php';
lt_require_login();

$from = preg_replace('/[^0-9\-]/', '', (string)($_GET['from'] ?? ''));
$to   = preg_replace('/[^0-9\-]/', '', (string)($_GET['to'] ?? ''));

$orders = lt_orders_all();
$pick = [];
foreach ($orders as $o) {
    $d = substr($o['created'] ?? '', 0, 10);
    if ($from && $d < $from) continue;
    if ($to && $d > $to) continue;
    $pick[] = $o;
}
if (!$pick) { http_response_code(404); header('Content-Type: text/plain'); echo 'No invoices for this period.'; exit; }
if (!class_exists('ZipArchive')) { http_response_code(500); header('Content-Type: text/plain'); echo 'ZIP support (php-zip) is not available on this server.'; exit; }

$tmp = tempnam(sys_get_temp_dir(), 'ltinv') . '.zip';
$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { http_response_code(500); echo 'Could not create archive.'; exit; }
foreach ($pick as $o) {
    $num = $o['number'] ?? ('order-' . (int)$o['id']);
    $zip->addFromString('Invoice-' . $num . '.pdf', lt_invoice_pdf($o, 'invoice', 'S'));
    if (($o['status'] ?? '') === 'cancelled') {
        $zip->addFromString('CreditNote-' . $num . '.pdf', lt_invoice_pdf($o, 'credit', 'S'));
    }
}
$zip->close();

$label = ($from ?: 'all') . '_' . ($to ?: 'all');
lt_audit('invoices-zip', count($pick) . ' invoices (' . $label . ')');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="invoices_' . $label . '.zip"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
@unlink($tmp);
exit;
