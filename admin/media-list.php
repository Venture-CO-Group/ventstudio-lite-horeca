<?php
require_once __DIR__ . '/auth.php';
lt_require_login();
header('Content-Type: application/json');
$root = dirname(__DIR__) . '/assets/img';
$folders = [
    'uploads' => 'Uploads', 'gallery' => 'Gallery photos', 'logos' => 'Partner logos',
    'team' => 'Team photos', 'photos' => 'Product photos', 'brand' => 'Brand assets',
];
$out = [];
foreach ($folders as $dir => $label) {
    $p = "$root/$dir";
    $files = is_dir($p) ? array_values(array_filter(scandir($p), fn($x) => preg_match('/\.(jpe?g|png|webp|gif|svg|avif)$/i', $x))) : [];
    sort($files);
    $out[$dir] = ['label' => $label, 'files' => $files];
}
echo json_encode($out, JSON_UNESCAPED_SLASHES);
