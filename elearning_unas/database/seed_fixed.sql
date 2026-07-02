-- =====================================================
-- SEED DATA - MAHASISWA & DOSEN
-- =====================================================

-- Insert Jurusan
INSERT IGNORE INTO jurusan (kode_jurusan, nama_jurusan, deskripsi) VALUES
('11', 'Sistem Informasi', 'Program Studi Sistem Informasi'),
('12', 'Informatika', 'Program Studi Informatika');

-- =====================================================
-- INSERT USERS
-- Password seed memakai pola nama depan + 3 angka (001-010) agar mudah login lokal.
-- =====================================================

INSERT INTO users (username, password, nama_lengkap, nama_depan, email, role, status)
WITH RECURSIVE seq AS (
  SELECT 1 AS n
  UNION ALL
  SELECT n + 1 FROM seq WHERE n < 50
),
names AS (
  SELECT
    n,
    CASE MOD(n - 1, 25) + 1
      WHEN 1 THEN 'Ahmad'
      WHEN 2 THEN 'Budi'
      WHEN 3 THEN 'Citra'
      WHEN 4 THEN 'Dewi'
      WHEN 5 THEN 'Eka'
      WHEN 6 THEN 'Farhan'
      WHEN 7 THEN 'Gita'
      WHEN 8 THEN 'Hana'
      WHEN 9 THEN 'Indra'
      WHEN 10 THEN 'Joko'
      WHEN 11 THEN 'Karin'
      WHEN 12 THEN 'Lukman'
      WHEN 13 THEN 'Maya'
      WHEN 14 THEN 'Nadia'
      WHEN 15 THEN 'Oscar'
      WHEN 16 THEN 'Putri'
      WHEN 17 THEN 'Rizky'
      WHEN 18 THEN 'Salsa'
      WHEN 19 THEN 'Taufik'
      WHEN 20 THEN 'Umar'
      WHEN 21 THEN 'Vina'
      WHEN 22 THEN 'Wahyu'
      WHEN 23 THEN 'Yulia'
      WHEN 24 THEN 'Zaki'
      ELSE 'Rani'
    END AS nama_depan,
    CASE FLOOR((n - 1) / 25)
      WHEN 0 THEN 'Pratama'
      ELSE 'Saputra'
    END AS nama_belakang
  FROM seq
)
SELECT
  CONCAT('252111', LPAD(n, 2, '0')),
  CONCAT(nama_depan, LPAD(MOD(n * 7, 10) + 1, 3, '0')),
  CONCAT(nama_depan, ' ', nama_belakang),
  nama_depan,
  CONCAT('si', LPAD(n, 2, '0'), '@student.unas.ac.id'),
  'mahasiswa',
  'aktif'
FROM names
ON DUPLICATE KEY UPDATE
  password = VALUES(password),
  nama_lengkap = VALUES(nama_lengkap),
  nama_depan = VALUES(nama_depan),
  email = VALUES(email),
  role = VALUES(role),
  status = VALUES(status);

INSERT INTO users (username, password, nama_lengkap, nama_depan, email, role, status)
WITH RECURSIVE seq AS (
  SELECT 1 AS n
  UNION ALL
  SELECT n + 1 FROM seq WHERE n < 50
),
names AS (
  SELECT
    n,
    CASE MOD(n - 1, 25) + 1
      WHEN 1 THEN 'Aditya'
      WHEN 2 THEN 'Bella'
      WHEN 3 THEN 'Dimas'
      WHEN 4 THEN 'Fauzan'
      WHEN 5 THEN 'Intan'
      WHEN 6 THEN 'Kevin'
      WHEN 7 THEN 'Laras'
      WHEN 8 THEN 'Maulana'
      WHEN 9 THEN 'Naufal'
      WHEN 10 THEN 'Olivia'
      WHEN 11 THEN 'Pandu'
      WHEN 12 THEN 'Qori'
      WHEN 13 THEN 'Ratna'
      WHEN 14 THEN 'Satria'
      WHEN 15 THEN 'Tiara'
      WHEN 16 THEN 'Usman'
      WHEN 17 THEN 'Valen'
      WHEN 18 THEN 'Wulan'
      WHEN 19 THEN 'Yoga'
      WHEN 20 THEN 'Zahra'
      WHEN 21 THEN 'Bagas'
      WHEN 22 THEN 'Chika'
      WHEN 23 THEN 'Fikri'
      WHEN 24 THEN 'Nabila'
      ELSE 'Raka'
    END AS nama_depan,
    CASE FLOOR((n - 1) / 25)
      WHEN 0 THEN 'Wijaya'
      ELSE 'Lestari'
    END AS nama_belakang
  FROM seq
)
SELECT
  CONCAT('252121', LPAD(n, 2, '0')),
  CONCAT(nama_depan, LPAD(MOD(n * 7, 10) + 1, 3, '0')),
  CONCAT(nama_depan, ' ', nama_belakang),
  nama_depan,
  CONCAT('if', LPAD(n, 2, '0'), '@student.unas.ac.id'),
  'mahasiswa',
  'aktif'
