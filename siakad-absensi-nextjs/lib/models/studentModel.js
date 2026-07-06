import { getPool, query } from '../db';

export async function getByLecturer(idDosen, keyword = '') {
  const params = [idDosen];
  let where = '';

  if (keyword !== '') {
    where = ' AND (m.npm LIKE ? OR m.nama_mahasiswa LIKE ? OR m.program_studi LIKE ?)';
    const like = `%${keyword}%`;
    params.push(like, like, like);
  }

  return query(
    `SELECT DISTINCT m.*
     FROM mahasiswa m
     JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
     JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
     WHERE mk.id_dosen = ? ${where}
     ORDER BY m.nama_mahasiswa ASC`,
    params
  );
}

export async function find(idMahasiswa, idDosen) {
  const rows = await query(
    `SELECT DISTINCT m.*
     FROM mahasiswa m
     JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
     JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
     WHERE m.id_mahasiswa = ? AND mk.id_dosen = ?
     LIMIT 1`,
    [idMahasiswa, idDosen]
  );
  return rows[0] || null;
}

export async function attachToCourse(idMahasiswa, idMatkul, tahunAjaran, connection) {
  const runner = connection || getPool();
  return runner.execute(
    'INSERT IGNORE INTO kelas_mahasiswa (id_mahasiswa, id_matkul, tahun_ajaran) VALUES (?, ?, ?)',
    [idMahasiswa, idMatkul, tahunAjaran]
  );
}

export async function create(data, idMatkul) {
  const pool = getPool();
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();

    const [result] = await connection.execute(
      `INSERT INTO mahasiswa (npm, nama_mahasiswa, program_studi, angkatan, email)
       VALUES (?, ?, ?, ?, ?)`,
      [data.npm, data.nama_mahasiswa, data.program_studi, data.angkatan, data.email || null]
    );

    const idMahasiswa = result.insertId;
    await attachToCourse(idMahasiswa, idMatkul, data.tahun_ajaran, connection);

    await connection.commit();
    return true;
  } catch (err) {
    await connection.rollback();
    throw err;
  } finally {
    connection.release();
  }
}

export async function update(idMahasiswa, data) {
  return query(
    `UPDATE mahasiswa
     SET npm = ?, nama_mahasiswa = ?, program_studi = ?, angkatan = ?, email = ?
     WHERE id_mahasiswa = ?`,
    [data.npm, data.nama_mahasiswa, data.program_studi, data.angkatan, data.email || null, idMahasiswa]
  );
}

export async function deleteForLecturer(idMahasiswa, idDosen) {
  await query(
    `DELETE km FROM kelas_mahasiswa km
     JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
     WHERE km.id_mahasiswa = ? AND mk.id_dosen = ?`,
    [idMahasiswa, idDosen]
  );

  return query(
    `DELETE m FROM mahasiswa m
     LEFT JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
     WHERE m.id_mahasiswa = ? AND km.id_kelas_mhs IS NULL`,
    [idMahasiswa]
  );
}
