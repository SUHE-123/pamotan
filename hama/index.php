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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Pendeteksi Hama</title>
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
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Mobile First Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--text-light);
            padding: 1rem;
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.active {
            left: 0;
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
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 1.3rem;
            padding: 0.5rem 0;
        }
        
        .logo i {
            margin-right: 12px;
            font-size: 1.5rem;
        }
        
        .nav-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1.5rem 0 0.8rem;
            color: var(--primary-lightest);
            padding: 0 0.5rem;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 8px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .nav-item i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
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
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.6rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .mobile-menu-toggle i {
            font-size: 1.2rem;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 1rem;
            margin-top: 0;
            transition: margin-left 0.3s ease;
        }
        
        .header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-top: 0.5rem;
        }
        
        .page-title h1 {
            font-size: 1.5rem;
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .page-title p {
            color: #78909C;
            font-size: 0.85rem;
        }
        
        .date-display {
            background-color: white;
            padding: 0.7rem 1.2rem;
            border-radius: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-weight: 500;
            color: var(--primary);
            display: flex;
            align-items: center;
            align-self: flex-start;
            font-size: 0.9rem;
        }
        
        .date-display i {
            margin-right: 8px;
            color: var(--primary-light);
        }
        
        /* Sensor Cards */
        .sensor-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .sensor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .card-header {
            padding: 1.2rem 1.2rem;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-header i {
            font-size: 1.3rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 1.2rem;
        }
        
        /* Sensor Grid for Mobile */
        .sensor-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.8rem;
            margin-bottom: 1.2rem;
        }
        
        .sensor-item {
            background: var(--primary-lightest);
            padding: 1rem 0.8rem;
            border-radius: 10px;
            text-align: center;
            border: 1px solid var(--primary-lighter);
        }
        
        .sensor-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0.3rem 0;
        }
        
        .sensor-label {
            font-size: 0.75rem;
            color: var(--primary-dark);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .sensor-unit {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        /* Hama Cards */
        .hama-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .hama-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 1.2rem;
            border-left: 4px solid var(--accent);
            transition: transform 0.2s ease;
        }
        
        .hama-card:hover {
            transform: translateX(3px);
        }
        
        .hama-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
            gap: 0.8rem;
        }
        
        .hama-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin: 0;
            flex: 1;
        }
        
        .confidence-badge {
            background: var(--primary);
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .hama-description {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.8rem;
            line-height: 1.5;
        }
        
        .hama-solution {
            font-size: 0.8rem;
            background: var(--primary-lightest);
            padding: 0.8rem;
            border-radius: 6px;
            line-height: 1.4;
        }
        
        /* Tables */
        .table-container {
            overflow-x: auto;
            margin: 1rem 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .compact-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 400px;
        }
        
        .compact-table thead th {
            background-color: var(--primary-lightest);
            color: var(--primary-dark);
            font-weight: 600;
            padding: 0.8rem 0.6rem;
            text-align: left;
            border-bottom: 2px solid var(--primary-lighter);
            white-space: nowrap;
        }
        
        .compact-table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .compact-table tbody tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        .compact-table td {
            padding: 0.7rem 0.6rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .compact-table tr:last-child td {
            border-bottom: none;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem 1rem;
            color: #78909C;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-top: 1.2rem;
        }
        
        .pagination a, .pagination strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 0.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .pagination a {
            color: var(--primary);
            border: 1px solid var(--primary-lighter);
            background-color: white;
        }
        
        .pagination a:hover {
            background-color: var(--primary-lightest);
            transform: translateY(-1px);
        }
        
        .pagination strong {
            background-color: var(--primary);
            color: white;
            border: 1px solid var(--primary);
            box-shadow: 0 2px 6px rgba(46, 125, 50, 0.3);
        }
        
        /* Progress Bar */
        .progress-container {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .progress-bar {
            flex: 1;
            background: #eee;
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        /* Responsive Breakpoints */
        @media (min-width: 768px) {
            .dashboard {
                flex-direction: row;
            }
            
            .sidebar {
                position: static;
                left: 0;
                width: 280px;
                height: 100vh;
                overflow-y: auto;
            }
            
            .mobile-menu-toggle {
                display: none;
            }
            
            .main-content {
                margin-top: 0;
                padding: 2rem;
                flex: 1;
            }
            
            .header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .date-display {
                align-self: center;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .sensor-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }
            
            .hama-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .card-header h2 {
                font-size: 1.25rem;
            }
        }
        
        @media (min-width: 1024px) {
            .main-content {
                padding: 2rem 3rem;
            }
            
            .hama-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .sensor-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.6rem;
            }
            
            .sensor-item {
                padding: 0.8rem 0.6rem;
            }
            
            .sensor-value {
                font-size: 1.2rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .compact-table {
                font-size: 0.75rem;
            }
            
            .compact-table th,
            .compact-table td {
                padding: 0.6rem 0.4rem;
            }
        }
        
        /* Overlay for mobile menu */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
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
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
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
                        <h3 style="color: var(--primary); margin-bottom: 0.8rem; font-size: 1.1rem;">Kondisi Cuaca Hari Ini:</h3>
                        <div class="sensor-grid">
                            <div class="sensor-item">
                                <div class="sensor-label">Suhu Rata-rata</div>
                                <div class="sensor-value"><?= round($data_rata_rata['suhu'], 1) ?><span class="sensor-unit">°C</span></div>
                            </div>
                            <div class="sensor-item">
                                <div class="sensor-label">Kelembaban Rata-rata</div>
                                <div class="sensor-value"><?= round($data_rata_rata['kelembaban'], 1) ?><span class="sensor-unit">%</span></div>
                            </div>
                            <div class="sensor-item">
                                <div class="sensor-label">Curah Hujan</div>
                                <div class="sensor-value"><?= round($data_rata_rata['curah_hujan'], 1) ?><span class="sensor-unit">mm</span></div>
                            </div>
                            <div class="sensor-item">
                                <div class="sensor-label">Kecepatan Angin</div>
                                <div class="sensor-value"><?= round($data_rata_rata['kecepatan_angin'], 1) ?><span class="sensor-unit">km/jam</span></div>
                            </div>
                        </div>
                    </div>

                    <?php if (count($hama_terdeteksi) > 0): ?>
                        <h3 style="color: var(--primary); margin-bottom: 1rem; font-size: 1.1rem;">Hama yang Mungkin Muncul:</h3>
                        <div class="hama-grid">
                            <?php foreach ($hama_terdeteksi as $hama): ?>
                                <div class="hama-card">
                                    <div class="hama-header">
                                        <h4 class="hama-name"><?= $hama['nama'] ?></h4>
                                        <span class="confidence-badge"><?= $hama['confidence'] ?>% Cocok</span>
                                    </div>
                                    <div class="hama-description">
                                        <?= $hama['deskripsi'] ?>
                                    </div>
                                    <div class="hama-solution">
                                        <strong>Solusi:</strong> <?= $hama['solusi'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--primary-light);"></i>
                            <div>Berdasarkan data sensor saat ini, tidak terdeteksi hama yang mungkin muncul.</div>
                        </div>
                    <?php endif; ?>

                    <?php if ($riwayat_deteksi->num_rows > 0): ?>
                        <h3 style="color: var(--primary); margin-top: 1.5rem; margin-bottom: 1rem; font-size: 1.1rem;">Riwayat Deteksi Hari Ini:</h3>
                        <div class="table-container">
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
                                            <td><?= date('H:i', strtotime($riwayat['tanggal'])) ?></td>
                                            <td><?= $riwayat['nama'] ?></td>
                                            <td>
                                                <div class="progress-container">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill" style="width: <?= $riwayat['confidence'] ?>%"></div>
                                                    </div>
                                                    <span style="font-weight: 600; color: var(--primary); font-size: 0.8rem;"><?= $riwayat['confidence'] ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sensor Data Cards -->
            <!-- Curah Hujan Card -->
            <div class="sensor-card">
                <div class="card-header">
                    <h2><i class="fas fa-cloud-rain"></i> Data Curah Hujan</h2>
                    <i class="fas fa-tint"></i>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem; font-weight: 500; background: var(--primary-lightest); padding: 0.8rem; border-radius: 8px; font-size: 0.9rem;">
                        <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>
                        Rata-rata hari ini: 
                        <span style="color: var(--primary); font-weight: 600;">
                            <?= round($data_rata_rata['curah_hujan'], 1) ?> mm
                        </span>
                    </div>
                    
                    <div class="table-container">
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
                                                <td><span style='background: var(--primary-lightest); color: var(--primary-dark); padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;'>{$row['nilai']} mm</span></td>
                                                <td style='color: #666; font-size: 0.8rem;'>{$row['waktu']}</td>
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
                    <div style="margin-bottom: 1rem; font-weight: 500; background: var(--primary-lightest); padding: 0.8rem; border-radius: 8px; font-size: 0.9rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
                            <div>
                                <i class="fas fa-fire" style="margin-right: 0.3rem;"></i>
                                <strong>Suhu rata-rata:</strong> 
                                <?= round($data_rata_rata['suhu'], 1) ?> °C
                            </div>
                            <div>
                                <i class="fas fa-tint" style="margin-right: 0.3rem;"></i>
                                <strong>Kelembaban rata-rata:</strong> 
                                <?= round($data_rata_rata['kelembaban'], 1) ?> %
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-container">
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
                                                <td><span style='background: #fff3e0; color: #e65100; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;'>{$row['suhu']}°C</span></td>
                                                <td><span style='background: #e3f2fd; color: #1565c0; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;'>{$row['kelembaban']}%</span></td>
                                                <td style='color: #666; font-size: 0.8rem;'>{$row['waktu']}</td>
                                            </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="no-data">Tidak ada data hari ini</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
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
                    <div style="margin-bottom: 1rem; font-weight: 500; background: var(--primary-lightest); padding: 0.8rem; border-radius: 8px; font-size: 0.9rem;">
                        <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>
                        Rata-rata hari ini: 
                        <span style="color: var(--primary); font-weight: 600;">
                            <?php 
                            if ($data_rata_rata['kecepatan_angin'] > 300) {
                                echo "<span style='color: #d32f2f;'>Data Invalid (Perlu Kalibrasi)</span>"; 
                            } else {
                                echo round($data_rata_rata['kecepatan_angin'], 2) . " km/jam";
                            }
                            ?>
                        </span>
                        <?php if(isset($data_an['total_data'])): ?>
                            <span style="color: #666; font-size: 0.8rem;">(dari <?= $data_an['total_data'] ?> data)</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="table-container">
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
                                                <td><span style='background: #f3e5f5; color: #7b1fa2; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;'>".round($row['kecepatan'], 2)." km/jam</span></td>
                                                <td style='color: #666; font-size: 0.8rem;'>{$row['waktu']}</td>
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

    <script>
        // Mobile menu functionality
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }

        menuToggle.addEventListener('click', toggleMenu);
        sidebarOverlay.addEventListener('click', toggleMenu);

        // Close menu when clicking on a nav item (mobile)
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    toggleMenu();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>