<?php
/* POST handler: newsletter subscribe -> Mailchimp. $LOCALE set by router. */
require_once __DIR__ . '/store.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$hp    = trim($_POST['website'] ?? '');
if ($hp !== '') { echo json_encode(['ok' => true]); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['ok' => false, 'error' => 'invalid_email']); exit;
}

$C = lt_content_load();
$mc = $C['settings']['mailchimp'] ?? [];
$key = trim($mc['apiKey'] ?? '') ?: LT_MAILCHIMP_KEY;
$aud = trim($mc['audienceId'] ?? '') ?: LT_MAILCHIMP_AUDIENCE;
$mcId = ''; $mcOk = false;

if ($key && $aud && strpos($key, '-') !== false) {
    $dc = substr(strrchr($key, '-'), 1);
    $double = !empty($mc['doubleOptIn']);
    $payload = json_encode(['email_address' => $email, 'status' => $double ? 'pending' : 'subscribed', 'language' => $LOCALE ?? 'en']);
    $ch = curl_init("https://$dc.api.mailchimp.com/3.0/lists/$aud/members");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
        CURLOPT_USERPWD => 'anystring:' . $key, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 10]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $j = json_decode((string)$res, true);
    if (is_array($j) && !empty($j['id'])) { $mcId = $j['id']; $mcOk = true; }
    if ($code === 400 && is_array($j) && stripos($j['title'] ?? '', 'exists') !== false) $mcOk = true; // already subscribed
}

/* local backup list (no UI; export lives in Mailchimp) */
lt_subscriber_add($email, $LOCALE ?? 'en', $mcId);
echo json_encode(['ok' => true, 'mailchimp' => $mcOk]);
