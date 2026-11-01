<?php
session_start();
include 'php/php.php'; // Koneksi ke database

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

if ($_SESSION['level'] != "pembeli") {
    header("Location: login/login_form.php");
    exit;
}

$user_id = intval($_SESSION['userid']);

// Query untuk mengambil data dari tabel cart berdasarkan user_id
$query = "
    SELECT c.product_id, p.name AS product_name, p.image, c.quantity, p.price, (c.quantity * p.price) AS total_price 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css"> <!-- Gaya CSS sesuai kebutuhan -->
</head>

<body>
    <div class="container">
        <h1>Keranjang Belanja</h1>
        <form method="post" action="update_cart.php">
            <table>
                <thead>
                    <tr>
                        <th>Pilih</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="<?= $row['product_id']; ?>"></td>
                        <td><img src="<?= $row['image']; ?>" alt="<?= $row['product_name']; ?>" width="50"></td>
                        <td><?= $row['product_name']; ?></td>
                        <td>
                            <input type="number" name="quantity[<?= $row['product_id']; ?>]"
                                value="<?= $row['quantity']; ?>" min="1">
                        </td>
                        <td>Rp<?= number_format($row['price'], 0, ',', '.'); ?></td>
                        <td>Rp<?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                        <td><a href="delete_cart_item.php?product_id=<?= $row['product_id']; ?>"
                                onclick="return confirm('Hapus produk ini?')">Hapus</a></td>
                    </tr>
                    <?php $total_price += $row['total_price']; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p>Total Belanja: Rp<?= number_format($total_price, 0, ',', '.'); ?></p>
            <button type="submit" name="update_cart">Perbarui Keranjang</button>
            <button type="submit" formaction="checkout.php">Checkout</button>
        </form>
    </div>
</body>

</html>