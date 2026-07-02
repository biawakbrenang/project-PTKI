<?php
/**
 * Halaman KRS (Kartu Rencana Studi) - Mahasiswa
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireRole('mahasiswa');

$user = getCurrentUser();
$mahasiswa = getMahasiswaByUserId($conn, $user['id']);

if (!$mahasiswa) {
  die('Data mahasiswa tidak ditemukan');
}

$tahunAkademik = '2025/2026';
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'add_krs') {
    $matakuliahId = (int)($_POST['matakuliah_id'] ?? 0);
    $kelas = fetchRow($conn, "
      SELECT id, semester
      FROM kelas
      WHERE matakuliah_id = ? AND tahun_akademik = ?
      ORDER BY jumlah_mahasiswa ASC, id ASC
      LIMIT 1
    ", [$matakuliahId, $tahunAkademik], 'is');

    $existing = fetchRow($conn, "
      SELECT id, status_krs
      FROM krs
      WHERE mahasiswa_id = ? AND matakuliah_id = ? AND tahun_akademik = ?
      LIMIT 1
    ", [$mahasiswa['id'], $matakuliahId, $tahunAkademik], 'iis');

    if (!$kelas) {
      $message = 'Kelas untuk matakuliah ini belum tersedia.';
      $messageType = 'danger';
    } elseif ($existing && $existing['status_krs'] !== 'batal') {
      $message = 'Matakuliah sudah ada di KRS.';
      $messageType = 'warning';
    } elseif ($existing) {
      updateRecord($conn, 'krs', [
        'kelas_id' => $kelas['id'],
        'status_krs' => 'diambil',
      ], 'id = ? AND mahasiswa_id = ?', [$existing['id'], $mahasiswa['id']], 'ii');
      $message = 'Matakuliah berhasil diaktifkan kembali.';
      $messageType = 'success';
    } else {
      insertRecord($conn, 'krs', [
        'mahasiswa_id' => $mahasiswa['id'],
        'matakuliah_id' => $matakuliahId,
        'kelas_id' => $kelas['id'],
        'semester' => $kelas['semester'],
        'tahun_akademik' => $tahunAkademik,
        'status_krs' => 'diambil',
      ]);
      $message = 'Matakuliah berhasil ditambahkan ke KRS.';
      $messageType = 'success';
    }
  }

  if ($action === 'cancel_krs') {
    $krsId = (int)($_POST['krs_id'] ?? 0);
    $updated = updateRecord($conn, 'krs', [
      'status_krs' => 'batal',
    ], 'id = ? AND mahasiswa_id = ? AND status_krs = ?', [$krsId, $mahasiswa['id'], 'diambil'], 'iis');

    $message = $updated ? 'KRS berhasil dibatalkan.' : 'KRS tidak dapat dibatalkan.';
    $messageType = $updated ? 'success' : 'danger';
  }
}

// Get all matakuliah
$allMatakuliah = fetchAll($conn, "
  SELECT m.*, j.nama_jurusan
  FROM matakuliah m
  JOIN jurusan j ON m.jurusan_id = j.id
  WHERE m.jurusan_id = ?
  ORDER BY m.semester, m.nama_matkul
", [$mahasiswa['jurusan_id']], 'i');

// Get current KRS
$currentKrs = getKRSByMahasiswaId($conn, $mahasiswa['id'], $tahunAkademik);
$activeKrs = array_values(array_filter($currentKrs, function($krs) {
  return $krs['status_krs'] !== 'batal';
}));
$currentKrsIds = array_column($activeKrs, 'matakuliah_id');

// Group matakuliah by semester
$matkuPerSemester = [];
foreach ($allMatakuliah as $mk) {
  $semester = $mk['semester'];
  if (!isset($matkuPerSemester[$semester])) {
    $matkuPerSemester[$semester] = [];
  }
  $matkuPerSemester[$semester][] = $mk;
}

// Calculate statistics
$totalSksAmbil = array_sum(array_map(function($krs) {
  return $krs['status_krs'] === 'diambil' ? $krs['sks'] : 0;
}, $activeKrs));

$totalSksPending = 144 - $totalSksAmbil;

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KRS - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Kartu Rencana Studi (KRS)</h1>
      <p class="page-subtitle">Kelola matakuliah yang akan Anda ambil</p>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-label">SKS Diambil</div>
        <div class="stat-value"><?php echo $totalSksAmbil; ?></div>
        <div class="stat-change">dari 144 SKS</div>
      </div>

      <div class="stat-card success">
        <div class="stat-label">SKS Tersisa</div>
        <div class="stat-value"><?php echo $totalSksPending; ?></div>
        <div class="stat-change">belum diambil</div>
      </div>

      <div class="stat-card warning">
        <div class="stat-label">Semester</div>
        <div class="stat-value"><?php echo $mahasiswa['semester_saat_ini']; ?></div>
        <div class="stat-change">Jurusan: <?php echo htmlspecialchars($mahasiswa['nama_jurusan']); ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Status KRS</div>
        <div class="stat-value"><?php echo count($activeKrs); ?></div>
        <div class="stat-change">matakuliah diambil</div>
      </div>
    </div>

    <!-- Current KRS -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">KRS Aktif (<?php echo $tahunAkademik; ?>)</h2>
      </div>
      <div class="card-body">
        <?php if (empty($activeKrs)): ?>
          <p class="text-muted">Belum ada KRS. Silakan tambahkan matakuliah di bawah.</p>
        <?php else: ?>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Matakuliah</th>
                <th>SKS</th>
                <th>Semester</th>
                <th>Status</th>
                <th>Nilai</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activeKrs as $krs): ?>
                <tr>
                  <td><?php echo htmlspecialchars($krs['kode_matkul'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($krs['nama_matkul']); ?></td>
                  <td><?php echo $krs['sks']; ?></td>
                  <td><?php echo $krs['semester']; ?></td>
                  <td>
                    <span class="badge badge-primary">
                      <?php echo ucfirst($krs['status_krs']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($krs['nilai_akhir']): ?>
                      <?php echo $krs['nilai_akhir']; ?> (<?php echo $krs['grade']; ?>)
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($krs['status_krs'] === 'diambil'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="cancel_krs">
                        <input type="hidden" name="krs_id" value="<?php echo $krs['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Batalkan KRS ini?')">
                        Batal
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Available Matakuliah -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Daftar Matakuliah Tersedia</h2>
      </div>
      <div class="card-body">
        <?php foreach ($matkuPerSemester as $semester => $matkul): ?>
          <div style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--color-primary);">
              Semester <?php echo $semester; ?>
            </h3>
            <table class="table table-compact">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Matakuliah</th>
                  <th>SKS</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($matkul as $mk): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($mk['kode_matkul']); ?></td>
                    <td><?php echo htmlspecialchars($mk['nama_matkul']); ?></td>
                    <td><?php echo $mk['sks']; ?></td>
                    <td>
                      <?php if (in_array($mk['id'], $currentKrsIds)): ?>
                        <span class="badge badge-success">Diambil</span>
                      <?php else: ?>
                        <span class="badge badge-warning">Tersedia</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!in_array($mk['id'], $currentKrsIds)): ?>
                        <form method="POST" style="display:inline;">
                          <input type="hidden" name="action" value="add_krs">
                          <input type="hidden" name="matakuliah_id" value="<?php echo $mk['id']; ?>">
                          <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Ambil matakuliah: <?php echo htmlspecialchars($mk['nama_matkul'], ENT_QUOTES); ?>?')">
                          Ambil
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <script src="../../public/js/script.js"></script>
</body>
</html>
