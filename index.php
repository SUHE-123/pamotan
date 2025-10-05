<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Pamotan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary: #1b5e20;
      --primary-light: #4caf50;
      --primary-lighter: #81c784;
      --primary-lightest: #e8f5e9;
      --accent: #ff9800;
      --text-dark: #2e7d32;
      --text-light: #ffffff;
      --gradient-primary: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%);
      --gradient-hero: linear-gradient(rgba(27, 94, 32, 0.85), rgba(76, 175, 80, 0.8));
      --shadow: 0 8px 30px rgba(0,0,0,0.12);
      --shadow-hover: 0 15px 40px rgba(0,0,0,0.15);
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f4f9f4 0%, #e8f5e9 100%);
      color: var(--text-dark);
      line-height: 1.7;
      overflow-x: hidden;
    }

    .hero {
      background: var(--gradient-hero), url('assets/img/desa-bg.jpg') center/cover no-repeat;
      color: var(--text-light);
      text-align: center;
      padding: 100px 20px;
      border-radius: 0 0 40px 40px;
      position: relative;
      overflow: hidden;
      min-height: 80vh;
      display: flex;
      align-items: center;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="1000,100 1000,0 0,100"/></svg>');
      background-size: cover;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      width: 100%;
    }

    .hero h1 {
      font-weight: 700;
      font-size: clamp(1.8rem, 5vw, 3.2rem);
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      line-height: 1.2;
    }

    .hero p {
      font-size: clamp(1rem, 3vw, 1.3rem);
      margin-bottom: 2rem;
      opacity: 0.95;
      font-weight: 400;
      line-height: 1.5;
    }

    .hero .btn {
      margin: 8px 6px;
      border-radius: 50px;
      padding: 14px 28px;
      font-weight: 600;
      font-size: clamp(0.9rem, 2vw, 1rem);
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      display: inline-block;
      min-width: 180px;
    }

    .hero .btn-light {
      background: rgba(255,255,255,0.95);
      color: var(--primary);
    }

    .hero .btn-success {
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255,255,255,0.3);
      color: white;
    }

    .logo-container {
      margin-bottom: 1.5rem;
    }

    .logo-container img {
      width: clamp(80px, 20vw, 120px);
      height: clamp(80px, 20vw, 120px);
      border-radius: 50%;
      border: 4px solid rgba(255,255,255,0.3);
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }

    .section-title {
      text-align: center;
      margin: 60px 0 40px;
      font-weight: 700;
      color: var(--primary);
      position: relative;
      font-size: clamp(1.5rem, 4vw, 2rem);
    }

    .section-title::after {
      content: '';
      display: block;
      width: 60px;
      height: 4px;
      background: var(--gradient-primary);
      margin: 15px auto;
      border-radius: 2px;
    }

    .card {
      border: none;
      border-radius: 20px;
      overflow: hidden;
      background: white;
      margin-bottom: 1.5rem;
      box-shadow: var(--shadow);
    }

    .card-body {
      padding: 1.5rem;
      position: relative;
    }

    .card-body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--gradient-primary);
    }

    .card h4 {
      font-weight: 700;
      margin-bottom: 1.2rem;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: clamp(1.1rem, 3vw, 1.3rem);
    }

    .card h4 i {
      font-size: 1.2rem;
    }

    .stat-list {
      list-style: none;
      padding: 0;
      margin-bottom: 1.5rem;
    }

    .stat-list li {
      padding: 10px 0;
      border-bottom: 1px solid #e8f5e9;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 500;
      font-size: clamp(0.85rem, 2.5vw, 0.95rem);
      flex-wrap: wrap;
    }

    .stat-list li:last-child {
      border-bottom: none;
    }

    .stat-value {
      background: var(--primary-lightest);
      color: var(--primary);
      padding: 5px 10px;
      border-radius: 15px;
      font-weight: 600;
      font-size: clamp(0.8rem, 2vw, 0.9rem);
      margin-left: 8px;
      white-space: nowrap;
    }

    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }

    .feature-card {
      background: white;
      padding: 1.5rem;
      border-radius: 20px;
      text-align: center;
      box-shadow: var(--shadow);
      border: 1px solid #e8f5e9;
      height: 100%;
    }

    .feature-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 1.2rem;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: white;
    }

    .feature-card h5 {
      font-size: clamp(1rem, 2.5vw, 1.2rem);
      margin-bottom: 0.8rem;
      font-weight: 600;
    }

    .feature-card p {
      font-size: clamp(0.85rem, 2vw, 0.95rem);
      line-height: 1.5;
      margin: 0;
    }

    footer {
      background: var(--gradient-primary);
      color: var(--text-light);
      text-align: center;
      padding: 30px 20px;
      margin-top: 60px;
      border-radius: 40px 40px 0 0;
      position: relative;
    }

    footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--accent);
    }

    .footer-content {
      max-width: 600px;
      margin: 0 auto;
    }

    .footer-content p {
      margin-bottom: 0.5rem;
      font-weight: 600;
      font-size: clamp(0.9rem, 2.5vw, 1rem);
    }

    .footer-content small {
      opacity: 0.9;
      font-size: clamp(0.8rem, 2vw, 0.9rem);
    }

    .chart-container {
      position: relative;
      height: 180px;
      margin-top: 1.2rem;
    }

    /* Mobile-specific improvements */
    .mobile-optimized-text {
      font-size: clamp(0.9rem, 2.5vw, 1rem);
      line-height: 1.6;
    }

    .mobile-tap-target {
      min-height: 44px;
      min-width: 44px;
    }

    /* Button container for better mobile layout */
    .btn-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-top: 1.5rem;
    }

    /* Extra small devices */
    @media (max-width: 576px) {
      .hero {
        padding: 80px 15px;
        border-radius: 0 0 30px 30px;
        min-height: 70vh;
      }

      .hero .btn {
        padding: 12px 24px;
        min-width: 160px;
        margin: 6px 4px;
      }

      .btn-container {
        flex-direction: column;
        align-items: center;
      }

      .btn-container .btn {
        width: 100%;
        max-width: 250px;
      }

      .card-body {
        padding: 1.2rem;
      }

      .feature-card {
        padding: 1.2rem;
      }

      .feature-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
      }

      .section-title {
        margin: 50px 0 30px;
      }

      footer {
        padding: 25px 15px;
        margin-top: 50px;
        border-radius: 30px 30px 0 0;
      }

      .stat-list li {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
      }

      .stat-value {
        margin-left: 0;
        align-self: flex-start;
      }
    }

    /* Very small devices */
    @media (max-width: 380px) {
      .hero {
        padding: 60px 12px;
      }

      .hero h1 {
        font-size: 1.6rem;
      }

      .hero p {
        font-size: 0.95rem;
      }

      .logo-container img {
        width: 70px;
        height: 70px;
      }

      .card-body {
        padding: 1rem;
      }

      .feature-grid {
        gap: 1rem;
      }

      .feature-card {
        padding: 1rem;
      }

      .chart-container {
        height: 160px;
      }
    }

    /* Landscape orientation for mobile */
    @media (max-height: 500px) and (orientation: landscape) {
      .hero {
        min-height: auto;
        padding: 60px 20px;
      }

      .logo-container {
        margin-bottom: 1rem;
      }

      .logo-container img {
        width: 60px;
        height: 60px;
      }

      .hero h1 {
        margin-bottom: 0.5rem;
      }

      .hero p {
        margin-bottom: 1rem;
      }
    }

    /* Prevent horizontal scroll */
    .container {
      max-width: 100%;
      padding-left: 15px;
      padding-right: 15px;
    }
  </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-content">
    <div class="logo-container">
      <img src="img/image.png" alt="Logo Desa Pamotan">
    </div>
    <h1>Sistem Digitalisasi Desa Pamotan</h1>
    <p>Mewujudkan Pertanian Cerdas Berbasis IoT — Irigasi & Pendeteksi Hama Otomatis</p>
    <div class="btn-container">
      <a href="irigasi/login.php" class="btn btn-light mobile-tap-target">
        <i class="fas fa-tint me-2"></i>Sistem Irigasi
      </a>
      <a href="hama/auth/login.php" class="btn btn-success mobile-tap-target">
        <i class="fas fa-bug me-2"></i>Pendeteksi Hama
      </a>
    </div>
  </div>
