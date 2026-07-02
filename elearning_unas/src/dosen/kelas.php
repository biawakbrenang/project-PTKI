<?php
/**
 * Halaman Kelola Kelas - Dosen
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireRole('dosen');

$user = getCurrentUser();
$dosen = getDosenByUserId($conn, $user['id']);

if (!$dosen) {
  die('Data dosen tidak ditemukan');
}

// Get kelas
$tahunAkademik = '2025/2026';
$kelasDosen = getKelasByDosenId($conn, $dosen['id'], $tahunAkademik);

// Get selected kelas if viewing details
$selectedKelasId = $_GET['id'] ?? null;
$selectedKelas = null;
$mahasiswaKelas = [];

if ($selectedKelasId) {
  $selectedKelas = fetchRow($conn, "
    SELECT k.*, m.nama_matkul, m.sks
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.id = ? AND k.dosen_id = ?
  ", [$selectedKelasId, $dosen['id']], 'ii');

  if ($selectedKelas) {
    $mahasiswaKelas = getMahasiswaInKelas($conn, $selectedKelasId);
  }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Kelas - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Kelola Kelas</h1>
      <p class="page-subtitle">Kelola daftar mahasiswa dan materi pembelajaran</p>
    </div>

    <?php if ($selectedKelas): ?>
      <!-- Detail Kelas -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title"><?php echo htmlspecialchars($selectedKelas['nama_matkul']); ?></h2>
          <a href="/elearning_unas/src/dosen/kelas.php" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-2" style="gap: 2rem; margin-bottom: 2rem;">
            <div>
              <p><strong>Kode Kelas:</strong> <?php echo htmlspecialchars($selectedKelas['kode_kelas']); ?></p>
              <p><strong>SKS:</strong> <?php echo $selectedKelas['sks']; ?></p>
              <p><strong>Semester:</strong> <?php echo $selectedKelas['semester']; ?></p>
            </div>
            <div>
              <p><strong>Hari:</strong> <?php echo $selectedKelas['hari']; ?></p>
              <p><strong>Jam:</strong> <?php echo $selectedKelas['jam_mulai'] . ' - ' . $selectedKelas['jam_selesai']; ?></p>
              <p><strong>Ruangan:</strong> <?php echo htmlspecialchars($selectedKelas['ruangan']); ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Daftar Mahasiswa -->
      <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
          <h2 class="card-title">Daftar Mahasiswa (<?php echo count($mahasiswaKelas); ?>)</h2>
        </div>
        <div class="card-body">
          <?php if (empty($mahasiswaKelas)): ?>
            <p class="text-muted">Belum ada mahasiswa terdaftar di kelas ini</p>
          <?php else: ?>
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>NIM</th>
                  <th>Nama Mahasiswa</th>
                  <th>Nilai Akhir</th>
                  <th>Grade</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mahasiswaKelas as $index => $mhs): ?>
                  <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($mhs['nim']); ?></td>
                    <td><?php echo htmlspecialchars($mhs['nama_lengkap']); ?></td>
                    <td>
                      <?php if ($mhs['nilai_akhir']): ?>
                        <?php echo number_format($mhs['nilai_akhir'], 2); ?>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($mhs['grade']): ?>
                        <span class="badge badge-primary"><?php echo $mhs['grade']; ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="/elearning_unas/src/dosen/nilai.php?krs_id=<?php echo $mhs['krs_id']; ?>" class="btn btn-primary btn-sm">
                        Input Nilai
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Aksi Cepat -->
      <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
          <h2 class="card-title">Aksi Cepat</h2>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-2" style="gap: 1rem;">
            <a href="/elearning_unas/src/dosen/absensi.php?kelas_id=<?php echo $selectedKelas['id']; ?>" class="btn btn-outline" style="justify-content: center;">
              ✓ Input Absensi
            </a>
            <a href="/elearning_unas/src/dosen/materi.php?kelas_id=<?php echo $selectedKelas['id']; ?>" class="btn btn-outline" style="justify-content: center;">
              📤 Upload Materi
            </a>
            <a href="/elearning_unas/src/dosen/pengumuman.php?kelas_id=<?php echo $selectedKelas['id']; ?>" class="btn btn-outline" style="justify-content: center;">
              📢 Buat Pengumuman
            </a>
            <a href="/elearning_unas/src/dosen/nilai.php?kelas_id=<?php echo $selectedKelas['id']; ?>" class="btn btn-outline" style="justify-content: center;">
              📝 Input Nilai Massal
            </a>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- Daftar Kelas -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Kelas Saya (<?php echo $tahunAkademik; ?>)</h2>
        </div>
        <div class="card-body">
          <?php if (empty($kelasDosen)): ?>
            <p class="text-muted">Belum ada kelas untuk tahun akademik ini</p>
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
    <?php endif; ?>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
