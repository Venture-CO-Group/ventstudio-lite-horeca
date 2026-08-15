<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/inc/store.php';
lt_require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !lt_check_csrf($_POST['csrf'] ?? '')) { header('Location: hours.php'); exit; }

$days = ['mon','tue','wed','thu','fri','sat','sun'];
$open = (array)($_POST['open'] ?? []);
$from = (array)($_POST['from'] ?? []);
$to   = (array)($_POST['to'] ?? []);
$t = function ($v, $d) { $v = trim((string)$v); return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : $d; };

$hours = [];
foreach ($days as $k) {
    $hours[$k] = [
        'open' => !empty($open[$k]),
        'from' => $t($from[$k] ?? '', '12:00'),
        'to'   => $t($to[$k] ?? '', '20:00'),
    ];
}
$openFrom = trim((string)($_POST['ordersOpenFrom'] ?? ''));
if ($openFrom !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $openFrom)) $openFrom = '';
$slot = max(10, min(60, (int)($_POST['slotMinutes'] ?? 30)));

$C = lt_content_load();
$C['settings']['hours'] = $hours;
$C['settings']['ordersOpenFrom'] = $openFrom;
$C['settings']['slotMinutes'] = $slot;
$C['settings']['orderingOpen'] = !empty($_POST['orderingOpen']);
[$ok] = lt_content_save($C);
lt_audit('hours-save', $ok ? 'updated' : 'failed');
header('Location: hours.php?' . ($ok ? 'saved=1' : 'err=1'));
exit;
