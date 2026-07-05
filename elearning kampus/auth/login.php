<?php
/**
 * ECO-LEARNING - Secure Login Portal
 */
require_once __DIR__ . '/../config.php';

$error = '';

// Simple inline CAPTCHA logic using session
if (!isset($_SESSION['captcha_num'])) {
    $_SESSION['captcha_num'] = rand(1000, 9999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? '');
    $captcha = sanitizeInput($_POST['captcha'] ?? '');

    if ($captcha !== (string)$_SESSION['captcha_num']) {
        $error = 'Verifikasi CAPTCHA keamanan tidak cocok!';
        $_SESSION['captcha_num'] = rand(1000, 9999); // reset
    } else {
        // Query database
        $stmt = $pdo->prepare("
            SELECT * FROM users
            WHERE (username = :login_username OR email = :login_email)
              AND role = :role
              AND status = 'aktif'
            LIMIT 1
        ");
        $stmt->execute([
            'login_username' => $username,
            'login_email' => $username,
            'role' => $role
        ]);
        $user = $stmt->fetch();

        if ($user) {
            // Since passwords in seed are plain-text for easy local testing, we do a direct check.
            // If using hash, we would do: password_verify($password, $user['password'])
            if ($password === $user['password']) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                // Get specific profile details
                if ($user['role'] === 'mahasiswa') {
                    $stmtMhs = $pdo->prepare("SELECT id, nim, semester_saat_ini FROM mahasiswa WHERE user_id = :user_id");
                    $stmtMhs->execute(['user_id' => $user['id']]);
                    $mhs = $stmtMhs->fetch();
                    if ($mhs) {
                        $_SESSION['mahasiswa_id'] = $mhs['id'];
                        $_SESSION['nim'] = $mhs['nim'];
                        $_SESSION['semester'] = $mhs['semester_saat_ini'];
                    }
                    header("Location: ../mahasiswa/dashboard.php");
                } elseif ($user['role'] === 'dosen') {
                    $stmtDosen = $pdo->prepare("SELECT id, nip FROM dosen WHERE user_id = :user_id");
                    $stmtDosen->execute(['user_id' => $user['id']]);
                    $dsn = $stmtDosen->fetch();
                    if ($dsn) {
                        $_SESSION['dosen_id'] = $dsn['id'];
                        $_SESSION['nip'] = $dsn['nip'];
                    }
                    header("Location: ../dosen/dashboard.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                }
                exit();
            } else {
                $error = 'Username atau password yang Anda masukkan salah.';
            }
        } else {
            $error = 'Akun tidak terdaftar atau dalam status tidak aktif.';
        }
        
        // Refresh CAPTCHA
        $_SESSION['captcha_num'] = rand(1000, 9999);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academic E-Learning System</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body class="login-body">
    <div class="login-card">
        <h1 class="login-title">Academic E-Learning System</h1>
        <p class="login-subtitle">Simple university portal login</p>

        <?php if (!empty($error)): ?>
            <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="form-stack">
            <div>
                <label class="form-label">Email / Username</label>
                <input type="text" name="username" required placeholder="25211101" class="form-input">
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" required placeholder="Enter password" class="form-input">
            </div>
            <div>
                <label class="form-label">Role</label>
                <select name="role" required class="form-input">
                    <option value="">Select role</option>
                    <option value="admin">Admin</option>
                    <option value="dosen">Dosen</option>
                    <option value="mahasiswa">Mahasiswa</option>
                </select>
            </div>
            <div>
                <label class="form-label">Security Code: <?= $_SESSION['captcha_num'] ?></label>
                <input type="text" name="captcha" required maxlength="4" placeholder="Type the code" class="form-input">
            </div>
            <button type="submit" class="btn-primary btn-block">Login</button>
        </form>
    </div>
</body>
</html>
