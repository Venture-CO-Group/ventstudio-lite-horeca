<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();
if (!isset($content['testimonials']['items']) || !is_array($content['testimonials']['items'])) $content['testimonials']['items'] = [];
$items = &$content['testimonials']['items'];

$idx = isset($_GET['i']) ? (int)$_GET['i'] : -1;
$isNew = !isset($items[$idx]);
$t = $isNew ? ['active'=>true,'name'=>'','org'=>['en'=>'','hu'=>'','es'=>''],'role'=>['en'=>'','hu'=>'','es'=>''],'quote'=>['en'=>'','hu'=>'','es'=>'']] : $items[$idx];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $rec = [
        'active' => !empty($_POST['active']),
        'name'   => trim($_POST['name'] ?? ''),
        'org'    => ['en'=>trim($_POST['org_en']??''),'hu'=>trim($_POST['org_hu']??''),'es'=>trim($_POST['org_es']??'')],
        'role'   => ['en'=>trim($_POST['role_en']??''),'hu'=>trim($_POST['role_hu']??''),'es'=>trim($_POST['role_es']??'')],
        'quote'  => ['en'=>trim($_POST['quote_en']??''),'hu'=>trim($_POST['quote_hu']??''),'es'=>trim($_POST['quote_es']??'')],
    ];
    if ($isNew) $items[] = $rec; else $items[$idx] = $rec;
    $content['testimonials']['items'] = array_values($items);
    lt_content_save($content);
    lt_audit('testimonial.save', $rec['name']);
    header('Location: testimonials.php'); exit;
}

lt_admin_head($isNew ? 'New testimonial' : 'Edit testimonial');
lt_admin_sidebar('testimonials');
lt_admin_top('Vent Studio', $isNew ? 'New testimonial' : 'Edit testimonial', '<a class="btn-studio" href="testimonials.php">← All testimonials</a>');
$LANGS = ['en'=>'English','hu'=>'Magyar','es'=>'Español'];
?>
<div class="admin-body">
  <form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="section-card"><div class="card-body">
      <div class="field"><div class="field-label">Name</div>
        <input name="name" class="txt" value="<?= htmlspecialchars($t['name'] ?? '', ENT_QUOTES) ?>"></div>
      <label class="switch-row"><span>Visible on the site</span>
        <label class="switch"><input type="checkbox" name="active" <?= !empty($t['active'])?'checked':'' ?>><span class="switch-slider"></span></label>
      </label>

      <div class="lang-bar" id="tLangBar" style="margin-left:0">
        <span class="lang-lab">Editing language:</span>
        <?php $i=0; foreach ($LANGS as $l=>$nm): ?><button class="lang-pill<?= $i===0?' on':'' ?>" type="button" data-lang="<?= $l ?>"><?= $nm ?></button><?php $i++; endforeach; ?>
      </div>

      <?php $i=0; foreach ($LANGS as $l=>$nm): ?>
      <div class="lang-pane<?= $i===0?' on':'' ?>" data-lang="<?= $l ?>">
        <div class="field"><div class="field-label">Organisation (<?= $l ?>)</div>
          <input name="org_<?= $l ?>" class="txt" value="<?= htmlspecialchars($t['org'][$l] ?? '', ENT_QUOTES) ?>"></div>
        <div class="field"><div class="field-label">Role (<?= $l ?>)</div>
          <input name="role_<?= $l ?>" class="txt" value="<?= htmlspecialchars($t['role'][$l] ?? '', ENT_QUOTES) ?>"></div>
        <div class="field"><div class="field-label">Quote (<?= $l ?>)</div>
          <textarea name="quote_<?= $l ?>" style="min-height:120px"><?= htmlspecialchars($t['quote'][$l] ?? '') ?></textarea></div>
      </div>
      <?php $i++; endforeach; ?>

      <button class="btn-studio primary" type="submit">Save testimonial</button>
    </div></div>
  </form>
</div>
<script>
(function(){
  var bar=document.getElementById('tLangBar');
  bar.addEventListener('click',function(e){var b=e.target.closest('[data-lang]');if(!b)return;var l=b.getAttribute('data-lang');
    bar.querySelectorAll('.lang-pill').forEach(function(x){x.classList.toggle('on',x===b);});
    document.querySelectorAll('.lang-pane').forEach(function(p){p.classList.toggle('on',p.getAttribute('data-lang')===l);});});
})();
</script>
<?php lt_admin_foot();
