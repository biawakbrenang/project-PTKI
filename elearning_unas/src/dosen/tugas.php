<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin();
requireRole(['dosen']);

$dosen = getDosenByUserId($conn, $_SESSION['user_id']);
if (!$dosen) {
  die('Data dosen tidak ditemukan');
}

// Get dosen's classes
$kelas_list = fetchAll($conn, "
  SELECT k.*, mk.nama_matkul, mk.kode_matkul
  FROM kelas k
  JOIN matakuliah mk ON k.matakuliah_id = mk.id
  WHERE k.dosen_id = ?
  ORDER BY k.kode_kelas
", [$dosen['id']], 'i');

// Get selected class
$selected_kelas_id = intval($_GET['kelas_id'] ?? ($kelas_list[0]['id'] ?? 0));
$kelas_ids = array_map('intval', array_column($kelas_list, 'id'));
if ($selected_kelas_id > 0 && !in_array($selected_kelas_id, $kelas_ids, true)) {
  $selected_kelas_id = intval($kelas_list[0]['id'] ?? 0);
}

// Get assignments for selected class
$tugas_list = fetchAll($conn, "
  SELECT t.*,
         COALESCE(s.total_submission, 0) as total_submission,
         COALESCE(s.graded_count, 0) as graded_count
  FROM tugas t
  LEFT JOIN (
    SELECT tugas_id,
           COUNT(id) as total_submission,
           SUM(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as graded_count
    FROM submission_tugas
    GROUP BY tugas_id
  ) s ON t.id = s.tugas_id
  WHERE t.kelas_id = ?
  ORDER BY t.tanggal_deadline DESC
", [$selected_kelas_id], 'i');

// Handle grading
$grade_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'grade') {
  $submission_id = intval($_POST['submission_id'] ?? 0);
  $nilai = intval($_POST['nilai'] ?? 0);
  $komentar = trim($_POST['komentar'] ?? '');
  
  $submission = fetchRow($conn, "
    SELECT st.id
    FROM submission_tugas st
    JOIN tugas t ON st.tugas_id = t.id
    JOIN kelas k ON t.kelas_id = k.id
    WHERE st.id = ? AND k.dosen_id = ?
  ", [$submission_id, $dosen['id']], 'ii');

  if ($submission && $nilai >= 0 && $nilai <= 100) {
    $updated = updateRecord($conn, 'submission_tugas', [
      'nilai' => $nilai,
      'komentar_dosen' => $komentar,
      'status' => 'graded',
      'tanggal_graded' => date('Y-m-d H:i:s')
    ], 'id = ?', [$submission_id], 'i');
    
    $grade_message = $updated !== false ? 'Nilai berhasil disimpan!' : 'Nilai gagal disimpan.';
  } else {
    $grade_message = 'Submission tidak valid atau nilai di luar rentang 0-100.';
  }
}

$total_tugas = count($tugas_list);
$total_submission = array_sum(array_map('intval', array_column($tugas_list, 'total_submission')));
$total_graded = array_sum(array_map('intval', array_column($tugas_list, 'graded_count')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tugas Mahasiswa - E-Learning Akademik</title>
  <link rel="stylesheet" href="/elearning_unas/public/css/style.css?v=20260702-fix3">
  <link rel="stylesheet" href="/elearning_unas/public/css/app.css?v=20260702-fix3">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>
  
  <div class="container">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <h1>Tugas Mahasiswa</h1>
        <p>Lihat dan nilai tugas mahasiswa</p>
      </div>

      <?php if ($grade_message): ?>
        <div class="alert alert-success">
          <?php echo htmlspecialchars($grade_message); ?>
        </div>
      <?php endif; ?>

      <!-- Class Filter -->
      <div class="card toolbar-card">
        <div class="card-body">
          <label for="kelasFilter">Pilih Kelas:</label>
          <?php if (empty($kelas_list)): ?>
            <div class="empty-state">Belum ada kelas yang diampu.</div>
          <?php else: ?>
            <select id="kelasFilter" onchange="window.location.href='?kelas_id=' + this.value" class="form-control">
              <?php foreach ($kelas_list as $k): ?>
                <option value="<?php echo $k['id']; ?>" <?php echo $k['id'] == $selected_kelas_id ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($k['nama_matkul'] . ' - ' . $k['kode_kelas']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
      </div>

      <div class="summary-grid">
        <div class="summary-tile">
          <span>Total Tugas</span>
          <strong><?php echo $total_tugas; ?></strong>
        </div>
        <div class="summary-tile">
          <span>Total Submission</span>
          <strong><?php echo $total_submission; ?></strong>
        </div>
        <div class="summary-tile">
          <span>Sudah Dinilai</span>
          <strong><?php echo $total_graded; ?></strong>
        </div>
      </div>

      <!-- Assignments List -->
      <div class="card">
        <div class="card-header">
          <h2>Daftar Tugas</h2>
        </div>
        <div class="card-body">
          <?php if (empty($tugas_list)): ?>
            <div class="empty-state">Tidak ada tugas untuk kelas ini.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Judul Tugas</th>
                    <th>Pertemuan</th>
                    <th>Deadline</th>
                    <th>Submission</th>
                    <th>Sudah Dinilai</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tugas_list as $t): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($t['judul_tugas']); ?></td>
                      <td>Pertemuan <?php echo $t['pertemuan']; ?></td>
                      <td><?php echo date('d M Y', strtotime($t['tanggal_deadline'])); ?></td>
                      <td><?php echo intval($t['total_submission']); ?> submission</td>
                      <td><?php echo intval($t['graded_count']); ?>/<?php echo intval($t['total_submission']); ?></td>
                      <td>
                        <button class="btn btn-sm btn-info" onclick="viewSubmissions(<?php echo $t['id']; ?>)">
                          Lihat Submission
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

  <!-- Submissions Modal -->
  <div id="submissionsModal" class="modal">
    <div class="modal-content modal-lg">
      <span class="close" onclick="closeSubmissionsModal()">&times;</span>
      <h2>Submission Tugas</h2>
      <div id="submissionsContent"></div>
    </div>
  </div>

  <script src="../../public/js/script.js"></script>
  <script>
    function viewSubmissions(tugasId) {
      fetch('get_submissions.php?tugas_id=' + tugasId)
        .then(r => {
          if (!r.ok) throw new Error('Gagal memuat submission');
          return r.text();
        })
        .then(html => {
          document.getElementById('submissionsContent').innerHTML = html;
          elearning.showModal('submissionsModal');
        })
        .catch(() => {
          elearning.showNotification('Submission gagal dimuat. Coba lagi.', 'danger');
        });
    }
    
    function closeSubmissionsModal() {
      elearning.hideModal('submissionsModal');
    }
    
    function gradeSubmission(submissionId) {
      const nilai = prompt('Masukkan nilai (0-100):');
      if (nilai !== null && nilai >= 0 && nilai <= 100) {
        const komentar = prompt('Komentar (opsional):');
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="action" value="grade">
          <input type="hidden" name="submission_id" value="${submissionId}">
          <input type="hidden" name="nilai" value="${nilai}">
          <input type="hidden" name="komentar" value="${komentar || ''}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
    
    window.onclick = function(event) {
      const modal = document.getElementById('submissionsModal');
      if (event.target == modal) {
        elearning.hideModal('submissionsModal');
      }
    }
  </script>
</body>
</html>
