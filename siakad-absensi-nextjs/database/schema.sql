
-- Tabel: dosen
CREATE TABLE IF NOT EXISTS dosen (
    id_dosen INT(11) PRIMARY KEY AUTO_INCREMENT,
    nidn VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel: mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id_mahasiswa INT(11) PRIMARY KEY AUTO_INCREMENT,
    npm VARCHAR(20) UNIQUE NOT NULL,
    nama_mahasiswa VARCHAR(100) NOT NULL,
    program_studi VARCHAR(50) NOT NULL,
    angkatan YEAR NOT NULL,
    email VARCHAR(100) UNIQUE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel: mata_kuliah
CREATE TABLE IF NOT EXISTS mata_kuliah (
    id_matkul INT(11) PRIMARY KEY AUTO_INCREMENT,
    kode_matkul VARCHAR(10) UNIQUE NOT NULL,
    nama_matkul VARCHAR(100) NOT NULL,
    sks INT(2) NOT NULL,
    semester INT(2) NOT NULL,
    id_dosen INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dosen) REFERENCES dosen(id_dosen) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabel: kelas_mahasiswa
CREATE TABLE IF NOT EXISTS kelas_mahasiswa (
    id_kelas_mhs INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_mahasiswa INT(11) NOT NULL,
    id_matkul INT(11) NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_matkul) REFERENCES mata_kuliah(id_matkul) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (id_mahasiswa, id_matkul, tahun_ajaran)
);

-- Tabel: jadwal_perkuliahan
CREATE TABLE IF NOT EXISTS jadwal_perkuliahan (
    id_jadwal INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_matkul INT(11) NOT NULL,
    hari VARCHAR(10) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(20) NOT NULL,
    pertemuan_ke INT(2) NOT NULL,
    tanggal_pertemuan DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_matkul) REFERENCES mata_kuliah(id_matkul) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (id_matkul, pertemuan_ke, tanggal_pertemuan)
);

-- Tabel: absensi
CREATE TABLE IF NOT EXISTS absensi (
    id_absensi INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_mahasiswa INT(11) NOT NULL,
    id_jadwal INT(11) NOT NULL,
    status_kehadiran ENUM('Hadir', 'Sakit', 'Izin', 'Alpa', 'Terlambat') NOT NULL,
    keterangan TEXT DEFAULT NULL,
    waktu_input TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_perkuliahan(id_jadwal) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (id_mahasiswa, id_jadwal)
);

-- Data Dummy
INSERT INTO dosen (nidn, nama_lengkap, email, password) VALUES 
('12345678', 'Firansyah, S.Si., MM', 'firansyah@univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

INSERT INTO mahasiswa (npm, nama_mahasiswa, program_studi, angkatan, email) VALUES 
('2021001', 'Budi Santoso', 'Teknik Informatika', 2021, 'budi@student.univ.ac.id'),
('2021002', 'Siti Aminah', 'Teknik Informatika', 2021, 'siti@student.univ.ac.id'),
('2021003', 'Andi Wijaya', 'Teknik Informatika', 2021, 'andi@student.univ.ac.id'),
('2021004', 'Dewi Lestari', 'Sistem Informasi', 2021, 'dewi@student.univ.ac.id'),
('2021005', 'Rian Hidayat', 'Sistem Informasi', 2021, 'rian@student.univ.ac.id');

INSERT INTO mata_kuliah (kode_matkul, nama_matkul, sks, semester, id_dosen) VALUES 
('MK001', 'Pemrograman Web', 3, 4, 1),
('MK002', 'Basis Data', 3, 4, 1),
('MK003', 'Analisis Algoritma', 3, 4, 1);

INSERT INTO kelas_mahasiswa (id_mahasiswa, id_matkul, tahun_ajaran) VALUES 
(1, 1, '2023/2024'), (2, 1, '2023/2024'), (3, 1, '2023/2024'), (4, 1, '2023/2024'), (5, 1, '2023/2024'),
(1, 2, '2023/2024'), (2, 2, '2023/2024'), (3, 2, '2023/2024'),
(4, 3, '2023/2024'), (5, 3, '2023/2024');

INSERT INTO jadwal_perkuliahan (id_matkul, hari, jam_mulai, jam_selesai, ruangan, pertemuan_ke, tanggal_pertemuan) VALUES 
(1, 'Senin', '08:00:00', '10:30:00', 'Lab 1', 1, '2026-07-06'),
(1, 'Senin', '08:00:00', '10:30:00', 'Lab 1', 2, '2026-07-13'),
(2, 'Selasa', '10:00:00', '12:30:00', 'R. 302', 1, '2026-07-07'),
(3, 'Rabu', '13:00:00', '15:30:00', 'R. 405', 1, '2026-07-08');
