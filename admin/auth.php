<?php
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/inc/store.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('lt_admin_sess');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function lt_logged_in()  { return !empty($_SESSION['lt_admin']); }
function lt_admin_email() { return $_SESSION['lt_admin_email'] ?? 'unknown'; }
function lt_require_login() { if (!lt_logged_in()) { header('Location: login.php'); exit; } }

function lt_is_owner() { return strcasecmp(lt_admin_email(), LT_OWNER) === 0; }
function lt_require_owner() { lt_require_login(); if (!lt_is_owner()) { http_response_code(403); exit('Forbidden — owner only.'); } }

/* role of the currently signed-in admin */
function lt_current_role() {
    if (lt_is_owner()) return 'owner';
    $a = lt_admins_load(); $e = lt_admin_email();
    return $a[$e]['role'] ?? 'admin';
}
/* owner and super-admins can manage other users (RBAC) */
function lt_can_manage_users() { return lt_is_owner() || lt_current_role() === 'superadmin'; }

function lt_admins_file() { return dirname(__DIR__) . '/data/admins.json'; }

/* Each admin: email => ['name','hash','role','active','created','last_login'].
   Legacy format (email => hash string) is upgraded transparently. */
function lt_admin_record($v, $email = '') {
    if (is_string($v)) {
        return ['name' => ucfirst(strtok($email, '@')), 'hash' => $v, 'role' => strcasecmp($email, LT_OWNER) === 0 ? 'owner' : 'admin',
                'active' => true, 'created' => '', 'last_login' => ''];
    }
    return array_merge(['name' => '', 'hash' => '', 'role' => 'admin', 'active' => true, 'created' => '', 'last_login' => ''], (array)$v);
}
function lt_admins_load() {
    $f = lt_admins_file(); $raw = null;
    if (is_file($f)) { $d = json_decode((string)file_get_contents($f), true); if (is_array($d) && $d) $raw = $d; }
    if ($raw === null) $raw = LT_ADMINS;
    $out = [];
    foreach ($raw as $e => $v) { $r = lt_admin_record($v, $e); if (strcasecmp($e, LT_OWNER) === 0) $r['role'] = 'owner'; $out[$e] = $r; }
    return $out;
}
function lt_admins_save($map) {
    if (!is_array($map) || !$map) return false;
    $json = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $f = lt_admins_file(); $tmp = $f . '.tmp';
    return !(file_put_contents($tmp, $json, LOCK_EX) === false || !@rename($tmp, $f));
}
function lt_admin_check($email, $pass) {
    $email = trim((string)$email);
    $map = lt_admins_load();
    foreach ($map as $e => $rec) {
        if (strcasecmp($email, $e) === 0 && !empty($rec['active']) && password_verify((string)$pass, $rec['hash'])) {
            $map[$e]['last_login'] = date('c');
            lt_admins_save($map);
            return $e;
        }
    }
    return false;
}
function lt_csrf() {
    if (empty($_SESSION['lt_csrf'])) $_SESSION['lt_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['lt_csrf'];
}
function lt_check_csrf($t) {
    return !empty($_SESSION['lt_csrf']) && is_string($t) && hash_equals($_SESSION['lt_csrf'], $t);
}
function lt_audit($action, $detail = '') {
    $rec = ['ts' => date('c'), 'admin' => lt_admin_email(), 'action' => (string)$action,
        'detail' => is_scalar($detail) ? (string)$detail : json_encode($detail, JSON_UNESCAPED_UNICODE),
        'ip' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)];
    @file_put_contents(dirname(__DIR__) . '/data/admin-log.jsonl',
        json_encode($rec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
}
