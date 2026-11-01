<?php
// koneksi.php berisi koneksi ke database
include 'php/php.php';

session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

if ($_SESSION['level'] != "pembeli") {
    header("Location: login/login_form.php");
    exit;
}

$user_id = $_SESSION['userid']; // Ambil user ID dari session

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    // Query untuk menghapus item dari tabel cart berdasarkan product_id dan user_id
    $deleteQuery = "DELETE FROM `order` WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $product_id, $user_id);  // "ii" berarti dua parameter bertipe integer
    
    if ($stmt->execute()) {
        // Redirect kembali ke halaman keranjang setelah item berhasil dihapus
        header("Location: index.php");
        exit;
    } else {
        echo "Gagal menghapus item dari keranjang.";
    }
    $stmt->close();
} else {
    echo "ID keranjang tidak ditemukan.";
}
$conn->close();
?>