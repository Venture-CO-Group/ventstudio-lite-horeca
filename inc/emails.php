<?php
/* VentStudio — transactional email templates, rendering & sending. */
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/mailer_smtp.php';
require_once __DIR__ . '/invoice.php';

function lt_em_content() { static $c=null; if($c===null)$c=lt_content_load(); return $c; }
function lt_em_setting($k, $d='') { return lt_em_content()['settings'][$k] ?? $d; }
function lt_em_sym() { return lt_em_setting('currencySymbol','£'); }
function lt_em_money($v) { return lt_em_sym() . number_format((float)$v, 2); }
function lt_em_templates_file() { return dirname(__DIR__) . '/data/email-templates.json'; }

function lt_email_defaults() {
    $pill = function ($t, $bg) { return '<span style="display:inline-block;background:'.$bg.';color:#FBEAD1;font-weight:700;font-size:12px;letter-spacing:.05em;text-transform:uppercase;padding:6px 14px;border-radius:999px;font-family:Arial,Helvetica,sans-serif">'.$t.'</span>'; };
    return [
        'order_received' => [
            'label' => 'Customer — Order received',
            'subject' => 'Order {{number}} received — we\'re firing up the grill 🔥',
            'body' => $pill('Order received', '#E8431F')
                . '<h2 style="font-size:26px;margin:14px 0 6px;color:#1B1512">Thanks, {{name}} — you\'re in!</h2>'
                . '<p style="color:#5d524b">We\'ve got your order and payment safe. The moment we confirm it, it\'s straight onto the hatch — smoked low, loaded high.</p>'
                . '<h3 style="margin:22px 0 4px;color:#E8431F">Your order {{number}}</h3>{{items_table}}{{fulfilment_block}}{{invoice_button}}'
                . '<p style="margin-top:18px;color:#5d524b">Sit tight — we\'ll email you again the second it\'s cooking. Questions? Reply here or WhatsApp <strong>{{phone_business}}</strong>.</p>'
        ],
        'order_approved' => [
            'label' => 'Customer — Order confirmed / cooking',
            'subject' => 'Order {{number}} is on the grill 👨‍🍳',
            'body' => $pill('Now cooking', '#F6A800')
                . '<h2 style="font-size:26px;margin:14px 0 6px;color:#1B1512">We\'re saucing, {{name}}!</h2>'
                . '<p style="color:#5d524b">Great news — order <strong>{{number}}</strong> is confirmed and being made fresh right now on the van.</p>'
                . '{{fulfilment_short}}'
                . '<h3 style="margin:22px 0 4px;color:#E8431F">What\'s coming</h3>{{items_table}}'
                . '<p style="margin-top:16px;color:#5d524b">Hot, fast and hand-made — the way street food should be.</p>'
        ],
        'order_ready' => [
            'label' => 'Customer — Ready to collect',
            'subject' => 'Order {{number}} is ready — come and get it! 🔔',
            'body' => $pill('Ready to collect', '#2FA86B')
                . '<h2 style="font-size:26px;margin:14px 0 6px;color:#1B1512">It\'s ready, {{name}}!</h2>'
                . '<p style="color:#5d524b">Your order <strong>{{number}}</strong> is freshly made and waiting at the van — come and grab it while it\'s hot.</p>'
                . '{{fulfilment_short}}'
                . '<h3 style="margin:22px 0 4px;color:#E8431F">Your order</h3>{{items_table}}'
                . '<p style="margin-top:16px;color:#5d524b">See you at the hatch. Pull up — we\'re saucing. 🌭</p>'
        ],
        'order_delivered' => [
            'label' => 'Customer — Delivered',
            'subject' => 'Order {{number}} — tuck in! 🎉',
            'body' => $pill('Delivered', '#2FA86B')
                . '<h2 style="font-size:26px;margin:14px 0 6px;color:#1B1512">Delivered — dig in!</h2>'
                . '<p style="color:#5d524b">Your order <strong>{{number}}</strong> is with you. We hope it\'s smoky, saucy and exactly what you fancied.</p>'
                . '<div style="background:#fff6e9;border-radius:12px;padding:18px 20px;margin:18px 0;text-align:center">'
                . '<p style="margin:0 0 6px;font-weight:700;color:#1B1512">Loved it?</p>'
                . '<p style="margin:0;color:#5d524b">Tag us <strong>@yourvenue</strong> or leave a review — it genuinely makes VentStudio\'s day.</p></div>'
                . '<p style="color:#5d524b">See you next time. Pull up — we\'re saucing. 🌭</p>'
        ],
        'kitchen' => [
            'label' => 'Kitchen — New order ticket',
            'subject' => '🧾 NEW {{fulfilment_upper}} ORDER {{number}} · {{time}}',
            'body' => $pill('New order · {{fulfilment_upper}}', '#1B1512')
                . '<h2 style="font-size:24px;margin:12px 0 6px;color:#1B1512">Ticket {{number}}</h2>'
                . '<p style="color:#5d524b;margin:0 0 8px"><strong>Wanted:</strong> {{time}}</p>'
                . '{{items_table}}'
                . '<p style="margin-top:6px"><strong>Customer:</strong> {{name}} · <a href="tel:{{phone}}" style="color:#E8431F">{{phone}}</a></p>'
                . '{{address_block}}{{notes_block}}'
        ],
        'courier' => [
            'label' => 'Courier — Delivery job',
            'subject' => '🛵 DELIVER {{number}} → {{postcode}}',
            'body' => $pill('Delivery job', '#E8431F')
                . '<h2 style="font-size:24px;margin:12px 0 6px;color:#1B1512">Drop {{number}}</h2>'
                . '<p style="margin:0 0 4px"><strong>{{name}}</strong> · <a href="tel:{{phone}}" style="color:#E8431F">{{phone}}</a></p>'
                . '<div style="font-size:18px;font-weight:800;margin:12px 0;color:#1B1512">{{address_oneline}}</div>'
                . '<p style="margin:0 0 14px">{{map_button}}</p>{{map_embed}}'
                . '<p style="margin-top:14px;color:#5d524b"><strong>{{items_count}}</strong> items · Payment: {{payment}}</p>{{notes_block}}'
        ],
    ];
}

