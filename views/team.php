<?php
$META_TITLE = tg('nav.team'); $META_DESC = tg('team.intro');
require __DIR__ . '/../inc/head.php';
$members = array_values((array)g('team.items'));
$accent = null; $flow = [];
foreach ($members as $m) { if (($m['role']['en'] ?? '') === 'Office Manager' || ($m['node'] ?? '') === 'accent') $accent = $m; else $flow[] = $m; }
$rows = array_chunk($flow, 2);
$rowCls = ['row-mid','row-wide','row-narrow','row-wide']; // formation widths per figma
?>
<main class="page team-page">
  <section class="pitch">
    <div class="pitch-inner">
      <img src="/assets/img/brand/field-center.webp" alt="" class="pitch-center" aria-hidden="true">
      <?php foreach ($rows as $ri => $r): ?>
        <div class="pitch-row <?= $rowCls[$ri] ?? '' ?>">
          <?php foreach ($r as $m): ?>
            <div class="member">
              <div class="member-photo"><img src="/assets/img/<?= e($m['photo'] ?? '') ?>" alt="<?= e($m['name'] ?? '') ?>" loading="lazy"></div>
              <div class="member-card">
                <h3><?= e($m['name'] ?? '') ?></h3>
                <p class="member-role"><?= e(t($m['role'] ?? '')) ?></p>
                <?php if (!empty($m['email'])): ?><a class="member-mail" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <?php if ($accent): ?>
        <div class="pitch-row">
          <div class="member">
            <div class="member-dot" aria-hidden="true"></div>
            <div class="member-card">
              <h3><?= e($accent['name'] ?? '') ?></h3>
              <p class="member-role"><?= e(t($accent['role'] ?? '')) ?></p>
              <?php if (!empty($accent['email'])): ?><a class="member-mail" href="mailto:<?= e($accent['email']) ?>"><?= e($accent['email']) ?></a><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php require __DIR__ . '/../inc/footer.php'; ?>
