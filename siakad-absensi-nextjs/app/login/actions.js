'use server';

import { redirect } from 'next/navigation';
import { findDosenForLogin } from '../../lib/models/authModel';
import { createSession } from '../../lib/auth';

export async function loginAction(prevState, formData) {
  const email = String(formData.get('email') || '').trim();
  const password = String(formData.get('password') || '');

  if (!email || !password) {
    return { error: 'Email dan password wajib diisi.' };
  }

  const dosen = await findDosenForLogin(email, password);
  if (!dosen) {
    return { error: 'Email atau password salah.' };
  }

  createSession({
    user_id: dosen.id_dosen,
    nidn: dosen.nidn,
    nama_lengkap: dosen.nama_lengkap,
    email: dosen.email,
    foto_profil: dosen.foto_profil,
  });

  redirect('/dashboard');
}
