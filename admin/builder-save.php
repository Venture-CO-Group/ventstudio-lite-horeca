<?php
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');
if (!lt_check_csrf($_SERVER['HTTP_X_CSRF'] ?? '')) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF token']); exit; }

$raw = file_get_contents('php://input');
$d = json_decode($raw, true);
if (!is_array($d)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($d['slug'] ?? '')));
if ($slug === '') { echo json_encode(['ok'=>false,'error'=>'A URL slug is required.']); exit; }

/* reserved slugs that collide with fixed routes */
$reserved = ['en','hu','es','about','team','faq','contact','blog','legal','book-a-demo','sitemap.xml','assets','admin'];
if (in_array($slug, $reserved, true)) { echo json_encode(['ok'=>false,'error'=>'That slug is reserved — pick another.']); exit; }

$orig = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($d['origSlug'] ?? '')));

$page = [
    'slug'      => $slug,
    'title'     => ['en'=>trim($d['title']['en'] ?? ''), 'hu'=>trim($d['title']['hu'] ?? ''), 'es'=>trim($d['title']['es'] ?? '')],
    'metaDesc'  => ['en'=>trim($d['metaDesc']['en'] ?? ''), 'hu'=>trim($d['metaDesc']['hu'] ?? ''), 'es'=>trim($d['metaDesc']['es'] ?? '')],
    'published' => !empty($d['published']),
    'updated'   => date('c'),
    'blocks'    => is_array($d['blocks'] ?? null) ? $d['blocks'] : [],
];

if ($orig && $orig !== $slug) lt_page_delete($orig);
if (lt_page_save($page)) { lt_audit('page.save', $slug); echo json_encode(['ok'=>true, 'slug'=>$slug]); }
else { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Could not write data/pages.json — make the data folder writable.']); }
