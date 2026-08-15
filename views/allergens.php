<?php
if (!settings('showAllergens', false)) { header('Location: ' . url('menu')); exit; }
$META_TITLE = 'Allergens';
$META_DESC = 'Allergen information for the VentStudio Street Food menu.';
require __DIR__ . '/../inc/head.php';
$legend = (array)g('allergenLegend');
$abbr = ['celery'=>'CE','gluten'=>'GL','crustaceans'=>'CR','eggs'=>'EG','fish'=>'FI','lupin'=>'LU',
         'milk'=>'MI','molluscs'=>'MO','mustard'=>'MU','nuts'=>'NU','peanuts'=>'PE','sesame'=>'SE','soya'=>'SO','sulphites'=>'SU'];
$cols = array_keys($abbr);
$groups = (array)g('menu.groups');
?>
<section class="page-hero"><div class="wrap">
  <p class="eyebrow" style="color:var(--griddle)">Eat with confidence</p>
  <h1>Allergen information</h1>
  <p>The 14 legally-declarable allergens for every item on our menu. Made fresh on the van.</p>
</div></section>

<section class="section"><div class="wrap">
  <div class="notice"><strong>Please note:</strong> our food is prepared in a kitchen/van that handles all 14 allergens, so we can't guarantee any dish is completely free from traces. If you have a serious allergy, please tell us in your order notes or call <?= e(settings('phone','+44 7000 000000')) ?> before ordering.</div>

  <div class="alg-legend">
    <?php foreach ($legend as $k=>$label): ?><span><strong><?= e($abbr[$k] ?? '') ?></strong> <?= e($label) ?></span><?php endforeach; ?>
  </div>

  <div class="alg-scroll">
    <table class="alg-table">
      <thead><tr><th class="alg-item">Item</th><?php foreach ($cols as $c): ?><th title="<?= e($legend[$c] ?? '') ?>"><?= e($abbr[$c]) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php foreach ($groups as $grp): ?>
          <tr class="alg-group"><td colspan="<?= count($cols)+1 ?>"><?= e($grp['title']) ?></td></tr>
          <?php foreach ((array)($grp['items'] ?? []) as $it): $a=(array)($it['allergens'] ?? []); ?>
            <tr><td class="alg-item"><?= e($it['name']) ?></td>
              <?php foreach ($cols as $c): ?><td class="<?= in_array($c,$a,true)?'yes':'' ?>"><?= in_array($c,$a,true)?'●':'' ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
