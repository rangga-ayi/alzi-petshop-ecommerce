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
<!-- Metadata end -->
