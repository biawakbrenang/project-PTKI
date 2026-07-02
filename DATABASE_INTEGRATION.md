# Database Integration Guide

## Overview

Sistem ini telah diintegrasikan dengan **MySQL Database** melalui **phpMyAdmin**. Semua data pengguna, kelas, tugas, dan aktivitas tersimpan dalam database relasional.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Browser / Frontend                        │
│              (HTML + CSS + JavaScript)                       │
└──────────────────────────┬──────────────────────────────────┘
                           │
                    AJAX / Form POST
                           │
┌──────────────────────────┴──────────────────────────────────┐
│                    PHP Backend                              │
│  ┌──────────────┐  ┌────────────┐  ┌──────────────────┐   │
│  │  auth.php    │  │  api.php   │  │  config.php      │   │
│  └──────────────┘  └────────────┘  └──────────────────┘   │
└──────────────────────────┬──────────────────────────────────┘
                           │
                     mysqli / PDO
                           │
┌──────────────────────────┴──────────────────────────────────┐
│                  MySQL Database                             │
│  ┌──────────┐ ┌─────────┐ ┌────────┐ ┌──────────────┐     │
│  │  users   │ │ classes │ │ grades │ │ submissions  │     │
│  └──────────┘ └─────────┘ └────────┘ └──────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

## Instalasi & Konfigurasi

### Langkah 1: Setup Database

**Menggunakan phpMyAdmin:**

1. Buka http://localhost/phpmyadmin
2. Login dengan username `root`
3. Pilih menu **Import**
4. Upload file `sql/elearning_schema.sql`
5. Klik **Go** untuk mengeksekusi

**Database yang dibuat:**
- Nama: `elearning_system`
- Charset: `utf8mb4_unicode_ci`

### Langkah 2: Konfigurasi Koneksi

Edit file `php/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Kosong jika tidak ada password
define('DB_NAME', 'elearning_system');
```

### Langkah 3: File Permissions

```bash
# Linux/Mac
chmod 755 php/
chmod 644 php/*.php
chmod 777 uploads/

# Windows (via File Properties)
# Set Read/Write permission untuk folder uploads/
```

## Database Schema

### 1. Users Table

```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) UNIQUE,
  email VARCHAR(120) UNIQUE,
  password VARCHAR(255),           -- Hashed dengan bcrypt
  role ENUM('admin', 'dosen', 'mahasiswa'),
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  nidn_nim VARCHAR(50),            -- NIDN untuk dosen, NIM untuk mahasiswa
  phone VARCHAR(20),
  status ENUM('active', 'inactive'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Sample Data:**
```
Username: admin
Password: admin123 (hashed)
Role: admin
Email: admin@univ.ac.id

Username: rina_hartati
Password: admin123 (hashed)
Role: dosen
Email: rina.hartati@univ.ac.id
NIDN: 0012038501

