<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$pages = lt_pages_load_all();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'delete') {
    if (!lt_check_csrf($_POST['csrf'] ?? '')) { http_response_code(403); exit('Bad CSRF'); }
    lt_page_delete($_POST['slug'] ?? ''); lt_audit('page.delete', $_POST['slug'] ?? '');
    header('Location: builder.php'); exit;
}

lt_admin_head('Page builder');
lt_admin_sidebar('builder');
lt_admin_top('Vent Studio', 'Page builder', '<a class="btn-studio primary" href="builder-edit.php">+ New page</a>');
?>
<div class="admin-body">
  <div class="st-hint">Build free-form pages with drag &amp; drop blocks — headings, text, images, YouTube, buttons, columns and spacers. Published pages are live at <code>/&lt;lang&gt;/your-slug</code>.</div>
  <table class="data-table">
    <tr><th>Title</th><th>URL</th><th>Blocks</th><th>Status</th><th style="width:220px">Actions</th></tr>
    <?php if (!$pages): ?><tr><td colspan="5" style="color:var(--gray)">No custom pages yet. Create your first one.</td></tr><?php endif; ?>
    <?php foreach ($pages as $p): ?>
    <tr>
      <td><strong><?= htmlspecialchars($p['title']['en'] ?? $p['slug']) ?></strong></td>
      <td><span style="color:#888;font-size:12px">/&lt;lang&gt;/<?= htmlspecialchars($p['slug']) ?></span></td>
      <td><?= count($p['blocks'] ?? []) ?></td>
      <td><span class="badge <?= !empty($p['published'])?'on':'off' ?>"><?= !empty($p['published'])?'Published':'Draft' ?></span></td>
      <td>
        <a class="btn-studio btn-mini" href="builder-edit.php?slug=<?= urlencode($p['slug']) ?>">Edit</a>
        <a class="btn-studio btn-mini" href="/en/<?= urlencode($p['slug']) ?>" target="_blank">View</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this page?')">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="act" value="delete"><input type="hidden" name="slug" value="<?= htmlspecialchars($p['slug'], ENT_QUOTES) ?>">
          <button class="btn-studio btn-mini btn-danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php lt_admin_foot();
