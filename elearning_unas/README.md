# Sistem E-Learning Akademik

Aplikasi e-learning akademik berbasis PHP dan MySQL untuk tiga peran utama: mahasiswa, dosen, dan administrator.

## Fitur Utama

### Mahasiswa
- Dashboard akademik
- KRS, jadwal, nilai, absensi, materi, pertemuan, dan tugas
- Profil dan transkrip akademik

### Dosen
- Dashboard kelas
- Daftar mahasiswa per kelas
- Nilai, absensi, materi, pengumuman, pertemuan, dan tugas

### Administrator
- Dashboard statistik
- Kelola user, matakuliah, kelas, dan laporan

## Stack

- PHP 8.1 direkomendasikan
- MySQL atau MariaDB
- Apache/XAMPP
- HTML, CSS, dan JavaScript tanpa framework

## Struktur Proyek

```text
config/
  database.php
database/
  schema.sql
  seed_fixed.sql
  migration_tugas.sql
  migration_pertemuan.sql
public/
  css/style.css
  js/script.js
src/
  admin/
  auth/
  components/
  dosen/
  helpers/
  mahasiswa/
index.php
```

## Database Resmi

Gunakan file SQL berikut secara berurutan:

1. `database/schema.sql`
2. `database/seed_fixed.sql`
3. `database/migration_tugas.sql`
4. `database/migration_pertemuan.sql`

File SQL lama yang bentrok sudah dihapus agar tidak membingungkan.

## Akun Seed

Data seed memakai username 8 digit:

- Mahasiswa Sistem Informasi: `25211101` sampai `25211150`
- Mahasiswa Informatika: `25212101` sampai `25212150`
- Dosen: `35112001`, `35112010`, `35112015`, `35122005`, `35122010`, `35122015`
- Admin: `15123416`, `15456724`

Password seed memakai pola `NamaDepan` + 3 angka 001-010. Contoh: `25211101` memakai `Ahmad008`, dosen `35112001` memakai `Rendra008`, admin `15123416` memakai `Admin007`. Untuk production, migrasikan ke `password_hash()` dan `password_verify()`.

## Menjalankan Lokal

Jika folder berada di `C:\xampp\htdocs\elearning_unas`, akses:

```text
http://localhost/elearning_unas/
```

Entry point akan mengarah ke halaman login.
