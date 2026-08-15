<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/inc/store.php';
lt_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !lt_check_csrf($_POST['csrf'] ?? '')) {
    header('Location: menu.php'); exit;
}
$in = json_decode($_POST['menu_json'] ?? '', true);
if (!is_array($in) || !isset($in['groups'])) { header('Location: menu.php?err=1'); exit; }

/* sanitize */
$clean = ['intro' => trim((string)($in['intro'] ?? '')), 'groups' => []];
$slugify = function ($s) {
    $s = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$s));
    return trim($s, '-');
};
foreach ((array)$in['groups'] as $g) {
    $title = trim((string)($g['title'] ?? ''));
    if ($title === '') continue;
    $grp = [
        'id' => $slugify($g['id'] ?? $title) ?: $slugify($title),
        'title' => $title,
        'accent' => in_array($g['accent'] ?? '', ['hotsauce','honey','fresh','berry'], true) ? $g['accent'] : 'hotsauce',
        'items' => [],
    ];
    if (!empty($g['note'])) $grp['note'] = trim((string)$g['note']);
    foreach ((array)($g['items'] ?? []) as $it) {
        $name = trim((string)($it['name'] ?? ''));
        if ($name === '') continue;
        $item = [
            'slug' => $slugify($it['slug'] ?? $name) ?: $slugify($name),
            'name' => $name,
            'desc' => trim((string)($it['desc'] ?? '')),
            'price' => round((float)($it['price'] ?? 0), 2),
            'tags' => array_values(array_filter(array_map('trim', (array)($it['tags'] ?? [])))),
            'allergens' => array_values(array_intersect(
                array_map(function ($x) { return strtolower(trim($x)); }, (array)($it['allergens'] ?? [])),
                ['celery','gluten','crustaceans','eggs','fish','lupin','milk','molluscs','mustard','nuts','peanuts','sesame','soya','sulphites'])),
            'visible' => !(array_key_exists('visible', (array)$it) && $it['visible'] === false),
            'stock' => (array_key_exists('stock', (array)$it) && $it['stock'] !== null && $it['stock'] !== '') ? max(0, (int)$it['stock']) : null,
        ];
        if (!empty($it['preorder'])) {
            $item['preorder'] = true;
            $item['preorderHours'] = max(0, (int)($it['preorderHours'] ?? 48)) ?: 48;
        }
        $grp['items'][] = $item;
    }
    $clean['groups'][] = $grp;
}

$C = lt_content_load();
$C['menu'] = $clean;
[$ok, $err] = lt_content_save($C);
lt_audit('menu-save', $ok ? count($clean['groups']) . ' groups' : $err);
header('Location: menu.php?' . ($ok ? 'saved=1' : 'err=1'));
exit;
