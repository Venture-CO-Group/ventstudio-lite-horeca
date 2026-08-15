<?php /* footer + cart drawer + contact popup + cookie banner + scripts */
$soc   = (array)settings('social');
$phone = settings('phone', '');
$wa    = settings('whatsapp', '');
$email = settings('email', '');
$je    = settings('justEatUrl', '');
$dl    = settings('deliverooUrl', '');
$ig = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>';
$fb = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v6h3v-6h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg>';
?>
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-col footer-brand">
      <a href="<?= url('') ?>" class="footer-lockup">
        <img src="/assets/img/brand/logo.svg" alt="" class="footer-badge">
        <span class="footer-wordmark"><?= e(g('footer.companyName') ?: settings('siteName','VentStudio')) ?></span>
      </a>
      <p class="footer-blurb"><?= e(tg('footer.blurb')) ?></p>
      <div class="footer-social">
        <?php if (!empty($soc['instagram'])): ?><a href="<?= e($soc['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram"><?= $ig ?></a><?php endif; ?>
        <?php if (!empty($soc['facebook'])): ?><a href="<?= e($soc['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook"><?= $fb ?></a><?php endif; ?>
      </div>
      <a href="https://ratings.food.gov.uk" target="_blank" rel="noopener" class="fhrs-badge" aria-label="Food Hygiene Rating: 5 - Very Good">
        <img src="/assets/img/brand/fhrs-5.svg" alt="Food Hygiene Rating 5 — Very Good">
      </a>
    </div>
    <div class="footer-col">
      <h4>Order</h4>
      <a href="<?= url('menu') ?>">Order online</a>
      <?php if ($phone): ?><a href="tel:<?= e(str_replace(' ','',$phone)) ?>">Call <?= e($phone) ?></a><?php endif; ?>
      <?php if ($wa): ?><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp us</a><?php endif; ?>
      <?php if ($je): ?><a href="<?= e($je) ?>" target="_blank" rel="noopener">Just Eat</a><?php endif; ?>
      <?php if ($dl): ?><a href="<?= e($dl) ?>" target="_blank" rel="noopener">Deliveroo</a><?php endif; ?>
    </div>
    <div class="footer-col">
      <h4>Explore</h4>
      <a href="<?= url('menu') ?>">Menu</a>
      <?php if (settings('showAllergens', false)): ?><a href="<?= url('allergens') ?>">Allergens</a><?php endif; ?>
      <a href="<?= url('about') ?>">About</a>
      <a href="<?= url('contact') ?>">Find us</a>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <a href="<?= url('legal/terms') ?>">Terms</a>
      <a href="<?= url('legal/cookies') ?>">Cookies</a>
      <a href="<?= url('policies') ?>">Privacy</a>
      <a href="#" id="openCookiePrefs">Cookie settings</a>
    </div>
  </div>
  <div class="footer-base">
    <span><?= date('Y') ?> <?= e(g('footer.companyName') ?: 'VentStudio Street Food') ?>. All rights reserved.<br>
      <span class="footer-legal"><?= e(settings('legalName','Example Trading Ltd')) ?> (<?= e(settings('legalForm','sole trader')) ?>) · <?= e(settings('address','1 Example Street, Your City, AB1 2CD')) ?></span></span>
    <div class="footer-pay">
      <span class="footer-pay-label">We accept</span>
      <span class="pay-badge" title="Visa"><svg viewBox="0 0 48 30" width="42" height="26"><rect width="48" height="30" rx="4" fill="#fff"/><text x="24" y="20" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-style="italic" font-size="12" fill="#1A1F71">VISA</text></svg></span>
      <span class="pay-badge" title="Mastercard"><svg viewBox="0 0 48 30" width="42" height="26"><rect width="48" height="30" rx="4" fill="#fff"/><circle cx="20" cy="15" r="8" fill="#EB001B"/><circle cx="28" cy="15" r="8" fill="#F79E1B" fill-opacity="0.9"/></svg></span>
    </div>
    <span class="footer-mono"><?= e(settings('footerTagline', 'EST. 2026')) ?></span>
  </div>
</footer>