</section>

<div class="container">

  <!-- Profil Desa -->
  <h2 class="section-title">Profil Desa Pamotan</h2>
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="mobile-optimized-text"><strong>Desa Pamotan</strong> merupakan pelopor desa digital di wilayahnya yang menerapkan sistem pertanian modern berbasis Internet of Things (IoT). 
      Sistem ini mengintegrasikan teknologi sensor untuk pemantauan kondisi tanah, iklim, dan potensi serangan hama secara otomatis dan real-time.</p>
      <p class="mobile-optimized-text">Dengan dukungan dari pemerintah desa dan kelompok tani, Desa Pamotan berkomitmen untuk mewujudkan konsep <em>Smart Village</em> dan <em>Smart Farming</em> 
      demi meningkatkan produktivitas serta keberlanjutan lingkungan.</p>
    </div>
  </div>

  <!-- Fitur Unggulan -->
  <h2 class="section-title">Fitur Unggulan Sistem</h2>
  <div class="feature-grid">
    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-cloud-sun"></i>
      </div>
      <h5>Monitoring Real-time</h5>
      <p>Pemantauan kondisi lingkungan dan tanaman secara real-time dengan update data setiap menit</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-robot"></i>
      </div>
      <h5>Otomatisasi Cerdas</h5>
      <p>Sistem irigasi dan deteksi hama bekerja otomatis berdasarkan kondisi lingkungan aktual</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-chart-line"></i>
      </div>
      <h5>Analitik Data</h5>
      <p>Visualisasi data yang mudah dipahami untuk pengambilan keputusan yang lebih baik</p>
    </div>
  </div>

  <!-- Tentang Sistem -->
  <h2 class="section-title">Tentang Sistem Digitalisasi Desa</h2>
  <div class="card shadow-sm">
    <div class="card-body">
      <p class="mobile-optimized-text">Sistem Digitalisasi Desa Pamotan merupakan inovasi untuk menghadirkan solusi pertanian cerdas dengan memanfaatkan teknologi IoT.
      Dua modul utama — Irigasi dan Pendeteksi Hama — saling terhubung untuk memantau, menganalisis, dan memberikan rekomendasi tindakan secara otomatis.</p>
      <p class="mobile-optimized-text">Platform ini dirancang dengan prinsip <strong>transparansi data, efisiensi sumber daya,</strong> dan <strong>kemudahan akses</strong> bagi petani maupun pemerintah desa.
      Dengan integrasi data real-time, sistem ini menjadi langkah nyata menuju desa tangguh dan mandiri teknologi.</p>
    </div>
  </div>
</div>

<!-- Footer -->
<footer>
  <div class="footer-content">
    <p class="mb-2">© 2025 Desa Pamotan — Sistem Digitalisasi Desa Berbasis IoT</p>
    <small>Dikembangkan oleh Tim Pengembang IoT PT AKTARA</small>
  </div>
</footer>

<script>
// Prevent zoom on double tap (iOS)
let lastTouchEnd = 0;
document.addEventListener('touchend', function (event) {
  const now = (new Date()).getTime();
  if (now - lastTouchEnd <= 300) {
    event.preventDefault();
  }
  lastTouchEnd = now;
}, false);
</script>
</body>
</html>