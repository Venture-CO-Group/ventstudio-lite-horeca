<?php
require_once __DIR__ . '/config.php';

/* PDO handle (null when DB disabled or unreachable -> flat-file mode) */
function lt_db() {
    static $pdo = false;
    if ($pdo !== false) return $pdo;
    $pdo = null;
    if (LT_DB_ENABLED) {
        try {
            $dsn = 'mysql:host=' . LT_DB_HOST . ';dbname=' . LT_DB_NAME . ';charset=' . LT_DB_CHARSET;
            $pdo = new PDO($dsn, LT_DB_USER, LT_DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Exception $e) { $pdo = null; }
    }
    return $pdo;
}
function lt_root() { return dirname(__DIR__); }

/* ---------------- site content (one JSON tree) ---------------- */
function lt_content_load() {
    $db = lt_db();
    if ($db) {
        try {
            $row = $db->query("SELECT data FROM lt_content WHERE id=1")->fetch();
            if ($row && $row['data']) { $d = json_decode($row['data'], true); if (is_array($d)) return $d; }
        } catch (Exception $e) {}
    }
    $f = lt_root() . '/content.json'; $df = lt_root() . '/content.default.json';
    $c = is_file($f) ? json_decode((string)file_get_contents($f), true) : null;
    if (!is_array($c) && is_file($df)) $c = json_decode((string)file_get_contents($df), true);
    return is_array($c) ? $c : [];
}
function lt_content_save($data) {
    if (!is_array($data) || !isset($data['settings'])) return [false, 'Invalid content payload'];
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $db = lt_db();
    if ($db) {
        try {
            $st = $db->prepare("INSERT INTO lt_content (id,data) VALUES (1,:d) ON DUPLICATE KEY UPDATE data=:d2");
            $st->execute([':d' => $json, ':d2' => $json]);
            return [true, ''];
        } catch (Exception $e) {
            /* DB unreachable or lt_content table not created yet — fall through to flat-file
               so the admin keeps working even before schema.sql is imported. */
        }
    }
    $t = lt_root() . '/content.json';
    if (is_file($t)) @copy($t, lt_root() . '/content.backup.json');
    $tmp = $t . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false || !@rename($tmp, $t))
        return [false, 'Could not write content.json — make the web folder writable (chmod 664 on content.json, 775 on the folder).'];
    return [true, ''];
}

/* ---------------- blog posts ----------------
   Post: slug, date(YYYY-MM-DD), published(bool), cover, category,
         title{en,hu,es}, excerpt{en,hu,es}, body{en,hu,es}, readMin */
function lt_posts_file() { return lt_root() . '/data/posts.json'; }

/* ---- WordPress / Visual Composer cleanup for imported content ---- */
/** Remove [shortcodes], leftover VC base64 raw blobs and stray smart-quote entities. */
function lt_clean_wp_html($s) {
    $s = (string)$s;
    // strip WordPress / Visual Composer shortcodes: [vc_row], [/vc_column], [vc_single_image …]
    $s = preg_replace('/\[\/?[a-z0-9_][^\]\r\n]{0,200}\]/i', '', $s);

    // protect real images (inline <img> tags and data: URIs) so the base64 sweep never touches them
    $keep = [];
    $protect = function ($m) use (&$keep) { $keep[] = $m[0]; return "\x01KEEP" . (count($keep) - 1) . "\x01"; };
    $s = preg_replace_callback('#<img\b[^>]*>#i', $protect, $s);
    $s = preg_replace_callback('#data:[a-z0-9.+\-]+/[a-z0-9.+\-]+;base64,[A-Za-z0-9+/=]+#i', $protect, $s);

    // remove long base64 blobs (vc_raw_html / vc_raw_js payloads, e.g. "JTND…")
    $s = preg_replace('#[A-Za-z0-9+/]{60,}={0,2}#', '', $s);

    // restore the protected images
    if ($keep) $s = preg_replace_callback('/\x01KEEP(\d+)\x01/', function ($m) use ($keep) { return $keep[(int)$m[1]] ?? ''; }, $s);
    // normalise the smart-quote / dash entities WP used inside attributes
    $s = str_replace(
        ['&#8221;','&#8243;','&#8220;','&#8217;','&#8216;','&#8211;','&#8212;','&#8230;','&#8242;'],
        ['"','"','"',"'","'",'–','—','…',"'"], $s);
    // tidy whitespace
    $s = preg_replace('/[ \t]+\n/', "\n", $s);
    $s = preg_replace('/\n{3,}/', "\n\n", $s);
    return trim($s);
}
/** Fully decode HTML entities for plain-text fields (titles, excerpts).
    Runs twice to catch double-encoded WordPress entities like &amp;#8217;. */
