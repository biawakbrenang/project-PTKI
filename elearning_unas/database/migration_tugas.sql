-- =====================================================
-- MIGRATION: Add Tugas (Assignment) Tables
-- =====================================================

-- Table: tugas (Assignments)
CREATE TABLE IF NOT EXISTS tugas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kelas_id INT NOT NULL,
  judul_tugas VARCHAR(255) NOT NULL,
  deskripsi TEXT,
  pertemuan INT NOT NULL,
  tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP,
  tanggal_deadline DATETIME NOT NULL,
  bobot_nilai INT DEFAULT 10,
  status ENUM('aktif', 'selesai', 'arsip') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  UNIQUE KEY unique_tugas_kelas_pertemuan_judul (kelas_id, pertemuan, judul_tugas),
  INDEX idx_kelas_pertemuan (kelas_id, pertemuan),
  INDEX idx_deadline (tanggal_deadline)
);

ALTER IGNORE TABLE tugas
ADD UNIQUE KEY IF NOT EXISTS unique_tugas_kelas_pertemuan_judul (kelas_id, pertemuan, judul_tugas);

-- Table: submission_tugas (Student Submissions)
CREATE TABLE IF NOT EXISTS submission_tugas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tugas_id INT NOT NULL,
  mahasiswa_id INT NOT NULL,
  file_path VARCHAR(255),
  file_name VARCHAR(255),
  file_size INT,
  deskripsi_submission TEXT,
  tanggal_submit DATETIME,
  status ENUM('belum_submit', 'submitted', 'terlambat', 'graded') DEFAULT 'belum_submit',
  nilai INT,
  komentar_dosen TEXT,
  tanggal_graded DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
  UNIQUE KEY unique_submission (tugas_id, mahasiswa_id),
  INDEX idx_status (status),
  INDEX idx_tanggal_submit (tanggal_submit)
);

-- Add tugas data
INSERT IGNORE INTO tugas (kelas_id, judul_tugas, deskripsi, pertemuan, tanggal_deadline, bobot_nilai) VALUES
(1, 'Tugas 1: HTML & CSS Dasar', 'Buat halaman web sederhana menggunakan HTML dan CSS', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(1, 'Tugas 2: JavaScript Interaktif', 'Buat form validasi dengan JavaScript', 2, DATE_ADD(NOW(), INTERVAL 14 DAY), 10),
(1, 'Tugas 3: DOM Manipulation', 'Manipulasi DOM menggunakan JavaScript', 3, DATE_ADD(NOW(), INTERVAL 21 DAY), 10),
(2, 'Tugas 1: Database Design', 'Desain database untuk sistem akademik', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(2, 'Tugas 2: SQL Query', 'Buat query SQL untuk reporting', 2, DATE_ADD(NOW(), INTERVAL 14 DAY), 10),
(3, 'Tugas 1: Array & Linked List', 'Implementasi Array dan Linked List', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(3, 'Tugas 2: Stack & Queue', 'Implementasi Stack dan Queue', 2, DATE_ADD(NOW(), INTERVAL 14 DAY), 10),
(4, 'Tugas 1: Sistem Informasi Bisnis', 'Analisis sistem informasi perusahaan', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(5, 'Tugas 1: Project Charter', 'Buat project charter untuk proyek TI', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 10);

-- Add sample submissions
INSERT IGNORE INTO submission_tugas (tugas_id, mahasiswa_id, file_path, file_name, file_size, tanggal_submit, status, nilai, komentar_dosen) VALUES
(1, 1, '/uploads/tugas/1_1.zip', 'tugas1_ahmad.zip', 2048576, NOW(), 'graded', 85, 'Bagus! HTML struktur sudah benar.'),
(1, 2, '/uploads/tugas/1_2.zip', 'tugas1_budi.zip', 1536000, NOW(), 'graded', 90, 'Sempurna! CSS styling sangat rapi.'),
(2, 1, '/uploads/tugas/2_1.zip', 'tugas2_ahmad.zip', 3145728, DATE_SUB(NOW(), INTERVAL 2 DAY), 'graded', 80, 'Validasi sudah berfungsi, tapi ada bug di error message.'),
(2, 2, '/uploads/tugas/2_2.zip', 'tugas2_budi.zip', 2621440, DATE_SUB(NOW(), INTERVAL 1 DAY), 'submitted', NULL, NULL);
