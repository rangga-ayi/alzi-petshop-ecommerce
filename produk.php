<?php
    session_start();
    // PHP Data Js Search
    include('php/php.php');
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Periksa apakah user sudah login
    if (!isset($_SESSION['userid'])) {
        header("Location: login/login_form.php");
        exit;
    }

    if ($_SESSION['level'] != "pembeli") {
        header("Location: login/login_form.php");
        exit;
    }
    $userid = $_SESSION['userid'];

    // Query untuk mengambil data user
    $sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
    FROM tbluser u 
    LEFT JOIN user_detail d ON u.id = d.id 
    WHERE u.id = '$userid'";

    $result = mysqli_query($conn, $sql);

    if ($result && $result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $foto = $row['foto'];
        $nama = $row['nama'];
        $email = $row['email'];
        $level = $row['level'];
    } else {
        echo "Data user tidak ditemukan.";
    }

    // Ambil semua produk untuk pencarian
    $query = "SELECT id, name, price, image FROM products";
    $result = $conn->query($query);

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    // Ambil produk berdasarkan ID dari parameter URL
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

    $data = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
    $productd = mysqli_fetch_assoc($data);
    if ($result && $result->num_rows > 0) {
        $nama_p = $productd['name'];
        $harga_p = $productd['price'];
        $satuan_p = $productd['satuan'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity']) && !isset($_POST['checkout']) && !isset($_POST['buy_now'])) {
        // Ambil data dari form
        $user_id = $_SESSION['userid'];
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $quantity = (int) $_POST['quantity'];

        // Ambil nama user berdasarkan user_id
        $user_query = "SELECT nama FROM tbluser WHERE id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_name = $user_result->fetch_assoc()['nama'];

        // Ambil nama produk dan harga berdasarkan product_id
        $product_query = "SELECT name, price FROM products WHERE id = ?";
        $product_stmt = $conn->prepare($product_query);
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        $product = $product_result->fetch_assoc();
        $product_name = $product['name'];
        $price = $product['price'] * $quantity;

        // Masukkan data ke tabel cart
        $insert_query = "INSERT INTO cart (user_id, user_name, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isissi", $user_id, $user_name, $product_id, $product_name, $quantity, $price);

        if ($insert_stmt->execute()) {
            echo "<p>Produk berhasil ditambahkan ke keranjang!</p>";
        } else {
            echo "<p>Gagal menambahkan produk: " . $conn->error . "</p>";
        }

        $user_stmt->close();
        $product_stmt->close();
        $insert_stmt->close();
    }

    //Nama Depan
    function getFirstName($fullName) {
        $parts = explode(" ", $fullName);
        return $parts[0];
    }

    require("php/navbar.php");
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

$total_price = 0; // Inisialisasi total_price
$items = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buy_now']) || (isset($_POST['action']) && $_POST['action'] === 'buy_now'))) {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $product_query = "SELECT name, price FROM products WHERE id = ?";
    $product_stmt = $conn->prepare($product_query);

    if ($product_stmt) {
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();

        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            $product_name = $product['name'];
            $price = $product['price'] * $quantity;

            $items[] = [
                'id' => $product_id,
                'price' => $price,
                'quantity' => $quantity,
                'name' => $product_name
            ];

            $total_price += $price; // Tambahkan harga produk ke total_price
        }
    }
}



