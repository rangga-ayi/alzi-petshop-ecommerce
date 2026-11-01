<?php
    session_start();
    include 'php/php.php';

    // Periksa apakah user sudah login
    if (!isset($_SESSION['userid'])) {
        header("Location: login/login_form.php");
        exit;
    }

    $userid = $_SESSION['userid'];

    // Query untuk mengambil data user dari database
    $sql = "SELECT u.id, u.nama, u.email, d.jenis_kelamin, d.tanggal_lahir, d.alamat, d.no_telepon, d.alamat_detail, d.foto
            FROM tbluser u 
            LEFT JOIN user_detail d ON u.id = d.id 
            WHERE u.id = '$userid'";

    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama = $row['nama'];
        $email = $row['email'];
        $jenis_kelamin = $row['jenis_kelamin'];
        $tanggal_lahir = $row['tanggal_lahir'];
        $alamat = $row['alamat'];
        $alamat_detail = $row['alamat_detail'];
        $no_telepon = $row['no_telepon'];
        $foto = $row['foto'];
    } else {
        echo "Data user tidak ditemukan.";
    }

    // Ambil koordinat tersimpan dari detail_address jika ada
    $lat_saved = -3.0;
    $lon_saved = 104.7;
    $has_coords = false;
    $res_addr = mysqli_query($conn, "SELECT latitude, longitude FROM detail_address WHERE user_id = '$userid'");
    if ($res_addr && $row_addr = mysqli_fetch_assoc($res_addr)) {
        if (!empty($row_addr['latitude']) && !empty($row_addr['longitude'])) {
            $lat_saved = floatval($row_addr['latitude']);
            $lon_saved = floatval($row_addr['longitude']);
            $has_coords = true;
        }
    }

    // Proses update data user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_baru = $_POST['nama'];
    $email_baru = $_POST['email'];
    $jenis_kelamin_baru = $_POST['jenis_kelamin'];
    $tanggal_lahir_baru = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
    $alamat_baru = $_POST['address'];
    $alamat_detail_baru = $_POST['alamat'];
    $no_telepon_baru = $_POST['no_telepon'];
    $destination = explode(',', $_POST['destination']);

    // Update tbluser
    $sql_update_user = "UPDATE tbluser SET nama = ?, email = ? WHERE id = ?";
    $stmt_user = $conn->prepare($sql_update_user);
    $stmt_user->bind_param('ssi', $nama_baru, $email_baru, $userid);
    $stmt_user->execute();

    // Update user_detail
    $sql_update_detail = "UPDATE user_detail SET jenis_kelamin = ?, tanggal_lahir = ?, alamat = ?, no_telepon = ?, alamat_detail = ? WHERE id = ?";
    $stmt_detail = $conn->prepare($sql_update_detail);
    $stmt_detail->bind_param('sssssi', $jenis_kelamin_baru, $tanggal_lahir_baru, $alamat_baru, $no_telepon_baru, $alamat_detail_baru, $userid);
    $stmt_detail->execute();

    // Validasi data latitude dan longitude
    if (count($destination) == 2) {
        $lat = floatval(trim($destination[0]));
        $lon = floatval(trim($destination[1]));
    } else {
        die("Data lokasi tidak valid.");
    }

    // Cek apakah user_id sudah ada di detail_address
    $sql_check_address = "SELECT id FROM detail_address WHERE user_id = ?";
    $stmt_check = $conn->prepare($sql_check_address);
    $stmt_check->bind_param('i', $userid);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika data ada, lakukan update
        $row = $result_check->fetch_assoc();
        $id_address = $row['id'];

        $sql_update_address = "UPDATE detail_address SET latitude = ?, longitude = ? WHERE id = ?";
        $stmt_update_address = $conn->prepare($sql_update_address);
        $stmt_update_address->bind_param('ddi', $lat, $lon, $id_address);
        $stmt_update_address->execute();
    } else {
        // Jika data tidak ada, lakukan insert
        $sql_insert_address = "INSERT INTO detail_address (user_id, latitude, longitude) VALUES (?, ?, ?)";
        $stmt_insert_address = $conn->prepare($sql_insert_address);
        $stmt_insert_address->bind_param('idd', $userid, $lat, $lon);
        $stmt_insert_address->execute();
    }

        // Redirect setelah update
        header("Location: detail.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Metadata -->
    <?php include('metadata.php'); ?>
    <title>Ubah Data</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Open Street Map -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- My Style -->
    <link rel="stylesheet" href="css/bootstrap_style.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
    html,
    body {
        width: 100%;
        height: 100vh;
    }

    .card {
        padding-top: 1rem;
    }

    .row {
        padding-top: 2.5rem;
    }

    .navbar {
        position: sticky;
        z-index: 9999;
    }

    footer {
        bottom: 0;
    }

    @media (max-width: 435px) {
        body {
            overflow-y: auto;
        }

        footer {
            position: static;
            bottom: 0;
            padding-top: 1rem;
        }
    }

    @media (min-width: 768px) {
        footer {
            background-color: white;
            margin-top: 2rem;
            padding: 1rem 0 0.1rem 0;
            width: 100%;
        }
    }

    h4 {
        font-weight: bold;
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand ms-2 font-weight-bold" href="login/login_form.php">
                Alzi Petshop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                </ul>
                <div class="navbar-item">
                    <a href="detail.php">
                        <img src="<?php echo $foto; ?>" class="rounded-circle me-2">
                        <span class="fw-bold"><?php echo $nama; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->
    <!-- Form Ubah Data -->
    <div class="container vh-100 mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="text-center">Ubah Data</h4>
                        <form method="post" action="">
                            <div class="form-group mb-3">
                                <label for="nama">Nama:</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?php echo $nama; ?>" required autocomplete="off">
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo $email; ?>" required autocomplete="off">
                            </div>
                            <div class="form-group mb-3">
                                <label for="jenis_kelamin">Jenis Kelamin:</label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required
                                    autocomplete="off">
                                    <option value="Laki-laki"
                                        <?php if ($jenis_kelamin == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                                    <option value="Perempuan"
                                        <?php if ($jenis_kelamin == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="tanggal_lahir">Tanggal Lahir:</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                    value="<?php echo $tanggal_lahir; ?>" required autocomplete="off">
                            </div>
                            <div class="form-group mb-3">
                                <label for="alamat">Alamat detail / patokan rumah (Optional):</label>
                                <textarea class="form-control" id="alamat" name="alamat"
                                    autocomplete="off"><?php echo $alamat_detail; ?></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="address" class="form-label">Alamat (pilih titik lokasi):</label>
                                <input type="text" id="address" name="address" class="form-control"
                                    value="<?php echo $alamat; ?>" readonly required>
                                <input type="text" id="destination" name="destination" value="<?php echo $has_coords ? ($lat_saved . ',' . $lon_saved) : ''; ?>" readonly required hidden><br>
                                <div id="map" style="height: 500px; width: 100%;"></div>

                                <script>
                                // Inisialisasi peta
                                var initialLat = <?php echo json_encode($lat_saved); ?>;
                                var initialLon = <?php echo json_encode($lon_saved); ?>;
                                var hasExisting = <?php echo $has_coords ? 'true' : 'false'; ?>;

                                var map = L.map('map').setView([initialLat, initialLon], hasExisting ? 16 : 12);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap contributors'
                                }).addTo(map);

                                var destinationMarker;
                                var searchMarker;

                                // Ikon untuk marker klik dan hasil pencarian
                                var clickIcon = L.icon({
                                    iconUrl: 'imgs/marker/klik_marker.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34]
                                });

                                var searchIcon = L.icon({
                                    iconUrl: 'imgs/marker/search_marker.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34]
                                });

                                if (hasExisting) {
                                    destinationMarker = L.marker([initialLat, initialLon], {
                                            icon: clickIcon
                                        })
                                        .addTo(map)
                                        .bindPopup('Lokasi Tersimpan')
                                        .openPopup();
                                }

                                // Fungsi untuk memperbarui marker klik
                                function updateClickMarker(latlng) {
                                    if (destinationMarker) {
                                        map.removeLayer(destinationMarker); // Hapus marker sebelumnya
                                    }
                                    destinationMarker = L.marker(latlng, {
                                            icon: clickIcon
                                        })
                                        .addTo(map)
                                        .bindPopup('Lokasi Tujuan')
                                        .openPopup();
                                    document.getElementById('destination').value = latlng.lat + ',' + latlng.lng;
                                    fetchAddress(latlng); // Ambil alamat dari koordinat
                                }

                                // Fungsi untuk memperbarui marker hasil pencarian
                                function updateSearchMarker(latlng) {
                                    if (searchMarker) {
                                        map.removeLayer(searchMarker); // Hapus marker sebelumnya
                                    }
                                    searchMarker = L.marker(latlng, {
                                            icon: searchIcon
                                        })
                                        .addTo(map)
                                        .bindPopup('Hasil Pencarian')
                                        .openPopup();
                                    document.getElementById('destination').value = latlng.lat + ',' + latlng.lng;
                                    fetchAddress(latlng); // Ambil alamat dari koordinat
                                }

                                // Fungsi untuk mendapatkan alamat detail dari koordinat
                                async function fetchAddress(latlng) {
                                    const url =
                                        `https://nominatim.openstreetmap.org/reverse?lat=${latlng.lat}&lon=${latlng.lng}&format=json`;
                                    try {
                                        const response = await fetch(url);
                                        if (!response.ok) throw new Error('Gagal mendapatkan data alamat');
                                        const data = await response.json();
                                        const address = data.display_name || 'Alamat tidak ditemukan';
                                        document.getElementById('address').value =
                                            address; // Perbarui input alamat detail
                                    } catch (error) {
                                        console.error(error);
                                        document.getElementById('address').value = 'Gagal mendapatkan alamat';
                                    }
                                }

                                // Tambahkan geocoder untuk pencarian lokasi
                                var geocoder = L.Control.Geocoder.nominatim();
                                var geocoderControl = L.Control.geocoder({
                                    position: 'topleft',
                                    geocoder: geocoder
                                }).addTo(map);

                                // Ketika hasil pencarian geocoder ditemukan
                                geocoderControl.on('markgeocode', function(e) {
                                    var latlng = e.geocode.center;
                                    map.setView(latlng, 16); // Perbesar peta ke lokasi hasil pencarian
                                    updateSearchMarker(latlng); // Perbarui marker hasil pencarian
                                });

                                // Klik pada peta untuk memilih atau mengganti lokasi tujuan
                                map.on('click', function(e) {
                                    updateClickMarker(e.latlng); // Perbarui marker klik
                                });
                                </script>

                            </div>
                            <div class="form-group mb-3">
                                <label for="no_telepon">No. Telepon:</label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon"
                                    value="<?php echo $no_telepon; ?>" required autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                            <a href="detail.php" class="btn btn-danger">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <footer class="text-center p-2 mt-3">
            <p>Create by Alzi Petshop | &copy 2024</p>
        </footer>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>