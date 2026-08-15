<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$editing = isset($_GET['slug']) ? lt_page_by_slug($_GET['slug']) : null;
$page = $editing ?: ['slug'=>'', 'title'=>['en'=>'','hu'=>'','es'=>''], 'metaDesc'=>['en'=>'','hu'=>'','es'=>''], 'published'=>false, 'blocks'=>[]];

lt_admin_head($editing ? 'Edit page' : 'New page');
lt_admin_sidebar('builder');
lt_admin_top('Vent Studio', $editing ? 'Edit page' : 'New page',
    '<a class="btn-studio" href="builder.php">← All pages</a>'
    . '<span class="lang-mini" id="bLang"></span>'
    . '<button class="btn-studio" id="bPreview" type="button">Preview</button>'
    . '<button class="btn-studio primary" id="bSave" type="button">Save page</button>');
?>
<div class="builder">
  <aside class="bx-palette" id="bxPalette">
    <div class="bx-p-title">Add block</div>
    <!-- populated by builder.js -->
  </aside>

  <main class="bx-canvas-wrap">
    <div class="bx-meta">
      <input id="bTitle" class="txt" placeholder="Page title (current language)" style="flex:1;min-width:200px">
      <span class="bx-slug">/<span id="bLangTag">en</span>/</span>
      <input id="bSlug" class="txt" placeholder="url-slug" value="<?= htmlspecialchars($page['slug'], ENT_QUOTES) ?>" style="width:200px">
      <label class="switch-row" style="margin:0"><span style="font-size:12.5px">Published</span><label class="switch"><input type="checkbox" id="bPub" <?= !empty($page['published'])?'checked':'' ?>><span class="switch-slider"></span></label></label>
    </div>
    <div class="bx-canvas" id="bxCanvas"></div>
  </main>

  <aside class="bx-inspector" id="bxInspector">
    <div class="bx-i-empty">Select a block to edit its settings, or add one from the left.</div>
  </aside>
</div>

<div class="toast" id="toast"></div>
<?php require __DIR__ . '/_media_picker.php'; ?>
<script>
window.LT_PAGE = <?= json_encode($page, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.LT_CSRF = <?= json_encode($csrf) ?>;
</script>
<script src="/assets/js/builder.js"></script>
<?php lt_admin_foot();
