<?php
$META_TITLE = 'Order confirmed';
require_once __DIR__ . '/../inc/orders.php';
require_once __DIR__ . '/../inc/stripe.php';
require_once __DIR__ . '/../inc/emails.php';

$id    = (int)($_GET['order'] ?? 0);
$sid   = (string)($_GET['session_id'] ?? '');   // Stripe Checkout Session id on return
$order = $id ? lt_order_get($id) : null;

if ($order && empty($order['notified'])) {
    if ($sid && lt_stripe_enabled()) {
        [$ok, $sess] = lt_stripe_get_session($sid);
        if ($ok && ($sess['payment_status'] ?? '') === 'paid') {
            $order = lt_order_update($id, ['status' => 'received', 'paid' => true, 'notified' => true]) ?: $order;
            lt_email_on_placed($order);           // customer confirmation + kitchen ticket
        }
    }
}
$sym = settings('currencySymbol', '£');
$wa  = settings('whatsapp', '');
require __DIR__ . '/../inc/head.php';
?>
<section class="section">
  <div class="wrap">
    <?php if (!$order): ?>
      <div class="success"><h1>We can't find that order.</h1>
        <p>If you were charged, don't worry — reply to your confirmation email or message us and we'll sort it.</p>
        <a class="btn btn-primary" href="<?= url('menu') ?>">Back to the menu</a></div>
    <?php else:
      $addr = $order['address'] ?? null;
      $addr_one = $addr ? trim(implode(', ', array_filter([$addr['line1']??'',$addr['line2']??'',$addr['city']??'',strtoupper($addr['postcode']??'')]))) : ''; ?>
      <div class="success">
        <div class="tick">✓</div>
        <h1>Order confirmed!</h1>
        <span class="order-no">Order <?= e($order['number']) ?></span>
        <p>Thanks <?= e($order['customer']['name'] ?? '') ?> — we're getting it on the grill.
        <?php if (($order['fulfilment'] ?? '')==='delivery'): ?>We'll deliver to <strong><?= e($addr_one) ?></strong> and text <strong><?= e($order['customer']['phone'] ?? '') ?></strong> when it's on the way.
        <?php else: ?>Collect from the van — we'll text <strong><?= e($order['customer']['phone'] ?? '') ?></strong> when it's ready.<?php endif; ?>
        A confirmation is on its way to <strong><?= e($order['customer']['email'] ?? '') ?></strong>.</p>
        <div class="co-card" style="text-align:left;max-width:440px;margin:22px auto">
          <?php foreach ((array)$order['items'] as $it): ?>
            <div class="co-line"><span><?= (int)$it['qty'] ?>× <?= e($it['name']) ?></span><span class="mono"><?= e($sym.number_format($it['qty']*$it['price'],2)) ?></span></div>
          <?php endforeach; ?>
          <?php if (($order['fulfilment']??'')==='delivery'): ?>
          <div class="co-line"><span>Delivery</span><span class="mono"><?= ($order['delivery_fee_pence']??0)>0 ? e($sym.number_format($order['delivery_fee_pence']/100,2)) : 'FREE' ?></span></div>
          <?php endif; ?>
          <div class="co-total"><span>Total</span><strong><?= e($sym.number_format(($order['total']??0)/100,2)) ?></strong></div>
          <?php if (($order['payment']??'')==='on_delivery'): ?><p class="cart-note" style="text-align:left">Pay on delivery.</p><?php endif; ?>
        </div>
        <div class="hero-cta" style="justify-content:center">
          <a class="btn btn-dark" href="/invoice/?order=<?= (int)$order['id'] ?>&t=<?= e($order['token'] ?? '') ?>" target="_blank" rel="noopener">View / print invoice</a>
          <?php if ($wa): ?><a class="btn btn-honey" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">Message the van</a><?php endif; ?>
          <a class="btn btn-primary" href="<?= url('menu') ?>">Order again</a>
        </div>
      </div>
      <script>if(window.LTCart) window.LTCart.clear();</script>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
