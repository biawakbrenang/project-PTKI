<?php
/**
 * ECO-LEARNING - Mahasiswa Dashboard
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
$semester = $_SESSION['semester'] ?? 1;

// Fetch current registered courses in KRS
$stmtKrs = $pdo->prepare("
    SELECT krs.*, k.kode_kelas, k.hari, k.jam_mulai, k.ruangan, m.nama_matkul, m.kode_matkul, m.sks, u.nama_lengkap AS nama_dosen
    FROM krs
    JOIN kelas k ON krs.kelas_id = k.id
    JOIN matakuliah m ON krs.matakuliah_id = m.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE krs.mahasiswa_id = :mhs AND krs.semester = :sem
");
$stmtKrs->execute(['mhs' => $mhs_id, 'sem' => $semester]);
$my_courses = $stmtKrs->fetchAll();

// Outstanding tasks list
$stmtTasks = $pdo->prepare("
    SELECT t.*, k.kode_kelas, m.nama_matkul
    FROM tugas_pertemuan t
    JOIN pertemuan p ON t.pertemuan_id = p.id
    JOIN kelas k ON p.kelas_id = k.id
    JOIN matakuliah m ON k.matakuliah_id = m.id
    JOIN krs ON krs.kelas_id = k.id
    WHERE krs.mahasiswa_id = :mhs AND t.status = 'aktif'
    ORDER BY t.tanggal_deadline ASC
    LIMIT 3
");
$stmtTasks->execute(['mhs' => $mhs_id]);
$pending_tasks = $stmtTasks->fetchAll();

// Calculate total SKS
$totalSks = 0;
foreach($my_courses as $c) {
    $totalSks += $c['sks'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Dashboard - ECO-LEARNING</title>
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
                    <h1 class="page-title">Dashboard Akademik</h1>
                    <p class="page-subtitle">Status pengambilan Kartu Rencana Studi (KRS), jadwal sesi kelas, dan pengumpulan tugas mandiri.</p>
                </div>
            </div>

            <!-- Stats strip cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">SKS Diambil Semester Ini</span>
                        <strong class="stat-value"><?= $totalSks ?> SKS</strong>
                        <span class="stat-desc text-emerald">✓ Disetujui Dosen Wali (PA)</span>
                    </div>
                    <div class="stat-icon-wrapper emerald">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.475 3.475 0 011.841 4.633 3.42 3.42 0 00-.73 1.996 3.42 3.42 0 00.73 1.996 3.475 3.475 0 01-1.841 4.633 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.475 3.475 0 01-1.841-4.633 3.42 3.42 0 00.73-1.996 3.42 3.42 0 00-.73-1.996 3.475 3.475 0 011.841-4.633z" />
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Semester Akademik</span>
                        <strong class="stat-value">Semester <?= $semester ?></strong>
                        <span class="stat-desc text-slate">Tahun Ajaran 2025/2026</span>
                    </div>
                    <div class="stat-icon-wrapper blue">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Tugas Menunggu</span>
                        <strong class="stat-value text-danger"><?= count($pending_tasks) ?> Penugasan</strong>
                        <span class="stat-desc text-danger">⚠️ Butuh Penyelesaian Segera</span>
                    </div>
                    <div class="stat-icon-wrapper red">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="split-grid">
                <!-- Registered KRS classes -->
                <div class="card col-span-2">
                    <h3 class="card-title mb-4">Kelas Pembelajaran Anda Semester Ini</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="table-header">Kode Kelas</th>
                                    <th class="table-header">Nama Matakuliah</th>
                                    <th class="table-header">Dosen Pengampu</th>
                                    <th class="table-header text-center">SKS</th>
                                    <th class="table-header">Jadwal / Ruang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($my_courses)): ?>
                                    <tr>
                                        <td colspan="5" class="table-cell text-center text-slate-400 font-mono">Anda belum memprogram KRS untuk semester ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($my_courses as $c): ?>
                                        <tr class="table-row-hover">
                                            <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($c['kode_kelas']) ?></td>
                                            <td class="table-cell font-bold text-slate-800"><?= htmlspecialchars($c['nama_matkul']) ?></td>
                                            <td class="table-cell font-medium text-slate-500"><?= htmlspecialchars($c['nama_dosen'] ?? 'Belum Ditentukan') ?></td>
                                            <td class="table-cell text-center font-mono font-bold"><?= $c['sks'] ?> SKS</td>
                                            <td class="table-cell font-medium text-slate-500">
                                                <?= htmlspecialchars($c['hari']) ?>, <?= date('H:i', strtotime($c['jam_mulai'])) ?><br>
                                                <span class="room-badge"><?= htmlspecialchars($c['ruangan']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending assignments column with countdown -->
                <div class="card flex flex-col justify-between">
                    <div>
                        <h3 class="card-title mb-4">Pengingat Tugas Mandiri</h3>
                        <div class="space-y-4">
                            <?php if (empty($pending_tasks)): ?>
                                <div class="text-center py-12 text-slate-400 font-mono text-xs">Semua tugas Anda telah selesai dikerjakan!</div>
                            <?php else: ?>
                                <?php foreach($pending_tasks as $task): ?>
                                    <div class="alert-box-red">
                                        <span class="alert-box-deadline">DEADLINE: <?= date('d M Y H:i', strtotime($task['tanggal_deadline'])) ?></span>
                                        <strong class="alert-box-title"><?= htmlspecialchars($task['judul_tugas']) ?></strong>
                                        <span class="alert-box-subtitle"><?= htmlspecialchars($task['nama_matkul']) ?></span>
                                        <a href="tugas.php" class="alert-box-action">Kumpulkan Sekarang &raquo;</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer-credit">
                        <span class="footer-credit-text">ECO-LEARNING v2.5 Unas Secure Client</span>
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
