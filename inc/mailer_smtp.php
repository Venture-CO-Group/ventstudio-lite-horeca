<?php
/* VentStudio — minimal SMTP client (no dependencies). HTML email via SMTP or
   PHP mail() fallback. Config from .env (LT_SMTP_*). */
require_once __DIR__ . '/config.php';

function lt_smtp_conf() {
    return [
        'host' => env('LT_SMTP_HOST', ''),
        'port' => (int)env('LT_SMTP_PORT', '587'),
        'secure' => strtolower(env('LT_SMTP_SECURE', 'tls')),   // '', 'tls', 'ssl'
        'user' => env('LT_SMTP_USER', ''),
        'pass' => env('LT_SMTP_PASS', ''),
        'from' => env('LT_MAIL_FROM', 'orders@yourvenue.co.uk'),
        'fromName' => env('LT_MAIL_FROM_NAME', 'VentStudio Street Food'),
    ];
}

function lt_mail_send($to, string $subject, string $html, string $replyTo = '') {
    $c = lt_smtp_conf();
    if ($c['host'] !== '' && $c['user'] !== '') {
        return lt_smtp_raw($c, $to, $subject, $html, $replyTo);
    }
    // Fallback: PHP mail()
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n"
             . 'From: ' . $c['fromName'] . ' <' . $c['from'] . ">\r\n";
    if ($replyTo) $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    return [@mail(is_array($to) ? implode(',', $to) : $to, $subject, $html, $headers), 'mail() fallback'];
}

function lt_smtp_raw(array $c, $to, string $subject, string $html, string $replyTo = '') {
    $to = is_array($to) ? $to : [$to];
    $host = $c['secure'] === 'ssl' ? 'ssl://' . $c['host'] : $c['host'];
    $fp = @fsockopen($host, $c['port'], $errno, $errstr, 20);
    if (!$fp) return [false, "connect failed: $errstr"];
    stream_set_timeout($fp, 20);
    $read = function () use ($fp) { $d = ''; while ($line = fgets($fp, 515)) { $d .= $line; if (isset($line[3]) && $line[3] === ' ') break; } return $d; };
    $cmd  = function ($s) use ($fp, $read) { fwrite($fp, $s . "\r\n"); return $read(); };
    $read();
    $ehlo = $cmd('EHLO example.com');
    if ($c['secure'] === 'tls') {
        $cmd('STARTTLS');
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) { fclose($fp); return [false, 'TLS failed']; }
        $cmd('EHLO example.com');
    }
    if ($c['user'] !== '') {
        $cmd('AUTH LOGIN');
        $cmd(base64_encode($c['user']));
        $r = $cmd(base64_encode($c['pass']));
        if (strpos($r, '235') === false) { fclose($fp); return [false, 'auth failed: ' . trim($r)]; }
    }
    $cmd('MAIL FROM:<' . $c['from'] . '>');
    foreach ($to as $rcpt) $cmd('RCPT TO:<' . $rcpt . '>');
    $r = $cmd('DATA');
    if (strpos($r, '354') === false) { fclose($fp); return [false, 'DATA rejected: ' . trim($r)]; }
    $headers  = 'From: ' . lt_mime_name($c['fromName']) . ' <' . $c['from'] . ">\r\n";
    $headers .= 'To: ' . implode(', ', $to) . "\r\n";
    if ($replyTo) $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    $headers .= 'Subject: ' . lt_mime_name($subject) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'Date: ' . date('r') . "\r\n";
    $body = preg_replace('/^\./m', '..', $html);
    $r = $cmd($headers . "\r\n" . $body . "\r\n.");
    $cmd('QUIT'); fclose($fp);
    return [strpos($r, '250') !== false, trim($r)];
}
function lt_mime_name($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; }
