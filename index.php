<?php
session_start();

// === Load config DB ===
$cfg_ir = __DIR__ . '/config/db_irigasi.php';
$cfg_hm = __DIR__ . '/config/db_hama.php';

require_once $cfg_ir;
require_once $cfg_hm;

if (!isset($db_irigasi) || !($db_irigasi instanceof mysqli)) die("Koneksi DB irigasi gagal.");
if (!isset($db_hama) || !($db_hama instanceof mysqli)) die("Koneksi DB hama gagal.");

// ===== Statistik Irigasi =====
$stat_irigasi = ['soil'=>'-','ph'=>'-','pompa'=>'-','jadwal'=>0];
$q = $db_irigasi->query("SELECT soil_moisture, ph, pompa_status FROM sensor_data ORDER BY waktu DESC LIMIT 1");
if ($q && $row=$q->fetch_assoc()) {
  $stat_irigasi['soil']=$row['soil_moisture']; $stat_irigasi['ph']=$row['ph']; $stat_irigasi['pompa']=$row['pompa_status'];
}
$q = $db_irigasi->query("SELECT COUNT(*) AS jml FROM penyiraman_air");
if ($q && $row=$q->fetch_assoc()) $stat_irigasi['jadwal']=$row['jml'];

// ===== Statistik Hama =====
$stat_hama = ['suhu'=>'-','hum'=>'-','hujan'=>'-','angin'=>'-','aturan'=>0];
$q=$db_hama->query("SELECT suhu, kelembaban FROM dht_data ORDER BY waktu DESC LIMIT 1");
if ($q && $row=$q->fetch_assoc()) {$stat_hama['suhu']=$row['suhu']; $stat_hama['hum']=$row['kelembaban'];}
$q=$db_hama->query("SELECT nilai FROM curah_hujan ORDER BY waktu DESC LIMIT 1");
if ($q && $row=$q->fetch_assoc()) $stat_hama['hujan']=$row['nilai'];
$q=$db_hama->query("SELECT kecepatan FROM anemometer ORDER BY waktu DESC LIMIT 1");
if ($q && $row=$q->fetch_assoc()) $stat_hama['angin']=$row['kecepatan'];
$q=$db_hama->query("SELECT COUNT(*) AS jml FROM aturan_hama");
if ($q && $row=$q->fetch_assoc()) $stat_hama['aturan']=$row['jml'];

// ===== Data untuk grafik (10 terakhir) =====
$chart_irigasi = ['labels'=>[], 'soil'=>[], 'ph'=>[]];
$q = $db_irigasi->query("SELECT waktu, soil_moisture, ph FROM sensor_data ORDER BY waktu DESC LIMIT 10");
$data = [];
while($row=$q->fetch_assoc()) $data[]=$row;
$data = array_reverse($data);
foreach($data as $d){
  $chart_irigasi['labels'][]=$d['waktu'];
  $chart_irigasi['soil'][]=$d['soil_moisture'];
  $chart_irigasi['ph'][]=$d['ph'];
}

