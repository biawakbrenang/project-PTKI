import bcrypt from 'bcryptjs';
import { query } from '../db';

/** Cari dosen berdasarkan email lalu cocokkan password (bcrypt). */
export async function findDosenForLogin(email, password) {
  const rows = await query(
    'SELECT id_dosen, nidn, nama_lengkap, email, password, foto_profil FROM dosen WHERE email = ? LIMIT 1',
    [email]
  );

  if (rows.length === 0) return null;

  const dosen = rows[0];
  const valid = await bcrypt.compare(password, dosen.password);
  if (!valid) return null;

  // Jangan simpan hash password ke dalam sesi.
  const { password: _hash, ...safeDosen } = dosen;
  return safeDosen;
}
