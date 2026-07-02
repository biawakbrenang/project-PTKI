<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin();
requireRole(['dosen']);

$tugas_id = intval($_GET['tugas_id'] ?? 0);

if ($tugas_id <= 0) {
  die('Invalid task ID');
}

$dosen = getDosenByUserId($conn, $_SESSION['user_id']);
$tugas = fetchRow($conn, "
  SELECT t.id
  FROM tugas t
  JOIN kelas k ON t.kelas_id = k.id
  WHERE t.id = ? AND k.dosen_id = ?
", [$tugas_id, $dosen['id'] ?? 0], 'ii');

if (!$tugas) {
  http_response_code(403);
  die('Akses submission tidak diizinkan');
}

$submissions = fetchAll($conn, "
  SELECT st.*, u.nama_lengkap, m.nim
  FROM submission_tugas st
  JOIN mahasiswa m ON st.mahasiswa_id = m.id
  JOIN users u ON m.user_id = u.id
  WHERE st.tugas_id = ?
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
            <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
            <td><?php echo htmlspecialchars($s['nim']); ?></td>
            <td>
              <span class="badge badge-<?php 
                echo $s['status'] === 'graded' ? 'success' : 
                     ($s['status'] === 'submitted' ? 'info' : 
                     ($s['status'] === 'terlambat' ? 'warning' : 'danger'));
              ?>">
                <?php echo ucfirst($s['status']); ?>
              </span>
            </td>
            <td><?php echo $s['tanggal_submit'] ? date('d M Y H:i', strtotime($s['tanggal_submit'])) : '-'; ?></td>
            <td>
              <?php if ($s['status'] === 'graded'): ?>
                <strong><?php echo intval($s['nilai']); ?></strong>/100
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['file_path']): ?>
                <a href="<?php echo htmlspecialchars($s['file_path']); ?>" class="btn btn-sm btn-info" download>Download</a>
              <?php endif; ?>
              <button class="btn btn-sm btn-success" onclick="gradeSubmission(<?php echo intval($s['id']); ?>)">Nilai</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
