<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$me   = lt_admin_email();
$isOwner = lt_is_owner();
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $admins = lt_admins_load();
    $act = $_POST['act'] ?? '';

    if ($act === 'profile') {
        $name   = trim($_POST['name'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $email  = strtolower(trim($_POST['email'] ?? ''));
        if ($name !== '') $admins[$me]['name'] = $name;
        $admins[$me]['avatar'] = $avatar;

        // e-mail change (re-key) — not allowed for the protected owner account
        if ($email !== '' && strcasecmp($email, $me) !== 0) {
            if ($isOwner) {
                $err = 'The owner e-mail is fixed in configuration and cannot be changed here.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $err = 'Please enter a valid e-mail address.';
            } elseif (isset($admins[$email])) {
                $err = 'That e-mail already belongs to another account.';
            } else {
                $rec = $admins[$me]; unset($admins[$me]); $admins[$email] = $rec;
                lt_admins_save($admins);
                $_SESSION['lt_admin_email'] = $email; $me = $email;
                lt_audit('account.email-change', $email);
                $msg = 'Profile updated — your sign-in e-mail is now ' . $email . '.';
            }
        }
        if ($err === '') {
            lt_admins_save($admins);
            lt_audit('account.profile');
            if ($msg === '') $msg = 'Your profile was updated.';
        }
    } elseif ($act === 'password') {
        $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['new'] ?? ''); $rep = (string)($_POST['repeat'] ?? '');
        if (!password_verify($cur, $admins[$me]['hash'] ?? '')) $err = 'Current password is incorrect.';
        elseif (strlen($new) < 10) $err = 'New password must be at least 10 characters.';
        elseif ($new !== $rep) $err = 'The new passwords do not match.';
        else { $admins[$me]['hash'] = password_hash($new, PASSWORD_BCRYPT); lt_admins_save($admins); lt_audit('account.password'); $msg = 'Your password was changed.'; }
    }
}

$admins = lt_admins_load();
$rec = $admins[$me] ?? ['name' => $me, 'role' => 'admin', 'avatar' => ''];
$parts = preg_split('/\s+/', trim($rec['name'] ?: $me));
$ini = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? '', 0, 1));
lt_admin_head('My account');
lt_admin_sidebar('');
lt_admin_top('Vent Studio', 'My account', '');
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <?php if ($err): ?><div class="notice" style="border-left-color:#b3261e"><strong><?= htmlspecialchars($err) ?></strong></div><?php endif; ?>

  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0">Profile</h2>
    <form method="post" id="profileForm">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="act" value="profile">

      <div class="field">
        <div class="field-label">Avatar</div>
        <div class="img-row">
          <div class="img-thumb<?= empty($rec['avatar']) ? ' empty' : '' ?>" id="avPrev">
            <?php if (!empty($rec['avatar'])): ?><img src="<?= htmlspecialchars($rec['avatar']) ?>" alt=""><?php else: ?><span style="font-weight:700;color:#8b91b8"><?= htmlspecialchars($ini) ?></span><?php endif; ?>
          </div>
          <input class="txt" type="text" name="avatar" id="avInput" placeholder="/assets/img/uploads/… or https://…" value="<?= htmlspecialchars($rec['avatar'] ?? '', ENT_QUOTES) ?>">
          <button class="btn-studio btn-mini" type="button" id="avBrowse">Browse…</button>
        </div>
      </div>

      <div class="field">
        <div class="field-label">Full name</div>
        <input class="txt" type="text" name="name" value="<?= htmlspecialchars($rec['name'] ?? '', ENT_QUOTES) ?>">
      </div>

      <div class="field">
        <div class="field-label">Sign-in e-mail</div>
        <input class="txt" type="email" name="email" value="<?= htmlspecialchars($me, ENT_QUOTES) ?>" <?= $isOwner ? 'readonly title="Owner e-mail is fixed in configuration"' : '' ?>>
        <?php if ($isOwner): ?><small class="copy-note">The owner account e-mail is fixed in configuration.</small><?php endif; ?>
      </div>

      <button class="btn-studio primary" type="submit">Save profile</button>
    </form>
  </div></div>

  <div class="section-card"><div class="card-body">
    <h2 style="margin-top:0">Change password</h2>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="act" value="password">
      <div class="form-row">
        <input type="password" name="current" placeholder="current password" required>
        <input type="password" name="new" placeholder="new password (min 10)" required minlength="10">
        <input type="password" name="repeat" placeholder="repeat new password" required minlength="10">
        <button class="btn-studio primary" type="submit">Change password</button>
      </div>
    </form>
  </div></div>
</div>

<!-- lightweight media picker (reuses media-list.php) -->
<div class="mp-overlay" id="avPicker">
  <div class="mp-card">
    <div class="mp-head"><strong>Choose an image</strong><button class="mp-close" type="button" id="avClose">&times;</button></div>
    <div class="mp-body" id="avBody">Loading…</div>
  </div>
</div>
<script>
(function(){
  var inp=document.getElementById('avInput'), prev=document.getElementById('avPrev');
  var ov=document.getElementById('avPicker'), body=document.getElementById('avBody');
  function setPrev(v){ prev.innerHTML = v ? '<img src="'+v+'" alt="">' : ''; prev.classList.toggle('empty', !v); }
  inp.addEventListener('input', function(){ setPrev(inp.value); });
  document.getElementById('avBrowse').addEventListener('click', function(){
    ov.classList.add('open');
    if (body.dataset.loaded) return;
    fetch('media-list.php').then(function(r){return r.json();}).then(function(d){
      body.innerHTML=''; body.dataset.loaded='1';
      Object.keys(d).forEach(function(folder){
        if(!d[folder].files.length) return;
        var fh=document.createElement('div'); fh.className='mp-folder'; fh.textContent=d[folder].label; body.appendChild(fh);
        var grid=document.createElement('div'); grid.className='mp-grid';
        d[folder].files.forEach(function(f){
          var cell=document.createElement('button'); cell.type='button'; cell.className='mp-cell';
          var img=document.createElement('img'); img.loading='lazy'; img.src='/assets/img/'+folder+'/'+f; cell.appendChild(img);
          var nm=document.createElement('span'); nm.textContent=f; cell.appendChild(nm);
          cell.addEventListener('click', function(){ var rel='/assets/img/'+folder+'/'+f; inp.value=rel; setPrev(rel); ov.classList.remove('open'); });
          grid.appendChild(cell);
        });
        body.appendChild(grid);
      });
    }).catch(function(){ body.textContent='Could not load the media library.'; });
  });
  document.getElementById('avClose').addEventListener('click', function(){ ov.classList.remove('open'); });
  ov.addEventListener('click', function(e){ if(e.target===ov) ov.classList.remove('open'); });
})();
</script>
<?php lt_admin_foot();
