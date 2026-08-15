<?php
/* VentStudio — branded, printable invoice/receipt for an order. */
require_once __DIR__ . '/store.php';

function lt_invoice_url(array $o): string {
    return 'https://example.com/invoice/?order=' . (int)$o['id'] . '&t=' . rawurlencode($o['token'] ?? '');
}

function lt_invoice_html(array $o): string {
    $C = lt_content_load(); $s = $C['settings'] ?? [];
    $sym = $s['currencySymbol'] ?? '£';
    $legal = $s['legalName'] ?? 'Example Trading Ltd';
    $addr = $s['address'] ?? '1 Example Street, Your City, AB1 2CD';
    $email = $s['email'] ?? 'hello@yourvenue.co.uk';
    $phone = $s['phone'] ?? '+44 7000 000000';
    $ee = fn($x) => htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8');
    $cust = $o['customer'] ?? []; $a = $o['address'] ?? null;
    $billTo = $ee($cust['name'] ?? '');
    if ($a) $billTo .= '<br>' . $ee(trim(implode(', ', array_filter([$a['line1']??'',$a['line2']??'',$a['city']??'',strtoupper($a['postcode']??'')]))));
    $billTo .= '<br>' . $ee($cust['phone'] ?? '') . ' · ' . $ee($cust['email'] ?? '');
    $rows = '';
    foreach ((array)$o['items'] as $it) {
        $rows .= '<tr><td>' . $ee($it['name']) . '</td><td class="c">' . (int)$it['qty'] . '</td>'
              . '<td class="r">' . $sym . number_format($it['price'],2) . '</td>'
              . '<td class="r">' . $sym . number_format($it['qty']*$it['price'],2) . '</td></tr>';
    }
    $subtotal = ($o['subtotal'] ?? 0) / 100; $fee = ($o['delivery_fee_pence'] ?? 0) / 100; $total = ($o['total'] ?? 0) / 100;
    $vatNo = $s['vatNumber'] ?? ''; $vatRate = (float)($s['vatRate'] ?? 20);
    $vatAmt = $vatRate > 0 ? $total - $total / (1 + $vatRate / 100) : 0;
    $vatLabel = rtrim(rtrim(number_format($vatRate, 2), '0'), '.');
    $pay = ($o['payment'] ?? '') === 'stripe' ? 'Card (Stripe)' : 'Pay on delivery';
    $paid = !empty($o['paid']) ? 'PAID' : (($o['payment'] ?? '')==='on_delivery' ? 'DUE ON DELIVERY' : 'PENDING');
    $date = date('j F Y, H:i', strtotime($o['created'] ?? 'now'));
    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
      . '<title>Invoice ' . $ee($o['number']) . ' — VentStudio</title><style>'
      . 'body{font-family:Arial,Helvetica,sans-serif;color:#1B1512;background:#f2ead9;margin:0;padding:24px}'
      . '.inv{max-width:720px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.12)}'
      . '.hd{background:#1B1512;color:#FBEAD1;padding:24px 28px;display:flex;justify-content:space-between;align-items:center}'
      . '.hd .b{display:flex;align-items:center;gap:12px}.hd img{width:52px;height:52px}'
      . '.hd .t{font-weight:800;font-size:22px}.hd .amp{color:#F6A800}'
      . '.hd .inv-no{text-align:right;font-size:13px;opacity:.85}'
      . '.bar{height:6px;background:#E8431F}'
      . '.bd{padding:26px 28px}'
      . '.meta{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:20px;font-size:13px}'
      . '.meta h3{margin:0 0 6px;font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#9a8f84}'
      . 'table{width:100%;border-collapse:collapse;margin:8px 0 4px}'
      . 'th,td{padding:9px 6px;border-bottom:1px solid #eee;font-size:14px;text-align:left}'
      . 'th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#9a8f84}'
      . 'td.r,th.r{text-align:right}td.c,th.c{text-align:center}'
      . '.tot{margin-left:auto;width:260px;margin-top:10px;font-size:14px}'
      . '.tot .row{display:flex;justify-content:space-between;padding:5px 0}'
      . '.tot .grand{font-weight:800;font-size:18px;border-top:2px solid #1B1512;margin-top:6px;padding-top:8px}'
      . '.status{display:inline-block;background:#2FA86B;color:#fff;font-weight:700;border-radius:999px;padding:5px 14px;font-size:12px}'
      . '.status.due{background:#F6A800;color:#1B1512}.status.pending{background:#9a8f84}'
      . '.ft{padding:18px 28px;background:#fbf4e6;font-size:12px;color:#6b5f57;text-align:center}'
      . '.noprint{text-align:center;margin:18px auto;max-width:720px}'
      . '.noprint button{background:#E8431F;color:#fff;border:none;border-radius:999px;padding:11px 26px;font-weight:700;cursor:pointer;font-size:15px}'
      . '@media print{body{background:#fff;padding:0}.inv{box-shadow:none;border-radius:0}.noprint{display:none}}'
      . '</style></head><body>'
      . '<div class="noprint"><a href="/invoice-pdf/?order=' . (int)$o['id'] . '&t=' . rawurlencode($o['token'] ?? '') . '" style="display:inline-block;background:#1B1512;color:#FBEAD1;text-decoration:none;border-radius:999px;padding:11px 26px;font-weight:700;margin-right:8px">Download PDF</a><button onclick="window.print()">Print</button></div>'
      . '<div class="inv"><div class="hd"><div class="b">'
      . '<img src="/assets/img/brand/logo.png" alt=""><span class="t">Liz <span class="amp">&amp;</span> Tom</span></div>'
      . '<div class="inv-no"><strong>INVOICE</strong><br>' . $ee($o['number']) . '<br>' . $ee($date) . '</div></div>'
      . '<div class="bar"></div><div class="bd">'
      . '<div class="meta"><div><h3>From</h3>' . $ee($legal) . '<br>trading as VentStudio Street Food<br>' . $ee($addr) . '<br>' . $ee($email) . ' · ' . $ee($phone) . ($vatNo ? '<br>VAT No. ' . $ee($vatNo) : '') . '</div>'
      . '<div><h3>Bill to</h3>' . $billTo . '</div>'
      . '<div><h3>Status</h3><span class="status ' . ($paid==='PAID'?'':($paid==='PENDING'?'pending':'due')) . '">' . $ee($paid) . '</span><br><span style="font-size:12px;color:#6b5f57">' . $ee($pay) . '</span><br><span style="font-size:12px;color:#6b5f57">' . $ee(ucfirst($o['fulfilment'] ?? 'delivery')) . ($o['customer']['time']??'' ? ' · ' . $ee($o['customer']['time']) : '') . '</span></div></div>'
      . '<table><thead><tr><th>Item</th><th class="c">Qty</th><th class="r">Unit</th><th class="r">Amount</th></tr></thead><tbody>' . $rows . '</tbody></table>'
      . '<div class="tot"><div class="row"><span>Subtotal</span><span>' . $sym . number_format($subtotal,2) . '</span></div>'
      . (($o['fulfilment']??'')==='delivery' ? '<div class="row"><span>Delivery</span><span>' . ($fee>0?$sym.number_format($fee,2):'FREE') . '</span></div>' : '')
      . '<div class="row grand"><span>Total</span><span>' . $sym . number_format($total,2) . '</span></div>'
      . ($vatAmt > 0 ? '<div class="row" style="font-size:12px;color:#6b5f57"><span>Includes VAT @ ' . $vatLabel . '%</span><span>' . $sym . number_format($vatAmt,2) . '</span></div>' : '')
      . '</div>'
      . '<p style="clear:both;font-size:12px;color:#9a8f84;margin-top:26px">' . ($vatNo ? 'All prices include VAT at ' . $vatLabel . '%. VAT registration number ' . $ee($vatNo) . '. ' : '') . 'Thank you for your order!</p>'
      . '</div><div class="ft">VentStudio Street Food · ' . $ee($addr) . ' · example.com</div></div>'
      . '</body></html>';
}
