<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/inc/store.php';
lt_require_login();

$C = lt_content_load();
$s = $C['settings'] ?? [];
$hours = $s['hours'] ?? [];
$days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
$csrf = lt_csrf();
$saved = isset($_GET['saved']);
lt_admin_head('Opening hours');
lt_admin_sidebar('hours');
lt_admin_top('Ordering', 'Opening hours', '<button form="hoursForm" class="btn-studio primary">Save hours</button>');
?>
<style>
.hr-wrap{padding:22px 34px;max-width:640px}
.hr-card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px;box-shadow:var(--shadow);margin-bottom:16px}
.hr-row{display:grid;grid-template-columns:110px 70px 1fr 20px 1fr;gap:10px;align-items:center;margin-bottom:8px}
.hr-row .day{font-weight:700}
.hr-row input[type=time]{border:1.5px solid var(--line-2);border-radius:9px;padding:8px 10px;width:100%}
.hr-row .sep{text-align:center;color:var(--gray)}
.hr-row.closed input[type=time]{opacity:.4}
.hr-field{margin:14px 0}
.hr-field label{display:block;font-weight:700;margin-bottom:6px}
.hr-field input{border:1.5px solid var(--line-2);border-radius:9px;padding:9px 12px}
.hr-saved{background:#e8f7ef;border:1px solid #b9e6cd;color:#1B7A46;border-radius:10px;padding:10px 14px;margin:0 34px 6px}
.hr-hint{color:var(--gray);font-size:13px;margin-top:4px}
</style>
<?php if ($saved): ?><div class="hr-saved">Opening hours saved — the checkout calendar updates instantly.</div><?php endif; ?>
<?php if (($_GET['opensaved'] ?? '') === 'open'): ?><div class="hr-saved">You're now <strong>OPEN</strong> for orders — the homepage badge is live.</div>
<?php elseif (($_GET['opensaved'] ?? '') === 'closed'): ?><div class="hr-saved" style="background:#fdecea;border-color:#f3b4ab;color:#9b2c2c">You're now <strong>CLOSED</strong> — customers can only pre-order BBQ specials.</div>
<?php elseif (($_GET['opensaved'] ?? '') === 'err'): ?><div class="hr-saved" style="background:#fdecea;border-color:#f3b4ab;color:#9b2c2c">Couldn't save the status — please try again.</div><?php endif; ?>
<div class="hr-wrap">
  <?php $isOpen = !array_key_exists('orderingOpen',$s) || !empty($s['orderingOpen']); ?>
  <form method="post" action="open-save.php" class="hr-card" style="background:<?= $isOpen?'#e8f7ef':'#fdecea' ?>;border:1.5px solid <?= $isOpen?'#8fd4ab':'#f0a99e' ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <h3 style="margin-top:0;font-family:var(--display);display:flex;align-items:center;gap:10px">
      <span style="width:12px;height:12px;border-radius:50%;background:<?= $isOpen?'#2FA86B':'#E8431F' ?>;display:inline-block"></span>
      Ordering is currently <?= $isOpen ? 'OPEN' : 'CLOSED' ?>
    </h3>
    <label style="display:flex;align-items:center;gap:10px;margin:6px 0 2px;font-size:16px;font-weight:700">
      <input type="checkbox" name="orderingOpen" value="1" <?= $isOpen?'checked':'' ?> style="width:18px;height:18px">
      We're open for orders right now
    </label>
    <p class="hr-hint" style="margin:8px 0 14px">Turn this off when the van is closed. While it's off, customers can't place normal orders — but <strong>pre-order BBQ specials still work</strong>. The homepage shows an Open / Closed badge to match.</p>
    <button class="btn-studio primary" type="submit">Save status</button>
  </form>
</div>
<div class="hr-wrap">
  <form id="hoursForm" method="post" action="hours-save.php">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="hr-card">
      <h3 style="margin-top:0;font-family:var(--display)">Weekly ordering hours</h3>
      <p class="hr-hint" style="margin-top:0">Untick a day to close it. Customers can only pick open days and time slots within these hours.</p>
      <?php foreach ($days as $k=>$label): $d = $hours[$k] ?? ['open'=>true,'from'=>'12:00','to'=>'20:00']; ?>
        <div class="hr-row <?= empty($d['open'])?'closed':'' ?>">
          <label class="day"><input type="checkbox" name="open[<?= $k ?>]" value="1" <?= !empty($d['open'])?'checked':'' ?> onchange="this.closest('.hr-row').classList.toggle('closed',!this.checked)"> <?= $label ?></label>
          <span></span>
          <input type="time" name="from[<?= $k ?>]" value="<?= htmlspecialchars($d['from'] ?? '12:00') ?>">
          <span class="sep">to</span>
          <input type="time" name="to[<?= $k ?>]" value="<?= htmlspecialchars($d['to'] ?? '20:00') ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="hr-card">
      <h3 style="margin-top:0;font-family:var(--display)">Availability</h3>
      <div class="hr-field">
        <label>Orders open from</label>
        <input type="date" name="ordersOpenFrom" value="<?= htmlspecialchars($s['ordersOpenFrom'] ?? '') ?>">
        <p class="hr-hint">The earliest date customers can order for. Set it to your launch/first trading day (leave empty for "from today"). Currently the first orderable day is this date or later, on an open weekday.</p>
      </div>
      <div class="hr-field">
        <label>Time-slot length (minutes)</label>
        <input type="number" name="slotMinutes" min="10" max="60" step="5" value="<?= (int)($s['slotMinutes'] ?? 30) ?>" style="width:120px">
      </div>
    </div>
  </form>
</div>
<?php lt_admin_foot(); ?>
