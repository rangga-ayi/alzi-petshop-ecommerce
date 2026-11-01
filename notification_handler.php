<?php
require ("php/php.php");
require_once 'midtrans_config.php';
require_once 'midtrans-php/Midtrans.php';

try {
    // Proses notifikasi
    $notification = new \Midtrans\Notification();

    // Mendapatkan data dari notifikasi
    $order_id = $notification->order_id;
    $transaction_status = $notification->transaction_status;
    $payment_type = $notification->payment_type;
    $gross_amount = $notification->gross_amount;

    // Log data transaksi untuk debugging
    file_put_contents("notification_log.txt", "Order ID: $order_id, Status: $transaction_status, Payment Type: $payment_type, Gross Amount: $gross_amount" . PHP_EOL, FILE_APPEND);

    // Update status transaksi di database dengan prepared statement
    $stmt_notif = $conn->prepare("UPDATE transactions SET 
              transaction_status = ?, 
              payment_type = ?, 
              gross_amount = ?, 
              update_time = NOW()
              WHERE order_id = ?");
    $stmt_notif->bind_param("ssds", $transaction_status, $payment_type, $gross_amount, $order_id);
    $stmt_notif->execute();
    $stmt_notif->close();

    // Kirim respons OK ke Midtrans
    echo "OK";

} catch (\Exception $e) {
    // Tangani error dan log
    file_put_contents("notification_log.txt", "ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(400); // Kirim HTTP 400 jika ada error
    echo $e->getMessage();
}