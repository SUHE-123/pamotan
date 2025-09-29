<?php
require 'api/config.php';
session_start();

if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}

// Proses input dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Penyiraman
  if (isset($_POST['penyiraman'])) {
    $mode = $_POST['mode'];
    $jam1 = isset($_POST['jam1']) ? (int)$_POST['jam1'] : null;
    $menit1 = isset($_POST['menit1']) ? (int)$_POST['menit1'] : null;
    $jam2 = isset($_POST['jam2']) ? (int)$_POST['jam2'] : null;
    $menit2 = isset($_POST['menit2']) ? (int)$_POST['menit2'] : null;

    if ($mode === 'SCHEDULE') {
      $conn->query("REPLACE INTO penyiraman_air (id, mode, jam1, menit1, jam2, menit2)
                    VALUES (1, '$mode', $jam1, $menit1, $jam2, $menit2)");
    } else {
      $conn->query("REPLACE INTO penyiraman_air (id, mode, jam1, menit1, jam2, menit2)
                    VALUES (1, '$mode', NULL, NULL, NULL, NULL)");
    }
  }

  // Pemupukan
  if (isset($_POST['pemupukan'])) {
    $jenis = $_POST['jenis'];
    $tanggal = $_POST['tanggal'];
    $jam = (int)$_POST['jam'];
    $menit = (int)$_POST['menit'];

    $conn->query("INSERT INTO pemupukan (jenis, tanggal, jam, menit)
                  VALUES ('$jenis', '$tanggal', $jam, $menit)");
  }
}

// Ambil data sensor terbaru
$latest = $conn->query("SELECT soil_moisture, ph, pompa_status, waktu FROM sensor_data ORDER BY waktu DESC LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Sistem Irigasi Cerdas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-green: #2e7d32;
      --light-green: #4caf50;
      --dark-green: #1b5e20;
      --water-blue: #0288d1;
      --sky-blue: #29b6f6;
      --earth-brown: #795548;
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
    
    .dashboard-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
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
      margin-bottom: 25px;
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
    
    .sensor-card {
      text-align: center;
      padding: 20px;
      border-radius: 12px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border: 1px solid #dee2e6;
      transition: transform 0.3s;
    }
    
    .sensor-card:hover {
      transform: translateY(-5px);
    }
    
    .sensor-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 10px 0;
    }
    
    .sensor-label {
      color: var(--dark-green);
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .sensor-unit {
      font-size: 1rem;
      color: #6c757d;
      font-weight: 500;
    }
    
    .status-on {
      color: var(--light-green);
    }
    
    .status-off {
      color: var(--danger-red);
    }
    
    .btn-custom {
      border-radius: 12px;
      padding: 12px 25px;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .btn-primary-custom {
      background: linear-gradient(45deg, var(--water-blue), var(--sky-blue));
      color: white;
    }
    
    .btn-secondary-custom {
      background: linear-gradient(45deg, var(--earth-brown), #a1887f);
      color: white;
    }
    
    .btn-danger-custom {
      background: linear-gradient(45deg, var(--danger-red), #ff9800);
      color: white;
    }
    
    .btn-custom:hover {
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
      transform: translateY(-2px);
      color: white;
    }
    
    .form-control {
      border-radius: 12px;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      transition: all 0.3s;
      background: #f8f9fa;
    }
    
    .form-control:focus {
      border-color: var(--light-green);
      box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
      background: white;
    }
    
    .form-label {
      font-weight: 600;
      color: var(--dark-green);
      margin-bottom: 8px;
    }
    
    .time-inputs {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      border: 2px dashed #dee2e6;
    }
    
    .nature-decoration {
      position: absolute;
      z-index: 0;
      opacity: 0.1;
      pointer-events: none;
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
    
    .last-update {
      background: rgba(255,255,255,0.9);
      border-radius: 10px;
      padding: 10px 15px;
      display: inline-block;
      font-size: 0.9rem;
      color: var(--dark-green);
      font-weight: 500;
    }
  </style>
  <script>
    function toggleWaktuFields() {
      const mode = document.querySelector('select[name="mode"]').value;
      document.getElementById('waktuFields').style.display = (mode === 'SCHEDULE') ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', toggleWaktuFields);
  </script>
</head>
<body>
  <div class="nature-decoration leaf-1">
    <i class="fas fa-leaf"></i>
  </div>
  <div class="nature-decoration leaf-2">
    <i class="fas fa-seedling"></i>
  </div>
  
  <div class="dashboard-container">
    <!-- Header -->
    <div class="header-container">
      <div class="header-content">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2"><i class="fas fa-tint me-3"></i>Dashboard Sistem Irigasi Cerdas</h1>
            <p class="mb-0 opacity-90">Monitor dan kelola sistem irigasi secara real-time</p>
          </div>
          <div class="col-md-4 text-end">
            <div class="btn-group">
              <a href="riwayat.php" class="btn btn-light btn-sm me-2">
                <i class="fas fa-history me-2"></i>Riwayat Data
              </a>
              <a href="logout.php" class="btn btn-light btn-sm">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Data Sensor Terkini -->
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h3 class="card-title mb-0"><i class="fas fa-chart-line"></i> Data Sensor Terkini</h3>
              <span class="last-update">
                <i class="fas fa-clock me-2"></i>
                <?= isset($latest['waktu']) ? $latest['waktu'] : 'Tidak ada data' ?>
              </span>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <div class="sensor-card">
                  <i class="fas fa-seedling fa-2x text-success"></i>
                  <div class="sensor-value"><?= isset($latest['soil_moisture']) ? $latest['soil_moisture'] : '-' ?><span class="sensor-unit">%</span></div>
                  <div class="sensor-label">Kelembaban Tanah</div>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="sensor-card">
                  <i class="fas fa-flask fa-2x text-primary"></i>
                  <div class="sensor-value"><?= isset($latest['ph']) ? $latest['ph'] : '-' ?></div>
                  <div class="sensor-label">Tingkat pH</div>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="sensor-card">
                  <i class="fas fa-power-off fa-2x <?= $latest['pompa_status'] == 'ON' ? 'status-on' : 'status-off' ?>"></i>
                  <div class="sensor-value <?= $latest['pompa_status'] == 'ON' ? 'status-on' : 'status-off' ?>">
                    <?= $latest['pompa_status'] ?? '-' ?>
                  </div>
                  <div class="sensor-label">Status Pompa</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pengaturan Penyiraman -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="card-title"><i class="fas fa-water me-2"></i>Pengaturan Penyiraman</h3>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Mode Penyiraman:</label>
                <select name="mode" onchange="toggleWaktuFields()" class="form-select">
                  <option value="AUTO">Otomatis (Berdasarkan Sensor)</option>
                  <option value="SCHEDULE">Manual (Jadwal)</option>
                </select>
              </div>
              
              <div id="waktuFields" style="display:none" class="time-inputs mb-3">
                <label class="form-label">Waktu Penyiraman:</label>
                <div class="row mb-3">
                  <div class="col-6">
                    <label class="form-label small">Sesi 1 - Jam</label>
                    <input type="number" name="jam1" min="0" max="23" class="form-control" placeholder="0-23" />
                  </div>
                  <div class="col-6">
                    <label class="form-label small">Sesi 1 - Menit</label>
                    <input type="number" name="menit1" min="0" max="59" class="form-control" placeholder="0-59" />
                  </div>
                </div>
                <div class="row">
                  <div class="col-6">
                    <label class="form-label small">Sesi 2 - Jam</label>
                    <input type="number" name="jam2" min="0" max="23" class="form-control" placeholder="0-23" />
                  </div>
                  <div class="col-6">
                    <label class="form-label small">Sesi 2 - Menit</label>
                    <input type="number" name="menit2" min="0" max="59" class="form-control" placeholder="0-59" />
                  </div>
                </div>
              </div>
              
              <button type="submit" name="penyiraman" class="btn btn-custom btn-primary-custom w-100">
                <i class="fas fa-save me-2"></i>Simpan Pengaturan Penyiraman
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Jadwal Pemupukan -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="card-title"><i class="fas fa-seedling me-2"></i>Jadwal Pemupukan</h3>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Jenis Pupuk:</label>
                <select name="jenis" class="form-select">
                  <option value="N">Nitrogen (N)</option>
                  <option value="P">Fosfor (P)</option>
                  <option value="K">Kalium (K)</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Tanggal Pemupukan:</label>
                <input type="date" name="tanggal" class="form-control" required />
              </div>
              
              <div class="row mb-3">
                <div class="col-6">
                  <label class="form-label">Jam:</label>
                  <input type="number" name="jam" min="0" max="23" class="form-control" placeholder="0-23" required />
                </div>
                <div class="col-6">
                  <label class="form-label">Menit:</label>
                  <input type="number" name="menit" min="0" max="59" class="form-control" placeholder="0-59" required />
                </div>
              </div>
              
              <button type="submit" name="pemupukan" class="btn btn-custom btn-secondary-custom w-100">
                <i class="fas fa-plus me-2"></i>Tambah Jadwal Pemupukan
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>