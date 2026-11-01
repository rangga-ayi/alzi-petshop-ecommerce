<?php
include '../php/php.php';

// Proses insert data user baru
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = sha1($_POST['password']); // Enkripsi password
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $tanggal_lahir = !empty(trim($_POST['tanggal_lahir'])) ? trim($_POST['tanggal_lahir']) : null;
    $alamat = trim($_POST['address']);
    $alamat_detail = trim($_POST['alamat']);
    $no_telepon = trim($_POST['no_telepon']);
    $destination = explode(',', $_POST['destination']);
    $default_foto = 'imgs/user/default.png'; // Path foto default
    
    // Validasi data latitude dan longitude
    if (count($destination) == 2) {
        $lat = floatval(trim($destination[0]));
        $lon = floatval(trim($destination[1]));
    } else {
        die("Data lokasi tidak valid.");
    }

    // Mulai transaksi database
    $conn->begin_transaction();

    try {
        // Insert ke tabel tbluser
        $sql_insert_user = "INSERT INTO tbluser (nama, email, password, level) VALUES (?, ?, ?, 'pembeli')";
        $stmt_user = $conn->prepare($sql_insert_user);
        $stmt_user->bind_param('sss', $nama, $email, $password);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id; // Mendapatkan ID user yang baru ditambahkan

        // Insert ke tabel user_detail
        $sql_insert_detail = "INSERT INTO user_detail (id, foto, jenis_kelamin, tanggal_lahir, alamat, no_telepon, alamat_detail) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_insert_detail);
        $stmt_detail->bind_param('issssss', $user_id, $default_foto, $jenis_kelamin, $tanggal_lahir, $alamat, $no_telepon, $alamat_detail);
        $stmt_detail->execute();

        // Insert ke tabel detail_address
        $sql_insert_address = "INSERT INTO detail_address (user_id, latitude, longitude) VALUES (?, ?, ?)";
        $stmt_address = $conn->prepare($sql_insert_address);
        $stmt_address->bind_param('idd', $user_id, $lat, $lon);
        $stmt_address->execute();

        // Commit transaksi
        $conn->commit();

        // Redirect setelah registrasi berhasil
        header("Location: login_form.php");
        exit;

    } catch (Exception $e) {
        // Rollback jika ada kesalahan
        $conn->rollback();
        die("Terjadi kesalahan saat registrasi: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Metadata -->
    <?php include('../metadata.php'); ?>
    <title>Register</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Open Street Map -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- My Style -->
    <link rel="stylesheet" href="../css/bootstrap_style.css">
    <link rel="stylesheet" href="../css/style.css">
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
            <a class="navbar-brand ms-2 font-weight-bold" href="login_form.php">
                Alzi Petshop
            </a>
    </nav>
    <!-- End Navbar -->
    <!-- Form Ubah Data -->
    <div class="container vh-100 mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="text-center">Register</h4>
                        <form method="post" action="">
                            <div class="form-group mb-3">
                                <label for="nama">Nama:</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="" required
                                    autocomplete="off">
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" value="" required
                                    autocomplete="off">
                            </div>
                            <div class="form-group mb-4">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="jenis_kelamin">Jenis Kelamin:</label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required
                                    autocomplete="off">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="tanggal_lahir">Tanggal Lahir:</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value=""
                                    required autocomplete="off">
                            </div>
                            <div class="form-group mb-3">
                                <label for="alamat">Alamat detail / patokan rumah (Optional):</label>
                                <textarea class="form-control" id="alamat" name="alamat" autocomplete="off"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="address" class="form-label">Alamat (pilih titik lokasi):</label>
                                <input type="text" id="address" name="address" class="form-control" readonly required>
                                <input type="text" id="destination" name="destination" readonly required hidden><br>
                                <div id="map" style="height: 500px; width: 100%;"></div>

                                <script>
                                // Inisialisasi peta
                                var map = L.map('map').setView([-3.0, 104.7], 12); // Lokasi awal di Palembang
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
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon" value=""
                                    required autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                            <a href="login_form.php" class="btn btn-danger">Batal</a>
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