$snap_token = null;
if (!empty($items) && $total_price > 0) {
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

    try {
        $snap_token = \Midtrans\Snap::getSnapToken($transaction_data);
    } catch (Exception $e) {
        error_log("Midtrans Error: " . $e->getMessage());
    }
}


    $conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .deskripsi-terbatas {
            cursor: pointer;
            position: relative;
            padding-bottom: 1rem;
        }

        .deskripsi-terbatas span {
            display: block;
        }

        .deskripsi-terbatas button {
            color: #007bff;
            text-decoration: underline;
            border: none;
            background: transparent;
        }

        .dropdown-menu {
            width: 100%;
            border: none;
            box-shadow: 0 -4px 4px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 436px) {
            #container-p {
                padding-bottom: 4rem;
            }

            .item-konten-p {
                padding: 1rem 1.5rem 0rem 1.5rem;
            }

            .item-button-mobile {
                display: flex;
                position: fixed;
                width: 100%;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                padding: 0.4rem 0.8rem;
                background-color: white;
                justify-content: center;
                z-index: 1000;
                box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
            }

            .item-button-tabdesk {
                display: none;
            }

            .btn-keranjang,
            .btn-beli {
                flex: 1;
            }
        }

        @media (min-width: 436px) {
            .item-button-tabdesk {
                display: block;
            }

            .item-button-mobile {
                display: none;
            }
        }

        footer {
            background-color: white;
            margin-top: 2rem;
            padding: 1rem 0 3rem 0;
            width: 100%;
        }
    </style>
    <title><?php echo isset($productd['name']) ? htmlspecialchars($productd['name']) . " — Alzi Petshop" : "Alzi Petshop"; ?></title>
</head>

