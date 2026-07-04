<?php
/**
 * ECO-LEARNING - Administrator Class Management
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

$msg = '';
$err = '';

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_class') {
    $matkul_id = sanitizeInput($_POST['matkul_id'] ?? '');
    $dosen_id = sanitizeInput($_POST['dosen_id'] ?? '');
    $kode_kelas = sanitizeInput($_POST['kode_kelas'] ?? '');
    $semester = sanitizeInput($_POST['semester'] ?? '');
    $hari = sanitizeInput($_POST['hari'] ?? '');
    $jam_mulai = sanitizeInput($_POST['jam_mulai'] ?? '');
    $jam_selesai = sanitizeInput($_POST['jam_selesai'] ?? '');
    $ruangan = sanitizeInput($_POST['ruangan'] ?? '');
    $kapasitas = sanitizeInput($_POST['kapasitas'] ?? 40);

    try {
        $stmtInsert = $pdo->prepare("
            INSERT INTO kelas (matakuliah_id, dosen_id, kode_kelas, semester, tahun_akademik, hari, jam_mulai, jam_selesai, ruangan, kapasitas)
            VALUES (:matkul, :dosen, :kode, :sem, '2025/2026', :hari, :mulai, :selesai, :ruang, :kap)
        ");
        $stmtInsert->execute([
            'matkul' => $matkul_id,
            'dosen' => $dosen_id,
            'kode' => $kode_kelas,
            'sem' => $semester,
            'hari' => $hari,
            'mulai' => $jam_mulai,
            'selesai' => $jam_selesai,
            'ruang' => $ruangan,
            'kap' => $kapasitas
        ]);
        $msg = "Kelas Baru $kode_kelas Berhasil Ditambahkan ke Basis Data!";
    } catch (PDOException $e) {
        $err = "Gagal Menambahkan Kelas: " . $e->getMessage();
    }
}

// Fetch lists of courses for selection
$courses = $pdo->query("SELECT id, kode_matkul, nama_matkul FROM matakuliah ORDER BY nama_matkul ASC")->fetchAll();

// Fetch lists of lecturers
$lecturers = $pdo->query("
    SELECT d.id, u.nama_lengkap 
    FROM dosen d
    JOIN users u ON d.user_id = u.id 
    ORDER BY u.nama_lengkap ASC
")->fetchAll();

// Fetch all classes with full JOIN details
$stmtAll = $pdo->query("
    SELECT k.*, m.nama_matkul, m.kode_matkul, m.sks, u.nama_lengkap AS nama_dosen
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    LEFT JOIN dosen d ON k.dosen_id = d.id
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY m.nama_matkul ASC
");
$classes = $stmtAll->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas - ECO-ACADEMIC</title>
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
                    <h1 class="page-title">Manajemen Kelas & Kurikulum</h1>
                    <p class="page-subtitle">Kelola pembagian kelas pararel, penugasan dosen pengajar, dan ruang perkuliahan.</p>
                </div>
            </div>

            <!-- Toast Alert Messages -->
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
                <!-- Class Creator Form -->
                <div class="card">
                    <h3 class="card-title mb-4">Tambah Kelas Paralel</h3>
                    <form action="" method="POST" class="form-stack">
                        <input type="hidden" name="action" value="add_class">
                        
                        <div>
                            <label class="form-label">Mata Kuliah Induk</label>
                            <select name="matkul_id" required class="form-input">
                                <option value="">-- Pilih Mata Kuliah --</option>
                                <?php foreach($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['kode_matkul']) ?> - <?= htmlspecialchars($course['nama_matkul']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Dosen Pengampu</label>
                            <select name="dosen_id" required class="form-input">
                                <option value="">-- Pilih Dosen Pengampu --</option>
                                <?php foreach($lecturers as $l): ?>
                                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama_lengkap']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Kode Kelas Paralel</label>
                                <input type="text" name="kode_kelas" required placeholder="Contoh: SI101-B" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Semester</label>
                                <input type="number" name="semester" required min="1" max="8" value="1" class="form-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="form-label">Hari</label>
                                <select name="hari" required class="form-input">
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai" required value="08:00" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai" required value="09:40" class="form-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Ruangan Kelas</label>
                                <input type="text" name="ruangan" required value="Ruang Laboratorium" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Kapasitas Maksimal</label>
                                <input type="number" name="kapasitas" required value="40" min="5" class="form-input">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary btn-block mt-4">
                            Simpan Kelas Baru
                        </button>
                    </form>
                </div>

                <!-- Classes Grid/Table -->
                <div class="card col-span-2">
                    <h3 class="card-title mb-4">Seluruh Kelas Paralel Terdaftar</h3>
                    
                    <div class="overflow-y-auto max-h-[600px]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="table-header">Kode Kelas</th>
                                    <th class="table-header">Mata Kuliah Induk</th>
                                    <th class="table-header">Dosen Pengampu</th>
                                    <th class="table-header">Ruang & Kapasitas</th>
                                    <th class="table-header">Jadwal Sesi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($classes as $c): ?>
                                    <tr class="table-row-hover">
                                        <td class="table-cell font-mono font-bold text-slate-900"><?= htmlspecialchars($c['kode_kelas']) ?></td>
                                        <td class="table-cell">
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($c['nama_matkul']) ?></div>
                                            <span class="text-[10px] font-mono text-slate-400"><?= htmlspecialchars($c['kode_matkul']) ?> (<?= $c['sks'] ?> SKS)</span>
                                        </td>
                                        <td class="table-cell text-slate-500"><?= htmlspecialchars($c['nama_dosen'] ?? 'Belum Ditentukan') ?></td>
                                        <td class="table-cell">
                                            <div class="text-slate-800"><?= htmlspecialchars($c['ruangan']) ?></div>
                                            <span class="text-[10px] font-mono text-emerald-600 font-bold"><?= $c['jumlah_mahasiswa'] ?> / <?= $c['kapasitas'] ?> Terisi</span>
                                        </td>
                                        <td class="table-cell">
                                            <span class="badge badge-blue">
                                                <?= htmlspecialchars($c['hari']) ?>, <?= date('H:i', strtotime($c['jam_mulai'])) ?> - <?= date('H:i', strtotime($c['jam_selesai'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
