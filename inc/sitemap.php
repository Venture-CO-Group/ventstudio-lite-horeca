<?php
require_once __DIR__ . '/store.php';
header('Content-Type: application/xml; charset=UTF-8');
$base = 'https://example.com';
$langs = ['en', 'hu', 'es'];
$routes = ['', 'about', 'team', 'blog', 'faq', 'contact', 'book-a-demo', 'legal/terms', 'legal/privacy', 'legal/cookies'];
foreach (lt_posts_published() as $p) $routes[] = 'blog/' . ($p['slug'] ?? '');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemap.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($routes as $r) {
    foreach ($langs as $l) {
        $loc = $base . '/' . $l . ($r !== '' ? '/' . $r : '/');
        echo "<url><loc>" . htmlspecialchars($loc, ENT_QUOTES) . "</loc>";
        foreach ($langs as $al) {
            $alt = $base . '/' . $al . ($r !== '' ? '/' . $r : '/');
            echo '<xhtml:link rel="alternate" hreflang="' . $al . '" href="' . htmlspecialchars($alt, ENT_QUOTES) . '"/>';
        }
        echo "</url>\n";
    }
}
echo '</urlset>';
