<?php
session_start();
require 'php/php.php'; // Koneksi ke database
require 'midtrans_config.php'; // Konfigurasi Midtrans

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

$user_id = $_SESSION['userid'];

// Ambil informasi user dari tabel tbluser dan user_detail
$query_user = "
    SELECT u.nama, u.email, d.alamat, d.no_telepon
    FROM tbluser u
    JOIN user_detail d ON u.id = d.id
    WHERE u.id = '$user_id'
";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);

// Ambil informasi produk dari tabel cart
$query_cart = "
    SELECT c.product_id, p.name AS product_name, c.quantity, p.price, (c.quantity * p.price) AS total_price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = '$user_id'
";
$result_cart = mysqli_query($conn, $query_cart);

// Hitung total harga
$total_price = 0;
$items = [];
while ($row = mysqli_fetch_assoc($result_cart)) {
    $total_price += $row['total_price'];
    $items[] = [
        'id' => $row['product_id'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'name' => $row['product_name']
    ];
}

// Buat parameter transaksi
$transaction_details = [
    'order_id' => 'ALZI-' . time() . '-' . rand(100, 999),
    'gross_amount' => $total_price
];

$customer_details = [
    'first_name' => $user['nama'],
    'email' => $user['email'],
    'phone' => $user['no_telepon'],
    'shipping_address' => $user['alamat']
];

$transaction_data = [
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $items
];

// Dapatkan token transaksi dari Midtrans
$snap_token = \Midtrans\Snap::getSnapToken($transaction_data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-X7zfk0k3aWOJvdhF">
    </script>
</head>

<body>
    <h1>Checkout</h1>
    <p>Nama: <?= $user['nama']; ?></p>
    <p>Email: <?= $user['email']; ?></p>
    <p>Alamat: <?= $user['alamat']; ?></p>
    <p>No. Telepon: <?= $user['no_telepon']; ?></p>
    <p>Total Pembayaran: Rp<?= number_format($total_price, 0, ',', '.'); ?></p>

    <button id="pay-button">Bayar Sekarang</button>

    <script>
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function() {
        window.snap.pay('<?= $snap_token; ?>', {
            onSuccess: function(result) {
                alert("Pembayaran berhasil!");
                console.log(result);
                window.location.href = "success.php";
            },
            onPending: function(result) {
                alert("Menunggu pembayaran.");
                console.log(result);
            },
            onError: function(result) {
                alert("Pembayaran gagal!");
                console.log(result);
            },
            onClose: function() {
                alert('Anda menutup jendela pembayaran.');
            }
        });
    });
    </script>
</body>

</html>