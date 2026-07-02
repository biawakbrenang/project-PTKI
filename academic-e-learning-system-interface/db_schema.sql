-- =========================================================================
-- Academic E-Learning System - Skema Database MySQL / phpMyAdmin
-- -------------------------------------------------------------------------
-- Database : elearning_akademik
-- Engine   : InnoDB
-- Charset  : utf8mb4
--
-- Cara import di phpMyAdmin:
--   1. Buka phpMyAdmin -> tab "Database" -> buat database "elearning_akademik"
--   2. Pilih database -> tab "Import" -> pilih file ini -> Klik "Import"
-- =========================================================================

CREATE DATABASE IF NOT EXISTS elearning_akademik
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE elearning_akademik;

-- -------------------------------------------------------------------------
-- Tabel users (Admin, Dosen, Mahasiswa)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama       VARCHAR(120) NOT NULL,
  username   VARCHAR(50)  NOT NULL UNIQUE,
  email      VARCHAR(120) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('admin','dosen','mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  foto       VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (role)
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel mata_kuliah
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS mata_kuliah;
CREATE TABLE mata_kuliah (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode       VARCHAR(20) NOT NULL UNIQUE,
  nama       VARCHAR(120) NOT NULL,
  sks        TINYINT UNSIGNED NOT NULL DEFAULT 3,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_nama (nama)
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel dosen (profil, relasi ke users)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS dosen;
CREATE TABLE dosen (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  nidn       VARCHAR(20)  NOT NULL UNIQUE,
  prodi      VARCHAR(80)  DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel mahasiswa (profil, relasi ke users)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS mahasiswa;
CREATE TABLE mahasiswa (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  nim        VARCHAR(20) NOT NULL UNIQUE,
  prodi      VARCHAR(80) DEFAULT NULL,
  angkatan   YEAR DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel kelas
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS kelas;
CREATE TABLE kelas (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kelas    VARCHAR(30) NOT NULL,
  mata_kuliah_id INT UNSIGNED NOT NULL,
  dosen_id      INT UNSIGNED NOT NULL,
  semester      VARCHAR(10) DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
  FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel enrollments (mahasiswa -> kelas)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS enrollments;
CREATE TABLE enrollments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mahasiswa_id INT UNSIGNED NOT NULL,
  kelas_id   INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_enroll (mahasiswa_id, kelas_id),
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel tugas
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS tugas;
CREATE TABLE tugas (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kelas_id     INT UNSIGNED NOT NULL,
  judul        VARCHAR(160) NOT NULL,
  deskripsi    TEXT,
  deadline     DATETIME NOT NULL,
  file_materi  VARCHAR(255) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  INDEX idx_deadline (deadline)
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel submissions (jawaban tugas dari mahasiswa)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS submissions;
CREATE TABLE submissions (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tugas_id    INT UNSIGNED NOT NULL,
  mahasiswa_id INT UNSIGNED NOT NULL,
  file_jawaban VARCHAR(255) NOT NULL,
  waktu_submit DATETIME DEFAULT CURRENT_TIMESTAMP,
  nilai       DECIMAL(5,2) DEFAULT NULL,
  feedback    TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_submit (tugas_id, mahasiswa_id),
  FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel absensi
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS absensi;
CREATE TABLE absensi (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kelas_id      INT UNSIGNED NOT NULL,
  mahasiswa_id  INT UNSIGNED NOT NULL,
  tanggal       DATE NOT NULL,
  status        ENUM('hadir','izin','alpha') NOT NULL DEFAULT 'hadir',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_absen (kelas_id, mahasiswa_id, tanggal),
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel nilai (rekap akhir per mahasiswa per kelas)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS nilai;
CREATE TABLE nilai (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mahasiswa_id  INT UNSIGNED NOT NULL,
  kelas_id      INT UNSIGNED NOT NULL,
  nilai_tugas   DECIMAL(5,2) DEFAULT NULL,
  nilai_uts     DECIMAL(5,2) DEFAULT NULL,
  nilai_uas     DECIMAL(5,2) DEFAULT NULL,
  nilai_akhir   DECIMAL(5,2) DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_nilai (mahasiswa_id, kelas_id),
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabel pengumuman
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS pengumuman;
CREATE TABLE pengumuman (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul      VARCHAR(160) NOT NULL,
  isi        TEXT NOT NULL,
  tanggal    DATE NOT NULL,
  user_id    INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB;

-- =========================================================================
-- DATA CONTOH (Seed)
-- =========================================================================

INSERT INTO users (nama, username, email, password, role) VALUES
  ('Admin Pusat',                'admin',     'admin@campus.ac.id',          '$2y$10$exadminhashedpasswordw0ntwork', 'admin'),
  ('Dr. Andi Wijaya, M.Kom',     'andi.wijaya','andi.wijaya@campus.ac.id',   '$2y$10$exdosenhashedpasswordexample',  'dosen'),
  ('Siti Rahmawati, M.Pd',       'siti.r',    'siti.r@campus.ac.id',         '$2y$10$exdosenhashedpasswordexample',  'dosen'),
  ('Budi Santoso',               'budi',      'budisantoso@mhs.campus.ac.id','$2y$10$exmahasiswahashedexample',     'mahasiswa'),
  ('Dewi Lestari',               'dewi',      'dewi.lestari@mhs.campus.ac.id','$2y$10$exmahasiswahashedexample',   'mahasiswa'),
  ('Agus Pratama',               'agus',      'agus.p@mhs.campus.ac.id',     '$2y$10$exmahasiswahashedexample',     'mahasiswa'),
  ('Rina Marlina',               'rina',      'rina.m@mhs.campus.ac.id',     '$2y$10$exmahasiswahashedexample',     'mahasiswa');

INSERT INTO mata_kuliah (kode, nama, sks) VALUES
  ('IF101', 'Pemrograman Web', 3),
  ('IF102', 'Basis Data', 3),
  ('IF103', 'Algoritma & Struktur Data', 4),
  ('IF104', 'Pemrograman Berorientasi Objek', 3);

INSERT INTO pengumuman (judul, isi, tanggal, user_id) VALUES
  ('Jadwal UTS Semester Genap', 'Ujian Tengah Semester dilaksanakan mulai 15 Maret 2026.', '2026-02-12', 1),
  ('Perubahan Ruang Kuliah Basis Data', 'Kuliah IF-5B dipindah ke Lab Komputer 3 mulai minggu depan.', '2026-02-10', 1),
  ('Batas Akhir Pembayaran UKT', 'Pembayaran UKT angkatan 2025 paling lambat 28 Februari 2026.', '2026-02-08', 1);