<!-- Cart drawer -->
<div class="cart-drawer" id="cartDrawer" aria-hidden="true">
  <div class="cart-overlay" data-cart-close></div>
  <aside class="cart-panel" role="dialog" aria-modal="true" aria-label="Your order">
    <div class="cart-head">
      <h3>Your order</h3>
      <button class="cart-x" data-cart-close aria-label="Close">&times;</button>
    </div>
    <div class="cart-items" id="cartItems"></div>
    <div class="cart-empty" id="cartEmpty">
      <p>Your bag is empty.</p>
      <a class="btn btn-primary" href="<?= url('menu') ?>" data-cart-close>Browse the menu</a>
    </div>
    <div class="cart-foot" id="cartFoot" hidden>
      <div class="cart-total"><span>Total</span><strong id="cartTotal"><?= e(settings('currencySymbol','£')) ?>0.00</strong></div>
      <a class="btn btn-primary btn-block" href="<?= url('order') ?>">Checkout</a>
      <p class="cart-note">Collection from the van · we'll text you when it's ready</p>
    </div>
  </aside>
</div>

<!-- Cookie consent banner -->
<div class="cookie" id="cookieBanner" hidden>
  <p><strong>We value your privacy</strong> — we use cookies to run this site and, with your consent, to measure traffic. <a href="<?= url('legal/cookies') ?>">Cookie Policy</a></p>
  <div class="cookie-btns">
    <button class="btn btn-ghost" id="cookieEssential">Essential only</button>
    <button class="btn btn-primary" id="cookieAll">Accept all</button>
  </div>
</div>

<?php
/* order rules: which items are extras / bases / toppings (for cart popups & gating) */
$LT_rules = ['extras'=>[], 'extraItems'=>[], 'bases'=>[], 'baseNames'=>(object)[],
             'toppings'=>['crepe-topping'=>'nutella-crepe','waffle-topping'=>'nutella-waffle'],
             'toppingFor'=>(object)[], 'baseNamesArr'=>[]];
$LT_allItems = [];
foreach ((array)g('menu.groups') as $grp) {
    foreach ((array)($grp['items'] ?? []) as $it) { $LT_allItems[$it['slug']] = $it; }
    if (($grp['id'] ?? '') === 'extras') {
        foreach ((array)($grp['items'] ?? []) as $it) {
            $LT_rules['extras'][] = $it['slug'];
            $LT_rules['extraItems'][] = ['slug'=>$it['slug'],'name'=>$it['name'],'price'=>(float)$it['price']];
        }
    }
    if (in_array($grp['id'] ?? '', ['burgers','wraps','boxes'], true)) {
        foreach ((array)($grp['items'] ?? []) as $it) {
            $LT_rules['bases'][] = $it['slug'];
            $LT_rules['baseNamesArr'][$it['slug']] = $it['name'];
        }
    }
    /* loaded fries is also a valid target for extras */
    foreach ((array)($grp['items'] ?? []) as $it) {
        if (($it['slug'] ?? '') === 'loaded-fries') { $LT_rules['bases'][] = 'loaded-fries'; $LT_rules['baseNamesArr']['loaded-fries'] = $it['name']; }
    }
}
$LT_rules['baseNames'] = $LT_rules['baseNamesArr']; unset($LT_rules['baseNamesArr']);
/* base crepe/waffle -> its topping (for the "want a topping?" popup) */
$LT_topFor = [];
foreach ($LT_rules['toppings'] as $topSlug => $baseSlug) {
    if (isset($LT_allItems[$topSlug])) {
        $t = $LT_allItems[$topSlug];
        $LT_topFor[$baseSlug] = ['slug'=>$t['slug'],'name'=>$t['name'],'price'=>(float)$t['price']];
    }
}
$LT_rules['toppingFor'] = $LT_topFor ?: (object)[];
?>
<script>window.LT = window.LT || {}; window.LT.menuUrl = "<?= url('menu') ?>"; window.LT.orderUrl = "<?= url('order') ?>"; window.LT.currency = "<?= e(settings('currencySymbol','£')) ?>"; window.LT.rules = <?= json_encode($LT_rules) ?>;</script>
<script src="/assets/js/cart.js" defer></script>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
