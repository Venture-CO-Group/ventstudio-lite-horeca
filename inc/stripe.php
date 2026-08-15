<?php
/* VentStudio — minimal Stripe Checkout integration (no SDK, plain curl).
   Keys come from .env: LT_STRIPE_SECRET (sk_test_… / sk_live_…). */
require_once __DIR__ . '/config.php';

function lt_stripe_secret(): string { return (string)env('LT_STRIPE_SECRET', ''); }
function lt_stripe_publishable(): string { return (string)env('LT_STRIPE_PUBLISHABLE', ''); }
function lt_stripe_enabled(): bool { return lt_stripe_secret() !== ''; }

/** low-level form-encoded call to the Stripe API */
function lt_stripe_call(string $method, string $path, array $params = []): array {
    $sk = lt_stripe_secret();
    if ($sk === '') return [false, ['error' => 'Stripe not configured'], 0];
    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    $ch = curl_init();
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => $sk . ':',
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => ['Stripe-Version: 2024-06-20'],
    ];
    if (strtoupper($method) === 'POST') {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
    } else {
        if ($params) $url .= '?' . http_build_query($params);
    }
    $opts[CURLOPT_URL] = $url;
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) return [false, ['error' => $err ?: 'network error'], 0];
    $json = json_decode($raw, true) ?: [];
    return [$code >= 200 && $code < 300, $json, $code];
}

/**
 * Create a Checkout Session.
 * $lineItems: [ ['name'=>..., 'amount'=>pence(int), 'qty'=>int], ... ]
 */
function lt_stripe_create_session(array $lineItems, string $currency, string $successUrl, string $cancelUrl, array $meta = []): array {
    $params = [
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
    ];
    $i = 0;
    foreach ($lineItems as $li) {
        $params["line_items[$i][quantity]"] = (int)$li['qty'];
        $params["line_items[$i][price_data][currency]"] = $currency;
        $params["line_items[$i][price_data][unit_amount]"] = (int)$li['amount'];
        $params["line_items[$i][price_data][product_data][name]"] = $li['name'];
        $i++;
    }
    foreach ($meta as $k => $v) $params["metadata[$k]"] = (string)$v;
    [$ok, $body] = lt_stripe_call('POST', 'checkout/sessions', $params);
    if (!$ok) return [false, '', '', $body['error']['message'] ?? ($body['error'] ?? 'Stripe error')];
    return [true, $body['url'] ?? '', $body['id'] ?? '', ''];
}

function lt_stripe_get_session(string $id): array {
    [$ok, $body] = lt_stripe_call('GET', 'checkout/sessions/' . rawurlencode($id));
    return [$ok, $body];
}

/** Refund the payment behind a Checkout Session. Returns [ok, refundId, err]. */
function lt_stripe_refund(string $sessionId) {
    if ($sessionId === '') return [false, '', 'No payment reference'];
    [$ok, $sess] = lt_stripe_get_session($sessionId);
    if (!$ok) return [false, '', 'Could not load payment'];
    $pi = $sess['payment_intent'] ?? '';
    if ($pi === '') return [false, '', 'No payment intent — nothing to refund'];
    [$ok2, $body] = lt_stripe_call('POST', 'refunds', ['payment_intent' => $pi]);
    if (!$ok2) return [false, '', $body['error']['message'] ?? 'Refund failed'];
    return [in_array(($body['status'] ?? ''), ['succeeded','pending'], true), $body['id'] ?? '', ''];
}
