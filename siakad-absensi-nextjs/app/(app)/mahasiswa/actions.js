'use server';

import { redirect } from 'next/navigation';
import { find, create, update, deleteForLecturer } from '../../../lib/models/studentModel';
import { getSession } from '../../../lib/auth';
import { flashQuery } from '../../../lib/flash';

export async function saveStudentAction(formData) {
  const session = getSession();
  const data = {
    npm: String(formData.get('npm') || '').trim(),
    nama_mahasiswa: String(formData.get('nama_mahasiswa') || '').trim(),
    program_studi: String(formData.get('program_studi') || '').trim(),
    angkatan: String(formData.get('angkatan') || '').trim(),
    email: String(formData.get('email') || '').trim(),
    tahun_ajaran: String(
      formData.get('tahun_ajaran') ||
        `${new Date().getFullYear()}/${new Date().getFullYear() + 1}`
    ).trim(),
  };
  const idMatkul = formData.get('id_matkul');
  const idMahasiswa = formData.get('id_mahasiswa');

  let redirectUrl;
  try {
    if (idMahasiswa) {
      const existing = await find(idMahasiswa, session.user_id);
      if (!existing) throw new Error('Data mahasiswa tidak ditemukan.');
      await update(idMahasiswa, data);
      redirectUrl = `/mahasiswa?${flashQuery('success', 'Data mahasiswa berhasil diperbarui.')}`;
    } else {
      if (!idMatkul) throw new Error('Pilih mata kuliah untuk menambahkan mahasiswa.');
      await create(data, idMatkul);
      redirectUrl = `/mahasiswa?${flashQuery('success', 'Mahasiswa berhasil ditambahkan.')}`;
    }
  } catch (err) {
    redirectUrl = `/mahasiswa?${flashQuery(
      'error',
      'Gagal menyimpan data. Pastikan NPM/email belum digunakan dan isian sudah lengkap.'
    )}`;
  }

  redirect(redirectUrl);
}

export async function deleteStudentAction(formData) {
  const session = getSession();
  const idMahasiswa = formData.get('id_mahasiswa');

  let redirectUrl;
  try {
    const existing = await find(idMahasiswa, session.user_id);
    if (!existing) throw new Error('Data mahasiswa tidak ditemukan.');
    await deleteForLecturer(idMahasiswa, session.user_id);
    redirectUrl = `/mahasiswa?${flashQuery('success', 'Mahasiswa berhasil dihapus dari kelas Anda.')}`;
  } catch (err) {
    redirectUrl = `/mahasiswa?${flashQuery('error', 'Gagal menghapus mahasiswa.')}`;
  }

  redirect(redirectUrl);
}
