<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__) . '/inc/orders.php';
lt_require_login();

$C = lt_content_load();
$sym = $C['settings']['currencySymbol'] ?? '£';
$vatRate = (float)($C['settings']['vatRate'] ?? 20);
$from = preg_replace('/[^0-9\-]/', '', (string)($_GET['from'] ?? ''));
$to   = preg_replace('/[^0-9\-]/', '', (string)($_GET['to'] ?? ''));

$all = lt_orders_all();
$rows = [];
$sumGross = 0; $sumVat = 0;
foreach ($all as $o) {
    $d = substr($o['created'] ?? '', 0, 10);
    if ($from && $d < $from) continue;
    if ($to && $d > $to) continue;
    $rows[] = $o;
    if (($o['status'] ?? '') !== 'cancelled') {
        $sumGross += (int)($o['total'] ?? 0);
        $sumVat += $vatRate > 0 ? ((int)($o['total'] ?? 0)) - ((int)($o['total'] ?? 0)) / (1 + $vatRate/100) : 0;
    }
}
function mm($p,$s){ return $s . number_format($p/100, 2); }
$badge = ['received'=>'#2D6CB0','approved'=>'#1B7A46','preparing'=>'#B8860B','out_for_delivery'=>'#E8431F','delivered'=>'#6B5F54','cancelled'=>'#9b2c2c'];

lt_admin_head('Invoices');
lt_admin_sidebar('invoices');
lt_admin_top('Ordering', 'Invoices', '<span class="btn-studio">' . count($rows) . ' shown</span>');
?>
<style>
.iv-wrap{padding:22px 34px}
.iv-filter{display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:16px 18px;margin-bottom:16px;box-shadow:var(--shadow)}
.iv-filter label{display:block;font-size:12px;font-weight:700;color:var(--gray);margin-bottom:5px}
.iv-filter input{border:1.5px solid var(--line-2);border-radius:9px;padding:9px 11px;font-family:var(--sans)}
.iv-sum{display:flex;gap:24px;margin-left:auto;font-size:13px}
.iv-sum b{display:block;font-family:var(--display);font-size:18px}
.iv-table{width:100%;border-collapse:collapse;background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
.iv-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:var(--gray);padding:11px 12px;background:#fbf6ec;border-bottom:1px solid var(--line)}
.iv-table td{padding:11px 12px;border-bottom:1px solid var(--line);font-size:13.5px}
.iv-badge{display:inline-block;padding:3px 10px;border-radius:999px;color:#fff;font-weight:700;font-size:11px}
.iv-dl{font-weight:700;color:var(--magenta);text-decoration:none;font-size:12.5px}
.iv-dl:hover{text-decoration:underline}
.iv-empty{padding:40px;text-align:center;color:var(--gray)}
</style>
<div class="iv-wrap">
  <form class="iv-filter" method="get">
    <div><label>From</label><input type="date" name="from" value="<?= htmlspecialchars($from) ?>"></div>
    <div><label>To</label><input type="date" name="to" value="<?= htmlspecialchars($to) ?>"></div>
    <button class="btn-studio primary" type="submit">Filter</button>
    <a class="btn-studio" href="invoices.php">Reset</a>
    <a class="btn-studio" href="invoices-zip.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" title="Download every invoice (and credit note) in the current date range as a ZIP">⬇ Download all (ZIP)</a>
    <div class="iv-sum">
      <div>Gross (excl. cancelled)<b><?= mm($sumGross,$sym) ?></b></div>
      <div>of which VAT @ <?= rtrim(rtrim(number_format($vatRate,2),'0'),'.') ?>%<b><?= mm((int)round($sumVat),$sym) ?></b></div>
    </div>
  </form>

  <?php if (!$rows): ?>
    <div class="iv-table"><div class="iv-empty">No invoices for this period.</div></div>
  <?php else: ?>
  <table class="iv-table">
    <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th><th>Status</th><th>Download</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $o): $st=$o['status']??'received'; $cancelled=$st==='cancelled'; ?>
      <tr>
        <td><strong><?= htmlspecialchars($o['number']) ?></strong><?php if ($cancelled): ?><br><span style="color:var(--gray);font-size:11.5px"><?= htmlspecialchars($o['credit_number'] ?? ('CN-'.substr($o['number'],3))) ?></span><?php endif; ?></td>
        <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($o['created'] ?? 'now'))) ?></td>
        <td><?= htmlspecialchars($o['customer']['name'] ?? '') ?><br><span style="color:var(--gray);font-size:12px"><?= htmlspecialchars($o['customer']['email'] ?? '') ?></span></td>
        <td><strong><?= mm((int)($o['total']??0),$sym) ?></strong><?php if (!empty($o['refunded'])): ?><br><span style="color:#9b2c2c;font-size:11.5px">refunded</span><?php endif; ?></td>
        <td><span class="iv-badge" style="background:<?= $badge[$st]??'#888' ?>"><?= htmlspecialchars(str_replace('_',' ',$st)) ?></span></td>
        <td>
          <a class="iv-dl" href="/invoice-pdf/?order=<?= (int)$o['id'] ?>&t=<?= rawurlencode($o['token'] ?? '') ?>&type=invoice" target="_blank" rel="noopener">Invoice</a>
          <?php if ($cancelled): ?> · <a class="iv-dl" href="/invoice-pdf/?order=<?= (int)$o['id'] ?>&t=<?= rawurlencode($o['token'] ?? '') ?>&type=credit" target="_blank" rel="noopener">Credit note</a><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php lt_admin_foot(); ?>
