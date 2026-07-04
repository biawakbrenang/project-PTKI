<?php
/**
 * ECO-LEARNING - Lecturer Student Homework Evaluation & Grading Centre
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);

$dosen_id = $_SESSION['dosen_id'] ?? 0;
$msg = '';

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'grade_submission') {
    $sub_id = sanitizeInput($_POST['submission_id'] ?? '');
    $nilai = sanitizeInput($_POST['nilai'] ?? '');
    $komentar = sanitizeInput($_POST['komentar_dosen'] ?? '');

    try {
        $stmtGrade = $pdo->prepare("
            UPDATE submission_tugas_pertemuan 
            SET nilai = :nilai, komentar_dosen = :komentar, status = 'graded', tanggal_graded = NOW() 
            WHERE id = :id
        ");
        $stmtGrade->execute([
            'nilai' => $nilai,
            'komentar' => $komentar,
            'id' => $sub_id
        ]);
        $msg = "Penilaian Submisi Tugas Berhasil Disimpan!";
    } catch (PDOException $e) {
        $msg = "Gagal Menyimpan Nilai: " . $e->getMessage();
    }
}

// Fetch all student submissions for this lecturer's classes
$stmtSubmissions = $pdo->prepare("
    SELECT s.*, t.judul_tugas, u.nama_lengkap AS nama_mahasiswa, m.nim, p.nomor_pertemuan, k.kode_kelas
    FROM submission_tugas_pertemuan s
    JOIN tugas_pertemuan t ON s.tugas_pertemuan_id = t.id
    JOIN pertemuan p ON t.pertemuan_id = p.id
    JOIN kelas k ON p.kelas_id = k.id
    JOIN mahasiswa m ON s.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE k.dosen_id = :dosen
    ORDER BY s.tanggal_submit DESC
");
$stmtSubmissions->execute(['dosen' => $dosen_id]);
$submissions = $stmtSubmissions->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Koreksi & Penilaian Tugas - ECO-LEARNING</title>
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
                    <h1 class="page-title">Evaluasi & Penilaian Tugas</h1>
                    <p class="page-subtitle">Periksa berkas unggahan tugas mahasiswa, berikan skor nilai, dan tulis ulasan umpan balik.</p>
                </div>
            </div>

            <!-- Toast alert message -->
            <?php if (!empty($msg)): ?>
                <div class="alert-success">
                    ✓ <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <!-- Submissions Table list -->
            <div class="card">
                <h3 class="card-title mb-4">Pengumpulan Tugas Siswa Masuk</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="table-header">Mahasiswa</th>
                                <th class="table-header">Kelas / Sesi</th>
                                <th class="table-header">Judul Tugas</th>
                                <th class="table-header">Berkas Submisi</th>
                                <th class="table-header text-center">Status</th>
                                <th class="table-header text-center">Skor Nilai</th>
                                <th class="table-header text-right">Tindakan Koreksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($submissions)): ?>
                                <tr>
                                    <td colspan="7" class="table-cell text-center text-slate-400 font-mono">Belum ada mahasiswa yang mengumpulkan tugas untuk matakuliah Anda.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($submissions as $sub): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell">
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($sub['nama_mahasiswa']) ?></div>
                                            <span class="text-[10px] font-mono text-slate-400"><?= htmlspecialchars($sub['nim']) ?></span>
                                        </td>
                                        <td class="table-cell">
                                            <div class="font-semibold text-slate-700"><?= htmlspecialchars($sub['kode_kelas']) ?></div>
                                            <span class="text-[9px] font-mono font-bold text-slate-400">PERT. <?= $sub['nomor_pertemuan'] ?></span>
                                        </td>
                                        <td class="table-cell text-slate-600 max-w-[180px] truncate" title="<?= htmlspecialchars($sub['judul_tugas']) ?>">
                                            <?= htmlspecialchars($sub['judul_tugas']) ?>
                                        </td>
                                        <td class="table-cell">
                                            <a href="#" class="btn-link-emerald font-mono">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <?= htmlspecialchars($sub['file_name']) ?>
                                            </a>
                                            <span class="file-size-info"><?= number_format($sub['file_size'] / (1024*1024), 2) ?> MB</span>
                                        </td>
                                        <td class="table-cell text-center">
                                            <?php if ($sub['status'] === 'graded'): ?>
                                                <span class="badge-status graded">Sudah Dinilai</span>
                                            <?php else: ?>
                                                <span class="badge-status pending">Butuh Koreksi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-cell text-center font-mono font-bold text-sm text-slate-800">
                                            <?= $sub['nilai'] !== null ? $sub['nilai'] : '-' ?>
                                        </td>
                                        <td class="table-cell text-right">
                                            <!-- Open grading toggle dialog -->
                                            <button onclick="toggleGradeModal('<?= $sub['id'] ?>', '<?= htmlspecialchars($sub['nama_mahasiswa']) ?>', '<?= $sub['nilai'] ?>', '<?= htmlspecialchars($sub['komentar_dosen'] ?? '') ?>')" class="btn-primary btn-sm">
                                                Input Nilai
                                            </button>
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

    <!-- Modals input score -->
    <div id="gradeModal" class="modal-backdrop hidden">
        <div class="modal-card">
            <h4 class="modal-title">Form Penilaian Mahasiswa</h4>
            <p id="gradeStudentName" class="modal-subtitle font-mono"></p>

            <form action="" method="POST" class="form-stack">
                <input type="hidden" name="action" value="grade_submission">
                <input type="hidden" name="submission_id" id="modalSubId">

                <div>
                    <label class="form-label">Skor Nilai Angka (0-100)</label>
                    <input type="number" id="modalScore" name="nilai" required min="0" max="100" class="form-input form-input-bold font-mono">
                </div>

                <div>
                    <label class="form-label">Catatan Kualitatif / Komentar Dosen</label>
                    <textarea id="modalComment" name="komentar_dosen" placeholder="Tulis masukan konstruktif bagi mahasiswa..." rows="3" class="form-input"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeGradeModal()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary btn-md">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
