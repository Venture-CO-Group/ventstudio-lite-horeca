<?php
/* VentStudio — bootstrap: content + helpers (English-only build).
   Included by the front controller (index.php) before output. */

require_once __DIR__ . '/store.php';
if (!defined('LT_ROOT')) define('LT_ROOT', dirname(__DIR__));

$C = lt_content_load();

/* Single-language site. Kept as an array so multi-language can return later. */
$ALLOWED = ['en'];
$LOCALE  = 'en';
$GLOBALS['LOCALE']  = $LOCALE;
$GLOBALS['ALLOWED'] = $ALLOWED;

/* ---- helpers ---- */
function cur_lang(): string { return 'en'; }

/** pick a value: supports plain scalars and legacy {en,hu,es} nodes */
function t($n) {
    if (!is_array($n)) return $n === null ? '' : $n;
    $v = $n['en'] ?? null;
    if ($v === null) { foreach ($n as $x) { if (!is_array($x)) { $v = $x; break; } } }
    return is_array($v) ? '' : (string)($v ?? '');
}
/** dotted-path lookup into content tree */
function g($path) {
    $o = $GLOBALS['C'] ?? [];
    foreach (explode('.', $path) as $p) {
        if (is_array($o) && array_key_exists($p, $o)) $o = $o[$p];
        else return null;
    }
    return $o;
}
function tg($path) { return t(g($path)); }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function settings($k, $d = '') { return $GLOBALS['C']['settings'][$k] ?? $d; }

/** legacy literal helper — now returns English only */
function L($en, $hu = null, $es = null) { return $en; }

/** clean site URL: url('menu') -> /menu ; url('') -> / */
function url(string $path = ''): string {
    $path = trim($path, '/');
    return $path === '' ? '/' : '/' . $path . '/';
}
function lang_switch_url(string $lang): string { return url($GLOBALS['ROUTE_PATH'] ?? ''); }

/* ---- money + menu helpers ---- */
function money($amount): string {
    $sym = settings('currencySymbol', '£');
    return $sym . number_format((float)$amount, 2);
}
/** flat map of slug => item across all menu groups (for server-side price validation) */
function lt_menu_index(): array {
    static $idx = null;
    if ($idx !== null) return $idx;
    $idx = [];
    foreach ((array)g('menu.groups') as $grp) {
        foreach ((array)($grp['items'] ?? []) as $it) {
            if (!empty($it['slug'])) $idx[$it['slug']] = $it;
        }
    }
    return $idx;
}
function menu_img($slug): string {
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        $rel = '/assets/img/menu/' . $slug . '.' . $ext;
        if (is_file(LT_ROOT . $rel)) return $rel;
    }
    return '';
}

/** Is a menu item shown on the website? Hidden if visible=false or stock is 0. */
function lt_item_visible($it): bool {
    if (is_array($it) && array_key_exists('visible', $it) && $it['visible'] === false) return false;
    if (is_array($it) && array_key_exists('stock', $it) && $it['stock'] !== null && $it['stock'] !== '' && (int)$it['stock'] <= 0) return false;
    return true;
}
/** Return only the visible items of a group. */
function lt_visible_items($grp): array {
    return array_values(array_filter((array)($grp['items'] ?? []), 'lt_item_visible'));
}

/** responsive <img> for an asset under assets/img */
function img_tag(string $src, string $alt, string $cls = '', bool $lazy = true): string {
    $l = $lazy ? ' loading="lazy" decoding="async"' : '';
    $c = $cls ? ' class="' . e($cls) . '"' : '';
    return '<img src="/assets/img/' . e($src) . '" alt="' . e($alt) . '"' . $c . $l . '>';
}