function lt_decode_entities($s) {
    $s = (string)$s;
    for ($i = 0; $i < 2; $i++) {
        $d = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($d === $s) break;
        $s = $d;
    }
    return $s;
}
/** Clean a plain-text field: strip shortcodes/base64/tags and decode all entities. */
function lt_clean_plain($s) {
    return trim(strip_tags(lt_decode_entities(lt_clean_wp_html($s))));
}
/** Build a plain-text excerpt from (possibly HTML) content. */
function lt_excerpt_from($html, $len = 180) {
    $t = trim(preg_replace('/\s+/', ' ', strip_tags((string)$html)));
    if (function_exists('mb_strlen')) {
        if (mb_strlen($t) <= $len) return $t;
        return rtrim(mb_substr($t, 0, $len)) . '…';
    }
    return strlen($t) <= $len ? $t : rtrim(substr($t, 0, $len)) . '…';
}
/** Clean one post's localized excerpt/body across all languages; recompute readMin. */
function lt_post_clean(array $p) {
    foreach (['en','hu','es'] as $l) {
        if (isset($p['title'][$l]))   $p['title'][$l]   = lt_clean_plain($p['title'][$l]);   // decode &#8217; etc.
        if (isset($p['body'][$l]))    $p['body'][$l]    = lt_clean_wp_html($p['body'][$l]);
        if (isset($p['excerpt'][$l])) $p['excerpt'][$l] = lt_clean_plain($p['excerpt'][$l]);
        // if the excerpt is now empty or still messy, derive it from the cleaned body
        $ex = trim($p['excerpt'][$l] ?? '');
        if ($ex === '' && !empty($p['body'][$l])) $p['excerpt'][$l] = lt_excerpt_from($p['body'][$l]);
    }
    // recompute reading time from the longest cleaned body
    $max = 0;
    foreach (['en','hu','es'] as $l) { $w = str_word_count(trim(strip_tags($p['body'][$l] ?? ''))); if ($w > $max) $max = $w; }
    if ($max > 0) $p['readMin'] = max(1, (int)ceil($max / 200));
    return $p;
}

