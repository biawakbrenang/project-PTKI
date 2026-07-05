<?php
/**
 * ECO-LEARNING - Lecturer Student Attendance Manager
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);

$dosen_id = $_SESSION['dosen_id'] ?? 0;
$kelas_id = sanitizeInput($_GET['kelas_id'] ?? '');

// Handle manual recording submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_attendance') {
    $attendance_data = $_POST['attendance'] ?? [];
    $pertemuan_num = sanitizeInput($_POST['pertemuan_num'] ?? 1);
    
    try {
        // Record each attendance in database
        foreach($attendance_data as $krs_id => $status) {
            // First check if already exists
            $stmtCheck = $pdo->prepare("SELECT id FROM absensi WHERE krs_id = :krs AND pertemuan = :pertemuan");
            $stmtCheck->execute(['krs' => $krs_id, 'pertemuan' => $pertemuan_num]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists) {
                $stmtUpdate = $pdo->prepare("UPDATE absensi SET status = :status, keterangan = :ket, tanggal = CURDATE() WHERE id = :id");
                $stmtUpdate->execute(['status' => $status, 'ket' => ucfirst($status), 'id' => $exists]);
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO absensi (krs_id, pertemuan, tanggal, status, keterangan) VALUES (:krs, :pertemuan, CURDATE(), :status, :ket)");
                $stmtInsert->execute(['krs' => $krs_id, 'pertemuan' => $pertemuan_num, 'status' => $status, 'ket' => ucfirst($status)]);
            }
        }
        $msg = "Presensi Sesi Pertemuan $pertemuan_num Berhasil Disimpan!";
    } catch (PDOException $e) {
        $msg = "Gagal Menyimpan Presensi: " . $e->getMessage();
    }
}

// Fetch all lecturer's classes for selection dropdown
$stmtClasses = $pdo->prepare("
    SELECT k.id, k.kode_kelas, m.nama_matkul 
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.dosen_id = :dosen
");
$stmtClasses->execute(['dosen' => $dosen_id]);
$my_classes = $stmtClasses->fetchAll();

// Fetch students registered in current selected class
$students = [];
$class_details = null;
if (!empty($kelas_id)) {
    // Get class detail
    $stmtDetail = $pdo->prepare("
        SELECT k.*, m.nama_matkul, m.kode_matkul 
        FROM kelas k
        JOIN matakuliah m ON k.matakuliah_id = m.id
        WHERE k.id = :id AND k.dosen_id = :dosen
    ");
    $stmtDetail->execute(['id' => $kelas_id, 'dosen' => $dosen_id]);
    $class_details = $stmtDetail->fetch();

    if ($class_details) {
        // Fetch students via KRS
        $stmtStudents = $pdo->prepare("
            SELECT krs.id AS krs_id, u.nama_lengkap, m.nim, krs.grade
            FROM krs
            JOIN mahasiswa m ON krs.mahasiswa_id = m.id
            JOIN users u ON m.user_id = u.id
            WHERE krs.kelas_id = :kelas_id
            ORDER BY m.nim ASC
        ");
        $stmtStudents->execute(['kelas_id' => $kelas_id]);
        $students = $stmtStudents->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Mahasiswa - ECO-LEARNING</title>
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
                    <h1 class="page-title">Presensi Mahasiswa</h1>
                    <p class="page-subtitle">Rekam kehadiran mahasiswa per sesi pertemuan secara manual.</p>
                </div>
            </div>

            <!-- Toast Alert message -->
            <?php if (!empty($msg)): ?>
                <div class="alert-success">
                    ✓ <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="split-grid">
                <!-- Class Selection Column -->
                <div class="card space-y-6">
                    <div>
                        <h3 class="card-title mb-3">Pilih Sesi Kelas</h3>
                        <form action="" method="GET" class="space-y-4">
                            <div>
                                <label class="form-label mb-1">Kelas Aktif Anda</label>
                                <select name="kelas_id" required onchange="this.form.submit()" class="form-input">
                                    <option value="">-- Pilih Kelas Aktif --</option>
                                    <?php foreach($my_classes as $myc): ?>
                                        <option value="<?= $myc['id'] ?>" <?= $kelas_id == $myc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($myc['kode_kelas']) ?> - <?= htmlspecialchars($myc['nama_matkul']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students attendance sheet -->
                <div class="card col-span-2">
                    <?php if (empty($kelas_id)): ?>
                        <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-center space-y-3">
                            <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                            <span class="text-xs font-semibold">Silakan pilih salah satu kelas di panel kiri untuk memuat daftar mahasiswa.</span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="card-title"><?= htmlspecialchars($class_details['kode_kelas']) ?> - <?= htmlspecialchars($class_details['nama_matkul']) ?></h3>
                                <p class="text-[10px] text-slate-400 font-semibold font-mono mt-0.5">Lembar Presensi Sesi Berlangsung</p>
                            </div>
                        </div>

                        <form action="" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="record_attendance">
                            
                            <div class="form-inline-row">
                                <label class="form-label label-inline">Pertemuan Ke</label>
                                <select name="pertemuan_num" required class="form-input form-input-sm">
                                    <?php for($i=1; $i<=16; $i++): ?>
                                        <option value="<?= $i ?>">Pertemuan <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="overflow-y-auto max-h-[400px]">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr>
                                            <th class="table-header">NPM</th>
                                            <th class="table-header">Nama Lengkap</th>
                                            <th class="table-header text-center">Status Presensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($students as $st): ?>
                                            <tr class="table-row-hover">
                                                <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($st['nim']) ?></td>
                                                <td class="table-cell font-medium"><?= htmlspecialchars($st['nama_lengkap']) ?></td>
                                                <td class="table-cell">
                                                    <!-- Hadir, Sakit, Izin, Alpa Radio Group -->
                                                    <div class="radio-attendance-group">
                                                        <label class="radio-attendance-label label-success">
                                                            <input type="radio" name="attendance[<?= $st['krs_id'] ?>]" value="hadir" checked class="radio-attendance-input">
                                                            <span>H</span>
                                                        </label>
                                                        <label class="radio-attendance-label label-info">
                                                            <input type="radio" name="attendance[<?= $st['krs_id'] ?>]" value="sakit" class="radio-attendance-input">
                                                            <span>S</span>
                                                        </label>
                                                        <label class="radio-attendance-label label-warning">
                                                            <input type="radio" name="attendance[<?= $st['krs_id'] ?>]" value="izin" class="radio-attendance-input">
                                                            <span>I</span>
                                                        </label>
                                                        <label class="radio-attendance-label label-danger">
                                                            <input type="radio" name="attendance[<?= $st['krs_id'] ?>]" value="alfa" class="radio-attendance-input">
                                                            <span>A</span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-footer-action">
                                <button type="submit" class="btn-primary btn-md">
                                    Simpan Rekap Presensi Sesi
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
