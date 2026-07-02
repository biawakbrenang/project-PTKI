<?php
/**
 * Login Page
 * Sistem E-Learning Akademik
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/check_auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
  redirectByRole();
}

$error = '';
$username = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  
  // Validate input
  if (empty($username) || empty($password)) {
    $error = 'Username dan password harus diisi';
  } elseif (!preg_match('/^\d{8}$/', $username)) {
    $error = 'Username harus terdiri dari 8 digit angka';
  } else {
    // Get user from database
    $user = getUserByUsername($conn, $username);
    
    // Check password (simple comparison - tidak pakai bcrypt)
    if ($user && $user['password'] === $password) {
      // Password correct
      if ($user['status'] === 'nonaktif') {
        $error = 'Akun Anda telah dinonaktifkan. Hubungi administrator.';
      } else {
        // Set session
        setUserSession($user);
        
        // Update last login
        updateRecord($conn, 'users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']], 'i');
        
        // Log activity
        logActivity($conn, $user['id'], 'LOGIN', 'User login berhasil');
        
        // Redirect to dashboard
        redirectByRole();
      }
    } else {
      $error = 'Username atau password salah';
      // Log failed login attempt
      logActivity($conn, null, 'LOGIN_FAILED', "Username: $username");
    }
  }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistem E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="login-header">
        <div class="login-logo">EL</div>
        <h1 class="login-title">E-Learning Akademik</h1>
        <p class="login-subtitle">Sistem Informasi Pembelajaran Perkuliahan</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <span><?php echo htmlspecialchars($error); ?></span>
          <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
      <?php endif; ?>

      <form method="POST" class="login-form" id="loginForm">
        <div class="form-group">
          <label for="username">Username (8 Digit)</label>
          <input 
            type="text" 
            id="username" 
            name="username" 
            class="form-control" 
            placeholder="Contoh: 25211105"
            value="<?php echo htmlspecialchars($username); ?>"
            maxlength="8"
            pattern="\d{8}"
            required
          >
    
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-control" 
            placeholder="Masukkan password Anda"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary login-btn">
          Masuk
        </button>
      </form>

      <div class="login-footer">
        <p>Sistem E-Learning Akademik &copy; 2025</p>
        <p>Hubungi administrator jika ada masalah login</p>
      </div>
    </div>
  </div>

  <script src="/elearning_unas/public/js/script.js"></script>
  <script>
    // Validate username format on input
    document.getElementById('username').addEventListener('input', function(e) {
      this.value = this.value.replace(/\D/g, '').substring(0, 8);
    });

    // Form submission validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;

      if (username.length !== 8) {
        e.preventDefault();
        alert('Username harus terdiri dari 8 digit angka');
        return false;
      }

      if (password.length < 4) {
        e.preventDefault();
        alert('Password terlalu pendek');
        return false;
      }
    });
  </script>
</body>
</html>
