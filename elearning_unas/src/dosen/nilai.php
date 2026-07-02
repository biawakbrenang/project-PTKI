<?php
/**
 * Halaman Input Nilai - Dosen
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

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Nilai - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Input Nilai Mahasiswa</h1>
      <p class="page-subtitle">Masukkan nilai tugas, kuis, UTS, dan UAS</p>
    </div>

    <div class="card">
      <div class="card-body">
        <p class="text-muted">Fitur Input Nilai sedang dalam pengembangan. Silakan kembali ke dashboard untuk mengakses fitur lainnya.</p>
        <a href="/elearning_unas/src/dosen/dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">
          Kembali ke Dashboard
        </a>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
