<?php
// Mengaktifkan session pada PHP
session_start();

// Menghubungkan PHP dengan koneksi database
include '../php/php.php';

// Menangkap data yang dikirim dari form login
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? sha1($_POST['password']) : '';

// Menyeleksi data user dengan email dan password menggunakan prepared statement
$stmt = $conn->prepare("SELECT * FROM tbluser WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$login = $stmt->get_result();

// Menghitung jumlah data yang ditemukan
$cek = $login->num_rows;

// Cek apakah email dan password ditemukan pada database
if ($cek > 0) {
    $data = $login->fetch_assoc();

    // Buat session login dan user ID
    $_SESSION['userid'] = $data['id'];
    $_SESSION['email'] = $email;
    $_SESSION['level'] = $data['level'];

    // Alihkan ke halaman sesuai level user
    if ($data['level'] == "admin") {
        header("Location: ../admin/admin.php");
    } elseif ($data['level'] == "penjual") {
        header("Location: ../index_p.php");
    } elseif ($data['level'] == "pembeli") {
        header("Location: ../index.php");
    } else {
        header("Location: login_form.php?error=Invalid user level");
    }
} else {
    header("Location: login_form.php?error=Email atau Password salah");
}
$stmt->close();
?>
