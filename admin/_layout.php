<?php
/* Shared Vent Studio admin chrome. */
function lt_admin_nav_items() {
    return [
        'Overview' => [
            ['index.php',        'Dashboard',    'dashboard',    'M4 4h5v5H4zM11 4h5v5h-5zM4 11h5v5H4zM11 11h5v5h-5z'],
        ],
        'Ordering' => [
            ['menu.php',         'Menu',         'menu',         'M5 3h10M5 3c0 3 2 4 2 6s-2 2-2 5M15 3c0 3-2 4-2 6s2 2 2 5M4 17h12'],
            ['orders.php',       'Orders',       'orders',       'M6 6h13l-1.5 8h-11zM6 6L5 3H2M8 18h.01M15 18h.01'],
            ['hours.php',        'Opening hours','hours',        'M10 5v5l3 2M10 18a8 8 0 110-16 8 8 0 010 16z'],
            ['emails.php',       'Email templates','emails',     'M3 6l7 5 7-5M3 6h14v9H3zM3 6v9'],
            ['invoices.php',     'Invoices',      'invoices',    'M5 3h10v14l-2-1.5L11 17l-2-1.5L7 17l-2-1.5zM7 7h6M7 10h6'],
        ],
        'Content' => [
            ['pages.php',        'Pages',        'pages',        'M4 5.5A1.5 1.5 0 015.5 4h9A1.5 1.5 0 0116 5.5v11a1.5 1.5 0 01-1.5 1.5h-9A1.5 1.5 0 014 16.5v-11zM7 8h6M7 11h6M7 14h4'],
            ['builder.php',      'Page builder', 'builder',      'M3 4h6v6H3zM11 4h6v3h-6zM11 9h6v7h-6zM3 12h6v4H3z'],
            ['posts.php',        'Blog',         'blog',         'M5 4h10a1 1 0 011 1v12l-3-2-3 2-3-2-3 2V5a1 1 0 011-1zM8 8h6M8 11h4'],
            ['gallery.php',      'Gallery',      'gallery',      'M4 5h12a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1zM7 9a1.2 1.2 0 100-2.4A1.2 1.2 0 007 9zm-3 6l4-4 3 3 2-2 4 3'],
            ['media.php',        'Media',        'media',        'M3 6a2 2 0 012-2h4l2 2h5a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V6z'],
        ],
        'Configuration' => [
            ['settings.php',    'Settings',    'settings',    'M10 6.5A3.5 3.5 0 1010 13.5 3.5 3.5 0 0010 6.5zM10 2v2M10 16v2M2 10h2M16 10h2M4.3 4.3l1.4 1.4M14.3 14.3l1.4 1.4M15.7 4.3l-1.4 1.4M5.7 14.3l-1.4 1.4'],
            ['users.php',       'Users',       'users',       'M7 9a3 3 0 100-6 3 3 0 000 6zM2 17c0-2.8 2.2-5 5-5s5 2.2 5 5M13 9.5a2.5 2.5 0 100-5M14.5 12.2c2 .5 3.5 2.3 3.5 4.8'],
            ['maintenance.php', 'Maintenance', 'maintenance', 'M13.5 6.5l-7 7M6 4l1 2.5L4.5 8 2 7l1-3 3 0zM14 12l2.5 1 1 3-3 0-1-2.5 0.5-1.5z'],
            ['log.php',         'Change log',  'log',         'M10 5v5l3 2M10 18a8 8 0 110-16 8 8 0 010 16z'],
        ],
        'Help' => [
            ['docs.php',        'Help & docs', 'docs',        'M10 18a8 8 0 110-16 8 8 0 010 16zM8 8a2 2 0 113 1.7c-.6.4-1 .8-1 1.6M10 14h.01'],
        ],
    ];
}
function lt_admin_head($title) {
    ?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title) ?> — VentStudio · Vent Studio</title>
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/brand/favicon-32.png">
<link rel="stylesheet" href="/assets/css/admin.css">
</head><body><div class="studio"><?php
}
function lt_admin_sidebar($active) {
    ?><aside class="sb">
      <div class="sb-brand"><a href="index.php" aria-label="Dashboard"><img src="/assets/img/brand/logo.svg" alt="VentStudio" class="sb-logo"></a></div>
      <nav class="sb-nav"><?php
        foreach (lt_admin_nav_items() as $group => $items) {
            echo '<div class="sb-group">' . htmlspecialchars($group) . '</div>';
            foreach ($items as $it) {
                $cls = $it[2] === $active ? ' active' : '';
                echo '<a class="sb-item' . $cls . '" href="' . $it[0] . '">'
                   . '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="' . $it[3] . '"/></svg>'
                   . '<span>' . htmlspecialchars($it[1]) . '</span></a>';
            }
        }
      ?></nav>
      <div class="sb-foot">
        <span class="sb-powered">Powered by <strong>Example Company</strong></span>
      </div>
    </aside><main class="st-main"><?php
}
function lt_admin_top($eyebrow, $title, $actionsHtml = '') {
    $email = lt_admin_email();
    $admins = function_exists('lt_admins_load') ? lt_admins_load() : [];
    $me = $admins[$email] ?? ['name' => $email, 'role' => 'admin'];
    $name = $me['name'] ?: $email;
    $parts = preg_split('/\s+/', trim($name));
    $ini = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? (strlen($parts[0] ?? '') > 1 ? substr($parts[0], 1, 1) : ''), 0, 1));
    ?><div class="st-top">
      <div><div class="st-eyebrow"><?= htmlspecialchars($eyebrow) ?></div><h1><?= htmlspecialchars($title) ?></h1></div>
      <div class="st-actions"><?= $actionsHtml ?>
        <div class="me" id="meMenu">
          <button class="me-trigger" type="button" title="<?= htmlspecialchars($name) ?>">
            <span class="me-name"><?= htmlspecialchars($name) ?></span>
            <?php if (!empty($me['avatar'])): ?>
              <span class="me-avatar has-img"><img src="<?= htmlspecialchars($me['avatar']) ?>" alt=""></span>
            <?php else: ?>
              <span class="me-avatar"><?= htmlspecialchars($ini) ?></span>
            <?php endif; ?>
          </button>
          <div class="me-dd">
            <div class="me-info"><strong><?= htmlspecialchars($name) ?></strong><small><?= htmlspecialchars($email) ?></small><span class="badge on"><?= htmlspecialchars($me['role'] ?? 'admin') ?></span></div>
            <a href="account.php">My account</a>
            <a href="logout.php" class="me-out">Sign out</a>
          </div>
        </div>
      </div>
    </div>
    <script>
    document.addEventListener('click', function(e){
      var m = document.getElementById('meMenu'); if (!m) return;
      if (m.contains(e.target) && e.target.closest('.me-trigger')) m.classList.toggle('open');
      else if (!m.contains(e.target)) m.classList.remove('open');
    });
    </script><?php
}
function lt_admin_foot() { ?></main></div><script src="/assets/js/wysiwyg.js"></script><script src="/assets/js/tour.js"></script></body></html><?php }
