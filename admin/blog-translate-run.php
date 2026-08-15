<?php
/* Translate one post's empty language fields via DeepL. POST: csrf, slug. Returns JSON. */
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');
if (!lt_check_csrf($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? ''))) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF']); exit; }

$content = lt_content_load();
$key = trim($content['settings']['deeplKey'] ?? '');
if ($key === '') { echo json_encode(['ok'=>false,'error'=>'No DeepL API key set.']); exit; }

$slug = trim($_POST['slug'] ?? '');
$p = lt_post_by_slug($slug);
if (!$p) { echo json_encode(['ok'=>false,'error'=>'Post not found']); exit; }

function deepl_call($key, $text, $target, $html) {
    if (trim((string)$text) === '') return '';
    $base = (strtolower(substr($key, -3)) === ':fx') ? 'https://api-free.deepl.com' : 'https://api.deepl.com';
    $fields = ['text' => $text, 'target_lang' => strtoupper($target)];
    if ($html) { $fields['tag_handling'] = 'html'; }
    $ch = curl_init($base . '/v2/translate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: DeepL-Auth-Key ' . $key],
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_TIMEOUT => 40,
    ]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($res === false || $code >= 400) return ['__err' => "DeepL HTTP $code"];
    $j = json_decode($res, true);
    return $j['translations'][0]['text'] ?? '';
}

$langs = ['en','hu','es'];
$firstText = function($map) { foreach (['en','hu','es'] as $l) if (trim((string)($map[$l] ?? '')) !== '') return $map[$l]; return ''; };

$filled = [];
foreach ($langs as $L) {
    $needTitle = trim((string)($p['title'][$L] ?? '')) === '';
    $needBody  = trim((string)($p['body'][$L] ?? '')) === '';
    $needExc   = trim((string)($p['excerpt'][$L] ?? '')) === '';
    if (!$needTitle && !$needBody && !$needExc) continue;

    if ($needTitle) { $r = deepl_call($key, $firstText($p['title'] ?? []), $L, false); if (is_array($r)) { echo json_encode(['ok'=>false,'error'=>$r['__err']]); exit; } if ($r!=='') $p['title'][$L] = $r; }
    if ($needExc)   { $r = deepl_call($key, $firstText($p['excerpt'] ?? []), $L, false); if (is_array($r)) { echo json_encode(['ok'=>false,'error'=>$r['__err']]); exit; } if ($r!=='') $p['excerpt'][$L] = $r; }
    if ($needBody)  { $r = deepl_call($key, $firstText($p['body'] ?? []), $L, true); if (is_array($r)) { echo json_encode(['ok'=>false,'error'=>$r['__err']]); exit; } if ($r!=='') $p['body'][$L] = $r; }
    $filled[] = $L;
}

if ($filled) { lt_post_save($p); lt_audit('post.translate', $slug . ':' . implode(',', $filled)); }
echo json_encode(['ok'=>true, 'slug'=>$slug, 'filled'=>$filled]);
