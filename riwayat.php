<?php
session_start();
require('php/php.php');
require('midtrans_config.php');

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: landing_page.php");
    exit;
}

if ($_SESSION['level'] !== "pembeli") {
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
    $nama = 'Pembeli';
    $foto = 'imgs/user/default.png';
}
$stmt_u->close();

function getFirstName($fullName) {
    $parts = explode(" ", $fullName);
    return $parts[0];
}

// Filter Status
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$valid_statuses = ['all', 'pending', 'success', 'cancel'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Query Count
$count_sql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ?";
if ($status_filter === 'pending') {
    $count_sql .= " AND transaction_status = 'pending'";
} elseif ($status_filter === 'success') {
    $count_sql .= " AND (transaction_status = 'success' OR transaction_status = 'settlement')";
} elseif ($status_filter === 'cancel') {
    $count_sql .= " AND (transaction_status = 'cancel' OR transaction_status = 'expire' OR transaction_status = 'deny')";
}

$stmt_cnt = $conn->prepare($count_sql);
$stmt_cnt->bind_param("i", $user_id);
$stmt_cnt->execute();
$total_rows = $stmt_cnt->get_result()->fetch_assoc()['total'];
$stmt_cnt->close();

$total_pages = max(1, ceil($total_rows / $limit));
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Query Data Transaksi
$data_sql = "
    SELECT 
        t.transaction_id,
        t.order_id,
        t.transaction_status,
        t.gross_amount,
        t.payment_time,
        t.update_time,
        t.item_details,
        t.snap_token,
        ss.status_pengiriman AS shipping_status,
        sd.status_pengiriman AS shipping_status_id
    FROM transactions t
    LEFT JOIN shipping_detail sd ON t.order_id = sd.order_id
    LEFT JOIN shipping_status ss ON sd.status_pengiriman = ss.id
    WHERE t.user_id = ?
";

if ($status_filter === 'pending') {
    $data_sql .= " AND t.transaction_status = 'pending'";
} elseif ($status_filter === 'success') {
    $data_sql .= " AND (t.transaction_status = 'success' OR t.transaction_status = 'settlement')";
} elseif ($status_filter === 'cancel') {
    $data_sql .= " AND (t.transaction_status = 'cancel' OR t.transaction_status = 'expire' OR t.transaction_status = 'deny')";
}

$data_sql .= " ORDER BY t.transaction_id DESC LIMIT ? OFFSET ?";

$stmt_data = $conn->prepare($data_sql);
$stmt_data->bind_param("iii", $user_id, $limit, $offset);
$stmt_data->execute();
$res_data = $stmt_data->get_result();

$orders = [];
while ($row = $res_data->fetch_assoc()) {
    $orders[] = $row;
}
$stmt_data->close();

// Ambil data gambar produk untuk item
$all_product_ids = [];
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
    <title>Riwayat Pesanan - Alzi Petshop</title>
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
        border: 1px solid #e9ecef;
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
        color: #495057;
        font-weight: 500;
        border-radius: 20px;
        padding: 6px 18px;
        font-size: 0.9rem;
    }

    .nav-pills .nav-link.active {
        background-color: #ff6c59;
        color: white;
    }

    .badge-status {
        font-weight: 600;
        font-size: 0.8rem;
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

    <main class="container my-5 pt-4" style="max-width: 920px;">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <div>
                <h2 class="fw-bold mb-1">Riwayat Pesanan</h2>
                <p class="text-muted small mb-0">Pantau status transaksi dan pengiriman belanjaan Anda</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Belanja Lagi
            </a>
        </div>

        <!-- Filter Status Tab -->
        <ul class="nav nav-pills gap-2 mb-4 bg-white p-2 rounded-3 shadow-sm border">
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'all') ? 'active' : '' ?>" href="riwayat.php?status=all">
                    Semua
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'pending') ? 'active' : '' ?>" href="riwayat.php?status=pending">
                    Menunggu Pembayaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'success') ? 'active' : '' ?>" href="riwayat.php?status=success">
                    Berhasil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'cancel') ? 'active' : '' ?>" href="riwayat.php?status=cancel">
                    Dibatalkan
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
                ?>
                <div class="card order-card mb-4 shadow-sm">
                    <!-- Header Kartu -->
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-receipt"></i> Belanja
                            </span>
                            <span class="text-muted small">
                                <?= !empty($date_display) ? date('d M Y, H:i', strtotime($date_display)) : '-' ?>
                            </span>
                            <span class="text-muted small d-none d-sm-inline">•</span>
                            <span class="text-secondary small font-monospace">
                                Order ID: #<?= htmlspecialchars($order['order_id']) ?>
                            </span>
                        </div>
                        <div>
                            <?php if ($tx_status === 'success' || $tx_status === 'settlement'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle badge-status">
                                    <i class="bi bi-check-circle me-1"></i> Pembayaran Berhasil
                                </span>
                            <?php elseif ($tx_status === 'pending'): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle badge-status">
                                    <i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-status">
                                    <i class="bi bi-x-circle me-1"></i> <?= htmlspecialchars(ucfirst($tx_status)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body Kartu -->
                    <div class="card-body p-3 p-md-4">
                        <?php if ($first_item): ?>
                            <div class="d-flex gap-3 align-items-start">
                                <img src="<?= htmlspecialchars($first_item_img) ?>" alt="Produk" class="product-thumb">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($first_item['name']) ?></h6>
                                    <p class="text-muted small mb-1">
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
                            <p class="text-muted mb-0 small">Item detail tidak tersedia.</p>
                        <?php endif; ?>

                        <!-- Info Status Pengiriman jika sudah bayar -->
                        <?php if (($tx_status === 'success' || $tx_status === 'settlement') && !empty($order['shipping_status'])): ?>
                            <div class="mt-3 p-2 bg-light rounded border d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-primary fs-5"></i>
                                <span class="small text-secondary">Status Pengiriman:</span>
                                <span class="small fw-bold text-dark"><?= htmlspecialchars($order['shipping_status']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Kartu -->
                    <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="text-muted small">
                                Total Belanja <?= ($shipping_fee > 0) ? "(Termasuk Ongkir " . rupiah($shipping_fee) . ")" : "" ?>:
                            </div>
                            <span class="fs-5 fw-bold text-dark"><?= rupiah($order['gross_amount']) ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="detail_riwayat.php?order_id=<?= urlencode($order['order_id']) ?>" 
                               class="btn btn-outline-secondary btn-sm px-3">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <?php if ($tx_status === 'pending' && !empty($order['snap_token'])): ?>
                                <button type="button" class="btn btn-primary btn-sm px-3 btn-pay-snap"
                                        data-snap="<?= htmlspecialchars($order['snap_token']) ?>">
                                    <i class="bi bi-credit-card"></i> Bayar Sekarang
                                </button>
                            <?php elseif ($tx_status === 'success' || $tx_status === 'settlement'): ?>
                                <a href="php/php-dompdf.php?order_id=<?= urlencode($order['order_id']) ?>" 
                                   class="btn btn-outline-warning btn-sm px-3 text-dark">
                                    <i class="bi bi-download"></i> Invoice
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Navigasi Halaman" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="riwayat.php?status=<?= $status_filter ?>&page=<?= $page - 1 ?>">
                                &laquo; Sebelumnya
                            </a>
                        </li>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="riwayat.php?status=<?= $status_filter ?>&page=<?= $p ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="riwayat.php?status=<?= $status_filter ?>&page=<?= $page + 1 ?>">
                                Selanjutnya &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5 bg-white rounded-3 shadow-sm border my-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="fw-bold mt-3 mb-1">Belum Ada Transaksi</h5>
                <p class="text-muted small mb-4">
                    <?= ($status_filter !== 'all') 
                        ? 'Tidak ada pesanan dengan status ' . htmlspecialchars($status_filter) . '.' 
                        : 'Anda belum memiliki riwayat pembelian produk di Alzi Petshop.' ?>
                </p>
                <a href="index.php" class="btn btn-warning px-4 py-2 fw-semibold">
                    Mulai Belanja Kebutuhan Kucing
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="text-center py-3 border-top mt-auto">
        <p class="mb-0 text-muted small">Create by Alzi Petshop | &copy 2024</p>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
    document.querySelectorAll('.btn-pay-snap').forEach(btn => {
        btn.addEventListener('click', function() {
            const snapToken = this.getAttribute('data-snap');
            if (snapToken && window.snap) {
                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        window.location.reload();
                    },
                    onPending: function(result) {
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert("Pembayaran gagal!");
                    },
                    onClose: function() {
                        console.log('Customer closed the popup without finishing the payment');
                    }
                });
            } else {
                alert("Snap Token tidak valid.");
            }
        });
    });
    </script>
</body>

</html>
