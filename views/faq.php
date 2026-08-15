<?php
$META_TITLE = tg('nav.faq'); $META_DESC = tg('faq.intro');
require __DIR__ . '/../inc/head.php';
$items = (array)g('faq.items');
?>
<main class="page faq-page">
  <section class="page-hero"><div class="wrap">
    <h1 class="display"><?= e(tg('faq.title')) ?></h1>
    <p class="page-lead"><?= e(tg('faq.intro')) ?></p>
  </div></section>
  <section class="section"><div class="wrap faq-list">
    <?php foreach ($items as $f): ?>
      <details class="faq-item">
        <summary><?= e(t($f['q'] ?? '')) ?></summary>
        <div class="faq-a"><?= nl2br(e(t($f['a'] ?? ''))) ?></div>
      </details>
    <?php endforeach; ?>
  </div></section>
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"FAQPage","mainEntity":[<?php
    $out=[]; foreach($items as $f){ $out[]=json_encode(['@type'=>'Question','name'=>t($f['q']??''),'acceptedAnswer'=>['@type'=>'Answer','text'=>t($f['a']??'')]],JSON_UNESCAPED_UNICODE); }
    echo implode(',',$out);
  ?>]}
  </script>
</main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
