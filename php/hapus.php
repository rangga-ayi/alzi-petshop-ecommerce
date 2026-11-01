<?php
    session_start();
    require 'php.php';

    // Periksa apakah user sudah login dan berhak (penjual / admin)
    if (!isset($_SESSION['userid']) || !in_array($_SESSION['level'], ['penjual', 'admin'])) {
        header("Location: ../login/login_form.php");
        exit;
    }

    if (isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);

        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: ../index_p.php");
            exit;
        } else {
            echo "Error: " . $conn->error;
            $stmt->close();
        }
    } else {
        echo "ID produk tidak ditemukan.";
    }
?>