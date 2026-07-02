<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin();
requireRole(['mahasiswa']);

$mahasiswa = getMahasiswaByUserId($conn, $_SESSION['user_id']);
if (!$mahasiswa) {
  die('Data mahasiswa tidak ditemukan');
}

// Get all assignments for this student's classes
$query = "
  SELECT t.*, k.kode_kelas, mk.nama_matkul, st.status as submission_status, st.nilai, st.tanggal_submit
  FROM tugas t
  JOIN kelas k ON t.kelas_id = k.id
  JOIN matakuliah mk ON k.matakuliah_id = mk.id
  LEFT JOIN submission_tugas st ON t.id = st.tugas_id AND st.mahasiswa_id = ?
  WHERE k.id IN (
    SELECT DISTINCT kelas_id FROM krs WHERE mahasiswa_id = ? AND status_krs = 'diambil'
  )
  ORDER BY t.tanggal_deadline ASC
";
$tugas_list = fetchAll($conn, $query, [$mahasiswa['id'], $mahasiswa['id']], 'ii');

// Handle file upload
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_tugas') {
  $tugas_id = intval($_POST['tugas_id'] ?? 0);
  
  if ($tugas_id > 0 && isset($_FILES['file_tugas'])) {
    $file = $_FILES['file_tugas'];
    
    // Validate file
    if ($file['error'] === UPLOAD_ERR_OK) {
      $max_size = 10 * 1024 * 1024; // 10 MB
      if ($file['size'] <= $max_size) {
        $upload_dir = __DIR__ . '/../../public/uploads/tugas/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'tugas_' . $mahasiswa['id'] . '_' . $tugas_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
          // Insert or update submission
          $check = fetchOne($conn, "SELECT id FROM submission_tugas WHERE tugas_id = ? AND mahasiswa_id = ?", [$tugas_id, $mahasiswa['id']], 'ii');
          
          if ($check) {
            updateRecord($conn, 'submission_tugas', [
              'file_path' => '/uploads/tugas/' . $file_name,
              'file_name' => $file['name'],
              'file_size' => $file['size'],
              'tanggal_submit' => date('Y-m-d H:i:s'),
              'status' => 'submitted'
            ], 'tugas_id = ? AND mahasiswa_id = ?', [$tugas_id, $mahasiswa['id']], 'ii');
          } else {
            // Insert new
            insertRecord($conn, 'submission_tugas', [
              'tugas_id' => $tugas_id,
              'mahasiswa_id' => $mahasiswa['id'],
              'file_path' => '/uploads/tugas/' . $file_name,
              'file_name' => $file['name'],
              'file_size' => $file['size'],
              'tanggal_submit' => date('Y-m-d H:i:s'),
              'status' => 'submitted'
            ]);
          }
          
          $upload_message = 'Tugas berhasil diupload!';
        } else {
          $upload_message = 'Gagal mengupload file!';
        }
      } else {
        $upload_message = 'Ukuran file terlalu besar (max 10 MB)!';
      }
    } else {
      $upload_message = 'Error saat upload: ' . $file['error'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tugas Saya - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  
  <div class="container">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <h1>Tugas Saya</h1>
        <p>Kelola tugas dan lihat nilai dari dosen</p>
      </div>

      <?php if ($upload_message): ?>
        <div class="alert alert-info">
          <?php echo htmlspecialchars($upload_message); ?>
        </div>
      <?php endif; ?>

      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-icon">📋</div>
          <div class="stat-content">
            <div class="stat-label">Total Tugas</div>
            <div class="stat-value"><?php echo count($tugas_list); ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <div class="stat-label">Sudah Dikumpulkan</div>
            <div class="stat-value"><?php echo count(array_filter($tugas_list, fn($t) => $t['submission_status'] !== 'belum_submit')); ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⭐</div>
          <div class="stat-content">
            <div class="stat-label">Sudah Dinilai</div>
            <div class="stat-value"><?php echo count(array_filter($tugas_list, fn($t) => $t['submission_status'] === 'graded')); ?></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Daftar Tugas</h2>
        </div>
        <div class="card-body">
          <?php if (empty($tugas_list)): ?>
            <p class="text-center text-muted">Tidak ada tugas</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Matakuliah</th>
                    <th>Judul Tugas</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tugas_list as $t): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($t['nama_matkul']); ?></td>
                      <td><?php echo htmlspecialchars($t['judul_tugas']); ?></td>
                      <td><?php echo date('d M Y H:i', strtotime($t['tanggal_deadline'])); ?></td>
                      <td>
                        <span class="badge badge-<?php 
                          echo $t['submission_status'] === 'graded' ? 'success' : 
                               ($t['submission_status'] === 'submitted' ? 'info' : 
                               ($t['submission_status'] === 'terlambat' ? 'warning' : 'danger'));
                        ?>">
                          <?php echo ucfirst($t['submission_status'] ?? 'belum_submit'); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($t['submission_status'] === 'graded'): ?>
                          <strong><?php echo $t['nilai']; ?></strong>/100
                        <?php else: ?>
                          -
                        <?php endif; ?>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="openUploadModal(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['judul_tugas']); ?>')">
                          Upload
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <!-- Upload Modal -->
  <div id="uploadModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeUploadModal()">&times;</span>
      <h2>Upload Tugas</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="submit_tugas">
        <input type="hidden" name="tugas_id" id="tugasId">
        
        <div class="form-group">
          <label for="fileTugas">Pilih File (Max 10 MB):</label>
          <input type="file" id="fileTugas" name="file_tugas" required accept=".pdf,.doc,.docx,.zip,.rar,.txt">
        </div>
        
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Upload</button>
          <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Batal</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../../public/js/script.js"></script>
  <script>
    function openUploadModal(tugasId, judulTugas) {
      document.getElementById('tugasId').value = tugasId;
      document.getElementById('uploadModal').style.display = 'block';
    }
    
    function closeUploadModal() {
      document.getElementById('uploadModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
      const modal = document.getElementById('uploadModal');
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }
  </script>
</body>
</html>
