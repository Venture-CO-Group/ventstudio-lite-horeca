<?php
/* Shared "section editor" page: renders the JSON editor limited to $groups. */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';

function lt_admin_editor($active, $title, $groups, $hint = '') {
    lt_require_login();
    $content = lt_content_load();
    $csrf = lt_csrf();
    lt_admin_head($title);
    lt_admin_sidebar($active);
    $actions = '<button class="btn-studio" id="btnDiscard" type="button">Discard</button>'
             . '<button class="btn-studio primary" id="btnSave" type="button">Save changes</button>';
    lt_admin_top('Vent Studio', $title, $actions);
    if (trim($hint) !== '') echo '<div class="st-hint">' . $hint . '</div>';
    ?>
<div id="panels"></div>
<div class="toast" id="toast"></div>
<script>
window.LT = {
  content: <?= json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  csrf: <?= json_encode($csrf) ?>,
  groups: <?= json_encode($groups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
};
</script>
<script src="/assets/js/admin.js"></script>
    <?php
    lt_admin_foot();
}
