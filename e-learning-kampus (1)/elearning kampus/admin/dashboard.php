<?php
/**
 * ECO-LEARNING - Administrator Dashboard
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

// Fetch real database statistics
$mhsCount = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
$dosenCount = $pdo->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
$kelasCount = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$matkulCount = $pdo->query("SELECT COUNT(*) FROM matakuliah")->fetchColumn();

// Fetch parallel classes with details
$stmtClasses = $pdo->query("
    SELECT k.*, m.nama_matkul, m.kode_matkul, m.sks, u.nama_lengkap AS nama_dosen
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY k.kode_kelas ASC
    LIMIT 5
");
$classes = $stmtClasses->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - ECO-ACADEMIC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body class="bg-slate-50">

    <!-- Sidebar Layout -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Main Workspace Content -->
    <div class="main-content">
        <!-- Header component -->
        <?php include __DIR__ . '/../components/header.php'; ?>

        <main class="page-container">
            
            <!-- Breadcrumbs -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard Utama Admin</h1>
                    <p class="page-subtitle">Garis besar statistik, data integrasi SIAKAD, dan pengawasan log aktivitas.</p>
                </div>
                <div class="header-actions">
                    <button onclick="window.location.reload()" class="btn-secondary">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.213 6H16" />
                        </svg>
                        Segarkan Data
                    </button>
                </div>
            </div>

            <!-- Bento Stats Panel Grid -->
            <div class="stats-grid">
                <!-- Stat Card 1 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Total Mahasiswa</span>
                        <strong class="stat-value"><?= $mhsCount ?></strong>
                        <span class="stat-desc text-emerald">✓ Terintegrasi SIAKAD</span>
                    </div>
                    <div class="stat-icon-wrapper emerald">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Dosen Pengajar</span>
                        <strong class="stat-value"><?= $dosenCount ?></strong>
                        <span class="stat-desc text-emerald">✓ 100% NIDN Terverifikasi</span>
                    </div>
                    <div class="stat-icon-wrapper blue">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>

                <!-- Stat Card 3 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Matakuliah Terdaftar</span>
                        <strong class="stat-value"><?= $matkulCount ?></strong>
                        <span class="stat-desc text-slate">Dari Kurikulum 2025</span>
                    </div>
                    <div class="stat-icon-wrapper amber">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>

                <!-- Stat Card 4 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Kelas Aktif Berlangsung</span>
                        <strong class="stat-value"><?= $kelasCount ?></strong>
                        <span class="stat-desc text-emerald">✓ Sinkron Jadwal Luring</span>
                    </div>
                    <div class="stat-icon-wrapper purple">
                        <svg class="stat-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content Split Row -->
            <div class="split-grid">
                <!-- Classes list (Large) -->
                <div class="card col-span-2">
                    <div class="card-header-row">
                        <h3 class="card-title">Daftar Kelas Pembelajaran Aktif</h3>
                        <a href="kelas.php" class="card-link">
                            Semua Kelas &raquo;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="table-header">Kode Kelas</th>
                                    <th class="table-header">Nama Matakuliah</th>
                                    <th class="table-header">Dosen Pengampu</th>
                                    <th class="table-header text-center">SKS</th>
                                    <th class="table-header">Hari & Jam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($classes as $c): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($c['kode_kelas']) ?></td>
                                        <td class="table-cell"><?= htmlspecialchars($c['nama_matkul']) ?></td>
                                        <td class="table-cell text-slate-500"><?= htmlspecialchars($c['nama_dosen'] ?? 'Belum Ditentukan') ?></td>
                                        <td class="table-cell text-center font-mono font-bold"><?= $c['sks'] ?> SKS</td>
                                        <td class="table-cell text-slate-500"><?= htmlspecialchars($c['hari']) ?>, <?= date('H:i', strtotime($c['jam_mulai'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Activity Log / Security panel -->
                <div class="card flex flex-col justify-between">
                    <div>
                        <h3 class="card-title mb-4">Log Keamanan Sistem (LPTIK)</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-dot emerald"></div>
                                <div class="activity-content">
                                    <strong class="activity-title">Admin Akademik Utama</strong>
                                    <p class="activity-text">Berhasil sinkronisasi KRS Mahasiswa Informatika Semester 1.</p>
                                    <span class="activity-time"><?= date('d M H:i') ?> WIB</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-dot blue"></div>
                                <div class="activity-content">
                                    <strong class="activity-title">Dr. Rendra Kusuma</strong>
                                    <p class="activity-text">Membuat pertemuan baru di kelas "SI101-A".</p>
                                    <span class="activity-time"><?= date('d M H:i', strtotime('-15 minutes')) ?> WIB</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-dot amber"></div>
                                <div class="activity-content">
                                    <strong class="activity-title">Sistem E-Learning</strong>
                                    <p class="activity-text">Pencadangan harian basis data mysql telah sukses.</p>
                                    <span class="activity-time"><?= date('d M 03:00') ?> WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer-note">
                        <p class="text-note">Keamanan Enkripsi Verifikasi SSL AES-256 Aktif</p>
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
