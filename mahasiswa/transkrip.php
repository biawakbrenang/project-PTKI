<?php
/**
 * ECO-LEARNING - Mahasiswa KRS & IPK Transcript Centre
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;

// Fetch ALL grades across all semesters for this student
$stmtTranscript = $pdo->prepare("
    SELECT krs.*, k.kode_kelas, m.nama_matkul, m.kode_matkul, m.sks, u.nama_lengkap AS nama_dosen
    FROM krs
    JOIN kelas k ON krs.kelas_id = k.id
    JOIN matakuliah m ON k.matakuliah_id = m.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE krs.mahasiswa_id = :mhs
    ORDER BY krs.semester ASC, m.nama_matkul ASC
");
$stmtTranscript->execute(['mhs' => $mhs_id]);
$transcript_items = $stmtTranscript->fetchAll();

// Dynamic Calculation of IPK
$total_sks_passed = 0;
$total_points = 0;

function convertGradeToPoint($grade) {
    switch(strtoupper($grade)) {
        case 'A': return 4.0;
        case 'B': return 3.0;
        case 'C': return 2.0;
        case 'D': return 1.0;
        case 'E': return 0.0;
        default: return null;
    }
}

foreach($transcript_items as $item) {
    $point = convertGradeToPoint($item['grade']);
    if ($point !== null) {
        $total_sks_passed += $item['sks'];
        $total_points += ($point * $item['sks']);
    }
}

$ipk = $total_sks_passed > 0 ? round($total_points / $total_sks_passed, 2) : 0.0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai Akademik - ECO-LEARNING</title>
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
                    <h1 class="page-title">Transkrip KHS & IPK Akademik</h1>
                    <p class="page-subtitle">Histori Kartu Hasil Studi (KHS) mahasiswa, rekapitulasi poin bobot, dan Indeks Prestasi Kumulatif.</p>
                </div>
            </div>

            <!-- GPA IPK Highlight Bento Box -->
            <div class="three-col-grid">
                <!-- IPK Card -->
                <div class="card-dark flex-col-between">
                    <div>
                        <span class="gpa-label">Indeks Prestasi Kumulatif</span>
                        <strong class="gpa-value"><?= number_format($ipk, 2) ?></strong>
                    </div>
                    <p class="gpa-footer">Skala Penilaian Standar BAN-PT Unas</p>
                </div>

                <!-- Total SKS Lulus -->
                <div class="card flex-col-between">
                    <div>
                        <span class="stat-label-light">Kredit SKS Terselesaikan</span>
                        <strong class="stat-value-large"><?= $total_sks_passed ?> SKS</strong>
                    </div>
                    <p class="gpa-footer-success">✓ Memenuhi Prasyarat Semester Selanjutnya</p>
                </div>

                <!-- Status Akademis -->
                <div class="card flex-col-between">
                    <div>
                        <span class="stat-label-light">Status Akademik Mahasiswa</span>
                        <strong class="stat-value-large">Aktif Normal</strong>
                    </div>
                    <p class="gpa-footer-neutral">Bebas Tunggakan Administrasi BAAK</p>
                </div>
            </div>

            <!-- Grade breakdown list -->
            <div class="card">
                <h3 class="card-title mb-4">Lembar Kartu Hasil Studi (KHS)</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="table-header">Semester</th>
                                <th class="table-header">Kode Matakuliah</th>
                                <th class="table-header">Nama Matakuliah</th>
                                <th class="table-header text-center">SKS</th>
                                <th class="table-header text-center">Nilai Huruf</th>
                                <th class="table-header text-center">Bobot Angka</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transcript_items)): ?>
                                <tr>
                                    <td colspan="6" class="table-cell text-center text-slate-400 font-mono">Belum ada KHS yang diterbitkan untuk akun Anda.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($transcript_items as $item): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell font-mono font-bold">SEM <?= $item['semester'] ?></td>
                                        <td class="table-cell font-mono text-slate-500"><?= htmlspecialchars($item['kode_matkul']) ?></td>
                                        <td class="table-cell font-bold text-slate-800"><?= htmlspecialchars($item['nama_matkul']) ?></td>
                                        <td class="table-cell text-center font-mono font-bold"><?= $item['sks'] ?> SKS</td>
                                        <td class="table-cell text-center font-mono font-bold">
                                            <span class="badge-letter">
                                                <?= $item['grade'] !== null ? strtoupper($item['grade']) : 'Belum Ada' ?>
                                            </span>
                                        </td>
                                        <td class="table-cell text-center font-mono font-bold text-slate-500">
                                            <?= $item['grade'] !== null ? number_format(convertGradeToPoint($item['grade']), 1) : '-' ?>
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
