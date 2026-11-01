<?php 
    require 'midtrans_config.php'; // Konfigurasi Midtrans
    $user_id = $_SESSION['userid'];
    $user_level = $_SESSION['level'];
    $restricted_levels = ['admin', 'penjual'];
    // Query untuk mengambil data dari tabel cart berdasarkan user_id


    // Hitung ongkir terpusat
    $shipping_info = get_shipping_info($user_id, $conn);
    $shipping_cost = $shipping_info['cost'];
    $shipping_distance = $shipping_info['distance'];

    // Ambil data keranjang untuk Midtrans
    $items = [];
    $total_price_midtrans = 0;  // Total price untuk Midtrans
    $user_id = intval($user_id);
    $stmt_qc1 = $conn->prepare("
        SELECT c.product_id, p.name AS product_name, p.image, c.quantity, p.price, (c.quantity * p.price) AS total_price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt_qc1->bind_param("i", $user_id);
    $stmt_qc1->execute();
    $resultcart = $stmt_qc1->get_result();
    while ($rowc = mysqli_fetch_assoc($resultcart)) {
        if ($rowc['price'] > 0 && $rowc['quantity'] > 0) {
            // Tambahkan harga item ke total price
            $total_price_midtrans += $rowc['total_price'];
            $items[] = [
                'id' => $rowc['product_id'],
                'price' => $rowc['price'],
                'quantity' => $rowc['quantity'],
                'name' => $rowc['product_name']
            ];
        }
    }

    // Tambahkan ongkir sebagai item
    $items[] = [
        'id' => 'shipping',
        'price' => $shipping_cost,
        'quantity' => 1,
        'name' => 'Ongkos Kirim'
    ];

    // Tambahkan ongkir ke total price
    $total_price_midtrans += $shipping_cost;

    // Ambil data keranjang untuk tampilan modal (tanpa menghitung total price lagi)
    $stmt_qc2 = $conn->prepare("
        SELECT c.id AS cart_id, c.product_id, p.name AS product_name, p.image, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt_qc2->bind_param("i", $user_id);
    $stmt_qc2->execute();
    $resultcart_display = $stmt_qc2->get_result();

    $total_price_display = 0; // Total price untuk tampilan modal

    // Ambil informasi user dari tabel tbluser dan user_detail
    $stmt_qu = $conn->prepare("
        SELECT u.nama, u.email, d.alamat, d.no_telepon
        FROM tbluser u
        JOIN user_detail d ON u.id = d.id
        WHERE u.id = ?
    ");
    $stmt_qu->bind_param("i", $user_id);
    $stmt_qu->execute();
    $result_user = $stmt_qu->get_result();
    $user = $result_user ? $result_user->fetch_assoc() : null;
    $stmt_qu->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
        header('Content-Type: application/json'); // Tambahkan header JSON di sini
        // Periksa apakah keranjang kosong
        $snap_token = null;
        if (!empty($items) && $total_price_midtrans > 0) {
            // Data transaksi
            $transaction_details = [
                'order_id' => rand(),
                'gross_amount' => $total_price_midtrans
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

            try {
                $item_details_json = json_encode($items);
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, order_id, transaction_status, gross_amount, item_details, snap_token) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                $payment_status = 'Pending';
                $order_ids = $transaction_details['order_id'];
                $snap_token = \Midtrans\Snap::getSnapToken($transaction_data);
                $stmt->bind_param("sssiss", $user_id,
                $transaction_details['order_id'],
                    
                    $payment_status, 
                    $transaction_details['gross_amount'],
                    $item_details_json,
                    $snap_token
                );
                $stmt->execute();
                $stmt->close();

                // Kirim respons JSON
                echo json_encode([
                    'success' => true,
                    'snap_token' => $snap_token,
                    'order_id' => $transaction_details['order_id'],
                    'payment_url' => "https://app.midtrans.com/snap/v2/vtweb/$snap_token" // URL untuk pembayaran
                ]);
                exit;
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Keranjang kosong atau total harga tidak valid.']);
            exit;
        }
    }

    if (isset($_POST['update_cart'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan data 'quantity' diterima sebagai array
            if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
                foreach ($_POST['quantity'] as $product_id => $quantity) {
                    $product_id = intval($product_id); // Pastikan product_id adalah integer
                    $quantity = max(1, intval($quantity)); // Pastikan quantity minimal 1
        
                    // Update jumlah barang dalam tabel cart
                    $updateQuery = "UPDATE cart SET quantity = ?, price = (SELECT price FROM products WHERE id = ?) * ? WHERE product_id = ? AND user_id = ?";
                    $stmt = $conn->prepare($updateQuery);
                    if ($stmt) {
                        $stmt->bind_param("iiiii", $quantity, $product_id, $quantity, $product_id, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        echo "Kesalahan saat mempersiapkan query: " . $conn->error;
                    }
                }
                header("Location: #"); // Redirect ke halaman keranjang
                exit;
            } else {
                
            }
        }
    }


    $queryriwayatpembeli = "
        SELECT 
        t.*, 
        u.nama AS nama_user,
        ss.status_pengiriman AS shipping_status 
        FROM transactions t
        LEFT JOIN tbluser u ON t.user_id = u.id
        LEFT JOIN shipping_detail sd ON t.order_id = sd.order_id
        LEFT JOIN shipping_status ss ON sd.status_pengiriman = ss.id
        WHERE t.transaction_status='success'
    ";

    // Gunakan prepared statement untuk queryriwayatpembeli
    $stmt2 = $conn->prepare($queryriwayatpembeli);
    $stmt2->execute();
    $resultriwayatpembeli = $stmt2->get_result();

    // Hitung pesanan masuk yang perlu diproses penjual
    $pending_orders_count = 0;
    if ($user_level === 'penjual') {
        $cnt_stmt = $conn->query("
            SELECT COUNT(*) AS cnt 
            FROM transactions t 
            JOIN shipping_detail sd ON t.order_id = sd.order_id 
            WHERE (t.transaction_status = 'success' OR t.transaction_status = 'settlement') 
            AND sd.status_pengiriman IN (1, 2, 3)
        ");
        if ($cnt_stmt && $cnt_row = $cnt_stmt->fetch_assoc()) {
            $pending_orders_count = intval($cnt_row['cnt']);
        }
    }
?>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-X7zfk0k3aWOJvdhF"></script>
    <style>
    .navbar-item img {
        height: 30px !important;
        width: 30px !important;
        object-fit: cover;
    }
    .modal-body {
        font-family: Arial, sans-serif;
    }

    .cart-item {
        display: flex;
        align-items: center;
        /*         border-bottom: 1px solid #ccc;  */
        padding: 10px 0;
    }

    .cart-image img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 10px;
    }

    .cart-details {
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: space-between;
    }

    .product-name {
        font-weight: bold;
        margin-right: auto;
    }

    .product-price {
        margin-left: 10px;
        color: #555;
    }

    .quantity-control {
        display: flex;
        align-items: center;
    }

    .quantity-control input {
        width: 50px;
        margin-left: 5px;
        text-align: center;
        transform: translateY(-8px);
    }

    .delete-link {
        color: red;
        text-decoration: none;
        margin-left: 10px;
        font-size: 0.9em;
        transform: translateY(-8px);
    }

    .delete-link:hover {
        text-decoration: underline;
    }

    .total-price {
        font-size: 1.2em;
        text-align: right;
        margin-top: 0px;
        font-weight: bold;
    }

    .cart-actions {
        text-align: center;
        margin-top: 15px;
    }
    </style>
    <!-- Navbar, Search, Keranjang, User -->
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand ms-2 font-weight-bold d-inline-flex align-items-center" href="<?= ($user_level === 'penjual') ? 'index_p.php' : (($user_level === 'admin') ? 'admin/admin.php' : 'index.php') ?>">
                <span>Alzi Petshop</span>
                <?php if ($user_level === 'penjual'): ?>
                    <span class="badge bg-warning text-dark border border-warning ms-2" style="font-size: 0.72rem; font-weight: 600;">Panel Toko</span>
                <?php elseif ($user_level === 'admin'): ?>
                    <span class="badge bg-primary text-white ms-2" style="font-size: 0.72rem; font-weight: 600;">Super Admin</span>
                <?php endif; ?>
            </a>
            <?php if (!in_array($user_level, $restricted_levels)): ?>
            <div class="search-box me-3">
                <input type="text" id="searchInput" placeholder="Cari produk..." autocomplete="off">
                <div class="search-dropdown" id="searchResults"></div>
            </div>
            <?php endif; ?>
            <?php if ($user_level == 'penjual'): ?>
            <?php if (basename($_SERVER['PHP_SELF']) == 'detail_order.php'): ?>
            <?php else: ?>
            <div class="d-flex align-items-center">
                <a href="tambah.php" class="btn btn-warning btn-sm fw-medium shadow-xs me-2" id="tambah">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Produk
                </a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <div class="navbar-item d-flex align-items-center">
                <?php if (basename($_SERVER['PHP_SELF']) == 'detail.php'): ?>
                <?php else: ?>
                <?php if (!in_array($user_level, $restricted_levels)): ?>
                <a href="riwayat.php" id="riwayatLink" title="Riwayat Pesanan Pelanggan">
                    <img src="imgs/riwayat.jpg" alt="riwayat" class="me-2">
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#keranjangModal" id="keranjangLink" title="Keranjang Belanja">
                    <img src="imgs/cart.png" alt="Keranjang" class="me-2">
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($user_level == 'penjual'): ?>
                <a href="#" data-bs-toggle="modal" data-bs-target="#riwayatModalPembeli" id="riwayatLinkPembeli" title="Pesanan Masuk (Fulfillment Toko)" class="position-relative me-3 d-inline-block">
                    <img src="imgs/riwayat.jpg" alt="Pesanan Masuk">
                    <?php if ($pending_orders_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?= $pending_orders_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <a href="detail.php">
                    <img src="<?php echo $foto; ?>" class="rounded-circle me-2">
                    <span id="user"><?php echo getFirstName($nama); ?></span>
                </a>
            </div>
        </div>
    </nav>
    <!-- End Navbar, Search, Keranjang, User -->
    <!-- Keranjang -->
    <div class="modal fade" id="keranjangModal" tabindex="-1" aria-labelledby="keranjangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="keranjangModalLabel">Keranjang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <?php while ($row = mysqli_fetch_assoc($resultcart_display)): ?>
                        <div class="cart-item">
                            <div class="cart-image">
                                <img src="<?= $row['image']; ?>" alt="<?= $row['product_name']; ?>">
                            </div>
                            <div class="cart-details">
                                <p class="product-name"><?= $row['product_name']; ?></p>
                                <p class="product-price">Rp<?= number_format($row['price'], 0, ',', '.'); ?></p>
                                <div class="quantity-control">
                                    <input type="number" id="quantity-<?= $row['product_id']; ?>"
                                        name="quantity[<?= $row['product_id']; ?>]" value="<?= $row['quantity']; ?>"
                                        min="1">
                                </div>
                                <a href="delete_cart_item.php?product_id=<?= $row['product_id']; ?>"
                                    onclick="return confirm('Hapus produk ini?')" class="delete-link">Hapus</a>
                            </div>
                        </div>
                        <?php 
						    $total_price_display += $row['price'] * $row['quantity']; // Hitung total price untuk tampilan modal
						endwhile; ?>
                        <div class="d-flex justify-content-between text-muted small mt-2">
                            <span>Subtotal Produk:</span>
                            <span>Rp<?= number_format($total_price_display, 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Ongkos Kirim<?= ($shipping_distance > 0) ? " ({$shipping_distance} km)" : "" ?>:</span>
                            <span>Rp<?= number_format($shipping_cost, 0, ',', '.'); ?></span>
                        </div>
                        <hr class="my-2">
                        <p class="total-price text-end fw-bold">Total Pembayaran: Rp<?= number_format($total_price_display + $shipping_cost, 0, ',', '.'); ?>
                        </p>
                        <div class="cart-actions container-fluid d-flex justify-content-between">
                            <button class="btn btn-warning" type="submit" name="update_cart">Update Keranjang</button>
                            <a class="btn btn-success" type="checkout" id="pay-button" href="#">Checkout</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End keranjang -->

    <!-- Modal Riwayat Penjual -->
    <div class="modal fade" id="riwayatModalPembeli" tabindex="-1" aria-labelledby="riwayatModalLabelPembeli"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="riwayatModalLabelPembeli">
                        <i class="bi bi-box-seam text-warning"></i> Pesanan Masuk (Fulfillment Toko)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama Pelanggan</th>
                                    <th>Nama Barang</th>
                                    <th>Quantity</th>
                                    <th>Order ID</th>
                                    <th>Harga</th>
                                    <th>Status Pembayaran</th>
                                    <th>Waktu Pembayaran</th>
                                    <th>Status Pengiriman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultriwayatpembeli && $resultriwayatpembeli->num_rows > 0): ?>
                                <?php while ($row = $resultriwayatpembeli->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_user']); ?></td>
                                    <?php 
                                            $item_details = json_decode($row['item_details'], true);
                                            if ($item_details): ?>
                                    <td>
                                        <?php 
                                                    $item_names = array_map(function($item) {
                                                        return htmlspecialchars($item['name']);
                                                    }, $item_details);
                                                    echo implode(", ", $item_names);
                                                    ?>
                                    </td>
                                    <td>
                                        <?php 
                                                    $item_quantities = array_map(function($item) {
                                                        return htmlspecialchars($item['quantity']);
                                                    }, $item_details);
                                                    echo implode(", ", $item_quantities);
                                                    ?>
                                    </td>
                                    <?php else: ?>
                                    <td>No items found</td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($row['order_id']); ?></td>
                                    <td><?= htmlspecialchars(rupiah($row['gross_amount'])); ?></td>
                                    <td><?= htmlspecialchars($row['transaction_status']); ?></td>
                                    <td><?= htmlspecialchars($row['payment_time']); ?></td>
                                    <td><a href="detail_order.php?order_id=<?php echo $row['order_id']; ?>"
                                            class="btn btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No transactions found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const navPayButton = document.getElementById('pay-button');
    if (navPayButton) {
        navPayButton.addEventListener('click', function(event) {
        event.preventDefault(); // Cegah refresh halaman
        if (typeof window.snap === 'undefined') {
            alert("Modul pembayaran Midtrans gagal dimuat. Pastikan koneksi internet aktif dan matikan AdBlocker/Brave Shield untuk situs ini.");
            return;
        }

        const checkoutEndpoint = window.location.pathname.includes('/admin/') ? '../process_checkout.php' : 'process_checkout.php';
        fetch(checkoutEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'checkout=true'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof window.snap === 'undefined') {
                        alert("Modul pembayaran Midtrans tidak tersedia.");
                        return;
                    }
                    // Panggil Snap Midtrans
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            window.location.href =
                                `./proses_status.php?order_id=${encodeURIComponent(data.order_id)}`;
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
                            console.log("Jendela pembayaran ditutup.");
                        }
                    });
                } else {
                    alert("Gagal memproses transaksi: " + (data.message || data.error || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                alert('Gagal memproses transaksi: ' + (error && error.message ? error.message : 'Silakan coba lagi.'));
            });
        });
    }

    const products = <?php echo json_encode($products); ?>;
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function() {
        const query = searchInput.value.toLowerCase().trim();

        searchResults.innerHTML = '';

        if (query.length > 0) {
            const filteredProducts = products.filter(product =>
                product.name.toLowerCase().includes(query)
            );

            if (filteredProducts.length > 0) {
                searchResults.style.display = 'block';
                filteredProducts.forEach(product => {
                    const item = document.createElement('div');
                    item.classList.add('item');
                    item.innerHTML = `
    	                                <img src="${product.image}" loading:="lazy" alt="${product.name}" class="item-image">
    	                                <div class="item-details">
    	                                    <h5>${product.name}</h5>
    	                                    <span>Rp${product.price.toLocaleString()}</span>
    	                                </div>
    	                            `;
                    item.addEventListener('click', () => {
                        window.location.href = `produk.php?product_id=${product.id}`;
                    });
                    searchResults.appendChild(item);
                });
            } else {
                searchResults.style.display = 'none';
            }
        } else {
            searchResults.style.display = 'none';
        }
    });
    }

    // searchInput.addEventListener('blur', function() {
    //     searchInput.value = '';
    //     searchResults.style.display = 'none';
    // });
    </script>