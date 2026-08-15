<?php
$META_TITLE = 'Terms & Conditions';
require __DIR__ . '/../inc/head.php';
$email = settings('email','hello@yourvenue.co.uk');
$phone = settings('phone','+44 7000 000000');
$legal = settings('legalName','Example Trading Ltd');
$form  = settings('legalForm','sole trader');
$addr  = settings('address','1 Example Street, Your City, AB1 2CD');
?>
<section class="page-hero"><div class="wrap">
  <p class="eyebrow" style="color:var(--griddle)">Legal</p>
  <h1>Terms &amp; Conditions</h1>
  <p>The terms on which we sell and deliver our food. Please read them before ordering. Last updated <?= date('F Y') ?>.</p>
</div></section>
<section class="section"><div class="wrap"><div class="prose">

  <h2>1. Who we are</h2>
  <p>This website (<strong>example.com</strong>) and the "VentStudio Street Food" business are operated by <strong><?= e($legal) ?></strong>, trading as <strong>VentStudio Street Food</strong>, a <?= e($form) ?> established in England. Our trading address is <?= e($addr) ?>. You can contact us at <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> or <?= e($phone) ?> ("we", "us", "our"). References to "you" mean the person placing an order.</p>

  <h2>2. These terms</h2>
  <p>These Terms &amp; Conditions, together with our <a href="<?= url('policies') ?>">Privacy Policy</a> and <a href="<?= url('legal/cookies') ?>">Cookie Policy</a>, govern your use of this website and every order you place. By placing an order you confirm that you accept these terms, are at least 18 years old (or have permission from a responsible adult) and are legally able to enter into a contract.</p>

  <h2>3. Ordering &amp; formation of the contract</h2>
  <p>The display of items on this website is an invitation to treat, not an offer. When you submit an order and payment you make an offer to buy. A legally binding contract is formed only when we send you an <strong>"order received"</strong> confirmation email accepting your order. If we cannot accept or fulfil your order (for example, an item is unavailable, you are outside our delivery area, or the kitchen is closed) we will tell you and refund any payment taken in full.</p>
  <p>We may refuse or cancel an order at our discretion, including where we suspect fraud, where stock is unavailable, or where an order cannot safely be prepared or delivered.</p>

  <h2>4. Prices, VAT &amp; charges</h2>
  <p>All prices are in pounds sterling (£) and are shown on the website and menu. Prices include VAT where applicable. A delivery fee (currently £2.99, free on orders over £30) and a minimum order value (currently £15 for delivery) apply and are shown clearly at checkout before you pay. We may change prices, fees and minimums at any time, but changes do not affect orders already confirmed. We take reasonable care to ensure prices are correct; if we discover an obvious pricing error we will contact you before processing the order.</p>

  <h2>5. Payment</h2>
  <p>We accept payment as follows:</p>
  <ul class="ticks">
    <li><strong>Online:</strong> card payments are taken through <strong>Stripe</strong>'s secure checkout at the time you place your order. You can pay with most major debit and credit cards, including Visa and Mastercard. We do not receive or store your full card details.</li>
    <li><strong>In person:</strong> at the van we accept card payments through a card reader (Stripe Terminal), alongside the payment methods displayed at the van.</li>
  </ul>
  <p>Online payments are processed by Stripe Payments UK Ltd and are subject to Stripe's own terms and privacy policy. By paying you authorise us to take the full order amount, including any delivery fee.</p>

  <h2>6. Delivery</h2>
  <p>We currently deliver within <strong>Your City</strong> (postcodes IP1–IP5). At checkout we validate your postcode; if it is outside our area you can choose collection instead. We aim to deliver within the estimated time shown, but all times are estimates and may vary with weather, demand, traffic and kitchen load. You must provide an accurate delivery address, contact number and any access instructions. We are not responsible for delays or failed deliveries caused by incorrect details, or where no one is available to receive the order after reasonable attempts to contact you; in such cases the order may not be refunded.</p>

  <h2>7. Collection</h2>
  <p>If you choose collection, you may pick your order up from the van at the location and time advised. We will notify you when it is ready. Please collect promptly, as freshly-prepared hot food deteriorates.</p>

  <h2>8. Cancellations, refunds &amp; your rights</h2>
  <p>Our food is freshly prepared to order and is perishable. Under the Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013, the 14-day right to cancel does <strong>not</strong> apply to food or to goods made to your order and liable to deteriorate quickly. Once we have accepted your order and begun preparation, it cannot normally be cancelled.</p>
  <p>Nothing in these terms affects your legal rights under the Consumer Rights Act 2015, including that food must be of satisfactory quality, as described and fit for purpose. If something is wrong with your order, contact us within 24 hours (a photo helps) and we will offer a repair, replacement or refund as appropriate.</p>

  <h2>9. Allergens &amp; food safety</h2>
  <p>Allergen and ingredient information for any item is available on request — please ask before ordering. Our food is prepared in a kitchen/van that handles the 14 regulated allergens, so we cannot guarantee any item is free from traces. If you have a food allergy or intolerance, please tell us in the order notes or contact us before ordering. We operate in line with UK food hygiene and food information law.</p>

  <h2>10. Promotions</h2>
  <p>Any deals, discounts or vouchers are subject to their own terms, cannot be exchanged for cash, and may be withdrawn or amended at any time. Only one offer applies per order unless stated otherwise.</p>

  <h2>11. Intellectual property &amp; acceptable use</h2>
  <p>All content on this website — including the VentStudio name, logo, branding, text, photography and design — is owned by or licensed to us and may not be copied or used without permission. You agree not to misuse the website, attempt to gain unauthorised access, or place fraudulent or automated orders.</p>

  <h2>12. Our liability</h2>
  <p>We do not exclude or limit liability where it would be unlawful to do so, including for death or personal injury caused by our negligence, for fraud, or for breach of your statutory consumer rights. Subject to that, our total liability arising from any order is limited to the amount you paid for that order, and we are not liable for losses that were not foreseeable when the contract was formed.</p>

  <h2>13. Complaints</h2>
  <p>We want you to be happy. Please raise any complaint with us first at <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> and we will do our best to resolve it quickly and fairly.</p>

  <h2>14. Events outside our control</h2>
  <p>We are not liable for failure or delay in performing our obligations where caused by events beyond our reasonable control (including severe weather, breakdown, illness, supplier failure or utility outages). We will let you know and, where appropriate, refund affected orders.</p>

  <h2>15. Changes &amp; governing law</h2>
  <p>We may update these terms from time to time; the version published here when you order is the one that applies. These terms and any dispute are governed by the laws of <strong>England &amp; Wales</strong> and subject to the non-exclusive jurisdiction of its courts.</p>

  <p style="color:#6b5f57;font-size:.9rem;margin-top:26px"><?= e($legal) ?>, trading as VentStudio Street Food · <?= e($addr) ?> · <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
</div></div></section>
<?php require __DIR__ . '/../inc/footer.php'; ?>
