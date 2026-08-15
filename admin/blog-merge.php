<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$msg = ''; $err = '';

/* ---- light language detector (hu / es / en) ---- */
function lt_detect_lang($text) {
    $t = ' ' . mb_strtolower(strip_tags((string)$text), 'UTF-8') . ' ';
    if (trim($t) === '') return 'en';
    $huChars = preg_match_all('/[őű]/u', $t);
    $esChars = preg_match_all('/[ñ¿¡]/u', $t);
    $hu = preg_match_all('/\b(és|hogy|nem|egy|meg|már|volt|szurkoló|szurkolók|csapat|magyar|köszön|minden|lehet)\b/u', $t);
    $es = preg_match_all('/\b(los|las|para|con|una|más|está|como|pero|también|por|del|aficionados|equipo|nuestro)\b/u', $t);
    $en = preg_match_all('/\b(the|and|with|for|this|that|from|have|were|their|fans|team|our)\b/u', $t);
    $hs = $huChars * 6 + $hu; $ess = $esChars * 6 + $es; $ens = $en;
    if ($hs > 0 && $hs >= $ess && $hs >= $ens) return 'hu';
    if ($ess > 0 && $ess >= $ens) return 'es';
    return 'en';
}
function lt_first_nonempty($m) { foreach (['en','hu','es'] as $l) { if (isset($m[$l]) && trim((string)$m[$l]) !== '') return (string)$m[$l]; } return ''; }
function lt_post_lang($p) { return lt_detect_lang(lt_first_nonempty($p['body'] ?? []) ?: lt_first_nonempty($p['title'] ?? [])); }

