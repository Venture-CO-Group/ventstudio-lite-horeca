<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_login();
$csrf = lt_csrf();
$posts = lt_posts_load_all();
usort($posts, fn($a,$b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
lt_admin_head('Blog');
lt_admin_sidebar('blog');
lt_admin_top('Vent Studio', 'Blog — all posts',
    '<a class="btn-studio" href="blog-merge.php">Merge translations</a><a class="btn-studio" href="blog-translate.php">Translate</a><a class="btn-studio primary" href="post-edit.php">+ New post</a>');
?>
<div class="admin-body">
  <?php if (!$posts): ?>
    <div class="notice"><strong>No posts yet.</strong> Create your first post, or import the archive under <a href="maintenance.php">Maintenance</a>.</div>
  <?php endif; ?>
  <table class="data-table">
    <tr><th>Title</th><th>Date</th><th>Category</th><th>Status</th><th style="width:180px">Actions</th></tr>
    <?php foreach ($posts as $p): ?>
    <tr>
      <td><strong><?= htmlspecialchars($p['title']['en'] ?? $p['slug']) ?></strong><br><span style="color:#888;font-size:12px">/<?= htmlspecialchars($p['slug']) ?></span></td>
      <td><?= htmlspecialchars($p['date'] ?? '') ?></td>
      <td><?= htmlspecialchars($p['category'] ?? '') ?></td>
      <td><?php
        $st = $p['status'] ?? (!empty($p['published']) ? 'published' : 'draft');
        $map = ['published'=>['on','Published'], 'scheduled'=>['sched','Scheduled'], 'draft'=>['off','Draft']];
        [$cls,$lbl] = $map[$st] ?? ['off','Draft'];
        if ($st === 'scheduled' && !empty($p['publishAt'])) $lbl .= ' · ' . date('M j, H:i', strtotime($p['publishAt']));
      ?><span class="badge <?= $cls ?>"><?= htmlspecialchars($lbl) ?></span><?= !empty($p['featured']) ? ' <span class="badge feat">Featured</span>' : '' ?></td>
      <td>
        <a class="btn-studio btn-mini" href="post-edit.php?slug=<?= urlencode($p['slug']) ?>">Edit</a>
        <a class="btn-studio btn-mini" href="/en/blog/<?= urlencode($p['slug']) ?>" target="_blank">View</a>
        <form method="post" action="blog-save.php" style="display:inline" onsubmit="return confirm('Delete this post?')">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="delete" value="<?= htmlspecialchars($p['slug'], ENT_QUOTES) ?>">
          <button class="btn-studio btn-mini btn-danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php lt_admin_foot();
