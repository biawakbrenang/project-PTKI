-- =====================================================
-- MIGRATION: Add Pertemuan (Session) Table
-- =====================================================

-- Table: pertemuan (Sessions/Meetings)
CREATE TABLE IF NOT EXISTS pertemuan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kelas_id INT NOT NULL,
  nomor_pertemuan INT NOT NULL,
  judul_pertemuan VARCHAR(255),
  deskripsi TEXT,
  tanggal_pertemuan DATE,
  jam_mulai TIME,
  jam_selesai TIME,
  status ENUM('belum_dimulai', 'berlangsung', 'selesai') DEFAULT 'belum_dimulai',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  UNIQUE KEY unique_pertemuan (kelas_id, nomor_pertemuan),
  INDEX idx_tanggal (tanggal_pertemuan)
);

-- Table: materi_pertemuan (Materials for each session)
CREATE TABLE IF NOT EXISTS materi_pertemuan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pertemuan_id INT NOT NULL,
  judul_materi VARCHAR(255) NOT NULL,
  deskripsi TEXT,
  file_path VARCHAR(255),
  file_name VARCHAR(255),
  file_size INT,
  tipe_file VARCHAR(50),
  urutan INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pertemuan_id) REFERENCES pertemuan(id) ON DELETE CASCADE,
  UNIQUE KEY unique_materi_pertemuan_judul (pertemuan_id, judul_materi),
  INDEX idx_pertemuan (pertemuan_id)
);

ALTER IGNORE TABLE materi_pertemuan
ADD UNIQUE KEY IF NOT EXISTS unique_materi_pertemuan_judul (pertemuan_id, judul_materi);

-- Table: tugas_pertemuan (Assignments for each session)
CREATE TABLE IF NOT EXISTS tugas_pertemuan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pertemuan_id INT NOT NULL,
  judul_tugas VARCHAR(255) NOT NULL,
  deskripsi TEXT,
  tanggal_deadline DATETIME NOT NULL,
  bobot_nilai INT DEFAULT 10,
  status ENUM('aktif', 'selesai', 'arsip') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pertemuan_id) REFERENCES pertemuan(id) ON DELETE CASCADE,
  UNIQUE KEY unique_tugas_pertemuan_judul (pertemuan_id, judul_tugas),
  INDEX idx_pertemuan (pertemuan_id),
  INDEX idx_deadline (tanggal_deadline)
);

ALTER IGNORE TABLE tugas_pertemuan
ADD UNIQUE KEY IF NOT EXISTS unique_tugas_pertemuan_judul (pertemuan_id, judul_tugas);

-- Table: submission_tugas_pertemuan (Student submissions)
CREATE TABLE IF NOT EXISTS submission_tugas_pertemuan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tugas_pertemuan_id INT NOT NULL,
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
  FOREIGN KEY (tugas_pertemuan_id) REFERENCES tugas_pertemuan(id) ON DELETE CASCADE,
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
  UNIQUE KEY unique_submission (tugas_pertemuan_id, mahasiswa_id),
  INDEX idx_status (status),
  INDEX idx_tanggal_submit (tanggal_submit)
);

-- Add sample pertemuan data
INSERT IGNORE INTO pertemuan (kelas_id, nomor_pertemuan, judul_pertemuan, deskripsi, tanggal_pertemuan, jam_mulai, jam_selesai) VALUES
(1, 1, 'Pengenalan HTML & CSS', 'Memahami struktur dasar HTML dan styling dengan CSS', '2024-04-20', '08:00', '09:30'),
(1, 2, 'JavaScript Dasar', 'Belajar JavaScript untuk interaksi web', '2024-04-27', '08:00', '09:30'),
(1, 3, 'DOM & Event Handling', 'Manipulasi DOM dan menangani event', '2024-05-04', '08:00', '09:30'),
(2, 1, 'Konsep Database', 'Pengenalan konsep database relasional', '2024-04-21', '10:00', '11:30'),
(2, 2, 'SQL Dasar', 'Query dasar SQL untuk CRUD', '2024-04-28', '10:00', '11:30'),
(3, 1, 'Array & Linked List', 'Struktur data Array dan Linked List', '2024-04-22', '13:00', '14:30'),
(3, 2, 'Stack & Queue', 'Implementasi Stack dan Queue', '2024-04-29', '13:00', '14:30');