FROM names
ON DUPLICATE KEY UPDATE
  password = VALUES(password),
  nama_lengkap = VALUES(nama_lengkap),
  nama_depan = VALUES(nama_depan),
  email = VALUES(email),
  role = VALUES(role),
  status = VALUES(status);

INSERT INTO users (username, password, nama_lengkap, nama_depan, email, role, status) VALUES
('35112001', 'Rendra008', 'Dr. Rendra Kusuma', 'Rendra', 'rendra.kusuma@unas.ac.id', 'dosen', 'aktif'),
('35112010', 'Siti005', 'Dr. Siti Maharani', 'Siti', 'siti.maharani@unas.ac.id', 'dosen', 'aktif'),
('35112015', 'Arif010', 'Arif Hidayat, M.Kom.', 'Arif', 'arif.hidayat@unas.ac.id', 'dosen', 'aktif'),
('35122005', 'Bayu006', 'Dr. Bayu Santoso', 'Bayu', 'bayu.santoso@unas.ac.id', 'dosen', 'aktif'),
('35122010', 'Nina003', 'Nina Kartika, M.T.', 'Nina', 'nina.kartika@unas.ac.id', 'dosen', 'aktif'),
('35122015', 'Dodi009', 'Dodi Firmansyah, M.Kom.', 'Dodi', 'dodi.firmansyah@unas.ac.id', 'dosen', 'aktif'),
('15123416', 'Admin007', 'Admin Akademik Utama', 'Admin', 'admin.akademik@unas.ac.id', 'admin', 'aktif'),
('15456724', 'Operator004', 'Operator Sistem E-Learning', 'Operator', 'operator.elearning@unas.ac.id', 'admin', 'aktif')
ON DUPLICATE KEY UPDATE
  password = VALUES(password),
  nama_lengkap = VALUES(nama_lengkap),
  nama_depan = VALUES(nama_depan),
  email = VALUES(email),
  role = VALUES(role),
  status = VALUES(status);

-- =====================================================
-- INSERT MAHASISWA (50 SI + 50 Informatika)
-- =====================================================

-- Mahasiswa SI (25211101-25211150)
INSERT IGNORE INTO mahasiswa (user_id, nim, jurusan_id, tahun_angkatan, semester_saat_ini, total_sks_diambil, status_akademik)
SELECT id, CONCAT('25211', LPAD(ROW_NUMBER() OVER (ORDER BY id), 3, '0')), 1, 2025, 1, 0, 'aktif'
FROM users WHERE role = 'mahasiswa' AND username >= '25211101' AND username <= '25211150' LIMIT 50;

-- Mahasiswa Informatika (25212101-25212150)
INSERT IGNORE INTO mahasiswa (user_id, nim, jurusan_id, tahun_angkatan, semester_saat_ini, total_sks_diambil, status_akademik)
SELECT id, CONCAT('25212', LPAD(ROW_NUMBER() OVER (ORDER BY id), 3, '0')), 2, 2025, 1, 0, 'aktif'
FROM users WHERE role = 'mahasiswa' AND username >= '25212101' AND username <= '25212150' LIMIT 50;

