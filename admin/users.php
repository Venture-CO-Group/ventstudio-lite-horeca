<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$me = lt_admin_email();
$isOwner = lt_is_owner();
$canManage = lt_can_manage_users();
$ROLES = ['superadmin' => 'Super admin', 'admin' => 'Admin', 'editor' => 'Editor'];
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $admins = lt_admins_load();
    $act = $_POST['act'] ?? '';

    if ($act === 'self-password') {
        $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['new'] ?? '');
        if (!password_verify($cur, $admins[$me]['hash'] ?? '')) $err = 'Current password is incorrect.';
        elseif (strlen($new) < 10) $err = 'New password must be at least 10 characters.';
        else { $admins[$me]['hash'] = password_hash($new, PASSWORD_BCRYPT); lt_admins_save($admins); lt_audit('user.self-password'); $msg = 'Your password was changed.'; }
    } elseif (!$canManage) {
        $err = 'Only the owner or a super admin can manage users.';
    } elseif ($act === 'add') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $name  = trim($_POST['name'] ?? '');
        $pass  = (string)($_POST['password'] ?? '');
        $role  = in_array($_POST['role'] ?? '', ['superadmin','admin','editor'], true) ? $_POST['role'] : 'admin';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Valid e-mail required.';
        elseif (isset($admins[$email])) $err = 'This e-mail already has access.';
        elseif (strlen($pass) < 10) $err = 'Password must be at least 10 characters.';
        else {
            $admins[$email] = ['name' => $name ?: ucfirst(strtok($email,'@')), 'hash' => password_hash($pass, PASSWORD_BCRYPT),
                'role' => $role, 'active' => true, 'created' => date('c'), 'last_login' => ''];
            lt_admins_save($admins); lt_audit('user.add', $email); $msg = "Added $email.";
        }
    } elseif (in_array($act, ['toggle','reset','remove','role'], true)) {
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!isset($admins[$email])) $err = 'Unknown user.';
        elseif (strcasecmp($email, LT_OWNER) === 0) $err = 'The owner account is protected.';
        elseif ($act === 'role') {
            $role = in_array($_POST['role'] ?? '', ['superadmin','admin','editor'], true) ? $_POST['role'] : 'admin';
            $admins[$email]['role'] = $role; lt_admins_save($admins); lt_audit('user.role', $email . '=' . $role); $msg = "Role updated for $email.";
        }
        elseif ($act === 'toggle') { $admins[$email]['active'] = empty($admins[$email]['active']); lt_admins_save($admins); lt_audit('user.toggle', $email); $msg = ($admins[$email]['active'] ? 'Activated ' : 'Deactivated ') . $email . '.'; }
        elseif ($act === 'remove') { unset($admins[$email]); lt_admins_save($admins); lt_audit('user.remove', $email); $msg = "Removed $email."; }
        elseif ($act === 'reset') {
            $pass = (string)($_POST['password'] ?? '');
            if (strlen($pass) < 10) $err = 'Password must be at least 10 characters.';
            else { $admins[$email]['hash'] = password_hash($pass, PASSWORD_BCRYPT); lt_admins_save($admins); lt_audit('user.reset', $email); $msg = "Password reset for $email."; }
        }
    }
}
$admins = lt_admins_load();
lt_admin_head('Users');
lt_admin_sidebar('users');
lt_admin_top('Vent Studio', 'Users & access', '');
function dt($iso){ return $iso ? date('Y-m-d H:i', strtotime($iso)) : '—'; }
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <?php if ($err): ?><div class="notice" style="border-left-color:#b3261e"><strong><?= htmlspecialchars($err) ?></strong></div><?php endif; ?>

  <table class="data-table">
    <tr><th>Name</th><th>E-mail</th><th>Role</th><th>Status</th><th>Last login</th><th>Created</th><?php if ($canManage): ?><th style="width:300px">Actions</th><?php endif; ?></tr>
    <?php foreach ($admins as $email => $a): $owner = strcasecmp($email, LT_OWNER) === 0; ?>
    <tr>
      <td><span class="u-cell"><span class="u-avatar"><?php $p=preg_split('/\s+/',trim($a['name'] ?: $email)); echo htmlspecialchars(strtoupper(substr($p[0],0,1).substr($p[1] ?? '',0,1))); ?></span><strong><?= htmlspecialchars($a['name']) ?></strong><?= strcasecmp($email,$me)===0 ? ' <span class="badge you">you</span>' : '' ?></span></td>
      <td><?= htmlspecialchars($email) ?></td>
      <td>
        <?php if ($canManage && !$owner): ?>
        <form method="post" style="display:inline">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="role"><input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">
          <select name="role" onchange="this.form.submit()" class="role-sel">
            <?php foreach ($ROLES as $rk=>$rl): ?><option value="<?= $rk ?>" <?= ($a['role']===$rk)?'selected':'' ?>><?= htmlspecialchars($rl) ?></option><?php endforeach; ?>
          </select>
        </form>
        <?php else: ?><span class="badge <?= $owner ? 'on' : 'off' ?>"><?= htmlspecialchars($a['role']) ?></span><?php endif; ?>
      </td>
      <td><span class="badge <?= !empty($a['active']) ? 'on' : 'off' ?>"><?= !empty($a['active']) ? 'active' : 'disabled' ?></span></td>
      <td><?= dt($a['last_login']) ?></td>
      <td><?= dt($a['created']) ?></td>
      <?php if ($canManage): ?>
      <td>
        <?php if (!$owner): ?>
        <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="toggle"><input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">
          <button class="btn-studio btn-mini" type="submit"><?= !empty($a['active']) ? 'Deactivate' : 'Activate' ?></button></form>
        <form method="post" style="display:inline" onsubmit="var p=prompt('New password for <?= htmlspecialchars($email) ?> (min 10 chars):');if(!p)return false;this.password.value=p;return true">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="reset"><input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>"><input type="hidden" name="password" value="">
          <button class="btn-studio btn-mini" type="submit">Reset password</button></form>
        <form method="post" style="display:inline" onsubmit="return confirm('Remove <?= htmlspecialchars($email) ?>?')">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="remove"><input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">
          <button class="btn-studio btn-mini btn-danger" type="submit">Remove</button></form>
        <?php else: ?><span style="color:#999;font-size:12.5px">Owner — protected</span><?php endif; ?>
      </td>
      <?php endif; ?>
    </tr>
    <?php endforeach; ?>
  </table>

  <?php if ($canManage): ?>
  <div class="section-card" style="margin-top:22px"><div class="card-body">
    <h2 style="margin-top:0">Add user</h2>
    <form method="post" class="form-row">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="add">
      <input name="name" placeholder="Full name">
      <input name="email" type="email" placeholder="e-mail" required>
      <input name="password" type="text" placeholder="password (min 10 chars)" required minlength="10" style="min-width:220px">
      <select name="role"><?php foreach ($ROLES as $rk=>$rl): ?><option value="<?= $rk ?>" <?= $rk==='admin'?'selected':'' ?>><?= htmlspecialchars($rl) ?></option><?php endforeach; ?></select>
      <button class="btn-studio primary" type="submit">Add user</button>
    </form>
    <p style="color:var(--gray);font-size:12.5px;margin:8px 0 0"><strong>Super admin</strong> can manage users; <strong>Admin</strong> edits all content; <strong>Editor</strong> edits content only. The owner account is fixed in configuration.</p>
  </div></div>
  <?php endif; ?>

  <div class="section-card" style="margin-top:22px"><div class="card-body">
    <h2 style="margin-top:0">Change my password</h2>
    <form method="post" class="form-row">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="self-password">
      <input name="current" type="password" placeholder="current password" required>
      <input name="new" type="password" placeholder="new password (min 10 chars)" required minlength="10">
      <button class="btn-studio primary" type="submit">Change password</button>
    </form>
  </div></div>
</div>
<?php lt_admin_foot();
