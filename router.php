<?php
/* Router for PHP's built-in server (local testing only):
     php -S localhost:8000 router.php
   On real Apache/Nginx hosting this file is not used — .htaccess handles routing. */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) return false;   // serve real static assets as-is
require __DIR__ . '/index.php';
