<?php
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['dosen']);
$dosen_id = $_SESSION['dosen_id'] ?? 0;
$classes = $pdo->prepare("SELECT k.*, m.nama_matkul, m.kode_matkul FROM kelas k JOIN matakuliah m ON k.matakuliah_id=m.id WHERE k.dosen_id=:id ORDER BY k.hari,k.jam_mulai");
$classes->execute(['id' => $dosen_id]);
$classes = $classes->fetchAll();
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Kelas Dosen</title><link rel="stylesheet" href="../assets/css/app.css"><script src="../assets/js/app.js" defer></script></head>
<body><?php include __DIR__ . '/../components/sidebar.php'; ?><div class="main-content"><?php include __DIR__ . '/../components/header.php'; ?><main class="page-container">
<div class="page-header"><div><h1 class="page-title">Kelas</h1><p class="page-subtitle">Classes assigned to this lecturer.</p></div></div>
<div class="card"><h3 class="card-title">Table Kelas</h3><div class="table-wrap"><table><thead><tr><th>Nama Kelas</th><th>Mata Kuliah</th><th>Jumlah Mahasiswa</th><th>Jadwal</th><th>Action</th></tr></thead><tbody>
<?php foreach ($classes as $c): ?><tr><td><?= htmlspecialchars($c['kode_kelas']) ?></td><td><?= htmlspecialchars($c['nama_matkul']) ?></td><td><?= $c['jumlah_mahasiswa'] ?></td><td><?= htmlspecialchars($c['hari']) ?>, <?= date('H:i', strtotime($c['jam_mulai'])) ?></td><td><a class="btn-primary btn-sm" href="materi_tugas.php?kelas_id=<?= $c['id'] ?>">View</a></td></tr><?php endforeach; ?>
</tbody></table></div></div></main></div></body></html>
