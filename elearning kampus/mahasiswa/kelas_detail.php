<?php
/**
 * ECO-LEARNING - Mahasiswa Class Detail (Materi, Tugas, Pertemuan/Kehadiran)
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['mahasiswa']);

$mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
$kelas_id = sanitizeInput($_GET['id'] ?? '');
$tab = sanitizeInput($_GET['tab'] ?? 'materi');
if (!in_array($tab, ['materi', 'tugas', 'pertemuan'])) $tab = 'materi';
$msg = '';
$err = '';

// Verify the student is enrolled in this class
$stmtEnroll = $pdo->prepare("
    SELECT krs.id AS krs_id, k.*, mk.nama_matkul, mk.kode_matkul, mk.sks, u.nama_lengkap AS nama_dosen
    FROM krs
    JOIN kelas k ON krs.kelas_id = k.id
    JOIN matakuliah mk ON k.matakuliah_id = mk.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE krs.mahasiswa_id = :mhs AND krs.kelas_id = :kelas
    LIMIT 1
");
$stmtEnroll->execute(['mhs' => $mhs_id, 'kelas' => $kelas_id]);
$kelas = $stmtEnroll->fetch();

if (!$kelas) {
    header('Location: kelas.php');
    exit;
}
$krs_id = $kelas['krs_id'];

// Handle assignment submission (kolom submission dengan upload berkas asli)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_tugas') {
    $tab = 'tugas';
    $tugas_id = sanitizeInput($_POST['tugas_id'] ?? '');
    $catatan = sanitizeInput($_POST['catatan_mahasiswa'] ?? '');

    // Ensure the assignment belongs to this class
    $stmtT = $pdo->prepare("
        SELECT t.id FROM tugas_pertemuan t
        JOIN pertemuan p ON t.pertemuan_id = p.id
        WHERE t.id = :tugas AND p.kelas_id = :kelas
    ");
    $stmtT->execute(['tugas' => $tugas_id, 'kelas' => $kelas_id]);

    if (!$stmtT->fetchColumn()) {
        $err = "Tugas tidak valid untuk kelas ini.";
    } elseif (!isset($_FILES['file_tugas']) || $_FILES['file_tugas']['error'] !== UPLOAD_ERR_OK) {
        $err = "Silakan pilih berkas tugas yang akan diunggah.";
    } else {
        $file = $_FILES['file_tugas'];
        $allowed = ['zip', 'rar', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $err = "Tipe berkas tidak diizinkan. Gunakan ZIP/RAR/PDF/DOC/PPT/XLS/gambar.";
        } elseif ($file['size'] > 50 * 1024 * 1024) {
            $err = "Ukuran berkas melebihi batas 50 MB.";
        } else {
            $uploadDir = __DIR__ . '/../uploads/submission/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $storedName = 'sub_' . $mhs_id . '_' . $tugas_id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
                try {
                    $stmtSub = $pdo->prepare("
                        INSERT INTO submission_tugas_pertemuan (tugas_pertemuan_id, mahasiswa_id, file_path, file_name, file_size, catatan_mahasiswa, status, tanggal_submit)
                        VALUES (:tugas, :mhs, :path, :name, :size, :catatan, 'submitted', NOW())
                    ");
                    $stmtSub->execute([
                        'tugas' => $tugas_id,
                        'mhs' => $mhs_id,
                        'path' => 'uploads/submission/' . $storedName,
                        'name' => $file['name'],
                        'size' => $file['size'],
                        'catatan' => $catatan
                    ]);
                    $msg = "Tugas Anda berhasil dikumpulkan melalui kolom submission!";
                } catch (PDOException $e) {
                    $err = "Gagal Menyimpan Submission: " . $e->getMessage();
                }
            } else {
                $err = "Gagal mengunggah berkas ke server.";
            }
        }
    }
}

// ---- Data per tab ----
// Materi
$stmtMateri = $pdo->prepare("
    SELECT mp.*, p.nomor_pertemuan, p.judul_pertemuan
    FROM materi_pertemuan mp
    JOIN pertemuan p ON mp.pertemuan_id = p.id
    WHERE p.kelas_id = :kelas
    ORDER BY p.nomor_pertemuan ASC, mp.created_at ASC
");
$stmtMateri->execute(['kelas' => $kelas_id]);
$materials = $stmtMateri->fetchAll();

// Tugas + submission status
$stmtTugas = $pdo->prepare("
    SELECT t.*, p.nomor_pertemuan,
           s.id AS sub_id, s.status AS sub_status, s.nilai AS sub_nilai, s.komentar_dosen AS sub_comment,
           s.file_name AS sub_file, s.tanggal_submit AS sub_date
    FROM tugas_pertemuan t
    JOIN pertemuan p ON t.pertemuan_id = p.id
    LEFT JOIN submission_tugas_pertemuan s ON s.tugas_pertemuan_id = t.id AND s.mahasiswa_id = :mhs
    WHERE p.kelas_id = :kelas
    ORDER BY t.tanggal_deadline ASC
");
$stmtTugas->execute(['mhs' => $mhs_id, 'kelas' => $kelas_id]);
$tasks = $stmtTugas->fetchAll();

// Pertemuan + kehadiran
$stmtPertemuan = $pdo->prepare("
    SELECT p.*, a.status AS status_hadir, a.tanggal AS tanggal_absen, a.keterangan
    FROM pertemuan p
    LEFT JOIN absensi a ON a.pertemuan = p.nomor_pertemuan AND a.krs_id = :krs
    WHERE p.kelas_id = :kelas
    ORDER BY p.nomor_pertemuan ASC
");
$stmtPertemuan->execute(['krs' => $krs_id, 'kelas' => $kelas_id]);
$meetings = $stmtPertemuan->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kelas['kode_kelas']) ?> - ECO-LEARNING</title>
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
                <a href="kelas.php" class="btn-link-emerald">&laquo; Kembali ke Daftar Kelas</a>
                <h1 class="page-title mt-1"><?= htmlspecialchars($kelas['kode_kelas']) ?> - <?= htmlspecialchars($kelas['nama_matkul']) ?></h1>
                <p class="page-subtitle">Dosen: <?= htmlspecialchars($kelas['nama_dosen'] ?? 'Belum Ditentukan') ?> · <?= htmlspecialchars($kelas['hari']) ?>, <?= date('H:i', strtotime($kelas['jam_mulai'])) ?> - <?= date('H:i', strtotime($kelas['jam_selesai'])) ?> · <?= htmlspecialchars($kelas['ruangan']) ?></p>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="card" style="padding:10px 14px;margin-bottom:16px;">
            <a href="?id=<?= $kelas_id ?>&tab=materi" class="<?= $tab === 'materi' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Materi</a>
            <a href="?id=<?= $kelas_id ?>&tab=tugas" class="<?= $tab === 'tugas' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Tugas</a>
            <a href="?id=<?= $kelas_id ?>&tab=pertemuan" class="<?= $tab === 'pertemuan' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Pertemuan</a>
        </div>

        <?php if ($tab === 'materi'): ?>
            <!-- MATERI: student can download files uploaded by lecturer -->
            <div class="card">
                <h3 class="card-title mb-4">Materi Perkuliahan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="table-header">Pertemuan</th>
                                <th class="table-header">Judul Materi</th>
                                <th class="table-header">Deskripsi</th>
                                <th class="table-header">Berkas</th>
                                <th class="table-header text-right">Unduh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($materials)): ?>
                                <tr><td colspan="5" class="table-cell text-center text-slate-400 font-mono">Belum ada materi yang diunggah dosen untuk kelas ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($materials as $m): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell"><span class="badge badge-emerald">Pert. <?= $m['nomor_pertemuan'] ?></span></td>
                                        <td class="table-cell font-bold text-slate-800"><?= htmlspecialchars($m['judul_materi']) ?></td>
                                        <td class="table-cell text-slate-500"><?= htmlspecialchars($m['deskripsi'] ?? '-') ?></td>
                                        <td class="table-cell font-mono"><?= htmlspecialchars($m['file_name']) ?> <span class="file-size-info">(<?= number_format($m['file_size'] / 1024, 0) ?> KB)</span></td>
                                        <td class="table-cell text-right">
                                            <a href="../<?= htmlspecialchars(ltrim($m['file_path'], '/')) ?>" download="<?= htmlspecialchars($m['file_name']) ?>" class="btn-primary btn-sm">Unduh Materi</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab === 'tugas'): ?>
            <!-- TUGAS: student submits work through the submission column -->
            <div class="card">
                <h3 class="card-title mb-4">Tugas & Kolom Submission</h3>
                <?php if (empty($tasks)): ?>
                    <div class="text-center py-12 text-slate-400 font-mono text-xs">Belum ada tugas yang diterbitkan dosen untuk kelas ini.</div>
                <?php else: ?>
                    <?php foreach ($tasks as $t): ?>
                        <div class="task-item-card">
                            <div class="task-item-header">
                                <div>
                                    <span class="badge badge-gray font-mono">Pert. <?= $t['nomor_pertemuan'] ?></span>
                                    <h4 class="task-item-title"><?= htmlspecialchars($t['judul_tugas']) ?></h4>
                                    <span class="task-item-meta">Bobot: <?= $t['bobot_nilai'] ?>% · Deadline: <?= date('d M Y H:i', strtotime($t['tanggal_deadline'])) ?></span>
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

                            <?php if (!empty($t['sub_comment'])): ?>
                                <div class="alert-box-amber">
                                    <span class="alert-box-title-amber">Ulasan Dosen:</span>
                                    "<?= htmlspecialchars($t['sub_comment']) ?>"
                                </div>
                            <?php endif; ?>

                            <?php if ($t['sub_status'] === 'submitted' || $t['sub_status'] === 'graded'): ?>
                                <p class="task-item-deadline font-mono">Berkas terkirim: <b><?= htmlspecialchars($t['sub_file']) ?></b> pada <?= date('d M Y H:i', strtotime($t['sub_date'])) ?></p>
                            <?php else: ?>
                                <!-- Kolom Submission -->
                                <form action="?id=<?= $kelas_id ?>&tab=tugas" method="POST" enctype="multipart/form-data" class="form-stack" style="border-top:1px solid #f1f5f9;padding-top:12px;margin-top:8px;">
                                    <input type="hidden" name="action" value="submit_tugas">
                                    <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                                    <div class="two-col-grid">
                                        <div>
                                            <label class="form-label">Kolom Submission (Unggah Berkas)</label>
                                            <input type="file" name="file_tugas" required class="form-input">
                                        </div>
                                        <div>
                                            <label class="form-label">Catatan untuk Dosen (opsional)</label>
                                            <input type="text" name="catatan_mahasiswa" placeholder="Pesan singkat..." class="form-input">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn-primary btn-sm">Kirim Tugas</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- PERTEMUAN: attendance list for this class -->
            <div class="card">
                <h3 class="card-title mb-4">Daftar Pertemuan & Kehadiran Anda</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="table-header">Pertemuan</th>
                                <th class="table-header">Judul</th>
                                <th class="table-header">Tanggal</th>
                                <th class="table-header text-center">Status Sesi</th>
                                <th class="table-header text-center">Kehadiran Anda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($meetings)): ?>
                                <tr><td colspan="5" class="table-cell text-center text-slate-400 font-mono">Belum ada pertemuan yang dijadwalkan untuk kelas ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($meetings as $p): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell font-mono font-bold">Pertemuan <?= $p['nomor_pertemuan'] ?></td>
                                        <td class="table-cell font-medium"><?= htmlspecialchars($p['judul_pertemuan'] ?? '-') ?></td>
                                        <td class="table-cell text-slate-500"><?= $p['tanggal_pertemuan'] ? date('d M Y', strtotime($p['tanggal_pertemuan'])) : '-' ?></td>
                                        <td class="table-cell text-center">
                                            <span class="badge badge-gray"><?= ucwords(str_replace('_', ' ', $p['status'])) ?></span>
                                        </td>
                                        <td class="table-cell text-center">
                                            <?php if ($p['status_hadir'] === 'hadir'): ?>
                                                <span class="badge badge-emerald">Hadir</span>
                                            <?php elseif ($p['status_hadir'] === 'sakit'): ?>
                                                <span class="badge badge-blue">Sakit</span>
                                            <?php elseif ($p['status_hadir'] === 'izin'): ?>
                                                <span class="badge badge-blue">Izin</span>
                                            <?php elseif ($p['status_hadir'] === 'alfa'): ?>
                                                <span class="badge badge-gray">Alfa</span>
                                            <?php else: ?>
                                                <span class="text-slate-400 font-mono text-xs">Belum Dicatat</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
