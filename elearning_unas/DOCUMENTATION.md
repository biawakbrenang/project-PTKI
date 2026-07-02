# Dokumentasi Teknis

## Arsitektur

Proyek ini adalah aplikasi PHP modular sederhana:

- `config/database.php` membuat koneksi MySQLi global `$conn`.
- `src/auth/check_auth.php` mengatur session, login guard, role guard, redirect, dan logout.
- `src/helpers/db.php` berisi helper query prepared statement dan helper domain akademik.
- `src/components/header.php` dan `src/components/sidebar.php` dipakai bersama oleh halaman role.
- Folder `src/mahasiswa`, `src/dosen`, dan `src/admin` berisi halaman per role.

## Konvensi Database

Skema utama memakai nama kolom berikut:

- `matakuliah.kode_matkul`
- `matakuliah.nama_matkul`
- `kelas.kode_kelas`
- `krs.status_krs`
- `absensi.status`

Jangan memakai nama dari skema lama seperti `kode_matakuliah`, `nama_kelas`, atau `krs.status` kecuali skema memang dimigrasikan penuh.

## Helper Penting

`src/helpers/db.php` menyediakan:

- `executeQuery()`
- `fetchRow()` dan alias `fetchOne()`
- `fetchAll()`
- `insertRecord()`
- `updateRecord()`
- `deleteRecord()`
- Helper akademik seperti `getKRSByMahasiswaId()`, `getKelasByDosenId()`, `calculateIPK()`, dan `getTranskrip()`

Gunakan helper tersebut untuk query dengan input user. Hindari query string langsung untuk operasi yang memakai `$_GET`, `$_POST`, atau nilai upload.

## Autentikasi

`requireLogin()` menerima parameter opsional:

```php
requireLogin();
requireLogin('admin');
requireLogin(['mahasiswa', 'dosen']);
```

`requireRole()` juga menerima string atau array role.

## Catatan Keamanan

Saat ini password seed masih plain text agar sesuai data awal. Untuk production:

1. Ubah penyimpanan password ke `password_hash()`.
2. Ubah login ke `password_verify()`.
3. Tambahkan CSRF token untuk form POST.
4. Validasi MIME file upload di server, bukan hanya ekstensi.
5. Batasi akses download file upload melalui kontrol role.

## Verifikasi Cepat

Lint PHP:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
```

Cari sisa nama kolom lama:

```powershell
rg "kode_matakuliah|nama_kelas|krs\.status\b|status_absensi|tanggal_upload"
```
