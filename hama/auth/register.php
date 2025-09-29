<?php
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $no_telp = $_POST['no_telp'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = $koneksi->prepare("SELECT * FROM users WHERE nama = ?");
    $cek->bind_param("s", $nama);
    $cek->execute();
    $cek_result = $cek->get_result();

    if ($cek_result->num_rows > 0) {
        $error = "Nama sudah terdaftar!";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO users (nama, no_telp, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $no_telp, $password);
        if ($stmt->execute()) {
            $success = "Pendaftaran berhasil! Silakan <a href='login.php'>login</a>.";
        } else {
            $error = "Gagal mendaftar. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 450px;
        }
        
        .register-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            text-align: center;
        }
        
        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.3rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--primary-lighter);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .login-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .login-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .error-message {
            color: #d32f2f;
            background-color: #fde8e8;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .success-message {
            color: var(--primary-dark);
            background-color: var(--primary-lightest);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .success-message a {
            color: var(--primary-dark);
            font-weight: 600;
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Daftar Akun</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_telp">Nomor Telepon</label>
                        <input type="text" id="no_telp" name="no_telp" class="form-control" placeholder="Masukkan nomor telepon" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Buat password" required>
                    </div>
                    
                    <button type="submit" class="btn">Daftar Sekarang</button>
                    
                    <div class="text-center mt-3">
                        <span>Sudah punya akun?</span>
                        <a href="login.php" class="login-link">Masuk disini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>