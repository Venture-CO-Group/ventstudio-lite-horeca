<?php
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');
if (!lt_check_csrf($_SERVER['HTTP_X_CSRF'] ?? '')) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF token']); exit; }
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }
list($ok,$err) = lt_content_save($data);
if ($ok) { lt_audit('content.save', 'bytes=' . strlen($raw)); echo json_encode(['ok'=>true]); }
else { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$err]); }
