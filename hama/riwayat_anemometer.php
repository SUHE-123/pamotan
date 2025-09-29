<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$limit = 40;
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = $koneksi->query("SELECT COUNT(*) AS total FROM anemometer WHERE DATE(waktu) = '$tanggal'");
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$sql = "SELECT * FROM anemometer WHERE DATE(waktu) = '$tanggal' ORDER BY waktu DESC LIMIT $limit OFFSET $offset";
$result = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Kecepatan Angin</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .date-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .date-filter label {
            font-weight: 500;
            color: var(--primary);
        }
        
        .date-filter input {
            padding: 0.5rem;
            border: 1px solid var(--primary-lighter);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        
        .date-filter button, .back-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .date-filter button:hover, .back-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            margin-bottom: 1.5rem;
        }
        
        /* Sensor Card */
        .sensor-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.3rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Compact Table */
        .compact-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
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
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .compact-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 5px;
        }
        
        .pagination a, .pagination strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
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
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .date-filter {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
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
    <div class="container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Dashboard</span>
        </a>
        
        <div class="header">
            <div class="page-title">
                <h1><i class="fas fa-wind"></i> Riwayat Kecepatan Angin</h1>
            </div>
            
            <form method="get" class="date-filter">
                <label for="tanggal">Pilih Tanggal:</label>
                <input type="date" name="tanggal" value="<?= $tanggal ?>" required>
                <button type="submit">Tampilkan</button>
            </form>
        </div>
        
        <div class="sensor-card">
            <div class="card-header">
                <h2><i class="fas fa-fan"></i> Data Kecepatan Angin (<?= $tanggal ?>)</h2>
            </div>
            <div class="card-body">
                <?php if ($total > 0): ?>
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kecepatan Angin (km/jam)</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1;
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>$no</td>
                                <td>{$row['kecepatan']}</td>
                                <td>{$row['waktu']}</td>
                            </tr>";
                            $no++;
                        } ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #78909C; font-style: italic;">
                    Tidak ada data untuk tanggal <?= $tanggal ?>
                </div>
                <?php endif; ?>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++) {
                        $link = "?tanggal=$tanggal&page=$i";
                        echo $i == $page ? "<strong>$i</strong>" : "<a href='$link'>$i</a>";
                    } ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>