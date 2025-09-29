<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');
$limit = 5;

// Curah Hujan Pagination
$page_ch = isset($_GET['page_ch']) ? (int)$_GET['page_ch'] : 1;
$offset_ch = ($page_ch - 1) * $limit;
$total_ch_result = $koneksi->query("SELECT COUNT(*) as total FROM curah_hujan WHERE DATE(waktu) = '$tanggal_hari_ini'");
$total_ch = $total_ch_result->fetch_assoc()['total'];
$total_pages_ch = ceil($total_ch / $limit);
$sql_ch = "SELECT * FROM curah_hujan WHERE DATE(waktu) = '$tanggal_hari_ini' ORDER BY waktu DESC LIMIT $limit OFFSET $offset_ch";
$result_ch = $koneksi->query($sql_ch);

// DHT Pagination
$page_dht = isset($_GET['page_dht']) ? (int)$_GET['page_dht'] : 1;
$offset_dht = ($page_dht - 1) * $limit;
$total_dht_result = $koneksi->query("SELECT COUNT(*) as total FROM dht_data WHERE DATE(waktu) = '$tanggal_hari_ini'");
$total_dht = $total_dht_result->fetch_assoc()['total'];
$total_pages_dht = ceil($total_dht / $limit);
$sql_dht = "SELECT * FROM dht_data WHERE DATE(waktu) = '$tanggal_hari_ini' ORDER BY waktu DESC LIMIT $limit OFFSET $offset_dht";
$result_dht = $koneksi->query($sql_dht);

