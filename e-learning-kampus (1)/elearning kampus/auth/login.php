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
    $captcha = sanitizeInput($_POST['captcha'] ?? '');

    if ($captcha !== (string)$_SESSION['captcha_num']) {
        $error = 'Verifikasi CAPTCHA keamanan tidak cocok!';
        $_SESSION['captcha_num'] = rand(1000, 9999); // reset
    } else {
        // Query database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND status = 'aktif' LIMIT 1");
        $stmt->execute(['username' => $username]);
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
    <title>Masuk - ECO-LEARNING Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 relative overflow-hidden">
    <!-- Background Gradient Accents -->
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-emerald-100/30 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-green-100/20 blur-3xl"></div>

    <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-8 shadow-xl relative z-10">
        <!-- Logo Brand -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 mb-4 shadow-sm">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-2xl font-display font-bold text-slate-900 tracking-tight text-center uppercase">
                ECO-LEARNING
            </h1>
            <p class="text-slate-500 text-xs mt-1 text-center font-medium">
                Portal Akademik & Manajemen Pembelajaran Mandiri
            </p>
        </div>

        <!-- Alert Error -->
        <?php if (!empty($error)): ?>
            <div class="alert-danger mb-5">
                ⚠ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="" method="POST" class="form-stack">
            <div>
                <label class="form-label">Username / NPM / NIDN</label>
                <input 
                    type="text" 
                    name="username" 
                    required 
                    placeholder="Contoh: 25211101 atau 35112001"
                    class="form-input"
                >
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="form-label mb-0">Password</label>
                    <a href="#" onclick="alert('Hubungi staf IT LPTIK Unas untuk me-reset sandi Anda.')" class="text-xs text-emerald-600 hover:underline font-semibold">Lupa Password?</a>
                </div>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                    class="form-input"
                >
            </div>

            <!-- CAPTCHA Security -->
            <div>
                <label class="form-label">Verifikasi Keamanan (CAPTCHA)</label>
                <div class="flex gap-3">
                    <div class="bg-emerald-50 border border-emerald-200 px-4 py-2.5 rounded-2xl flex items-center justify-center select-none font-mono text-lg font-bold tracking-widest text-emerald-700 line-through skew-x-12 relative overflow-hidden shadow-sm">
                        <?= $_SESSION['captcha_num'] ?>
                    </div>
                    <input 
                        type="text" 
                        name="captcha" 
                        required 
                        placeholder="Ketik captcha"
                        maxlength="4"
                        class="form-input flex-1 py-2.5 text-center tracking-widest uppercase"
                    >
                </div>
            </div>

            <button 
                type="submit" 
                class="btn-primary btn-block btn-lg mt-2"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Masuk ke Sistem
            </button>
        </form>

        <!-- Quick Demo Credentials Block -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-center text-[10px] uppercase tracking-widest text-slate-400 mb-3 font-semibold font-mono">Bypass Login Cepat Demo</p>
            <div class="demo-buttons-grid">
                <button onclick="fillDemo('35112001', 'Rendra996', '<?= $_SESSION['captcha_num'] ?>')" class="btn-demo">
                    Dr. Rendra<br><span class="btn-demo-sub">(Dosen)</span>
                </button>
                <button onclick="fillDemo('25211101', 'Ahmad689', '<?= $_SESSION['captcha_num'] ?>')" class="btn-demo">
                    Ahmad P.<br><span class="btn-demo-sub">(Mhs SI)</span>
                </button>
                <button onclick="fillDemo('15123416', 'Admin658', '<?= $_SESSION['captcha_num'] ?>')" class="btn-demo">
                    Admin<br><span class="btn-demo-sub">(Kekuasaan)</span>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
