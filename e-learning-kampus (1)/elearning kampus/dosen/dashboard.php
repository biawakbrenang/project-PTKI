<?php
/**
 * ECO-LEARNING - Lecturer Dashboard
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);

$dosen_id = $_SESSION['dosen_id'] ?? 0;

// Fetch scheduled classes for this specific lecturer
$stmtScheduled = $pdo->prepare("
    SELECT k.*, m.nama_matkul, m.kode_matkul, m.sks
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.dosen_id = :dosen_id
    ORDER BY k.hari ASC, k.jam_mulai ASC
");
$stmtScheduled->execute(['dosen_id' => $dosen_id]);
$scheduledClasses = $stmtScheduled->fetchAll();

// Statistics calculation
$totalSks = 0;
$totalStudents = 0;
foreach($scheduledClasses as $sc) {
    $totalSks += $sc['sks'];
    $totalStudents += $sc['jumlah_mahasiswa'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - ECO-LEARNING</title>
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
                    <h1 class="page-title">Dashboard Utama Pengajar</h1>
                    <p class="page-subtitle">Kelola presensi QR/Token mahasiswa, unggah bahan ajar, dan berikan nilai tugas terintegrasi.</p>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Beban SKS Semester Ini</span>
                        <strong class="stat-value"><?= $totalSks ?> SKS</strong>
                        <span class="stat-desc text-emerald">✓ Memenuhi Target BKD</span>
                    </div>
                    <div class="stat-icon-wrapper emerald">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.475 3.475 0 011.841 4.633 3.42 3.42 0 00-.73 1.996 3.42 3.42 0 00.73 1.996 3.475 3.475 0 01-1.841 4.633 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.475 3.475 0 01-1.841-4.633 3.42 3.42 0 00.73-1.996 3.42 3.42 0 00-.73-1.996 3.475 3.475 0 011.841-4.633z" />
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Total Mahasiswa Didik</span>
                        <strong class="stat-value"><?= $totalStudents ?> Orang</strong>
                        <span class="stat-desc text-slate">Dari Seluruh Kelas Paralel</span>
                    </div>
                    <div class="stat-icon-wrapper blue">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Kelas Paralel Anda</span>
                        <strong class="stat-value"><?= count($scheduledClasses) ?> Kelas</strong>
                        <span class="stat-desc text-emerald">✓ Hari Mengajar Terjadwal</span>
                    </div>
                    <div class="stat-icon-wrapper purple">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Lecturer Schedule details -->
            <div class="card">
                <h3 class="card-title mb-4">Jadwal Mengajar Anda Pekan Ini</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="table-header">Hari</th>
                                <th class="table-header">Kode Kelas</th>
                                <th class="table-header">Nama Matakuliah</th>
                                <th class="table-header text-center">SKS</th>
                                <th class="table-header">Jam & Ruangan</th>
                                <th class="table-header">Siswa Terdaftar</th>
                                <th class="table-header text-right">Menu Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($scheduledClasses)): ?>
                                <tr>
                                    <td colspan="7" class="table-cell text-center text-slate-400 font-mono">Belum ada kelas yang ditugaskan kepada Anda.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($scheduledClasses as $class): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell">
                                            <span class="badge badge-emerald">
                                                <?= htmlspecialchars($class['hari']) ?>
                                            </span>
                                        </td>
                                        <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($class['kode_kelas']) ?></td>
                                        <td class="table-cell font-bold text-slate-800"><?= htmlspecialchars($class['nama_matkul']) ?></td>
                                        <td class="table-cell text-center font-mono font-bold"><?= $class['sks'] ?> SKS</td>
                                        <td class="table-cell font-medium text-slate-500">
                                            <?= date('H:i', strtotime($class['jam_mulai'])) ?> - <?= date('H:i', strtotime($class['jam_selesai'])) ?><br>
                                            <span class="room-badge-gray"><?= htmlspecialchars($class['ruangan']) ?></span>
                                        </td>
                                        <td class="table-cell font-mono font-semibold text-emerald-600"><?= $class['jumlah_mahasiswa'] ?> Mahasiswa</td>
                                        <td class="table-cell text-right">
                                            <a href="absensi.php?kelas_id=<?= $class['id'] ?>" class="btn-primary btn-sm">Presensi</a>
                                            <a href="materi_tugas.php?kelas_id=<?= $class['id'] ?>" class="btn-secondary btn-sm">Materi & Tugas</a>
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
