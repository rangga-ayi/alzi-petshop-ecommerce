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
$userid = intval($_SESSION['userid']);

// Query untuk mengambil data admin
$sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
FROM tbluser u 
LEFT JOIN user_detail d ON u.id = d.id 
WHERE u.id = ?";
$stmt_admin = $conn->prepare($sql);
$stmt_admin->bind_param("i", $userid);
$stmt_admin->execute();
$result = $stmt_admin->get_result();

if ($result && $result->num_rows > 0) {
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
$stmt_admin->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $email_baru = trim($_POST['email']);
    $password = !empty($_POST['password']) ? sha1($_POST['password']) : null;
    $level = trim($_POST['level']);

    if ($password) {
        $stmt_up = $conn->prepare("UPDATE tbluser SET nama = ?, email = ?, level = ?, password = ? WHERE id = ?");
        $stmt_up->bind_param("ssssi", $nama, $email_baru, $level, $password, $id);
    } else {
        $stmt_up = $conn->prepare("UPDATE tbluser SET nama = ?, email = ?, level = ? WHERE id = ?");
        $stmt_up->bind_param("sssi", $nama, $email_baru, $level, $id);
    }
    $stmt_up->execute();
    $stmt_up->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Ambil foto user yang akan dihapus
    $stmt_uf = $conn->prepare("SELECT foto FROM user_detail WHERE id = ?");
    $stmt_uf->bind_param("i", $id);
    $stmt_uf->execute();
    $res_uf = $stmt_uf->get_result();
    if ($res_uf && $row_uf = $res_uf->fetch_assoc()) {
        $user_foto = $row_uf['foto'];
        if (!empty($user_foto) && file_exists('../' . $user_foto) && strpos($user_foto, 'default.png') === false) {
            @unlink('../' . $user_foto);
        }
    }
    $stmt_uf->close();

    $stmt_del = $conn->prepare("DELETE FROM tbluser WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    $stmt_del->close();
}

// Fetch Users
$users = $conn->query("SELECT * FROM tbluser");
    //Nama Depan
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
                    <li><a class="dropdown-item" href="cart.php">Cart</a></li>
                    <li><a class="dropdown-item" href="transaction.php">Transaction</a></li>
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
    <div class="container mt-5 h-100">
        <h3 style="color: black;">Users</h3>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th style="width: 30%;">Nama</th>
                    <th style="width: 30%;">Email</th>
                    <th style="width: 20%;">Level</th>
                    <th style="width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nama']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['level']; ?></td>
                    <td>
                        <a href="users.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm edit-btn"
                            data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal"
                            data-bs-target="#userModal">Edit</a>
                        <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- User Edit -->
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="admin.php">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="userModalLabel">
                                <?php echo isset($_GET['edit']) ? 'Edit User' : 'Add User'; ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <input type="hidden" name="id" value='<?php echo $user['id']; ?>'>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?php echo $user['nama'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo $user['email'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" value=""
                                    placeholder="Kosongkan jika tidak ingin mengganti password">
                            </div>
                            <div class="mb-3">
                                <label for="level" class="form-label">Level</label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="admin"
                                        <?php echo (($user['level'] ?? '') == 'admin') ? 'selected' : ''; ?>>Admin
                                    </option>
                                    <option value="penjual"
                                        <?php echo (($user['level'] ?? '') == 'penjual') ? 'selected' : ''; ?>>Penjual
                                    </option>
                                    <option value="pembeli"
                                        <?php echo (($user['level'] ?? '') == 'pembeli') ? 'selected' : ''; ?>>Pembeli
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary mx-1" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary mx-1">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ambil semua tombol Edit
        const editButtons = document.querySelectorAll(".edit-btn");

        // Tambahkan event listener pada setiap tombol Edit
        editButtons.forEach((btn) => {
            btn.addEventListener("click", function() {
                const userId = this.getAttribute("data-id");

                // Kirim AJAX request untuk mendapatkan data user
                fetch(`get_user.php?id=${userId}`)
                    .then((response) => response.json())
                    .then((data) => {
                        // Isi modal dengan data user
                        document.getElementById("nama").value = data.nama;
                        document.getElementById("email").value = data.email;
                        document.getElementById("password").value =
                            ""; // Kosongkan untuk keamanan
                        document.getElementById("level").value = data.level;
                        document.querySelector("input[name='id']").value = data.id;
                    })
                    .catch((error) => console.error("Error:", error));
            });
        });
    });
    </script>
    <footer class="text-center">
        <p>Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>