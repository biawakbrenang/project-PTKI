<?php
/**
 * ECO-LEARNING - Lecturer Announcements Publisher
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);

$dosen_id = $_SESSION['dosen_id'] ?? 0;
$msg = '';
$err = '';

// Handle announcement publishing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'publish_pengumuman') {
    $judul = sanitizeInput($_POST['judul'] ?? '');
    $isi = sanitizeInput($_POST['isi'] ?? '');
    $kelas_target = sanitizeInput($_POST['kelas_id'] ?? '');
    $tanggal = sanitizeInput($_POST['tanggal'] ?? date('Y-m-d'));

    if (empty($judul) || empty($isi)) {
        $err = "Judul dan isi pengumuman wajib diisi.";
    } else {
        try {
            $stmtIns = $pdo->prepare("
                INSERT INTO pengumuman (dosen_id, kelas_id, judul, isi, tanggal)
                VALUES (:dosen, :kelas, :judul, :isi, :tanggal)
            ");
            $stmtIns->execute([
                'dosen' => $dosen_id,
                'kelas' => $kelas_target !== '' ? $kelas_target : null,
                'judul' => $judul,
                'isi' => $isi,
                'tanggal' => $tanggal
            ]);
            $msg = "Pengumuman berhasil diterbitkan dan akan muncul di notifikasi mahasiswa!";
        } catch (PDOException $e) {
            $err = "Gagal Menerbitkan Pengumuman: " . $e->getMessage();
        }
    }
}

// Fetch lecturer classes for target dropdown
$stmtClasses = $pdo->prepare("
    SELECT k.id, k.kode_kelas, m.nama_matkul
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.dosen_id = :dosen
    ORDER BY k.kode_kelas
");
$stmtClasses->execute(['dosen' => $dosen_id]);
$my_classes = $stmtClasses->fetchAll();

// Fetch own announcements
$stmtList = $pdo->prepare("
    SELECT p.*, k.kode_kelas
    FROM pengumuman p
    LEFT JOIN kelas k ON p.kelas_id = k.id
    WHERE p.dosen_id = :dosen
    ORDER BY p.created_at DESC
    LIMIT 30
");
$stmtList->execute(['dosen' => $dosen_id]);
$announcements = $stmtList->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Dosen - ECO-LEARNING</title>
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
                <h1 class="page-title">Pengumuman</h1>
                <p class="page-subtitle">Terbitkan pengumuman yang akan muncul di notifikasi mahasiswa.</p>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <div class="two-col-grid">
            <div class="card">
                <h3 class="card-title">Form Pengumuman</h3>
                <form action="" method="POST" class="form-stack">
                    <input type="hidden" name="action" value="publish_pengumuman">
                    <div>
                        <label class="form-label">Judul</label>
                        <input class="form-input" name="judul" required placeholder="Contoh: Perubahan Jadwal Praktikum">
                    </div>
                    <div>
                        <label class="form-label">Isi Pengumuman</label>
                        <textarea class="form-input" name="isi" rows="4" required placeholder="Tulis isi pengumuman untuk mahasiswa..."></textarea>
                    </div>
                    <div>
                        <label class="form-label">Target Kelas (kosongkan untuk semua kelas Anda)</label>
                        <select name="kelas_id" class="form-input">
                            <option value="">Semua Kelas Saya</option>
                            <?php foreach($my_classes as $myc): ?>
                                <option value="<?= $myc['id'] ?>"><?= htmlspecialchars($myc['kode_kelas']) ?> - <?= htmlspecialchars($myc['nama_matkul']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal</label>
                        <input class="form-input" type="date" name="tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" class="btn-primary">Terbitkan</button>
                </form>
            </div>

            <div class="card">
                <h3 class="card-title">Pengumuman Terbit</h3>
                <div class="mini-list">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-8 text-slate-400 font-mono text-xs">Belum ada pengumuman yang Anda terbitkan.</div>
                    <?php else: ?>
                        <?php foreach($announcements as $a): ?>
                            <div class="mini-item">
                                <b><?= htmlspecialchars($a['judul']) ?></b>
                                <br><small><?= date('d M Y', strtotime($a['tanggal'])) ?> — <?= $a['kode_kelas'] ? htmlspecialchars($a['kode_kelas']) : 'Semua Kelas' ?></small>
                                <p><?= nl2br(htmlspecialchars($a['isi'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
