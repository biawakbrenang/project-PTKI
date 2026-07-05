<?php
/**
 * ECO-LEARNING - Lecturer Grade Input Centre
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);

$dosen_id = $_SESSION['dosen_id'] ?? 0;
$kelas_id = sanitizeInput($_GET['kelas_id'] ?? '');
$msg = '';
$err = '';

// Handle final grade input per student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'input_grade') {
    $krs_id = sanitizeInput($_POST['krs_id'] ?? '');
    $grade = strtoupper(sanitizeInput($_POST['grade'] ?? ''));
    $kelas_id = sanitizeInput($_POST['kelas_id'] ?? $kelas_id);

    if (!in_array($grade, ['A', 'B', 'C', 'D', 'E'])) {
        $err = "Grade tidak valid. Gunakan huruf A, B, C, D, atau E.";
    } else {
        try {
            // Only update rows belonging to this lecturer's classes
            $stmtGrade = $pdo->prepare("
                UPDATE krs
                JOIN kelas k ON krs.kelas_id = k.id
                SET krs.grade = :grade
                WHERE krs.id = :krs_id AND k.dosen_id = :dosen
            ");
            $stmtGrade->execute(['grade' => $grade, 'krs_id' => $krs_id, 'dosen' => $dosen_id]);
            $msg = "Nilai akhir mahasiswa berhasil disimpan!";
        } catch (PDOException $e) {
            $err = "Gagal Menyimpan Nilai: " . $e->getMessage();
        }
    }
}

// Fetch lecturer's classes for dropdown
$stmtClasses = $pdo->prepare("
    SELECT k.id, k.kode_kelas, m.nama_matkul
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.dosen_id = :dosen
    ORDER BY k.kode_kelas
");
$stmtClasses->execute(['dosen' => $dosen_id]);
$my_classes = $stmtClasses->fetchAll();

// Fetch students of selected class with their assignment average
$students = [];
if (!empty($kelas_id)) {
    $stmtStudents = $pdo->prepare("
        SELECT krs.id AS krs_id, krs.grade, u.nama_lengkap, mh.nim, k.kode_kelas, mk.nama_matkul,
               (SELECT ROUND(AVG(s.nilai), 1)
                FROM submission_tugas_pertemuan s
                JOIN tugas_pertemuan t ON s.tugas_pertemuan_id = t.id
                JOIN pertemuan p ON t.pertemuan_id = p.id
                WHERE p.kelas_id = k.id AND s.mahasiswa_id = mh.id AND s.nilai IS NOT NULL) AS rata_tugas
        FROM krs
        JOIN mahasiswa mh ON krs.mahasiswa_id = mh.id
        JOIN users u ON mh.user_id = u.id
        JOIN kelas k ON krs.kelas_id = k.id
        JOIN matakuliah mk ON krs.matakuliah_id = mk.id
        WHERE krs.kelas_id = :kelas AND k.dosen_id = :dosen
        ORDER BY mh.nim ASC
    ");
    $stmtStudents->execute(['kelas' => $kelas_id, 'dosen' => $dosen_id]);
    $students = $stmtStudents->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - ECO-LEARNING</title>
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
                    <h1 class="page-title">Input Nilai Akhir Mahasiswa</h1>
                    <p class="page-subtitle">Pilih kelas, lihat rata-rata nilai tugas, dan tetapkan grade akhir (A-E) per mahasiswa.</p>
                </div>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if (!empty($err)): ?>
                <div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <h3 class="card-title mb-3">Pilih Kelas</h3>
                <form action="" method="GET">
                    <select name="kelas_id" required onchange="this.form.submit()" class="form-input">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach($my_classes as $myc): ?>
                            <option value="<?= $myc['id'] ?>" <?= $kelas_id == $myc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($myc['kode_kelas']) ?> - <?= htmlspecialchars($myc['nama_matkul']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="card">
                <h3 class="card-title mb-4">Tabel Input Nilai Mahasiswa</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="table-header">NPM</th>
                                <th class="table-header">Nama Mahasiswa</th>
                                <th class="table-header">Kelas</th>
                                <th class="table-header">Mata Kuliah</th>
                                <th class="table-header text-center">Rata-rata Tugas</th>
                                <th class="table-header text-center">Grade Saat Ini</th>
                                <th class="table-header text-right">Input Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kelas_id)): ?>
                                <tr><td colspan="7" class="table-cell text-center text-slate-400 font-mono">Silakan pilih kelas terlebih dahulu untuk memuat daftar mahasiswa.</td></tr>
                            <?php elseif (empty($students)): ?>
                                <tr><td colspan="7" class="table-cell text-center text-slate-400 font-mono">Belum ada mahasiswa terdaftar di kelas ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($students as $st): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($st['nim']) ?></td>
                                        <td class="table-cell font-medium"><?= htmlspecialchars($st['nama_lengkap']) ?></td>
                                        <td class="table-cell font-mono"><?= htmlspecialchars($st['kode_kelas']) ?></td>
                                        <td class="table-cell"><?= htmlspecialchars($st['nama_matkul']) ?></td>
                                        <td class="table-cell text-center font-mono"><?= $st['rata_tugas'] !== null ? $st['rata_tugas'] : '-' ?></td>
                                        <td class="table-cell text-center">
                                            <span class="badge badge-blue"><?= htmlspecialchars($st['grade'] ?? 'Belum Ada') ?></span>
                                        </td>
                                        <td class="table-cell text-right">
                                            <form action="" method="POST" class="inline-flex items-center gap-2 justify-end">
                                                <input type="hidden" name="action" value="input_grade">
                                                <input type="hidden" name="krs_id" value="<?= $st['krs_id'] ?>">
                                                <input type="hidden" name="kelas_id" value="<?= htmlspecialchars($kelas_id) ?>">
                                                <select name="grade" required class="form-input form-input-sm" style="width:auto;display:inline-block;">
                                                    <option value="">Grade</option>
                                                    <?php foreach(['A','B','C','D','E'] as $g): ?>
                                                        <option value="<?= $g ?>" <?= ($st['grade'] === $g) ? 'selected' : '' ?>><?= $g ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn-primary btn-sm">Simpan</button>
                                            </form>
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
