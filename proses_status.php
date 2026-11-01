<?php
session_start();
include('php/php.php');

if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? htmlspecialchars(trim($_GET['order_id'])) : '';
$new_status = "success";
$new_shipping_status = 2;

// Mulai transaksi untuk memastikan konsistensi data
$conn->begin_transaction();

try {
    // Update transaction_status di tabel transactions
    $sql = "UPDATE transactions SET transaction_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $new_status, $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Gagal memperbarui transaction status: " . $stmt->error);
        }
        $stmt->close();
    } else {
        throw new Exception("Gagal mempersiapkan statement untuk transactions: " . $conn->error);
    }

    // Update status_pengiriman di tabel shipping_detail
    $sql = "UPDATE shipping_detail SET status_pengiriman = 2 WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Gagal memperbarui shipping status: " . $stmt->error);
        }
        $stmt->close();
    } else {
        throw new Exception("Gagal mempersiapkan statement untuk shipping_detail: " . $conn->error);
    }

    // Commit transaksi
    $conn->commit();

    if (!empty($order_id)) {
        header('Location: detail_riwayat.php?order_id=' . urlencode($order_id));
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    echo $e->getMessage();
}

header('Location: index.php');

// Tutup koneksi
$conn->close();
?>