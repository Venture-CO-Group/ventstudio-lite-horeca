<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$editing = null;
if (isset($_GET['slug'])) $editing = lt_post_by_slug($_GET['slug']);
$csrf = lt_csrf();
function pv($p,$k,$l){ return htmlspecialchars($p[$k][$l] ?? '', ENT_QUOTES); }

/* existing categories for the dropdown */
$cats = [];
foreach (lt_posts_load_all() as $pp) { $c = trim($pp['category'] ?? ''); if ($c !== '' && !in_array($c, $cats, true)) $cats[] = $c; }
sort($cats);

/* current status (back-compat with old published boolean) */
$curStatus = $editing['status'] ?? (!empty($editing['published']) ? 'published' : 'draft');
$curPublishAt = $editing['publishAt'] ?? '';

lt_admin_head($editing ? 'Edit post' : 'New post');
lt_admin_sidebar('blog');
lt_admin_top('Vent Studio', $editing ? 'Edit post' : 'New post', '<a class="btn-studio" href="posts.php">← All posts</a>');
$LANGS = ['en'=>'English'];
?>
<div class="admin-body">
  <form method="post" action="blog-save.php" id="postForm">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="orig_slug" value="<?= htmlspecialchars($editing['slug'] ?? '', ENT_QUOTES) ?>">

    <div class="post-editor">
      <!-- MAIN column -->
      <div class="post-main">
        <div class="section-card"><div class="card-body">
          <!-- language switcher -->
          <div class="lang-bar" id="postLangBar">
            <span class="lang-lab">Editing language:</span>
            <?php $i=0; foreach ($LANGS as $l=>$nm): ?>
              <button class="lang-pill<?= $i===0?' on':'' ?>" type="button" data-lang="<?= $l ?>"><?= $nm ?></button>
            <?php $i++; endforeach; ?>
          </div>

          <?php $i=0; foreach ($LANGS as $l=>$nm): ?>
          <div class="lang-pane<?= $i===0?' on':'' ?>" data-lang="<?= $l ?>">
            <div class="field"><div class="field-label">Title (<?= $l ?>)</div>
              <input name="title_<?= $l ?>" class="txt" value="<?= pv($editing ?? [], 'title', $l) ?>"></div>
            <div class="field"><div class="field-label">Excerpt (<?= $l ?>)</div>
              <textarea name="excerpt_<?= $l ?>" style="min-height:70px"><?= htmlspecialchars($editing['excerpt'][$l] ?? '') ?></textarea></div>
            <div class="field"><div class="field-label">Body — HTML (<?= $l ?>)</div>
              <textarea name="body_<?= $l ?>" class="post-body-ta" data-wysiwyg data-lang="<?= $l ?>" style="min-height:340px"><?= htmlspecialchars($editing['body'][$l] ?? '') ?></textarea></div>
          </div>
          <?php $i++; endforeach; ?>
        </div></div>
      </div>

      <!-- SIDEBAR column -->
      <div class="post-side">
        <div class="section-card"><h2>Publish</h2><div class="card-body">
          <div class="field"><div class="field-label">Status</div>
            <select name="status" id="statusSel" class="txt">
              <option value="draft"     <?= $curStatus==='draft'?'selected':'' ?>>Draft</option>
              <option value="published" <?= $curStatus==='published'?'selected':'' ?>>Published</option>
              <option value="scheduled" <?= $curStatus==='scheduled'?'selected':'' ?>>Scheduled</option>
            </select>
          </div>
          <div class="field" id="schedRow" style="<?= $curStatus==='scheduled'?'':'display:none' ?>">
            <div class="field-label">Publish at</div>
            <input type="datetime-local" name="publishAt" class="txt" value="<?= htmlspecialchars($curPublishAt, ENT_QUOTES) ?>">
          </div>
          <div class="field"><div class="field-label">Post date</div>
            <input name="date" type="date" class="txt" required value="<?= htmlspecialchars($editing['date'] ?? date('Y-m-d'), ENT_QUOTES) ?>"></div>
          <label class="chk"><input type="checkbox" name="featured" <?= !empty($editing['featured']) ? 'checked' : '' ?>> Featured (hero carousel)</label>
          <button class="btn-studio primary" type="submit" style="width:100%;justify-content:center;margin-top:12px">Save post</button>
        </div></div>

        <div class="section-card"><h2>Details</h2><div class="card-body">
          <div class="field"><div class="field-label">Slug (URL)</div>
            <input name="slug" class="txt" required value="<?= htmlspecialchars($editing['slug'] ?? '', ENT_QUOTES) ?>"></div>
          <div class="field"><div class="field-label">Category</div>
            <input name="category" class="txt" list="catList" placeholder="Pick or type a new one" value="<?= htmlspecialchars($editing['category'] ?? '', ENT_QUOTES) ?>">
            <datalist id="catList"><?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c, ENT_QUOTES) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="field"><div class="field-label">Reading time</div>
            <div class="readmin-row">
              <input name="readMin" id="readMin" type="number" min="1" class="txt" style="width:90px" value="<?= htmlspecialchars($editing['readMin'] ?? '', ENT_QUOTES) ?>">
              <span class="readmin-note">min · <span id="wordCount">0</span> words <button type="button" class="btn-studio btn-mini" id="autoRead">Auto</button></span>
            </div>
          </div>
        </div></div>

        <div class="section-card"><h2>Cover image</h2><div class="card-body">
          <div class="cover-prev<?= empty($editing['cover'])?' empty':'' ?>" id="coverPrev">
            <?php if (!empty($editing['cover'])): ?><img src="<?= htmlspecialchars($editing['cover']) ?>" alt=""><?php endif; ?>
          </div>
          <input name="cover" id="coverInput" class="txt" style="margin-top:10px" placeholder="/assets/img/… or https://…" value="<?= htmlspecialchars($editing['cover'] ?? '', ENT_QUOTES) ?>">
          <div class="form-row" style="margin-top:8px">
            <button type="button" class="btn-studio btn-mini" data-mediapick="#coverInput" data-preview="#coverPrev">Browse library…</button>
            <button type="button" class="btn-studio btn-mini" data-mediaupload="#coverInput" data-preview="#coverPrev">Upload new</button>
          </div>
        </div></div>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/_media_picker.php'; ?>
