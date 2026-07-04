<?php
/**
 * ECO-LEARNING - Mahasiswa Assignment Workspace & Submission Page
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
$msg = '';
$err = '';

// Handle homework file submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_homework') {
    $tugas_id = sanitizeInput($_POST['tugas_id'] ?? '');
    $file_name = sanitizeInput($_POST['file_name'] ?? 'Tugas_Mahasiswa.zip');
    $catatan = sanitizeInput($_POST['catatan_mahasiswa'] ?? '');

    try {
        // Insert submission
        $stmtSubmit = $pdo->prepare("
            INSERT INTO submission_tugas_pertemuan (tugas_pertemuan_id, mahasiswa_id, file_path, file_name, file_size, catatan_mahasiswa, status, tanggal_submit)
            VALUES (:tugas, :mhs, '/uploads/tugas/submisi.zip', :file_name, 1048576, :catatan, 'submitted', NOW())
        ");
        $stmtSubmit->execute([
            'tugas' => $tugas_id,
            'mhs' => $mhs_id,
            'file_name' => $file_name,
            'catatan' => $catatan
        ]);
        $msg = "Tugas Anda Berhasil Dikumpulkan ke Sistem!";
    } catch (PDOException $e) {
        $err = "Gagal Mengumpulkan Tugas: " . $e->getMessage();
    }
}

// Fetch all assignments across student's classes
$stmtTasks = $pdo->prepare("
    SELECT t.*, k.kode_kelas, m.nama_matkul, p.nomor_pertemuan,
           (SELECT status FROM submission_tugas_pertemuan WHERE tugas_pertemuan_id = t.id AND mahasiswa_id = :mhs LIMIT 1) AS sub_status,
           (SELECT nilai FROM submission_tugas_pertemuan WHERE tugas_pertemuan_id = t.id AND mahasiswa_id = :mhs LIMIT 1) AS sub_nilai,
           (SELECT komentar_dosen FROM submission_tugas_pertemuan WHERE tugas_pertemuan_id = t.id AND mahasiswa_id = :mhs LIMIT 1) AS sub_comment
    FROM tugas_pertemuan t
    JOIN pertemuan p ON t.pertemuan_id = p.id
    JOIN kelas k ON p.kelas_id = k.id
    JOIN matakuliah m ON k.matakuliah_id = m.id
    JOIN krs ON krs.kelas_id = k.id
    WHERE krs.mahasiswa_id = :mhs
    ORDER BY t.tanggal_deadline ASC
");
$stmtTasks->execute(['mhs' => $mhs_id]);
$tasks = $stmtTasks->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pekerjaan Rumah & Tugas - ECO-LEARNING</title>
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
                    <h1 class="page-title">Tugas & Pengumpulan Lembar Kerja</h1>
                    <p class="page-subtitle">Unduh berkas soal, kumpulkan penugasan individu maupun kelompok, dan lihat nilai kualitatif dosen.</p>
                </div>
            </div>

            <!-- Toast feedback msg -->
            <?php if (!empty($msg)): ?>
                <div class="alert-success">
                    ✓ <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($err)): ?>
                <div class="alert-danger">
                    ⚠ <?= htmlspecialchars($err) ?>
                </div>
            <?php endif; ?>

            <div class="split-grid">
                <!-- Assignments List Panel (Large) -->
                <div class="card col-span-2 flex-col-stack">
                    <h3 class="card-title">Daftar Penugasan Aktif</h3>
                    
                    <div class="scrollable-task-list">
                        <?php if (empty($tasks)): ?>
                            <div class="text-center py-12 text-slate-400 font-mono text-xs">Belum ada tugas yang diterbitkan untuk seluruh kelas Anda.</div>
                        <?php else: ?>
                            <?php foreach($tasks as $t): ?>
                                <div class="task-item-card">
                                    <div class="task-item-header">
                                        <div>
                                            <span class="badge badge-gray font-mono"><?= htmlspecialchars($t['kode_kelas']) ?> - Pert. <?= $t['nomor_pertemuan'] ?></span>
                                            <h4 class="task-item-title"><?= htmlspecialchars($t['judul_tugas']) ?></h4>
                                            <span class="task-item-meta"><?= htmlspecialchars($t['nama_matkul']) ?> (Bobot: <?= $t['bobot_nilai'] ?>%)</span>
                                        </div>
                                        <div>
                                            <?php if ($t['sub_status'] === 'graded'): ?>
                                                <span class="badge-status graded">Nilai: <?= $t['sub_nilai'] ?> / 100</span>
                                            <?php elseif ($t['sub_status'] === 'submitted'): ?>
                                                <span class="badge-status submitted">Sudah Dikumpul</span>
                                            <?php else: ?>
                                                <span class="badge-status pending">Belum Dikumpul</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <p class="task-item-desc"><?= htmlspecialchars($t['deskripsi'] ?? 'Tidak ada instruksi khusus.') ?></p>

                                    <!-- Feedback Comment from Lecturer if available -->
                                    <?php if (!empty($t['sub_comment'])): ?>
                                        <div class="alert-box-amber">
                                            <span class="alert-box-title-amber">Review Ulasan Dosen:</span>
                                            "<?= htmlspecialchars($t['sub_comment']) ?>"
                                        </div>
                                    <?php endif; ?>

                                    <div class="task-item-footer">
                                        <div class="task-item-deadline font-mono">
                                            Batas Akhir: <span class="text-danger-bold"><?= date('d F Y H:i', strtotime($t['tanggal_deadline'])) ?></span>
                                        </div>
                                        <?php if ($t['sub_status'] !== 'submitted' && $t['sub_status'] !== 'graded'): ?>
                                            <button onclick="openSubmitModal('<?= $t['id'] ?>', '<?= htmlspecialchars($t['judul_tugas']) ?>')" class="btn-primary btn-sm">
                                                Kumpulkan Tugas
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Guidance Upload Workspace Info Column -->
                <div class="card">
                    <h3 class="card-title">Pusat Bantuan Berkas</h3>
                    <ul class="guidance-list">
                        <li>Pastikan semua berkas kode sumber dikompresi ke dalam bentuk format .ZIP atau .RAR</li>
                        <li>Maksimal kapasitas file unggahan adalah sebesar 50 MB.</li>
                        <li>Sistem menggunakan penanda waktu server (WIB) untuk menguji keterlambatan deadline. Keterlambatan dapat memengaruhi hasil penilaian kualitatif dosen.</li>
                    </ul>
                </div>
            </div>

        </main>
    </div>

    <!-- Submit Homework Modal -->
    <div id="submitModal" class="modal-backdrop hidden">
        <div class="modal-card">
            <h4 class="modal-title">Unggah Lembar Kerja Tugas</h4>
            <p id="modalTaskName" class="modal-subtitle font-mono"></p>

            <form action="" method="POST" class="form-stack">
                <input type="hidden" name="action" value="submit_homework">
                <input type="hidden" name="tugas_id" id="modalTaskId">

                <div>
                    <label class="form-label">Nama Berkas ZIP / PDF</label>
                    <input type="text" name="file_name" required value="tugas_praktikum_unas.zip" class="form-input form-input-bold font-mono">
                </div>

                <div>
                    <label class="form-label">Catatan Pengantar</label>
                    <textarea name="catatan_mahasiswa" placeholder="Ketik pesan singkat kepada dosen pengajar (opsional)..." rows="3" class="form-input"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeSubmitModal()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary btn-md">Kirim Tugas</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
