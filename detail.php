<?php
    session_start();
    include 'php/php.php';

    // Periksa apakah user sudah login
    if (!isset($_SESSION['userid'])) {
        header("Location: login/login_form.php");
        exit;
    }

    $userid = $_SESSION['userid']; // Ambil user ID dari session
    $user_id = $_SESSION['userid'];

    // Query untuk mengambil data dari kedua tabel
    $sql = "SELECT u.id, u.nama, u.email, u.level, d.foto, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.alamat_detail, d.no_telepon 
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
        $alamat_detail = $row['alamat_detail'];
        $no_telepon = $row['no_telepon'];
    } else {
        echo "Data user tidak ditemukan.";
    }

    // Ambil koordinat dari detail_address untuk preview peta
    $has_location = false;
    $user_lat = 0;
    $user_lon = 0;
    $stmt_addr = $conn->prepare("SELECT latitude, longitude FROM detail_address WHERE user_id = ?");
    $stmt_addr->bind_param("i", $userid);
    $stmt_addr->execute();
    $res_addr = $stmt_addr->get_result();
    if ($res_addr && $row_addr = mysqli_fetch_assoc($res_addr)) {
        if (!empty($row_addr['latitude']) && !empty($row_addr['longitude'])) {
            $user_lat = floatval($row_addr['latitude']);
            $user_lon = floatval($row_addr['longitude']);
            $has_location = true;
        }
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
    <title>Profile Saya</title>

    <!-- Metadata -->
    <?php include('metadata.php'); ?>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Leaflet OpenStreetMap -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <!-- My Style -->
    <link rel="stylesheet" href="css/bootstrap_style.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
    html,
    body {
        width: 100%;
        height: 100%;
    }

    .navbar {
        position: fixed;
    }

    #tambah {
        display: none;
    }

    .container {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    #user {
        display: inline;
    }

    footer {
        background-color: white;
        width: 100%;
    }

    h4 {
        font-weight: bold;
    }

    table {
        height: 100%;
    }

    table a {
        color: var(--secondary-color);
        text-decoration: none;
    }

    table a:hover {
        color: black;
    }

    table td {
        text-align: left;
    }

    table .ubah {
        text-align: right;
    }

    /* Polishing button styling preserving original visual identity */
    .img-user .btn-action {
        width: 100%;
        border-radius: 8px;
        font-weight: 500;
        padding: 0.55rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    .img-user .btn-action:hover,
    td.ubah .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        color: inherit;
    }

    .img-user .btn-action:active,
    td.ubah .btn-action:active {
        transform: translateY(0);
        box-shadow: none;
    }

    td.ubah .btn-action {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.45rem 1rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    table a.btn-action {
        color: inherit;
    }

    form {
        width: 100%;
        height: 50%;
    }

    .img-user {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .row {
        width: 100%;
        display: flex;
    }

    .profile-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
    }

    @media (min-width: 320px) {
        .container {
            padding-top: 4rem;
            font-size: 13px;
            height: auto;
        }

        .img-user {
            padding: 0 20px;
        }

        .navbar-brand {
            display: flex;
        }

        .btn {
            font-size: 14px;
        }

        h4 {
            font-size: 16px;
        }
    }

    @media (min-width: 375px) {
        .container {
            padding-top: 4rem;
            font-size: 13px;
            height: auto;
        }

        .img-user {
            padding: 0 20px;
        }

        .navbar-brand {
            display: flex;
        }

        .btn {
            font-size: 14px;
        }

        h4 {
            font-size: 16px;
        }
    }

    @media (min-width: 425px) {
        .container {
            padding-top: 4rem;
            font-size: 13px;
            height: auto;
        }

        #brand {
            display: inline;
        }

        .img-user {
            padding: 0 20px;
        }

        .navbar-brand {
            display: flex;
        }

        .btn {
            font-size: 14px;
        }

        h4 {
            font-size: 16px;
        }
    }

    @media (min-width: 768px) {
        .container {
            height: 90vh;
            font-size: 16px;
        }

        .img-user {
            margin-bottom: 5rem;
            padding: 0;
        }

        .navbar {
            padding: 0.5rem 1rem;
        }

        .navbar-brand {
            display: flex;
        }

        h4 {
            font-size: 20px;
        }

        footer {
            margin-top: 3rem;
            background-color: white;
            bottom: 0;
            width: 100%;
        }
    }

    @media (min-width: 1024px) {
        .container {
            height: 85vh;
        }

        .navbar-brand {
            display: flex;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar, Search, Keranjang, User -->
    <?php require('php/navbar.php'); ?>
    <!-- End Navbar, Search, Keranjang, User -->

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="img-user text-center">
                    <img src="<?php echo $foto; ?>" class="profile-img mb-3" alt="Foto Profil">
                    <h3 style="font-size: 1.3rem;" class="fw-bold"><?php echo $nama; ?></h3>
                    <a href="ubah_foto.php" class="btn btn-warning btn-action mt-3">
                        <i class="bi bi-camera"></i> Ubah Foto Profil
                    </a>
                    <a href="ubah_sandi.php" class="btn btn-warning btn-action mt-2">
                        <i class="bi bi-shield-lock"></i> Ubah Kata Sandi
                    </a>
                    <a class="btn btn-danger btn-action mt-2" href="login/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
            <div class="col-md-9">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2" class="align-middle">
                                <h4 class="mb-0">Profile Saya</h4>
                            </th>
                            <td class="ubah align-middle">
                                <a class="btn btn-warning btn-action" href="ubah_data.php">
                                    <i class="bi bi-pencil-square"></i> Ubah Data
                                </a>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Nama</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($nama); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($email); ?></td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($jenis_kelamin); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Lahir</th>
                            <td>:</td>
                            <td><?php
                            function formatTanggalIndonesia($tanggal) {
                                $bulan = [
                                    1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                                    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                                ];
                                $pecahkan = explode('-', $tanggal); // Pecah string tanggal
                                return $pecahkan[2] . '-' . $bulan[(int)$pecahkan[1]] . '-' . $pecahkan[0];
                            }
                            
                            if (!empty($tanggal_lahir)) {
                                $tanggal_asli = $tanggal_lahir;
                                $tanggal_format = formatTanggalIndonesia($tanggal_asli);
                                echo $tanggal_format;
                            }
                            ?></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($alamat); ?></td>
                        </tr>
                        <tr>
                            <th>Patokan Rumah</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($alamat_detail); ?></td>
                        </tr>
                        <tr>
                            <th>No. Telepon</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($no_telepon); ?></td>
                        </tr>
                        <?php if ($has_location): ?>
                        <tr>
                            <th>Preview Lokasi</th>
                            <td>:</td>
                            <td>
                                <div id="map-preview" style="height: 220px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="text-center">
        <p class="p-3">Create by Alzi Petshop | &copy 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <?php if ($has_location): ?>
    <script>
        var previewMap = L.map('map-preview').setView([<?= $user_lat ?>, <?= $user_lon ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(previewMap);
        var clickIcon = L.icon({
            iconUrl: 'imgs/marker/klik_marker.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });
        L.marker([<?= $user_lat ?>, <?= $user_lon ?>], { icon: clickIcon })
            .addTo(previewMap)
            .bindPopup('<?= htmlspecialchars($alamat ? $alamat : "Lokasi Saya") ?>')
            .openPopup();
    </script>
    <?php endif; ?>
</body>

</html>