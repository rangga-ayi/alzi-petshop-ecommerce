<?php
require_once dirname(__FILE__) . '/midtrans-php/Midtrans.php';

// Set your Merchant Server Key
\Midtrans\Config::$serverKey = 'SB-Mid-server-hI81QFjkGWJvQmZhcWea5GUU';
// Set your Merchant Client Key
\Midtrans\Config::$clientKey = 'SB-Mid-client-X7zfk0k3aWOJvdhF';
// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
\Midtrans\Config::$isProduction = false;
// Set sanitization on (default)
\Midtrans\Config::$isSanitized = true;
// Set 3DS transaction for credit card to true
\Midtrans\Config::$is3ds = true;
?>
