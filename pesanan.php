<?php
session_start();
require('php/php.php');

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

if ($_SESSION['level'] !== "penjual") {
    header("Location: login/login_form.php");
    exit;
}

$user_id = intval($_SESSION['userid']);

// Ambil data user untuk profil di navbar
$sql_user = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.no_telepon 
             FROM tbluser u 
             LEFT JOIN user_detail d ON u.id = d.id 
             WHERE u.id = ?";
$stmt_u = $conn->prepare($sql_user);
$stmt_u->bind_param("i", $user_id);
$stmt_u->execute();
$res_u = $stmt_u->get_result();
if ($res_u && $row_u = $res_u->fetch_assoc()) {
    $nama = $row_u['nama'];
    $email = $row_u['email'];
    $level = $row_u['level'];
    $foto = !empty($row_u['foto']) ? $row_u['foto'] : 'imgs/user/default.png';
} else {
    $nama = 'Penjual';
    $foto = 'imgs/user/default.png';
}
$stmt_u->close();

function getFirstName($fullName) {
    $parts = explode(" ", $fullName);
    return $parts[0];
}

// Filter Status
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$valid_statuses = ['all', 'pending', 'shipping', 'success'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

// Hitung total count per tab status untuk badge
$cnt_all = $conn->query("SELECT COUNT(*) AS total FROM transactions t JOIN shipping_detail sd ON t.order_id = sd.order_id WHERE (t.transaction_status = 'success' OR t.transaction_status = 'settlement')")->fetch_assoc()['total'] ?? 0;
$cnt_pending = $conn->query("SELECT COUNT(*) AS total FROM transactions t JOIN shipping_detail sd ON t.order_id = sd.order_id WHERE (t.transaction_status = 'success' OR t.transaction_status = 'settlement') AND sd.status_pengiriman IN (1, 2)")->fetch_assoc()['total'] ?? 0;
$cnt_shipping = $conn->query("SELECT COUNT(*) AS total FROM transactions t JOIN shipping_detail sd ON t.order_id = sd.order_id WHERE (t.transaction_status = 'success' OR t.transaction_status = 'settlement') AND sd.status_pengiriman IN (3, 4)")->fetch_assoc()['total'] ?? 0;
$cnt_success = $conn->query("SELECT COUNT(*) AS total FROM transactions t JOIN shipping_detail sd ON t.order_id = sd.order_id WHERE (t.transaction_status = 'success' OR t.transaction_status = 'settlement') AND sd.status_pengiriman IN (5, 6)")->fetch_assoc()['total'] ?? 0;

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Menentukan total baris untuk tab aktif
$total_rows = $cnt_all;
$where_clause = "(t.transaction_status = 'success' OR t.transaction_status = 'settlement')";
if ($status_filter === 'pending') {
    $total_rows = $cnt_pending;
    $where_clause .= " AND sd.status_pengiriman IN (1, 2)";
} elseif ($status_filter === 'shipping') {
    $total_rows = $cnt_shipping;
    $where_clause .= " AND sd.status_pengiriman IN (3, 4)";
} elseif ($status_filter === 'success') {
    $total_rows = $cnt_success;
    $where_clause .= " AND sd.status_pengiriman IN (5, 6)";
}

$total_pages = max(1, ceil($total_rows / $limit));
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Query data pesanan masuk lengkap
$query_orders = "
    SELECT 
        t.transaction_id,
        t.order_id,
        t.gross_amount,
        t.transaction_status,
        t.payment_time,
        t.update_time,
        t.item_details,
        u.nama AS buyer_name,
        d.no_telepon AS buyer_phone,
        d.alamat AS buyer_address,
        COALESCE(sd.status_pengiriman, 1) AS shipping_status_id,
        COALESCE(ss.status_pengiriman, 'Menunggu Konfirmasi') AS shipping_status_name
    FROM transactions t
    LEFT JOIN tbluser u ON t.user_id = u.id
    LEFT JOIN user_detail d ON u.id = d.id
    LEFT JOIN shipping_detail sd ON t.order_id = sd.order_id
    LEFT JOIN shipping_status ss ON sd.status_pengiriman = ss.id
    WHERE $where_clause
    ORDER BY t.transaction_id DESC
    LIMIT ? OFFSET ?
";

$stmt_orders = $conn->prepare($query_orders);
$stmt_orders->bind_param("ii", $limit, $offset);
$stmt_orders->execute();
$res_orders = $stmt_orders->get_result();

$orders = [];
$all_product_ids = [];
if ($res_orders) {
    while ($row_o = $res_orders->fetch_assoc()) {
        $orders[] = $row_o;
    }
}
$stmt_orders->close();

// Mengambil gambar produk pertama untuk tiap pesanan
foreach ($orders as $ord) {
    $items = json_decode($ord['item_details'], true);
    if (is_array($items)) {
        foreach ($items as $it) {
            if (isset($it['id']) && is_numeric($it['id'])) {
                $all_product_ids[] = intval($it['id']);
            }
        }
    }
}

$product_images = [];
if (!empty($all_product_ids)) {
    $unique_pids = array_unique($all_product_ids);
    $in_clause = implode(',', $unique_pids);
    $res_p = $conn->query("SELECT id, image FROM products WHERE id IN ($in_clause)");
    if ($res_p) {
        while ($prow = $res_p->fetch_assoc()) {
            $product_images[$prow['id']] = $prow['image'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk Toko - Alzi Petshop</title>
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
    .order-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        overflow: hidden;
    }

    .order-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .product-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .nav-pills .nav-link {
        color: #475569;
        font-weight: 500;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .nav-pills .nav-link.active {
        background-color: #ffd700;
        color: #000;
        font-weight: 600;
    }

    .badge-status {
        font-weight: 600;
        font-size: 0.82rem;
        padding: 6px 12px;
        border-radius: 20px;
    }

    footer {
        background-color: white;
        width: 100%;
        margin-top: auto;
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <?php require('php/navbar.php'); ?>

    <main class="container my-5 pt-4" style="max-width: 950px;">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam text-warning"></i> Pesanan Masuk Toko
                </h2>
                <p class="text-secondary small mb-0 fw-medium">Kelola pesanan pelanggan dan konfirmasi status pengiriman toko</p>
            </div>
            <a href="index_p.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Katalog Toko
            </a>
        </div>

        <!-- Filter Status Tab -->
        <ul class="nav nav-pills gap-2 mb-4 bg-white p-2 rounded-3 shadow-sm border flex-wrap">
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'all') ? 'active' : '' ?>" href="pesanan.php?status=all">
                    Semua Pesanan <span class="badge bg-secondary ms-1"><?= $cnt_all ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'pending') ? 'active' : '' ?>" href="pesanan.php?status=pending">
                    Perlu Diproses <span class="badge <?= ($cnt_pending > 0) ? 'bg-danger' : 'bg-secondary' ?> ms-1"><?= $cnt_pending ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'shipping') ? 'active' : '' ?>" href="pesanan.php?status=shipping">
                    Sedang Dikirim <span class="badge bg-secondary ms-1"><?= $cnt_shipping ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'success') ? 'active' : '' ?>" href="pesanan.php?status=success">
                    Selesai <span class="badge bg-secondary ms-1"><?= $cnt_success ?></span>
                </a>
            </li>
        </ul>

        <!-- Daftar Kartu Pesanan -->
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <?php
                $raw_items = json_decode($order['item_details'], true);
                $product_items = [];
                $shipping_fee = 0;

                if (is_array($raw_items)) {
                    foreach ($raw_items as $item) {
                        if (isset($item['id']) && $item['id'] === 'shipping') {
                            $shipping_fee = intval($item['price']);
                        } else {
                            $product_items[] = $item;
                        }
                    }
                }

                $tx_status = strtolower($order['transaction_status']);
                $date_display = !empty($order['payment_time']) ? $order['payment_time'] : $order['update_time'];
                $first_item = !empty($product_items) ? $product_items[0] : null;
                $first_item_img = ($first_item && isset($product_images[$first_item['id']])) 
                                  ? $product_images[$first_item['id']] 
                                  : 'imgs/user/default.png';
                $other_count = count($product_items) - 1;
                $shipping_id = intval($order['shipping_status_id']);
                ?>
                <div class="card order-card mb-4 shadow-sm">
                    <!-- Header Kartu -->
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-receipt"></i> Pesanan
                            </span>
                            <span class="text-secondary small fw-medium">
                                <?= !empty($date_display) ? date('d M Y, H:i', strtotime($date_display)) : '-' ?>
                            </span>
                            <span class="text-muted small d-none d-sm-inline">•</span>
                            <span class="text-secondary small font-monospace">
                                Order ID: #<?= htmlspecialchars($order['order_id']) ?>
                            </span>
                        </div>
                        <div>
                            <?php if ($shipping_id === 1 || $shipping_id === 2): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle badge-status">
                                    <i class="bi bi-clock-history me-1"></i> Perlu Dikonfirmasi Toko
                                </span>
                            <?php elseif ($shipping_id === 3 || $shipping_id === 4): ?>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle badge-status">
                                    <i class="bi bi-truck me-1"></i> <?= htmlspecialchars($order['shipping_status_name']) ?>
                                </span>
                            <?php elseif ($shipping_id >= 5): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle badge-status">
                                    <i class="bi bi-check-circle me-1"></i> Pesanan Selesai
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border badge-status">
                                    <?= htmlspecialchars($order['shipping_status_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body Kartu -->
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 align-items-center">
                            <!-- Detail Produk -->
                            <div class="col-md-7">
                                <?php if ($first_item): ?>
                                    <div class="d-flex gap-3 align-items-start">
                                        <img src="<?= htmlspecialchars($first_item_img) ?>" alt="Produk" class="product-thumb">
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($first_item['name']) ?></h6>
                                            <p class="text-secondary small mb-1 fw-medium">
                                                <?= htmlspecialchars($first_item['quantity']) ?> barang x <?= rupiah($first_item['price']) ?>
                                            </p>
                                            <?php if ($other_count > 0): ?>
                                                <p class="text-primary small mb-0 fw-semibold">
                                                    + <?= $other_count ?> produk lainnya
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-secondary mb-0 small">Item detail tidak tersedia.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Data Pembeli & Alamat Ringkas -->
                            <div class="col-md-5 border-start-md ps-md-3">
                                <div class="bg-light p-2 rounded-2 border">
                                    <div class="small fw-bold text-dark d-flex align-items-center gap-1 mb-1">
                                        <i class="bi bi-person text-secondary"></i> <?= htmlspecialchars($order['buyer_name'] ?? 'Pelanggan') ?>
                                        <?php if (!empty($order['buyer_phone'])): ?>
                                            <span class="text-muted fw-normal">• <?= htmlspecialchars($order['buyer_phone']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-secondary" style="font-size: 0.8rem; line-height: 1.4;">
                                        <i class="bi bi-geo-alt text-muted me-1"></i>
                                        <?= !empty($order['buyer_address']) ? htmlspecialchars(mb_strimwidth($order['buyer_address'], 0, 85, '...')) : 'Alamat GPS' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kartu -->
                    <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="text-secondary small fw-medium">
                                Total Pembayaran <?= ($shipping_fee > 0) ? "(Termasuk Ongkir " . rupiah($shipping_fee) . ")" : "" ?>:
                            </div>
                            <span class="fs-5 fw-bold text-dark"><?= rupiah($order['gross_amount']) ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="detail_order.php?order_id=<?= urlencode($order['order_id']) ?>" 
                               class="btn <?= ($shipping_id <= 2) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm px-3 shadow-xs">
                                <i class="bi bi-box-seam me-1"></i> <?= ($shipping_id <= 2) ? 'Proses & Kirim Pesanan' : 'Lihat Detail Pesanan' ?>
                            </a>
                            <a href="php/php-dompdf.php?order_id=<?= urlencode($order['order_id']) ?>" 
                               class="btn btn-outline-warning btn-sm px-3 text-dark" title="Cetak Invoice">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Navigasi Halaman" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="pesanan.php?status=<?= urlencode($status_filter) ?>&page=<?= $page - 1 ?>" aria-label="Sebelumnya">
                                &laquo;
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="pesanan.php?status=<?= urlencode($status_filter) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="pesanan.php?status=<?= urlencode($status_filter) ?>&page=<?= $page + 1 ?>" aria-label="Selanjutnya">
                                &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="card border text-center p-5 shadow-sm rounded-3 bg-white">
                <i class="bi bi-inbox text-muted display-4 mb-3"></i>
                <h5 class="fw-bold">Belum Ada Pesanan Masuk</h5>
                <p class="text-secondary small mb-3">Tidak ada transaksi yang sesuai dengan filter yang dipilih.</p>
                <div>
                    <a href="pesanan.php?status=all" class="btn btn-outline-secondary btn-sm px-3">
                        Lihat Semua Pesanan
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="text-center py-3 border-top mt-auto bg-white">
        <p class="mb-0 text-muted small">&copy; <?= date('Y') ?> Alzi Petshop - Panel Toko</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
