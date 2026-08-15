<?php
/* Retired page (was a Hungarian SimplePay/OTP statement from a previous deployment).
   VentStudio takes payments via Stripe. Redirect to the privacy policy. */
header('Location: ' . url('policies'), true, 301);
exit;
