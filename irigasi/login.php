<?php
session_start();
include 'api/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = md5($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
  $stmt->bind_param("ss", $username, $password);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $_SESSION['login'] = true;
    $_SESSION['username'] = $username;
    header('Location: dashboard.php');
    exit;
  } else {
    $error = "Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Sistem Irigasi Cerdas</title>
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
    }
    
    body {
      background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
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
    
    .login-container {
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      border: none;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .login-header {
      background: linear-gradient(90deg, var(--primary-green) 0%, var(--light-green) 100%);
      color: white;
      padding: 30px 20px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .login-header::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      z-index: 0;
    }
    
    .login-header-content {
      position: relative;
      z-index: 1;
    }
    
    .login-icon {
      font-size: 3rem;
      margin-bottom: 15px;
      display: block;
    }
    
    .login-body {
      padding: 30px;
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
    
    .btn-login {
      background: linear-gradient(45deg, var(--water-blue), var(--sky-blue));
      border: none;
      border-radius: 12px;
      padding: 12px;
      font-weight: 600;
      color: white;
      transition: all 0.3s;
      width: 100%;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(2, 136, 209, 0.3);
    }
    
    .btn-back {
      background: linear-gradient(45deg, var(--earth-brown), #a1887f);
      border: none;
      border-radius: 12px;
      padding: 10px 20px;
      font-weight: 600;
      color: white;
      transition: all 0.3s;
      width: 100%;
      margin-top: 15px;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(121, 85, 72, 0.3);
      color: white;
    }
    
    .alert-error {
      background: #ffebee;
      color: #c62828;
      border: 1px solid #ffcdd2;
      border-radius: 12px;
      padding: 12px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 500;
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
    
    .copyright {
      text-align: center;
      margin-top: 20px;
      color: var(--dark-green);
      font-size: 0.9rem;
    }
    
    .input-group {
      position: relative;
      margin-bottom: 20px;
    }
    
    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--light-green);
      z-index: 3;
    }
    
    .input-with-icon {
      padding-left: 45px;
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
  
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-header-content">
          <i class="fas fa-tint login-icon"></i>
          <h2 class="mb-0">Sistem Irigasi Cerdas</h2>
          <p class="mb-0 mt-2 opacity-90">Masuk ke Dashboard</p>
        </div>
      </div>
      
      <div class="login-body">
        <?php if ($error): ?>
          <div class="alert-error">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="input-group">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="username" name="username" class="form-control input-with-icon" placeholder="Masukkan username" required>
          </div>
          
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control input-with-icon" placeholder="Masukkan password" required>
          </div>
          
          <button type="submit" class="btn btn-login">
            <i class="fas fa-sign-in-alt me-2"></i>Masuk
          </button>
        </form>
        
        <a href="../index.php" class="btn btn-back">
          <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard Utama
        </a>
        
        <div class="copyright">
          &copy; 2025 Smart Agriculture
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>