# 🚀 PANDUAN SETUP E-LEARNING KAMPUS DI XAMPP LOCALHOST

Ikuti langkah-langkah mudah di bawah ini untuk memasang dan menjalankan aplikasi **E-Learning Kampus** (Eco-Learning Portal) di komputer lokal Anda menggunakan **XAMPP**.

---

## 📋 Persyaratan Sistem
1. **XAMPP** (sudah terpasang PHP 7.4 ke atas / PHP 8.x dan MySQL/MariaDB).
2. **Web Browser** (Google Chrome, Microsoft Edge, Mozilla Firefox, dll).
3. **VS Code** (atau teks editor lainnya untuk mengedit kode jika diperlukan).

---

## 🛠️ Langkah-Langkah Setup

### Langkah 1: Pindahkan Folder Project ke `htdocs`
1. Download atau salin folder **`elearning kampus`** dari komputer Anda.
2. Tempel/Paste folder tersebut ke dalam direktori **`htdocs`** milik XAMPP Anda.
   * **Windows**: `C:\xampp\htdocs\`
     *(Sehingga jalurnya menjadi: `C:\xampp\htdocs\elearning kampus\`)*
   * **Mac OS**: `/Applications/XAMPP/xamppfiles/htdocs/`
   * **Linux**: `/opt/lampp/htdocs/`

---

### Langkah 2: Aktifkan Apache & MySQL di XAMPP
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL** hingga warnanya berubah menjadi hijau.

---

### Langkah 3: Buat & Impor Database di phpMyAdmin
Kami telah menyatukan semua tabel dan data contoh ke dalam satu file tunggal yang sangat praktis bernama **`elearning_kampus_lengkap.sql`** di dalam folder project Anda.

1. Buka Web Browser Anda, lalu akses: **`http://localhost/phpmyadmin/`**
2. Di panel sebelah kiri, klik **New** (Baru) untuk membuat database baru.
3. Masukkan Nama Database: **`elearning_kampus`**
4. Klik tombol **Create** (Buat).
5. Setelah database berhasil dibuat, klik nama database **`elearning_kampus`** tersebut di panel kiri.
6. Klik tab **Import** di bagian menu atas phpMyAdmin.
7. Pada bagian **File to import**, klik tombol **Choose File** (Pilih Berkas).
8. Cari dan pilih file **`elearning_kampus_lengkap.sql`** yang terletak langsung di dalam folder project Anda:
   `C:\xampp\htdocs\elearning kampus\elearning_kampus_lengkap.sql`
9. Gulir ke bawah halaman phpMyAdmin, lalu klik tombol **Import** (atau **Go**).
10. Tunggu beberapa detik hingga muncul pesan sukses berwarna hijau ("*Import has been successfully finished...*").

---

### Langkah 4: Sesuaikan Konfigurasi Koneksi (Opsional)
Secara default, konfigurasi di file **`config.php`** sudah diset agar cocok dengan XAMPP standar tanpa kata sandi. Jika Anda menggunakan username/password database kustom, ikuti langkah berikut:

1. Buka folder `elearning kampus` menggunakan VS Code.
2. Buka file bernama **`config.php`**.
3. Sesuaikan parameter kredensial database berikut dengan konfigurasi MySQL lokal Anda:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');  // Default XAMPP adalah 'root'
   define('DB_PASS', '');      // Default XAMPP adalah kosong (tidak memakai password)
   define('DB_NAME', 'elearning_kampus'); // Nama database yang Anda buat tadi
   ```
4. Simpan file (`Ctrl + S`).

---

### Langkah 5: Jalankan Aplikasi di Browser!
Aplikasi siap dimainkan! Silakan buka web browser Anda dan kunjungi tautan berikut:

👉 **[http://localhost/elearning%20kampus/](http://localhost/elearning%20kampus/)** atau **`http://localhost/elearning kampus/`**

---

## 🔑 Akun Demo Cepat (Bypass Form Login)
Untuk mempermudah pengujian Anda tanpa harus mendaftar secara manual, di halaman login terbawah terdapat tombol **Bypass Login Cepat** sekali klik, atau Anda bisa mengetik manual kredensial berikut:

| Peran (Role) | Username / NIM / NIDN | Kata Sandi (Password) | Nama Akun Contoh |
| :--- | :--- | :--- | :--- |
| **Dosen** | `35112001` | `Rendra996` | Dr. Rendra Kusuma, M.T. |
| **Mahasiswa** | `25211101` | `Ahmad689` | Ahmad Pratama |
| **Admin** | `15123416` | `Admin658` | Administrator Akademik LPTIK |

---

## 📁 Struktur Penting Folder Project
Untuk kenyamanan bekerja di VS Code, berikut file-file utama yang perlu Anda ketahui:
* `/config.php` - Berkas konfigurasi utama koneksi database PDO.
* `/index.php` - Halaman peralihan & pengecekan login awal.
* `/auth/login.php` - Halaman login portal lengkap dengan bypass akun & CAPTCHA.
* `/dosen/` - Folder dashboard, presensi QR, dan publikasi materi/tugas dosen.
* `/mahasiswa/` - Folder modul kelas aktif, input kode presensi, dan upload tugas mahasiswa.
* `/admin/` - Folder kelola data kelas, mata kuliah, jadwal kuliah, dan pengajar.
* `/components/` - Folder komponen global UI (seperti `sidebar.php`, `header.php`).
* `/assets/` - Berkas aset (CSS terpadu Tailwind, Javascript pendukung presensi QR).

---
*Selamat mengajar dan belajar! Jika ada kendala, pastikan Apache & MySQL di XAMPP dalam status **Running** (aktif).* 💡
