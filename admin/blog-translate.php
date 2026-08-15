<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$content = lt_content_load();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'savekey') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $content['settings']['deeplKey'] = trim($_POST['deeplKey'] ?? '');
    lt_content_save($content);
    lt_audit('settings.deeplKey');
    $msg = 'DeepL API key saved.';
    $content = lt_content_load();
}
$key = trim($content['settings']['deeplKey'] ?? '');

/* posts missing at least one language body */
$posts = lt_posts_load_all();
$missing = [];
foreach ($posts as $p) {
    foreach (['en','hu','es'] as $L) {
        if (trim((string)($p['body'][$L] ?? '')) === '' && trim((string)($p['title'][$L] ?? '')) === '') { $missing[] = $p['slug']; break; }
    }
}

lt_admin_head('Translate posts');
lt_admin_sidebar('blog');
lt_admin_top('Vent Studio', 'Translate posts', '<a class="btn-studio" href="posts.php">← All posts</a>');
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <div class="st-hint"><strong>Run “Merge translations” first</strong> so each post's existing languages sit in the right slots. Then this tool fills the remaining empty languages (EN/HU/ES) with DeepL, keeping the HTML formatting. Only empty fields are filled — existing text is never overwritten.</div>

  <div class="section-card"><h2>DeepL API key</h2><div class="card-body">
    <form method="post" class="form-row">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="savekey">
      <input name="deeplKey" type="text" value="<?= htmlspecialchars($key, ENT_QUOTES) ?>" placeholder="DeepL Auth Key (free keys end with :fx)" style="min-width:380px">
      <button class="btn-studio primary" type="submit">Save key</button>
      <span style="color:#888;font-size:12.5px">Get a free key at deepl.com/pro-api. Stored in site settings.</span>
    </form>
  </div></div>

  <div class="section-card"><h2>Fill missing languages <span style="color:#999;font-weight:400;font-size:14px"><?= count($missing) ?> post<?= count($missing)===1?'':'s' ?> need work</span></h2><div class="card-body">
    <?php if (!$key): ?>
      <p style="color:var(--err)">Add your DeepL API key above first.</p>
    <?php elseif (!$missing): ?>
      <p style="color:var(--success)">All posts already have EN, HU and ES. Nothing to translate. 🎉</p>
    <?php else: ?>
      <button class="btn-studio primary" id="startTr" type="button">Translate <?= count($missing) ?> posts →</button>
      <div class="tr-progress" id="trProg" style="display:none;margin-top:16px">
        <div class="tr-bar"><div class="tr-bar-fill" id="trFill"></div></div>
        <div id="trStatus" style="margin-top:8px;font-size:13px;color:var(--gray)"></div>
        <div id="trLog" style="margin-top:10px;font-size:12.5px;color:var(--gray);max-height:220px;overflow:auto"></div>
      </div>
    <?php endif; ?>
  </div></div>
</div>
<script>
(function(){
  var slugs = <?= json_encode($missing, JSON_UNESCAPED_SLASHES) ?>;
  var CSRF = <?= json_encode($csrf) ?>;
  var btn = document.getElementById('startTr'); if(!btn) return;
  var fill = document.getElementById('trFill'), status = document.getElementById('trStatus'), log = document.getElementById('trLog'), prog = document.getElementById('trProg');
  btn.addEventListener('click', function(){
    btn.disabled = true; prog.style.display='block'; var i=0, done=0, errors=0;
    function next(){
      if(i>=slugs.length){ status.textContent='Finished — '+done+' translated, '+errors+' error(s). Reload the Blog list to review.'; return; }
      var slug=slugs[i++];
      status.textContent='Translating '+i+' / '+slugs.length+' — '+slug;
      var fd=new FormData(); fd.append('csrf',CSRF); fd.append('slug',slug);
      fetch('blog-translate-run.php',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
        var line=document.createElement('div');
        if(j.ok){ done++; line.textContent='✓ '+slug+(j.filled&&j.filled.length?' ('+j.filled.join(', ')+')':''); }
        else { errors++; line.textContent='✕ '+slug+' — '+(j.error||'failed'); line.style.color='#C6362C'; }
        log.appendChild(line); log.scrollTop=log.scrollHeight;
        fill.style.width = Math.round(i/slugs.length*100)+'%';
        setTimeout(next, 250); // gentle pacing for the API
      }).catch(function(){ errors++; var l=document.createElement('div'); l.textContent='✕ '+slug+' — network error'; l.style.color='#C6362C'; log.appendChild(l); setTimeout(next,400); });
    }
    next();
  });
})();
</script>
<?php lt_admin_foot();
