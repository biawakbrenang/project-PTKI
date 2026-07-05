<?php
/**
 * ECO-LEARNING - Mahasiswa KRS (Kartu Rencana Studi)
 * Pick available classes offered in the current semester.
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
$semester = $_SESSION['semester'] ?? 1;
$msg = '';
$err = '';

// Handle enroll (ambil kelas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ambil_kelas') {
    $kelas_id = sanitizeInput($_POST['kelas_id'] ?? '');
    try {
        $stmtKelas = $pdo->prepare("SELECT * FROM kelas WHERE id = :id AND semester = :sem");
        $stmtKelas->execute(['id' => $kelas_id, 'sem' => $semester]);
        $kelas = $stmtKelas->fetch();

        if (!$kelas) {
            $err = "Kelas tidak ditemukan untuk semester ini.";
        } else {
            $stmtCheck = $pdo->prepare("SELECT id FROM krs WHERE mahasiswa_id = :mhs AND kelas_id = :kelas");
            $stmtCheck->execute(['mhs' => $mhs_id, 'kelas' => $kelas_id]);
            if ($stmtCheck->fetchColumn()) {
                $err = "Anda sudah mengambil kelas ini di KRS.";
            } elseif ($kelas['jumlah_mahasiswa'] >= $kelas['kapasitas']) {
                $err = "Kuota kelas sudah penuh.";
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO krs (mahasiswa_id, kelas_id, matakuliah_id, semester) VALUES (:mhs, :kelas, :matkul, :sem)");
                $stmtIns->execute(['mhs' => $mhs_id, 'kelas' => $kelas_id, 'matkul' => $kelas['matakuliah_id'], 'sem' => $semester]);
                $pdo->prepare("UPDATE kelas SET jumlah_mahasiswa = jumlah_mahasiswa + 1 WHERE id = :id")->execute(['id' => $kelas_id]);
                $msg = "Kelas berhasil ditambahkan ke KRS Anda!";
            }
        }
    } catch (PDOException $e) {
        $err = "Gagal Mengambil Kelas: " . $e->getMessage();
    }
}

// Handle drop (batal ambil)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batal_kelas') {
    $kelas_id = sanitizeInput($_POST['kelas_id'] ?? '');
    try {
        $stmtDel = $pdo->prepare("DELETE FROM krs WHERE mahasiswa_id = :mhs AND kelas_id = :kelas AND grade IS NULL");
        $stmtDel->execute(['mhs' => $mhs_id, 'kelas' => $kelas_id]);
        if ($stmtDel->rowCount() > 0) {
            $pdo->prepare("UPDATE kelas SET jumlah_mahasiswa = GREATEST(jumlah_mahasiswa - 1, 0) WHERE id = :id")->execute(['id' => $kelas_id]);
            $msg = "Kelas berhasil dibatalkan dari KRS Anda.";
        } else {
            $err = "Kelas tidak dapat dibatalkan (sudah memiliki nilai atau tidak terdaftar).";
        }
    } catch (PDOException $e) {
        $err = "Gagal Membatalkan Kelas: " . $e->getMessage();
    }
}

// Fetch all classes available for this semester with enrollment flag
$stmtAvail = $pdo->prepare("
    SELECT k.*, m.nama_matkul, m.kode_matkul, m.sks, u.nama_lengkap AS nama_dosen,
           (SELECT COUNT(*) FROM krs WHERE krs.kelas_id = k.id AND krs.mahasiswa_id = :mhs) AS sudah_ambil
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE k.semester = :sem
    ORDER BY k.kode_kelas ASC
");
$stmtAvail->execute(['mhs' => $mhs_id, 'sem' => $semester]);
$available = $stmtAvail->fetchAll();

$totalSks = 0;
foreach ($available as $a) {
    if ($a['sudah_ambil']) $totalSks += $a['sks'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KRS - ECO-LEARNING</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body class="bg-slate-50">
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <?php include __DIR__ . '/../components/header.php'; ?>
    <main class="page-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Kartu Rencana Studi (KRS)</h1>
                <p class="page-subtitle">Pilih kelas yang tersedia di Semester <?= $semester ?>. Total SKS diambil: <b><?= $totalSks ?> SKS</b>.</p>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 class="card-title mb-4">Kelas Tersedia Semester <?= $semester ?></h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="table-header">Kode Kelas</th>
                            <th class="table-header">Mata Kuliah</th>
                            <th class="table-header">Dosen Pengampu</th>
                            <th class="table-header text-center">SKS</th>
                            <th class="table-header">Jadwal / Ruang</th>
                            <th class="table-header text-center">Kuota</th>
                            <th class="table-header text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($available)): ?>
                            <tr><td colspan="7" class="table-cell text-center text-slate-400 font-mono">Belum ada kelas yang dibuka untuk semester ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($available as $k): ?>
                                <tr class="table-row-hover">
                                    <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($k['kode_kelas']) ?></td>
                                    <td class="table-cell font-bold text-slate-800"><?= htmlspecialchars($k['nama_matkul']) ?></td>
                                    <td class="table-cell text-slate-500"><?= htmlspecialchars($k['nama_dosen'] ?? 'Belum Ditentukan') ?></td>
                                    <td class="table-cell text-center font-mono font-bold"><?= $k['sks'] ?></td>
                                    <td class="table-cell text-slate-500">
                                        <?= htmlspecialchars($k['hari']) ?>, <?= date('H:i', strtotime($k['jam_mulai'])) ?> - <?= date('H:i', strtotime($k['jam_selesai'])) ?><br>
                                        <span class="room-badge-gray"><?= htmlspecialchars($k['ruangan']) ?></span>
                                    </td>
                                    <td class="table-cell text-center font-mono"><?= $k['jumlah_mahasiswa'] ?>/<?= $k['kapasitas'] ?></td>
                                    <td class="table-cell text-right">
                                        <?php if ($k['sudah_ambil']): ?>
                                            <form action="" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="batal_kelas">
                                                <input type="hidden" name="kelas_id" value="<?= $k['id'] ?>">
                                                <span class="badge badge-emerald">Terdaftar</span>
                                                <button type="submit" class="btn-secondary btn-sm">Batal</button>
                                            </form>
                                        <?php elseif ($k['jumlah_mahasiswa'] >= $k['kapasitas']): ?>
                                            <span class="badge badge-gray">Penuh</span>
                                        <?php else: ?>
                                            <form action="" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="ambil_kelas">
                                                <input type="hidden" name="kelas_id" value="<?= $k['id'] ?>">
                                                <button type="submit" class="btn-primary btn-sm">Ambil Kelas</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
