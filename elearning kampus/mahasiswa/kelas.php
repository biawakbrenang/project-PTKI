<?php
/**
 * ECO-LEARNING - Mahasiswa Class List (click a class to open Materi, Tugas & Pertemuan)
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
$rows = $pdo->prepare("
    SELECT k.id, k.kode_kelas, k.hari, k.jam_mulai, k.jam_selesai, k.ruangan, mk.nama_matkul, mk.sks, u.nama_lengkap AS dosen
    FROM krs
    JOIN kelas k ON krs.kelas_id = k.id
    JOIN matakuliah mk ON krs.matakuliah_id = mk.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE krs.mahasiswa_id = :id
    ORDER BY k.hari, k.jam_mulai
");
$rows->execute(['id' => $mhs_id]);
$rows = $rows->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas - ECO-LEARNING</title>
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
                <h1 class="page-title">Kelas Saya</h1>
                <p class="page-subtitle">Klik salah satu kelas untuk melihat Materi, Tugas, dan Pertemuan (kehadiran).</p>
            </div>
        </div>

        <?php if (empty($rows)): ?>
            <div class="card text-center py-12 text-slate-400 font-mono text-xs">
                Anda belum mengambil kelas. Silakan pilih kelas melalui menu <a href="krs.php" class="btn-link-emerald">KRS</a>.
            </div>
        <?php else: ?>
            <div class="stats-grid">
                <?php foreach ($rows as $r): ?>
                    <a href="kelas_detail.php?id=<?= $r['id'] ?>" class="card table-row-hover" style="display:block;text-decoration:none;">
                        <span class="badge badge-emerald font-mono"><?= htmlspecialchars($r['kode_kelas']) ?></span>
                        <h3 class="card-title mt-2"><?= htmlspecialchars($r['nama_matkul']) ?> (<?= $r['sks'] ?> SKS)</h3>
                        <p class="text-xs text-slate-500 mt-1">Dosen: <?= htmlspecialchars($r['dosen'] ?? 'Belum Ditentukan') ?></p>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($r['hari']) ?>, <?= date('H:i', strtotime($r['jam_mulai'])) ?> - <?= date('H:i', strtotime($r['jam_selesai'])) ?> · <?= htmlspecialchars($r['ruangan']) ?></p>
                        <span class="btn-primary btn-sm mt-3" style="display:inline-block;">Buka Kelas &raquo;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
