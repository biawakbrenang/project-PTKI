<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireRole('dosen');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Materi - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main>
    <div class="page-header">
      <h1 class="page-title">Upload Materi Pembelajaran</h1>
      <p class="page-subtitle">Bagikan modul, referensi, dan file pembelajaran ke mahasiswa.</p>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Materi Kelas</h2>
      </div>
      <div class="card-body">
        <p class="text-muted">Fitur Upload Materi sedang dalam pengembangan.</p>
        <a href="/elearning_unas/src/dosen/dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">Kembali</a>
      </div>
    </div>
  </main>

  <script src="/elearning_unas/public/js/script.js"></script>
</body>
</html>
