<?php
/* VentStudio — checkout endpoint. Validates cart server-side, enforces delivery
   rules (Your City postcodes, minimum order, delivery fee), records the order,
   and creates a Stripe Checkout Session (or pay-on-delivery fallback). */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/orders.php';
require_once __DIR__ . '/stripe.php';
require_once __DIR__ . '/emails.php';

header('Content-Type: application/json');
$GLOBALS['C'] = lt_content_load();

function fail($msg, $code = 422) { http_response_code($code); echo json_encode(['error' => $msg]); exit; }

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) fail('Bad request', 400);

/* ---- items (prices from server) ---- */
$idx = lt_menu_index();
$cur = strtolower((string)settings('currency', 'gbp'));
$items = []; $subtotal = 0; $lineItems = [];
foreach ((array)($in['items'] ?? []) as $ci) {
    $slug = (string)($ci['slug'] ?? ''); $qty = max(1, min(50, (int)($ci['qty'] ?? 0)));
    if ($slug === '' || empty($idx[$slug])) continue;
    $note = trim((string)($ci['note'] ?? '')); if (function_exists('mb_substr')) $note = mb_substr($note, 0, 60); else $note = substr($note, 0, 60);
    $note = preg_replace('/[\x00-\x1F]/', '', $note);
    $name = $m_name = $idx[$slug]['name'];
    if ($note !== '') $name = $m_name . ' — ' . $note;
    $m = $idx[$slug]; $amount = (int)round(((float)$m['price']) * 100);
    $subtotal += $amount * $qty;
    $items[] = ['slug' => $slug, 'name' => $name, 'price' => (float)$m['price'], 'qty' => $qty, 'note' => $note];
    $lineItems[] = ['name' => $name, 'amount' => $amount, 'qty' => $qty];
}
if (!$items) fail('Your basket is empty.');

/* ---- server-side ordering rules (mirror the cart popups) ---- */
$rule_bases = []; $rule_extras = []; $rule_toppings = ['crepe-topping' => 'nutella-crepe', 'waffle-topping' => 'nutella-waffle'];
foreach ((array)g('menu.groups') as $grp) {
    $gid = $grp['id'] ?? '';
    if ($gid === 'extras') { foreach ((array)($grp['items'] ?? []) as $it) $rule_extras[] = $it['slug']; }
    if (in_array($gid, ['burgers', 'wraps', 'boxes'], true)) { foreach ((array)($grp['items'] ?? []) as $it) $rule_bases[] = $it['slug']; }
    foreach ((array)($grp['items'] ?? []) as $it) { if (($it['slug'] ?? '') === 'loaded-fries') $rule_bases[] = 'loaded-fries'; }
}
$orderSlugs = array_column($items, 'slug');
$hasBase = (bool)array_intersect($orderSlugs, $rule_bases);
foreach ($items as $it) {
    if (isset($rule_toppings[$it['slug']]) && !in_array($rule_toppings[$it['slug']], $orderSlugs, true)) {
        fail('Toppings can only be ordered with their crepe or waffle.');
    }
    if (in_array($it['slug'], $rule_extras, true) && !$hasBase) {
        fail('Extras can only be added to a burger, box, wrap or loaded fries.');
    }
}

/* ---- customer ---- */
$cust = [
    'name'  => trim((string)($in['name'] ?? '')),
    'phone' => trim((string)($in['phone'] ?? '')),
    'email' => trim((string)($in['email'] ?? '')),
    'notes' => trim((string)($in['notes'] ?? '')),
    'time'  => trim((string)($in['time'] ?? 'ASAP')),
];
if ($cust['name'] === '' || $cust['phone'] === '') fail('Please add your name and phone number.');
if ($cust['email'] === '' || !filter_var($cust['email'], FILTER_VALIDATE_EMAIL)) fail('Please add a valid email for your confirmation.');

/* ---- ordering window (opening hours + first available date) ---- */
$orderDate = trim((string)($in['order_date'] ?? ''));
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $orderDate)) {
    $openFrom = (string)settings('ordersOpenFrom', '');
    if ($openFrom !== '' && $orderDate < $openFrom) fail("We're not taking orders for that date yet — the first available day is " . date('j M Y', strtotime($openFrom)) . '.');
    $hrs = (array)settings('hours', []);
    if ($hrs) {
        $wd = ['sun','mon','tue','wed','thu','fri','sat'][(int)date('w', strtotime($orderDate))];
        if (empty($hrs[$wd]['open'])) fail("Sorry, we're closed on the day you selected — please pick another.");
    }
}
if (trim((string)($in['order_slot'] ?? 'x')) === '') fail('Please choose an available time slot.');

