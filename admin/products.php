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
    WHERE u.id = ?";
    $stmt_u = $conn->prepare($sql);
    $stmt_u->bind_param("i", $userid);
    $stmt_u->execute();
    $result = $stmt_u->get_result();

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
    <title>Product List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .deskripsi-terbatas {
        cursor: pointer;
        position: relative;
        padding-bottom: 1rem;
    }

    .deskripsi-terbatas span {
        display: block;
    }

    .deskripsi-terbatas button {
        color: #007bff;
        text-decoration: underline;
        border: none;
        background: transparent;
    }
    </style>
</head>

<body>
    <?php include('navbar_admin.php'); ?>
    <div class="container-fluid px-4 py-4 h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-dark fw-bold"><i class="bi bi-boxes me-2"></i>Data Master Produk</h3>
            <a href="tambah.php" class="btn btn-primary btn-sm px-3 shadow-xs">
                <i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru
            </a>
        </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="col-2">Name</th>
                            <th class="col-4">Description</th>
                            <th>Price</th>
                            <th>Image</th>
                            <th class="hide-on-mobile">Category</th>
                            <th class="hide-on-mobile">Satuan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                $sql = "SELECT * FROM products";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $id_safe = htmlspecialchars($row['id']);
                                        $name_safe = htmlspecialchars($row['name']);
                                        $price_safe = rupiah($row['price']);
                                        $img_safe = htmlspecialchars($row['image']);
                                        $cat_safe = htmlspecialchars($row['category']);
                                        $satuan_safe = htmlspecialchars($row['satuan']);

                                        echo "<tr>
                                                <td>{$id_safe}</td>
                                                <td>{$name_safe}</td>";
                                            ?>
                        <td id="description-<?php echo $row['id']; ?>" class="deskripsi-terbatas"
                            onclick="toggleDescription(<?php echo $row['id']; ?>)">
                            <?php 
                                            $maxLength = 150;
                                            $description = nl2br(htmlspecialchars($row['description']));
                                            $shortDesc = substr($description, 0, $maxLength);
                                            $isTruncated = strlen($description) > $maxLength;
                                            echo '<span id="short-desc-' . $row['id'] . '">' . $shortDesc . ($isTruncated ? '...' : '') . '</span>';
                                            echo '<span id="full-desc-' . $row['id'] . '" style="display:none;">' . $description . '</span>';
                                        ?>
                            <?php if ($isTruncated): ?>
                            <button id="toggle-desc-<?php echo $row['id']; ?>" type="button" class="btn btn-link p-0"
                                style="pointer-events: none; text-decoration: none; color: rgb(255, 180, 0); font-weight: bold;">Lihat
                                Selengkapnya</button>
                            <?php endif; ?>
                        </td>
                        <?php
                                                echo "
                                                <td>{$price_safe}</td>
                                                <td><img src='../{$img_safe}' alt='{$name_safe}' width='100'></td>
                                                <td class='hide-on-mobile'>{$cat_safe}</td>
                                                <td class='hide-on-mobile'>{$satuan_safe}</td>
                                                <td>
                                                    <a href='edit.php?product_id={$id_safe}' class='btn btn-warning my-1'>Edit</a>
                                                    <a href='../php/hapus.php?product_id={$id_safe}' class='btn btn-danger my-1' onclick=\"return confirm('Apakah Anda yakin ingin menghapus produk ini?');\">Delete</a>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No products found</td></tr>";
                                }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    function toggleDescription(id) {
        const shortDesc = document.getElementById(`short-desc-${id}`);
        const fullDesc = document.getElementById(`full-desc-${id}`);
        const button = document.getElementById(`toggle-desc-${id}`);

        if (fullDesc.style.display === 'none' || fullDesc.style.display === '') {
            fullDesc.style.display = 'inline';
            shortDesc.style.display = 'none';
            button.textContent = 'Sembunyikan';
        } else {
            fullDesc.style.display = 'none';
            shortDesc.style.display = 'inline';
            button.textContent = 'Lihat Selengkapnya';
        }
    }
    </script>
    <footer class="text-center">
        <p>Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
<?php $conn->close(); ?>