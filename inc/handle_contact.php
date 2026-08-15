<?php
/* POST handler: contact / demo form. $LOCALE set by router. */
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/mailer.php';

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$company = trim($_POST['company'] ?? '');
$message = trim($_POST['message'] ?? '');
$source  = preg_replace('/[^a-z]/', '', $_POST['source'] ?? 'popup') ?: 'popup';
$hp      = trim($_POST['website'] ?? '');

$back = '/' . ($LOCALE ?? 'en') . '/contact?sent=';
if ($hp !== '') { header('Location: ' . $back . '1'); exit; }
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { header('Location: ' . $back . '0'); exit; }

/* silent file backup (data/ is web-blocked) — the inbox UI was removed on request */
lt_submission_add([
    'source' => $source, 'name' => $name, 'email' => $email, 'company' => $company,
    'message' => $message, 'locale' => $LOCALE ?? 'en',
    'ip' => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
    'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
]);

$s = lt_smtp_settings();
$subject = "[VentStudio] New $source message from $name";
$body = "Name: $name\nEmail: $email\nCompany: $company\nSource: $source\nLocale: " . ($LOCALE ?? 'en') . "\n\n$message\n";
lt_send_mail($s['to'], $subject, $body, $email);

header('Location: ' . $back . '1');
exit;
