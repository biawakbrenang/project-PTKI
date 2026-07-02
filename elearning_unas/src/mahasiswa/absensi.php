<?php
/**
 * Halaman Absensi - Mahasiswa
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

// Get current KRS
$tahunAkademik = '2025/2026';
$krs = getKRSByMahasiswaId($conn, $mahasiswa['id'], $tahunAkademik);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Rekap Absensi</h1>
      <p class="page-subtitle">Lihat kehadiran Anda di setiap kelas</p>
    </div>

    <!-- Absensi per Matakuliah -->
    <?php if (empty($krs)): ?>
      <div class="card">
        <div class="card-body">
          <p class="text-muted">Belum ada KRS. Silakan daftar KRS terlebih dahulu.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($krs as $k): ?>
        <?php if ($k['status_krs'] === 'diambil'): ?>
          <?php
            $absensi = getAbsensiByKrsId($conn, $k['id']);
            $totalHadir = count(array_filter($absensi, function($a) { return $a['status'] === 'hadir'; }));
            $totalIzin = count(array_filter($absensi, function($a) { return $a['status'] === 'izin'; }));
            $totalSakit = count(array_filter($absensi, function($a) { return $a['status'] === 'sakit'; }));
            $totalAlpa = count(array_filter($absensi, function($a) { return $a['status'] === 'alpa'; }));
            $totalPertemuan = count($absensi);
            $persentaseHadir = $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100, 1) : 0;
          ?>
          <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
              <h2 class="card-title"><?php echo htmlspecialchars($k['nama_matkul']); ?></h2>
            </div>
            <div class="card-body">
              <!-- Statistics -->
              <div class="grid grid-cols-4" style="gap: 1rem; margin-bottom: 2rem;">
                <div class="stat-card success">
                  <div class="stat-label">Hadir</div>
                  <div class="stat-value"><?php echo $totalHadir; ?></div>
                </div>
                <div class="stat-card warning">
                  <div class="stat-label">Izin</div>
                  <div class="stat-value"><?php echo $totalIzin; ?></div>
                </div>
                <div class="stat-card warning">
                  <div class="stat-label">Sakit</div>
                  <div class="stat-value"><?php echo $totalSakit; ?></div>
                </div>
                <div class="stat-card danger">
                  <div class="stat-label">Alpa</div>
                  <div class="stat-value"><?php echo $totalAlpa; ?></div>
                </div>
              </div>

              <!-- Persentase -->
              <div style="margin-bottom: 2rem;">
                <p style="margin-bottom: 0.5rem;"><strong>Persentase Kehadiran: <?php echo $persentaseHadir; ?>%</strong></p>
                <div style="background-color: var(--color-border); height: 20px; border-radius: var(--radius-md); overflow: hidden;">
                  <div style="background-color: var(--color-success); height: 100%; width: <?php echo $persentaseHadir; ?>%; transition: width 0.3s;"></div>
                </div>
              </div>

              <!-- Detail Absensi -->
              <?php if (empty($absensi)): ?>
                <p class="text-muted">Belum ada data absensi</p>
              <?php else: ?>
                <table class="table table-compact">
                  <thead>
                    <tr>
                      <th>Pertemuan</th>
                      <th>Tanggal</th>
                      <th>Status</th>
                      <th>Keterangan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($absensi as $a): ?>
                      <tr>
                        <td><?php echo $a['pertemuan']; ?></td>
                        <td><?php echo date('d M Y', strtotime($a['tanggal'])); ?></td>
                        <td>
                          <?php if ($a['status'] === 'hadir'): ?>
                            <span class="badge badge-success">Hadir</span>
                          <?php elseif ($a['status'] === 'izin'): ?>
                            <span class="badge badge-warning">Izin</span>
                          <?php elseif ($a['status'] === 'sakit'): ?>
                            <span class="badge badge-warning">Sakit</span>
                          <?php else: ?>
                            <span class="badge badge-danger">Alpa</span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($a['keterangan'] ?? '-'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
