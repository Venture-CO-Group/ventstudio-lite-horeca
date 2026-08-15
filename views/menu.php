<?php
$META_TITLE = 'Menu'; $META_DESC = tg('menu.intro');
require __DIR__ . '/../inc/head.php';
$allGroups = (array)g('menu.groups');
$groups = [];
foreach ($allGroups as $grp) { $vis = lt_visible_items($grp); if ($vis) { $grp['items'] = $vis; $groups[] = $grp; } }
$accents = ['hotsauce'=>'pill','honey'=>'pill','fresh'=>'pill pill-fresh','berry'=>'pill pill-berry'];
?>
<section class="menu-hero">
  <div class="wrap">
    <p class="eyebrow" style="color:var(--honey)">Grill street food · Your Region</p>
    <h1>The menu.</h1>
    <p><?= e(tg('menu.intro')) ?></p>
  </div>
</section>

<nav class="menu-nav" aria-label="Menu sections">
  <div class="wrap">
    <?php foreach ($groups as $grp): ?>
      <a href="#<?= e($grp['id']) ?>"><?= e($grp['title']) ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<div class="wrap">
<?php foreach ($groups as $grp):
  $pillcls = $accents[$grp['accent'] ?? 'honey'] ?? 'pill'; ?>
  <section class="menu-group" id="<?= e($grp['id']) ?>">
    <h2 style="color:var(--<?= e($grp['accent'] ?? 'hotsauce') ?>)"><?= e($grp['title']) ?></h2>
    <?php if (!empty($grp['note'])): ?><p class="note"><?= e($grp['note']) ?></p><?php endif; ?>
    <div class="menu-grid">
      <?php foreach ((array)($grp['items'] ?? []) as $it):
        $slug = $it['slug']; $im = menu_img($slug); $tags = (array)($it['tags'] ?? []);
        $pre = !empty($it['preorder']); $preH = (int)($it['preorderHours'] ?? 48); ?>
        <div class="dish<?= $pre ? ' dish-preorder' : '' ?>">
          <div class="dish-img<?= $im ? '' : ' ph' ?>">
            <?php if ($im): ?><img src="<?= e($im) ?>" alt="<?= e($it['name']) ?>">
            <?php else: ?><img src="/assets/img/brand/logo.svg" alt="" style="width:44%;margin:auto;height:100%;object-fit:contain"><?php endif; ?>
            <?php if ($pre): ?><span class="dish-badge-pre">⏱ Pre-order · <?= round($preH/24) ?: 2 ?> days ahead</span>
            <?php elseif ($tags): ?><span class="dish-tag <?= $pillcls ?>"><?= e($tags[0]) ?></span><?php endif; ?>
          </div>
          <div class="dish-body">
            <h3><?= e($it['name']) ?></h3>
            <?php if (!empty($it['desc'])): ?><p class="dish-desc"><?= e($it['desc']) ?></p><?php endif; ?>
            <?php if ($pre): ?><p class="dish-pre-note">Pre-order BBQ special — order at least <?= $preH ?> hours ahead.</p><?php endif; ?>
            <div class="dish-foot">
              <span class="<?= $pillcls ?>"><?= e(money($it['price'])) ?></span>
              <button class="add-btn" data-add="<?= e($slug) ?>" data-name="<?= e($it['name']) ?>" data-price="<?= e($it['price']) ?>"<?= $pre ? ' data-preorder="'.$preH.'"' : '' ?>>Add +</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
</div>

<div class="section" style="text-align:center">
  <a class="btn btn-primary" id="cartCta" href="<?= url('order') ?>">Go to checkout</a>
</div>

<?php require __DIR__ . '/../inc/footer.php'; ?>