<script>
(function(){
  /* ---- language tabs ---- */
  var bar = document.getElementById('postLangBar');
  bar.addEventListener('click', function(e){
    var b = e.target.closest('[data-lang]'); if(!b) return;
    var lang = b.getAttribute('data-lang');
    bar.querySelectorAll('.lang-pill').forEach(function(x){ x.classList.toggle('on', x===b); });
    document.querySelectorAll('.lang-pane').forEach(function(p){ p.classList.toggle('on', p.getAttribute('data-lang')===lang); });
    recount();
  });

  /* ---- auto reading time from the visible body ---- */
  function activeBody(){
    var pane = document.querySelector('.lang-pane.on');
    return pane ? pane.querySelector('.post-body-ta') : document.querySelector('.post-body-ta');
  }
  function words(str){ str = str.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim(); return str ? str.split(' ').length : 0; }
  function recount(){
    var ta = activeBody(); var w = ta ? words(ta.value) : 0;
    document.getElementById('wordCount').textContent = w;
  }
  document.getElementById('autoRead').addEventListener('click', function(){
    var maxW = 0;
    document.querySelectorAll('.post-body-ta').forEach(function(ta){ var w=words(ta.value); if(w>maxW) maxW=w; });
    document.getElementById('readMin').value = Math.max(1, Math.ceil(maxW/200));
  });
  document.querySelectorAll('.post-body-ta').forEach(function(ta){ ta.addEventListener('input', recount); });
  recount();

  /* ---- status → schedule row ---- */
  var sel = document.getElementById('statusSel'), row = document.getElementById('schedRow');
  sel.addEventListener('change', function(){ row.style.display = sel.value==='scheduled' ? '' : 'none'; });

  /* ---- slug auto from EN title when empty (new posts) ---- */
  var slugEl = document.querySelector('input[name=slug]');
  var enTitle = document.querySelector('input[name=title_en]');
  if (slugEl && enTitle && !slugEl.value) {
    enTitle.addEventListener('input', function(){
      if (slugEl.dataset.touched) return;
      slugEl.value = enTitle.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    });
    slugEl.addEventListener('input', function(){ slugEl.dataset.touched='1'; });
  }
})();
</script>
<?php lt_admin_foot();
