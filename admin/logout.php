<?php
require_once __DIR__ . '/auth.php';
lt_audit('logout');
$_SESSION = []; session_destroy();
header('Location: login.php'); exit;
