<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
$current_url = $protocol . $host . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
?>
<!-- Metadata start -->
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta property="og:title" content="Alzi Petshop" />
<meta property="og:description" content="Toko makanan dan perlengkapan kucing terpercaya" />
<meta property="og:image" content="https://iili.io/2hymaZG.md.jpg" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="1200" />
<meta property="og:url" content="<?php echo htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>" />
<link rel="icon" href="https://iili.io/2hymaZG.md.jpg" type="image/x-icon" />
<!-- Google Fonts Rubik (Weights 400, 500, 600, 700) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Metadata end -->
