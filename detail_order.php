<?php
session_start();

// Koneksi ke database
require('php/php.php');
require('midtrans_config.php');

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: landing_page.php");
    exit;
}

if ($_SESSION['level'] != "penjual") {
    header("Location: login/login_form.php");
    exit;
}

$userid = intval($_SESSION['userid']);

// Query untuk mengambil data user navbar
$sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
FROM tbluser u 
LEFT JOIN user_detail d ON u.id = d.id 
WHERE u.id = ?";
$stmt_u = $conn->prepare($sql);
$stmt_u->bind_param("i", $userid);
$stmt_u->execute();
$result = $stmt_u->get_result();

if ($result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $foto = !empty($row['foto']) ? $row['foto'] : 'imgs/user/default.png';
    $nama = $row['nama'];
    $email = $row['email'];
    $level = $row['level'];
    $jenis_kelamin = $row['jenis_kelamin'];
    $tanggal_lahir = $row['tanggal_lahir'];
    $alamat = $row['alamat'];
    $no_telepon = $row['no_telepon'];
} else {
    echo "Data user tidak ditemukan.";
    exit;
}
$stmt_u->close();

// Mengambil order_id dengan GET
$order_id = isset($_GET['order_id']) ? htmlspecialchars(trim($_GET['order_id'])) : '';

$feedback_msg = '';
// Jika aksi status pengiriman diklik oleh penjual
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = null;
    if (isset($_POST['pembayaran'])) {
        $new_status = 3; // Pesanan dikonfirmasi & sedang dikemas
    } elseif (isset($_POST['pengemasan'])) {
        $new_status = 4; // Pesanan sedang dikirim
    } elseif (isset($_POST['pengiriman'])) {
        $new_status = 5; // Pesanan telah diterima pembeli
    }

    if ($new_status !== null) {
        $update_query = "UPDATE shipping_detail SET status_pengiriman = ? WHERE order_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("is", $new_status, $order_id);
        if ($update_stmt->execute()) {
            $feedback_msg = '<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle me-2"></i>Status pengiriman berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } else {
            $feedback_msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gagal memperbarui status pengiriman.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
        $update_stmt->close();
    }
}

// Query untuk mengambil data transaksi lengkap
$query = "
    SELECT 
        t.transaction_id,
        u.nama AS name,
        u.email AS buyer_email,
        d.alamat AS buyer_address,
        d.no_telepon AS buyer_phone,
        t.order_id,
        t.transaction_status,
        t.gross_amount,
        ss.status_pengiriman AS shipping_status,
        sd.status_pengiriman AS shipping_status_id,
        t.payment_time,
        t.update_time,
        t.item_details,
        t.snap_token
    FROM transactions t
    LEFT JOIN tbluser u ON t.user_id = u.id
    LEFT JOIN user_detail d ON u.id = d.id
    LEFT JOIN shipping_detail sd ON t.order_id = sd.order_id
    LEFT JOIN shipping_status ss ON sd.status_pengiriman = ss.id
    WHERE t.order_id = ?
";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row['name']);
        $buyer_email = htmlspecialchars($row['buyer_email'] ?? '');
        $buyer_address = htmlspecialchars($row['buyer_address'] ?? '');
        $buyer_phone = htmlspecialchars($row['buyer_phone'] ?? '');
        $transaction_status = htmlspecialchars(strtolower($row['transaction_status']));
        $gross_amount_num = floatval($row['gross_amount']);
        $gross_amount = rupiah($gross_amount_num);
        $shipping_status = htmlspecialchars($row['shipping_status'] ?? 'Menunggu pembayaran');
        $shipping_status_id = intval($row['shipping_status_id']);
        $payment_time = htmlspecialchars($row['payment_time'] ?? '');
        $update_time = htmlspecialchars($row['update_time'] ?? '');
        $item_details = json_decode($row['item_details'], true);
        $snap_token = htmlspecialchars($row['snap_token'] ?? '');
    } else {
        echo '<div class="container my-5 text-center"><div class="alert alert-warning">Data transaksi tidak ditemukan.</div><a href="index_p.php" class="btn btn-secondary">Kembali</a></div>';
        exit;
    }
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">Kesalahan dalam query transaksi.</div>';
    exit;
}

