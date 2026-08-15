<?php
require_once __DIR__ . '/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) $err = 'Session expired — please try again.';
    elseif (trim($_POST['website'] ?? '') !== '') $err = 'Bot detected.';
    else {
        $who = lt_admin_check($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($who) {
            session_regenerate_id(true);
            $_SESSION['lt_admin'] = true; $_SESSION['lt_admin_email'] = $who;
            lt_audit('login'); header('Location: index.php'); exit;
        }
        $err = 'Invalid email or password.';
    }
}
$csrf = lt_csrf();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>VentStudio — Vent Studio Sign in</title>
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/brand/favicon-32.png">
<link rel="stylesheet" href="/assets/css/admin.css"></head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo"><img src="/assets/img/brand/logo.svg" alt="VentStudio" style="max-height:52px;width:auto"></div>
    <h1>Sign in</h1><p>Manage your VentStudio website — menu, orders &amp; content.</p>
    <?php if ($err): ?><div class="login-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="text" name="website" class="hp" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px">
      <label>Email<input type="email" name="email" required autofocus></label>
      <label>Password<input type="password" name="password" required></label>
      <button class="login-btn" type="submit">Sign in</button>
    </form>
    <a class="login-back" href="/">&larr; Back to website</a>
  </div>
</div>
</body></html>
