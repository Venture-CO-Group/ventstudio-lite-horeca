<?php
$META_TITLE = L('Privacy Policy','Adatvédelmi szabályzat','Política de Privacidad');
require __DIR__ . '/../inc/head.php';
$d = g('legal.privacy');
$docs = (array)($d['partnerDocs'] ?? []);
?>
<main class="page legal-page"><section class="section"><div class="wrap legal">
  <h1 class="display"><?= e(t($d['title'] ?? $META_TITLE)) ?></h1>
  <div class="legal-body"><?= t($d['intro'] ?? '') ?></div>
  <?php if ($docs):
    // group consecutive docs by their "group" (partner) label, preserving order
    $groups = []; $order = [];
    foreach ($docs as $doc) {
        $pdf = t($doc['pdf'] ?? ''); if (!$pdf) continue;
        $gk = trim((string)($doc['group'] ?? ''));
        if (!isset($groups[$gk])) { $groups[$gk] = []; $order[] = $gk; }
        $groups[$gk][] = $doc;
    }
  ?>
    <h2 class="display"><?= e(L('Product & partner policies','Termék- és partner-tájékoztatók','Políticas de producto y socios')) ?></h2>
    <div class="policy-groups">
      <?php foreach ($order as $gk): $items = $groups[$gk]; $glogo = ''; foreach ($items as $it0){ if(!empty($it0['logo'])){ $glogo=$it0['logo']; break; } }
        $gsrc = $glogo ? (preg_match('#^https?:|^/#', $glogo) ? $glogo : '/assets/img/' . $glogo) : ''; ?>
        <div class="policy-group">
          <div class="policy-group-head">
            <?php if ($gsrc): ?><img class="doc-logo" src="<?= e($gsrc) ?>" alt="" loading="lazy"><?php endif; ?>
            <?php if ($gk !== ''): ?><h3 class="policy-group-name"><?= e($gk) ?></h3><?php endif; ?>
          </div>
          <ul class="doc-list">
            <?php foreach ($items as $doc): $pdf = t($doc['pdf'] ?? '');
              $ext = preg_match('#^https?:#', $pdf); ?>
              <li class="doc-item">
                <span class="doc-name"><?= e(t($doc['label'] ?? 'Document')) ?></span>
                <a class="doc-dl" href="<?= e($pdf) ?>"<?= $ext ? ' target="_blank" rel="noopener"' : '' ?>><?= e(L('Open','Megnyitás','Abrir')) ?> &nearr;</a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div></section></main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
