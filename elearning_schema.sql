-- Academic E-Learning System Database Schema
-- Import this file into phpMyAdmin to set up the database

-- Create Database
CREATE DATABASE IF NOT EXISTS elearning_system;
USE elearning_system;

-- Table: Users
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  nidn_nim VARCHAR(50),
  phone VARCHAR(20),
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (role),
  INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Courses
CREATE TABLE IF NOT EXISTS courses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  sks INT DEFAULT 3,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Classes
CREATE TABLE IF NOT EXISTS classes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  course_id INT NOT NULL,
  lecturer_id INT NOT NULL,
  semester INT,
  academic_year VARCHAR(20),
  max_students INT DEFAULT 40,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT,
  FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX (course_id),
  INDEX (lecturer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Class Enrollments
CREATE TABLE IF NOT EXISTS class_enrollments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT NOT NULL,
  student_id INT NOT NULL,
  enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active', 'dropped') DEFAULT 'active',
  UNIQUE KEY unique_enrollment (class_id, student_id),
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (class_id),
  INDEX (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Assignments
CREATE TABLE IF NOT EXISTS assignments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description LONGTEXT,
  deadline DATETIME,
  file_path VARCHAR(255),
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (class_id),
  INDEX (deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Submissions
CREATE TABLE IF NOT EXISTS submissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT NOT NULL,
  student_id INT NOT NULL,
  file_path VARCHAR(255),
  submission_notes TEXT,
  submitted_at DATETIME,
  grade INT,
  feedback TEXT,
  graded_by INT,
  graded_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (assignment_id),
  INDEX (student_id),
  INDEX (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Attendance
CREATE TABLE IF NOT EXISTS attendance (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT NOT NULL,
  student_id INT NOT NULL,
  meeting_number INT,
  status ENUM('hadir', 'izin', 'alpha') DEFAULT 'alpha',
  attendance_date DATE,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_attendance (class_id, student_id, attendance_date),
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (class_id),
  INDEX (student_id),
  INDEX (attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Announcements
CREATE TABLE IF NOT EXISTS announcements (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  content LONGTEXT NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (class_id),
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Grades
CREATE TABLE IF NOT EXISTS grades (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT NOT NULL,
  student_id INT NOT NULL,
  score INT,
  feedback TEXT,
  graded_by INT,
  graded_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (assignment_id),
  INDEX (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Data

-- Admin User
INSERT INTO users (username, email, password, role, first_name, last_name, nidn_nim) 
VALUES ('admin', 'admin@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'admin', 'Administrator', 'System', 'ADM001');

-- Lecturer Users
INSERT INTO users (username, email, password, role, first_name, last_name, nidn_nim) 
VALUES 
('rina_hartati', 'rina.hartati@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'dosen', 'Rina Hartati', '', '0012038501'),
('sutanto', 'sutanto@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'dosen', 'Sutanto', '', '0009027702'),
('yuni_kartika', 'yuni.kartika@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'dosen', 'Yuni Kartika', '', '0021048803');

-- Student Users
INSERT INTO users (username, email, password, role, first_name, last_name, nidn_nim) 
VALUES 
('ahmad_fauzi', 'ahmad.fauzi@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'mahasiswa', 'Ahmad', 'Fauzi', '2155201101'),
('siti_nurhaliza', 'siti.nurhaliza@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'mahasiswa', 'Siti', 'Nurhaliza', '2155201102'),
('budi_santoso', 'budi.santoso@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'mahasiswa', 'Budi', 'Santoso', '2155201103'),
('dewi_lestari', 'dewi.lestari@univ.ac.id', '$2y$10$rX5d3pFrW5Z8N9K2L1M0eOPqR4s3T2u1V0W9X8Y7Z6A5B4C3D2E1', 'mahasiswa', 'Dewi', 'Lestari', '2155201104');

-- Courses
INSERT INTO courses (code, name, sks, description) 
VALUES 
('TI201', 'Pemrograman Web', 3, 'Pembelajaran dasar hingga lanjut pemrograman web menggunakan HTML, CSS, PHP dan JavaScript'),
('TI305', 'Basis Data Lanjut', 3, 'Basis data relasional, SQL lanjut, normalisasi, dan optimisasi query'),
('TI410', 'Kecerdasan Buatan', 3, 'Pengenalan AI, machine learning, neural networks'),
('TI112', 'Algoritma & Struktur Data', 4, 'Analisis algoritma dan berbagai struktur data');

-- Classes
INSERT INTO classes (code, name, course_id, lecturer_id, semester, academic_year, max_students) 
VALUES 
('TI-3A', 'Pemrograman Web A', 1, 2, 3, '2026/2027', 32),
('TI-3B', 'Pemrograman Web B', 1, 2, 3, '2026/2027', 30),
('TI-5A', 'Basis Data Lanjut', 2, 3, 5, '2026/2027', 28),
('TI-5C', 'Kecerdasan Buatan', 3, 4, 5, '2026/2027', 26);

-- Class Enrollments (students in TI-3A)
INSERT INTO class_enrollments (class_id, student_id, status) 
VALUES 
(1, 5, 'active'),
(1, 6, 'active'),
(1, 7, 'active'),
(1, 8, 'active');

-- Assignments
INSERT INTO assignments (class_id, title, description, deadline, created_by, created_at) 
VALUES 
(1, 'Implementasi CRUD Laravel', 'Buat aplikasi CRUD sederhana menggunakan framework Laravel dengan validasi form dan file upload. Kumpulkan dalam bentuk file .zip beserta dokumentasi.', '2026-07-10 23:59:00', 2, NOW()),
(3, 'UTS Take Home', 'Ujian tengah semester dalam bentuk take home exam. Kerjakan soal-soal yang disediakan dan submit dalam format PDF.', '2026-07-02 23:59:00', 3, NOW()),
(3, 'Quiz Normalisasi DB', 'Quiz online tentang normalisasi basis data. Durasi 45 menit.', '2026-06-28 23:59:00', 3, NOW());

-- Submissions
INSERT INTO submissions (assignment_id, student_id, submitted_at, grade, feedback, graded_by, graded_at) 
VALUES 
(3, 5, '2026-06-27 20:10:00', 90, 'Pemahaman normalisasi sudah baik. Bagus!', 3, '2026-06-27 22:00:00'),
(1, 5, '2026-07-08 21:14:00', 88, 'Struktur kode rapi, tingkatkan validasi form.', 2, '2026-07-08 23:30:00'),
(1, 6, '2026-07-09 09:02:00', 92, 'Implementasi sangat bagus, UX sudah dipikirkan.', 2, '2026-07-09 11:00:00'),
(1, 7, '2026-07-09 22:40:00', NULL, NULL, NULL, NULL);

-- Attendance
INSERT INTO attendance (class_id, student_id, meeting_number, status, attendance_date, notes) 
VALUES 
(1, 5, 7, 'hadir', '2026-06-23', NULL),
(1, 6, 7, 'hadir', '2026-06-23', NULL),
(1, 7, 7, 'izin', '2026-06-23', 'Izin karena sakit'),
(1, 8, 7, 'alpha', '2026-06-23', NULL);

-- Announcements
INSERT INTO announcements (class_id, title, content, created_by, created_at) 
VALUES 
(1, 'Perubahan Jadwal Kuliah', 'Perkuliahan tanggal 3 Juli 2026 dipindahkan ke ruang Lab Komputer 2 pukul 09.00.', 2, '2026-06-30 10:30:00'),
(3, 'Pengumpulan Tugas Diperpanjang', 'Deadline tugas UTS take home diperpanjang hingga 2 Juli 2026 pukul 23.59.', 3, '2026-06-25 14:20:00');
