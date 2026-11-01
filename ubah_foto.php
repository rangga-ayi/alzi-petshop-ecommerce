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

    // Ambil email user dari database
    $query = "SELECT email FROM tbluser WHERE id = '$userid'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    $email = $user['email'];

    // Ambil path foto user dari database
    $query = "SELECT foto FROM user_detail WHERE id = '$userid'";
    $result = mysqli_query($conn, $query);
    $user_detail = mysqli_fetch_assoc($result);
    $old_photo_path = $user_detail['foto'];
    // Tentukan foto default jika $old_photo_path kosong atau null
    $foto_default = 'imgs/user/default.png';
    if (empty($old_photo_path)) {
        $old_photo_path = $foto_default;
    }

    // Fungsi untuk membersihkan email menjadi nama file yang valid
    function sanitize_filename($filename) {
        // Ganti karakter yang tidak valid dengan _
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
    }

    if (isset($_POST["submit"])) {
        $target_dir = "imgs/user/";
        $sanitized_email = sanitize_filename($email); // Sanitize email untuk digunakan sebagai nama file
        $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . $sanitized_email . "." . $imageFileType;
        $uploadOk = 1;

        // Cek apakah file gambar adalah gambar asli atau bukan
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Cek ukuran file
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Batasi format file
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Maaf, hanya file JPG, JPEG, PNG & GIF yang di izinkan.";
            $uploadOk = 0;
        }


        // Cek apakah $uploadOk bernilai 0 karena error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        // Jika semua cek lolos, coba upload file
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                // Hapus file foto lama jika ada
                if ($old_photo_path && file_exists($old_photo_path)) {
                    if ($old_photo_path =! "/imgs/user/default.png") {
                        unlink($old_photo_path);
                    }
                }

                // Update path foto di database
                $sql = "UPDATE user_detail SET foto='$target_file' WHERE id='$userid'";
                if (mysqli_query($conn, $sql)) {
                    echo "<script>
                            document.location='index.php';
                          </script>";
                } else {
                    echo "<script>
                            alert('Terjadi kesalahan saat mengubah foto profil. Silakan coba lagi.');
                            window.history.back();
                          </script>";
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
        mysqli_close($conn);
    }
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
    <!-- Metadata -->
    <?php include('metadata.php'); ?>
    <title>Ubah Foto Profil</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- My Style -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap_style.css">
    <style>
    html,
    body {
        height: 100vh;
    }

    .navbar {
        position: sticky;
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
    <div class="container vh-100 mt-5">
        <h2 class="text-center">Ubah Foto Profil</h2>
        <form action="ubah_foto.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fileToUpload" class="form-label">Pilih file gambar untuk diupload:</label>
                <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" required>
            </div>
            <button type="submit" name="submit" class="btn btn-warning">Upload Gambar</button>
            <a href="detail.php" class="btn btn-danger">Cancel</a>
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