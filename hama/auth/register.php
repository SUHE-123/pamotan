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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Pendeteksi Hama</title>
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
            --danger: #d32f2f;
            --danger-light: #fde8e8;
            --success: #1B5E20;
            --success-light: #e8f5e9;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 16px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Background decoration */
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
        
        .nature-decoration {
            position: absolute;
            z-index: 0;
            opacity: 0.1;
            pointer-events: none;
        }
        
        .leaf-1 {
            top: 10%;
            left: 5%;
            font-size: 80px;
            color: var(--primary);
            transform: rotate(-15deg);
        }
        
        .leaf-2 {
            bottom: 10%;
            right: 5%;
            font-size: 60px;
            color: var(--primary-light);
            transform: rotate(25deg);
        }
        
        .register-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .card-header {
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            z-index: 0;
        }
        
        .card-header-content {
            position: relative;
            z-index: 1;
        }
        
        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .card-header i {
            font-size: 1.4rem;
        }
        
        .card-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .card-body {
            padding: 2rem 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-dark);
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--primary-lighter);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
            background: white;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 2;
        }
        
        .input-with-icon .form-control {
            padding-left: 3rem;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .links-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--danger);
            background-color: var(--danger-light);
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 4px solid var(--danger);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .error-message i {
            font-size: 1.1rem;
        }
        
        .success-message {
            color: var(--success);
            background-color: var(--success-light);
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .success-message i {
            font-size: 1.1rem;
        }
        
        .success-message a {
            color: var(--success);
            font-weight: 600;
            text-decoration: underline;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #666;
        }
        
        .password-requirements {
            font-size: 0.75rem;
            color: #888;
            margin-top: 0.25rem;
        }
        
        /* Responsive Design */
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            
            .register-container {
                max-width: 100%;
            }
            
            .card-header {
                padding: 1.75rem 1.25rem;
            }
            
            .card-header h2 {
                font-size: 1.4rem;
                flex-direction: column;
                gap: 8px;
            }
            
            .card-body {
                padding: 1.75rem 1.25rem;
            }
            
            .form-control {
                padding: 0.75rem 0.875rem;
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            .input-with-icon .form-control {
                padding-left: 2.75rem;
            }
            
            .input-icon {
                left: 0.875rem;
            }
            
            .btn {
                padding: 0.75rem 1.25rem;
            }
            
            .leaf-1 {
                font-size: 60px;
                top: 5%;
                left: 2%;
            }
            
            .leaf-2 {
                font-size: 50px;
                bottom: 5%;
                right: 2%;
            }
        }
        
        @media (max-width: 360px) {
            .card-header {
                padding: 1.5rem 1rem;
            }
            
            .card-body {
                padding: 1.5rem 1rem;
            }
            
            .card-header h2 {
                font-size: 1.3rem;
            }
            
            .form-group {
                margin-bottom: 1.25rem;
            }
            
            .links-container {
                margin-top: 1.25rem;
            }
        }
        
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: flex-start;
            }
            
            .register-container {
                margin: 20px 0;
            }
            
            .card-header {
                padding: 1.25rem 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
        
        /* Loading state */
        .btn.loading {
            position: relative;
            color: transparent;
        }
        
        .btn.loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s ease infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .input-with-icon.password-field .form-control {
            padding-right: 3rem;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Background decorations -->
    <div class="nature-decoration leaf-1">
        <i class="fas fa-leaf"></i>
    </div>
    <div class="nature-decoration leaf-2">
        <i class="fas fa-seedling"></i>
    </div>
    
    <div class="register-container">
        <div class="register-card">
            <div class="card-header">
                <div class="card-header-content">
                    <h2>
                        <i class="fas fa-user-plus"></i>
                        <span>Daftar Akun Baru</span>
                    </h2>
                    <p>Sistem Pendeteksi Hama Pertanian</p>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_telp">Nomor Telepon</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" id="no_telp" name="no_telp" class="form-control" placeholder="Masukkan nomor telepon" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon password-field">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Buat password yang kuat" required minlength="6">
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-requirements">
                            Minimal 6 karakter
                        </div>
                    </div>
                    
                    <button type="submit" class="btn" id="registerBtn">
                        <span>Daftar Sekarang</span>
                    </button>
                    
                    <div class="links-container">
                        <a href="login.php" class="link">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Sudah punya akun? Masuk disini</span>
                        </a>
                        <a href="../../index.php" class="link">
                            <i class="fas fa-home"></i>
                            <span>Kembali ke Beranda Utama</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form functionality
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const registerBtn = document.getElementById('registerBtn');
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            // Password toggle functionality
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
            
            // Form submission with loading state
            registerForm.addEventListener('submit', function(e) {
                const btn = document.getElementById('registerBtn');
                const originalText = btn.innerHTML;
                
                // Add loading state
                btn.classList.add('loading');
                btn.disabled = true;
                
                // Simulate loading for better UX (remove in production)
                setTimeout(() => {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 2000);
            });

            // Auto-focus on first input
            document.getElementById('nama').focus();

            // Phone number formatting
            const phoneInput = document.getElementById('no_telp');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = value.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
                    value = value.substring(0, 14); // Limit to 12 digits + 2 dashes
                }
                e.target.value = value;
            });

            // Password strength indicator (basic)
            passwordInput.addEventListener('input', function(e) {
                const password = e.target.value;
                const strengthIndicator = document.querySelector('.password-strength');
                
                if (!strengthIndicator) {
                    const indicator = document.createElement('div');
                    indicator.className = 'password-strength';
                    e.target.parentNode.parentNode.appendChild(indicator);
                }
                
                const indicator = document.querySelector('.password-strength');
                let strength = 'Lemah';
                let color = '#d32f2f';
                
                if (password.length >= 8) {
                    strength = 'Sedang';
                    color = '#ff9800';
                }
                if (password.length >= 12) {
                    strength = 'Kuat';
                    color = '#4caf50';
                }
                
                indicator.innerHTML = `Kekuatan password: <span style="color: ${color}; font-weight: 600;">${strength}</span>`;
            });
        });

        // Prevent zoom on input focus in iOS
        document.addEventListener('touchstart', function() {}, {passive: true});
    </script>
</body>
</html>