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

// Get selected class
$selected_kelas_id = intval($_GET['kelas_id'] ?? 0);

// Get mahasiswa's classes
$kelas_list = fetchAll($conn, "
  SELECT DISTINCT k.*, mk.nama_matkul, mk.kode_matkul
  FROM kelas k
  JOIN matakuliah mk ON k.matakuliah_id = mk.id
  JOIN krs ON k.id = krs.kelas_id
  WHERE krs.mahasiswa_id = ? AND krs.status_krs = 'diambil'
  ORDER BY k.kode_kelas
", [$mahasiswa['id']], 'i');

if (empty($kelas_list)) {
  die('Anda belum mengambil KRS');
}

if ($selected_kelas_id <= 0) {
  $selected_kelas_id = $kelas_list[0]['id'];
}

// Get pertemuan for selected class
$pertemuan_list = fetchAll($conn, "
  SELECT p.*, 
         COUNT(DISTINCT mp.id) as total_materi,
         COUNT(DISTINCT tp.id) as total_tugas
  FROM pertemuan p
  LEFT JOIN materi_pertemuan mp ON p.id = mp.pertemuan_id
  LEFT JOIN tugas_pertemuan tp ON p.id = tp.pertemuan_id
  WHERE p.kelas_id = ?
  GROUP BY p.id
  ORDER BY p.nomor_pertemuan ASC
", [$selected_kelas_id], 'i');

// Handle file upload
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_tugas') {
  $tugas_id = intval($_POST['tugas_id'] ?? 0);
  
  if ($tugas_id > 0 && isset($_FILES['file_tugas'])) {
    $file = $_FILES['file_tugas'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
      $max_size = 10 * 1024 * 1024;
      if ($file['size'] <= $max_size) {
        $upload_dir = __DIR__ . '/../../public/uploads/tugas/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'tugas_' . $mahasiswa['id'] . '_' . $tugas_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
          $check = fetchOne($conn, "SELECT id FROM submission_tugas_pertemuan WHERE tugas_pertemuan_id = ? AND mahasiswa_id = ?", [$tugas_id, $mahasiswa['id']], 'ii');
          
          if ($check) {
            updateRecord($conn, 'submission_tugas_pertemuan', [
              'file_path' => '/uploads/tugas/' . $file_name,
              'file_name' => $file['name'],
              'file_size' => $file['size'],
              'tanggal_submit' => date('Y-m-d H:i:s'),
              'status' => 'submitted'
            ], 'tugas_pertemuan_id = ? AND mahasiswa_id = ?', [$tugas_id, $mahasiswa['id']], 'ii');
          } else {
            insertRecord($conn, 'submission_tugas_pertemuan', [
              'tugas_pertemuan_id' => $tugas_id,
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
          $upload_message = 'Gagal mengupload file.';
        }
      } else {
        $upload_message = 'Ukuran file terlalu besar (maksimal 10 MB).';
      }
    } else {
      $upload_message = 'Upload gagal. Kode error: ' . $file['error'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pertemuan - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  
  <div class="container">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <h1>Pertemuan & Materi</h1>
        <p>Lihat materi dan upload tugas per pertemuan</p>
      </div>

      <?php if ($upload_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($upload_message); ?></div>
      <?php endif; ?>

      <!-- Class Filter -->
      <div class="card">
        <div class="card-body">
          <label for="kelasFilter">Pilih Kelas:</label>
          <select id="kelasFilter" onchange="window.location.href='?kelas_id=' + this.value" class="form-control">
            <?php foreach ($kelas_list as $k): ?>
              <option value="<?php echo $k['id']; ?>" <?php echo $k['id'] == $selected_kelas_id ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($k['nama_matkul'] . ' - ' . $k['kode_kelas']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Pertemuan List -->
      <div class="pertemuan-container">
        <?php if (empty($pertemuan_list)): ?>
          <p class="text-center text-muted">Tidak ada pertemuan</p>
        <?php else: ?>
          <?php foreach ($pertemuan_list as $p): ?>
            <div class="card pertemuan-card">
              <div class="card-header">
                <h3>Pertemuan <?php echo $p['nomor_pertemuan']; ?>: <?php echo htmlspecialchars($p['judul_pertemuan']); ?></h3>
                <div class="pertemuan-meta">
                  <span class="badge">📅 <?php echo date('d M Y', strtotime($p['tanggal_pertemuan'])); ?></span>
                  <span class="badge">⏰ <?php echo $p['jam_mulai']; ?> - <?php echo $p['jam_selesai']; ?></span>
                </div>
              </div>

              <div class="card-body">
                <!-- Materi Section -->
                <div class="section">
                  <h4>📚 Materi Pembelajaran (<?php echo $p['total_materi']; ?>)</h4>
                  <?php 
                    $materi = fetchAll($conn, "SELECT * FROM materi_pertemuan WHERE pertemuan_id = ? ORDER BY urutan", [$p['id']], 'i');
                    if (empty($materi)): 
                  ?>
                    <p class="text-muted">Tidak ada materi</p>
                  <?php else: ?>
                    <div class="materi-list">
                      <?php foreach ($materi as $m): ?>
                        <div class="materi-item">
                          <div class="materi-info">
                            <strong><?php echo htmlspecialchars($m['judul_materi']); ?></strong>
                            <p><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                            <small><?php echo htmlspecialchars($m['file_name']); ?> (<?php echo round($m['file_size'] / 1024 / 1024, 2); ?> MB)</small>
                          </div>
                          <a href="<?php echo htmlspecialchars($m['file_path']); ?>" class="btn btn-sm btn-info" download>Download</a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Tugas Section -->
                <div class="section">
                  <h4>📝 Tugas (<?php echo $p['total_tugas']; ?>)</h4>
                  <?php 
                    $tugas = fetchAll($conn, "
                      SELECT tp.*, st.status as submission_status, st.nilai, st.komentar_dosen
                      FROM tugas_pertemuan tp
                      LEFT JOIN submission_tugas_pertemuan st ON tp.id = st.tugas_pertemuan_id AND st.mahasiswa_id = ?
                      WHERE tp.pertemuan_id = ?
                      ORDER BY tp.tanggal_deadline
                    ", [$mahasiswa['id'], $p['id']], 'ii');
                    
                    if (empty($tugas)): 
                  ?>
                    <p class="text-muted">Tidak ada tugas</p>
                  <?php else: ?>
                    <div class="tugas-list">
                      <?php foreach ($tugas as $t): ?>
                        <div class="tugas-item">
                          <div class="tugas-info">
                            <strong><?php echo htmlspecialchars($t['judul_tugas']); ?></strong>
                            <p><?php echo htmlspecialchars($t['deskripsi']); ?></p>
                            <small>Deadline: <?php echo date('d M Y H:i', strtotime($t['tanggal_deadline'])); ?></small>
                            
                            <?php if ($t['submission_status'] === 'graded'): ?>
                              <div class="submission-info">
                                <p><strong>Nilai: <?php echo $t['nilai']; ?>/100</strong></p>
                                <p>Komentar: <?php echo htmlspecialchars($t['komentar_dosen']); ?></p>
                              </div>
                            <?php endif; ?>
                          </div>
                          <div class="tugas-actions">
                            <span class="badge badge-<?php 
                              echo $t['submission_status'] === 'graded' ? 'success' : 
                                   ($t['submission_status'] === 'submitted' ? 'info' : 'danger');
                            ?>">
                              <?php echo ucfirst($t['submission_status'] ?? 'belum_submit'); ?>
                            </span>
                            <button class="btn btn-sm btn-primary" onclick="openUploadModal(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['judul_tugas']); ?>')">
                              Upload
                            </button>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
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

  <style>
    .pertemuan-container { display: flex; flex-direction: column; gap: 20px; }
    .pertemuan-card { margin-bottom: 20px; }
    .pertemuan-meta { margin-top: 10px; display: flex; gap: 10px; }
    .section { margin-bottom: 30px; }
    .section h4 { margin-bottom: 15px; color: #1e40af; }
    .materi-list, .tugas-list { display: flex; flex-direction: column; gap: 12px; }
    .materi-item, .tugas-item { 
      display: flex; 
      justify-content: space-between; 
      align-items: center;
      padding: 12px;
      background: #f9fafb;
      border-radius: 6px;
      border-left: 4px solid #1e40af;
    }
    .materi-info, .tugas-info { flex: 1; }
    .materi-info p, .tugas-info p { margin: 5px 0; font-size: 14px; }
    .tugas-actions { display: flex; gap: 10px; align-items: center; }
    .submission-info { margin-top: 10px; padding: 10px; background: #e0f2fe; border-radius: 4px; }
  </style>
</body>
</html>