-- =====================================================
-- INSERT DOSEN
-- =====================================================

INSERT IGNORE INTO dosen (user_id, nip, jurusan_id, tahun_mulai_mengajar, status_akademik)
VALUES
((SELECT id FROM users WHERE username = '35112001'), 'NIP001', 1, 2001, 'aktif'),
((SELECT id FROM users WHERE username = '35112010'), 'NIP002', 1, 2010, 'aktif'),
((SELECT id FROM users WHERE username = '35112015'), 'NIP003', 1, 2015, 'aktif'),
((SELECT id FROM users WHERE username = '35122005'), 'NIP004', 2, 2005, 'aktif'),
((SELECT id FROM users WHERE username = '35122010'), 'NIP005', 2, 2010, 'aktif'),
((SELECT id FROM users WHERE username = '35122015'), 'NIP006', 2, 2015, 'aktif');

-- =====================================================
-- INSERT MATAKULIAH (144 SKS - 36 Matakuliah)
-- =====================================================

-- Semester 1 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI101', 'Pengantar Sistem Informasi', 3, 1, 1, 'Pengenalan dasar sistem informasi'),
('SI102', 'Pemrograman Dasar', 4, 1, 1, 'Pemrograman menggunakan bahasa pemrograman'),
('SI103', 'Matematika Diskrit', 3, 1, 1, 'Matematika untuk ilmu komputer'),
('SI104', 'Logika Informatika', 2, 1, 1, 'Logika dalam sistem informasi');

-- Semester 2 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI201', 'Struktur Data', 4, 2, 1, 'Struktur data dan algoritma'),
('SI202', 'Basis Data', 3, 2, 1, 'Desain dan implementasi basis data'),
('SI203', 'Algoritma', 3, 2, 1, 'Analisis dan desain algoritma'),
('SI204', 'Pemrograman Web', 2, 2, 1, 'Pengembangan aplikasi web');

-- Semester 3 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI301', 'Sistem Operasi', 3, 3, 1, 'Konsep sistem operasi'),
('SI302', 'Jaringan Komputer', 4, 3, 1, 'Arsitektur dan protokol jaringan'),
('SI303', 'Keamanan Informasi', 3, 3, 1, 'Keamanan sistem informasi'),
('SI304', 'Interaksi Manusia Komputer', 2, 3, 1, 'Desain antarmuka pengguna');

-- Semester 4 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI401', 'Rekayasa Perangkat Lunak', 4, 4, 1, 'Metodologi pengembangan software'),
('SI402', 'Basis Data Lanjut', 3, 4, 1, 'Topik lanjut basis data'),
('SI403', 'Pemrograman Berorientasi Objek', 3, 4, 1, 'OOP dan design patterns'),
('SI404', 'Manajemen Proyek', 2, 4, 1, 'Manajemen proyek IT');

-- Semester 5 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI501', 'Kecerdasan Buatan', 3, 5, 1, 'Konsep dan aplikasi AI'),
('SI502', 'Data Mining', 4, 5, 1, 'Teknik ekstraksi pengetahuan dari data'),
('SI503', 'Sistem Informasi Manajemen', 3, 5, 1, 'Sistem informasi untuk manajemen'),
('SI504', 'Etika Informatika', 2, 5, 1, 'Etika dalam penggunaan teknologi');

-- Semester 6 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI601', 'Cloud Computing', 3, 6, 1, 'Teknologi cloud dan aplikasinya'),
('SI602', 'Big Data', 4, 6, 1, 'Manajemen dan analisis big data'),
('SI603', 'Business Intelligence', 3, 6, 1, 'Analitik bisnis dan business intelligence'),
('SI604', 'Audit Sistem Informasi', 2, 6, 1, 'Audit dan compliance IT');

-- Semester 7 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI701', 'Internet of Things', 3, 7, 1, 'Konsep dan aplikasi IoT'),
('SI702', 'Mobile Application Development', 4, 7, 1, 'Pengembangan aplikasi mobile'),
('SI703', 'Blockchain', 3, 7, 1, 'Teknologi blockchain dan aplikasinya'),
('SI704', 'Capstone Project', 2, 7, 1, 'Proyek akhir studi');

