<?php
require 'api/config.php';

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// Query data
$query = $conn->prepare("SELECT * FROM sensor_data WHERE DATE(waktu) BETWEEN ? AND ? ORDER BY waktu DESC");
$query->bind_param("ss", $tgl_awal, $tgl_akhir);
$query->execute();
$result = $query->get_result();

// Siapkan data untuk grafik
$result->data_seek(0);
$labels = [];
$moistureData = [];
$phData = [];

while ($row = $result->fetch_assoc()) {
  $labels[] = date('d/m H:i', strtotime($row['waktu']));
  $moistureData[] = floatval($row['soil_moisture']);
  $phData[] = floatval($row['ph']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Data Sensor - Sistem Irigasi Cerdas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    
    .container-custom {
      max-width: 1400px;
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
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      margin-bottom: 25px;
    }
    
    .card-header {
      background: linear-gradient(90deg, var(--water-blue) 0%, var(--sky-blue) 100%);
      color: white;
      border-radius: 15px 15px 0 0 !important;
      padding: 15px 20px;
      border: none;
    }
    
    .card-title {
      color: var(--primary-green);
      font-weight: 600;
      margin-bottom: 0;
      display: flex;
      align-items: center;
    }
    
    .card-title i {
      margin-right: 10px;
      font-size: 1.3rem;
    }
    
    .btn-custom {
      border-radius: 12px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    
    .btn-primary-custom {
      background: linear-gradient(45deg, var(--water-blue), var(--sky-blue));
      color: white;
    }
    
    .btn-success-custom {
      background: linear-gradient(45deg, var(--light-green), var(--primary-green));
      color: white;
    }
    
    .btn-secondary-custom {
      background: linear-gradient(45deg, var(--earth-brown), #a1887f);
      color: white;
    }
    
    .btn-custom:hover {
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
      transform: translateY(-2px);
      color: white;
    }
    
    .form-control {
      border-radius: 12px;
      padding: 10px 15px;
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
    
    .table-container {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    .table {
      margin-bottom: 0;
      background: white;
    }
    
    .table thead {
      background: linear-gradient(90deg, var(--primary-green) 0%, var(--light-green) 100%);
      color: white;
    }
    
    .table th {
      border: none;
      padding: 15px 12px;
      font-weight: 600;
      text-align: center;
      vertical-align: middle;
    }
    
    .table td {
      border: 1px solid #e9ecef;
      padding: 12px;
      text-align: center;
      vertical-align: middle;
    }
    
    .table tbody tr:hover {
      background-color: rgba(76, 175, 80, 0.05);
    }
    
    .status-on {
      color: var(--light-green);
      font-weight: 600;
    }
    
    .status-off {
      color: var(--danger-red);
      font-weight: 600;
    }
    
    .chart-container {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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
    
    .filter-section {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
      .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
    
    .pagination-info {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 500;
      color: var(--dark-green);
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
  
  <div class="container-custom">
    <!-- Header -->
    <div class="header-container">
      <div class="header-content">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2"><i class="fas fa-history me-3"></i>Riwayat Data Sensor</h1>
            <p class="mb-0 opacity-90">Data lengkap monitoring sistem irigasi</p>
          </div>
          <div class="col-md-4 text-end">
            <a href="dashboard.php" class="btn btn-light btn-custom no-print">
              <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section no-print">
      <div class="row align-items-end">
        <div class="col-md-3">
          <label class="form-label">Tanggal Awal:</label>
          <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Akhir:</label>
          <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control" />
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-custom btn-primary-custom w-100">
            <i class="fas fa-filter me-2"></i>Filter Data
          </button>
        </div>
        <div class="col-md-2">
          <button type="button" onclick="window.print()" class="btn btn-custom btn-success-custom w-100">
            <i class="fas fa-print me-2"></i>Cetak
          </button>
        </div>
      </div>
    </div>

    <!-- Info Periode -->
    <div class="pagination-info">
      <i class="fas fa-calendar me-2"></i>
      Menampilkan data dari <strong><?= date('d F Y', strtotime($tgl_awal)) ?></strong> 
      hingga <strong><?= date('d F Y', strtotime($tgl_akhir)) ?></strong>
    </div>

    <!-- Tabel Data -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0 text-white"><i class="fas fa-table me-2"></i>Tabel Data Sensor</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-container">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>No</th>
                  <th><i class="fas fa-seedling me-2"></i>Soil Moisture</th>
                  <th><i class="fas fa-flask me-2"></i>pH Tanah</th>
                  <th><i class="fas fa-power-off me-2"></i>Status Pompa</th>
                  <th><i class="fas fa-clock me-2"></i>Waktu Pencatatan</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result->data_seek(0);
                $no = 1;
                while($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td class="fw-bold"><?= $no++ ?></td>
                    <td>
                      <span class="badge bg-info bg-opacity-10 text-info fs-6">
                        <?= $row['soil_moisture'] ?>%
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-primary bg-opacity-10 text-primary fs-6">
                        <?= $row['ph'] ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge <?= $row['pompa_status'] === 'ON' ? 'bg-success' : 'bg-danger' ?> fs-6">
                        <i class="fas fa-<?= $row['pompa_status'] === 'ON' ? 'play' : 'stop' ?> me-1"></i>
                        <?= $row['pompa_status'] ?? '-' ?>
                      </span>
                    </td>
                    <td>
                      <span class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        <?= date('d/m/Y H:i:s', strtotime($row['waktu'])) ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Grafik -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0 text-white"><i class="fas fa-chart-line me-2"></i>Grafik Trend Data Sensor</h3>
      </div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="sensorChart" height="120"></canvas>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="row no-print">
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-seedling fa-2x text-success mb-3"></i>
            <h4 class="text-success">Kelembaban Rata-rata</h4>
            <h2 class="text-success">
              <?php 
                $result->data_seek(0);
                $total = 0; $count = 0;
                while($row = $result->fetch_assoc()) {
                  $total += $row['soil_moisture'];
                  $count++;
                }
                echo $count > 0 ? round($total/$count, 1) . '%' : '-';
              ?>
            </h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-flask fa-2x text-primary mb-3"></i>
            <h4 class="text-primary">pH Rata-rata</h4>
            <h2 class="text-primary">
              <?php 
                $result->data_seek(0);
                $total = 0; $count = 0;
                while($row = $result->fetch_assoc()) {
                  $total += $row['ph'];
                  $count++;
                }
                echo $count > 0 ? round($total/$count, 1) : '-';
              ?>
            </h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-database fa-2x text-warning mb-3"></i>
            <h4 class="text-warning">Total Data</h4>
            <h2 class="text-warning"><?= $no-1 ?> Records</h2>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Chart.js Script -->
  <script>
    const ctx = document.getElementById('sensorChart').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode(array_reverse($labels)) ?>,
        datasets: [
          {
            label: 'Soil Moisture (%)',
            data: <?= json_encode(array_reverse($moistureData)) ?>,
            borderColor: '#4caf50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
          },
          {
            label: 'pH Tanah',
            data: <?= json_encode(array_reverse($phData)) ?>,
            borderColor: '#2196f3',
            backgroundColor: 'rgba(33, 150, 243, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: { size: 12, weight: '600' },
              color: '#333'
            }
          },
          title: {
            display: true,
            text: 'Trend Data Sensor',
            font: { size: 16, weight: '600' },
            color: '#333'
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            grid: { color: 'rgba(0,0,0,0.1)' },
            ticks: { color: '#666', font: { size: 11 } }
          },
          x: {
            grid: { color: 'rgba(0,0,0,0.1)' },
            ticks: { color: '#666', font: { size: 10 } }
          }
        }
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>