# Setup

Panduan ini diasumsikan untuk XAMPP di Windows.

## Prasyarat

- XAMPP dengan Apache, PHP 8.1+, dan MySQL/MariaDB
- Browser modern
- phpMyAdmin atau MySQL CLI

## Langkah Instalasi

1. Letakkan proyek di:

```text
C:\xampp\htdocs\elearning_unas
```

2. Buat database:

```sql
CREATE DATABASE elearning_unas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import SQL secara berurutan:

```text
database/schema.sql
database/seed_fixed.sql
database/migration_tugas.sql
database/migration_pertemuan.sql
```

4. Cek konfigurasi database di `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_unas');
define('DB_CHARSET', 'utf8mb4');
```

5. Jalankan Apache dan MySQL dari XAMPP Control Panel.

6. Buka:

```text
http://localhost/elearning_unas/
```

## Upload Folder

Folder upload akan dibuat otomatis saat mahasiswa mengumpulkan tugas. Jika server menolak upload, buat manual:

```text
public/uploads/tugas
```

Pastikan folder tersebut writable oleh Apache.

## Troubleshooting

### Login gagal

- Pastikan database sudah di-import berurutan.
- Pastikan `config/database.php` sesuai user/password MySQL lokal.
- Cek tabel `users` memiliki data seed.

### Halaman kosong atau redirect aneh

- Pastikan Apache membaca proyek dari `C:\xampp\htdocs\elearning_unas`.
- Pastikan tidak ada output error PHP tersembunyi di log Apache.

### Fitur tugas/pertemuan error

- Pastikan `migration_tugas.sql` dan `migration_pertemuan.sql` sudah di-import setelah seed.
