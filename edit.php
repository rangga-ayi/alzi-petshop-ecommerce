<?php
session_start();
// PHP Data Js Search
include('php/php.php');

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: login/login_form.php");
    exit;
}

$allowed_levels = ['admin', 'penjual'];

  if (!in_array($_SESSION['level'], $allowed_levels)) {

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

// Ambil nilai ENUM untuk category
$query_enum_category = "SHOW COLUMNS FROM products LIKE 'category'";
$result_enum_category = mysqli_query($conn, $query_enum_category);
$enum_values_category = [];
if ($result_enum_category) {
    $row_enum_category = mysqli_fetch_assoc($result_enum_category);
    preg_match_all("/'([^']+)'/", $row_enum_category['Type'], $matches);
    $enum_values_category = $matches[1];
}

// Query untuk mengambil data produk
$query = "SELECT id, name, price, image FROM products";
$result = $conn->query($query);

$products = [];
if ($result->num_rows > 0) {
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
    $deskripsi_p = $productd['description'];
    $image_p = $productd['image'];
    $harga_p = $productd['price'];
    $category_p = $productd['category'];
    $satuan_p = $productd['satuan'];
}

// Nama Depan
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
    <title>Tambah Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <style>
    .navbar-brand {
        display: inline !important;
    }

    #tambah {
        display: none;
    }

    .container {
        padding-top: 5rem;
    }

    @media (max-width: 321px) {
        .navbar-brand {
            font-size: 17px;
        }
    }
    </style>
</head>

<body>
    <script src="script/script.js"></script>
    <!-- Navbar, Search, Keranjang, User -->
    <?php require('php/navbar.php'); ?>
    <!-- End Navbar, Search, Keranjang, User -->

    <div class="container px-3" style="color: black;">
        <h3 style="font-weight: bold;">Edit Produk</h3>
        <form action="php/proses_edit.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $product_id ?>"> <!-- Menambahkan ID Produk -->

            <div class="mb-3">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" name="name" id="name" value="<?= $nama_p ?>" class="form-control" required
                    autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control" rows="4" required
                    autocomplete="off"><?= htmlspecialchars($deskripsi_p) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="text" name="price" id="price" value="<?= $harga_p ?>" class="form-control" required
                    autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Kategori</label>
                <select name="category" id="category" class="form-control" required autocomplete="off">
                    <?php
                        // Menampilkan kategori dengan menandai kategori yang sudah dipilih
                        foreach ($enum_values_category as $value) {
                            $selected = ($category_p == $value) ? 'selected' : '';
                            echo "<option value='$value' $selected>$value</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="satuan" class="form-label">Satuan</label>
                <input type="text" name="satuan" id="satuan" value="<?= $satuan_p ?>" class="form-control" required
                    autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Foto Produk</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" autocomplete="off">
                <!-- Menampilkan gambar lama jika ada -->
                <?php if (!empty($productd['image'])): ?>
                <img src="<?= $productd['image'] ?>" alt="Gambar Produk" width="100" class="mt-2">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        </form>
        <a href="index_p.php" class="btn btn-warning my-2 me-1">Kembali</a>
        <a href="php/hapus.php?product_id=<?= $product_id ?>" class="btn btn-danger my-2">Hapus</a>
    </div>
    <!-- Footer start -->
    <footer class="text-center">
        <p>Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <!-- Footer End -->

</body>

</html>