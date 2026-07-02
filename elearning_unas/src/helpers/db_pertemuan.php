<?php
/**
 * Database Helper Functions for Pertemuan (Sessions)
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/db.php';

/**
 * Get all pertemuan for a kelas
 */
function getPertemuanByKelasId($conn, $kelasId) {
  return fetchAll($conn, "
    SELECT p.*, 
           COUNT(DISTINCT mp.id) as total_materi,
           COUNT(DISTINCT tp.id) as total_tugas
    FROM pertemuan p
    LEFT JOIN materi_pertemuan mp ON p.id = mp.pertemuan_id
    LEFT JOIN tugas_pertemuan tp ON p.id = tp.pertemuan_id
    WHERE p.kelas_id = ?
    GROUP BY p.id
    ORDER BY p.nomor_pertemuan ASC
  ", [$kelasId], 'i');
}

/**
 * Get single pertemuan by ID
 */
function getPertemuanById($conn, $pertemuanId) {
  return fetchRow($conn, "
    SELECT p.*, 
           COUNT(DISTINCT mp.id) as total_materi,
           COUNT(DISTINCT tp.id) as total_tugas
    FROM pertemuan p
    LEFT JOIN materi_pertemuan mp ON p.id = mp.pertemuan_id
    LEFT JOIN tugas_pertemuan tp ON p.id = tp.pertemuan_id
    WHERE p.id = ?
    GROUP BY p.id
  ", [$pertemuanId], 'i');
}

/**
 * Get materi for a pertemuan
 */
function getMateriPertemuan($conn, $pertemuanId) {
  return fetchAll($conn, "
    SELECT * FROM materi_pertemuan
    WHERE pertemuan_id = ?
    ORDER BY urutan ASC
  ", [$pertemuanId], 'i');
}

/**
 * Get tugas for a pertemuan
 */
function getTugasPertemuan($conn, $pertemuanId) {
  return fetchAll($conn, "
    SELECT * FROM tugas_pertemuan
    WHERE pertemuan_id = ?
    ORDER BY tanggal_deadline ASC
  ", [$pertemuanId], 'i');
}

/**
 * Get submission status for a mahasiswa in a tugas
 */
function getSubmissionStatus($conn, $tugasId, $mahasiswaId) {
  return fetchRow($conn, "
    SELECT * FROM submission_tugas_pertemuan
    WHERE tugas_pertemuan_id = ? AND mahasiswa_id = ?
  ", [$tugasId, $mahasiswaId], 'ii');
}

/**
 * Get all submissions for a tugas
 */
function getSubmissionsByTugasId($conn, $tugasId) {
  return fetchAll($conn, "
    SELECT st.*, m.nim, u.username, u.nama_lengkap
    FROM submission_tugas_pertemuan st
    JOIN mahasiswa m ON st.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE st.tugas_pertemuan_id = ?
    ORDER BY st.tanggal_submit DESC
  ", [$tugasId], 'i');
}

/**
 * Get submissions for a mahasiswa in a pertemuan
 */
function getSubmissionsByMahasiswaPertemuan($conn, $mahasiswaId, $pertemuanId) {
  return fetchAll($conn, "
    SELECT st.*, tp.judul_tugas, tp.tanggal_deadline
    FROM submission_tugas_pertemuan st
    JOIN tugas_pertemuan tp ON st.tugas_pertemuan_id = tp.id
    WHERE st.mahasiswa_id = ? AND tp.pertemuan_id = ?
    ORDER BY tp.tanggal_deadline ASC
  ", [$mahasiswaId, $pertemuanId], 'ii');
}

/**
 * Create pertemuan
 */
function createPertemuan($conn, $data) {
  return insertRecord($conn, 'pertemuan', $data);
}

/**
 * Update pertemuan
 */
function updatePertemuan($conn, $pertemuanId, $data) {
  return updateRecord($conn, 'pertemuan', $data, 'id = ?', [$pertemuanId], 'i');
}

/**
 * Delete pertemuan
 */
function deletePertemuan($conn, $pertemuanId) {
  return deleteRecord($conn, 'pertemuan', 'id = ?', [$pertemuanId], 'i');
}

/**
 * Add materi to pertemuan
 */
function addMateriPertemuan($conn, $data) {
  return insertRecord($conn, 'materi_pertemuan', $data);
}

/**
 * Update materi pertemuan
 */
function updateMateriPertemuan($conn, $materiId, $data) {
  return updateRecord($conn, 'materi_pertemuan', $data, 'id = ?', [$materiId], 'i');
}

/**
 * Delete materi pertemuan
 */
function deleteMateriPertemuan($conn, $materiId) {
  return deleteRecord($conn, 'materi_pertemuan', 'id = ?', [$materiId], 'i');
}

/**
 * Add tugas to pertemuan
 */
function addTugasPertemuan($conn, $data) {
  return insertRecord($conn, 'tugas_pertemuan', $data);
}

/**
 * Update tugas pertemuan
 */
function updateTugasPertemuan($conn, $tugasId, $data) {
  return updateRecord($conn, 'tugas_pertemuan', $data, 'id = ?', [$tugasId], 'i');
}

/**
 * Delete tugas pertemuan
 */
function deleteTugasPertemuan($conn, $tugasId) {
  return deleteRecord($conn, 'tugas_pertemuan', 'id = ?', [$tugasId], 'i');
}

/**
 * Submit tugas
 */
function submitTugas($conn, $data) {
  return insertRecord($conn, 'submission_tugas_pertemuan', $data);
}

/**
 * Update submission
 */
function updateSubmission($conn, $submissionId, $data) {
  return updateRecord($conn, 'submission_tugas_pertemuan', $data, 'id = ?', [$submissionId], 'i');
}

/**
 * Grade submission
 */
function gradeSubmission($conn, $submissionId, $nilai, $komentar) {
  return updateRecord($conn, 'submission_tugas_pertemuan', [
    'nilai' => $nilai,
    'komentar_dosen' => $komentar,
    'status' => 'graded',
    'tanggal_graded' => date('Y-m-d H:i:s')
  ], 'id = ?', [$submissionId], 'i');
}

/**
 * Get submission statistics for a tugas
 */
function getSubmissionStats($conn, $tugasId) {
  return fetchRow($conn, "
    SELECT 
      COUNT(*) as total_submission,
      SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
      SUM(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as graded,
      SUM(CASE WHEN status = 'belum_submit' THEN 1 ELSE 0 END) as belum_submit,
      AVG(nilai) as rata_rata_nilai
    FROM submission_tugas_pertemuan
    WHERE tugas_pertemuan_id = ?
  ", [$tugasId], 'i');
}

/**
 * Get mahasiswa grades for a pertemuan
 */
function getMahasiswaGradesPertemuan($conn, $mahasiswaId, $pertemuanId) {
  return fetchAll($conn, "
    SELECT tp.id, tp.judul_tugas, tp.bobot_nilai, st.nilai, st.status
    FROM tugas_pertemuan tp
    LEFT JOIN submission_tugas_pertemuan st ON tp.id = st.tugas_pertemuan_id AND st.mahasiswa_id = ?
    WHERE tp.pertemuan_id = ?
    ORDER BY tp.tanggal_deadline ASC
  ", [$mahasiswaId, $pertemuanId], 'ii');
}

/**
 * Calculate total nilai for mahasiswa in a pertemuan
 */
function calculateTotalNilaiPertemuan($conn, $mahasiswaId, $pertemuanId) {
  $result = fetchRow($conn, "
    SELECT 
      SUM(CASE WHEN st.nilai IS NOT NULL THEN st.nilai ELSE 0 END) as total_nilai,
      SUM(tp.bobot_nilai) as total_bobot
    FROM tugas_pertemuan tp
    LEFT JOIN submission_tugas_pertemuan st ON tp.id = st.tugas_pertemuan_id AND st.mahasiswa_id = ?
    WHERE tp.pertemuan_id = ?
  ", [$mahasiswaId, $pertemuanId], 'ii');
  
  if ($result && $result['total_bobot'] > 0) {
    return ($result['total_nilai'] / $result['total_bobot']) * 100;
  }
  return 0;
}

?>
