<?php
    session_start();
    // Koneksi database
    require 'php/php.php';
    if (!isset($_SESSION['userid'])) {
        header("Location: login/login_form.php");
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

    //Nama Depan
    function getFirstName($fullName) {
        $parts = explode(" ", $fullName);
        return $parts[0];
    }

    // Menangkap data yang dikirim dari form
    if (isset($_POST["submit"])) {
        $old_password = mysqli_real_escape_string($conn, $_POST['old_password']);
        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

        // Validasi password lama
        $sql = "SELECT password FROM tbluser WHERE id = '$userid'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        if (sha1($old_password) === $row['password']) {
            if ($new_password === $confirm_password) {
                $new_password_encrypted = sha1($new_password);
                $update_sql = "UPDATE tbluser SET password='$new_password_encrypted' WHERE id='$userid'";

                if (mysqli_query($conn, $update_sql)) {
                    echo "<script>
                            alert('Kata sandi berhasil diubah');
                            document.location='index.php';
                          </script>";
                } else {
                    echo "<script>
                            alert('Terjadi kesalahan saat mengubah kata sandi. Silakan coba lagi.');
                            window.history.back();
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Konfirmasi kata sandi tidak cocok.');
                        window.history.back();
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Kata sandi lama tidak cocok.');
                    window.history.back();
                  </script>";
        }
        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Metadata -->
    <?php include('metadata.php'); ?>
    <title>Ubah Kata Sandi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- My Style -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap_style.css">
    <style>
        html, body {
            height: 100vh;
        }

        .navbar {
            position: sticky;
        }

        .button {
            float: right;
        }

        footer {
            background-color: white;
            width: 100%;
            bottom: 0;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                display: inline;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar, Search, Keranjang, User -->
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand ms-2 font-weight-bold" href="login/login_form.php">
                Alzi Petshop
            </a>
            <div class="navbar-item">
                <a href="detail.php">
                    <img src="<?php echo $foto; ?>" class="rounded-circle me-2">
                    <span id="user"><?php echo getFirstName($nama); ?></span>
                </a>
            </div>
        </div>
    </nav>
    <!-- End Navbar, Search, Keranjang, User -->
    <div class="container d-flex vh-100 justify-content-center align-item-center mt-5">
        <form action="#" method="POST">
            <table class="table-sm mb-3">
                <thead>
                    <tr>
                        <th colspan='3'>
                            <h2 class="text-center">Ubah Kata Sandi</h2>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <label for="old_password" class="form-label">Kata Sandi Lama</label>
                        </td>
                        <td>:</td>
                        <td><input type="password" class="form-control" name="old_password" id="old_password" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="new_password" class="form-label">Kata Sandi Baru</label>
                        </td>
                        <td>:</td>
                        <td><input type="password" class="form-control" name="new_password" id="new_password" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        </td>
                        <td>:</td>
                        <td><input type="password" class="form-control" name="confirm_password" id="confirm_password"
                                required>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="button">
                <button type="submit" name="submit" class="btn btn-warning">Ubah Kata Sandi</button>
                <a href="detail.php" class="btn btn-danger">Batal</a>
            </div>
        </form>
    </div>

    <footer class="text-center">
        <p class="p-3">Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>