/* ---- pre-order lead time (e.g. BBQ specials need 48h) ---- */
$preLead = 0; $preSlugsAll = [];
foreach ((array)g('menu.groups') as $grp) {
    foreach ((array)($grp['items'] ?? []) as $it) {
        if (!empty($it['preorder'])) {
            $preSlugsAll[$it['slug']] = (int)($it['preorderHours'] ?? 48);
            if (in_array($it['slug'], $orderSlugs, true)) {
                $h = (int)($it['preorderHours'] ?? 48); if ($h > $preLead) $preLead = $h;
            }
        }
    }
}

/* ---- shop open/closed: when closed only pre-order items can be ordered ---- */
if (!(bool)settings('orderingOpen', true)) {
    $nonPre = array_diff($orderSlugs, array_keys($preSlugsAll));
    if ($nonPre) fail("We're closed for orders right now — only pre-order BBQ specials can be ordered at the moment. Please remove the other items or check back when we're open.");
}
if ($preLead > 0) {
    $slot = trim((string)($in['order_slot'] ?? ''));
    if (!preg_match('/^\d{1,2}:\d{2}$/', $slot) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $orderDate)) {
        fail('Your order includes pre-order items — please pick a specific day and time at least ' . $preLead . ' hours ahead.');
    }
    $ts = strtotime($orderDate . ' ' . $slot);
    if ($ts === false || $ts < time() + $preLead * 3600) {
        fail('Pre-order BBQ items need at least ' . $preLead . ' hours (' . round($preLead / 24) . ' days) notice — please choose a later slot.');
    }
}

/* ---- fulfilment + delivery rules ---- */
$fulfil = ($in['fulfilment'] ?? 'delivery') === 'collection' ? 'collection' : 'delivery';
$fee = 0; $address = null;
if ($fulfil === 'delivery') {
    $minOrder = (float)settings('minOrder', 15) * 100;
    if ($subtotal < $minOrder) fail('Minimum delivery order is ' . money(settings('minOrder', 15)) . '. Add a little more!');
    $address = [
        'line1' => trim((string)($in['line1'] ?? '')),
        'line2' => trim((string)($in['line2'] ?? '')),
        'city'  => trim((string)($in['city'] ?? settings('deliveryCity', 'Your City'))),
        'postcode' => trim((string)($in['postcode'] ?? '')),
    ];
    if ($address['line1'] === '' || $address['postcode'] === '') fail('Please add your delivery address and postcode.');
    // postcode area check
    $norm = strtoupper(preg_replace('/\s+/', '', $address['postcode']));
    $outward = strlen($norm) > 3 ? substr($norm, 0, -3) : $norm;
    $accepted = (array)settings('deliveryPostcodes', ['IP1','IP2','IP3','IP4','IP5']);
    if (!in_array($outward, $accepted, true)) {
        fail('Sorry, we only deliver to ' . settings('deliveryCity', 'Your City') . ' (' . implode(', ', $accepted) . ') right now. You can still choose collection.');
    }
    $freeOver = (float)settings('freeDeliveryOver', 30) * 100;
    $fee = $subtotal >= $freeOver ? 0 : (int)round((float)settings('deliveryFee', 2.99) * 100);
    if ($fee > 0) $lineItems[] = ['name' => 'Delivery', 'amount' => $fee, 'qty' => 1];
}
$total = $subtotal + $fee;

$order = lt_orders_add([
    'items' => $items, 'subtotal' => $subtotal, 'delivery_fee_pence' => $fee, 'total' => $total,
    'currency' => $cur, 'customer' => $cust, 'address' => $address, 'fulfilment' => $fulfil,
    'status' => 'received', 'payment' => lt_stripe_enabled() ? 'stripe' : 'on_delivery',
    'token' => bin2hex(random_bytes(8)),
]);

$base = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'example.com');
if (lt_stripe_enabled()) {
    $success = $base . '/order-success/?order=' . $order['id'] . '&session_id={CHECKOUT_SESSION_ID}';
    $cancel  = $base . '/order/?cancelled=1';
    [$ok, $urlOut, $sid, $err] = lt_stripe_create_session($lineItems, $cur, $success, $cancel, ['order' => $order['number']]);
    if (!$ok) fail('Payment error: ' . $err, 502);
    lt_order_update($order['id'], ['stripe_session' => $sid]);
    echo json_encode(['url' => $urlOut, 'order' => $order['number']]);
    exit;
}
/* Pay on delivery — confirm now and fire notifications */
$order = lt_order_update($order['id'], ['status' => 'received']) ?: $order;
lt_email_on_placed($order);
echo json_encode(['url' => '/order-success/?order=' . $order['id'] . '&mock=1', 'order' => $order['number']]);
