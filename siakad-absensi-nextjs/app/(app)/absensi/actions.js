'use server';

import { redirect } from 'next/navigation';
import { saveAttendance } from '../../../lib/models/attendanceModel';
import { flashQuery } from '../../../lib/flash';

const VALID_STATUSES = ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa'];

export async function saveAbsensiAction(formData) {
  const idMatkul = formData.get('id_matkul');
  const idJadwal = formData.get('id_jadwal');

  if (!idMatkul || !idJadwal) {
    redirect(`/absensi?${flashQuery('error', 'Pilih mata kuliah dan jadwal terlebih dahulu.')}`);
  }

  for (const [key, value] of formData.entries()) {
    const match = key.match(/^status\[(\d+)\]$/);
    if (!match) continue;

    const idMahasiswa = match[1];
    if (!VALID_STATUSES.includes(value)) continue;

    const keterangan = String(formData.get(`keterangan[${idMahasiswa}]`) || '').trim();
    await saveAttendance(idMahasiswa, idJadwal, value, keterangan);
  }

  redirect(
    `/absensi?id_matkul=${idMatkul}&id_jadwal=${idJadwal}&${flashQuery('success', 'Absensi berhasil disimpan.')}`
  );
}
