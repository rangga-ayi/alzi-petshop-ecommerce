<?php

session_start();
include 'php/php.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

if ($_SESSION['level'] != "admin") {
    header("Location: login/login_form.php");
    exit;
}

$userid = $_SESSION['userid']; // Ambil user ID dari session

// Query untuk mengambil data dari kedua tabel
$userid = intval($_SESSION['userid']);
$sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
FROM tbluser u 
LEFT JOIN user_detail d ON u.id = d.id 
WHERE u.id = ?";
$stmt_admin = $conn->prepare($sql);
$stmt_admin->bind_param("i", $userid);
$stmt_admin->execute();
$result = $stmt_admin->get_result();

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

// Handle Create and Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $level = trim($_POST['level']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        if (!empty($_POST['password'])) {
            $password = sha1($_POST['password']);
            $stmt_up = $conn->prepare("UPDATE tbluser SET nama = ?, email = ?, password = ?, level = ? WHERE id = ?");
            $stmt_up->bind_param("ssssi", $nama, $email, $password, $level, $id);
        } else {
            $stmt_up = $conn->prepare("UPDATE tbluser SET nama = ?, email = ?, level = ? WHERE id = ?");
            $stmt_up->bind_param("sssi", $nama, $email, $level, $id);
        }
        $stmt_up->execute();
        $stmt_up->close();
    } else {
        $password = sha1($_POST['password']);
        $stmt_in = $conn->prepare("INSERT INTO tbluser (nama, email, password, level) VALUES (?, ?, ?, ?)");
        $stmt_in->bind_param("ssss", $nama, $email, $password, $level);
        $stmt_in->execute();
        $stmt_in->close();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
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
    <link rel="stylesheet" href="css/style.css">
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
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand ms-2 font-weight-bold" href="index_p.php">Alzi Petshop [Admin]</a>
            <div class="d-flex ms-auto">
            </div>
            <div class="navbar-item">
                <a href="detail.php">
                    <img src="<?php echo $foto; ?>" class="rounded-circle me-2">
                    <span id="user"><?php echo getFirstName($nama); ?></span>
                </a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>Users</h2>
        <a href="users.php" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#userModal">Add User</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nama']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['level']; ?></td>
                    <td>
                        <a href="users.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal" data-bs-target="#userModal">Edit</a>
                        <a href="users.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- User Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="users.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userModalLabel">Add/Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (isset($_GET['edit'])):
                            $id = intval($_GET['edit']);
                            $stmt_u_ed = $conn->prepare("SELECT * FROM tbluser WHERE id = ?");
                            $stmt_u_ed->bind_param("i", $id);
                            $stmt_u_ed->execute();
                            $res_u_ed = $stmt_u_ed->get_result();
                            $user = $res_u_ed ? $res_u_ed->fetch_assoc() : null;
                            $stmt_u_ed->close();
                            ?>
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <?php endif; ?>
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
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="level" class="form-label">Level</label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="admin"
                                        <?php if (($user['level'] ?? '') == 'admin') echo 'selected'; ?>>Admin</option>
                                    <option value="penjual"
                                        <?php if (($user['level'] ?? '') == 'penjual') echo 'selected'; ?>>Penjual
                                    </option>
                                    <option value="pembeli"
                                        <?php if (($user['level'] ?? '') == 'pembeli') echo 'selected'; ?>>Pembeli
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
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