Username: ahmad_fauzi
Password: admin123 (hashed)
Role: mahasiswa
Email: ahmad.fauzi@univ.ac.id
NIM: 2155201101
```

### 2. Courses Table

```sql
CREATE TABLE courses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) UNIQUE,         -- TI201, TI305, dll
  name VARCHAR(150),               -- Pemrograman Web, Basis Data, dll
  sks INT,                         -- Satuan Kredit Semester
  description TEXT,
  created_at TIMESTAMP
);
```

### 3. Classes Table

```sql
CREATE TABLE classes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) UNIQUE,         -- TI-3A, TI-3B, dll
  name VARCHAR(100),
  course_id INT,                   -- Foreign key ke courses
  lecturer_id INT,                 -- Foreign key ke users (role='dosen')
  semester INT,                    -- Semester ke-
  academic_year VARCHAR(20),       -- 2026/2027
  max_students INT,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (lecturer_id) REFERENCES users(id)
);
```

### 4. Class Enrollments Table

```sql
CREATE TABLE class_enrollments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT,                    -- Foreign key ke classes
  student_id INT,                  -- Foreign key ke users (role='mahasiswa')
  enrollment_date TIMESTAMP,
  status ENUM('active', 'dropped'),
  UNIQUE(class_id, student_id)     -- Satu mahasiswa hanya bisa enroll sekali per kelas
);
```

### 5. Assignments Table

```sql
CREATE TABLE assignments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT,                    -- Foreign key ke classes
  title VARCHAR(150),
  description LONGTEXT,
  deadline DATETIME,               -- Batas waktu pengumpulan
  file_path VARCHAR(255),          -- Path file materi (opsional)
  created_by INT,                  -- Foreign key ke users (dosen)
  created_at TIMESTAMP
);
```

### 6. Submissions Table

```sql
CREATE TABLE submissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT,               -- Foreign key ke assignments
  student_id INT,                  -- Foreign key ke users (mahasiswa)
  file_path VARCHAR(255),          -- File jawaban yang diupload
  submitted_at DATETIME,
  grade INT,                       -- Nilai (0-100)
  feedback TEXT,                   -- Komentar dari dosen
  graded_by INT,                   -- ID dosen yang menilai
  graded_at DATETIME,
  FOREIGN KEY (assignment_id) REFERENCES assignments(id),
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (graded_by) REFERENCES users(id)
);
```

### 7. Attendance Table

```sql
CREATE TABLE attendance (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT,                    -- Foreign key ke classes
  student_id INT,                  -- Foreign key ke users (mahasiswa)
  meeting_number INT,              -- Pertemuan ke-
  status ENUM('hadir', 'izin', 'alpha'),
  attendance_date DATE,
  notes TEXT,
  UNIQUE(class_id, student_id, attendance_date) -- Satu record per mahasiswa per hari
);
```

### 8. Announcements Table

```sql
CREATE TABLE announcements (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT,                    -- Foreign key ke classes
  title VARCHAR(150),
  content LONGTEXT,
  created_by INT,                  -- Foreign key ke users (dosen)
  created_at TIMESTAMP
);
```

### 9. Grades Table

```sql
CREATE TABLE grades (
  id INT PRIMARY KEY AUTO_INCREMENT,
  assignment_id INT,
  student_id INT,
  score INT,
  feedback TEXT,
  graded_by INT,
  graded_at DATETIME
);
```

## API Endpoints

Semua request dikirim ke `api.php` dengan method `POST`:

### Authentication

**Login:**
```javascript
POST /php/auth.php
{
  action: 'login',
  username: 'ahmad_fauzi',
  password: 'admin123',
  role: 'mahasiswa'
}
```

**Logout:**
```javascript
POST /php/auth.php
{
  action: 'logout'
}
```

### Data Operations

**Get Dashboard Data:**
```javascript
POST /php/api.php
{
  action: 'getDashboardData'
}
```

**Get Classes:**
```javascript
POST /php/api.php
{
  action: 'getClasses'
}
```

**Get Assignments:**
```javascript
POST /php/api.php
{
  action: 'getAssignments',
  class_id: 1  // Optional
}
```

**Submit Assignment:**
```javascript
POST /php/api.php (multipart/form-data)
{
  action: 'submitAssignment',
  assignment_id: 5,
  file: File,        // File upload
  notes: 'Catatan'
}
```

**Get Submissions:**
```javascript
POST /php/api.php
{
  action: 'getSubmissions',
  assignment_id: 5
}
```

**Grade Submission:**
```javascript
POST /php/api.php
{
  action: 'gradeSubmission',
  submission_id: 12,
  grade: 85,
  feedback: 'Bagus!'
}
```

**Mark Attendance (Mahasiswa):**
```javascript
POST /php/api.php
{
  action: 'markAttendance'
}
```

**Record Attendance (Dosen):**
```javascript
POST /php/api.php
{
  action: 'recordAttendance',
  class_id: 1,
  student_id: 5,
  status: 'hadir',
  attendance_date: '2026-07-02'
}
```

**Get Announcements:**
```javascript
POST /php/api.php
{
  action: 'getAnnouncements',
  class_id: 1  // Optional
}
```

**Create Announcement (Dosen):**
```javascript
POST /php/api.php
{
  action: 'createAnnouncement',
  class_id: 1,
  title: 'Judul Pengumuman',
  content: 'Isi pengumuman...'
}
```

## Password Hashing

Semua password disimpan dalam format terenkripsi menggunakan **bcrypt** (PHP's `password_hash` function):

```php
// Generate hash (saat user signup/register)
$hashed = password_hash('admin123', PASSWORD_BCRYPT);

// Verifikasi password (saat login)
if (password_verify($password_input, $hashed_from_db)) {
    // Password benar
}
```

## Session Management

Session disimpan di server menggunakan `$_SESSION` PHP:

```php
// Saat login berhasil
$_SESSION['user_id'] = 5;
$_SESSION['username'] = 'ahmad_fauzi';
$_SESSION['role'] = 'mahasiswa';
$_SESSION['email'] = 'ahmad.fauzi@univ.ac.id';

// Cek apakah sudah login
if (isset($_SESSION['user_id'])) {
    // User sudah login
}

// Logout
session_destroy();
```

## File Upload Handling

File yang diupload disimpan di folder `uploads/`:

```php
// Struktur file yang diupload
uploads/
├── 1656234567_assignment_1.pdf
├── 1656234568_report.docx
└── ...

// Naming convention: [timestamp]_[filename]
// Tujuan: menghindari duplikasi nama file
```

## Backup & Recovery

### Backup Database

**Via phpMyAdmin:**
1. Pilih database `elearning_system`
2. Klik tab **Export**
3. Format: **SQL**
4. Klik **Go**
5. File `.sql` akan terdownload

**Via Command Line:**
```bash
mysqldump -u root -p elearning_system > backup.sql
```

### Restore Database

**Via phpMyAdmin:**
1. Buat database baru (atau gunakan yang lama)
2. Klik tab **Import**
3. Upload file backup `.sql`
4. Klik **Go**

**Via Command Line:**
```bash
mysql -u root -p elearning_system < backup.sql
```

## Performance Tips

1. **Indexing** - Database sudah dikonfigurasi dengan index pada foreign keys dan field yang sering di-query
2. **Pagination** - Implementasikan pagination untuk list data yang besar
3. **Caching** - Cache data yang jarang berubah
4. **Query Optimization** - Gunakan EXPLAIN untuk analisis query

## Security Best Practices

1. ✅ **Password Hashing** - Menggunakan bcrypt
2. ✅ **Input Sanitization** - Menggunakan prepared statements
3. ✅ **Session Timeout** - Implementasikan timeout session
4. ✅ **HTTPS** - Gunakan HTTPS untuk production
5. ⚠️ **CSRF Protection** - Perlu ditambahkan untuk form submissions
6. ⚠️ **Rate Limiting** - Perlu ditambahkan pada login endpoint
7. ⚠️ **Audit Logging** - Perlu ditambahkan untuk tracking aktivitas

---

**Database siap digunakan! 🎉**