-- Add sample materi_pertemuan
INSERT IGNORE INTO materi_pertemuan (pertemuan_id, judul_materi, deskripsi, file_path, file_name, file_size, tipe_file) VALUES
(1, 'Slide HTML Dasar', 'Pengenalan tag HTML dan struktur', '/uploads/materi/html_dasar.pdf', 'html_dasar.pdf', 2048576, 'pdf'),
(1, 'Contoh Kode HTML', 'Contoh-contoh kode HTML', '/uploads/materi/contoh_html.zip', 'contoh_html.zip', 1024000, 'zip'),
(2, 'Slide JavaScript', 'Pengenalan JavaScript', '/uploads/materi/javascript_dasar.pdf', 'javascript_dasar.pdf', 2560000, 'pdf'),
(3, 'DOM Tutorial', 'Tutorial manipulasi DOM', '/uploads/materi/dom_tutorial.pdf', 'dom_tutorial.pdf', 1800000, 'pdf'),
(4, 'Database Concepts', 'Konsep database relasional', '/uploads/materi/database_concepts.pdf', 'database_concepts.pdf', 2200000, 'pdf'),
(5, 'SQL Query Guide', 'Panduan SQL query', '/uploads/materi/sql_guide.pdf', 'sql_guide.pdf', 1900000, 'pdf');

-- Add sample tugas_pertemuan
INSERT IGNORE INTO tugas_pertemuan (pertemuan_id, judul_tugas, deskripsi, tanggal_deadline, bobot_nilai) VALUES
(1, 'Tugas 1: Buat Halaman HTML', 'Buat halaman web sederhana dengan HTML', DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(2, 'Tugas 2: JavaScript Interaktif', 'Buat form validasi dengan JavaScript', DATE_ADD(NOW(), INTERVAL 14 DAY), 10),
(3, 'Tugas 3: DOM Manipulation', 'Manipulasi DOM dengan JavaScript', DATE_ADD(NOW(), INTERVAL 21 DAY), 10),
(4, 'Tugas 1: Database Design', 'Desain database untuk sistem akademik', DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(5, 'Tugas 2: SQL Query', 'Buat query SQL untuk reporting', DATE_ADD(NOW(), INTERVAL 14 DAY), 10),
(6, 'Tugas 1: Array Implementation', 'Implementasi Array dengan bahasa pemrograman', DATE_ADD(NOW(), INTERVAL 7 DAY), 10),
(7, 'Tugas 2: Stack & Queue', 'Implementasi Stack dan Queue', DATE_ADD(NOW(), INTERVAL 14 DAY), 10);

-- Add sample submissions
INSERT IGNORE INTO submission_tugas_pertemuan (tugas_pertemuan_id, mahasiswa_id, file_path, file_name, file_size, tanggal_submit, status, nilai, komentar_dosen) VALUES
(1, 1, '/uploads/tugas/tugas1_ahmad.zip', 'tugas1_ahmad.zip', 2048576, NOW(), 'graded', 85, 'Bagus! HTML struktur sudah benar.'),
(1, 2, '/uploads/tugas/tugas1_budi.zip', 'tugas1_budi.zip', 1536000, NOW(), 'graded', 90, 'Sempurna! CSS styling sangat rapi.'),
(2, 1, '/uploads/tugas/tugas2_ahmad.zip', 'tugas2_ahmad.zip', 3145728, DATE_SUB(NOW(), INTERVAL 2 DAY), 'graded', 80, 'Validasi sudah berfungsi, tapi ada bug di error message.'),
(2, 2, '/uploads/tugas/tugas2_budi.zip', 'tugas2_budi.zip', 2621440, DATE_SUB(NOW(), INTERVAL 1 DAY), 'submitted', NULL, NULL);
