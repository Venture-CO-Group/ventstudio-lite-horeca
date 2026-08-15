<?php
/* Per-image SEO metadata store (alt / title / description / caption).
   GET  ?path=uploads/x.webp        -> {ok:true, meta:{...}}
   POST csrf, path, alt, title, description, caption -> {ok:true} */
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');

$file = dirname(__DIR__) . '/data/media-meta.json';
function media_meta_load($file) {
    if (!is_file($file)) return [];
    $d = json_decode((string)file_get_contents($file), true);
    return is_array($d) ? $d : [];
}
function media_meta_save($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = $file . '.tmp';
    return !(file_put_contents($tmp, $json, LOCK_EX) === false || !@rename($tmp, $file));
}
function media_meta_key($p) {
    $p = preg_replace('#^/?assets/img/#', '', (string)$p);
    return trim(str_replace(['..', "\0"], '', $p), '/');
}

$all = media_meta_load($file);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? ''))) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF']); exit; }
    $key = media_meta_key($_POST['path'] ?? '');
    if ($key === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing path']); exit; }
    $all[$key] = [
        'alt'         => trim($_POST['alt'] ?? ''),
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'caption'     => trim($_POST['caption'] ?? ''),
    ];
    if ($all[$key] === ['alt'=>'','title'=>'','description'=>'','caption'=>'']) unset($all[$key]);
    media_meta_save($file, $all);
    lt_audit('media.meta', $key);
    echo json_encode(['ok'=>true]); exit;
}

$key = media_meta_key($_GET['path'] ?? '');
echo json_encode(['ok'=>true, 'meta'=>($all[$key] ?? ['alt'=>'','title'=>'','description'=>'','caption'=>''])], JSON_UNESCAPED_UNICODE);