function lt_posts_load_all() {
    $db = lt_db();
    if ($db) {
        try {
            $rows = $db->query("SELECT data FROM lt_posts ORDER BY date DESC, id DESC")->fetchAll();
            $out = []; foreach ($rows as $r) { $d = json_decode($r['data'], true); if (is_array($d)) $out[] = $d; }
            return $out;
        } catch (Exception $e) {}
    }
    $a = is_file(lt_posts_file()) ? json_decode((string)file_get_contents(lt_posts_file()), true) : [];
    if (!is_array($a)) $a = [];
    usort($a, fn($x, $y) => strcmp($y['date'] ?? '', $x['date'] ?? ''));
    return $a;
}
/** A post is publicly live when published, or scheduled with a publish time now in the past. */
function lt_post_is_live($p) {
    $status = $p['status'] ?? (!empty($p['published']) ? 'published' : 'draft');
    if ($status === 'published') return true;
    if ($status === 'scheduled') {
        $t = $p['publishAt'] ?? ($p['date'] ?? '');
        return $t !== '' && strtotime($t) !== false && strtotime($t) <= time();
    }
    return false;
}
function lt_posts_published() {
    return array_values(array_filter(lt_posts_load_all(), 'lt_post_is_live'));
}
function lt_post_by_slug($slug) {
    foreach (lt_posts_load_all() as $p) if (($p['slug'] ?? '') === $slug) return $p;
    return null;
}
function lt_posts_save_all($arr) {
    $json = json_encode(array_values($arr), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $t = lt_posts_file(); $tmp = $t . '.tmp';
    return !(file_put_contents($tmp, $json, LOCK_EX) === false || !@rename($tmp, $t));
}
function lt_post_save($post) {
    $db = lt_db();
    if ($db) {
        try {
            $json = json_encode($post, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $st = $db->prepare("INSERT INTO lt_posts (slug,date,published,cover,category,data)
                VALUES (:s,:d,:p,:c,:cat,:j)
                ON DUPLICATE KEY UPDATE date=:d2,published=:p2,cover=:c2,category=:cat2,data=:j2");
            $pub = !empty($post['published']) ? 1 : 0;
            $st->execute([':s'=>$post['slug'], ':d'=>$post['date'], ':p'=>$pub,
                ':c'=>$post['cover'] ?? '', ':cat'=>$post['category'] ?? '', ':j'=>$json,
                ':d2'=>$post['date'], ':p2'=>$pub, ':c2'=>$post['cover'] ?? '',
                ':cat2'=>$post['category'] ?? '', ':j2'=>$json]);
            return [true, ''];
        } catch (Exception $e) { return [false, $e->getMessage()]; }
    }
    $all = lt_posts_load_all(); $found = false;
    foreach ($all as $i => $p) if (($p['slug'] ?? '') === $post['slug']) { $all[$i] = $post; $found = true; break; }
    if (!$found) $all[] = $post;
    return [lt_posts_save_all($all), ''];
}
function lt_post_delete($slug) {
    $db = lt_db();
    if ($db) { try { $db->prepare("DELETE FROM lt_posts WHERE slug=:s")->execute([':s'=>$slug]); return true; } catch (Exception $e) { return false; } }
    return lt_posts_save_all(array_filter(lt_posts_load_all(), fn($p) => ($p['slug'] ?? '') !== $slug));
}

/* ---------------- custom (block-built) pages ----------------
   Page: slug, title{en,hu,es}, published(bool), updated, blocks[]
   Block: {type, ...localized/props}. Rendered by inc/blocks.php. */
function lt_pages_file() { return lt_root() . '/data/pages.json'; }
function lt_pages_load_all() {
    $f = lt_pages_file();
    $a = is_file($f) ? json_decode((string)file_get_contents($f), true) : [];
    return is_array($a) ? $a : [];
}
function lt_page_by_slug($slug) {
    foreach (lt_pages_load_all() as $p) if (($p['slug'] ?? '') === $slug) return $p;
    return null;
}
function lt_page_is_live($p) { return !empty($p['published']); }
function lt_pages_save_all($arr) {
    $json = json_encode(array_values($arr), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $t = lt_pages_file(); $tmp = $t . '.tmp';
    return !(file_put_contents($tmp, $json, LOCK_EX) === false || !@rename($tmp, $t));
}
function lt_page_save($page) {
    $all = lt_pages_load_all(); $found = false;
    foreach ($all as $i => $p) if (($p['slug'] ?? '') === $page['slug']) { $all[$i] = $page; $found = true; break; }
    if (!$found) $all[] = $page;
    return lt_pages_save_all($all);
}
function lt_page_delete($slug) {
    return lt_pages_save_all(array_filter(lt_pages_load_all(), fn($p) => ($p['slug'] ?? '') !== $slug));
}

/* ---------------- newsletter subscribers ---------------- */
function lt_sub_file() { return lt_root() . '/data/subscribers.jsonl'; }
function lt_subscriber_add($email, $locale, $mcId = '') {
    $db = lt_db();
    if ($db) {
        try {
            $st = $db->prepare("INSERT INTO lt_subscribers (email,locale,status,mailchimp_id,consent_at,created_at)
                VALUES (:e,:l,'subscribed',:m,NOW(),NOW())
                ON DUPLICATE KEY UPDATE status='subscribed', locale=:l2, mailchimp_id=:m2");
            $st->execute([':e'=>$email, ':l'=>$locale, ':m'=>$mcId, ':l2'=>$locale, ':m2'=>$mcId]);
            return true;
        } catch (Exception $e) { return false; }
    }
    $rec = ['email'=>$email, 'locale'=>$locale, 'status'=>'subscribed', 'mailchimp_id'=>$mcId, 'ts'=>date('c')];
    return @file_put_contents(lt_sub_file(), json_encode($rec, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX) !== false;
}
function lt_subscribers_all() {
    $db = lt_db();
    if ($db) { try { return $db->query("SELECT * FROM lt_subscribers ORDER BY created_at DESC")->fetchAll(); } catch (Exception $e) {} }
    $out = []; if (is_file(lt_sub_file())) foreach (file(lt_sub_file(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) { $d = json_decode($l, true); if ($d) $out[] = $d; }
    return array_reverse($out);
}

/* ---------------- contact / demo submissions ---------------- */
function lt_sub_msg_file() { return lt_root() . '/data/submissions.jsonl'; }
function lt_submission_add($rec) {
    $db = lt_db();
    if ($db) {
        try {
            $st = $db->prepare("INSERT INTO lt_submissions (source,name,email,company,message,locale,ip,ua,is_spam,status,created_at)
                VALUES (:s,:n,:e,:c,:m,:l,:ip,:ua,0,'new',NOW())");
            $st->execute([':s'=>$rec['source'], ':n'=>$rec['name'], ':e'=>$rec['email'],
                ':c'=>$rec['company'], ':m'=>$rec['message'], ':l'=>$rec['locale'],
                ':ip'=>$rec['ip'], ':ua'=>$rec['ua']]);
            return true;
        } catch (Exception $e) { return false; }
    }
    $rec['ts'] = date('c'); $rec['status'] = 'new';
    return @file_put_contents(lt_sub_msg_file(), json_encode($rec, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX) !== false;
}
function lt_submissions_all() {
    $db = lt_db();
    if ($db) { try { return $db->query("SELECT * FROM lt_submissions ORDER BY created_at DESC")->fetchAll(); } catch (Exception $e) {} }
    $out = []; if (is_file(lt_sub_msg_file())) foreach (file(lt_sub_msg_file(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) { $d = json_decode($l, true); if ($d) $out[] = $d; }
    return array_reverse($out);
}

/* ---------------- redirects ---------------- */
function lt_redirect_for($path) {
    $db = lt_db();
    if ($db) {
        try {
            $st = $db->prepare("SELECT to_path,code FROM lt_redirects WHERE from_path=:p AND active=1 LIMIT 1");
            $st->execute([':p'=>$path]); $r = $st->fetch();
            if ($r) return $r;
        } catch (Exception $e) {}
    }
    return null;
}
