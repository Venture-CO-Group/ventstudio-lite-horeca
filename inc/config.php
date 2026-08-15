<?php
/* =========================================================
   VentStudio — configuration.
   Secrets are read from a .env file (see .env.example).
   DEFAULT storage = flat files (content.json + data/posts.json),
   works immediately. Set LT_DB_ENABLED=1 in .env to use MySQL.
   ========================================================= */

/* ---- tiny .env loader (KEY=VALUE, # comments) ---- */
(function () {
    $f = dirname(__DIR__) . '/.env';
    if (!is_file($f)) return;
    foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $k = trim(substr($line, 0, $pos));
        $v = trim(substr($line, $pos + 1));
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'")) $v = substr($v, 1, -1);
        if ($k !== '' && getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
})();

function env(string $k, $default = '') {
    $v = getenv($k);
    return $v === false ? $default : $v;
}

/* ---- Database (optional) ---- */
define('LT_DB_ENABLED', env('LT_DB_ENABLED', '0') === '1');
define('LT_DB_HOST',    env('LT_DB_HOST', 'localhost'));
define('LT_DB_NAME',    env('LT_DB_NAME', ''));
define('LT_DB_USER',    env('LT_DB_USER', ''));
define('LT_DB_PASS',    env('LT_DB_PASS', ''));
define('LT_DB_CHARSET', 'utf8mb4');

/* ---- Integrations ---- */
define('LT_MAILCHIMP_KEY',      env('LT_MAILCHIMP_KEY', ''));      // e.g. xxxx-us21
define('LT_MAILCHIMP_AUDIENCE', env('LT_MAILCHIMP_AUDIENCE', '')); // audience / list id
define('LT_MAIL_TO',            env('LT_MAIL_TO', 'hello@yourvenue.co.uk'));
define('LT_MAIL_FROM',          env('LT_MAIL_FROM', 'no-reply@yourvenue.co.uk'));
define('LT_TURNSTILE_SECRET',   env('LT_TURNSTILE_SECRET', '')); // optional spam protection
