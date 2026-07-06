# SIAKAD V2 - Sistem Absensi Dosen Lengkap

Sistem absensi berbasis web yang dirancang khusus untuk memudahkan dosen dalam mengelola kehadiran mahasiswa, mata kuliah, dan rekapitulasi secara efisien dengan antarmuka yang modern dan responsif.

## Fitur Utama

*   **Dashboard Interaktif**: Statistik ringkasan mata kuliah, mahasiswa, dan pertemuan.
*   **Input Absensi**: Pencatatan kehadiran per pertemuan dengan status (Hadir, Sakit, Izin, Alpa) dan catatan tambahan.
*   **Rekapitulasi**: Laporan persentase kehadiran mahasiswa secara otomatis.
*   **Manajemen Mahasiswa**: Daftar mahasiswa yang terdaftar di mata kuliah dosen.
*   **Keamanan**: Sistem login aman dengan *password hashing*.
*   **UI/UX Modern**: Desain bersih menggunakan Tailwind CSS dan font Inter.

## Cara Instalasi

1.  Ekstrak file ZIP proyek ke direktori web server Anda (misal: `htdocs` untuk XAMPP).
2.  Import database:
    *   Buka `phpMyAdmin`.
    *   Buat database baru dengan nama `absensi_v2`.
    *   Import file `database.sql` yang ada di root folder proyek.
3.  Konfigurasi Database:
    *   Buka file `config/database.php`.
    *   Sesuaikan `host`, `db_name`, `username`, dan `password` jika diperlukan.
4.  Akses melalui browser: `http://localhost/ABSENSI-DOSEN/public/`

## Akun Demo Dosen

*   **Email**: `firansyah@univ.ac.id`
*   **Password**: `password`

## Struktur Proyek

*   `config/`: Konfigurasi database.
*   `models/`: Logika bisnis dan interaksi database (Auth, Attendance, Dashboard).
*   `views/`: File tampilan (UI) PHP.
*   `public/`: Entry point aplikasi dan aset (index.php, CSS, JS, Gambar).
*   `database.sql`: Skema database MySQL.
