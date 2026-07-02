<?php
/**
 * Halaman Materi Pembelajaran - Mahasiswa
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
  <title>Materi Pembelajaran - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Materi Pembelajaran</h1>
      <p class="page-subtitle">Download materi dari dosen pengampu</p>
    </div>

    <!-- Materi per Matakuliah -->
    <?php if (empty($krs)): ?>
      <div class="card">
        <div class="card-body">
          <p class="text-muted">Belum ada KRS. Silakan daftar KRS terlebih dahulu.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($krs as $k): ?>
        <?php if ($k['status_krs'] === 'diambil'): ?>
          <?php $materi = getMateriByKelasId($conn, $k['kelas_id']); ?>
          <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
              <h2 class="card-title"><?php echo htmlspecialchars($k['nama_matkul']); ?></h2>
            </div>
            <div class="card-body">
              <?php if (empty($materi)): ?>
                <p class="text-muted">Belum ada materi untuk kelas ini</p>
              <?php else: ?>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Pertemuan</th>
                      <th>Judul Materi</th>
                      <th>Tipe File</th>
                      <th>Ukuran</th>
                      <th>Tanggal Upload</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($materi as $m): ?>
                      <tr>
                        <td>
                          <?php if ($m['pertemuan']): ?>
                            Pertemuan <?php echo $m['pertemuan']; ?>
                          <?php else: ?>
                            <span class="text-muted">-</span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($m['judul_materi']); ?></td>
                        <td>
                          <span class="badge badge-primary">
                            <?php echo strtoupper($m['tipe_file']); ?>
                          </span>
                        </td>
                        <td><?php echo elearning.getFileSizeReadable($m['ukuran_file']); ?></td>
                        <td><?php echo date('d M Y', strtotime($m['created_at'])); ?></td>
                        <td>
                          <a href="/public/uploads/<?php echo htmlspecialchars($m['file_path']); ?>" class="btn btn-primary btn-sm" download>
                            Download
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
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
  <script>
    // Helper function to format file size
    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Update file sizes
    document.querySelectorAll('td').forEach(td => {
      if (td.textContent.match(/^\d+$/)) {
        const bytes = parseInt(td.textContent);
        if (bytes > 0) {
          td.textContent = formatFileSize(bytes);
        }
      }
    });
  </script>
</body>
</html>