<body>
    <script src="script/script.js"></script>
    <!-- Detail Produk -->
    <div id="container-p" class="container mt-5" style="padding-top: 1.5rem; color: black;">
        <?php if ($productd): ?>
        <div class="row">
            <!-- Gambar Produk -->
            <div class="col-md-6 text-center">
                <img src="<?php echo $productd['image']; ?>" alt="<?php echo $productd['name']; ?>"
                    class="product-image">
            </div>

            <!-- Detail Produk -->
            <div class="col-md-6 item-konten-p">
                <h4 class="nama-p"><?php echo htmlspecialchars($productd['name']); ?></h4>
                <h2 class="harga-p">Rp<?php echo number_format($productd['price'], 0, ',', '.'); ?></h2>
                <form method="POST" action="">
                    <!-- Deskripsi -->
                    <div id="description" class="deskripsi-terbatas" onclick="toggleDescription()">
                        <?php 
                            $maxLength = 200;
                            $description = nl2br(htmlspecialchars($productd['description']));
                            $shortDesc = substr($description, 0, $maxLength);
                            $isTruncated = strlen($description) > $maxLength;

                            echo '<span id="short-desc">' . $shortDesc . ($isTruncated ? '...' : '') . '</span>';
                            echo '<span id="full-desc" style="display:none;">' . $description . '</span>';
                        ?>
                        <?php if ($isTruncated): ?>
                        <button id="toggle-desc" type="button" class="btn btn-link p-0"
                            style="pointer-events: none; text-decoration: none; color: rgb(255, 180, 0); font-weight: bold;">Lihat
                            Selengkapnya</button>
                        <?php endif; ?>
                    </div>

                    <form action="produk.php" method="POST">
                        <!-- Tombol Mobile -->
                        <div id="A" class="gap-3 item-button-mobile">
                            <!-- Tombol Beli Sekarang -->
                            <button type="button" class="btn-beli" id="beliButton" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Beli Sekarang
                            </button>
                            <!-- Div Dropdown -->
                            <div class="dropdown-menu p-4" aria-labelledby="beliButton" id="beliDropdown">
                                <h6>Konfirmasi Pembelian</h6>
                                <!-- Jumlah Barang -->
                                <div class="d-flex align-items-center my-3">
                                    <label for="quantity" class="me-2">Jumlah:</label>
                                    <div class="quantity-box d-flex">
                                        <button type="button" class="btn btn-outline-secondary decreaseBtn">-</button>
                                        <input type="number" class="quantityInput form-control mx-2" name="quantity"
                                            min="1" value="1" />
                                        <button type="button" class="btn btn-outline-secondary increaseBtn">+</button>
                                    </div>
                                </div>
                                <button type="submit" name="action" value="buy_now" class="btn-beli">Beli</button>
                            </div>
                        </div>
                        <!-- Tombol Tablet + Dekstop -->
                        <div id="B" class="gap-3 item-button-tabdesk">
                            <!-- Jumlah Barang -->
                            <div class="d-flex align-items-center my-3">
                                <label for="quantity" class="me-2">Jumlah:</label>
                                <div class="quantity-box d-flex">
                                    <button type="button" class="btn btn-outline-secondary decreaseBtn">-</button>
                                    <input type="number" class="quantityInput form-control mx-2" name="quantity" min="1"
                                        value="1" />
                                    <button type="button" class="btn btn-outline-secondary increaseBtn">+</button>
                                </div>
                            </div>
                            <!-- Tombol Beli Sekarang -->
                            <button type="submit" name="action" value="buy_now" class="btn-beli">Masukkan
                                Keranjang</button>
                            <a class="btn btn-success" type="checkout" id="pay-buttons">Checkout</a>
                        </div>
                    </form>
                </form>
            </div>
        </div>
        <?php else: ?>
        <p class="text-danger">Produk tidak ditemukan.</p>
        <?php endif; ?>
    </div>

    <!-- Js -->
    <script>
        // Kuantitas/Jumlah Barang
        document.addEventListener("DOMContentLoaded", function() {
            const decreaseBtns = document.querySelectorAll('.decreaseBtn');
            const increaseBtns = document.querySelectorAll('.increaseBtn');
            const quantityInputs = document.querySelectorAll('.quantityInput');
            const form = document.querySelector('form'); // Ambil form

            // Fungsi untuk mengurangi jumlah
            decreaseBtns.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    let quantity = parseInt(quantityInputs[index].value);
                    if (quantity > 1) {
                        quantityInputs[index].value = quantity - 1;
                    }
                });
            });

            // Fungsi untuk menambah jumlah
            increaseBtns.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    let quantity = parseInt(quantityInputs[index].value);
                    quantityInputs[index].value = quantity + 1;
                });
            });

            // Saat form disubmit, pastikan value quantity yang terbaru dikirimkan
            form.addEventListener('submit', function() {
                const quantity = quantityInputs[0].value; // Ambil nilai dari input pertama
                console.log("Form submitted with quantity: " + quantity); // Debug log
            });
        });

        // Hapus Div Width Tertentu
        function checkWidthA() {
            var divA = document.getElementById("A");
            var divB = document.getElementById("B");
            if (window.innerWidth > 436) {
                divA.innerHTML = '';
            } else {
                divB.innerHTML = '';
            }
        }

        window.addEventListener("resize", checkWidthA);
        window.addEventListener("load", checkWidthA);

        // Lihat Selengkapnya
        function toggleDescription() {
            const shortDesc = document.getElementById('short-desc');
            const fullDesc = document.getElementById('full-desc');
            const button = document.getElementById('toggle-desc');

            if (fullDesc.style.display === 'none' || fullDesc.style.display === '') {
                fullDesc.style.display = 'inline';
                shortDesc.style.display = 'none';
                button.textContent = 'Sembunyikan';
            } else {
                fullDesc.style.display = 'none';
                shortDesc.style.display = 'inline';
                button.textContent = 'Lihat Selengkapnya';
            }
        }

        // Dropdown Beli
        const beliButton = document.querySelector('#beliButton');
        const beliDropdown = document.querySelector('#beliDropdown');

        beliButton.addEventListener('click', function(event) {
            const isOpen = beliDropdown.classList.contains('show');
            if (!isOpen) {
                const dropdown = new bootstrap.Dropdown(beliButton);
                dropdown.show();
            } else {
                event.stopPropagation();
            }
        });

        beliDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        document.addEventListener('click', function(e) {
            if (!beliButton.contains(e.target) && !beliDropdown.contains(e.target)) {
                const dropdown = new bootstrap.Dropdown(beliButton);
                dropdown.hide();
            }
        });

    var payButton = document.getElementById('pay-buttons');
    if (payButton) {
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
                }
            });
        });
    }
   
    </script>
    <!-- End Js Search -->
    <!-- Sementara tanpa footer -->
    <footer class="text-center">
        <p>Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>