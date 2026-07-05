<?php
/**
 * ECO-LEARNING - Mahasiswa Announcements / Notifications
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;

// Announcements from lecturers of the student's enrolled classes:
// targeted to one of their classes, or broadcast (kelas_id NULL) by a lecturer teaching them
$stmtList = $pdo->prepare("
    SELECT DISTINCT p.*, u.nama_lengkap AS nama_dosen, k.kode_kelas
    FROM pengumuman p
    JOIN dosen d ON p.dosen_id = d.id
    JOIN users u ON d.user_id = u.id
    LEFT JOIN kelas k ON p.kelas_id = k.id
    WHERE (p.kelas_id IN (SELECT kelas_id FROM krs WHERE mahasiswa_id = :mhs1))
       OR (p.kelas_id IS NULL AND p.dosen_id IN (
              SELECT kls.dosen_id FROM krs
              JOIN kelas kls ON krs.kelas_id = kls.id
              WHERE krs.mahasiswa_id = :mhs2 AND kls.dosen_id IS NOT NULL))
    ORDER BY p.created_at DESC
    LIMIT 50
");
$stmtList->execute(['mhs1' => $mhs_id, 'mhs2' => $mhs_id]);
$announcements = $stmtList->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman - ECO-LEARNING</title>
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
                <h1 class="page-title">Pengumuman & Notifikasi</h1>
                <p class="page-subtitle">Pengumuman terbaru dari dosen pengampu kelas Anda.</p>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Daftar Pengumuman</h3>
            <div class="mini-list">
                <?php if (empty($announcements)): ?>
                    <div class="text-center py-8 text-slate-400 font-mono text-xs">Belum ada pengumuman dari dosen Anda.</div>
                <?php else: ?>
                    <?php foreach($announcements as $a): ?>
                        <div class="mini-item">
                            <b><?= htmlspecialchars($a['judul']) ?></b>
                            <br><small><?= date('d M Y', strtotime($a['tanggal'])) ?> — <?= htmlspecialchars($a['nama_dosen']) ?><?= $a['kode_kelas'] ? ' (' . htmlspecialchars($a['kode_kelas']) . ')' : '' ?></small>
                            <p><?= nl2br(htmlspecialchars($a['isi'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
