<?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $name = htmlspecialchars($_POST['name']);
      $message = htmlspecialchars($_POST['message']);
      
      $text = "Halo nama saya " . $name . ", ingin menyampaikan suatu pesan yaitu: " . $message;

      $encoded_text = urlencode($text);

      header("Location: https://api.whatsapp.com/send?phone=6283192655757&text=$encoded_text"); 
      exit;
  }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php include('metadata.php'); ?>
  <title>Alzi Petshop - Pilihan Kebutuhan & Pakan Kucing Terbaik di Palembang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
      scroll-padding-top: 75px;
    }

    body {
      font-family: 'Rubik', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      line-height: 1.6;
      color: #1e293b;
      background-color: #f8fafc;
    }

    /* Navbar Modern Adaptive */
    .navbar-landing {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1030;
      padding: 16px 0;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }

    .navbar-landing.scrolled {
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border-bottom: 1px solid #e2e8f0;
      padding: 10px 0;
    }

    .navbar-landing .brand-name {
      font-size: 1.35rem;
      font-weight: 700;
      color: #ffffff;
      transition: color 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .navbar-landing.scrolled .brand-name {
      color: #0f172a;
    }

    .navbar-landing .nav-link {
      color: rgba(255, 255, 255, 0.88);
      font-weight: 500;
      padding: 0.5rem 1rem !important;
      transition: color 0.2s ease;
      border-radius: 6px;
    }

    .navbar-landing .nav-link:hover {
      color: #ffc107;
    }

    .navbar-landing.scrolled .nav-link {
      color: #475569;
    }

    .navbar-landing.scrolled .nav-link:hover {
      color: #d97706;
    }

    .navbar-landing .btn-nav-login {
      transition: all 0.2s ease;
    }

    .navbar-landing .btn-nav-login:hover {
      transform: translateY(-1px);
    }

    /* Hero Header */
    .hero-header {
      position: relative;
      min-height: 100vh;
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.58) 50%, rgba(15, 23, 42, 0.92) 100%),
                  url('imgs/landing.jpg') center/cover no-repeat;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 120px 20px 70px;
      text-align: center;
    }

    .hero-title {
      font-size: 2.1rem;
      font-weight: 700;
      letter-spacing: -0.5px;
      line-height: 1.25;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }

    @media (min-width: 768px) {
      .hero-title {
        font-size: 3.25rem;
      }
    }

    .hero-lead {
      font-size: 1.05rem;
      color: #e2e8f0;
      max-width: 680px;
      margin: 0 auto 2rem;
      line-height: 1.65;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
    }

    @media (min-width: 768px) {
      .hero-lead {
        font-size: 1.2rem;
      }
    }

    .trust-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 14px;
      padding: 14px 18px;
      text-align: left;
      transition: transform 0.2s ease, background 0.2s ease;
    }

    .trust-card:hover {
      transform: translateY(-3px);
      background: rgba(255, 255, 255, 0.16);
    }

    /* Section Styles */
    .section {
      padding: 80px 20px;
    }

    .section-about {
      background: #ffffff;
    }

    .section-product {
      background: #f1f5f9;
    }

    .section-contact {
      background: #ffffff;
    }

    .feature-card {
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .product-preview-card {
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      background: #ffffff;
      border: 1px solid #e2e8f0;
    }

    .product-preview-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.09) !important;
    }

    .product-preview-img {
      height: 220px;
      object-fit: contain;
      background-color: #f8fafc;
      padding: 16px;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }

    .contact-card {
      border: 1px solid #e2e8f0;
      transition: transform 0.2s ease;
    }

    .contact-card:hover {
      transform: translateY(-3px);
    }

    footer {
      background: #0f172a;
      color: #94a3b8;
      text-align: center;
      padding: 24px 0;
    }

    .reveal {
      opacity: 0;
      transform: translateY(35px);
      transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body>
  <!-- Navbar Start -->
  <nav class="navbar navbar-expand-lg navbar-landing fixed-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand brand-name" href="#home">
        <i class="bi bi-shop text-warning"></i>
        <span>Alzi Petshop</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item">
            <a class="nav-link" href="#home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">Tentang</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#product">Produk</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Kontak</a>
          </li>
          <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
            <a class="btn btn-warning btn-nav-login rounded-pill px-4 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm text-dark" href="login/login_form.php">
              <i class="bi bi-box-arrow-in-right"></i> Masuk / Daftar
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Navbar End -->

  <!-- Hero Header Start -->
  <header class="hero-header" id="home">
    <div class="container hero-content">
      <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-patch-check-fill"></i> Solusi Kebutuhan Kucing No. 1 di Palembang
      </span>
      <h1 class="hero-title mb-3">
        Penuhi Kebutuhan Kucing Kesayangan Anda
      </h1>
      <p class="hero-lead">
        Pakan bergizi, vitamin, aksesoris, dan perlengkapan higienis lengkap dengan harga terjangkau serta pengiriman cepat langsung ke rumah Anda.
      </p>

      <!-- CTA Buttons -->
      <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
        <a href="#product" class="btn btn-warning btn-lg px-4 py-2.5 fw-semibold rounded-pill shadow d-inline-flex align-items-center gap-2 text-dark">
          <i class="bi bi-bag-check-fill"></i> Mulai Belanja Sekarang
        </a>
        <a href="https://api.whatsapp.com/send?phone=6283192655757&text=Halo%20Alzi%20Petshop,%20saya%20ingin%20tanya%20kebutuhan%20kucing" target="_blank" class="btn btn-outline-light btn-lg px-4 py-2.5 fw-semibold rounded-pill d-inline-flex align-items-center gap-2">
          <i class="bi bi-whatsapp text-success"></i> Konsultasi WhatsApp
        </a>
      </div>

      <!-- Trust Badges -->
      <div class="row g-3 justify-content-center text-start" style="max-width: 860px; margin: 0 auto;">
        <div class="col-md-4 col-sm-6">
          <div class="trust-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-truck"></i>
            </div>
            <div>
              <div class="fw-bold text-white small">Pengiriman Cepat</div>
              <div class="text-light text-opacity-75" style="font-size: 0.78rem;">Kurir lokal & ongkir hemat</div>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6">
          <div class="trust-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-shield-check"></i>
            </div>
            <div>
              <div class="fw-bold text-white small">100% Produk Asli</div>
              <div class="text-light text-opacity-75" style="font-size: 0.78rem;">Pakan & vitamin teruji aman</div>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6">
          <div class="trust-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-heart-pulse"></i>
            </div>
            <div>
              <div class="fw-bold text-white small">Konsultasi Ramah</div>
              <div class="text-light text-opacity-75" style="font-size: 0.78rem;">Siap bantu perawatan kucing</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
  <!-- Hero Header End -->

  <!-- Tentang Kami Start -->
  <section id="about" class="section section-about">
    <div class="container reveal">
      <div class="text-center mb-5">
        <span class="badge bg-warning bg-opacity-25 text-warning-emphasis px-3 py-2 rounded-pill fw-semibold mb-2">
          Tentang Alzi Petshop
        </span>
        <h2 class="fw-bold mb-3">Dedikasi Untuk Kucing Kesayangan Anda</h2>
        <p class="text-muted mx-auto" style="max-width: 760px; font-size: 1.05rem;">
          Alzi Petshop telah beroperasi selama lebih dari 4 tahun di Palembang, berdedikasi menyediakan produk berkualitas tinggi mulai dari nutrisi pakan harian, vitamin kesehatan, perlengkapan kebersihan, hingga aksesoris fungsional untuk anabul Anda.
        </p>
      </div>

      <div class="row g-4 justify-content-center">
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm p-4 rounded-4 text-center feature-card">
            <div class="rounded-circle bg-warning-subtle text-warning-emphasis mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.6rem;">
              <i class="bi bi-award-fill"></i>
            </div>
            <h5 class="fw-bold mb-2">4+ Tahun Berdedikasi</h5>
            <p class="text-muted small mb-0">Melayani ratusan pemilik kucing di Palembang dengan pemahaman mendalam tentang kebutuhan dan nutrisi spesifik anabul.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm p-4 rounded-4 text-center feature-card">
            <div class="rounded-circle bg-warning-subtle text-warning-emphasis mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.6rem;">
              <i class="bi bi-box-seam-fill"></i>
            </div>
            <h5 class="fw-bold mb-2">Pilihan Produk Lengkap</h5>
            <p class="text-muted small mb-0">Tersedia pakan kering/basah berkualitas, vitamin imun, obat cacing, kandang kokoh, serta pasir higienis bebas debu.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm p-4 rounded-4 text-center feature-card">
            <div class="rounded-circle bg-warning-subtle text-warning-emphasis mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.6rem;">
              <i class="bi bi-chat-heart-fill"></i>
            </div>
            <h5 class="fw-bold mb-2">Layanan Konsultasi Cepat</h5>
            <p class="text-muted small mb-0">Bingung memilih makanan yang cocok? Tim Alzi Petshop siap membantu memberikan rekomendasi terbaik secara ramah dan solutif.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Tentang Kami End -->

  <!-- Produk Kami Start -->
  <section id="product" class="section section-product">
    <div class="container reveal">
      <div class="text-center mb-5">
        <span class="badge bg-warning bg-opacity-25 text-warning-emphasis px-3 py-2 rounded-pill fw-semibold mb-2">
          Koleksi Pilihan
        </span>
        <h2 class="fw-bold mb-3">Produk Populer di Toko Kami</h2>
        <p class="text-muted mx-auto" style="max-width: 650px;">
          Berbagai pilihan makanan kucing, kandang, dan perlengkapan anabul siap antar dengan kualitas terjamin.
        </p>
      </div>

      <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-4 col-sm-6">
          <div class="card h-100 product-preview-card rounded-4 overflow-hidden">
            <img src="imgs/lezato.jpeg" alt="Lezato Makanan Kucing" class="product-preview-img">
            <div class="card-body p-4 text-center">
              <span class="badge bg-info-subtle text-info-emphasis mb-2">Makanan Kucing</span>
              <h5 class="fw-bold mb-1">Lezato Adult Cat Food</h5>
              <p class="text-muted small mb-2">Rasa tuna lezat untuk nafsu makan maksimal</p>
              <div class="fw-bold text-warning fs-5">Mulai Rp22.000</div>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-sm-6">
          <div class="card h-100 product-preview-card rounded-4 overflow-hidden">
            <img src="imgs/kandang.jpg" alt="Kandang Kucing Besi" class="product-preview-img">
            <div class="card-body p-4 text-center">
              <span class="badge bg-success-subtle text-success-emphasis mb-2">Peralatan</span>
              <h5 class="fw-bold mb-1">Kandang Kucing Besi Lipat</h5>
              <p class="text-muted small mb-2">Kuat, aman, dan mudah dibersihkan</p>
              <div class="fw-bold text-warning fs-5">Harga Terjangkau</div>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-sm-6">
          <div class="card h-100 product-preview-card rounded-4 overflow-hidden">
            <img src="imgs/kalung.jpg" alt="Kalung Kucing Lonceng" class="product-preview-img">
            <div class="card-body p-4 text-center">
              <span class="badge bg-warning-subtle text-warning-emphasis mb-2">Aksesoris</span>
              <h5 class="fw-bold mb-1">Kalung Kucing Berlonceng</h5>
              <p class="text-muted small mb-2">Bahan nyaman dan aman untuk leher anabul</p>
              <div class="fw-bold text-warning fs-5">Mulai Rp15.000</div>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center">
        <a href="login/login_form.php" class="btn btn-warning btn-lg px-5 py-2.5 fw-semibold rounded-pill shadow-sm text-dark d-inline-flex align-items-center gap-2">
          <i class="bi bi-cart3"></i> Lihat Katalog Lengkap & Belanja
        </a>
      </div>
    </div>
  </section>
  <!-- Produk Kami End -->

  <!-- Hubungi Kami Start -->
  <section id="contact" class="section section-contact">
    <div class="container reveal">
      <div class="text-center mb-5">
        <span class="badge bg-warning bg-opacity-25 text-warning-emphasis px-3 py-2 rounded-pill fw-semibold mb-2">
          Layanan & Bantuan
        </span>
        <h2 class="fw-bold mb-3">Hubungi Alzi Petshop</h2>
        <p class="text-muted mx-auto" style="max-width: 650px;">
          Punya pertanyaan seputar ketersediaan stok atau lokasi toko? Jangan ragu menghubungi kami.
        </p>
      </div>

      <div class="row g-4 mb-5">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm rounded-4 p-4 text-center contact-card">
            <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <h5 class="fw-bold mb-2">Alamat Toko</h5>
            <p class="text-muted small mb-0">Perum. Patra Sriwijaya FD No.11, Kota Palembang</p>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card h-100 shadow-sm rounded-4 p-4 text-center contact-card">
            <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <h5 class="fw-bold mb-2">Email</h5>
            <p class="text-muted small mb-0">contact@alzipetshop.com</p>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card h-100 shadow-sm rounded-4 p-4 text-center contact-card">
            <div class="rounded-circle bg-warning-subtle text-warning-emphasis mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
              <i class="bi bi-whatsapp"></i>
            </div>
            <h5 class="fw-bold mb-2">WhatsApp / Telepon</h5>
            <p class="text-muted small mb-0">0831 6107 6087 / 0831 9265 5757</p>
          </div>
        </div>
      </div>

      <div class="contact-form mx-auto p-4 p-md-5 rounded-4 shadow-sm border bg-light" style="max-width: 650px;">
        <h4 class="text-center fw-bold mb-3">Kirim Pesan Langsung</h4>
        <p class="text-center text-muted small mb-4">Pesan Anda akan langsung diteruskan ke WhatsApp resmi toko kami.</p>
        <form action="landing_page.php" method="POST">
          <div class="mb-3">
            <label for="name" class="form-label small fw-semibold text-secondary">Nama Anda</label>
            <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="Contoh: Budi Santoso" required>
          </div>
          <div class="mb-4">
            <label for="message" class="form-label small fw-semibold text-secondary">Pesan / Kebutuhan</label>
            <textarea class="form-control rounded-3" id="message" name="message" rows="4" placeholder="Tuliskan pesan atau produk yang ingin Anda tanyakan..." required></textarea>
          </div>
          <button type="submit" class="btn btn-warning w-100 py-2.5 fw-semibold rounded-pill text-dark shadow-sm d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-whatsapp"></i> Kirim Pesan ke WhatsApp
          </button>
        </form>
      </div>
    </div>
  </section>
  <!-- Hubungi Kami End -->

  <!-- Footer Start -->
  <footer class="py-4">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 text-muted small">
        <div>
          <strong class="text-white">Alzi Petshop</strong> — Mitra Terbaik Kucing Anda di Palembang
        </div>
        <div>
          &copy; <?= date('Y') ?> Alzi Petshop. All rights reserved.
        </div>
      </div>
    </div>
  </footer>
  <!-- Footer End -->

  <!-- Scripts -->
  <script>
    // Navbar adaptive scroll listener
    const mainNav = document.getElementById('mainNav');
    function updateNavOnScroll() {
      if (window.scrollY > 40) {
        mainNav.classList.add('scrolled');
      } else {
        mainNav.classList.remove('scrolled');
      }
    }
    window.addEventListener('scroll', updateNavOnScroll);
    updateNavOnScroll();

    // Reveal animations on scroll
    document.addEventListener("scroll", () => {
      const reveals = document.querySelectorAll(".reveal");
      const windowHeight = window.innerHeight;

      reveals.forEach((el) => {
        const revealTop = el.getBoundingClientRect().top;
        const revealBottom = el.getBoundingClientRect().bottom;

        if (revealTop < windowHeight - 80 && revealBottom > 80) {
          el.classList.add("active");
        }
      });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
  </script>
</body>
</html>
