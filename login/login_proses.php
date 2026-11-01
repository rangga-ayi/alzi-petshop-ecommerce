<?php
// Mengaktifkan session pada PHP
session_start();

// Menghubungkan PHP dengan koneksi database
include '../php/php.php';

// Menangkap data yang dikirim dari form login
$login_input = isset($_POST['login_input']) ? trim($_POST['login_input']) : '';
$raw_password = isset($_POST['password']) ? $_POST['password'] : '';

// Menyeleksi data user berdasarkan email atau no_telepon dengan prepared statement
$stmt = $conn->prepare(
    "SELECT tbluser.id, tbluser.email, tbluser.password, tbluser.level, user_detail.no_telepon 
     FROM tbluser 
     LEFT JOIN user_detail ON tbluser.id = user_detail.id 
     WHERE tbluser.email = ? OR user_detail.no_telepon = ?"
);
$stmt->bind_param("ss", $login_input, $login_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $data = $result->fetch_assoc()) {
    $db_hash = $data['password'];
    $authenticated = false;

    // Verifikasi password hybrid: modern bcrypt atau legacy sha1
    if (password_verify($raw_password, $db_hash)) {
        $authenticated = true;
        // Re-hash jika parameter/algoritma default berubah
        if (password_needs_rehash($db_hash, PASSWORD_DEFAULT)) {
            $new_hash = password_hash($raw_password, PASSWORD_DEFAULT);
            $stmt_re = $conn->prepare("UPDATE tbluser SET password = ? WHERE id = ?");
            $stmt_re->bind_param("si", $new_hash, $data['id']);
            $stmt_re->execute();
            $stmt_re->close();
        }
    } elseif (sha1($raw_password) === $db_hash) {
        $authenticated = true;
        // Auto-upgrade dari sha1 ke bcrypt
        $new_hash = password_hash($raw_password, PASSWORD_DEFAULT);
        $stmt_upg = $conn->prepare("UPDATE tbluser SET password = ? WHERE id = ?");
        $stmt_upg->bind_param("si", $new_hash, $data['id']);
        $stmt_upg->execute();
        $stmt_upg->close();
    }

    if ($authenticated) {
        // Regenerasi ID session untuk mencegah session fixation
        session_regenerate_id(true);

        $_SESSION['userid'] = $data['id'];
        $_SESSION['level'] = $data['level'];

        if ($login_input == $data['email']) {
            $_SESSION['email'] = $data['email'];
        } else {
            $_SESSION['no_telepon'] = $data['no_telepon'];
        }

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
        exit;
    }
}

if ($stmt) {
    $stmt->close();
}
header("Location: login_form.php?error=Email atau No Telepon atau Password salah");
exit;
?>
