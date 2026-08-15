<?php
/* Block renderer for custom (Elementor-style) pages.
   Each block is an assoc array with a "type" and its props.
   Localized fields are {en,hu,es} pairs and resolved with t(). */

function lt_block_localval($v) { return is_array($v) ? t($v) : (string)($v ?? ''); }

function lt_yt_id($u) {
    $u = trim((string)$u);
    if ($u === '') return '';
    if (preg_match('~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{6,})~', $u, $m)) return $m[1];
    if (preg_match('~^[A-Za-z0-9_-]{6,}$~', $u)) return $u;
    return '';
}

function lt_render_block($b) {
    $type = $b['type'] ?? '';
    switch ($type) {
        case 'heading':
            $tag = in_array($b['level'] ?? 'h2', ['h1','h2','h3','h4'], true) ? $b['level'] : 'h2';
            $txt = lt_block_localval($b['text'] ?? '');
            $align = $b['align'] ?? 'left';
            return '<' . $tag . ' class="blk-heading display" style="text-align:' . e($align) . '">' . e($txt) . '</' . $tag . '>';

        case 'text':
            $html = lt_block_localval($b['html'] ?? '');
            // allow a safe subset of inline formatting authored in the editor
            $html = strip_tags($html, '<p><br><strong><em><b><i><a><ul><ol><li><h2><h3><h4><blockquote><span>');
            return '<div class="blk-text">' . $html . '</div>';

        case 'image':
            $src = $b['src'] ?? '';
            if ($src === '') return '';
            $src = preg_match('~^https?:|^/~', $src) ? $src : '/assets/img/' . $src;
            $alt = lt_block_localval($b['alt'] ?? '');
            $r = !empty($b['rounded']) ? ' blk-rounded' : '';
            return '<figure class="blk-image' . $r . '"><img src="' . e($src) . '" alt="' . e($alt) . '" loading="lazy"></figure>';

        case 'youtube':
            $id = lt_yt_id($b['url'] ?? ($b['id'] ?? ''));
            if ($id === '') return '';
            return '<div class="blk-video"><iframe src="https://www.youtube-nocookie.com/embed/' . e($id) . '" title="YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';

        case 'button':
            $label = lt_block_localval($b['label'] ?? '');
            $url = $b['url'] ?? '#';
            $style = ($b['style'] ?? 'magenta') === 'navy' ? 'btn-navy' : (($b['style'] ?? '') === 'ghost' ? 'btn-ghost' : 'btn-magenta');
            $align = $b['align'] ?? 'left';
            return '<div class="blk-btn" style="text-align:' . e($align) . '"><a class="btn ' . $style . '" href="' . e($url) . '">' . e($label) . '</a></div>';

        case 'spacer':
            $h = (int)($b['size'] ?? 40);
            return '<div class="blk-spacer" style="height:' . $h . 'px"></div>';

        case 'columns':
            $cols = $b['columns'] ?? [];
            $out = '<div class="blk-cols" style="grid-template-columns:repeat(' . max(1, min(4, count($cols))) . ',1fr)">';
            foreach ($cols as $col) {
                $out .= '<div class="blk-col">';
                foreach (($col['blocks'] ?? []) as $inner) $out .= lt_render_block($inner);
                $out .= '</div>';
            }
            return $out . '</div>';

        default:
            return '';
    }
}

function lt_render_blocks($blocks) {
    if (!is_array($blocks)) return '';
    $out = '';
    foreach ($blocks as $b) $out .= lt_render_block($b);
    return $out;
}
