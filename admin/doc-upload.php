<?php
/* PDF/document upload endpoint (Privacy policy etc.). Saves to /assets/doc/.
   Returns {ok:true, path:"/assets/doc/xyz.pdf"} */
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');

if (!lt_check_csrf($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? ''))) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF token']); exit;
}
if (empty($_FILES['file']['name'])) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No file received']); exit; }

$dir = dirname(__DIR__) . '/assets/doc';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$f = $_FILES['file'];
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$allowed = ['pdf','doc','docx'];
if ($f['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'Upload failed (error '.$f['error'].').']); exit; }
if (!in_array($ext, $allowed, true)) { echo json_encode(['ok'=>false,'error'=>'Only PDF or Word documents are allowed.']); exit; }
if ($f['size'] > 25 * 1024 * 1024) { echo json_encode(['ok'=>false,'error'=>'Max file size is 25 MB.']); exit; }

$name = preg_replace('/[^a-z0-9._-]/', '-', strtolower(basename($f['name'])));
$name = trim($name, '-.') ?: ('doc-' . time() . '.' . $ext);
if (is_file("$dir/$name")) $name = pathinfo($name, PATHINFO_FILENAME) . '-' . time() . '.' . $ext;

if (move_uploaded_file($f['tmp_name'], "$dir/$name")) {
    lt_audit('doc.upload', $name);
    echo json_encode(['ok'=>true, 'path'=>"/assets/doc/$name", 'name'=>$name], JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode(['ok'=>false,'error'=>'Could not save the file — check that assets/doc is writable.']);
}