$chart_hama = ['labels'=>[], 'suhu'=>[], 'hum'=>[], 'hujan'=>[], 'angin'=>[]];
$q = $db_hama->query("SELECT waktu, suhu, kelembaban FROM dht_data ORDER BY waktu DESC LIMIT 10");
$data=[]; while($row=$q->fetch_assoc()) $data[]=$row; $data=array_reverse($data);
foreach($data as $d){
  $chart_hama['labels'][]=$d['waktu'];
  $chart_hama['suhu'][]=$d['suhu'];
  $chart_hama['hum'][]=$d['kelembaban'];
}
$q = $db_hama->query("SELECT waktu, nilai FROM curah_hujan ORDER BY waktu DESC LIMIT 10");
$data=[]; while($row=$q->fetch_assoc()) $data[]=$row; $data=array_reverse($data);
foreach($data as $d){ $chart_hama['hujan'][]=$d['nilai']; }
$q = $db_hama->query("SELECT waktu, kecepatan FROM anemometer ORDER BY waktu DESC LIMIT 10");
$data=[]; while($row=$q->fetch_assoc()) $data[]=$row; $data=array_reverse($data);
foreach($data as $d){ $chart_hama['angin'][]=$d['kecepatan']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard IoT Gabungan - Sistem Monitoring Pertanian Cerdas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-green: #2e7d32;
      --light-green: #4caf50;
      --dark-green: #1b5e20;
      --earth-brown: #795548;
      --sky-blue: #29b6f6;
      --sun-yellow: #ffc107;
      --water-blue: #0288d1;
      --danger-red: #f44336;
    }
    
    body {
      background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }
    
    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="none" width="100" height="100"/><path fill="%234caf50" opacity="0.1" d="M0,0 L100,0 L100,100 L0,100 Z M20,20 L80,20 L80,80 L20,80 Z"/></svg>');
      z-index: -1;
    }
    
    .header-container {
      background: linear-gradient(90deg, var(--primary-green) 0%, var(--light-green) 100%);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .header-container::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      z-index: 0;
    }
    
    .header-content {
      position: relative;
      z-index: 1;
    }
    
    .card {
      border-radius: 15px;
      border: none;
      box-shadow: 0 10px 20px rgba(0,0,0,0.08);
      transition: box-shadow 0.3s;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
    }
    
    .card:hover {
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .card-title {
      color: var(--primary-green);
      font-weight: 600;
      border-bottom: 2px solid var(--light-green);
      padding-bottom: 10px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    
    .card-title i {
      margin-right: 10px;
      font-size: 1.5rem;
    }
    
    .stat-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px dashed #e0e0e0;
    }
    
    .stat-item:last-child {
      border-bottom: none;
    }
    
    .stat-value {
      font-weight: 600;
      color: var(--dark-green);
      background: #e8f5e9;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.9rem;
    }
    
    .btn-custom {
      border-radius: 25px;
      padding: 10px 25px;
      font-weight: 600;
      transition: box-shadow 0.3s;
      border: none;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .btn-irigasi {
      background: linear-gradient(45deg, var(--water-blue), var(--sky-blue));
      color: white;
    }
    
    .btn-hama {
      background: linear-gradient(45deg, var(--danger-red), #ff9800);
      color: white;
    }
    
    .btn-custom:hover {
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .card-body {
      padding: 25px;
    }
    
    .nature-decoration {
      position: absolute;
      z-index: 0;
      opacity: 0.1;
    }
    
    .leaf-1 {
      top: 10%;
      left: 5%;
      font-size: 120px;
      color: var(--primary-green);
      transform: rotate(-15deg);
    }
    
    .leaf-2 {
      bottom: 10%;
      right: 5%;
      font-size: 100px;
      color: var(--light-green);
      transform: rotate(25deg);
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    
    .status-on {
      background-color: #c8e6c9;
      color: var(--dark-green);
    }
    
    .status-off {
      background-color: #ffcdd2;
      color: var(--danger-red);
    }
    
    .chart-container {
      position: relative;
      height: 250px;
      margin-top: 20px;
    }
    
    .system-status {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
    }
    
    .status-card {
      background: white;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      flex: 1;
      max-width: 200px;
    }
    
    .status-card i {
      font-size: 2rem;
      margin-bottom: 10px;
    }
    
    .status-online {
      color: var(--light-green);
    }
    
    .status-offline {
      color: #9e9e9e;
    }
  </style>
</head>
<body>
  <div class="nature-decoration leaf-1">
    <i class="fas fa-leaf"></i>
  </div>
  <div class="nature-decoration leaf-2">
    <i class="fas fa-seedling"></i>
  </div>
  
  <div class="container my-5">
    <div class="header-container">
      <div class="header-content text-center">
        <h1 class="display-4 fw-bold"><i class="fas fa-tree"></i> Sistem IoT Pertanian Cerdas</h1>
        <p class="lead">Monitor dan kelola sistem irigasi serta deteksi hama secara real-time</p>
        <div class="system-status">
          <div class="status-card">
            <i class="fas fa-tint status-online"></i>
            <div>Sistem Irigasi</div>
            <small class="text-success">Online</small>
          </div>
          <div class="status-card">
            <i class="fas fa-bug status-online"></i>
            <div>Deteksi Hama</div>
            <small class="text-success">Online</small>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Card Irigasi -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title"><i class="fas fa-tint"></i> Sistem Irigasi Cerdas</h4>
            <div class="stat-list">
              <div class="stat-item">
                <span><i class="fas fa-seedling me-2"></i> Kelembaban Tanah:</span>
                <span class="stat-value"><?= $stat_irigasi['soil']; ?>%</span>
              </div>
              <div class="stat-item">
                <span><i class="fas fa-flask me-2"></i> Tingkat pH:</span>
                <span class="stat-value"><?= $stat_irigasi['ph']; ?></span>
              </div>
              <div class="stat-item">
                <span><i class="fas fa-power-off me-2"></i> Status Pompa:</span>
                <span class="status-badge <?= $stat_irigasi['pompa'] == 'ON' ? 'status-on' : 'status-off'; ?>">
                  <?= $stat_irigasi['pompa']; ?>
                </span>
              </div>
              <div class="stat-item">
                <span><i class="far fa-calendar-alt me-2"></i> Jadwal Penyiraman:</span>
                <span class="stat-value"><?= $stat_irigasi['jadwal']; ?> aktif</span>
              </div>
            </div>
            <div class="chart-container">
              <canvas id="chartIrigasi"></canvas>
            </div>
            <div class="text-center mt-4">
              <a href="irigasi/login.php" class="btn btn-custom btn-irigasi">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem Irigasi
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Hama -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title"><i class="fas fa-bug"></i> Sistem Deteksi Hama</h4>
            <div class="stat-list">
              <div class="stat-item">
                <span><i class="fas fa-thermometer-half me-2"></i> Suhu:</span>
                <span class="stat-value"><?= $stat_hama['suhu']; ?> °C</span>
              </div>
              <div class="stat-item">
                <span><i class="fas fa-tint me-2"></i> Kelembaban Udara:</span>
                <span class="stat-value"><?= $stat_hama['hum']; ?>%</span>
              </div>
              <div class="stat-item">
                <span><i class="fas fa-cloud-rain me-2"></i> Curah Hujan:</span>
                <span class="stat-value"><?= $stat_hama['hujan']; ?> mm</span>
              </div>
              <div class="stat-item">
                <span><i class="fas fa-wind me-2"></i> Kecepatan Angin:</span>
                <span class="stat-value"><?= $stat_hama['angin']; ?> km/jam</span>
              </div>
              <div class="stat-item">
                <span><i class="fas fa-ruler-combined me-2"></i> Aturan Hama:</span>
                <span class="stat-value"><?= $stat_hama['aturan']; ?> terdeteksi</span>
              </div>
            </div>
            <div class="chart-container">
              <canvas id="chartHama"></canvas>
            </div>
            <div class="text-center mt-4">
              <a href="hama/auth/login.php" class="btn btn-custom btn-hama">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Deteksi Hama
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const irigasiData = <?= json_encode($chart_irigasi); ?>;
    const hamaData = <?= json_encode($chart_hama); ?>;

    // Format waktu untuk label grafik
    function formatTimeLabel(fullTime) {
      const date = new Date(fullTime);
      return date.getHours().toString().padStart(2, '0') + ':' + 
             date.getMinutes().toString().padStart(2, '0');
    }

    // Chart Irigasi
    new Chart(document.getElementById('chartIrigasi'), {
      type: 'line',
      data: {
        labels: irigasiData.labels.map(label => formatTimeLabel(label)),
        datasets: [
          { 
            label: 'Kelembaban Tanah (%)', 
            data: irigasiData.soil, 
            borderColor: '#4caf50', 
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
          },
          { 
            label: 'Tingkat pH', 
            data: irigasiData.ph, 
            borderColor: '#2196f3', 
            backgroundColor: 'rgba(33, 150, 243, 0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
          }
        ]
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Trend Data Irigasi'
          }
        },
        scales: {
          y: {
            beginAtZero: false
          }
        }
      }
    });

    // Chart Hama
    new Chart(document.getElementById('chartHama'), {
      type: 'line',
      data: {
        labels: hamaData.labels.map(label => formatTimeLabel(label)),
        datasets: [
          { 
            label: 'Suhu (°C)', 
            data: hamaData.suhu, 
            borderColor: '#ff5722', 
            backgroundColor: 'rgba(255, 87, 34, 0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
          },
          { 
            label: 'Kelembaban (%)', 
            data: hamaData.hum, 
            borderColor: '#2196f3', 
            backgroundColor: 'rgba(33, 150, 243, 0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
          },
          { 
            label: 'Curah Hujan (mm)', 
            data: hamaData.hujan, 
            borderColor: '#0288d1', 
            backgroundColor: 'rgba(2, 136, 209, 0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
          },
          { 
            label: 'Kecepatan Angin (km/jam)', 
            data: hamaData.angin, 
            borderColor: '#795548', 
            backgroundColor: 'rgba(121, 85, 72, 0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
          }
        ]
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Trend Data Lingkungan'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>