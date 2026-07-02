# Academic E-Learning System - Setup Guide

Panduan lengkap untuk mengintegrasikan sistem dengan phpMyAdmin dan database SQL.

## Prasyarat

- XAMPP, WAMP, atau MAMP (Web Server dengan Apache, PHP, MySQL)
- phpMyAdmin (biasanya sudah terinstall dengan XAMPP/WAMP)
- Browser modern
- Text Editor atau IDE (VS Code, Sublime Text, dll)

## Langkah-Langkah Setup

### 1. Ekstrak/Copy File-File Project

```
htdocs/ (atau www/)
├── elearning/
│   ├── php/
│   │   ├── config.php
│   │   ├── auth.php
│   │   ├── api.php
│   │   └── dashboard.php
│   ├── sql/
│   │   └── elearning_schema.sql
│   ├── uploads/
│   ├── index.php
│   └── academic-elearning-system.html
```

### 2. Buat Database di phpMyAdmin

#### Cara A: Import dari File SQL (Rekomendasi)

1. Buka **phpMyAdmin** (http://localhost/phpmyadmin)
2. Login dengan username `root` dan password (kosongkan jika tidak ada)
3. Klik tab **"Import"** atau **"Import Files"**
4. Upload file `sql/elearning_schema.sql`
5. Klik **"Go"** / **"Import"** untuk mengeksekusi semua perintah SQL
6. Database `elearning_system` akan otomatis terbuat dengan semua tabel dan data sample

#### Cara B: Manual di phpMyAdmin

1. Klik **"New"** untuk membuat database baru
2. Nama database: `elearning_system`
3. Collation: `utf8mb4_unicode_ci`
4. Klik **"Create"**
5. Buka tab **"SQL"**
6. Copy-paste isi file `elearning_schema.sql` ke text area
7. Klik **"Go"**

### 3. Konfigurasi Database Connection

Edit file `php/config.php`:

```php
define('DB_HOST', 'localhost');      // Biasanya localhost
define('DB_USER', 'root');           // Default user MySQL
define('DB_PASS', '');               // Kosong atau password Anda
define('DB_NAME', 'elearning_system');
define('DB_PORT', 3306);
```

Jika Anda menggunakan password untuk MySQL, ubah `DB_PASS`:
```php
define('DB_PASS', 'your_password_here');
```

### 4. Buat Folder Uploads

Buat folder `uploads/` di folder project untuk menyimpan file yang di-upload:

```bash
mkdir uploads
chmod 777 uploads  # Di Linux/Mac
```

### 5. Buat File `index.php`

Buat file `index.php` di root folder project (bukan di subfolder php/):

```php
<?php
require_once 'php/config.php';
require_once 'php/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Delegasi ke API handler
    require_once 'php/api.php';
}

// Load HTML interface
require_once 'academic-elearning-system.html';
?>
```

### 6. Update File HTML

File `academic-elearning-system.html` sudah menyertakan kode untuk mengintegrasikan dengan PHP. Pastikan sudah menggunakan versi terbaru yang sudah dimodifikasi.

## Default Login Credentials

Setelah import database, gunakan akun berikut untuk login:

### Admin
- **Username:** admin
- **Email:** admin@univ.ac.id
- **Password:** admin123
- **Role:** Admin

### Dosen (Lecturer)
- **Username:** rina_hartati
- **Email:** rina.hartati@univ.ac.id
- **Password:** admin123
- **Role:** Dosen

### Mahasiswa (Student)
- **Username:** ahmad_fauzi
- **Email:** ahmad.fauzi@univ.ac.id
- **Password:** admin123
- **Role:** Mahasiswa

## Testing Akses

1. Buka browser dan akses: `http://localhost/elearning/`
2. Halaman login akan muncul
3. Pilih role (Admin, Dosen, atau Mahasiswa)
4. Masukkan username/email dan password
5. Klik **Login**

## Struktur Database

### Tabel Utama

1. **users** - Data pengguna (admin, dosen, mahasiswa)
2. **courses** - Data mata kuliah
3. **classes** - Data kelas/rombongan belajar
4. **class_enrollments** - Data pendaftaran mahasiswa ke kelas
5. **assignments** - Data tugas/assignment
6. **submissions** - Data pengumpulan tugas dari mahasiswa
7. **attendance** - Data kehadiran/absensi
8. **announcements** - Data pengumuman
9. **grades** - Data nilai

### Relasi Antar Tabel

```
users (1) ──── (banyak) classes (lecturer_id)
users (1) ──── (banyak) assignments (created_by)
users (1) ──── (banyak) submissions (student_id)
users (1) ──── (banyak) attendance (student_id)
users (1) ──── (banyak) announcements (created_by)

courses (1) ──── (banyak) classes (course_id)
classes (1) ──── (banyak) class_enrollments
classes (1) ──── (banyak) assignments
classes (1) ──── (banyak) attendance
classes (1) ──── (banyak) announcements

assignments (1) ──── (banyak) submissions
assignments (1) ──── (banyak) grades
```

## API Endpoints

Semua request ke `php/api.php` dengan method POST dan parameter `action`:

### Login
```
POST /php/auth.php
action=login
username=[username/email]
password=[password]
role=[admin/dosen/mahasiswa]
```

### Dashboard
```
POST /php/api.php
action=getDashboardData
```

### Classes
```
POST /php/api.php
action=getClasses
```

### Assignments
```
POST /php/api.php
action=getAssignments
class_id=[optional]
```

### Submissions
```
POST /php/api.php
action=getSubmissions
assignment_id=[assignment_id]
```

## Troubleshooting

### Error: Connection Failed
- Pastikan MySQL/phpMyAdmin sudah running
- Cek konfigurasi `config.php`
- Verifikasi user dan password di phpMyAdmin

### Error: Database not found
- Pastikan sudah import SQL schema
- Cek apakah database `elearning_system` sudah ada di phpMyAdmin
- Jalankan ulang import dari file `elearning_schema.sql`

### Error: Permission Denied pada uploads/
- Buat folder `uploads/` jika belum ada
- Set permission: `chmod 777 uploads`

### Login tidak berhasil
- Pastikan sudah import sample data dari SQL
- Periksa username dan password (case-sensitive untuk password)
- Gunakan credential yang sudah disediakan di atas

### Data tidak muncul
- Refresh halaman browser
- Check console browser (F12 → Console) untuk error messages
- Periksa log error di `php/error.log`

## Security Notes

### Production Deployment

Sebelum go-live, lakukan:

1. **Ubah default password semua user**
2. **Setup proper HTTPS** (SSL Certificate)
3. **Konfigurasi firewall** database
4. **Backup database secara regular**
5. **Update PHP ke versi terbaru**
6. **Set proper file permissions:**
   ```bash
   chmod 644 *.php *.html
   chmod 755 php/ sql/ uploads/
   chmod 600 php/config.php
   ```
7. **Enable password hashing** (sudah menggunakan bcrypt)
8. **Implement rate limiting** pada login
9. **Add CSRF protection** pada forms
10. **Validate & sanitize** semua input

## File Directory Structure

```
elearning/
├── index.php                          # Main entry point
├── academic-elearning-system.html     # UI/Frontend
├── php/
│   ├── config.php                    # Database config
│   ├── auth.php                      # Login/Session handling
│   └── api.php                       # API endpoints
├── sql/
│   └── elearning_schema.sql          # Database schema
├── uploads/                          # User file uploads
└── SETUP_GUIDE.md                    # This file
```

## Dukungan & Bantuan

Jika mengalami masalah:

1. Periksa browser console (F12)
2. Lihat server error log
3. Verifikasi konfigurasi database
4. Pastikan semua file sudah ter-copy dengan benar
5. Cek file permissions

## Next Steps

Setelah setup berhasil, Anda dapat:

1. **Customize tampilan** - Edit CSS di HTML file
2. **Tambah fitur** - Modifikasi API endpoints di `api.php`
3. **Integrasikan email** - Tambahkan notifikasi email
4. **Setup backups** - Buat scheduled database backups
5. **Monitor usage** - Tambahkan logging system

---

**Happy Learning! 🎓**
