<?php
/**
 * Dashboard Admin
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

// Require admin role
requireRole('admin');

$user = getCurrentUser();

// Get statistics
$totalMahasiswa = fetchRow($conn, "SELECT COUNT(*) as count FROM mahasiswa")['count'];
$totalDosen = fetchRow($conn, "SELECT COUNT(*) as count FROM dosen")['count'];
$totalMatakuliah = fetchRow($conn, "SELECT COUNT(*) as count FROM matakuliah")['count'];
$totalKelas = fetchRow($conn, "SELECT COUNT(*) as count FROM kelas WHERE tahun_akademik = ?", ['2025/2026'], 's')['count'];

// Get recent activity
$recentActivity = getActivityLog($conn, 10);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Dashboard Administrator</h1>
      <p class="page-subtitle">Selamat datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-label">Total Mahasiswa</div>
        <div class="stat-value"><?php echo $totalMahasiswa; ?></div>
        <div class="stat-change">Aktif</div>
      </div>

      <div class="stat-card success">
        <div class="stat-label">Total Dosen</div>
        <div class="stat-value"><?php echo $totalDosen; ?></div>
        <div class="stat-change">Aktif</div>
      </div>

      <div class="stat-card warning">
        <div class="stat-label">Total Matakuliah</div>
        <div class="stat-value"><?php echo $totalMatakuliah; ?></div>
        <div class="stat-change">144 SKS</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Kelas Aktif</div>
        <div class="stat-value"><?php echo $totalKelas; ?></div>
        <div class="stat-change">Tahun 2025/2026</div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Aktivitas Terbaru Sistem</h2>
      </div>
      <div class="card-body">
        <?php if (empty($recentActivity)): ?>
          <p class="text-muted">Tidak ada aktivitas</p>
        <?php else: ?>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>User</th>
                <th>Aksi</th>
                <th>Deskripsi</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentActivity as $activity): ?>
                <tr>
                  <td><?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?></td>
                  <td><?php echo htmlspecialchars($activity['nama_lengkap'] ?? 'System'); ?></td>
                  <td>
                    <span class="badge badge-primary">
                      <?php echo htmlspecialchars($activity['action']); ?>
                    </span>
                  </td>
                  <td><?php echo htmlspecialchars($activity['description'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Management Links -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Manajemen Sistem</h2>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-2" style="gap: 1rem;">
          <a href="/elearning_unas/src/admin/user.php" class="btn btn-outline" style="justify-content: center;">
            Kelola User
          </a>
          <a href="/elearning_unas/src/admin/matakuliah.php" class="btn btn-outline" style="justify-content: center;">
            Matakuliah
          </a>
          <a href="/elearning_unas/src/admin/kelas.php" class="btn btn-outline" style="justify-content: center;">
            Kelas
          </a>
          <a href="/elearning_unas/src/admin/laporan.php" class="btn btn-outline" style="justify-content: center;">
            Laporan
          </a>
        </div>
      </div>
    </div>

    <!-- System Information -->
    <div class="card" style="margin-top: 2rem;">
      <div class="card-header">
        <h2 class="card-title">Informasi Sistem</h2>
      </div>
      <div class="card-body">
        <table class="table">
          <tbody>
            <tr>
              <td><strong>Nama Sistem</strong></td>
              <td>Sistem E-Learning Akademik</td>
            </tr>
            <tr>
              <td><strong>Versi</strong></td>
              <td>1.0.0</td>
            </tr>
            <tr>
              <td><strong>Tahun Akademik</strong></td>
              <td>2025/2026</td>
            </tr>
            <tr>
              <td><strong>Tanggal Sistem</strong></td>
              <td><?php echo date('d M Y H:i:s'); ?></td>
            </tr>
            <tr>
              <td><strong>PHP Version</strong></td>
              <td><?php echo phpversion(); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
