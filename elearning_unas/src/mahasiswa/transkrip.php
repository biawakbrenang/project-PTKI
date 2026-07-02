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

$ipk = calculateIPK($conn, $mahasiswa['id']);
$transkrip = getTranskrip($conn, $mahasiswa['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Akademik - E-Learning Akademik</title>
    <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    
    <div class="container-main">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
        
        <main class="content">
            <div class="page-header">
                <h1>Transkrip Akademik</h1>
                <p>Riwayat lengkap nilai dan IPK Anda</p>
                <button class="btn btn-secondary" onclick="window.print()">Cetak Transkrip</button>
            </div>

            <!-- Ringkasan -->
            <div class="grid grid-4">
                <div class="card-stat">
                    <div class="stat-value"><?php echo number_format($ipk, 2); ?></div>
                    <div class="stat-label">IPK Kumulatif</div>
                </div>
                <div class="card-stat">
                    <div class="stat-value"><?php echo count($transkrip); ?></div>
                    <div class="stat-label">Matakuliah Diambil</div>
                </div>
                <div class="card-stat">
                    <div class="stat-value"><?php echo getTotalSKSCompleted($conn, $mahasiswa['id']); ?></div>
                    <div class="stat-label">Total SKS Selesai</div>
                </div>
                <div class="card-stat">
                    <div class="stat-value"><?php echo getGradeDistribution($conn, $mahasiswa['id'])['A'] ?? 0; ?></div>
                    <div class="stat-label">Nilai A</div>
                </div>
            </div>

            <!-- Transkrip Detail -->
            <div class="card">
                <div class="card-header">
                    <h2>Detail Transkrip</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode Matakuliah</th>
                                <th>Nama Matakuliah</th>
                                <th>SKS</th>
                                <th>Semester</th>
                                <th>Nilai</th>
                                <th>Grade</th>
                                <th>Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_bobot = 0;
                            $total_sks = 0;
                            foreach ($transkrip as $t):
                                $bobot = calculateGPA($t['grade']) * $t['sks'];
                                $total_bobot += $bobot;
                                $total_sks += $t['sks'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['kode_matkul']); ?></td>
                                <td><?php echo htmlspecialchars($t['nama_matkul']); ?></td>
                                <td><?php echo $t['sks']; ?></td>
                                <td><?php echo $t['semester']; ?></td>
                                <td><?php echo $t['nilai_akhir'] ?? '-'; ?></td>
                                <td><span class="badge badge-info"><?php echo $t['grade'] ?? '-'; ?></span></td>
                                <td><?php echo number_format($bobot, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistik Nilai -->
            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h2>Distribusi Grade</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        $grades = getGradeDistribution($conn, $mahasiswa['id']);
                        foreach (['A', 'B', 'C', 'D', 'E'] as $grade):
                            $count = $grades[$grade] ?? 0;
                            $percentage = ($count / count($transkrip)) * 100;
                        ?>
                        <div class="progress-item">
                            <label><?php echo $grade; ?></label>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span><?php echo $count; ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Status Akademik</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <label>Status</label>
                            <p><span class="badge badge-success"><?php echo ucfirst($mahasiswa['status_akademik']); ?></span></p>
                        </div>
                        <div class="info-group">
                            <label>IPK Kumulatif</label>
                            <p class="highlight"><?php echo number_format($ipk, 2); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Total SKS Selesai</label>
                            <p><?php echo getTotalSKSCompleted($conn, $mahasiswa['id']); ?> / 144 SKS</p>
                        </div>
                        <div class="info-group">
                            <label>Sisa SKS</label>
                            <p><?php echo 144 - getTotalSKSCompleted($conn, $mahasiswa['id']); ?> SKS</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../public/js/script.js"></script>
</body>
</html>
