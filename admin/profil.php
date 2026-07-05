<?php
/**
 * ECO-LEARNING - Administrator Profile Management
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

$msg = '';
$err = '';

$user_id = $_SESSION['user_id'] ?? 0;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = sanitizeInput($_POST['nama_lengkap'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $new_password = sanitizeInput($_POST['new_password'] ?? '');

    if (empty($nama_lengkap) || empty($email)) {
        $err = "Nama Lengkap dan Email tidak boleh kosong!";
    } else {
        try {
            if (!empty($new_password)) {
                $stmtUpdate = $pdo->prepare("
                    UPDATE users 
                    SET nama_lengkap = :nama, email = :email, password = :pass
                    WHERE id = :id
                ");
                $stmtUpdate->execute([
                    'nama' => $nama_lengkap,
                    'email' => $email,
                    'pass' => $new_password,
                    'id' => $user_id
                ]);
            } else {
                $stmtUpdate = $pdo->prepare("
                    UPDATE users 
                    SET nama_lengkap = :nama, email = :email
                    WHERE id = :id
                ");
                $stmtUpdate->execute([
                    'nama' => $nama_lengkap,
                    'email' => $email,
                    'id' => $user_id
                ]);
            }

            // Update active session variables
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['email'] = $email;

            $msg = "Profil administrator berhasil diperbarui!";
        } catch (PDOException $e) {
            $err = "Gagal menyimpan perubahan: " . $e->getMessage();
        }
    }
}

// Fetch latest user details
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmtUser->execute(['id' => $user_id]);
$current_user = $stmtUser->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Panel Admin ECO-LEARNING</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body class="bg-slate-50">

    <!-- Sidebar Layout -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main-content">
        <?php include __DIR__ . '/../components/header.php'; ?>

        <main class="page-container">
            
            <div class="page-header">
                <div>
                    <h1 class="page-title">Profil Administrator</h1>
                    <p class="page-subtitle">Kelola kredensial login, nama lengkap, dan data autentikasi sistem Anda.</p>
                </div>
            </div>

            <!-- Messages Toast -->
            <?php if (!empty($msg)): ?>
                <div class="alert-success">
                    ✓ <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($err)): ?>
                <div class="alert-danger">
                    ⚠ <?= htmlspecialchars($err) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Information Card (Left) -->
                <div class="card flex flex-col items-center text-center">
                    <div class="w-24 h-24 rounded-full bg-emerald-100 border-4 border-emerald-50 flex items-center justify-center text-emerald-700 font-display font-bold text-4xl shadow-md uppercase mb-4">
                        <?= substr($current_user['nama_lengkap'] ?? 'A', 0, 1) ?>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 font-display"><?= htmlspecialchars($current_user['nama_lengkap'] ?? 'Administrator') ?></h2>
                    <span class="px-3 py-1 bg-red-50 border border-red-100 text-red-600 font-mono text-[10px] font-bold tracking-wider rounded-full uppercase mt-1">
                        Role: <?= htmlspecialchars($current_user['role'] ?? 'admin') ?>
                    </span>

                    <div class="w-full border-t border-slate-100 mt-6 pt-6 space-y-4 text-left text-xs text-slate-600 font-medium">
                        <div class="flex justify-between py-1.5 border-b border-slate-50">
                            <span class="text-slate-400">Username</span>
                            <span class="font-mono text-slate-900 font-bold"><?= htmlspecialchars($current_user['username'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50">
                            <span class="text-slate-400">Email Utama</span>
                            <span class="text-slate-900"><?= htmlspecialchars($current_user['email'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50">
                            <span class="text-slate-400">Status Akun</span>
                            <span class="text-emerald-600 font-bold">✓ <?= htmlspecialchars($current_user['status'] ?? 'aktif') ?></span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-400">Terdaftar Sejak</span>
                            <span class="text-slate-900 font-mono"><?= isset($current_user['created_at']) ? date('d-m-Y', strtotime($current_user['created_at'])) : '-' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Profile Update Form (Right) -->
                <div class="card lg:col-span-2">
                    <h3 class="card-title mb-4">Perbarui Informasi Profil</h3>
                    <form action="" method="POST" class="form-stack">
                        
                        <div>
                            <label class="form-label mb-1.5">Nama Lengkap Administrator</label>
                            <input 
                                type="text" 
                                name="nama_lengkap" 
                                required 
                                value="<?= htmlspecialchars($current_user['nama_lengkap'] ?? '') ?>" 
                                class="form-input"
                            >
                        </div>

                        <div>
                            <label class="form-label mb-1.5">Alamat Email Resmi</label>
                            <input 
                                type="email" 
                                name="email" 
                                required 
                                value="<?= htmlspecialchars($current_user['email'] ?? '') ?>" 
                                class="form-input"
                            >
                        </div>

                        <div class="border-t border-slate-100 pt-4 mt-6">
                            <div class="mb-3">
                                <h4 class="font-display font-bold text-slate-800 text-xs uppercase">Keamanan & Kata Sandi</h4>
                                <p class="text-[10px] text-slate-400">Biarkan bidang di bawah ini kosong jika Anda tidak ingin mengubah kata sandi Anda.</p>
                            </div>

                            <div>
                                <label class="form-label mb-1.5">Kata Sandi Baru</label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    placeholder="Ketik password baru jika ingin mengubah" 
                                    class="form-input font-mono"
                                >
                            </div>
                        </div>

                        <div class="form-footer-action">
                            <button type="submit" class="btn-primary btn-md">
                                Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
