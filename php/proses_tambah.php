<?php
session_start();
require 'php.php';

// Periksa apakah user sudah login dan berhak (penjual / admin)
if (!isset($_SESSION['userid']) || !in_array($_SESSION['level'], ['penjual', 'admin'])) {
    header("Location: ../login/login_form.php");
    exit;
}

if (isset($_POST["name"]) && isset($_POST["description"]) && isset($_POST["price"]) && isset($_POST["category"])) {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = intval(preg_replace('/[^0-9]/', '', $_POST["price"]));
    $category = trim($_POST["category"]);
    $satuan = isset($_POST["satuan"]) ? trim($_POST["satuan"]) : '';

    $target_file = "";

    // Upload Gambar dengan validasi ekstensi aman
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext)) {
            $image_clean = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["image"]["name"]));
            $image_name = time() . '_' . $image_clean;
            $target_dir1 = "../imgs/";

            if (!file_exists($target_dir1)) {
                mkdir($target_dir1, 0777, true);
            }

            $target_file = "./imgs/" . $image_name;
            $destination = $target_dir1 . $image_name;
            move_uploaded_file($_FILES["image"]["tmp_name"], $destination);
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category, satuan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $name, $description, $price, $target_file, $category, $satuan);
    $stmt->execute();
    $stmt->close();

    if (isset($_SESSION['level']) && $_SESSION['level'] === 'admin') {
        header("Location: ../admin/products.php");
    } else {
        header("Location: ../index_p.php");
    }
    exit;
}
?>