/* ---- merge a set of post slugs into one multilingual post ---- */
function lt_merge_posts($slugs) {
    $members = [];
    foreach ($slugs as $s) { $p = lt_post_by_slug($s); if ($p) $members[] = $p; }
    if (count($members) < 2) return [false, 'Pick at least two posts to merge.'];

    $title = ['en'=>'','hu'=>'','es'=>'']; $excerpt = $title; $body = $title;
    $primary = null;
    foreach ($members as $m) {
        $lang = lt_post_lang($m);
        if ($title[$lang] === '')   $title[$lang]   = lt_first_nonempty($m['title'] ?? []);
        if ($excerpt[$lang] === '') $excerpt[$lang] = lt_first_nonempty($m['excerpt'] ?? []);
        if ($body[$lang] === '')    $body[$lang]    = lt_first_nonempty($m['body'] ?? []);
        if ($lang === 'en' && !$primary) $primary = $m;
    }
    if (!$primary) $primary = $members[0];

    $max = 0; foreach (['en','hu','es'] as $l) { $w = str_word_count(trim(strip_tags($body[$l]))); if ($w > $max) $max = $w; }
    $merged = [
        'slug'      => $primary['slug'],
        'date'      => $primary['date'] ?? date('Y-m-d'),
        'status'    => 'published',
        'publishAt' => '',
        'published' => true,
        'featured'  => (bool)array_filter($members, fn($m)=>!empty($m['featured'])),
        'cover'     => $primary['cover'] ?? '',
        'category'  => $primary['category'] ?? '',
        'readMin'   => max(1, (int)ceil($max / 200)),
        'title'     => $title, 'excerpt' => $excerpt, 'body' => $body,
    ];
    lt_post_save($merged);
    foreach ($members as $m) if (($m['slug'] ?? '') !== $merged['slug']) lt_post_delete($m['slug']);
    return [true, $merged['slug']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    $slugs = array_values(array_filter(array_map('trim', explode(',', $_POST['slugs'] ?? ''))));
    if (($_POST['act'] ?? '') === 'merge' && $slugs) {
        list($ok, $info) = lt_merge_posts($slugs);
        if ($ok) { lt_audit('post.merge', implode('+', $slugs) . '=>' . $info); $msg = 'Merged ' . count($slugs) . ' posts into “' . $info . '”.'; }
        else $err = $info;
    }
}

$posts = lt_posts_load_all();
foreach ($posts as &$pp) { $pp['_lang'] = lt_post_lang($pp); $pp['_langs'] = array_values(array_filter(['en','hu','es'], fn($l)=>trim(lt_first_nonempty([$pp['title'][$l] ?? '']))!=='' || trim((string)($pp['body'][$l] ?? ''))!=='')); }
unset($pp);

/* group candidate translation sets by cover image */
$byCover = [];
foreach ($posts as $p) { $key = trim($p['cover'] ?? ''); if ($key === '') continue; $byCover[$key][] = $p; }
$groups = array_filter($byCover, fn($g) => count($g) > 1);

lt_admin_head('Merge translations');
lt_admin_sidebar('blog');
lt_admin_top('Vent Studio', 'Merge translations', '<a class="btn-studio" href="posts.php">← All posts</a>');
function lgbadge($l){ $m=['en'=>'EN','hu'=>'HU','es'=>'ES']; return '<span class="badge off">'.($m[$l]??$l).'</span>'; }
?>
<div class="admin-body">
  <?php if ($msg): ?><div class="notice" style="border-left-color:#177a48"><strong><?= htmlspecialchars($msg) ?></strong></div><?php endif; ?>
  <?php if ($err): ?><div class="notice" style="border-left-color:#b3261e"><strong><?= htmlspecialchars($err) ?></strong></div><?php endif; ?>
  <div class="st-hint">Posts that were imported once per language show up as duplicates. Below, posts sharing the same cover image are grouped as likely translation sets — review the detected language and click <strong>Merge</strong> to combine them into one multilingual post (the English slug is kept). You can also hand-pick posts in the full list and merge them.</div>

  <div class="section-card"><h2>Suggested translation sets <span style="color:#999;font-weight:400;font-size:14px"><?= count($groups) ?></span></h2><div class="card-body">
    <?php if (!$groups): ?><p style="color:var(--gray)">No obvious duplicates found (no two posts share a cover image).</p><?php endif; ?>
    <?php foreach ($groups as $cover => $set): $slugs = array_map(fn($p)=>$p['slug'], $set); ?>
      <div class="merge-set">
        <table class="data-table" style="margin:0 0 10px">
          <?php foreach ($set as $p): ?>
          <tr>
            <td style="width:70px"><img src="<?= htmlspecialchars((str_starts_with($p['cover']??'','http')||str_starts_with($p['cover']??'','/'))?$p['cover']:'/assets/img/'.($p['cover']??'')) ?>" style="width:56px;height:36px;object-fit:cover;border-radius:6px"></td>
            <td><strong><?= htmlspecialchars(lt_first_nonempty($p['title'] ?? []) ?: $p['slug']) ?></strong><br><span style="color:#888;font-size:12px">/<?= htmlspecialchars($p['slug']) ?></span></td>
            <td>detected: <?= lgbadge($p['_lang']) ?></td>
            <td><?= htmlspecialchars($p['date'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
        <form method="post" onsubmit="return confirm('Merge these <?= count($set) ?> posts into one multilingual post?')">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="merge">
          <input type="hidden" name="slugs" value="<?= htmlspecialchars(implode(',', $slugs), ENT_QUOTES) ?>">
          <button class="btn-studio primary btn-mini" type="submit">Merge these <?= count($set) ?> →</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div></div>

  <div class="section-card"><h2>All posts — hand-pick to merge</h2><div class="card-body">
    <form method="post" onsubmit="return this.slugs.value && confirm('Merge the selected posts into one?')">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="merge">
      <input type="hidden" name="slugs" id="pickSlugs" value="">
      <table class="data-table">
        <tr><th style="width:40px"></th><th>Title</th><th>Slug</th><th>Detected</th><th>Has languages</th><th>Date</th></tr>
        <?php foreach ($posts as $p): ?>
        <tr>
          <td><input type="checkbox" class="pick" value="<?= htmlspecialchars($p['slug'], ENT_QUOTES) ?>"></td>
          <td><?= htmlspecialchars(lt_first_nonempty($p['title'] ?? []) ?: $p['slug']) ?></td>
          <td style="color:#888;font-size:12px">/<?= htmlspecialchars($p['slug']) ?></td>
          <td><?= lgbadge($p['_lang']) ?></td>
          <td><?php foreach ($p['_langs'] as $l) echo lgbadge($l).' '; ?></td>
          <td><?= htmlspecialchars($p['date'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <button class="btn-studio primary" type="submit" style="margin-top:12px">Merge selected →</button>
    </form>
  </div></div>
</div>
<script>
(function(){
  var boxes=document.querySelectorAll('.pick'), field=document.getElementById('pickSlugs');
  function sync(){ field.value=[].slice.call(boxes).filter(function(b){return b.checked;}).map(function(b){return b.value;}).join(','); }
  boxes.forEach(function(b){ b.addEventListener('change', sync); });
})();
</script>
<?php lt_admin_foot();
