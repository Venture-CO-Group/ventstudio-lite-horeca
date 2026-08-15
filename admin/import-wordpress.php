<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_layout.php';
lt_require_owner();
$csrf = lt_csrf(); $report = []; $done = false;

function lt_wp_clean_body($html) {
    $html = preg_replace('/\[[^\]]*\]/', '', (string)$html);                       // WPBakery shortcodes
    $html = preg_replace('/<p>[A-Za-z0-9+\/=]{80,}<\/p>/', '', $html);             // stray base64 blobs
    $html = preg_replace('/<h3>\s*Follow us.*$/is', '', $html);                     // trailing social footer
    $html = preg_replace('/<(script|style|iframe|form)[^>]*>.*?<\/\1>/is', '', $html);
    $html = preg_replace('/<p>\s*<\/p>/', '', $html);
    return trim($html);
}

function wp_get($base, $path) {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>25, CURLOPT_HEADER=>true,
        CURLOPT_USERAGENT=>'VentStudioImporter/1.0', CURLOPT_FOLLOWLOCATION=>true]);
    $res = curl_exec($ch); $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($res === false) return [null, [], 0];
    $head = substr($res, 0, $hs); $body = substr($res, $hs);
    $total = 0; if (preg_match('/x-wp-total:\s*(\d+)/i', $head, $m)) $total = (int)$m[1];
    return [json_decode($body, true), $head, $total, $code];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && lt_check_csrf($_POST['csrf'] ?? '')) {
    $base = rtrim(trim($_POST['site'] ?? ''), '/');
    $langs = array_values(array_filter(array_map('trim', explode(',', $_POST['langs'] ?? 'en')))); // e.g. en,hu,es
    if (!$langs) $langs = ['en'];
    if (!preg_match('~^https?://~', $base)) { $report[] = 'Enter a full site URL, e.g. https://example.com'; }
    else {
        $byGroup = []; // translation group -> [lang => postdata]
        $imported = 0; $seen = 0;
        foreach ($langs as $lang) {
            $page = 1;
            while ($page <= 10) {
                $q = "/wp-json/wp/v2/posts?per_page=50&page=$page&_embed=1" . ($lang !== 'en' ? "&lang=$lang" : "");
                list($items, $head, $total, $code) = wp_get($base, $q);
                if (!is_array($items) || !$items) break;
                foreach ($items as $it) {
                    $seen++;
                    $slug = $it['slug'] ?? '';
                    $title = lt_clean_plain($it['title']['rendered'] ?? '');
                    $body = lt_clean_wp_html(lt_wp_clean_body($it['content']['rendered'] ?? ''));
                    $excerpt = lt_clean_plain($it['excerpt']['rendered'] ?? '');
                    if ($excerpt === '') $excerpt = lt_excerpt_from($body);
                    $date = substr($it['date'] ?? '', 0, 10);
                    $cover = '';
                    if (!empty($it['_embedded']['wp:featuredmedia'][0]['source_url'])) $cover = $it['_embedded']['wp:featuredmedia'][0]['source_url'];
                    // translation grouping (WPML/Polylang REST)
                    $gid = $it['id'];
                    if (!empty($it['translations']) && is_array($it['translations'])) { $ids = array_values($it['translations']); sort($ids); $gid = 'g' . implode('-', $ids); }
                    if (!isset($byGroup[$gid])) $byGroup[$gid] = ['slug'=>$slug, 'date'=>$date, 'cover'=>$cover, 'category'=>'', 'published'=>true, 'readMin'=>max(1, round(str_word_count(strip_tags($body))/200)), 'title'=>['en'=>'','hu'=>'','es'=>''], 'excerpt'=>['en'=>'','hu'=>'','es'=>''], 'body'=>['en'=>'','hu'=>'','es'=>'']];
                    $byGroup[$gid]['title'][$lang] = $title;
                    $byGroup[$gid]['excerpt'][$lang] = $excerpt;
                    $byGroup[$gid]['body'][$lang] = $body;
                    if ($lang === $langs[0]) { $byGroup[$gid]['slug'] = $slug; $byGroup[$gid]['date'] = $date; $byGroup[$gid]['cover'] = $cover; }
                }
                if (count($items) < 50) break;
                $page++;
            }
        }
        foreach ($byGroup as $p) {
            // fallback: if only one lang filled, copy into en so the site always shows something
            foreach (['hu','es'] as $l) if ($p['title'][$l] === '' && $p['title']['en'] !== '') { /* leave empty -> falls back to en at render */ }
            if ($p['title']['en'] === '') { foreach (['hu','es'] as $l) if ($p['title'][$l] !== '') { $p['title']['en']=$p['title'][$l]; $p['excerpt']['en']=$p['excerpt'][$l]; $p['body']['en']=$p['body'][$l]; break; } }
            if (($p['slug'] ?? '') !== '') { lt_post_save($p); $imported++; }
        }
        $report[] = "Fetched $seen post(s) across languages [" . implode(', ', $langs) . "]. Imported/updated $imported unique post(s).";
        $report[] = "Covers are linked from the source site. You can re-upload them locally later for best performance.";
        lt_audit('wp.import', $imported.' posts');
        $done = true;
    }
}
lt_admin_head('Import WordPress'); lt_admin_sidebar('blog');
lt_admin_top('Blog', 'Import from WordPress');
?>
<div class="admin-body">
  <div class="notice">Pulls posts from a WordPress site via its REST API (<code>/wp-json/wp/v2/posts</code>) into <strong>lt_posts</strong>. If the site is multilingual (WPML/Polylang) list the language codes and matching translations are merged automatically. Otherwise import <code>en</code> only and translate later in the Blog editor.</div>
  <?php foreach ($report as $r): ?><div class="notice"><?= htmlspecialchars($r) ?></div><?php endforeach; ?>
  <form method="post" class="form-row">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input name="site" placeholder="https://example.com" value="https://example.com" style="min-width:320px" required>
    <input name="langs" placeholder="en,hu,es" value="en,hu,es" style="width:160px">
    <button class="btn-studio primary" type="submit">Import posts</button>
  </form>
  <?php if ($done): ?><a class="btn-studio" href="posts.php">→ Back to Blog</a><?php endif; ?>
</div>
<?php lt_admin_foot();
