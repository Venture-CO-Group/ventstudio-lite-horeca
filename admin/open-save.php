<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/inc/store.php';
lt_require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !lt_check_csrf($_POST['csrf'] ?? '')) { header('Location: hours.php'); exit; }

$C = lt_content_load();
if (!isset($C['settings']) || !is_array($C['settings'])) $C['settings'] = [];
$C['settings']['orderingOpen'] = !empty($_POST['orderingOpen']);
[$ok, $err] = lt_content_save($C);
lt_audit('ordering-open', $ok ? ($C['settings']['orderingOpen'] ? 'OPEN' : 'CLOSED') : ('failed: ' . $err));
header('Location: hours.php?opensaved=' . ($ok ? ($C['settings']['orderingOpen'] ? 'open' : 'closed') : 'err'));
exit;
