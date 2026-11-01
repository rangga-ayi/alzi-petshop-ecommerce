<?php
require_once '../midtrans-php/Midtrans.php'; // Pastikan path ke autoload benar

// Set konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-hI81QFjkGWJvQmZhcWea5GUU';
\Midtrans\Config::$isProduction = false; // false untuk Sandbox
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Data transaksi
$transaction_details = [
    'order_id' => uniqid(), // Buat ID order unik
    'gross_amount' => 150000, // Jumlah total pembayaran
];

// Data item yang dibeli
$item_details = [
    [
        'id' => 'item1',
        'price' => 50000,
        'quantity' => 1,
        'name' => "Produk A"
    ],
    [
        'id' => 'item2',
        'price' => 100000,
        'quantity' => 1,
        'name' => "Produk B"
    ]
];

// Data pelanggan
$customer_details = [
    'first_name' => 'Rangga',
    'last_name' => '2',
    'email' => 'user@example.com',
    'phone' => '08123456789'
];

// Parameter Snap
$params = [
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details
];

// Buat URL Snap
try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo "Buka invoice pembayaran di sini: <a href='https://app.sandbox.midtrans.com/snap/v2/vtweb/$snapToken'>Bayar Sekarang</a>";
} catch (Exception $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>