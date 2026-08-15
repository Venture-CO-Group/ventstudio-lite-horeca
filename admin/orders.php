<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/inc/orders.php';
require_once dirname(__DIR__) . '/inc/emails.php';
require_once dirname(__DIR__) . '/inc/stripe.php';
lt_require_login();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && lt_check_csrf($_POST['csrf'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $st = preg_replace('/[^a-z_]/', '', (string)($_POST['status'] ?? ''));
    $order = $id ? lt_order_get($id) : null;
    if ($order && $st) {
        $order = lt_order_update($id, ['status' => $st]) ?: $order;
        lt_audit('order-status', "#$id -> $st");
        // fire the matching notification
        if ($st === 'approved')          { lt_email_on_approved($order); $flash = "Order {$order['number']} approved — customer emailed."; }
        elseif ($st === 'out_for_delivery') { lt_email_on_dispatch($order); $flash = "Courier notified for {$order['number']} (map sent)."; }
        elseif ($st === 'ready')         { lt_email_on_ready($order); $flash = "Order {$order['number']} — customer emailed it's ready to collect."; }
        elseif ($st === 'delivered')     { lt_email_on_delivered($order); $flash = "Order {$order['number']} marked delivered — customer emailed."; }
        elseif ($st === 'cancelled') {
            $patch = ['credit_number' => 'CN-' . substr((string)$order['number'], 3)];
            if (empty($order['refunded']) && ($order['payment'] ?? '') === 'stripe' && !empty($order['paid']) && !empty($order['stripe_session'])) {
                [$rok, $rid, $rerr] = lt_stripe_refund($order['stripe_session']);
                if ($rok) { $patch['refunded'] = true; $patch['refund_id'] = $rid; }
                else { $flash = "Order {$order['number']} cancelled, but Stripe refund failed ($rerr) — refund manually in Stripe. Credit note ready."; }
            }
            $order = lt_order_update($id, $patch) ?: $order;
            lt_audit('order-cancel', "#$id" . (!empty($order['refunded']) ? ' refunded ' . ($order['refund_id'] ?? '') : ''));
            if ($flash === '') $flash = "Order {$order['number']} cancelled" . (!empty($order['refunded']) ? " and refunded via Stripe" : "") . ". Credit note ready.";
        }
        else { $flash = "Order {$order['number']} → " . str_replace('_',' ', $st); }
    }
    header('Location: orders.php?msg=' . rawurlencode($flash)); exit;
}
$flash = $_GET['msg'] ?? '';

$C = lt_content_load();
$sym = $C['settings']['currencySymbol'] ?? '£';
$orders = lt_orders_all();
$csrf = lt_csrf();
$badge = [
  'received'=>['Received','#2D6CB0'],'approved'=>['Approved','#1B7A46'],'preparing'=>['Preparing','#B8860B'],
  'ready'=>['Ready to collect','#2FA86B'],
  'out_for_delivery'=>['Out for delivery','#E8431F'],'delivered'=>['Delivered','#6B5F54'],'cancelled'=>['Cancelled','#9b2c2c'],
];
lt_admin_head('Orders');
lt_admin_sidebar('orders');
lt_admin_top('Ordering', 'Orders', '<span class="btn-studio">' . count($orders) . ' total</span>');
?>
<style>
.od-wrap{padding:22px 34px}
.od-flash{background:#e8f7ef;border:1px solid #b9e6cd;color:#1B7A46;border-radius:10px;padding:11px 15px;margin-bottom:14px}
.od-card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px 20px;margin-bottom:14px;box-shadow:var(--shadow)}
.od-h{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.od-num{font-family:var(--sans);font-weight:800;font-size:17px}
.od-badge{display:inline-block;padding:4px 12px;border-radius:999px;color:#fff;font-weight:700;font-size:12px}
.od-meta{color:var(--gray);font-size:13px;margin:6px 0}
.od-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:16px;margin-top:10px}
.od-items{font-size:13.5px}.od-items div{padding:3px 0}
.od-addr{background:#fbf6ec;border-radius:10px;padding:12px 14px;font-size:13.5px}
.od-addr a{color:var(--magenta);font-weight:700}
.od-total{font-family:var(--display);font-weight:800;font-size:18px}
.od-act{display:flex;gap:6px;flex-wrap:wrap;margin-top:12px}
.od-act button{border:1.5px solid var(--line-2);background:#fff;border-radius:999px;padding:7px 13px;font-weight:700;font-size:12.5px;cursor:pointer}
.od-act button:hover{border-color:var(--navy)}
.od-act .go{background:var(--navy);color:#fff;border-color:var(--navy)}
.od-docs{margin-top:10px;display:flex;gap:16px}
.od-docs a{font-size:12.5px;font-weight:700;color:var(--magenta);text-decoration:none}
.od-docs a:hover{text-decoration:underline}
.od-empty{padding:50px;text-align:center;color:var(--gray)}
</style>
<div class="od-wrap">
<?php if ($flash): ?><div class="od-flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
<?php if (!$orders): ?>
  <div class="od-card"><div class="od-empty">No orders yet. Online orders will appear here in real time.</div></div>
<?php else: foreach ($orders as $o):
  $b = $badge[$o['status']] ?? [$o['status'],'#6B5F54'];
  $addr = $o['address'] ?? null;
  $one = $addr ? trim(implode(', ', array_filter([$addr['line1']??'',$addr['line2']??'',$addr['city']??'',strtoupper($addr['postcode']??'')]))) : '';
  $isDelivery = ($o['fulfilment'] ?? '')==='delivery'; ?>
  <div class="od-card">
    <div class="od-h">
      <div><span class="od-num"><?= htmlspecialchars($o['number']) ?></span>
        <span class="od-badge" style="background:<?= $b[1] ?>"><?= htmlspecialchars($b[0]) ?></span>
        <span class="od-meta" style="margin-left:8px"><?= htmlspecialchars($isDelivery?'🛵 Delivery':'🏠 Collection') ?> · <?= htmlspecialchars(date('d M, H:i', strtotime($o['created'] ?? 'now'))) ?> · <?= htmlspecialchars($o['customer']['time'] ?? '') ?></span>
      </div>
      <div class="od-total"><?= htmlspecialchars($sym.number_format(($o['total']??0)/100,2)) ?> <span class="od-meta" style="font-weight:600"><?= ($o['payment']??'')==='stripe'?'· Card':'· On delivery' ?></span></div>
    </div>
    <div class="od-grid">
      <div class="od-items">
        <?php foreach ((array)$o['items'] as $it): ?><div><?= (int)$it['qty'] ?>× <?= htmlspecialchars($it['name']) ?></div><?php endforeach; ?>
        <?php if (!empty($o['customer']['notes'])): ?><div style="margin-top:6px;color:var(--err)">📝 <?= htmlspecialchars($o['customer']['notes']) ?></div><?php endif; ?>
      </div>
      <div class="od-addr">
        <strong><?= htmlspecialchars($o['customer']['name'] ?? '') ?></strong><br>
        <?= htmlspecialchars($o['customer']['phone'] ?? '') ?> · <?= htmlspecialchars($o['customer']['email'] ?? '') ?>
        <?php if ($isDelivery && $one): ?><br><br><?= htmlspecialchars($one) ?><br>
          <a href="https://www.google.com/maps/dir/?api=1&destination=<?= rawurlencode($one) ?>" target="_blank" rel="noopener">Open in Google Maps →</a><?php endif; ?>
      </div>
    </div>
    <form method="post" class="od-act">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
      <button class="go" name="status" value="approved">Approve (email)</button>
      <button name="status" value="preparing">Preparing</button>
      <?php if ($isDelivery): ?><button name="status" value="out_for_delivery">Out for delivery (courier + customer)</button>
      <?php else: ?><button class="go" name="status" value="ready">Ready to collect (email)</button><?php endif; ?>
      <button name="status" value="delivered"><?= $isDelivery?'Delivered':'Collected' ?> (email)</button>
      <button name="status" value="cancelled" onclick="return confirm('Cancel this order? If it was paid by card it will be refunded via Stripe and a credit note issued.')">Cancel / refund</button>
    </form>
    <div class="od-docs">
      <a href="/invoice-pdf/?order=<?= (int)$o['id'] ?>&t=<?= rawurlencode($o['token'] ?? '') ?>&type=invoice" target="_blank" rel="noopener">⬇ Invoice PDF</a>
      <?php if (($o['status'] ?? '') === 'cancelled'): ?>
        <a href="/invoice-pdf/?order=<?= (int)$o['id'] ?>&t=<?= rawurlencode($o['token'] ?? '') ?>&type=credit" target="_blank" rel="noopener">⬇ Credit note PDF</a>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; endif; ?>
</div>
<?php lt_admin_foot(); ?>
