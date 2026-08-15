<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/inc/emails.php';
lt_require_login();

$templates = lt_email_templates();
$saved = isset($_GET['saved']);
$csrf = lt_csrf();
$placeholders = ['{{number}}','{{name}}','{{phone}}','{{items_table}}','{{fulfilment_block}}','{{address_oneline}}','{{postcode}}','{{map_button}}','{{time}}','{{notes_block}}','{{invoice_button}}','{{payment}}'];

lt_admin_head('Email templates');
lt_admin_sidebar('emails');
lt_admin_top('Ordering', 'Email templates', '<button form="emForm" class="btn-studio primary">Save templates</button>');
?>
<style>
.em-wrap{padding:22px 34px;max-width:900px}
.em-card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px;margin-bottom:18px;box-shadow:var(--shadow)}
.em-card h3{margin:0 0 4px;font-family:var(--display)}
.em-card .em-sub{color:var(--gray);font-size:12.5px;margin-bottom:12px}
.em-card label{display:block;font-weight:700;margin:10px 0 6px;font-size:13px}
.em-card input{width:100%;border:1.5px solid var(--line-2);border-radius:9px;padding:10px 12px;font-family:var(--sans)}
.em-ph{background:#fbf6ec;border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:12.5px;color:var(--gray)}
.em-ph code{background:#efe2cc;border-radius:5px;padding:1px 6px;font-size:12px;color:#1B1512;margin:0 2px;display:inline-block}
.em-saved{background:#e8f7ef;border:1px solid #b9e6cd;color:#1B7A46;border-radius:10px;padding:10px 14px;margin:0 34px 4px}
</style>
<?php if ($saved): ?><div class="em-saved">Email templates saved.</div><?php endif; ?>
<div class="em-wrap">
  <div class="em-ph"><strong>Placeholders</strong> (auto-filled per order): <?php foreach ($placeholders as $p): ?><code><?= htmlspecialchars($p) ?></code><?php endforeach; ?><br>
  Every email is wrapped in the branded VentStudio layout automatically — just edit the message inside.</div>
  <form id="emForm" method="post" action="emails-save.php">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <?php foreach ($templates as $key => $t): ?>
      <div class="em-card">
        <h3><?= htmlspecialchars($t['label'] ?? $key) ?></h3>
        <div class="em-sub"><?= htmlspecialchars($key) ?></div>
        <label>Subject line</label>
        <input name="subject[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($t['subject'] ?? '') ?>">
        <label>Message body</label>
        <textarea name="body[<?= htmlspecialchars($key) ?>]" data-wysiwyg rows="6"><?= htmlspecialchars($t['body'] ?? '') ?></textarea>
      </div>
    <?php endforeach; ?>
  </form>
</div>
<?php lt_admin_foot(); ?>
