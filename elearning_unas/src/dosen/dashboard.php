<?php
/**
 * Dashboard Dosen
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

// Require dosen role
requireRole('dosen');

$user = getCurrentUser();
$dosen = getDosenByUserId($conn, $user['id']);

if (!$dosen) {
  die('Data dosen tidak ditemukan');
}

$tahunAkademik = '2025/2026';
$kelasDosen = getKelasByDosenId($conn, $dosen['id'], $tahunAkademik);

// Calculate statistics
$totalKelas = count($kelasDosen);
$totalMahasiswa = 0;

foreach ($kelasDosen as $kelas) {
  $totalMahasiswa += $kelas['jumlah_mahasiswa'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Dosen - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Dashboard Dosen</h1>
      <p class="page-subtitle">Selamat datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-label">Total Kelas</div>
        <div class="stat-value"><?php echo $totalKelas; ?></div>
        <div class="stat-change">Tahun Akademik <?php echo $tahunAkademik; ?></div>
      </div>

      <div class="stat-card success">
        <div class="stat-label">Total Mahasiswa</div>
        <div class="stat-value"><?php echo $totalMahasiswa; ?></div>
        <div class="stat-change">Semester ini</div>
      </div>

      <div class="stat-card warning">
        <div class="stat-label">Jurusan</div>
        <div class="stat-value"><?php echo htmlspecialchars($dosen['nama_jurusan']); ?></div>
        <div class="stat-change">Sejak <?php echo $dosen['tahun_mulai_mengajar']; ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Status</div>
        <div class="stat-value"><?php echo ucfirst($dosen['status_akademik']); ?></div>
        <div class="stat-change">NIP: <?php echo htmlspecialchars($dosen['nip']); ?></div>
      </div>
    </div>

    <!-- Kelas List -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Daftar Kelas</h2>
      </div>
      <div class="card-body">
        <?php if (empty($kelasDosen)): ?>
          <p class="text-muted">Tidak ada kelas untuk tahun akademik ini</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Kode Kelas</th>
                <th>Matakuliah</th>
                <th>SKS</th>
                <th>Jadwal</th>
                <th>Ruangan</th>
                <th>Mahasiswa</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($kelasDosen as $kelas): ?>
                <tr>
                  <td><?php echo htmlspecialchars($kelas['kode_kelas']); ?></td>
                  <td><?php echo htmlspecialchars($kelas['nama_matkul']); ?></td>
                  <td><?php echo $kelas['sks']; ?></td>
                  <td><?php echo $kelas['hari'] . ' ' . $kelas['jam_mulai'] . '-' . $kelas['jam_selesai']; ?></td>
                  <td><?php echo htmlspecialchars($kelas['ruangan']); ?></td>
                  <td><?php echo $kelas['jumlah_mahasiswa']; ?>/<?php echo $kelas['kapasitas']; ?></td>
                  <td>
                    <a href="/elearning_unas/src/dosen/kelas.php?id=<?php echo $kelas['id']; ?>" class="btn btn-primary btn-sm">
                      Kelola
                    </a>
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
          <a href="/elearning_unas/src/dosen/kelas.php" class="btn btn-outline" style="justify-content: center;">
            Kelola Kelas
          </a>
          <a href="/elearning_unas/src/dosen/nilai.php" class="btn btn-outline" style="justify-content: center;">
            Input Nilai
          </a>
          <a href="/elearning_unas/src/dosen/absensi.php" class="btn btn-outline" style="justify-content: center;">
            Input Absensi
          </a>
          <a href="/elearning_unas/src/dosen/materi.php" class="btn btn-outline" style="justify-content: center;">
            Upload Materi
          </a>
        </div>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