function lt_email_templates() {
    $def = lt_email_defaults();
    $f = lt_em_templates_file();
    if (is_file($f)) {
        $o = json_decode((string)file_get_contents($f), true);
        if (is_array($o)) foreach ($o as $k => $v) if (isset($def[$k])) $def[$k] = array_merge($def[$k], $v);
    }
    return $def;
}
function lt_email_save_templates(array $t) {
    $f = lt_em_templates_file(); $tmp = $f . '.tmp';
    if (@file_put_contents($tmp, json_encode($t, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX) === false) return false;
    return @rename($tmp, $f);
}

function lt_email_layout($inner) {
    $base = 'https://example.com';
    return '<!DOCTYPE html><html><body style="margin:0;background:#f2ead9;font-family:Arial,Helvetica,sans-serif;color:#1B1512">'
      . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2ead9;padding:24px 0"><tr><td align="center">'
      . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FBEAD1;border-radius:16px;overflow:hidden">'
      . '<tr><td style="background:#1B1512;padding:24px 28px" align="center">'
      . '<img src="'.$base.'/assets/img/brand/logo.png" alt="" width="58" height="58" style="display:inline-block;vertical-align:middle">'
      . '<span style="display:inline-block;vertical-align:middle;margin-left:12px;font-family:Arial,Helvetica,sans-serif;font-weight:800;font-size:26px;color:#FBEAD1">Liz <span style="color:#F6A800">&amp;</span> Tom</span>'
      . '</td></tr>'
      . '<tr><td style="height:6px;background:#E8431F"></td></tr>'
      . '<tr><td style="padding:30px 30px 26px;font-size:15px;line-height:1.65;color:#1B1512">'.$inner.'</td></tr>'
      . '<tr><td style="background:#1B1512;color:#FBEAD1;padding:22px 28px;font-size:12px;text-align:center;line-height:1.7">'
      . '<a href="'.$base.'/menu/" style="display:inline-block;background:#E8431F;color:#FBEAD1;text-decoration:none;font-weight:700;padding:11px 24px;border-radius:999px;font-family:Arial,Helvetica,sans-serif;margin-bottom:14px">Order again</a><br>'
      . 'VentStudio Street Food · Your Region · <a href="'.$base.'" style="color:#F6A800">example.com</a><br>'
      . '<span style="color:#8a7f74">Delivering hot across Your City · EST. 2026</span></td></tr>'
      . '</table></td></tr></table></body></html>';
}

function lt_order_email_vars(array $o) {
    $sym = lt_em_sym();
    $rows = '';
    foreach ((array)$o['items'] as $it) {
        $rows .= '<tr><td style="padding:6px 0;border-bottom:1px solid #e6d6ba">'.(int)$it['qty'].'× '.htmlspecialchars($it['name']).'</td>'
              . '<td align="right" style="padding:6px 0;border-bottom:1px solid #e6d6ba;font-family:monospace">'.$sym.number_format($it['qty']*$it['price'],2).'</td></tr>';
    }
    $subtotal = ($o['subtotal'] ?? (($o['total'] ?? 0) - ($o['delivery_fee_pence'] ?? 0))) / 100;
    $fee = ($o['delivery_fee_pence'] ?? 0) / 100;
    $table = '<table role="presentation" width="100%" style="margin:16px 0;font-size:14px">'.$rows
        . '<tr><td style="padding-top:8px">Subtotal</td><td align="right" style="padding-top:8px;font-family:monospace">'.$sym.number_format($subtotal,2).'</td></tr>'
        . ($o['fulfilment']==='delivery' ? '<tr><td>Delivery</td><td align="right" style="font-family:monospace">'.($fee>0?$sym.number_format($fee,2):'FREE').'</td></tr>' : '')
        . '<tr><td style="font-weight:700;font-size:16px;padding-top:6px">Total</td><td align="right" style="font-weight:700;font-size:16px;font-family:monospace;padding-top:6px">'.$sym.number_format(($o['total']??0)/100,2).'</td></tr>'
        . '</table>';
    $cust = $o['customer'] ?? [];
    $addr = $o['address'] ?? [];
    $addr_one = trim(implode(', ', array_filter([$addr['line1']??'', $addr['line2']??'', $addr['city']??'', strtoupper($addr['postcode']??'')])));
    $mapsq = rawurlencode($addr_one);
    $mapDir = 'https://www.google.com/maps/dir/?api=1&destination='.$mapsq;
    $mapEmbed = $addr_one ? '<div style="margin:14px 0"><iframe width="100%" height="220" style="border:0;border-radius:12px" loading="lazy" src="https://maps.google.com/maps?q='.$mapsq.'&z=15&output=embed"></iframe></div>' : '';
    $mapBtn = $addr_one ? '<a href="'.$mapDir.'" style="display:inline-block;background:#E8431F;color:#FBEAD1;text-decoration:none;font-weight:700;padding:12px 22px;border-radius:999px">Open in Google Maps →</a>' : '';
    $fulfil = ($o['fulfilment'] ?? 'delivery');
    if ($fulfil === 'delivery') {
        $fblock = '<div style="background:#fff6e9;border-radius:12px;padding:14px 16px;margin:14px 0"><strong>Delivery to</strong><br>'.htmlspecialchars($addr_one).'<br>'.($mapBtn).'</div>';
        $fshort = '<p><strong>Delivering to:</strong> '.htmlspecialchars($addr_one).'</p>';
        $addr_block = '<p><strong>Deliver to:</strong> '.htmlspecialchars($addr_one).'</p>';
    } else {
        $fblock = '<div style="background:#fff6e9;border-radius:12px;padding:14px 16px;margin:14px 0"><strong>Collection from the van</strong><br>We\'ll message you when it\'s ready.</div>';
        $fshort = '<p><strong>Collection</strong> from the van.</p>';
        $addr_block = '<p><strong>Collection</strong> from the van.</p>';
    }
    $notes = trim($cust['notes'] ?? '');
    $notes_block = $notes ? '<div style="background:#fdecea;border-radius:10px;padding:10px 14px;margin:10px 0"><strong>Notes:</strong> '.htmlspecialchars($notes).'</div>' : '';
    return [
        '{{number}}' => $o['number'] ?? '',
        '{{name}}' => htmlspecialchars($cust['name'] ?? ''),
        '{{phone}}' => htmlspecialchars($cust['phone'] ?? ''),
        '{{phone_business}}' => lt_em_setting('phone','+44 7000 000000'),
        '{{items_table}}' => $table,
        '{{items_count}}' => (string)array_sum(array_map(fn($i)=>(int)$i['qty'], (array)$o['items'])),
        '{{fulfilment_block}}' => $fblock,
        '{{fulfilment_short}}' => $fshort,
        '{{fulfilment_upper}}' => strtoupper($fulfil),
        '{{address_block}}' => $addr_block,
        '{{address_oneline}}' => htmlspecialchars($addr_one),
        '{{postcode}}' => strtoupper($addr['postcode'] ?? ''),
        '{{map_button}}' => $mapBtn,
        '{{map_embed}}' => $mapEmbed,
        '{{time}}' => htmlspecialchars($cust['time'] ?? 'ASAP'),
        '{{notes_block}}' => $notes_block,
        '{{payment}}' => ($o['payment'] ?? '') === 'stripe' ? 'card (Stripe)' : 'on delivery',
        '{{invoice_button}}' => !empty($o['token']) ? '<p style="margin:18px 0"><a href="'.lt_invoice_url($o).'" style="display:inline-block;background:#1B1512;color:#FBEAD1;text-decoration:none;font-weight:700;padding:11px 22px;border-radius:999px;font-family:Arial,Helvetica,sans-serif">View / download your invoice</a></p>' : '',
    ];
}

function lt_email_render($key, array $vars) {
    $t = lt_email_templates()[$key] ?? null;
    if (!$t) return null;
    $subject = strtr($t['subject'], $vars);
    $html = lt_email_layout(strtr($t['body'], $vars));
    return [$subject, $html];
}

function lt_email_send_order($order, $key, $to) {
    $r = lt_email_render($key, lt_order_email_vars($order));
    if (!$r) return [false, 'no template'];
    return lt_mail_send($to, $r[0], $r[1]);
}

/* Lifecycle helpers */
function lt_email_on_placed($order) {
    $cust = $order['customer']['email'] ?? '';
    if ($cust) lt_email_send_order($order, 'order_received', $cust);
    $k = env('LT_KITCHEN_EMAIL', lt_em_setting('kitchenEmail',''));
    if ($k) lt_email_send_order($order, 'kitchen', $k);
}
function lt_email_on_approved($order) {
    $cust = $order['customer']['email'] ?? '';
    if ($cust) lt_email_send_order($order, 'order_approved', $cust);
}
function lt_email_on_dispatch($order) {
    if (($order['fulfilment'] ?? '') !== 'delivery') return;
    $c = env('LT_COURIER_EMAIL', lt_em_setting('courierEmail',''));
    if ($c) lt_email_send_order($order, 'courier', $c);
}
function lt_email_on_ready($order) {
    if (($order['fulfilment'] ?? '') !== 'collection') return;
    $cust = $order['customer']['email'] ?? '';
    if ($cust) lt_email_send_order($order, 'order_ready', $cust);
}
function lt_email_on_delivered($order) {
    $cust = $order['customer']['email'] ?? '';
    if ($cust) lt_email_send_order($order, 'order_delivered', $cust);
}
