import { query } from '../db';

export async function getStats(idDosen) {
  const stats = {};

  const totalMatkul = await query(
    'SELECT COUNT(*) as total FROM mata_kuliah WHERE id_dosen = ?',
    [idDosen]
  );
  stats.total_matkul = totalMatkul[0].total;

  const totalMahasiswa = await query(
    `SELECT COUNT(DISTINCT id_mahasiswa) as total FROM kelas_mahasiswa km
     JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
     WHERE mk.id_dosen = ?`,
    [idDosen]
  );
  stats.total_mahasiswa = totalMahasiswa[0].total;

  const pertemuanSelesai = await query(
    `SELECT COUNT(DISTINCT a.id_jadwal) as total FROM absensi a
     JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal
     JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
     WHERE mk.id_dosen = ?`,
    [idDosen]
  );
  stats.pertemuan_selesai = pertemuanSelesai[0].total;

  const totalJadwal = await query(
    `SELECT COUNT(*) as total FROM jadwal_perkuliahan jp
     JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
     WHERE mk.id_dosen = ?`,
    [idDosen]
  );
  stats.total_jadwal = totalJadwal[0].total;

  const rataKehadiran = await query(
    `SELECT ROUND(
        (SUM(CASE WHEN a.status_kehadiran IN ('Hadir', 'Terlambat') THEN 1 ELSE 0 END) / NULLIF(COUNT(a.id_absensi), 0)) * 100
     ) as total
     FROM absensi a
     JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal
     JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
     WHERE mk.id_dosen = ?`,
    [idDosen]
  );
  stats.rata_kehadiran = rataKehadiran[0].total || 0;

  return stats;
}

export async function getTodaySchedules(idDosen) {
  return query(
    `SELECT jp.*, mk.kode_matkul, mk.nama_matkul
     FROM jadwal_perkuliahan jp
     JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
     WHERE mk.id_dosen = ?
     ORDER BY jp.tanggal_pertemuan ASC, jp.jam_mulai ASC
     LIMIT 5`,
    [idDosen]
  );
}
