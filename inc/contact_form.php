<?php /* Reusable contact form. $formSource optional (popup|contact|demo). */
$src = $formSource ?? 'popup';
?>
<form class="lt-form" action="<?= url('contact-submit') ?>" method="post">
  <input type="hidden" name="source" value="<?= e($src) ?>">
  <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
  <input type="text" name="name" required placeholder="<?= e(tg('contact.fName')) ?>">
  <input type="email" name="email" required placeholder="<?= e(tg('contact.fEmail')) ?>">
  <input type="text" name="company" placeholder="<?= e(tg('contact.fCompany')) ?>">
  <textarea name="message" rows="4" placeholder="<?= e(tg('contact.fGoals')) ?>"></textarea>
  <button type="submit" class="btn btn-magenta btn-block"><?= e(tg('contact.fSubmit')) ?></button>
  <p class="form-consent"><?= e(tg('contact.consent')) ?></p>
</form>