// Anemometer Pagination
$page_an = isset($_GET['page_an']) ? (int)$_GET['page_an'] : 1;
$offset_an = ($page_an - 1) * $limit;
$total_an_result = $koneksi->query("SELECT COUNT(*) as total FROM anemometer WHERE DATE(waktu) = '$tanggal_hari_ini'");
$total_an = $total_an_result->fetch_assoc()['total'];
$total_pages_an = ceil($total_an / $limit);
$sql_an = "SELECT * FROM anemometer WHERE DATE(waktu) = '$tanggal_hari_ini' ORDER BY waktu DESC LIMIT $limit OFFSET $offset_an";
$result_an = $koneksi->query($sql_an);
?>
<?php
function deteksiHama($koneksi, $suhu, $kelembaban, $curah_hujan, $kecepatan_angin) {
    // Ambil semua aturan hama dari database
    $query = "SELECT h.*, a.* FROM hama h JOIN aturan_hama a ON h.id = a.hama_id";
    $result = $koneksi->query($query);
    
    $hama_terdeteksi = array();
    $tanggal_hari_ini = date('Y-m-d');
    
    while ($aturan = $result->fetch_assoc()) {
        $cocok = true;
        
        // Cek kondisi suhu
        if (!is_null($aturan['suhu_min']) && $suhu < $aturan['suhu_min']) $cocok = false;
        if (!is_null($aturan['suhu_max']) && $suhu > $aturan['suhu_max']) $cocok = false;
        
        // Cek kondisi kelembaban
        if (!is_null($aturan['kelembaban_min']) && $kelembaban < $aturan['kelembaban_min']) $cocok = false;
        if (!is_null($aturan['kelembaban_max']) && $kelembaban > $aturan['kelembaban_max']) $cocok = false;
        
        // Cek kondisi curah hujan
        if (!is_null($aturan['curah_hujan_min']) && $curah_hujan < $aturan['curah_hujan_min']) $cocok = false;
        if (!is_null($aturan['curah_hujan_max']) && $curah_hujan > $aturan['curah_hujan_max']) $cocok = false;
        
        // Cek kondisi kecepatan angin
        if (!is_null($aturan['kecepatan_angin_min']) && $kecepatan_angin < $aturan['kecepatan_angin_min']) $cocok = false;
        if (!is_null($aturan['kecepatan_angin_max']) && $kecepatan_angin > $aturan['kecepatan_angin_max']) $cocok = false;
        
        if ($cocok) {
            // Hitung confidence level (persentase kecocokan)
            $confidence = 0;
            $parameter = 0;
            
            if (!is_null($aturan['suhu_min']) || !is_null($aturan['suhu_max'])) {
                $parameter++;
                if ($suhu >= $aturan['suhu_min'] && $suhu <= $aturan['suhu_max']) $confidence++;
            }
            
            if (!is_null($aturan['kelembaban_min']) || !is_null($aturan['kelembaban_max'])) {
                $parameter++;
                if ($kelembaban >= $aturan['kelembaban_min'] && $kelembaban <= $aturan['kelembaban_max']) $confidence++;
            }
            
            if (!is_null($aturan['curah_hujan_min']) || !is_null($aturan['curah_hujan_max'])) {
                $parameter++;
                if ($curah_hujan >= $aturan['curah_hujan_min'] && $curah_hujan <= $aturan['curah_hujan_max']) $confidence++;
            }
            
            if (!is_null($aturan['kecepatan_angin_min']) || !is_null($aturan['kecepatan_angin_max'])) {
                $parameter++;
                if ($kecepatan_angin >= $aturan['kecepatan_angin_min'] && $kecepatan_angin <= $aturan['kecepatan_angin_max']) $confidence++;
            }
            
            $confidence_level = ($parameter > 0) ? round(($confidence / $parameter) * 100) : 0;
            
            if ($confidence_level > 0) {
                $hama_terdeteksi[] = array(
                    'id' => $aturan['id'],
                    'nama' => $aturan['nama'],
                    'deskripsi' => $aturan['deskripsi'],
                    'solusi' => $aturan['solusi'],
                    'confidence' => $confidence_level
                );
                
                // Simpan ke database
                $koneksi->query("INSERT INTO deteksi_hama (tanggal, hama_id, confidence) 
                                 VALUES ('$tanggal_hari_ini', {$aturan['hama_id']}, $confidence_level)");
            }
        }
    }
    
    return $hama_terdeteksi;
}

// Fungsi untuk mendapatkan data rata-rata sensor hari ini
function getDataRataRataHariIni($koneksi) {
    $tanggal_hari_ini = date('Y-m-d');
    
    // Rata-rata suhu dan kelembaban
    $query_dht = "SELECT AVG(suhu) as avg_suhu, AVG(kelembaban) as avg_kelembaban 
                  FROM dht_data WHERE DATE(waktu) = '$tanggal_hari_ini'";
    $result_dht = $koneksi->query($query_dht);
    $data_dht = $result_dht->fetch_assoc();
    
    // Rata-rata curah hujan
    $query_ch = "SELECT AVG(nilai) as avg_curah_hujan 
                 FROM curah_hujan WHERE DATE(waktu) = '$tanggal_hari_ini'";
    $result_ch = $koneksi->query($query_ch);
    $data_ch = $result_ch->fetch_assoc();
    
    // Rata-rata kecepatan angin
    $query_an = "SELECT 
                    AVG(CAST(kecepatan AS DECIMAL(10,2))/360) as avg_kecepatan,
                    COUNT(*) as total_data,
                    MIN(kecepatan/360) as min_kecepatan,
                    MAX(kecepatan/360) as max_kecepatan
                 FROM anemometer 
                 WHERE DATE(waktu) = '$tanggal_hari_ini'";
    $result_an = $koneksi->query($query_an);
    $data_an = $result_an->fetch_assoc();

    return array(
        'suhu' => $data_dht['avg_suhu'] ?? 0,
        'kelembaban' => $data_dht['avg_kelembaban'] ?? 0,
        'curah_hujan' => $data_ch['avg_curah_hujan'] ?? 0,
        'kecepatan_angin' => round($data_an['avg_kecepatan'] ?? 0, 2),
        'kecepatan_min' => round($data_an['min_kecepatan'] ?? 0, 2),
        'kecepatan_max' => round($data_an['max_kecepatan'] ?? 0, 2)
    );
}
?>
<?php
// Mendapatkan data rata-rata hari ini
$data_rata_rata = getDataRataRataHariIni($koneksi);

// Melakukan deteksi hama
$hama_terdeteksi = deteksiHama(
    $koneksi,
    $data_rata_rata['suhu'],
    $data_rata_rata['kelembaban'],
    $data_rata_rata['curah_hujan'],
    $data_rata_rata['kecepatan_angin']
);

// Mendapatkan riwayat deteksi hari ini
$riwayat_deteksi = $koneksi->query("SELECT dh.*, h.nama, h.deskripsi, h.solusi 
                                    FROM deteksi_hama dh 
                                    JOIN hama h ON dh.hama_id = h.id 
                                    WHERE dh.tanggal = '$tanggal_hari_ini'
                                    ORDER BY dh.confidence DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Cuaca Hari Ini</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #1B5E20;
            --primary: #2E7D32;
            --primary-light: #4CAF50;
            --primary-lighter: #81C784;
            --primary-lightest: #C8E6C9;
            --accent: #8BC34A;
            --text-dark: #263238;
            --text-light: #ECEFF1;
            --bg-light: #F5F5F6;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--text-light);
            padding: 2rem 1.5rem;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
            opacity: 0.6;
            z-index: -1;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .logo i {
            margin-right: 12px;
            font-size: 1.8rem;
        }
        
        .nav-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2rem 0 1rem;
            color: var(--primary-lightest);
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-item i {
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .nav-item:hover {
            background-color: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background-color: var(--accent);
            color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem 3rem;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .page-title p {
            color: #78909C;
            font-size: 0.9rem;
        }
        
        .date-display {
            background-color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            font-weight: 500;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .date-display i {
            margin-right: 8px;
            color: var(--primary-light);
        }
        
        /* Sensor Cards */
        .sensor-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .sensor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.1);
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .card-header i {
            font-size: 1.5rem;
            opacity: 0.8;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Compact Table */
        .compact-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
        }
        
        .compact-table thead th {
            background-color: var(--primary-lightest);
            color: var(--primary-dark);
            font-weight: 600;
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 2px solid var(--primary-lighter);
        }
        
        .compact-table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .compact-table tbody tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        .compact-table td {
            padding: 0.6rem 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .compact-table tr:last-child td {
            border-bottom: none;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #78909C;
            font-style: italic;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .pagination a, .pagination strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            margin: 0 3px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        
        .pagination a {
            color: var(--primary);
            border: 1px solid var(--primary-lighter);
            background-color: white;
        }
        
        .pagination a:hover {
            background-color: var(--primary-lightest);
            transform: translateY(-2px);
        }
        
        .pagination strong {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 1.5rem;
            }
            
            .main-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .date-display {
                margin-top: 1rem;
            }
            
            .compact-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-cloud-sun"></i>
                <span>Pendeteksi Hama</span>
            </div>
            
            <div class="nav-title">Navigation</div>
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="nav-title">Sensor Data</div>
            <a href="riwayat_curah_hujan.php" class="nav-item">
                <i class="fas fa-cloud-rain"></i>
                <span>Curah Hujan</span>
            </a>
            <a href="riwayat_dht.php" class="nav-item">
                <i class="fas fa-thermometer-half"></i>
                <span>Suhu & Kelembaban</span>
            </a>
            <a href="riwayat_anemometer.php" class="nav-item">
                <i class="fas fa-wind"></i>
                <span>Kecepatan Angin</span>
            </a>
            <a href="auth/logout.php" class="nav-item">
                <i class=""></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Monitoring Cuaca Hari Ini</h1>
                    <p>Data real-time dari berbagai sensor cuaca</p>
                </div>
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span><?php echo $tanggal_hari_ini; ?></span>
                </div>
            </div>
            <!-- Hasil Deteksi Hama Card -->
<div class="sensor-card">
    <div class="card-header">
        <h2><i class="fas fa-bug"></i> Hasil Deteksi Hama</h2>
        <i class="fas fa-search"></i>
    </div>
    <div class="card-body">
        <div style="margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary); margin-bottom: 0.5rem;">Kondisi Cuaca Hari Ini:</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <div style="background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px; flex: 1; min-width: 120px;">
                    <div style="font-size: 0.8rem; color: var(--primary-dark);">Suhu Rata-rata</div>
                    <div style="font-weight: 600; font-size: 1.2rem;"><?= round($data_rata_rata['suhu'], 1) ?> °C</div>
                </div>
                <div style="background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px; flex: 1; min-width: 120px;">
                    <div style="font-size: 0.8rem; color: var(--primary-dark);">Kelembaban Rata-rata</div>
                    <div style="font-weight: 600; font-size: 1.2rem;"><?= round($data_rata_rata['kelembaban'], 1) ?> %</div>
                </div>
                <div style="background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px; flex: 1; min-width: 120px;">
                    <div style="font-size: 0.8rem; color: var(--primary-dark);">Curah Hujan</div>
                    <div style="font-weight: 600; font-size: 1.2rem;"><?= round($data_rata_rata['curah_hujan'], 1) ?> mm</div>
                </div>
                <div style="background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px; flex: 1; min-width: 120px;">
                    <div style="font-size: 0.8rem; color: var(--primary-dark);">Kecepatan Angin</div>
                    <div style="font-weight: 600; font-size: 1.2rem;"><?= round($data_rata_rata['kecepatan_angin'], 1) ?> km/jam</div>
                </div>
            </div>
        </div>

        <?php if (count($hama_terdeteksi) > 0): ?>
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Hama yang Mungkin Muncul:</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                <?php foreach ($hama_terdeteksi as $hama): ?>
                    <div style="background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem; border-left: 4px solid var(--accent);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <h4 style="margin: 0; color: var(--primary-dark);"><?= $hama['nama'] ?></h4>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                <?= $hama['confidence'] ?>% Cocok
                            </span>
                        </div>
                        <div style="font-size: 0.9rem; color: #555; margin-bottom: 0.5rem;">
                            <?= $hama['deskripsi'] ?>
                        </div>
                        <div style="font-size: 0.85rem; background: var(--primary-lightest); padding: 0.5rem; border-radius: 4px;">
                            <strong>Solusi:</strong> <?= $hama['solusi'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: #78909C; font-style: italic;">
                Berdasarkan data sensor saat ini, tidak terdeteksi hama yang mungkin muncul.
            </div>
        <?php endif; ?>

        <?php if ($riwayat_deteksi->num_rows > 0): ?>
            <h3 style="color: var(--primary); margin-top: 2rem; margin-bottom: 1rem;">Riwayat Deteksi Hari Ini:</h3>
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Hama</th>
                        <th>Tingkat Kecocokan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($riwayat = $riwayat_deteksi->fetch_assoc()): ?>
                        
                        <tr>
                            <td><?= date('d-m-Y', strtotime($riwayat['tanggal'])) ?></td>
                            <td><?= $riwayat['nama'] ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 100%; background: #eee; height: 8px; border-radius: 4px;">
                                        <div style="width: <?= $riwayat['confidence'] ?>%; background: var(--primary); height: 100%; border-radius: 4px;"></div>
                                    </div>
                                    <span><?= $riwayat['confidence'] ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
            <!-- Curah Hujan Card -->
            <div class="sensor-card">
                <div class="card-header">
                    <h2><i class="fas fa-cloud-rain"></i> Data Curah Hujan</h2>
                    <i class="fas fa-tint"></i>
                </div>
                <div class="card-body">
                    <!-- Tambahan informasi rata-rata -->
                    <div style="margin-bottom: 1rem; font-weight: 500; background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px;">
                        Rata-rata hari ini: 
                        <span style="color: var(--primary); font-weight: 600;">
                            <?= round($data_rata_rata['curah_hujan'], 1) ?> mm
                        </span>
                    </div>
                    
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Curah Hujan (mm)</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset_ch + 1;
                            if ($total_ch > 0) {
                                while ($row = $result_ch->fetch_assoc()) {
                                    echo "<tr>
                                            <td>$no</td>
                                            <td>{$row['nilai']}</td>
                                            <td>{$row['waktu']}</td>
                                        </tr>";
                                    $no++;
                                }
                            } else {
                                echo '<tr><td colspan="3" class="no-data">Tidak ada data hari ini</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages_ch > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages_ch; $i++) {
                            $link = "?page_ch=$i&page_dht=$page_dht&page_an=$page_an";
                            echo $i == $page_ch ? "<strong>$i</strong>" : "<a href='$link'>$i</a>";
                        } ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Temperature & Humidity Card -->
            <div class="sensor-card">
                <div class="card-header">
                    <h2><i class="fas fa-thermometer-half"></i> Data Suhu & Kelembaban</h2>
                    <i class="fas fa-water"></i>
                </div>
                <div class="card-body">
                    <!-- Tambahan informasi rata-rata -->
                    <div style="margin-bottom: 1rem; font-weight: 500; background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <div>
                            Rata-rata suhu: 
                            <span style="color: var(--primary); font-weight: 600;">
                                <?= round($data_rata_rata['suhu'], 1) ?> °C
                            </span>
                        </div>
                        <div>
                            Rata-rata kelembaban: 
                            <span style="color: var(--primary); font-weight: 600;">
                                <?= round($data_rata_rata['kelembaban'], 1) ?> %
                            </span>
                        </div>
                    </div>
                    
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Suhu (°C)</th>
                                <th>Kelembaban (%)</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset_dht + 1;
                            if ($total_dht > 0) {
                                while ($row = $result_dht->fetch_assoc()) {
                                    echo "<tr>
                                            <td>$no</td>
                                            <td>{$row['suhu']}</td>
                                            <td>{$row['kelembaban']}</td>
                                            <td>{$row['waktu']}</td>
                                        </tr>";
                                    $no++;
                                }
                            } else {
                                echo '<tr><td colspan="4" class="no-data">Tidak ada data hari ini</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages_dht > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages_dht; $i++) {
                            $link = "?page_ch=$page_ch&page_dht=$i&page_an=$page_an";
                            echo $i == $page_dht ? "<strong>$i</strong>" : "<a href='$link'>$i</a>";
                        } ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

             <!-- Wind Speed Card -->
            <div class="sensor-card">
                <div class="card-header">
                    <h2><i class="fas fa-wind"></i> Data Kecepatan Angin</h2>
                    <i class="fas fa-fan"></i>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem; font-weight: 500; background: var(--primary-lightest); padding: 0.75rem; border-radius: 8px;">
                    Rata-rata hari ini: 
                    <span style="color: var(--primary); font-weight: 600;">
                        <?php 
                        if ($data_rata_rata['kecepatan_angin'] > 300) {
                            echo "Data Invalid (Perlu Kalibrasi)"; 
                        } else {
                            echo round($data_rata_rata['kecepatan_angin'], 2) . " km/jam";
                        }
                        ?>
                    </span>
                    <?php if(isset($data_an['total_data'])): ?>
                        (dari <?= $data_an['total_data'] ?> data)
                    <?php endif; ?>
                </div>
                    
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kecepatan Angin (km/jam)</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset_an + 1;
                            if ($total_an > 0) {
                                while ($row = $result_an->fetch_assoc()) {
                                    echo "<tr>
                                            <td>$no</td>
                                            <td>".round($row['kecepatan'], 2)."</td>
                                            <td>{$row['waktu']}</td>
                                        </tr>";
                                    $no++;
                                }
                            } else {
                                echo '<tr><td colspan="3" class="no-data">Tidak ada data hari ini</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
                    <?php if ($total_pages_an > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages_an; $i++) {
                            $link = "?page_ch=$page_ch&page_dht=$page_dht&page_an=$i";
                            echo $i == $page_an ? "<strong>$i</strong>" : "<a href='$link'>$i</a>";
                        } ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>