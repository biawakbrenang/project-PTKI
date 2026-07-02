<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin('mahasiswa');

$user = getCurrentUser();
$mahasiswa = getMahasiswaByUserId($conn, $user['id']);

if (!$mahasiswa) {
    die('Data mahasiswa tidak ditemukan');
}

// Get akademik info
$semester_info = getSemesterInfo($conn, $mahasiswa['id']);
$ipk = calculateIPK($conn, $mahasiswa['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa - E-Learning Akademik</title>
    <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    
    <div class="container-main">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
        
        <main class="content">
            <div class="page-header">
                <h1>Profil Mahasiswa</h1>
                <p>Informasi pribadi dan akademik Anda</p>
            </div>

            <div class="grid grid-2">
                <!-- Profil Pribadi -->
                <div class="card">
                    <div class="card-header">
                        <h2>Data Pribadi</h2>
                    </div>
                    <div class="card-body">
                        <div class="profile-photo">
                            <img src="https://via.placeholder.com/150" alt="Foto Profil" class="avatar-large">
                        </div>
                        <div class="info-group">
                            <label>Nama Lengkap</label>
                            <p><?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Nama Depan</label>
                            <p><?php echo htmlspecialchars($user['nama_depan']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Email</label>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Username</label>
                            <p><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Data Akademik -->
                <div class="card">
                    <div class="card-header">
                        <h2>Data Akademik</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <label>NIM</label>
                            <p><?php echo htmlspecialchars($mahasiswa['nim']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Jurusan</label>
                            <p><?php echo htmlspecialchars($mahasiswa['jurusan_nama']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Tahun Angkatan</label>
                            <p><?php echo htmlspecialchars($mahasiswa['tahun_angkatan']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Semester Saat Ini</label>
                            <p>Semester <?php echo htmlspecialchars($mahasiswa['semester_saat_ini']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Total SKS Diambil</label>
                            <p><?php echo htmlspecialchars($mahasiswa['total_sks_diambil']); ?> SKS</p>
                        </div>
                        <div class="info-group">
                            <label>IPK</label>
                            <p class="highlight"><?php echo number_format($ipk, 2); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Status Akademik</label>
                            <p><span class="badge badge-success"><?php echo ucfirst($mahasiswa['status_akademik']); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Akademik -->
            <div class="card">
                <div class="card-header">
                    <h2>Riwayat Akademik</h2>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Semester</th>
                                <th>SKS Diambil</th>
                                <th>IPK Semester</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $history = getAcademicHistory($conn, $mahasiswa['id']);
                            foreach ($history as $h):
                            ?>
                            <tr>
                                <td>Semester <?php echo $h['semester']; ?></td>
                                <td><?php echo $h['sks_diambil']; ?> SKS</td>
                                <td><?php echo number_format($h['ipk_semester'], 2); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($h['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ubah Password -->
            <div class="card">
                <div class="card-header">
                    <h2>Keamanan</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="../../src/auth/change_password.php" class="form">
                        <div class="form-group">
                            <label for="old_password">Password Lama</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../public/js/script.js"></script>
</body>
</html>
