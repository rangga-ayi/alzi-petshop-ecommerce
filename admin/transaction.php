<?php
    session_start();
    // Koneksi ke database
    require('../php/php.php');

    // Periksa apakah user sudah login
    if (!isset($_SESSION['userid'])) {
        header("Location: ../login/login_form.php");
        exit;
    }

    if ($_SESSION['level'] != "admin") {
        header("Location: ../login/login_form.php");
        exit;
    }

    $userid = $_SESSION['userid']; // Ambil user ID dari session
    // Handle DELETE request
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt_del = $conn->prepare("DELETE FROM transactions WHERE transaction_id = ?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        $stmt_del->close();
    }

    // Fetch data from database
    $query = "
        SELECT 
            t.transaction_id,
            u.nama AS name,
            t.order_id,
            t.transaction_status,
            t.gross_amount,
            ss.status_pengiriman AS shipping_status,
            t.payment_time,
            t.update_time,
            t.item_details
        FROM transactions t
        LEFT JOIN tbluser u ON t.user_id = u.id
        LEFT JOIN shipping_detail sd ON t.order_id = sd.order_id
        LEFT JOIN shipping_status ss ON sd.status_pengiriman = ss.id
    ";

    $result = $conn->query($query);

    // Query untuk mengambil data dari kedua tabel
    $sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
    FROM tbluser u 
    LEFT JOIN user_detail d ON u.id = d.id 
    WHERE u.id = ?";
    $stmt_u = $conn->prepare($sql);
    $stmt_u->bind_param("i", $userid);
    $stmt_u->execute();
    $resultuser = $stmt_u->get_result();

    if ($resultuser->num_rows > 0) {
        $row = mysqli_fetch_assoc($resultuser);
        $foto = $row['foto'];
        $nama = $row['nama'];
        $email = $row['email'];
        $level = $row['level'];
        $jenis_kelamin = $row['jenis_kelamin'];
        $tanggal_lahir = $row['tanggal_lahir'];
        $alamat = $row['alamat'];
        $no_telepon = $row['no_telepon'];
    } else {
        echo "Data user tidak ditemukan.";
    }

    function getFirstName($fullName) {
        $parts = explode(" ", $fullName);
        return $parts[0];
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f4f4f4;
    }

    .btn {
        padding: 5px 10px;
        text-decoration: none;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .btn-delete {
        color: red;
    }

    /* Memperkecil kolom item details */
    .item-details-col {
        max-width: 200px;
        /* Menentukan batas maksimal lebar */
        word-wrap: break-word;
    }
    </style>
</head>

<body>
    <?php include('navbar_admin.php'); ?>
    <div class="container py-4">
        <h3 class="mb-4 text-dark fw-bold"><i class="bi bi-receipt me-2"></i>Audit Transaksi Toko</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Order ID</th>
                        <th>Transaction Status</th>
                        <th>Gross Amount</th>
                        <th>Shipping Status</th>
                        <th>Payment Time</th>
                        <th>Update Time</th>
                        <th class="item-details-col">Name</th> <!-- Kolom item details diperbesar batasnya -->
                        <th class="item-details-col">Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['order_id']) ?></td>
                        <td><?= htmlspecialchars($row['transaction_status']) ?></td>
                        <td><?= htmlspecialchars(rupiah($row['gross_amount'])) ?></td>
                        <td><?= htmlspecialchars($row['shipping_status'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['payment_time']) ?></td>
                        <td><?= htmlspecialchars($row['update_time']) ?></td>
                        <?php 
                                $item_details = json_decode($row['item_details'], true);
                                $item_names = [];
                                $item_quantities = [];
                                if ($item_details && is_array($item_details)) {
                                    $item_names = array_map(function($item) {
                                        return htmlspecialchars($item['name']);
                                    }, $item_details);
                                    $item_quantities = array_map(function($item) {
                                        return htmlspecialchars($item['quantity']);
                                    }, $item_details);
                                }
                        ?>
                        <td><?= !empty($item_names) ? implode(", ", $item_names) : '-' ?></td>
                        <td><?= !empty($item_quantities) ? implode(", ", $item_quantities) : '-' ?></td>
                        <td>
                            <a class="btn btn-danger btn-sm" href="?delete=<?= $row['transaction_id'] ?>"
                                onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <footer class="text-center">
        <p>Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
<?php
$conn->close();
?>