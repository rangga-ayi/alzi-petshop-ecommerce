<?php
include('php/php.php'); // Pastikan file koneksi di-include

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'], $data['product_id'], $data['quantity'])) {
    $userId = intval($data['user_id']);
    $productId = intval($data['product_id']);
    $quantity = intval($data['quantity']);

    // Ambil informasi produk
    $productQuery = "SELECT name, price FROM products WHERE id = ?";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->bind_param("i", $productId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    $product = $productResult->fetch_assoc();

    if ($product) {
        $productName = $product['name'];
        $price = $product['price'] * $quantity;

        // Masukkan data ke tabel order
        $insertQuery = "INSERT INTO `order` (user_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iisid", $userId, $productId, $productName, $quantity, $price);

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $insertStmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Produk tidak ditemukan.']);
    }
    $productStmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap.']);
}

$conn->close();
?>
