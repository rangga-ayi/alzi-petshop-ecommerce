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
$sql = "SELECT u.id, u.nama, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon 
        FROM tbluser u 
        LEFT JOIN user_detail d ON u.id = d.id";

$result = mysqli_query($conn, $sql);

// Handle Create and Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $no_telepon = $_POST['no_telepon'];

    if (!empty($id)) {
        // Perbarui data jika ID sudah ada
        $sql = "UPDATE user_detail 
                SET jenis_kelamin = ?, tanggal_lahir = ?, alamat = ?, no_telepon = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $jenis_kelamin, $tanggal_lahir, $alamat, $no_telepon, $id);
    } else {
        // Tambah data baru
        $sql = "INSERT INTO user_detail (jenis_kelamin, tanggal_lahir, alamat, no_telepon) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $jenis_kelamin, $tanggal_lahir, $alamat, $no_telepon);
    }
    if ($stmt->execute()) {
        header("Location: user_details.php");
        exit;
    } else {
        echo "Terjadi kesalahan: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt_del = $conn->prepare("DELETE FROM user_detail WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    $stmt_del->close();
    header("Location: user_details.php");
    exit;
}

// Fetch User Details
$user_details = $conn->query("SELECT * FROM user_detail");
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
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand ms-2 font-weight-bold" href="../index_a.php">Alzi Petshop [Admin]</a>
            <div class="d-flex ms-auto">
                <a href="tambah.php" class="btn btn-warning me-3">Tambah</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>User Details</h2>
        <a href="user_details.php" class="btn btn-primary mb-2" data-bs-toggle="modal"
            data-bs-target="#userDetailModal">Add User Detail</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Jenis Kelamin</th>
                    <th>Tanggal Lahir</th>
                    <th>Alamat</th>
                    <th>No Telepon</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $user_details->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['jenis_kelamin']; ?></td>
                    <td><?php echo $row['tanggal_lahir']; ?></td>
                    <td><?php echo $row['alamat']; ?></td>
                    <td><?php echo $row['no_telepon']; ?></td>
                    <td>
                        <a href="user_details.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal" data-bs-target="#userDetailModal">Edit</a>
                        <a href="user_details.php?delete=<?php echo $row['id']; ?>"
                            class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- User Detail Modal -->
        <div class="modal fade" id="userDetailModal" tabindex="-1" aria-labelledby="userDetailModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="user_details.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userDetailModalLabel">Add/Edit User Detail</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (isset($_GET['edit'])):
                            $id = intval($_GET['edit']);
                            $stmt_ed = $conn->prepare("SELECT * FROM user_detail WHERE id = ?");
                            $stmt_ed->bind_param("i", $id);
                            $stmt_ed->execute();
                            $res_ed = $stmt_ed->get_result();
                            $user_detail = $res_ed ? $res_ed->fetch_assoc() : null;
                            $stmt_ed->close();
                            ?>
                            <input type="hidden" name="id" value="<?php echo $user_detail['id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="id" class="form-label">User ID</label>
                                <input type="text" class="form-control" id="id" name="id"
                                    value="<?php echo $user_detail['id'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <input type="text" class="form-control" id="jenis_kelamin" name="jenis_kelamin"
                                    value="<?php echo $user_detail['jenis_kelamin'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                    value="<?php echo $user_detail['tanggal_lahir'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <input type="text" class="form-control" id="alamat" name="alamat"
                                    value="<?php echo $user_detail['alamat'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="no_telepon" class="form-label">No Telepon</label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon"
                                    value="<?php echo $user_detail['no_telepon'] ?? ''; ?>">
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