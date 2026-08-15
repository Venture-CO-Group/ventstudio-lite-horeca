<?php
require_once __DIR__ . '/auth.php';
lt_require_login();
if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
if (!empty($_POST['delete'])) { lt_post_delete($_POST['delete']); lt_audit('post.delete', $_POST['delete']); header('Location: posts.php'); exit; }
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['slug'] ?? '')));
if ($slug === '') { header('Location: posts.php'); exit; }

$body = ['en'=>(string)($_POST['body_en'] ?? ''), 'hu'=>(string)($_POST['body_hu'] ?? ''), 'es'=>(string)($_POST['body_es'] ?? '')];

/* auto min-read: longest body across languages, ~200 words/min, floor 1 */
$maxWords = 0;
foreach ($body as $b) { $w = str_word_count(trim(strip_tags($b))); if ($w > $maxWords) $maxWords = $w; }
$autoRead = max(1, (int)ceil($maxWords / 200));
$readMin = (int)($_POST['readMin'] ?? 0);
if ($readMin < 1) $readMin = $autoRead;

$status = in_array($_POST['status'] ?? '', ['draft','published','scheduled'], true) ? $_POST['status'] : 'draft';
$publishAt = trim($_POST['publishAt'] ?? '');
if ($status !== 'scheduled') $publishAt = '';
/* keep the legacy boolean in sync so anything still reading it behaves sanely */
$isPublished = ($status === 'published') || ($status === 'scheduled' && $publishAt !== '' && strtotime($publishAt) !== false && strtotime($publishAt) <= time());

$post = [
  'slug' => $slug,
  'date' => $_POST['date'] ?: date('Y-m-d'),
  'status' => $status,
  'publishAt' => $publishAt,
  'published' => $isPublished,
  'featured' => !empty($_POST['featured']),
  'cover' => trim($_POST['cover'] ?? ''),
  'category' => trim($_POST['category'] ?? ''),
  'readMin' => $readMin,
  'title' => ['en'=>trim($_POST['title_en'] ?? ''), 'hu'=>trim($_POST['title_hu'] ?? ''), 'es'=>trim($_POST['title_es'] ?? '')],
  'excerpt' => ['en'=>trim($_POST['excerpt_en'] ?? ''), 'hu'=>trim($_POST['excerpt_hu'] ?? ''), 'es'=>trim($_POST['excerpt_es'] ?? '')],
  'body' => $body,
];
// if slug changed, delete the old record
$orig = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['orig_slug'] ?? '')));
if ($orig && $orig !== $slug) lt_post_delete($orig);
lt_post_save($post);
lt_audit('post.save', $slug);
header('Location: posts.php'); exit;
