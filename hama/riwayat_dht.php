<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$limit = 40;
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = $koneksi->query("SELECT COUNT(*) AS total FROM dht_data WHERE DATE(waktu) = '$tanggal'");
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$sql = "SELECT * FROM dht_data WHERE DATE(waktu) = '$tanggal' ORDER BY waktu DESC LIMIT $limit OFFSET $offset";
$result = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Suhu & Kelembaban</title>
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
            padding: 16px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            font-size: 1.5rem;
            color: var(--primary-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .date-filter {
            display: flex;
            flex-direction: column;
            gap: 12px;
            background-color: white;
            padding: 1.25rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            width: 100%;
        }
        
        .date-filter label {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.95rem;
        }
        
        .date-filter input {
            padding: 0.75rem;
            border: 2px solid var(--primary-lighter);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .date-filter input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .date-filter button, .back-button {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
        
        .date-filter button:hover, .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
        }
        
        .back-button {
            margin-bottom: 1.5rem;
            align-self: flex-start;
            font-size: 0.95rem;
        }
        
        /* Sensor Card */
        .sensor-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .sensor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        
        .card-header {
            padding: 1.5rem 1.25rem;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
        }
        
        .card-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.3rem;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        /* Compact Table */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin: 1rem 0;
        }
        
        .compact-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            min-width: 500px;
            background: white;
        }
        
        .compact-table thead th {
            background-color: var(--primary-lightest);
            color: var(--primary-dark);
            font-weight: 600;
            padding: 1rem 0.75rem;
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
            padding: 0.875rem 0.75rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .compact-table tr:last-child td {
            border-bottom: none;
        }
        
        .temp-value {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #c62828;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
            border-left: 3px solid #f44336;
        }
        
        .humidity-value {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1565c0;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
            border-left: 3px solid #2196f3;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 1.5rem;
        }
        
        .pagination a, .pagination strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 0.75rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .pagination a {
            color: var(--primary);
            border: 2px solid var(--primary-lighter);
            background-color: white;
        }
        
        .pagination a:hover {
            background-color: var(--primary-lightest);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .pagination strong {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: 2px solid var(--primary);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
        
        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #78909C;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 1rem 0;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-lighter);
            opacity: 0.7;
        }
        
        /* Stats Info */
        .stats-info {
            background: linear-gradient(135deg, var(--primary-lightest), #e8f5e9);
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
        }
        
        .stats-info p {
            margin: 0;
            font-weight: 500;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stats-info i {
            color: var(--primary);
        }
        
        /* Sensor Indicators */
        .sensor-indicators {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .temp-indicator {
            background: #ffebee;
            color: #c62828;
            border-left: 3px solid #f44336;
        }
        
        .humidity-indicator {
            background: #e3f2fd;
            color: #1565c0;
            border-left: 3px solid #2196f3;
        }
        
        /* Responsive Breakpoints */
        @media (min-width: 768px) {
            body {
                padding: 20px;
            }
            
            .header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .date-filter {
                flex-direction: row;
                align-items: center;
                width: auto;
                gap: 15px;
            }
            
            .date-filter button {
                white-space: nowrap;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .card-header {
                padding: 1.5rem 2rem;
            }
            
            .card-body {
                padding: 2rem;
            }
            
            .compact-table {
                font-size: 0.9rem;
            }
            
            .compact-table thead th {
                padding: 1.25rem 1rem;
            }
            
            .compact-table td {
                padding: 1rem;
            }
        }
        
        @media (min-width: 1024px) {
            .container {
                padding: 0 20px;
            }
            
            .date-filter {
                padding: 1rem 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            
            .page-title h1 {
                font-size: 1.3rem;
            }
            
            .card-header {
                padding: 1.25rem 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .compact-table {
                font-size: 0.8rem;
                min-width: 450px;
            }
            
            .compact-table thead th {
                padding: 0.875rem 0.5rem;
            }
            
            .compact-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .temp-value, .humidity-value {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }
            
            .pagination a, .pagination strong {
                min-width: 36px;
                height: 36px;
                font-size: 0.85rem;
            }
            
            .stats-info {
                padding: 0.875rem 1rem;
            }
            
            .stats-info p {
                font-size: 0.9rem;
            }
            
            .sensor-indicators {
                gap: 0.5rem;
            }
            
            .indicator {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
        
        /* Print Styles */
        @media print {
            .back-button, .date-filter {
                display: none;
            }
            
            .sensor-card {
                box-shadow: none;
                border: 1px solid #ddd;
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
                <h1><i class="fas fa-history"></i> Riwayat Sensor</h1>
            </div>
            
            <form method="get" class="date-filter">
                <label for="tanggal">Pilih Tanggal:</label>
                <input type="date" name="tanggal" value="<?= $tanggal ?>" required>
                <button type="submit">
                    <i class="fas fa-filter"></i>
                    <span>Tampilkan</span>
                </button>
            </form>
        </div>
        
        <div class="sensor-card">
            <div class="card-header">
                <h2><i class="fas fa-thermometer-half"></i> Data Suhu & Kelembaban (<?= $tanggal ?>)</h2>
            </div>
            <div class="card-body">
                <?php if ($total > 0): ?>
                    <div class="stats-info">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Menampilkan <?= $total ?> data sensor untuk tanggal <?= $tanggal ?>
                        </p>
                    </div>
                    
                    <div class="sensor-indicators">
                        <div class="indicator temp-indicator">
                            <i class="fas fa-fire"></i>
                            <span>Suhu (°C)</span>
                        </div>
                        <div class="indicator humidity-indicator">
                            <i class="fas fa-tint"></i>
                            <span>Kelembaban (%)</span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="compact-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Suhu</th>
                                    <th>Kelembaban</th>
                                    <th>Waktu Pencatatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1;
                                while($row = $result->fetch_assoc()) {
                                    $suhu = round($row['suhu'], 1);
                                    $kelembaban = round($row['kelembaban'], 1);
                                    echo "<tr>
                                        <td><strong>$no</strong></td>
                                        <td><span class='temp-value'>$suhu °C</span></td>
                                        <td><span class='humidity-value'>$kelembaban %</span></td>
                                        <td style='color: #666;'>{$row['waktu']}</td>
                                    </tr>";
                                    $no++;
                                } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-thermometer-half"></i>
                        <h3 style="color: #78909C; margin-bottom: 0.5rem;">Tidak Ada Data</h3>
                        <p>Tidak ada data suhu dan kelembaban untuk tanggal <?= $tanggal ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php 
                    // Previous page link
                    if ($page > 1) {
                        $prev_link = "?tanggal=$tanggal&page=" . ($page - 1);
                        echo "<a href='$prev_link' title='Halaman Sebelumnya'><i class='fas fa-chevron-left'></i></a>";
                    }
                    
                    // Page numbers
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $link = "?tanggal=$tanggal&page=$i";
                        echo $i == $page ? "<strong>$i</strong>" : "<a href='$link'>$i</a>";
                    }
                    
                    // Next page link
                    if ($page < $total_pages) {
                        $next_link = "?tanggal=$tanggal&page=" . ($page + 1);
                        echo "<a href='$next_link' title='Halaman Selanjutnya'><i class='fas fa-chevron-right'></i></a>";
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Enhanced mobile experience
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on date input for better mobile UX
            const dateInput = document.querySelector('input[type="date"]');
            if (window.innerWidth < 768) {
                dateInput.focus();
            }
            
            // Add loading state to form submission
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>';
                button.disabled = true;
                
                // Revert after 3 seconds (fallback)
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 3000);
            });
            
            // Improve touch experience for pagination
            const paginationLinks = document.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                link.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            });
            
            // Add temperature effect to table rows on hover/touch
            const tableRows = document.querySelectorAll('.compact-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    const tempValue = this.querySelector('.temp-value');
                    if (tempValue) {
                        tempValue.style.transform = 'scale(1.05)';
                        tempValue.style.transition = 'transform 0.2s ease';
                    }
                });
                
                row.addEventListener('mouseleave', function() {
                    const tempValue = this.querySelector('.temp-value');
                    if (tempValue) {
                        tempValue.style.transform = 'scale(1)';
                    }
                });
            });
        });
        
        // Handle orientation change
        window.addEventListener('orientationchange', function() {
            // Small delay to ensure layout recalculation
            setTimeout(() => {
                window.scrollTo(0, 0);
            }, 100);
        });
    </script>
</body>
</html>