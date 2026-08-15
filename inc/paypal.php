<?php
/* VentStudio — PayPal Checkout (Orders API v2), no SDK, plain curl.
   Credentials from .env: LT_PAYPAL_CLIENT_ID, LT_PAYPAL_SECRET, LT_PAYPAL_ENV (sandbox|live). */
require_once __DIR__ . '/config.php';

function lt_paypal_id()   { return (string)env('LT_PAYPAL_CLIENT_ID', ''); }
function lt_paypal_secret(){ return (string)env('LT_PAYPAL_SECRET', ''); }
function lt_paypal_enabled(){ return lt_paypal_id() !== '' && lt_paypal_secret() !== ''; }
function lt_paypal_base() {
    return strtolower(env('LT_PAYPAL_ENV', 'sandbox')) === 'live'
        ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
}

function lt_paypal_token() {
    $ch = curl_init(lt_paypal_base() . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_USERPWD => lt_paypal_id() . ':' . lt_paypal_secret(),
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => ['Accept: application/json'], CURLOPT_TIMEOUT => 25,
    ]);
    $raw = curl_exec($ch); curl_close($ch);
    $j = json_decode($raw, true) ?: [];
    return $j['access_token'] ?? '';
}

function lt_paypal_call($method, $path, $token, $body = null) {
    $ch = curl_init(lt_paypal_base() . $path);
    $opts = [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
        CURLOPT_TIMEOUT => 25,
    ];
    if ($body !== null) $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch); $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return [$code, json_decode($raw, true) ?: []];
}

/** Create an order; returns [ok, approveUrl, paypalOrderId, err]. $total in pence. */
function lt_paypal_create_order(int $total, string $currency, string $returnUrl, string $cancelUrl, string $ref = '') {
    $token = lt_paypal_token();
    if ($token === '') return [false, '', '', 'Could not authenticate with PayPal'];
    $body = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => $ref,
            'description'  => 'VentStudio Street Food order ' . $ref,
            'amount' => ['currency_code' => strtoupper($currency), 'value' => number_format($total / 100, 2, '.', '')],
        ]],
        'application_context' => [
            'brand_name' => 'VentStudio Street Food',
            'user_action' => 'PAY_NOW',
            'shipping_preference' => 'NO_SHIPPING',
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
        ],
    ];
    [$code, $j] = lt_paypal_call('POST', '/v2/checkout/orders', $token, $body);
    if ($code < 200 || $code >= 300) return [false, '', '', $j['message'] ?? 'PayPal error'];
    $approve = '';
    foreach (($j['links'] ?? []) as $l) if (($l['rel'] ?? '') === 'approve') $approve = $l['href'];
    return [$approve !== '', $approve, $j['id'] ?? '', $approve ? '' : 'No approval link'];
}

/** Capture an approved order; returns [ok, status]. */
function lt_paypal_capture(string $paypalOrderId) {
    $token = lt_paypal_token();
    if ($token === '') return [false, 'auth'];
    [$code, $j] = lt_paypal_call('POST', '/v2/checkout/orders/' . rawurlencode($paypalOrderId) . '/capture', $token, new stdClass());
    return [($j['status'] ?? '') === 'COMPLETED', $j['status'] ?? 'error'];
}
