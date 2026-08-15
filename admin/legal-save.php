<?php
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');
if (!lt_check_csrf($_SERVER['HTTP_X_CSRF'] ?? '')) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF token']); exit; }

$d = json_decode(file_get_contents('php://input'), true);
if (!is_array($d)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$content = lt_content_load();
if (!isset($content['legal']) || !is_array($content['legal'])) $content['legal'] = [];
$priv = $content['legal']['privacy'] ?? [];

$L = ['en','hu','es'];
function pick3($v, $L) { $o = []; foreach ($L as $l) $o[$l] = trim((string)($v[$l] ?? '')); return $o; }

$priv['title'] = pick3($d['title'] ?? [], $L);
$priv['intro'] = ['en'=>(string)($d['intro']['en'] ?? ''), 'hu'=>(string)($d['intro']['hu'] ?? ''), 'es'=>(string)($d['intro']['es'] ?? '')];

$docs = [];
foreach (($d['docs'] ?? []) as $doc) {
    $label = pick3($doc['label'] ?? [], $L);
    // one shared file/URL for all languages
    $file  = trim((string)($doc['pdf'] ?? ''));
    if ($label['en'] === '' && $file === '') continue;
    $logo = trim((string)($doc['logo'] ?? ''));
    if (!preg_match('#^https?:#', $logo)) $logo = preg_replace('#^/?assets/img/#', '', $logo);
    $docs[] = [
        'group' => trim((string)($doc['group'] ?? '')),
        'label' => $label,
        'pdf'   => ['en'=>$file, 'hu'=>$file, 'es'=>$file],
        'logo'  => $logo,
    ];
}
$priv['partnerDocs'] = $docs;
$content['legal']['privacy'] = $priv;

list($ok, $err) = lt_content_save($content);
if ($ok) { lt_audit('legal.privacy.save', count($docs) . ' docs'); echo json_encode(['ok'=>true]); }
else { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$err]); }
