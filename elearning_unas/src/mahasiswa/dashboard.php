<?php
/**
 * Dashboard Mahasiswa
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

// Require mahasiswa role
requireRole('mahasiswa');

$user = getCurrentUser();
$mahasiswa = getMahasiswaByUserId($conn, $user['id']);

if (!$mahasiswa) {
  die('Data mahasiswa tidak ditemukan');
}

// Get current KRS
$tahunAkademik = '2025/2026';
$krsAktif = getKRSByMahasiswaId($conn, $mahasiswa['id'], $tahunAkademik);

// Calculate statistics
$totalSksAktif = 0;
$totalMatkul = 0;

foreach ($krsAktif as $krs) {
  if ($krs['status_krs'] === 'diambil') {
    $totalSksAktif += $krs['sks'];
    $totalMatkul++;
  }
}

// Get today's schedule
$hariIni = strtolower(date('l'));
$hariMap = [
  'monday' => 'Senin',
  'tuesday' => 'Selasa',
  'wednesday' => 'Rabu',
  'thursday' => 'Kamis',
  'friday' => 'Jumat',
  'saturday' => 'Sabtu',
  'sunday' => 'Minggu'
];

$jadwalHariIni = array_filter($krsAktif, function($krs) use ($hariMap) {
  return strtolower($krs['hari']) === strtolower($hariMap[strtolower(date('l'))] ?? '');
});

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Dashboard Mahasiswa</h1>
      <p class="page-subtitle">Selamat datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-label">Total SKS Diambil</div>
        <div class="stat-value"><?php echo $totalSksAktif; ?></div>
        <div class="stat-change">dari 144 SKS</div>
      </div>

      <div class="stat-card success">
        <div class="stat-label">Matakuliah Aktif</div>
        <div class="stat-value"><?php echo $totalMatkul; ?></div>
        <div class="stat-change">semester <?php echo $mahasiswa['semester_saat_ini']; ?></div>
      </div>

      <div class="stat-card warning">
        <div class="stat-label">IPK</div>
        <div class="stat-value"><?php echo number_format($mahasiswa['ipk'], 2); ?></div>
        <div class="stat-change">Status: <?php echo ucfirst($mahasiswa['status_akademik']); ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Tahun Angkatan</div>
        <div class="stat-value"><?php echo $mahasiswa['tahun_angkatan']; ?></div>
        <div class="stat-change">Jurusan: <?php echo htmlspecialchars($mahasiswa['nama_jurusan']); ?></div>
      </div>
    </div>

    <!-- Today's Schedule -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Jadwal Hari Ini (<?php echo date('d M Y'); ?>)</h2>
      </div>
      <div class="card-body">
        <?php if (empty($jadwalHariIni)): ?>
          <p class="text-muted">Tidak ada jadwal kelas hari ini</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Matakuliah</th>
                <th>Jam</th>
                <th>Ruangan</th>
                <th>SKS</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($jadwalHariIni as $jadwal): ?>
                <tr>
                  <td><?php echo htmlspecialchars($jadwal['nama_matkul']); ?></td>
                  <td><?php echo $jadwal['jam_mulai'] . ' - ' . $jadwal['jam_selesai']; ?></td>
                  <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                  <td><?php echo $jadwal['sks']; ?> SKS</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Current KRS -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">KRS Aktif (<?php echo $tahunAkademik; ?>)</h2>
        <a href="/elearning_unas/src/mahasiswa/krs.php" class="btn btn-primary btn-sm">Lihat Semua</a>
      </div>
      <div class="card-body">
        <?php if (empty($krsAktif)): ?>
          <p class="text-muted">Belum ada KRS untuk semester ini</p>
        <?php else: ?>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Matakuliah</th>
                <th>SKS</th>
                <th>Status</th>
                <th>Nilai</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($krsAktif, 0, 5) as $krs): ?>
                <tr>
                  <td><?php echo htmlspecialchars($krs['nama_matkul']); ?></td>
                  <td><?php echo $krs['sks']; ?></td>
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
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Akses Cepat</h2>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-2" style="gap: 1rem;">
          <a href="/elearning_unas/src/mahasiswa/krs.php" class="btn btn-outline" style="justify-content: center;">
            Kelola KRS
          </a>
          <a href="/elearning_unas/src/mahasiswa/jadwal.php" class="btn btn-outline" style="justify-content: center;">
            Lihat Jadwal
          </a>
          <a href="/elearning_unas/src/mahasiswa/nilai.php" class="btn btn-outline" style="justify-content: center;">
            Lihat Nilai
          </a>
          <a href="/elearning_unas/src/mahasiswa/materi.php" class="btn btn-outline" style="justify-content: center;">
            Materi Pembelajaran
          </a>
        </div>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