// Ambil gambar produk untuk setiap item
$product_images = [];
if (!empty($item_details) && is_array($item_details)) {
    $pids = [];
    foreach ($item_details as $it) {
        if (isset($it['id']) && is_numeric($it['id'])) {
            $pids[] = intval($it['id']);
        }
    }
    if (!empty($pids)) {
        $pids_in = implode(',', array_unique($pids));
        $res_img = $conn->query("SELECT id, image FROM products WHERE id IN ($pids_in)");
        if ($res_img) {
            while ($prow = $res_img->fetch_assoc()) {
                $product_images[$prow['id']] = $prow['image'];
            }
        }
    }
}

function getFirstName($fullName) {
    $parts = explode(" ", $fullName);
    return $parts[0];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan #<?= $order_id ?> - Alzi Petshop</title>
    <!-- Metadata -->
    <?php include('metadata.php'); ?>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Style -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap_style.css">
    <style>
    .detail-card {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        overflow: hidden;
    }

    .badge-status {
        font-weight: 600;
        font-size: 0.85rem;
        padding: 6px 14px;
        border-radius: 20px;
    }

    .product-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    footer {
        width: 100%;
        background-color: white;
        margin-top: auto;
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <?php require('php/navbar.php'); ?>

    <main class="container my-5 pt-4" style="max-width: 900px;">
        <!-- Tombol Kembali -->
        <div class="mb-3">
            <a href="pesanan.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Pesanan Masuk
            </a>
        </div>

        <?= $feedback_msg ?>

        <!-- Kartu Detail Transaksi -->
        <div class="card detail-card shadow-sm mb-4">
            <!-- Header Kartu -->
            <div class="card-header bg-white p-3 p-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Manajemen Pesanan Pelanggan</h4>
                    <div class="text-muted small">
                        <span>No. Pesanan: <strong class="text-dark font-monospace">#<?= htmlspecialchars($order_id) ?></strong></span>
                        <span class="mx-1">•</span>
                        <span><?= !empty($payment_time) ? date('d M Y, H:i', strtotime($payment_time)) : (!empty($update_time) ? date('d M Y, H:i', strtotime($update_time)) : '-') ?></span>
                    </div>
                </div>
                <div>
                    <?php if ($transaction_status === 'success' || $transaction_status === 'settlement'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle badge-status">
                            <i class="bi bi-check-circle me-1"></i> Pembayaran Berhasil
                        </span>
                    <?php elseif ($transaction_status === 'pending'): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle badge-status">
                            <i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-status">
                            <i class="bi bi-x-circle me-1"></i> <?= htmlspecialchars(ucfirst($transaction_status)) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-3 p-md-4">
                <!-- Status & Kontrol Pengiriman Penjual -->
                <div class="p-3 bg-light rounded-3 border mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-dark p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Status Pengiriman Saat Ini:</div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($shipping_status) ?></div>
                        </div>
                    </div>
                    <div>
                        <?php if ($shipping_status_id === 2): ?>
                            <form method="post">
                                <button type="submit" name="pembayaran" class="btn btn-warning btn-sm px-3 fw-semibold">
                                    <i class="bi bi-box-seam me-1"></i> Konfirmasi Pesanan & Kemas
                                </button>
                            </form>
                        <?php elseif ($shipping_status_id === 3): ?>
                            <form method="post">
                                <button type="submit" name="pengemasan" class="btn btn-primary btn-sm px-3">
                                    <i class="bi bi-send me-1"></i> Kirim Pesanan ke Kurir
                                </button>
                            </form>
                        <?php elseif ($shipping_status_id === 4): ?>
                            <form method="post">
                                <button type="submit" name="pengiriman" class="btn btn-success btn-sm px-3">
                                    <i class="bi bi-check2-circle me-1"></i> Selesaikan Pesanan
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rincian Produk -->
                <h6 class="fw-bold mb-3"><i class="bi bi-bag-check me-2"></i>Produk yang Dipesan Pelanggan</h6>
                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 250px;">Produk</th>
                                <th scope="col" class="text-center">Harga Satuan</th>
                                <th scope="col" class="text-center">Jumlah</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal_products = 0;
                            $shipping_fee = 0;
                            if ($item_details && is_array($item_details)):
                                foreach ($item_details as $item):
                                    if (isset($item['id']) && $item['id'] === 'shipping'):
                                        $shipping_fee = intval($item['price']);
                                        continue;
                                    endif;
                                    $item_qty = intval($item['quantity']);
                                    $item_price = intval($item['price']);
                                    $item_subtotal = $item_qty * $item_price;
                                    $subtotal_products += $item_subtotal;
                                    $item_img = isset($product_images[$item['id']]) ? $product_images[$item['id']] : 'imgs/user/default.png';
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= htmlspecialchars($item_img) ?>" class="product-thumb" alt="">
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($item['name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-secondary fw-medium"><?= rupiah($item_price) ?></td>
                                    <td class="text-center"><?= $item_qty ?></td>
                                    <td class="text-end fw-semibold"><?= rupiah($item_subtotal) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Rincian produk tidak tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Grid Informasi Pelanggan & Biaya -->
                <div class="row g-3">
                    <!-- Info Pelanggan -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 bg-white shadow-xs">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-person-fill text-primary"></i> Data Penerima & Alamat
                            </h6>
                            <p class="mb-1 fw-bold text-dark fs-6"><?= $name ?></p>
                            <p class="mb-1 text-secondary" style="font-size: 0.92rem; line-height: 1.5; font-weight: 450;">
                                <i class="bi bi-envelope text-muted me-1"></i> <?= !empty($buyer_email) ? htmlspecialchars($buyer_email) : '-' ?>
                            </p>
                            <p class="mb-1 text-secondary" style="font-size: 0.92rem; line-height: 1.5; font-weight: 450;">
                                <i class="bi bi-telephone text-muted me-1"></i> <?= !empty($buyer_phone) ? htmlspecialchars($buyer_phone) : '-' ?>
                            </p>
                            <p class="mb-0 text-secondary" style="font-size: 0.92rem; line-height: 1.5; font-weight: 450;">
                                <i class="bi bi-house text-muted me-1"></i> <?= !empty($buyer_address) ? htmlspecialchars($buyer_address) : 'Alamat sesuai titik koordinat GPS' ?>
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Biaya -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 bg-white shadow-xs">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-receipt-cutoff text-primary"></i> Rincian Pembayaran
                            </h6>
                            <div class="d-flex justify-content-between text-secondary mb-2" style="font-size: 0.92rem; font-weight: 450;">
                                <span>Total Harga Barang</span>
                                <span class="fw-medium text-dark"><?= rupiah($subtotal_products) ?></span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary mb-2" style="font-size: 0.92rem; font-weight: 450;">
                                <span>Ongkos Kirim</span>
                                <span class="fw-medium text-dark"><?= rupiah($shipping_fee) ?></span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold fs-5 text-dark">
                                <span>Total Diterima</span>
                                <span class="text-primary"><?= $gross_amount ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Kartu -->
            <div class="card-footer bg-white border-top p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a href="pesanan.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan Masuk
                </a>
                <div>
                    <?php if ($transaction_status === 'success' || $transaction_status === 'settlement'): ?>
                        <a href="php/php-dompdf.php?order_id=<?= urlencode($order_id) ?>" class="btn btn-warning text-dark px-3">
                            <i class="bi bi-download me-1"></i> Download Invoice
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center py-3 border-top mt-auto">
        <p class="mb-0 text-muted small">Create by Alzi Petshop | &copy 2024</p>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
