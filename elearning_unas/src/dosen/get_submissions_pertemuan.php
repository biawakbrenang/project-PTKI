<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin();
requireRole(['dosen']);

$tugas_id = intval($_GET['tugas_id'] ?? 0);

if ($tugas_id <= 0) {
  die('Invalid tugas_id');
}

$dosen = getDosenByUserId($conn, $_SESSION['user_id']);
$tugas = fetchRow($conn, "
  SELECT tp.id
  FROM tugas_pertemuan tp
  JOIN pertemuan p ON tp.pertemuan_id = p.id
  JOIN kelas k ON p.kelas_id = k.id
  WHERE tp.id = ? AND k.dosen_id = ?
", [$tugas_id, $dosen['id'] ?? 0], 'ii');

if (!$tugas) {
  http_response_code(403);
  die('Akses submission tidak diizinkan');
}

$submissions = fetchAll($conn, "
  SELECT st.*, u.nama_lengkap, u.username, m.nim
  FROM submission_tugas_pertemuan st
  JOIN mahasiswa m ON st.mahasiswa_id = m.id
  JOIN users u ON m.user_id = u.id
  WHERE st.tugas_pertemuan_id = ?
  ORDER BY st.tanggal_submit DESC
", [$tugas_id], 'i');

?>

<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>Mahasiswa</th>
        <th>NIM</th>
        <th>Status</th>
        <th>Tanggal Submit</th>
        <th>Nilai</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($submissions)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted">Belum ada submission</td>
        </tr>
      <?php else: ?>
        <?php foreach ($submissions as $s): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($s['nama_lengkap']); ?></strong>
              <br><small><?php echo htmlspecialchars($s['username']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($s['nim']); ?></td>
            <td>
              <span class="badge badge-<?php 
                echo $s['status'] === 'graded' ? 'success' : 
                     ($s['status'] === 'submitted' ? 'info' : 'danger');
              ?>">
                <?php echo ucfirst($s['status']); ?>
              </span>
            </td>
            <td>
              <?php echo $s['tanggal_submit'] ? date('d M Y H:i', strtotime($s['tanggal_submit'])) : '-'; ?>
            </td>
            <td>
              <?php if ($s['nilai'] !== null): ?>
                <strong><?php echo intval($s['nilai']); ?>/100</strong>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['file_path']): ?>
                <a href="<?php echo htmlspecialchars($s['file_path']); ?>" class="btn btn-xs btn-info" download>Download</a>
              <?php endif; ?>
              <button class="btn btn-xs btn-primary" onclick="gradeSubmission(<?php echo intval($s['id']); ?>)">Nilai</button>
            </td>
          </tr>
          <?php if ($s['komentar_dosen']): ?>
            <tr class="comment-row">
              <td colspan="6">
                <strong>Komentar Dosen:</strong><br>
                <?php echo htmlspecialchars($s['komentar_dosen']); ?>
              </td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
