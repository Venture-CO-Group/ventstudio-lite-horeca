<?php
/* Minimal SMTP client (AUTH LOGIN, TLS/SSL). Settings from admin (settings.smtp),
   falls back to PHP mail() when no SMTP host is configured. */
require_once __DIR__ . '/store.php';

function lt_smtp_settings() {
    $C = lt_content_load();
    $s = $C['settings']['smtp'] ?? [];
    return [
        'host'   => trim($s['host'] ?? ''),
        'port'   => (int)($s['port'] ?? 587),
        'secure' => strtolower(trim($s['secure'] ?? 'tls')), // tls | ssl | none
        'user'   => trim($s['user'] ?? ''),
        'pass'   => (string)($s['pass'] ?? ''),
        'from'   => trim($s['from'] ?? '') ?: LT_MAIL_FROM,
        'to'     => trim($s['to'] ?? '') ?: LT_MAIL_TO,
    ];
}

function lt_send_mail($to, $subject, $body, $replyTo = '') {
    $s = lt_smtp_settings();
    if ($s['host'] === '') {
        $headers = 'From: ' . $s['from'] . "\r\n" . ($replyTo ? "Reply-To: $replyTo\r\n" : '') . "Content-Type: text/plain; charset=UTF-8";
        return @mail($to, $subject, $body, $headers);
    }
    $timeout = 12;
    $remote = ($s['secure'] === 'ssl' ? 'ssl://' : '') . $s['host'];
    $fp = @stream_socket_client("$remote:{$s['port']}", $errno, $errstr, $timeout);
    if (!$fp) return false;
    stream_set_timeout($fp, $timeout);
    $read = function () use ($fp) { $d = ''; while ($l = fgets($fp, 515)) { $d .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $d; };
    $cmd  = function ($c) use ($fp, $read) { fwrite($fp, $c . "\r\n"); return $read(); };
    $ok   = function ($r, $codes) { return in_array((int)substr($r, 0, 3), (array)$codes, true); };

    if (!$ok($read(), 220)) { fclose($fp); return false; }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $r = $cmd("EHLO $host");
    if ($s['secure'] === 'tls') {
        if (!$ok($cmd('STARTTLS'), 220)) { fclose($fp); return false; }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
        $r = $cmd("EHLO $host");
    }
    if ($s['user'] !== '') {
        if (!$ok($cmd('AUTH LOGIN'), 334)) { fclose($fp); return false; }
        if (!$ok($cmd(base64_encode($s['user'])), 334)) { fclose($fp); return false; }
        if (!$ok($cmd(base64_encode($s['pass'])), 235)) { fclose($fp); return false; }
    }
    if (!$ok($cmd('MAIL FROM:<' . $s['from'] . '>'), 250)) { fclose($fp); return false; }
    if (!$ok($cmd('RCPT TO:<' . $to . '>'), [250, 251])) { fclose($fp); return false; }
    if (!$ok($cmd('DATA'), 354)) { fclose($fp); return false; }
    $subjectEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $msg  = "From: VentStudio <{$s['from']}>\r\nTo: <$to>\r\nSubject: $subjectEnc\r\n";
    if ($replyTo) $msg .= "Reply-To: <$replyTo>\r\n";
    $msg .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
    $msg .= preg_replace('/^\./m', '..', $body) . "\r\n.";
    if (!$ok($cmd($msg), 250)) { fclose($fp); return false; }
    $cmd('QUIT'); fclose($fp);
    return true;
}
