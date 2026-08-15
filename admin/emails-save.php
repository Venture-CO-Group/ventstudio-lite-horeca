<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/inc/emails.php';
lt_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !lt_check_csrf($_POST['csrf'] ?? '')) { header('Location: emails.php'); exit; }

$def = lt_email_defaults();
$out = [];
$subjects = (array)($_POST['subject'] ?? []);
$bodies   = (array)($_POST['body'] ?? []);
foreach ($def as $key => $t) {
    $out[$key] = [
        'label'   => $t['label'],
        'subject' => trim((string)($subjects[$key] ?? $t['subject'])),
        'body'    => trim((string)($bodies[$key] ?? $t['body'])),
    ];
}
lt_email_save_templates($out);
lt_audit('email-templates-save', count($out) . ' templates');
header('Location: emails.php?saved=1');
exit;
