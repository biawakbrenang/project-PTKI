# TODO - Perbaikan fitur Matakuliah

## Rencana
1. Periksa file yang menangani CRUD matakuliah: `admin/matakuliah.php`.
2. Update UI/form agar benar-benar submit (method POST, action, name input, validasi).
3. Tambahkan backend handler di `admin/matakuliah.php` untuk:
   - Tambah (INSERT)
   - Edit (UPDATE) berdasarkan `?edit=ID`
   - Cancel/beralih ke mode tambah.
4. Pastikan tombol Edit mengirim `?edit=ID` dan tombol Save memproses aksi yang sesuai.
5. Uji alur:
   - Tambah matakuliah
   - Edit matakuliah
6. (Opsional) Tambahkan pesan sukses/gagal dan refresh tampilan setelah operasi.

## Progress
- [x] Update `admin/matakuliah.php` agar form tambah/edit benar-benar submit dan punya handler backend.
- [x] Pastikan schema matakuliah punya PK `id` (dari `elearning_kampus_lengkap.sql`).


