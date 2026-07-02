<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin('admin');

$users = getAllUsers($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola User - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>

  <div class="container-main">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="content">
      <div class="page-header">
        <h1>Kelola User</h1>
        <p>Manajemen data pengguna sistem</p>
      </div>

      <div class="card">
        <div class="card-body">
          <div class="filter-group">
            <input type="text" id="searchUser" placeholder="Cari user..." class="form-control">
            <select id="filterRole" class="form-control">
              <option value="">Semua Role</option>
              <option value="mahasiswa">Mahasiswa</option>
              <option value="dosen">Dosen</option>
              <option value="admin">Admin</option>
            </select>
            <select id="filterStatus" class="form-control">
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Daftar User</h2>
        </div>
        <div class="card-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?php echo htmlspecialchars($u['username']); ?></td>
                  <td><?php echo htmlspecialchars($u['nama_lengkap']); ?></td>
                  <td><?php echo htmlspecialchars($u['email'] ?? '-'); ?></td>
                  <td><span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span></td>
                  <td>
                    <span class="badge badge-<?php echo $u['status'] === 'aktif' ? 'success' : 'danger'; ?>">
                      <?php echo ucfirst($u['status']); ?>
                    </span>
                  </td>
                  <td><span class="text-muted">Belum tersedia</span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="../../public/js/script.js"></script>
</body>
</html>
