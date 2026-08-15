<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();
if (!isset($content['testimonials']['items']) || !is_array($content['testimonials']['items'])) $content['testimonials']['items'] = [];
$items = $content['testimonials']['items'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $i = isset($_POST['i']) ? (int)$_POST['i'] : -1;
    $act = $_POST['act'] ?? '';
    if ($act === 'toggle' && isset($items[$i])) { $items[$i]['active'] = empty($items[$i]['active']); $msg = 'Visibility updated.'; }
    elseif ($act === 'delete' && isset($items[$i])) { array_splice($items, $i, 1); $msg = 'Testimonial removed.'; }
    elseif ($act === 'up' && $i > 0) { $t = $items[$i]; $items[$i] = $items[$i-1]; $items[$i-1] = $t; }
    elseif ($act === 'down' && isset($items[$i+1])) { $t = $items[$i]; $items[$i] = $items[$i+1]; $items[$i+1] = $t; }
    $content['testimonials']['items'] = array_values($items);
    lt_content_save($content);
    lt_audit('testimonials.' . $act);
    $items = $content['testimonials']['items'];
}

lt_admin_head('Testimonials');
lt_admin_sidebar('testimonials');
lt_admin_top('Vent Studio', 'Testimonials', '<a class="btn-studio primary" href="testimonial-edit.php">+ Add testimonial</a>');
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <div class="st-hint">Each testimonial can be shown or hidden with the toggle. Click a name to edit its text in English, Magyar or Español.</div>

  <table class="data-table">
    <tr><th style="width:70px">Visible</th><th>Name</th><th>Organisation</th><th>Quote</th><th style="width:200px">Actions</th></tr>
    <?php if (!$items): ?>
      <tr><td colspan="5" style="color:var(--gray)">No testimonials yet. Add your first one.</td></tr>
    <?php endif; ?>
    <?php foreach ($items as $i => $q): ?>
    <tr>
      <td>
        <form method="post" style="margin:0">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="toggle"><input type="hidden" name="i" value="<?= $i ?>">
          <label class="switch"><input type="checkbox" onchange="this.form.submit()" <?= !empty($q['active'])?'checked':'' ?>><span class="switch-slider"></span></label>
        </form>
      </td>
      <td><a href="testimonial-edit.php?i=<?= $i ?>" style="font-weight:700;color:var(--navy)"><?= htmlspecialchars($q['name'] ?? '(no name)') ?></a></td>
      <td><?= htmlspecialchars($q['org']['en'] ?? '') ?></td>
      <td style="color:var(--gray)"><?= htmlspecialchars(mb_strimwidth((string)($q['quote']['en'] ?? ''), 0, 70, '…')) ?></td>
      <td>
        <a class="btn-studio btn-mini" href="testimonial-edit.php?i=<?= $i ?>">Edit</a>
        <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="up"><input type="hidden" name="i" value="<?= $i ?>"><button class="btn-studio btn-mini" type="submit" title="Move up">↑</button></form>
        <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="down"><input type="hidden" name="i" value="<?= $i ?>"><button class="btn-studio btn-mini" type="submit" title="Move down">↓</button></form>
        <form method="post" style="display:inline" onsubmit="return confirm('Remove this testimonial?')"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="delete"><input type="hidden" name="i" value="<?= $i ?>"><button class="btn-studio btn-mini btn-danger" type="submit">Delete</button></form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php lt_admin_foot();
