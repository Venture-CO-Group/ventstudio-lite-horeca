<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/inc/orders.php';
lt_require_login();

$C = lt_content_load();
$sym = $C['settings']['currencySymbol'] ?? '£';
$orders = lt_orders_all();

$today = date('Y-m-d');
$rev = 0; $revToday = 0; $todayN = 0; $itemQty = [];
$status = ['received'=>0,'approved'=>0,'preparing'=>0,'out_for_delivery'=>0,'delivered'=>0,'cancelled'=>0];
$delivery = 0; $collection = 0; $paidN = 0;
foreach ($orders as $o) {
    $st = $o['status'] ?? 'received';
    if (isset($status[$st])) $status[$st]++;
    $isCancel = $st === 'cancelled';
    if (!$isCancel) $rev += (int)($o['total'] ?? 0);
    if (!empty($o['paid'])) $paidN++;
    $od = substr($o['created'] ?? '', 0, 10);
    if ($od === $today) { $todayN++; if (!$isCancel) $revToday += (int)($o['total'] ?? 0); }
    if (($o['fulfilment'] ?? '') === 'collection') $collection++; else $delivery++;
    foreach ((array)($o['items'] ?? []) as $it) { $k = $it['name']; $itemQty[$k] = ($itemQty[$k] ?? 0) + (int)$it['qty']; }
}
$n = count($orders);
$avg = $n ? $rev / $n : 0;
arsort($itemQty);
$top = array_slice($itemQty, 0, 6, true);
$maxStatus = max(1, max($status));
$badge = ['received'=>'#2D6CB0','approved'=>'#1B7A46','preparing'=>'#B8860B','out_for_delivery'=>'#E8431F','delivered'=>'#6B5F54','cancelled'=>'#9b2c2c'];
function m2($p,$s){ return $s . number_format($p/100, 2); }

lt_admin_head('Dashboard');
lt_admin_sidebar('dashboard');
lt_admin_top('Vent Studio', 'Dashboard',
    '<a class="btn-studio" href="/" target="_blank">View website &nearr;</a><a class="btn-studio primary" href="orders.php">View orders</a>');
?>
<div class="admin-body">
  <div class="dash-kpis">
    <div class="kpi"><span class="kpi-l">Orders (all time)</span><span class="kpi-v"><?= $n ?></span><span class="kpi-s"><?= $paidN ?> paid online</span></div>
    <div class="kpi"><span class="kpi-l">Revenue</span><span class="kpi-v"><?= m2($rev,$sym) ?></span><span class="kpi-s">excl. cancelled</span></div>
    <div class="kpi"><span class="kpi-l">Orders today</span><span class="kpi-v"><?= $todayN ?></span><span class="kpi-s"><?= m2($revToday,$sym) ?> today</span></div>
    <div class="kpi"><span class="kpi-l">Avg order value</span><span class="kpi-v"><?= m2((int)$avg,$sym) ?></span><span class="kpi-s"><?= $delivery ?> delivery · <?= $collection ?> collection</span></div>
  </div>

  <div class="dash-grid">
    <div class="section-card"><h2>Recent orders</h2><div class="card-body">
      <?php if (!$orders): ?>
        <p style="color:var(--gray)">No orders yet. When customers order online they'll appear here.</p>
      <?php else: foreach (array_slice($orders, 0, 8) as $o): $st=$o['status']??'received'; ?>
        <a class="dash-row" href="orders.php">
          <span class="dash-row-main"><?= htmlspecialchars($o['number']) ?> · <?= htmlspecialchars($o['customer']['name'] ?? '') ?></span>
          <span class="dash-row-meta"><?= m2((int)($o['total']??0),$sym) ?> · <span class="badge" style="background:<?= $badge[$st]??'#888' ?>;color:#fff"><?= htmlspecialchars(str_replace('_',' ',$st)) ?></span></span>
        </a>
      <?php endforeach; endif; ?>
      <div style="margin-top:12px"><a class="btn-studio btn-mini" href="orders.php">All orders</a></div>
    </div></div>

    <div class="section-card"><h2>Orders by status</h2><div class="card-body">
      <?php foreach ($status as $k=>$v): ?>
        <div class="dash-bar-row"><span class="dash-bar-lbl"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$k))) ?></span>
          <span class="dash-bar"><span style="width:<?= round($v/$maxStatus*100) ?>%;background:<?= $badge[$k] ?>"></span></span>
          <span class="dash-bar-n"><?= $v ?></span></div>
      <?php endforeach; ?>
    </div></div>

    <div class="section-card"><h2>Top sellers</h2><div class="card-body">
      <?php if (!$top): ?><p style="color:var(--gray)">No sales yet.</p>
      <?php else: foreach ($top as $name=>$q): ?>
        <div class="dash-row"><span class="dash-row-main"><?= htmlspecialchars($name) ?></span><span class="dash-row-meta"><strong><?= $q ?></strong> sold</span></div>
      <?php endforeach; endif; ?>
    </div></div>

    <div class="section-card"><h2>Setup checklist</h2><div class="card-body">
      <?php
        $pp = trim((string)getenv('LT_STRIPE_SECRET'));
        $smtp = trim((string)getenv('LT_SMTP_HOST'));
        $rows = [
          ['Stripe payments', $pp !== ''],
          ['Order emails (SMTP)', $smtp !== ''],
          ['Menu published', !empty($C['menu']['groups'])],
        ];
        foreach ($rows as $r): ?>
        <div class="dash-row"><span class="dash-row-main"><?= htmlspecialchars($r[0]) ?></span><span class="dash-row-meta"><span class="badge <?= $r[1]?'on':'off' ?>"><?= $r[1]?'ready':'set up' ?></span></span></div>
      <?php endforeach; ?>
      <div style="margin-top:12px"><a class="btn-studio btn-mini" href="menu.php">Edit menu</a> <a class="btn-studio btn-mini" href="emails.php">Email templates</a></div>
    </div></div>
  </div>
</div>
<style>
.dash-bar-row{display:flex;align-items:center;gap:10px;margin-bottom:9px;font-size:13px}
.dash-bar-lbl{width:120px;color:var(--gray)}
.dash-bar{flex:1;height:10px;background:#efe7d8;border-radius:6px;overflow:hidden}
.dash-bar span{display:block;height:100%;border-radius:6px}
.dash-bar-n{width:26px;text-align:right;font-weight:700}
</style>
<?php lt_admin_foot();
