-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 20, 2025 at 07:58 AM
-- Server version: 8.0.40
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alzipetshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 5, 3, 84000.00),
(2, 1, 2, 3, 66000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_address`
--

CREATE TABLE `detail_address` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_address`
--

INSERT INTO `detail_address` (`id`, `user_id`, `latitude`, `longitude`) VALUES
(1, 1, -2.92634595, 104.78258616),
(2, 4, -2.97857850, 104.74578805),
(3, 3, -3.26236100, 102.38027350),
(4, 2, -3.00203090, 104.69663310);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('Makanan','Peralatan','Aksesoris','Kesehatan','Kebersihan') DEFAULT NULL,
  `satuan` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category`, `satuan`) VALUES
(1, 'Bolt - Makanan Kucing', 'Bolt Premium Cat Food adalah pilihan terbaik untuk memenuhi kebutuhan nutrisi harian kucing Anda. Diperkaya dengan formula lengkap dan seimbang, Bolt dirancang untuk menjaga kesehatan kucing Anda, dari bulu yang berkilau hingga energi yang optimal setiap harinya.\r\n\r\nKeunggulan Produk:\r\n\r\nProtein Berkualitas Tinggi: Membantu mendukung pertumbuhan otot yang kuat dan kesehatan tubuh.\r\nKandungan Omega 3 & 6: Memperindah bulu dan menjaga kulit tetap sehat.\r\nSerat Alami: Membantu menjaga sistem pencernaan yang sehat dan mencegah hairball.\r\nVitamin & Mineral Lengkap: Mendukung kekebalan tubuh dan vitalitas kucing Anda.\r\nRasa Lezat: Disukai oleh kucing, membuat waktu makan menjadi momen yang menyenangkan.\r\nTersedia Dalam Berbagai Pilihan Rasa:\r\n\r\nAyam\r\nSalmon\r\nTuna\r\nDaging Sapi\r\nKemasan Praktis:\r\nBolt hadir dalam berbagai ukuran (500g, 1kg, dan 5kg) untuk memenuhi kebutuhan Anda, baik untuk kucing rumahan maupun pemilik dengan banyak kucing.\r\n\r\nDengan Bolt Premium Cat Food, pastikan kucing kesayangan Anda mendapatkan asupan terbaik untuk hidup sehat, bahagia, dan aktif sepanjang hari. 🌟\r\n\r\n\"Bolt, pilihan cerdas untuk kucing sehat dan bahagia!\"', 2000.00, './imgs/bolt.jpeg', 'Makanan', 'bungkus'),
(2, 'Lezato - Makanan Kucing', 'Lezato Cat Food adalah solusi sempurna untuk kebutuhan nutrisi harian kucing Anda. Dibuat dengan bahan-bahan berkualitas tinggi, Lezato memastikan setiap gigitan dipenuhi dengan kebaikan yang diperlukan untuk kesehatan dan kebahagiaan kucing Anda.\r\n\r\nKeunggulan Produk:\r\n\r\nKaya Protein: Mendukung pertumbuhan otot yang kuat dan memberikan energi optimal.\r\nAsam Lemak Omega: Membantu menjaga bulu tetap lebat dan berkilau, serta kulit sehat.\r\nSerat Alami: Membantu meningkatkan pencernaan dan mencegah masalah hairball.\r\nRasa yang Disukai Kucing: Dengan aroma dan rasa yang menggoda, kucing Anda akan makan dengan lahap.\r\nVitamin dan Mineral Lengkap: Menunjang daya tahan tubuh dan kesehatan organ dalam.\r\nPilihan Rasa yang Menggugah Selera:\r\n\r\nIkan Laut\r\nAyam Kampung\r\nTuna Segar\r\nKemasan Tersedia:\r\nLezato hadir dalam berbagai ukuran kemasan (1kg, 3kg, dan 10kg), cocok untuk kucing tunggal hingga pemilik dengan banyak kucing.\r\n\r\nDengan Lezato Cat Food, berikan kasih sayang terbaik kepada kucing kesayangan Anda dengan makanan bernutrisi tinggi dan rasa lezat yang akan mereka cintai.\r\n\r\n\"Lezato, solusi lezat untuk kucing sehat dan aktif setiap hari!\" 🐾', 22000.00, './imgs/678d181eb08c4_lezato.jpeg', 'Makanan', 'bungkus'),
(3, 'Ori Cat - Makanan Kucing', 'Ori Cat Food adalah pilihan sempurna untuk pemilik kucing yang mencari makanan berkualitas tinggi dengan harga terjangkau. Diformulasikan secara khusus untuk mendukung kebutuhan gizi kucing Anda, Ori Cat memastikan setiap gigitan memberikan nutrisi seimbang untuk kesehatan dan kebahagiaan mereka.\r\n\r\nKeunggulan Produk:\r\n\r\nProtein Berkualitas Tinggi: Menunjang pertumbuhan dan menjaga kesehatan otot kucing.\r\nFormula Rendah Lemak: Membantu menjaga berat badan ideal dan mencegah obesitas.\r\nKandungan Serat Alami: Membantu memperlancar pencernaan dan mengurangi hairball.\r\nRasa yang Disukai Kucing: Aroma dan rasa yang menggoda, cocok untuk kucing yang pemilih.\r\nDiperkaya Vitamin dan Mineral: Mendukung sistem kekebalan tubuh dan kesehatan gigi.\r\nVarian Rasa yang Tersedia:\r\n\r\nAyam Panggang\r\nIkan Tuna\r\nDaging Sapi\r\nPilihan Kemasan:\r\nOri Cat tersedia dalam berbagai ukuran kemasan (500g, 1kg, dan 5kg) yang sesuai dengan kebutuhan Anda.\r\n\r\nBerikan kasih sayang terbaik untuk kucing Anda dengan Ori Cat Food yang lezat, bernutrisi, dan cocok untuk semua jenis kucing, baik anak kucing maupun dewasa.\r\n\r\n\"Ori Cat, pilihan bijak untuk kucing sehat dan bahagia setiap hari!\" 🐾', 18000.00, './imgs/oricat.jpeg', 'Makanan', 'bungkus'),
(4, 'Cat Choise - Makanan Kucing', 'Cat Choice Food adalah makanan kucing premium yang dirancang untuk memenuhi kebutuhan nutrisi kucing Anda secara menyeluruh. Dengan bahan-bahan pilihan dan formula khusus, Cat Choice memastikan kucing Anda tetap sehat, aktif, dan bahagia.\r\n\r\nKeunggulan Produk:\r\n\r\nKaya Protein Berkualitas: Membantu mendukung pertumbuhan otot dan kesehatan kucing.\r\nOmega 3 & 6: Untuk menjaga kesehatan kulit dan bulu agar tetap lembut dan berkilau.\r\nSerat Alami: Membantu mengurangi hairball dan menjaga pencernaan yang sehat.\r\nDiperkaya dengan Taurin: Mendukung kesehatan mata dan jantung kucing.\r\nRasa Lezat yang Disukai Kucing: Membuat waktu makan menjadi momen yang dinantikan.\r\nPilihan Varian Rasa:\r\n\r\nAyam\r\nIkan Salmon\r\nDaging Kelinci\r\nKemasan yang Praktis:\r\nCat Choice hadir dalam berbagai ukuran (500g, 1kg, dan 3kg) yang sesuai untuk kebutuhan harian atau stok jangka panjang.\r\n\r\nCocok untuk:\r\n\r\nSemua jenis kucing, baik anak kucing, kucing dewasa, maupun kucing senior.\r\nPemilik yang ingin memberikan makanan bernutrisi tinggi tanpa menguras kantong.\r\n\"Cat Choice, karena kucing Anda layak mendapatkan pilihan terbaik!\" 🐱', 25000.00, './imgs/catchoise.jpg', 'Makanan', 'bungkus'),
(5, 'Whiskas - Makanan Kucing', 'Whiskas Cat Food adalah makanan kucing terpercaya yang diformulasikan khusus untuk memenuhi kebutuhan gizi kucing pada setiap tahap kehidupannya. Dengan rasa yang lezat dan tekstur yang menarik, Whiskas menjadi pilihan favorit bagi pemilik kucing di seluruh dunia.\r\n\r\nKeunggulan Produk:\r\n\r\nNutrisi Lengkap dan Seimbang: Mendukung kesehatan dan vitalitas kucing setiap hari.\r\nKaya Vitamin dan Mineral: Untuk tulang yang kuat, sistem imun yang sehat, dan energi optimal.\r\nDiperkaya Taurin: Membantu menjaga penglihatan yang tajam dan kesehatan jantung.\r\nOmega 3 & 6: Mendukung kulit sehat dan bulu yang berkilau.\r\nBeragam Tekstur: Tersedia dalam bentuk kering, basah, dan pouch untuk memenuhi preferensi kucing Anda.\r\nPilihan Varian Rasa:\r\n\r\nIkan Tuna\r\nAyam\r\nIkan Laut\r\nDaging Sapi\r\nKemasan yang Praktis dan Beragam:\r\nTersedia dalam ukuran mulai dari 85g hingga 10kg, cocok untuk konsumsi harian atau persediaan jangka panjang.\r\n\r\nCocok untuk:\r\n\r\nAnak kucing, kucing dewasa, hingga kucing senior.\r\nKucing dengan berbagai selera dan kebutuhan khusus.\r\n\"Whiskas, pilihan yang alami untuk kucing kesayangan Anda!\" 🐾', 28000.00, './imgs/whiskas.jpg', 'Makanan', 'bungkus'),
(7, 'Kandang Kucing', 'Kandang Kucing Multifungsi adalah solusi praktis untuk memastikan kenyamanan dan keamanan kucing kesayangan Anda. Dirancang dengan bahan berkualitas dan fitur modern, kandang ini cocok digunakan di dalam maupun luar ruangan. Tersedia dalam berbagai ukuran untuk memenuhi kebutuhan kucing Anda, baik yang masih kitten maupun dewasa.\r\n\r\nKeunggulan Produk:\r\n\r\nBahan Tahan Lama: Terbuat dari besi anti karat atau plastik tebal yang mudah dibersihkan.\r\nDesain Aman: Jarak antar jeruji dirancang aman untuk mencegah kucing terjepit.\r\nVentilasi Maksimal: Memberikan sirkulasi udara yang baik untuk kenyamanan kucing.\r\nPintu Ganda dengan Kunci Pengaman: Memudahkan akses sekaligus menjaga keamanan kucing.\r\nBaki Alas yang Bisa Dilepas: Mempermudah proses pembersihan kotoran.\r\nFitur Lipat (Foldable): Mudah disimpan dan dibawa saat bepergian.\r\nVarian Ukuran:\r\n\r\nKecil (60x40x40 cm): Cocok untuk kitten atau kucing kecil.\r\nSedang (90x60x60 cm): Ideal untuk satu atau dua kucing dewasa.\r\nBesar (120x90x90 cm): Untuk kucing besar atau digunakan sebagai kandang multi-level.\r\nPilihan Fitur Tambahan:\r\n\r\nMulti-Level: Dilengkapi dengan tangga dan platform untuk bermain.\r\nRoda Berputar: Memudahkan Anda memindahkan kandang.\r\nTirai Penutup: Memberikan privasi dan melindungi kucing dari angin atau sinar matahari langsung.\r\nCocok untuk:\r\n\r\nTempat tinggal sementara, saat perjalanan, atau ketika kucing perlu isolasi karena sakit.\r\nPemilik kucing yang mengutamakan kebersihan dan keamanan peliharaan.\r\n\"Kandang kucing yang nyaman dan aman, memberikan ketenangan hati untuk Anda dan kebahagiaan untuk kucing kesayangan.\" 🐾', 170000.00, './imgs/kandang.jpg', 'Peralatan', 'buah'),
(13, 'Royal Canin 2KG - Makanan Kucing', 'Royal Canin Cat Food adalah makanan premium yang dirancang khusus untuk memenuhi kebutuhan spesifik kucing berdasarkan usia, ras, gaya hidup, hingga kondisi kesehatannya. Dengan formula yang didukung oleh penelitian ilmiah, Royal Canin menjadi pilihan ideal untuk memberikan nutrisi terbaik bagi kucing kesayangan Anda.\r\n\r\nKeunggulan Produk:\r\n\r\nFormulasi Berdasarkan Kebutuhan Spesifik: Tersedia untuk kucing persia, maine coon, siam, dan ras lainnya, serta kucing dengan kondisi tertentu seperti obesitas atau masalah pencernaan.\r\nNutrisi Lengkap dan Seimbang: Mendukung kesehatan jangka panjang, sistem imun, dan fungsi tubuh optimal.\r\nProtein Berkualitas Tinggi: Sumber energi untuk pertumbuhan dan aktivitas kucing.\r\nKombinasi Vitamin & Mineral yang Tepat: Menjaga kesehatan kulit, bulu, tulang, dan gigi.\r\nTekstur dan Ukuran Kibble Disesuaikan: Mempermudah kucing dalam mengunyah sesuai bentuk rahangnya.\r\nVarian Utama:\r\n\r\nKitten Formula: Untuk mendukung pertumbuhan anak kucing.\r\nAdult Formula: Nutrisi lengkap untuk kucing dewasa aktif.\r\nIndoor Formula: Mengurangi bau feses, ideal untuk kucing yang tidak keluar rumah.\r\nHairball Care: Membantu mencegah hairball pada kucing berbulu panjang.\r\nUrinary Care: Mendukung kesehatan saluran kemih.\r\nKemasan yang Tersedia:\r\nUkuran mulai dari 400g hingga 10kg, cocok untuk kebutuhan harian dan persediaan dalam jumlah besar.\r\n\r\nCocok untuk:\r\n\r\nSemua jenis kucing berdasarkan kebutuhan spesifiknya.\r\nPemilik yang mengutamakan nutrisi premium bagi kucing peliharaan.\r\n\"Royal Canin, pilihan profesional untuk kesehatan kucing yang optimal.\" 🐾', 250000.00, './imgs/royalcanin.jpg', 'Makanan', 'bungkus'),
(14, 'Sekop Pasir Kucing', 'Sekop Pasir Kucing Praktis adalah alat penting untuk menjaga kebersihan litter box kucing Anda. Dirancang khusus dengan bentuk dan ukuran yang memudahkan Anda menyaring kotoran sekaligus menghemat pasir kucing.\r\n\r\nKeunggulan Produk:\r\n\r\nBahan Tahan Lama: Terbuat dari plastik tebal atau logam berkualitas tinggi, sehingga tidak mudah patah atau bengkok.\r\nDesain Ergonomis: Pegangan nyaman untuk digenggam, memudahkan Anda saat membersihkan litter box.\r\nSaringan Optimal: Lubang saringan dengan ukuran yang pas untuk memisahkan kotoran dari pasir.\r\nMudah Dibersihkan: Permukaan sekop anti lengket, sehingga tidak meninggalkan bau atau residu.\r\nRingan dan Praktis: Cocok digunakan di rumah atau saat bepergian.\r\nPilihan Varian:\r\n\r\nSekop Standar: Ukuran sedang untuk litter box kecil atau medium.\r\nSekop Besar: Untuk litter box besar, cocok untuk pemilik beberapa kucing.\r\nSekop dengan Wadah Penampung: Dilengkapi dengan tempat penampungan untuk mempermudah pembuangan kotoran.\r\nManfaat:\r\n\r\nMembantu menjaga kebersihan litter box dengan cepat dan efisien.\r\nMeminimalkan pemborosan pasir kucing.\r\nMencegah bau tidak sedap di sekitar area litter box.\r\nCocok untuk:\r\n\r\nSemua jenis pasir kucing, termasuk pasir gumpal, kristal, atau biodegradable.\r\n\"Bersihkan litter box dengan mudah dan cepat, karena kebersihan adalah kunci kenyamanan kucing Anda!\" 🐾', 6000.00, './imgs/sekop.jpeg', 'Peralatan', 'buah'),
(15, 'Wadah Pasir', 'Wadah Pasir Kucing Premium adalah perlengkapan esensial untuk menjaga kebersihan dan kenyamanan kucing Anda saat melakukan kebutuhan. Dirancang dengan material berkualitas tinggi dan desain yang fungsional, produk ini cocok untuk segala jenis kucing dan berbagai jenis pasir.\r\n\r\nKeunggulan Produk:\r\n\r\nBahan Berkualitas: Terbuat dari plastik tebal yang tahan lama, tidak mudah retak, dan aman untuk kucing.\r\nDesain Ergonomis:\r\nTepi rendah untuk memudahkan kucing masuk dan keluar.\r\nBeberapa model dilengkapi penutup untuk mengurangi penyebaran bau dan pasir.\r\nAnti Bocor: Permukaan bawah tahan air sehingga menjaga kebersihan lantai rumah Anda.\r\nMudah Dibersihkan: Permukaan halus yang mempermudah proses pencucian.\r\nTersedia dalam Berbagai Ukuran:\r\nUkuran kecil untuk anak kucing atau ruangan terbatas.\r\nUkuran besar untuk kucing dewasa atau multi-kucing.\r\nPilihan Varian:\r\n\r\nWadah Pasir Terbuka: Cocok untuk kucing yang suka ruang terbuka.\r\nWadah Pasir Tertutup: Dilengkapi dengan pintu flap untuk menjaga privasi dan mengurangi bau.\r\nWadah Pasir Otomatis: Dilengkapi dengan fitur penyaringan otomatis untuk kebersihan maksimal.\r\nManfaat:\r\n\r\nMemberikan kenyamanan optimal untuk kucing saat menggunakan litter box.\r\nMenjaga kebersihan lingkungan rumah.\r\nMeminimalkan penyebaran bau dan pasir di sekitar area litter box.\r\n\"Berikan kucing Anda wadah pasir terbaik, karena kenyamanan mereka adalah prioritas utama!\" 🐾', 15000.00, './imgs/wadahpasir.jpeg', 'Peralatan', 'buah'),
(16, 'Kalung Kucing', 'Kalung Kucing Fashionable adalah aksesori lucu sekaligus fungsional yang dirancang untuk mempercantik penampilan kucing Anda. Tersedia dalam berbagai model dan warna menarik, kalung ini juga memberikan identitas tambahan untuk peliharaan kesayangan Anda.\r\n\r\nKeunggulan Produk:\r\nMaterial Berkualitas:\r\n\r\nTerbuat dari bahan lembut seperti nylon, kulit sintetis, atau kain katun yang nyaman dan tidak menyebabkan iritasi pada leher kucing.\r\nDilengkapi dengan pengunci yang aman dan mudah dilepas pasang.\r\nDesain Menarik:\r\n\r\nBerbagai pilihan motif, seperti polos, bergaris, bunga, hingga karakter lucu.\r\nDilengkapi dengan lonceng kecil yang membantu mengetahui posisi kucing Anda.\r\nUkuran Adjustable:\r\n\r\nDapat disesuaikan dengan ukuran leher kucing, cocok untuk semua ras kucing, mulai dari anak kucing hingga dewasa.\r\nFungsi Tambahan:\r\n\r\nBeberapa model dilengkapi dengan slot untuk menambahkan nama atau nomor telepon pemilik.\r\nOpsi reflective (memantulkan cahaya) untuk memastikan kucing tetap terlihat di malam hari.\r\nRingan dan Aman:\r\n\r\nDesain ringan sehingga kucing tetap nyaman bermain.\r\nPengunci quick-release untuk mencegah kucing terjebak jika kalung tersangkut.', 5000.00, './imgs/kalung.jpg', 'Aksesoris', 'buah'),
(17, 'Obat Flu dan Pilek', 'Obat Flu Kucing adalah solusi khusus untuk membantu mengatasi flu pada kucing peliharaan Anda. Dirancang oleh ahli kesehatan hewan, obat ini diformulasikan untuk meredakan gejala flu seperti bersin, hidung tersumbat, dan lemas pada kucing, sekaligus meningkatkan daya tahan tubuh mereka.\r\n\r\nKeunggulan Produk:\r\nFormula Khusus:\r\n\r\nMengandung bahan aktif yang aman untuk kucing, seperti vitamin C, ekstrak herbal, dan imunostimulan.\r\nTidak mengandung bahan berbahaya atau menyebabkan ketergantungan.\r\nMeredakan Gejala Flu:\r\n\r\nMembantu mengurangi bersin, hidung berair, mata berair, dan demam.\r\nEfektif mempercepat pemulihan kucing yang terkena flu.\r\nMeningkatkan Imunitas:\r\n\r\nDiperkaya dengan nutrisi tambahan untuk memperkuat daya tahan tubuh.\r\nMembantu mencegah flu berulang.\r\nMudah Digunakan:\r\n\r\nTersedia dalam bentuk sirup cair yang mudah dicampur dengan makanan atau air minum.\r\nBeberapa varian juga tersedia dalam bentuk tablet kecil yang mudah ditelan.\r\nAman untuk Semua Usia:\r\n\r\nCocok digunakan untuk anak kucing hingga kucing dewasa.\r\nPetunjuk Penggunaan:\r\nDosis Umum:\r\nSesuai petunjuk dokter hewan atau instruksi pada kemasan (berdasarkan berat badan kucing).\r\nCara Pemberian:\r\nCampurkan ke makanan atau berikan langsung dengan spuit/dropper.\r\nPastikan kucing minum cukup air selama masa pengobatan.\r\nCatatan Penting:\r\nSebelum menggunakan, konsultasikan dengan dokter hewan jika kucing memiliki riwayat alergi atau sedang mengonsumsi obat lain.\r\nHanya untuk pengobatan flu ringan. Jika gejala berlanjut atau memburuk dalam 2–3 hari, segera bawa kucing ke dokter hewan.\r\n\"Bantu kucing kesayangan Anda kembali aktif dan sehat dengan perawatan terbaik!\" 🐾', 15000.00, './imgs/flu.jpeg', 'Kesehatan', 'botol'),
(18, 'Obat Batuk', 'Obat Batuk Kucing adalah solusi aman dan efektif untuk membantu mengatasi batuk pada kucing kesayangan Anda. Dirancang dengan formula khusus, obat ini membantu meredakan batuk yang disebabkan oleh infeksi ringan, iritasi tenggorokan, atau alergi, sekaligus meningkatkan kesehatan pernapasan kucing.\r\n\r\nKeunggulan Produk:\r\nFormula Aman dan Efektif:\r\n\r\nMengandung bahan herbal alami seperti ekstrak madu, licorice, dan jahe.\r\nBebas dari bahan kimia keras yang berpotensi berbahaya bagi kucing.\r\nMeredakan Batuk dengan Cepat:\r\n\r\nMembantu mengurangi frekuensi batuk dan menenangkan iritasi tenggorokan.\r\nEfektif untuk batuk basah maupun batuk kering.\r\nMeningkatkan Kesehatan Pernapasan:\r\n\r\nMengandung vitamin dan antioksidan untuk menjaga kesehatan saluran pernapasan.\r\nMudah Digunakan:\r\n\r\nTersedia dalam bentuk sirup cair yang ramah kucing dengan rasa manis alami dari madu.\r\nBeberapa varian juga tersedia dalam bentuk tablet kunyah atau serbuk yang mudah dicampur dengan makanan.\r\nCocok untuk Semua Usia:\r\n\r\nAman digunakan untuk anak kucing, kucing dewasa, maupun kucing senior.\r\nPetunjuk Penggunaan:\r\nDosis Umum:\r\nBerikan sesuai petunjuk dokter hewan atau sesuai instruksi pada kemasan (biasanya berdasarkan berat badan kucing).\r\nCara Pemberian:\r\nBerikan langsung menggunakan spuit/dropper atau campurkan ke makanan basah.\r\nPastikan kucing cukup terhidrasi selama pengobatan.\r\nCatatan Penting:\r\nObat ini dirancang untuk batuk ringan atau sementara.\r\nJika batuk disertai gejala lain seperti demam, lemas, atau sulit bernapas, segera konsultasikan ke dokter hewan.\r\nSimpan di tempat sejuk dan jauh dari jangkauan anak-anak.\r\n\"Bantu kucing Anda bernapas lega dan nyaman dengan perawatan terbaik!\" 🐾', 15000.00, './imgs/batuk.png', 'Kesehatan', 'botol'),
(19, 'Pasir Kucing', 'Pasir Kucing Berkualitas dirancang untuk memenuhi kebutuhan kucing dan pemiliknya dengan memberikan solusi praktis dan higienis untuk pengelolaan kotoran kucing. Tersedia dalam berbagai jenis dan ukuran, pasir kucing ini memberikan kenyamanan ekstra untuk kucing kesayangan Anda.\r\n\r\nKeunggulan Produk:\r\nDaya Serap Tinggi:\r\n\r\nEfektif menyerap cairan dan mengurangi bau tidak sedap.\r\nMenjaga area tetap kering dan nyaman untuk kucing.\r\nPengendalian Bau Optimal:\r\n\r\nMengandung teknologi deodorizer atau bahan alami untuk mengurangi bau.\r\nCocok untuk digunakan di ruang tertutup.\r\nPilihan Bahan:\r\n\r\nPasir Bentonit: Menggumpal dengan kuat sehingga mudah dibersihkan.\r\nPasir Kristal Silika: Super ringan, tahan lama, dan hemat pemakaian.\r\nPasir Organik (Tofu): Ramah lingkungan, terbuat dari bahan alami seperti kedelai.\r\nPasir Zeolit: Ekonomis dan efektif dalam mengurangi bau.\r\nAman untuk Kucing:\r\n\r\nTidak berdebu sehingga aman untuk pernapasan kucing.\r\nBebas dari bahan kimia berbahaya.\r\nMudah Digunakan:\r\n\r\nGumpalan kotoran mudah dibuang, tidak perlu mengganti semua pasir setiap hari.\r\nTersedia dalam berbagai kemasan sesuai kebutuhan Anda (5 kg, 10 kg, atau lebih).\r\nCara Penggunaan:\r\nIsi wadah pasir dengan pasir setinggi 5–7 cm.\r\nBuang gumpalan kotoran setiap hari menggunakan sekop pasir.\r\nTambahkan pasir baru secara berkala untuk menjaga kebersihan.\r\nGanti seluruh pasir setiap 1–2 minggu untuk hasil terbaik.\r\nTips Memilih Pasir Kucing yang Tepat:\r\nPilih jenis pasir sesuai preferensi kucing Anda.\r\nJika kucing sensitif atau memiliki alergi, pilih pasir organik atau rendah debu.\r\nPertimbangkan tingkat kepraktisan dan kebutuhan, misalnya pasir yang menggumpal untuk pembersihan lebih cepat.\r\n\"Berikan kenyamanan maksimal bagi kucing kesayangan Anda dengan pasir berkualitas tinggi yang praktis dan higienis!\" 🐾', 34000.00, './imgs/pasir.jpg', 'Kebersihan', 'pack'),
(25, 'tes', 'tes', 1.00, './imgs/batuk.png', 'Makanan', 'bungkus');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_detail`
--

CREATE TABLE `shipping_detail` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `status_pengiriman` int NOT NULL DEFAULT '1',
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shipping_detail`
--

INSERT INTO `shipping_detail` (`id`, `order_id`, `status_pengiriman`, `update_time`) VALUES
(5, 1871421711, 2, '2025-01-19 14:13:00'),
(6, 1138941000, 2, '2025-01-19 15:28:57');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_status`
--

CREATE TABLE `shipping_status` (
  `id` int NOT NULL,
  `status_pengiriman` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shipping_status`
--

INSERT INTO `shipping_status` (`id`, `status_pengiriman`) VALUES
(1, 'Menunggu pembayaran'),
(2, 'Pembayaran Success, Menunggu Konfirmasi Penjual'),
(3, 'Pesanan telah dikonfirmasi, pesanan sedang dikemas'),
(4, 'Pesanan sedang dikirim'),
(5, 'Pesanan telah diterima pembeli'),
(6, 'Pesanan telah diterima dan telah dikonfirmasi oleh pembeli'),
(7, 'Pesanan Dibatalkan Penjual'),
(8, 'Pesanan dibatalkan pembeli');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `id` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `level` enum('admin','penjual','pembeli') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`id`, `nama`, `email`, `password`, `level`) VALUES
(1, 'Tri Ambar Ningtias', 'ambar@gmail.com', '008599efdcb7822df1c91508d4ab3ee9622b6d5d', 'pembeli'),
(2, 'Rangga Ayi Pratama', 'rangga@gmail.com', '76e8cc8d7c24f7811041ccb0a1e04e4290bda083', 'admin'),
(3, 'Aditiya Alif As Siddiq', 'adit@gmail.com', 'a368402126ad9e4704fbb1ceac9367ad4e2ccf5f', 'penjual'),
(4, 'M. Andriano Alfarazi', 'aji@gmail.com', '7c33489720fccf682f22f2efb2cefc7aee7de177', 'pembeli');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_id` int NOT NULL,
  `transaction_status` enum('pending','success','expire','cancel') DEFAULT 'pending',
  `gross_amount` decimal(15,2) DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `item_details` json DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `order_id`, `transaction_status`, `gross_amount`, `payment_time`, `update_time`, `item_details`, `snap_token`) VALUES
(81, 1, 1871421711, 'success', 150070.00, '2025-01-19 14:12:26', '2025-01-19 14:13:00', '[{\"id\": \"5\", \"name\": \"Whiskas - Makanan Kucing\", \"price\": \"28000.00\", \"quantity\": \"3\"}, {\"id\": \"2\", \"name\": \"Lezato - Makanan Kucing\", \"price\": \"22000.00\", \"quantity\": \"3\"}, {\"id\": \"shipping\", \"name\": \"Ongkos Kirim\", \"price\": 70, \"quantity\": 1}]', '0c2021e8-043a-462a-95d1-abd0ec0522b3'),
(82, 4, 1138941000, 'success', 1250000.00, '2025-01-19 15:28:28', '2025-01-19 15:28:57', '[{\"id\": 13, \"name\": \"Royal Canin 2KG - Makanan Kucing\", \"price\": \"250000.00\", \"quantity\": 5}, {\"id\": \"shipping\", \"name\": \"Ongkos Kirim\", \"price\": 0, \"quantity\": 1}]', '7eef1e00-575c-4b99-b8ae-02ec719477a6');

--
-- Triggers `transactions`
--
DELIMITER $$
CREATE TRIGGER `after_transaction_insert` AFTER INSERT ON `transactions` FOR EACH ROW BEGIN
  INSERT INTO shipping_detail (order_id, status_pengiriman)
  VALUES (NEW.order_id, 1);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_detail`
--

CREATE TABLE `user_detail` (
  `id` int NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_telepon` varchar(15) DEFAULT NULL,
  `alamat_detail` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_detail`
--

INSERT INTO `user_detail` (`id`, `foto`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `no_telepon`, `alamat_detail`) VALUES
(1, 'imgs/user/default.png', 'Perempuan', '2005-06-19', 'Perumahan Pusri Sako, Sako, Palembang, South Sumatra, Sumatra, 30163, Indonesia', '081367618388', 'Samping Pusri'),
(2, 'imgs/user/rangga_gmail_com.jpg', 'Laki-laki', '1990-01-01', 'Jalan Lunjuk Jaya, Ilir Barat I, Palembang, South Sumatra, Sumatra, 30139, Indonesia', '08123456789', 'Kos Pakde Yono'),
(3, 'imgs/user/adit_gmail_com.png', 'Laki-laki', '1992-02-02', 'Gandus, Palembang, South Sumatra, Sumatra, 30149, Indonesia', '08123456780', 'Dekat sungai'),
(4, 'imgs/user/aji_gmail_com.jpeg', 'Laki-laki', '1993-03-03', 'Jalan Lintas Curup - Muara Aman, Kutai Donok, Lebong, Bengkulu, Sumatra, Indonesia', '08123456782', 'Samping polsek');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `detail_address`
--
ALTER TABLE `detail_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_detail`
--
ALTER TABLE `shipping_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `status_pengiriman` (`status_pengiriman`);

--
-- Indexes for table `shipping_status`
--
ALTER TABLE `shipping_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `user_detail`
--
ALTER TABLE `user_detail`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `no_telepon` (`no_telepon`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detail_address`
--
ALTER TABLE `detail_address`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `shipping_detail`
--
ALTER TABLE `shipping_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shipping_status`
--
ALTER TABLE `shipping_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_address`
--
ALTER TABLE `detail_address`
  ADD CONSTRAINT `detail_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shipping_detail`
--
ALTER TABLE `shipping_detail`
  ADD CONSTRAINT `shipping_detail_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `transactions` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shipping_detail_ibfk_2` FOREIGN KEY (`status_pengiriman`) REFERENCES `shipping_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_detail`
--
ALTER TABLE `user_detail`
  ADD CONSTRAINT `user_detail_ibfk_1` FOREIGN KEY (`id`) REFERENCES `tbluser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `update_shipping_status` ON SCHEDULE EVERY 1 HOUR STARTS '2025-01-16 10:40:29' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  UPDATE shipping_detail
  SET status_pengiriman = 6
  WHERE status_pengiriman = 5
    AND update_time <= NOW() - INTERVAL 1 DAY;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `update_shipping_status_2_to_3` ON SCHEDULE EVERY 1 HOUR STARTS '2025-01-17 00:20:23' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  UPDATE shipping_detail
  SET status_pengiriman = 3, update_time = CURRENT_TIMESTAMP
  WHERE status_pengiriman = 2
    AND TIMESTAMPDIFF(HOUR, update_time, CURRENT_TIMESTAMP) >= 24;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
