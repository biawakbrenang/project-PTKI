<?php
/**
 * Halaman Nilai - Mahasiswa
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

// Get all KRS (including completed)
$allKrs = fetchAll($conn, "
  SELECT k.*, m.nama_matkul, m.sks, m.semester
  FROM krs k
  JOIN matakuliah m ON k.matakuliah_id = m.id
  WHERE k.mahasiswa_id = ?
  ORDER BY m.semester, m.nama_matkul
", [$mahasiswa['id']], 'i');

// Calculate GPA
$totalSks = 0;
$totalGradePoints = 0;

foreach ($allKrs as $krs) {
  if ($krs['status_krs'] === 'lulus' && $krs['grade']) {
    $totalSks += $krs['sks'];
    $totalGradePoints += calculateGPA($krs['grade']) * $krs['sks'];
  }
}

$gpa = $totalSks > 0 ? round($totalGradePoints / $totalSks, 2) : 0;

// Group by semester
$nilaiPerSemester = [];
foreach ($allKrs as $krs) {
  $semester = $krs['semester'];
  if (!isset($nilaiPerSemester[$semester])) {
    $nilaiPerSemester[$semester] = [];
  }
  $nilaiPerSemester[$semester][] = $krs;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nilai - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Nilai Akademik</h1>
      <p class="page-subtitle">Rekap nilai dan grade Anda</p>
    </div>

    <!-- Statistics -->
    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-label">IPK</div>
        <div class="stat-value"><?php echo number_format($gpa, 2); ?></div>
        <div class="stat-change">Cumulative GPA</div>
      </div>

      <div class="stat-card success">
        <div class="stat-label">Matakuliah Lulus</div>
        <div class="stat-value">
          <?php echo count(array_filter($allKrs, function($k) { return $k['status_krs'] === 'lulus'; })); ?>
        </div>
        <div class="stat-change">dari <?php echo count($allKrs); ?> matakuliah</div>
      </div>

      <div class="stat-card warning">
        <div class="stat-label">SKS Lulus</div>
        <div class="stat-value"><?php echo $totalSks; ?></div>
        <div class="stat-change">dari 144 SKS</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Status Akademik</div>
        <div class="stat-value"><?php echo ucfirst($mahasiswa['status_akademik']); ?></div>
        <div class="stat-change">Semester <?php echo $mahasiswa['semester_saat_ini']; ?></div>
      </div>
    </div>

    <!-- Nilai per Semester -->
    <?php if (empty($nilaiPerSemester)): ?>
      <div class="card" style="margin-top: 2rem;">
        <div class="card-body">
          <p class="text-muted">Belum ada nilai. Silakan daftar KRS terlebih dahulu.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($nilaiPerSemester as $semester => $krs): ?>
        <div class="card" style="margin-top: 2rem;">
          <div class="card-header">
            <h2 class="card-title">Semester <?php echo $semester; ?></h2>
          </div>
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Matakuliah</th>
                  <th>SKS</th>
                  <th>Nilai Akhir</th>
                  <th>Grade</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($krs as $k): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($k['nama_matkul']); ?></td>
                    <td><?php echo $k['sks']; ?></td>
                    <td>
                      <?php if ($k['nilai_akhir']): ?>
                        <?php echo number_format($k['nilai_akhir'], 2); ?>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($k['grade']): ?>
                        <span class="badge badge-primary"><?php echo $k['grade']; ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($k['status_krs'] === 'lulus'): ?>
                        <span class="badge badge-success">Lulus</span>
                      <?php elseif ($k['status_krs'] === 'gagal'): ?>
                        <span class="badge badge-danger">Gagal</span>
                      <?php elseif ($k['status_krs'] === 'diambil'): ?>
                        <span class="badge badge-warning">Diambil</span>
                      <?php else: ?>
                        <span class="badge"><?php echo ucfirst($k['status_krs']); ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Grade Scale -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Skala Penilaian</h2>
      </div>
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <th>Grade</th>
              <th>Rentang Nilai</th>
              <th>Bobot</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge badge-primary">A</span></td>
              <td>85 - 100</td>
              <td>4.0</td>
              <td>Sangat Memuaskan</td>
            </tr>
            <tr>
              <td><span class="badge badge-primary">A-</span></td>
              <td>80 - 84</td>
              <td>3.7</td>
              <td>Sangat Memuaskan</td>
            </tr>
            <tr>
              <td><span class="badge badge-primary">B+</span></td>
              <td>75 - 79</td>
              <td>3.3</td>
              <td>Memuaskan</td>
            </tr>
            <tr>
              <td><span class="badge badge-primary">B</span></td>
              <td>70 - 74</td>
              <td>3.0</td>
              <td>Memuaskan</td>
            </tr>
            <tr>
              <td><span class="badge badge-primary">B-</span></td>
              <td>65 - 69</td>
              <td>2.7</td>
              <td>Cukup Memuaskan</td>
            </tr>
            <tr>
              <td><span class="badge badge-primary">C+</span></td>
              <td>60 - 64</td>
              <td>2.3</td>
              <td>Cukup</td>
            </tr>
            <tr>
              <td><span class="badge badge-primary">C</span></td>
              <td>55 - 59</td>
              <td>2.0</td>
              <td>Cukup</td>
            </tr>
            <tr>
              <td><span class="badge badge-danger">D</span></td>
              <td>50 - 54</td>
              <td>1.0</td>
              <td>Kurang</td>
            </tr>
            <tr>
              <td><span class="badge badge-danger">E</span></td>
              <td>0 - 49</td>
              <td>0.0</td>
              <td>Gagal</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
