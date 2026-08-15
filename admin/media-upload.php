<?php
/* JSON image-upload endpoint — used by the in-editor media picker (blog, logos, gallery, avatar).
   Returns {ok:true, path:"/assets/img/uploads/xyz.webp"} or {ok:false, error:"…"}. */
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');

if (!lt_check_csrf($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? ''))) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF token']); exit;
}
if (empty($_FILES['file']['name'])) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No file received']); exit; }

$root = dirname(__DIR__) . '/assets/img';
$uploadDir = $root . '/uploads';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

$f = $_FILES['file'];
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp','gif','svg','avif'];
if ($f['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'Upload failed (error '.$f['error'].').']); exit; }
if (!in_array($ext, $allowed, true)) { echo json_encode(['ok'=>false,'error'=>'Only image files are allowed.']); exit; }
if ($f['size'] > 8 * 1024 * 1024) { echo json_encode(['ok'=>false,'error'=>'Max file size is 8 MB.']); exit; }

$name = preg_replace('/[^a-z0-9._-]/', '-', strtolower(basename($f['name'])));
$name = trim($name, '-.') ?: ('img-' . time() . '.' . $ext);
if (is_file("$uploadDir/$name")) $name = pathinfo($name, PATHINFO_FILENAME) . '-' . time() . '.' . $ext;

if (move_uploaded_file($f['tmp_name'], "$uploadDir/$name")) {
    lt_audit('media.upload', $name);
    echo json_encode(['ok'=>true, 'path'=>"/assets/img/uploads/$name", 'rel'=>"uploads/$name", 'name'=>$name], JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode(['ok'=>false,'error'=>'Could not save the file — check that assets/img/uploads is writable.']);
}
