<?php
/* VentStudio — front controller (English-only, clean URLs).
   All requests route through here (see .htaccess). */

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = trim(rawurldecode($uri), '/');           // e.g. "menu" or "blog/my-post"
$segs = $path === '' ? [] : explode('/', $path);

require __DIR__ . '/inc/bootstrap.php';
$GLOBALS['C'] = $C;
$GLOBALS['ROUTE_PATH'] = $path;

/* ---- locale-agnostic POST / API endpoints ---- */
$m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($path === 'newsletter'      && $m === 'POST') { require __DIR__ . '/inc/handle_newsletter.php'; exit; }
if ($path === 'contact-submit'  && $m === 'POST') { require __DIR__ . '/inc/handle_contact.php';    exit; }
if ($path === 'checkout'        && $m === 'POST') { require __DIR__ . '/inc/handle_checkout.php';   exit; }

/* db-managed redirects (optional) */
if ($r = lt_redirect_for('/' . $path)) {
    header('Location: ' . $r['to_path'], true, (int)$r['code'] ?: 301); exit;
}

/* routing table */
$route = $segs[0] ?? '';
$view = null; $PAGE = '';

switch ($route) {
    case '':              $view = 'home.php';          $PAGE = 'home'; break;
    case 'menu':          $view = 'menu.php';          $PAGE = 'menu'; break;
    case 'allergens':     $view = 'allergens.php';     $PAGE = 'allergens'; break;
    case 'order':         $view = 'checkout.php';      $PAGE = 'order'; break;
    case 'order-success': $view = 'order-success.php'; $PAGE = 'order'; break;
    case 'invoice':       $view = 'invoice.php';       $PAGE = 'invoice'; break;
    case 'invoice-pdf':   $view = 'invoice-pdf.php';   $PAGE = 'invoice'; break;
    case 'about':         $view = 'about.php';         $PAGE = 'about'; break;
    case 'contact':       $view = 'contact.php';       $PAGE = 'contact'; break;
    case 'team':          $view = 'team.php';          $PAGE = 'team'; break;
    case 'faq':           $view = 'faq.php';           $PAGE = 'faq'; break;
    case 'blog':
        if (isset($segs[1]) && $segs[1] !== '') { $GLOBALS['POST_SLUG'] = $segs[1]; $view = 'post.php'; $PAGE = 'blog'; }
        else { $view = 'blog.php'; $PAGE = 'blog'; }
        break;
    case 'legal':
        $sub = $segs[1] ?? '';
        if ($sub === 'terms')        { $view = 'terms.php';   $PAGE = 'legal'; }
        elseif ($sub === 'cookies')  { $view = 'cookies.php'; $PAGE = 'legal'; }
        elseif ($sub === 'privacy')  { header('Location: ' . url('policies'), true, 301); exit; }
        break;
    case 'policies':   $view = 'policies.php'; $PAGE = 'legal'; break;
    case 'sitemap.xml': require __DIR__ . '/inc/sitemap.php'; exit;
}

/* custom (block-built) pages — fall through when no fixed route matched */
if ($view === null && $route !== '') {
    $cp = lt_page_by_slug($route);
    if ($cp && lt_page_is_live($cp)) { $GLOBALS['CUSTOM_PAGE'] = $cp; $view = 'custom.php'; $PAGE = 'custom'; }
}

if ($view === null) { http_response_code(404); $view = '404.php'; $PAGE = '404'; }
else { http_response_code(200); }   // ensures 200 even when reached via ErrorDocument fallback
$GLOBALS['PAGE'] = $PAGE;

/* quick routing diagnostic: /?__ltdiag=1 (or /menu?__ltdiag=1) */
if (isset($_GET['__ltdiag'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "VentStudio router OK\n";
    echo 'request_uri: ' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    echo 'parsed path: ' . $path . "\n";
    echo 'route: ' . ($route === '' ? '(home)' : $route) . "\n";
    echo 'view: ' . $view . "\n";
    echo 'php: ' . PHP_VERSION . "\n";
    echo 'rewrite_seen: ' . (isset($_SERVER['REDIRECT_STATUS']) || isset($_SERVER['REDIRECT_URL']) ? 'yes' : 'maybe-direct') . "\n";
    exit;
}

require __DIR__ . '/views/' . $view;