-- Semester 8 - SI (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('SI801', 'Cybersecurity', 3, 8, 1, 'Keamanan siber tingkat lanjut'),
('SI802', 'DevOps', 4, 8, 1, 'Development dan operations integration'),
('SI803', 'Machine Learning', 3, 8, 1, 'Pembelajaran mesin dan aplikasinya'),
('SI804', 'Tugas Akhir', 2, 8, 1, 'Tugas akhir / skripsi');

-- Semester 1 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF101', 'Pengantar Informatika', 3, 1, 2, 'Pengenalan dasar informatika'),
('IF102', 'Pemrograman Procedural', 4, 1, 2, 'Pemrograman procedural'),
('IF103', 'Matematika Komputasi', 3, 1, 2, 'Matematika untuk komputasi'),
('IF104', 'Pengantar Sistem Komputer', 2, 1, 2, 'Arsitektur sistem komputer');

-- Semester 2 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF201', 'Struktur Data dan Algoritma', 4, 2, 2, 'Struktur data dan algoritma'),
('IF202', 'Database Design', 3, 2, 2, 'Desain basis data'),
('IF203', 'Analisis Algoritma', 3, 2, 2, 'Analisis kompleksitas algoritma'),
('IF204', 'Pemrograman Web Dasar', 2, 2, 2, 'Web programming basics');

-- Semester 3 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF301', 'Sistem Operasi', 3, 3, 2, 'Konsep sistem operasi'),
('IF302', 'Jaringan Komputer', 4, 3, 2, 'Jaringan dan komunikasi data'),
('IF303', 'Keamanan Komputer', 3, 3, 2, 'Keamanan sistem komputer'),
('IF304', 'User Interface Design', 2, 3, 2, 'Desain antarmuka pengguna');

-- Semester 4 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF401', 'Software Engineering', 4, 4, 2, 'Rekayasa perangkat lunak'),
('IF402', 'Advanced Database', 3, 4, 2, 'Basis data lanjut'),
('IF403', 'Object Oriented Programming', 3, 4, 2, 'Pemrograman berorientasi objek'),
('IF404', 'Project Management', 2, 4, 2, 'Manajemen proyek');

-- Semester 5 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF501', 'Artificial Intelligence', 3, 5, 2, 'Kecerdasan buatan'),
('IF502', 'Data Science', 4, 5, 2, 'Ilmu data dan analitik'),
('IF503', 'Enterprise Systems', 3, 5, 2, 'Sistem enterprise'),
('IF504', 'IT Ethics', 2, 5, 2, 'Etika dalam IT');

-- Semester 6 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF601', 'Cloud Technologies', 3, 6, 2, 'Teknologi cloud'),
('IF602', 'Large Scale Data', 4, 6, 2, 'Manajemen data skala besar'),
('IF603', 'Analytics', 3, 6, 2, 'Analitik data'),
('IF604', 'IT Compliance', 2, 6, 2, 'Compliance dan audit IT');

-- Semester 7 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF701', 'IoT Systems', 3, 7, 2, 'Sistem IoT'),
('IF702', 'Mobile Development', 4, 7, 2, 'Pengembangan aplikasi mobile'),
('IF703', 'Distributed Ledger', 3, 7, 2, 'Teknologi blockchain'),
('IF704', 'Final Project', 2, 7, 2, 'Proyek akhir');

-- Semester 8 - Informatika (12 SKS)
INSERT IGNORE INTO matakuliah (kode_matkul, nama_matkul, sks, semester, jurusan_id, deskripsi) VALUES
('IF801', 'Advanced Security', 3, 8, 2, 'Keamanan tingkat lanjut'),
('IF802', 'DevOps Engineering', 4, 8, 2, 'DevOps dan continuous integration'),
('IF803', 'Deep Learning', 3, 8, 2, 'Deep learning dan neural networks'),
('IF804', 'Thesis', 2, 8, 2, 'Tugas akhir / thesis');

-- =====================================================
-- INSERT KELAS (36 Kelas - 1 kelas per matakuliah)
-- =====================================================

-- Kelas SI
INSERT IGNORE INTO kelas (matakuliah_id, dosen_id, kode_kelas, semester, tahun_akademik, hari, jam_mulai, jam_selesai, ruangan, kapasitas, jumlah_mahasiswa)
SELECT m.id, (SELECT id FROM dosen WHERE user_id = (SELECT id FROM users WHERE username = '35112001')), 
CONCAT(m.kode_matkul, '-A'), m.semester, '2025/2026', 'Senin', '08:00:00', '10:00:00', 'Ruang 101', 40, 0
FROM matakuliah m WHERE m.jurusan_id = 1;

-- Kelas Informatika
INSERT IGNORE INTO kelas (matakuliah_id, dosen_id, kode_kelas, semester, tahun_akademik, hari, jam_mulai, jam_selesai, ruangan, kapasitas, jumlah_mahasiswa)
SELECT m.id, (SELECT id FROM dosen WHERE user_id = (SELECT id FROM users WHERE username = '35122005')), 
CONCAT(m.kode_matkul, '-A'), m.semester, '2025/2026', 'Selasa', '08:00:00', '10:00:00', 'Ruang 201', 40, 0
FROM matakuliah m WHERE m.jurusan_id = 2;

-- =====================================================
-- INSERT KRS (Kartu Rencana Studi)
-- =====================================================

-- KRS untuk mahasiswa SI semester 1
INSERT IGNORE INTO krs (mahasiswa_id, kelas_id, matakuliah_id, tahun_akademik, semester, status_krs, nilai_akhir, grade)
SELECT m.id, k.id, k.matakuliah_id, '2025/2026', 1, 'diambil', NULL, NULL
FROM mahasiswa m
JOIN kelas k ON k.matakuliah_id IN (SELECT id FROM matakuliah WHERE jurusan_id = 1 AND semester = 1)
WHERE m.jurusan_id = 1
LIMIT 100;

-- KRS untuk mahasiswa Informatika semester 1
INSERT IGNORE INTO krs (mahasiswa_id, kelas_id, matakuliah_id, tahun_akademik, semester, status_krs, nilai_akhir, grade)
SELECT m.id, k.id, k.matakuliah_id, '2025/2026', 1, 'diambil', NULL, NULL
FROM mahasiswa m
JOIN kelas k ON k.matakuliah_id IN (SELECT id FROM matakuliah WHERE jurusan_id = 2 AND semester = 1)
WHERE m.jurusan_id = 2
LIMIT 100;

-- =====================================================
-- INSERT NILAI (Contoh nilai untuk beberapa mahasiswa)
-- =====================================================

UPDATE krs SET nilai_akhir = ROUND(RAND() * 40 + 60, 0), grade = 'A' WHERE nilai_akhir IS NULL LIMIT 50;

-- =====================================================
-- INSERT ABSENSI (Contoh absensi)
-- =====================================================

INSERT IGNORE INTO absensi (krs_id, pertemuan, tanggal, status, keterangan)
SELECT k.id, 1, CURDATE(), 'hadir', 'Hadir'
FROM krs k LIMIT 100;

-- =====================================================
-- INSERT MATERI
-- =====================================================

ALTER IGNORE TABLE materi
ADD UNIQUE KEY IF NOT EXISTS unique_materi_kelas_pertemuan_judul (kelas_id, pertemuan, judul_materi);

INSERT IGNORE INTO materi (kelas_id, judul_materi, deskripsi, file_path, tipe_file, pertemuan)
SELECT k.id, CONCAT('Materi Pertemuan 1 - ', m.nama_matkul), 'Materi pembelajaran pertemuan pertama', 
'/uploads/materi_1.pdf', 'pdf', 1
FROM kelas k
JOIN matakuliah m ON k.matakuliah_id = m.id
LIMIT 36;

-- =====================================================
-- SELESAI
-- =====================================================
