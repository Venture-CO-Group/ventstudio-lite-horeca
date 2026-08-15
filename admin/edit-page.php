<?php
require_once __DIR__ . '/_editor.php';
require_once __DIR__ . '/_pages.php';
$pages = lt_admin_pages();
$id = $_GET['id'] ?? 'home';
if (!isset($pages[$id])) { header('Location: index.php'); exit; }
$p = $pages[$id];
lt_admin_editor('pages', 'Pages — ' . $p['label'],
    [['id' => $id, 'label' => $p['label'], 'group' => 'Page', 'sections' => $p['sections']]],
    '<a href="index.php">← All pages</a>');
