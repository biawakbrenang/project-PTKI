<?php
/**
 * ECO-LEARNING - Lecturer Materials & Assignment Upload Centre
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);

$dosen_id = $_SESSION['dosen_id'] ?? 0;
$kelas_id = sanitizeInput($_GET['kelas_id'] ?? '');

$msg = '';
$err = '';

// Handle Material creation with real file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_materi') {
    $pertemuan_id = sanitizeInput($_POST['pertemuan_id'] ?? '');
    $judul_materi = sanitizeInput($_POST['judul_materi'] ?? '');
    $deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');

    if (!isset($_FILES['file_materi']) || $_FILES['file_materi']['error'] !== UPLOAD_ERR_OK) {
        $err = "Silakan pilih berkas materi yang akan diunggah.";
    } else {
        $file = $_FILES['file_materi'];
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $err = "Tipe berkas tidak diizinkan. Gunakan PDF/DOC/PPT/XLS/ZIP/gambar.";
        } elseif ($file['size'] > 50 * 1024 * 1024) {
            $err = "Ukuran berkas melebihi batas 50 MB.";
        } else {
            $uploadDir = __DIR__ . '/../uploads/materi/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $storedName = 'materi_' . $dosen_id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
                try {
                    $stmtMateri = $pdo->prepare("
                        INSERT INTO materi_pertemuan (pertemuan_id, judul_materi, deskripsi, file_path, file_name, file_size, tipe_file)
                        VALUES (:pertemuan, :judul, :deskripsi, :path, :file_name, :size, :tipe)
                    ");
                    $stmtMateri->execute([
                        'pertemuan' => $pertemuan_id,
                        'judul' => $judul_materi,
                        'deskripsi' => $deskripsi,
                        'path' => 'uploads/materi/' . $storedName,
                        'file_name' => $file['name'],
                        'size' => $file['size'],
                        'tipe' => $ext
                    ]);
                    $msg = "Bahan Ajar Materi Baru Berhasil Dipublikasikan!";
                } catch (PDOException $e) {
                    $err = "Gagal Mengunggah Materi: " . $e->getMessage();
                }
            } else {
                $err = "Gagal mengunggah berkas ke server.";
            }
        }
    }
}

// Handle Assignment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tugas') {
    $pertemuan_id = sanitizeInput($_POST['pertemuan_id'] ?? '');
    $judul_tugas = sanitizeInput($_POST['judul_tugas'] ?? '');
    $deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');
    $deadline = sanitizeInput($_POST['tanggal_deadline'] ?? '');
    $bobot = sanitizeInput($_POST['bobot_nilai'] ?? 10);

    try {
        $stmtTugas = $pdo->prepare("
            INSERT INTO tugas_pertemuan (pertemuan_id, judul_tugas, deskripsi, tanggal_deadline, bobot_nilai, status)
            VALUES (:pertemuan, :judul, :deskripsi, :deadline, :bobot, 'aktif')
        ");
        $stmtTugas->execute([
            'pertemuan' => $pertemuan_id,
            'judul' => $judul_tugas,
            'deskripsi' => $deskripsi,
            'deadline' => $deadline,
            'bobot' => $bobot
        ]);
        $msg = "Penugasan Mahasiswa Baru Berhasil Dipublikasikan!";
    } catch (PDOException $e) {
        $err = "Gagal Menambahkan Tugas: " . $e->getMessage();
    }
}

// Fetch all classes of this lecturer for dropdown selection
$stmtClasses = $pdo->prepare("
    SELECT k.id, k.kode_kelas, m.nama_matkul 
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.dosen_id = :dosen
");
$stmtClasses->execute(['dosen' => $dosen_id]);
$my_classes = $stmtClasses->fetchAll();

// Fetch sessions (pertemuan) associated with current class
$sessions = [];
if (!empty($kelas_id)) {
    $stmtSess = $pdo->prepare("SELECT * FROM pertemuan WHERE kelas_id = :kelas_id ORDER BY nomor_pertemuan ASC");
    $stmtSess->execute(['kelas_id' => $kelas_id]);
    $sessions = $stmtSess->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Materi & Tugas - ECO-LEARNING</title>
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
                    <h1 class="page-title">Publikasi Bahan Ajar & Penugasan</h1>
                    <p class="page-subtitle">Unggah berkas modul perkuliahan, slide presentasi, dan buat tugas bagi mahasiswa per pertemuan.</p>
                </div>
            </div>

            <!-- Toast alert message block -->
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
                <!-- Class Selection Side Panel -->
                <div class="card space-y-4">
                    <h3 class="card-title">Pilih Sesi Kelas</h3>
                    <form action="" method="GET">
                        <div>
                            <label class="form-label mb-1">Kelas Aktif Anda</label>
                            <select name="kelas_id" required onchange="this.form.submit()" class="form-input">
                                <option value="">-- Pilih Sesi Kelas --</option>
                                <?php foreach($my_classes as $myc): ?>
                                    <option value="<?= $myc['id'] ?>" <?= $kelas_id == $myc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($myc['kode_kelas']) ?> - <?= htmlspecialchars($myc['nama_matkul']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Direct Meeting timeline links if loaded -->
                    <?php if (!empty($kelas_id)): ?>
                        <div class="pt-4 border-t border-slate-100">
                            <span class="text-slate-400 text-[9px] font-bold font-mono uppercase tracking-wider block mb-2">Daftar Pertemuan Kelas</span>
                            <div class="space-y-1.5 overflow-y-auto max-h-[300px]">
                                <?php foreach($sessions as $sess): ?>
                                    <div class="p-3 bg-slate-50 hover:bg-emerald-50/20 border border-slate-100 rounded-xl transition-all flex items-center justify-between text-xs font-semibold text-slate-700">
                                        <div>
                                            <span class="text-[9px] font-mono text-emerald-600 block">PERTEMUAN <?= $sess['nomor_pertemuan'] ?></span>
                                            <?= htmlspecialchars($sess['judul_pertemuan']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Forms split (Materials Creator & Assignments Creator) -->
                <div class="card col-span-2 flex-col-stack">
                    <?php if (empty($kelas_id)): ?>
                        <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-center space-y-3">
                            <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-xs font-semibold">Silakan pilih salah satu kelas di panel kiri untuk membuat materi atau tugas.</span>
                        </div>
                    <?php else: ?>
                        <!-- Form 1: Add Modul / Slide Materi -->
                        <div>
                            <h3 class="card-title mb-4 pb-2 border-b border-slate-100">I. Unggah Materi Bahan Ajar</h3>
                            <form action="" method="POST" enctype="multipart/form-data" class="form-stack">
                                <input type="hidden" name="action" value="add_materi">
                                <div class="two-col-grid">
                                    <div>
                                        <label class="form-label">Hubungkan Sesi Pertemuan</label>
                                        <select name="pertemuan_id" required class="form-input">
                                            <option value="">-- Pilih Pertemuan --</option>
                                            <?php foreach($sessions as $sess): ?>
                                                <option value="<?= $sess['id'] ?>">Pertemuan <?= $sess['nomor_pertemuan'] ?>: <?= htmlspecialchars($sess['judul_pertemuan']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Judul Bahan Ajar</label>
                                        <input type="text" name="judul_materi" required placeholder="Contoh: Modul 1 - Pemrograman Dasar" class="form-input">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Deskripsi Singkat / Instruksi Membaca</label>
                                    <textarea name="deskripsi" placeholder="Tulis catatan kecil bahan ajar ini..." rows="2" class="form-input"></textarea>
                                </div>
                                <div class="two-col-grid-align-end">
                                    <div>
                                        <label class="form-label">Berkas Materi (PDF/DOC/PPT/ZIP)</label>
                                        <input type="file" name="file_materi" required class="form-input">
                                    </div>
                                    <div>
                                        <button type="submit" class="btn-primary btn-block">
                                            Publikasikan Berkas Bahan Ajar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Form 2: Create Assignment -->
                        <div class="pt-8 border-t border-slate-100">
                            <h3 class="card-title mb-4 pb-2 border-b border-slate-100">II. Terbitkan Penugasan Pertemuan</h3>
                            <form action="" method="POST" class="form-stack">
                                <input type="hidden" name="action" value="add_tugas">
                                <div class="three-col-grid">
                                    <div>
                                        <label class="form-label">Pertemuan Terkait</label>
                                        <select name="pertemuan_id" required class="form-input">
                                            <option value="">-- Pilih Pertemuan --</option>
                                            <?php foreach($sessions as $sess): ?>
                                                <option value="<?= $sess['id'] ?>">Pertemuan <?= $sess['nomor_pertemuan'] ?>: <?= htmlspecialchars($sess['judul_pertemuan']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Judul Penugasan</label>
                                        <input type="text" name="judul_tugas" required placeholder="Contoh: Tugas Individu 1 - OOP" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label">Batas Pengumpulan (Deadline)</label>
                                        <input type="datetime-local" name="tanggal_deadline" required class="form-input font-mono">
                                    </div>
                                </div>
                                <div class="three-col-grid-complex">
                                    <div class="col-span-2">
                                        <label class="form-label">Detail Deskripsi Soal Tugas</label>
                                        <textarea name="deskripsi" placeholder="Berikan instruksi soal / kriteria pengumpulan file ZIP..." rows="3" class="form-input"></textarea>
                                    </div>
                                    <div class="form-col-align-between">
                                        <div>
                                            <label class="form-label">Bobot Nilai (%)</label>
                                            <input type="number" name="bobot_nilai" required value="10" min="5" max="100" class="form-input font-mono">
                                        </div>
                                        <button type="submit" class="btn-primary btn-block">
                                            Terbitkan Tugas Baru
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
