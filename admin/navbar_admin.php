<?php
$current_admin_page = basename($_SERVER['PHP_SELF']);
$admin_name = isset($nama) ? getFirstName($nama) : 'Admin';
$admin_photo = !empty($foto) ? '../' . ltrim($foto, '../') : '../imgs/user/default.png';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top py-2 shadow-sm">
    <div class="container-fluid px-3">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="admin.php">
            <span class="text-white">Alzi Petshop</span>
            <span class="badge bg-primary text-white" style="font-size: 0.72rem; letter-spacing: 0.5px;">SUPER ADMIN</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_admin_page === 'admin.php') ? 'active fw-bold text-white bg-primary bg-opacity-25 rounded px-3' : 'text-light px-3' ?>" href="admin.php">
                        <i class="bi bi-people-fill me-1"></i> Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_admin_page === 'transaction.php') ? 'active fw-bold text-white bg-primary bg-opacity-25 rounded px-3' : 'text-light px-3' ?>" href="transaction.php">
                        <i class="bi bi-receipt me-1"></i> Audit Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_admin_page === 'user_details.php') ? 'active fw-bold text-white bg-primary bg-opacity-25 rounded px-3' : 'text-light px-3' ?>" href="user_details.php">
                        <i class="bi bi-person-lines-fill me-1"></i> Detail Profil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_admin_page === 'products.php') ? 'active fw-bold text-white bg-primary bg-opacity-25 rounded px-3' : 'text-light px-3' ?>" href="products.php">
                        <i class="bi bi-boxes me-1"></i> Data Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_admin_page === 'cart.php') ? 'active fw-bold text-white bg-primary bg-opacity-25 rounded px-3' : 'text-light px-3' ?>" href="cart.php">
                        <i class="bi bi-cart me-1"></i> Keranjang
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <a href="../detail.php" class="text-decoration-none d-flex align-items-center text-white" title="Profil Admin">
                    <img src="<?= htmlspecialchars($admin_photo) ?>" class="rounded-circle me-2 border border-2 border-primary" style="width: 32px; height: 32px; object-fit: cover;">
                    <span class="fw-medium small"><?= htmlspecialchars($admin_name) ?></span>
                </a>
                <a href="../login/logout.php" class="btn btn-outline-danger btn-sm px-3" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
