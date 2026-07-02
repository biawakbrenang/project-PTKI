<?php
/**
 * Halaman Jadwal Kelas - Mahasiswa
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
$jadwal = getKRSByMahasiswaId($conn, $mahasiswa['id'], $tahunAkademik);

// Group by day
$hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$jadwalPerHari = [];

foreach ($jadwal as $j) {
  if ($j['status_krs'] === 'diambil') {
    $hari = $j['hari'];
    if (!isset($jadwalPerHari[$hari])) {
      $jadwalPerHari[$hari] = [];
    }
    $jadwalPerHari[$hari][] = $j;
  }
}

// Sort by day order
$jadwalSorted = [];
foreach ($hariOrder as $hari) {
  if (isset($jadwalPerHari[$hari])) {
    $jadwalSorted[$hari] = $jadwalPerHari[$hari];
    usort($jadwalSorted[$hari], function($a, $b) {
      return strcmp($a['jam_mulai'], $b['jam_mulai']);
    });
  }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jadwal Kelas - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Jadwal Kelas</h1>
      <p class="page-subtitle">Jadwal pembelajaran <?php echo $tahunAkademik; ?></p>
    </div>

    <!-- Statistics -->
    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-label">Total Kelas</div>
        <div class="stat-value"><?php echo count($jadwal); ?></div>
        <div class="stat-change">semester <?php echo $mahasiswa['semester_saat_ini']; ?></div>
      </div>

      <div class="stat-card success">
        <div class="stat-label">Hari Aktif</div>
        <div class="stat-value"><?php echo count($jadwalSorted); ?></div>
        <div class="stat-change">hari per minggu</div>
      </div>
    </div>

    <!-- Jadwal per Hari -->
    <?php if (empty($jadwalSorted)): ?>
      <div class="card" style="margin-top: 2rem;">
        <div class="card-body">
          <p class="text-muted">Belum ada jadwal kelas. Silakan daftar KRS terlebih dahulu.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($jadwalSorted as $hari => $kelasHari): ?>
        <div class="card" style="margin-top: 2rem;">
          <div class="card-header">
            <h2 class="card-title"><?php echo $hari; ?></h2>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Jam</th>
                  <th>Matakuliah</th>
                  <th>Ruangan</th>
                  <th>SKS</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($kelasHari as $kelas): ?>
                  <tr>
                    <td>
                      <strong><?php echo $kelas['jam_mulai'] . ' - ' . $kelas['jam_selesai']; ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($kelas['nama_matkul']); ?></td>
                    <td><?php echo htmlspecialchars($kelas['ruangan']); ?></td>
                    <td><?php echo $kelas['sks']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Summary -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Ringkasan Jadwal</h2>
      </div>
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <th>Matakuliah</th>
              <th>Hari</th>
              <th>Jam</th>
              <th>Ruangan</th>
              <th>SKS</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($jadwal as $j): ?>
              <?php if ($j['status_krs'] === 'diambil'): ?>
                <tr>
                  <td><?php echo htmlspecialchars($j['nama_matkul']); ?></td>
                  <td><?php echo $j['hari']; ?></td>
                  <td><?php echo $j['jam_mulai'] . ' - ' . $j['jam_selesai']; ?></td>
                  <td><?php echo htmlspecialchars($j['ruangan']); ?></td>
                  <td><?php echo $j['sks']; ?></td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
