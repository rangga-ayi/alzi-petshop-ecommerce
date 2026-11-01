<?php
    session_start();
    include '../php/php.php';

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

    // Query untuk mengambil data dari kedua tabel
    $sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
    FROM tbluser u 
    LEFT JOIN user_detail d ON u.id = d.id 
    WHERE u.id = '$userid'";

    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
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
    <title>Cart Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <title>Alzi Petshop</title>
    <style>
    html,
    body {
        width: 100%;
        height: 100%;
    }

    .navbar {
        position: sticky;
    }

    .navbar-brand {
        display: inline;
    }

    h3 {
        font-weight: bold;
    }

    table {
        table-layout: fixed;
        width: 100%;
    }

    th,
    td {
        word-wrap: break-word;
        text-align: center;
        justify-content: center;
        align-items: center;
        align-content: center;
    }

    img {
        max-width: 100%;
        height: auto;
    }

    @media (min-width: 0px) {
        .navbar-brand {
            font-size: 16px;
        }

        table {
            font-size: 11px;
        }

        .btn {
            font-size: 10px;
            margin: 0.1rem 0;
            padding: 0.25rem 0.5rem;
        }
    }

    @media (min-width: 375px) {
        .navbar-brand {
            font-size: 18px;
        }

        table {
            font-size: 13px;
        }

        .btn {
            font-size: 11px;
            margin: 0.1rem 0;
            padding: 0.3rem 0.5rem;
        }
    }

    @media (min-width: 425px) {
        .navbar-brand {
            font-size: 20px;
        }

        table {
            font-size: 14px;
        }

        .btn {
            font-size: 12px;
            margin: 0.1rem 0;
            padding: 0.3rem 0.5rem;
        }
    }

    @media (min-width: 768px) {
        .navbar-brand {
            font-size: 20px;
        }

        table {
            font-size: 16px;
        }

        .btn {
            font-size: 14px;
            margin: 0.1rem 0;
            padding: 0.3rem 0.5rem;
        }
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand ms-2 font-weight-bold" href="admin.php">Alzi Petshop [Admin]</a>
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fw-bold" style="color: black;" href="#" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Others
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="products.php">Product</a></li>
                    <li><a class="dropdown-item" href="transaction.php">Transactions</a></li>
                    <li><a class="dropdown-item" href="admin.php">User</a></li>
                </ul>
            </div>
            <div class="d-flex ms-auto">
            </div>
            <div class="d-flex">
                <a href="users.php" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">Add
                    User</a>
            </div>
            <div class="navbar-item">
                <a href="../detail.php">
                    <img src="../<?php echo $foto; ?>" class="rounded-circle me-2">
                    <span id="user"><?php echo getFirstName($nama); ?></span>
                </a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center">Cart</h2>
        <?php
        // Handle Delete Item
        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $sql = "DELETE FROM cart WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success'>Item deleted successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }

        // Fetch All Items
        $result = $conn->query("
            SELECT 
                cart.id,
                tbluser.nama AS user_name,
                products.name AS product_name,
                cart.quantity,
                cart.price
            FROM cart
            INNER JOIN tbluser ON cart.user_id = tbluser.id
            INNER JOIN products ON cart.product_id = products.id
        ");
        ?>

        <!-- Items Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['user_name']; ?></td>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td>
                        <a href="edit_cart.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <footer class="text-center">
        <p>Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>