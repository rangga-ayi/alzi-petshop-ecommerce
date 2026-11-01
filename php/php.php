<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "alzipetshop";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    function rupiah($angka){
        $hasil_rupiah = "Rp" . number_format($angka,0,',','.');
        return $hasil_rupiah;
    }

    if (!function_exists('haversine_distance')) {
        function haversine_distance($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371; // Radius bumi dalam km
            $latDiff = deg2rad($lat2 - $lat1);
            $lonDiff = deg2rad($lon2 - $lon1);
            $a = sin($latDiff / 2) * sin($latDiff / 2) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
                sin($lonDiff / 2) * sin($lonDiff / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            return $earthRadius * $c;
        }
    }

    if (!function_exists('get_shipping_info')) {
        function get_shipping_info($user_id, $conn) {
            // Titik Koordinat Toko Alzi Petshop (Palembang)
            $lat_toko = -3.0113878;
            $lon_toko = 104.6895402;

            $user_id = intval($user_id);
            $stmt = $conn->prepare("SELECT latitude, longitude FROM detail_address WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res && $row = $res->fetch_assoc()) {
                    $lat = floatval($row['latitude']);
                    $lon = floatval($row['longitude']);

                    if ($lat != 0 && $lon != 0) {
                        $distance = haversine_distance($lat_toko, $lon_toko, $lat, $lon);

                        // Struktur Tarif Masuk Akal
                        if ($distance <= 2) {
                            // Radius dekat toko (<= 2 km): tarif dasar Rp5.000
                            $cost = 5000;
                        } elseif ($distance <= 35) {
                            // Kurir lokal dalam kota (2 - 35 km): Rp5.000 + Rp2.000/km di atas 2 km (dibulatkan ke kelipatan Rp500)
                            $cost = 5000 + ceil(($distance - 2) * 2000 / 500) * 500;
                        } else {
                            // Ekspedisi reguler luar kota (> 35 km): Rp25.000 base + Rp500 per 10km (maks Rp75.000)
                            $extra = ceil(($distance - 35) / 10) * 500;
                            $cost = min(25000 + $extra, 75000);
                        }

                        $stmt->close();
                        return [
                            'has_address' => true,
                            'distance' => round($distance, 2),
                            'cost' => (int) $cost
                        ];
                    }
                }
                $stmt->close();
            }

            // Fallback jika pembeli belum mengisi titik koordinat alamat
            return [
                'has_address' => false,
                'distance' => 0,
                'cost' => 10000
            ];
        }
    }
?>
