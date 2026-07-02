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

$kelas_list = fetchAll($conn, "
  SELECT k.*, mk.nama_matkul, mk.kode_matkul
  FROM kelas k
  JOIN matakuliah mk ON k.matakuliah_id = mk.id
  WHERE k.dosen_id = ?
  ORDER BY k.kode_kelas
", [$dosen['id']], 'i');

$selected_kelas_id = intval($_GET['kelas_id'] ?? ($kelas_list[0]['id'] ?? 0));
$kelas_ids = array_map('intval', array_column($kelas_list, 'id'));
if ($selected_kelas_id > 0 && !in_array($selected_kelas_id, $kelas_ids, true)) {
  $selected_kelas_id = intval($kelas_list[0]['id'] ?? 0);
}

$pertemuan_list = fetchAll($conn, "
  SELECT p.*,
         COALESCE(mp.total_materi, 0) as total_materi,
         COALESCE(tp.total_tugas, 0) as total_tugas
  FROM pertemuan p
  LEFT JOIN (
    SELECT pertemuan_id, COUNT(id) as total_materi
    FROM materi_pertemuan
    GROUP BY pertemuan_id
  ) mp ON p.id = mp.pertemuan_id
  LEFT JOIN (
    SELECT pertemuan_id, COUNT(id) as total_tugas
    FROM tugas_pertemuan
    GROUP BY pertemuan_id
  ) tp ON p.id = tp.pertemuan_id
  WHERE p.kelas_id = ?
  ORDER BY p.nomor_pertemuan ASC
", [$selected_kelas_id], 'i');

$grade_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'grade') {
  $submission_id = intval($_POST['submission_id'] ?? 0);
  $nilai = intval($_POST['nilai'] ?? -1);
  $komentar = trim($_POST['komentar'] ?? '');

  $submission = fetchRow($conn, "
    SELECT st.id
    FROM submission_tugas_pertemuan st
    JOIN tugas_pertemuan tp ON st.tugas_pertemuan_id = tp.id
    JOIN pertemuan p ON tp.pertemuan_id = p.id
    JOIN kelas k ON p.kelas_id = k.id
    WHERE st.id = ? AND k.dosen_id = ?
  ", [$submission_id, $dosen['id']], 'ii');

  if ($submission && $nilai >= 0 && $nilai <= 100) {
    $updated = updateRecord($conn, 'submission_tugas_pertemuan', [
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

$total_pertemuan = count($pertemuan_list);
$total_materi = array_sum(array_map('intval', array_column($pertemuan_list, 'total_materi')));
$total_tugas = array_sum(array_map('intval', array_column($pertemuan_list, 'total_tugas')));
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
        <h1>Kelola Pertemuan</h1>
        <p>Kelola materi, tugas, dan nilai per pertemuan</p>
      </div>

      <?php if ($grade_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($grade_message); ?></div>
      <?php endif; ?>

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
          <span>Total Pertemuan</span>
          <strong><?php echo $total_pertemuan; ?></strong>
        </div>
        <div class="summary-tile">
          <span>Total Materi</span>
          <strong><?php echo $total_materi; ?></strong>
        </div>
        <div class="summary-tile">
          <span>Total Tugas</span>
          <strong><?php echo $total_tugas; ?></strong>
        </div>
      </div>

      <div class="pertemuan-container">
        <?php if (empty($pertemuan_list)): ?>
          <div class="empty-state">Tidak ada pertemuan untuk kelas ini.</div>
        <?php else: ?>
          <?php foreach ($pertemuan_list as $p): ?>
            <div class="card pertemuan-card">
              <div class="card-header">
                <div>
                  <h3>Pertemuan <?php echo intval($p['nomor_pertemuan']); ?>: <?php echo htmlspecialchars($p['judul_pertemuan']); ?></h3>
                  <div class="pertemuan-meta">
                    <span class="badge">Tanggal: <?php echo $p['tanggal_pertemuan'] ? date('d M Y', strtotime($p['tanggal_pertemuan'])) : '-'; ?></span>
                    <span class="badge">Jam: <?php echo htmlspecialchars(substr($p['jam_mulai'] ?? '', 0, 5) . ' - ' . substr($p['jam_selesai'] ?? '', 0, 5)); ?></span>
                    <span class="badge"><?php echo intval($p['total_materi']); ?> Materi</span>
                    <span class="badge"><?php echo intval($p['total_tugas']); ?> Tugas</span>
                  </div>
                </div>
              </div>

              <div class="card-body">
                <div class="session-section">
                  <h4>Materi Pembelajaran</h4>
                  <?php
                    $materi = fetchAll($conn, "SELECT * FROM materi_pertemuan WHERE pertemuan_id = ? ORDER BY urutan", [$p['id']], 'i');
                  ?>
                  <?php if (empty($materi)): ?>
                    <p class="text-muted">Tidak ada materi</p>
                  <?php else: ?>
                    <div class="materi-list">
                      <?php foreach ($materi as $m): ?>
                        <div class="materi-item">
                          <div class="materi-info">
                            <strong><?php echo htmlspecialchars($m['judul_materi']); ?></strong>
                            <p><?php echo htmlspecialchars($m['deskripsi'] ?? ''); ?></p>
                            <small>
                              <?php echo htmlspecialchars($m['file_name'] ?? '-'); ?>
                              <?php if (!empty($m['file_size'])): ?>
                                (<?php echo round($m['file_size'] / 1024 / 1024, 2); ?> MB)
                              <?php endif; ?>
                            </small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="session-section">
                  <h4>Tugas & Submission</h4>
                  <?php
                    $tugas = fetchAll($conn, "SELECT * FROM tugas_pertemuan WHERE pertemuan_id = ? ORDER BY tanggal_deadline", [$p['id']], 'i');
                  ?>
                  <?php if (empty($tugas)): ?>
                    <p class="text-muted">Tidak ada tugas</p>
                  <?php else: ?>
                    <div class="tugas-list">
                      <?php foreach ($tugas as $t): ?>
                        <div class="tugas-item">
                          <div class="tugas-info">
                            <strong><?php echo htmlspecialchars($t['judul_tugas']); ?></strong>
                            <p><?php echo htmlspecialchars($t['deskripsi'] ?? ''); ?></p>
                            <small>Deadline: <?php echo date('d M Y H:i', strtotime($t['tanggal_deadline'])); ?> | Bobot: <?php echo intval($t['bobot_nilai']); ?> poin</small>
                          </div>
                          <button class="btn btn-sm btn-info" onclick="viewSubmissions(<?php echo intval($t['id']); ?>)">
                            Lihat Submission
                          </button>
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
      fetch('get_submissions_pertemuan.php?tugas_id=' + tugasId)
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
        const komentar = prompt('Komentar:');
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
