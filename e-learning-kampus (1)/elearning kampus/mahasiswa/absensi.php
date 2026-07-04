<?php
/**
 * ECO-LEARNING - Mahasiswa Token Presensi Validator
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
$msg = '';
$err = '';

// Handle Token Presensi checking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_token') {
    $token_input = sanitizeInput($_POST['token_input'] ?? '');
    $kelas_id = sanitizeInput($_POST['kelas_id'] ?? '');
    $pertemuan_num = sanitizeInput($_POST['pertemuan_num'] ?? 1);

    // Verify session token matches
    $session_token = $_SESSION['absensi_token'] ?? '';
    
    if (empty($session_token) || $token_input !== (string)$session_token) {
        $err = "Token presensi salah atau telah kedaluwarsa. Silakan tanyakan ulang kepada dosen pengampu.";
    } else {
        try {
            // Find Student KRS ID for this class
            $stmtKrs = $pdo->prepare("SELECT id FROM krs WHERE mahasiswa_id = :mhs AND kelas_id = :kelas LIMIT 1");
            $stmtKrs->execute(['mhs' => $mhs_id, 'kelas' => $kelas_id]);
            $krs_id = $stmtKrs->fetchColumn();

            if (!$krs_id) {
                $err = "Anda tidak terdaftar secara resmi di kelas ini dalam KRS.";
            } else {
                // Check if already registered attendance
                $stmtCheck = $pdo->prepare("SELECT id FROM absensi WHERE krs_id = :krs AND pertemuan = :pertemuan");
                $stmtCheck->execute(['krs' => $krs_id, 'pertemuan' => $pertemuan_num]);
                $exists = $stmtCheck->fetchColumn();

                if ($exists) {
                    $msg = "Anda sudah terdaftar Hadir untuk Sesi Pertemuan $pertemuan_num sebelumnya!";
                } else {
                    $stmtInsert = $pdo->prepare("INSERT INTO absensi (krs_id, pertemuan, tanggal, status, keterangan) VALUES (:krs, :pertemuan, CURDATE(), 'hadir', 'Hadir via Token Mandiri')");
                    $stmtInsert->execute(['krs' => $krs_id, 'pertemuan' => $pertemuan_num]);
                    $msg = "Presensi Anda Berhasil Dicatat! Anda dinyatakan HADIR pada Pertemuan $pertemuan_num.";
                }
            }
        } catch (PDOException $e) {
            $err = "Kegagalan Sistem Presensi: " . $e->getMessage();
        }
    }
}

// Fetch current registered courses in KRS for selection
$stmtKrs = $pdo->prepare("
    SELECT krs.kelas_id, k.kode_kelas, m.nama_matkul
    FROM krs
    JOIN kelas k ON krs.kelas_id = k.id
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE krs.mahasiswa_id = :mhs
");
$stmtKrs->execute(['mhs' => $mhs_id]);
$my_courses = $stmtKrs->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Presensi Token - ECO-LEARNING</title>
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
                    <h1 class="page-title">Isi Presensi Token Mandiri</h1>
                    <p class="page-subtitle">Masukkan kode 6-digit dari layar proyektor atau papan tulis kelas dosen Anda.</p>
                </div>
            </div>

            <!-- Toast feedback messaging -->
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

            <div class="two-col-grid">
                <!-- Submit Form Panel -->
                <div class="card">
                    <h3 class="card-title">Form Verifikasi Presensi</h3>
                    <form action="" method="POST" class="form-stack">
                        <input type="hidden" name="action" value="submit_token">

                        <div>
                            <label class="form-label">Pilih Matakuliah Sesi</label>
                            <select name="kelas_id" required class="form-input form-input-semibold">
                                <option value="">-- Pilih Matakuliah --</option>
                                <?php foreach($my_courses as $mc): ?>
                                    <option value="<?= $mc['kelas_id'] ?>"><?= htmlspecialchars($mc['kode_kelas']) ?> - <?= htmlspecialchars($mc['nama_matkul']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div>
                                <label class="form-label">Pertemuan Ke</label>
                                <select name="pertemuan_num" required class="form-input">
                                    <?php for($i=1; $i<=16; $i++): ?>
                                        <option value="<?= $i ?>">Pertemuan <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Kode Token (6 Digit)</label>
                                <input type="text" name="token_input" required placeholder="Ketik 6 digit token" maxlength="6" class="form-input token-input">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary btn-block">
                            Kirim Kode Presensi
                        </button>
                    </form>
                </div>

                <!-- Guidance Info Box -->
                <div class="card">
                    <h3 class="card-title">Aturan Kehadiran Mandiri</h3>
                    <ul class="guidance-list">
                        <li>Token presensi digenerate secara langsung oleh dosen pengampu ketika sesi kuliah luring dimulai.</li>
                        <li>Token memiliki batas kedaluwarsa waktu. Pengisian lewat jam kuliah dinyatakan terlambat/absen secara luring.</li>
                        <li>Segala kecurangan (berbagi token di luar grup kelas) terekam dalam log sistem LPTIK dan dapat berakibat nilai akhir dibatalkan.</li>
                    </ul>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
