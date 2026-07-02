-- =====================================================
-- SISTEM E-LEARNING AKADEMIK - DATABASE SCHEMA
-- =====================================================

-- Tabel Jurusan
CREATE TABLE IF NOT EXISTS jurusan (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kode_jurusan VARCHAR(2) UNIQUE NOT NULL,
  nama_jurusan VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Users (Core Authentication)
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(8) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  nama_depan VARCHAR(50) NOT NULL,
  email VARCHAR(100),
  role ENUM('mahasiswa', 'dosen', 'admin') NOT NULL,
  status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_role (role)
);

-- Tabel Mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL UNIQUE,
  nim VARCHAR(20) UNIQUE,
  jurusan_id INT NOT NULL,
  tahun_angkatan INT NOT NULL,
  semester_saat_ini INT DEFAULT 1,
  total_sks_diambil INT DEFAULT 0,
  total_sks_lulus INT DEFAULT 0,
  ipk DECIMAL(3,2) DEFAULT 0.00,
  status_akademik ENUM('aktif', 'cuti', 'lulus', 'drop') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (jurusan_id) REFERENCES jurusan(id),
  INDEX idx_jurusan (jurusan_id),
  INDEX idx_tahun_angkatan (tahun_angkatan)
);

-- Tabel Dosen
CREATE TABLE IF NOT EXISTS dosen (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL UNIQUE,
  nip VARCHAR(20) UNIQUE,
  jurusan_id INT NOT NULL,
  tahun_mulai_mengajar INT NOT NULL,
  status_akademik ENUM('aktif', 'cuti', 'pensiun') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (jurusan_id) REFERENCES jurusan(id),
  INDEX idx_jurusan (jurusan_id)
);

-- Tabel Matakuliah
CREATE TABLE IF NOT EXISTS matakuliah (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kode_matkul VARCHAR(10) UNIQUE NOT NULL,
  nama_matkul VARCHAR(100) NOT NULL,
  sks INT NOT NULL,
  semester INT NOT NULL,
  jurusan_id INT NOT NULL,
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (jurusan_id) REFERENCES jurusan(id),
  INDEX idx_semester (semester),
  INDEX idx_jurusan (jurusan_id)
);

-- Tabel Kelas
CREATE TABLE IF NOT EXISTS kelas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  matakuliah_id INT NOT NULL,
  dosen_id INT NOT NULL,
  kode_kelas VARCHAR(10) UNIQUE NOT NULL,
  semester INT NOT NULL,
  tahun_akademik VARCHAR(9) NOT NULL,
  hari VARCHAR(20) NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  ruangan VARCHAR(50),
  kapasitas INT DEFAULT 40,
  jumlah_mahasiswa INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id),
  FOREIGN KEY (dosen_id) REFERENCES dosen(id),
  INDEX idx_dosen (dosen_id),
  INDEX idx_matakuliah (matakuliah_id),
  INDEX idx_tahun_akademik (tahun_akademik)
);

-- Tabel KRS (Kartu Rencana Studi)
CREATE TABLE IF NOT EXISTS krs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  mahasiswa_id INT NOT NULL,
  matakuliah_id INT NOT NULL,
  kelas_id INT NOT NULL,
  semester INT NOT NULL,
  tahun_akademik VARCHAR(9) NOT NULL,
  status_krs ENUM('diambil', 'lulus', 'gagal', 'batal') DEFAULT 'diambil',
  nilai_akhir DECIMAL(5,2),
  grade VARCHAR(2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
  FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id),
  FOREIGN KEY (kelas_id) REFERENCES kelas(id),
  UNIQUE KEY unique_krs (mahasiswa_id, matakuliah_id, tahun_akademik),
  INDEX idx_mahasiswa (mahasiswa_id),
  INDEX idx_matakuliah (matakuliah_id),
  INDEX idx_tahun_akademik (tahun_akademik)
);

-- Tabel Absensi
CREATE TABLE IF NOT EXISTS absensi (
  id INT PRIMARY KEY AUTO_INCREMENT,
  krs_id INT NOT NULL,
  pertemuan INT NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('hadir', 'izin', 'sakit', 'alpa') DEFAULT 'alpa',
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (krs_id) REFERENCES krs(id) ON DELETE CASCADE,
  UNIQUE KEY unique_absensi (krs_id, pertemuan, tanggal),
  INDEX idx_krs (krs_id),
  INDEX idx_tanggal (tanggal)
);

-- Tabel Nilai
CREATE TABLE IF NOT EXISTS nilai (
  id INT PRIMARY KEY AUTO_INCREMENT,
  krs_id INT NOT NULL,
  jenis_nilai ENUM('tugas', 'kuis', 'uts', 'uas') NOT NULL,
  nilai DECIMAL(5,2) NOT NULL,
  bobot INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (krs_id) REFERENCES krs(id) ON DELETE CASCADE,
  INDEX idx_krs (krs_id),
  INDEX idx_jenis (jenis_nilai)
);

-- Tabel Materi
CREATE TABLE IF NOT EXISTS materi (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kelas_id INT NOT NULL,
  judul_materi VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  file_path VARCHAR(255),
  tipe_file VARCHAR(50),
  ukuran_file INT,
  pertemuan INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  UNIQUE KEY unique_materi_kelas_pertemuan_judul (kelas_id, pertemuan, judul_materi),
  INDEX idx_kelas (kelas_id),
  INDEX idx_pertemuan (pertemuan)
);

-- Tabel Pengumuman
CREATE TABLE IF NOT EXISTS pengumuman (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kelas_id INT NOT NULL,
  dosen_id INT NOT NULL,
  judul VARCHAR(100) NOT NULL,
  konten TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
  FOREIGN KEY (dosen_id) REFERENCES dosen(id),
  INDEX idx_kelas (kelas_id),
  INDEX idx_dosen (dosen_id),
  INDEX idx_created (created_at)
);

-- Tabel Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  action VARCHAR(100) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user (user_id),
  INDEX idx_created (created_at)
);

-- =====================================================
-- INDEXES UNTUK PERFORMA
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_users_role_status ON users(role, status);
CREATE INDEX IF NOT EXISTS idx_krs_status ON krs(status_krs);
CREATE INDEX IF NOT EXISTS idx_matakuliah_semester_jurusan ON matakuliah(semester, jurusan_id);
