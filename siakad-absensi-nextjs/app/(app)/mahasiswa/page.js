import { redirect } from 'next/navigation';
import { getSession } from '../../../lib/auth';
import { getCoursesByDosen } from '../../../lib/models/attendanceModel';
import { getByLecturer, find } from '../../../lib/models/studentModel';
import { parseFlash, flashQuery } from '../../../lib/flash';
import FlashMessage from '../../../components/FlashMessage';
import ConfirmForm from '../../../components/ConfirmForm';
import { saveStudentAction, deleteStudentAction } from './actions';

export const metadata = { title: 'Data Mahasiswa - Sistem Absensi Dosen' };

export default async function MahasiswaPage({ searchParams }) {
  const session = getSession();
  const keyword = (searchParams.q || '').trim();
  const editId = searchParams.edit || '';
  const flash = parseFlash(searchParams);

  const courses = await getCoursesByDosen(session.user_id);

  let editing = null;
  if (editId) {
    editing = await find(editId, session.user_id);
    if (!editing) {
      redirect(`/mahasiswa?${flashQuery('error', 'Data mahasiswa tidak ditemukan.')}`);
    }
  }

  const students = await getByLecturer(session.user_id, keyword);
  const currentYear = new Date().getFullYear();

  return (
    <section className="grid gap-6 xl:grid-cols-[0.9fr_1.4fr]">
      <div className="panel h-fit">
        <div className="panel-header">
          <div>
            <h2 className="panel-title">{editing ? 'Edit Mahasiswa' : 'Tambah Mahasiswa'}</h2>
            <p className="panel-subtitle">
              Mahasiswa baru otomatis masuk ke mata kuliah yang dipilih.
            </p>
          </div>
        </div>

        <FlashMessage flash={flash} />

        <form action={saveStudentAction} className="space-y-4">
          {editing && <input type="hidden" name="id_mahasiswa" value={editing.id_mahasiswa} />}
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="form-label">NPM</label>
              <input
                className="form-input"
                type="text"
                name="npm"
                required
                defaultValue={editing?.npm || ''}
                placeholder="2021001"
              />
            </div>
            <div>
              <label className="form-label">Angkatan</label>
              <input
                className="form-input"
                type="number"
                name="angkatan"
                required
                min="2000"
                max="2099"
                defaultValue={editing?.angkatan || currentYear}
              />
            </div>
          </div>
          <div>
            <label className="form-label">Nama Mahasiswa</label>
            <input
              className="form-input"
              type="text"
              name="nama_mahasiswa"
              required
              defaultValue={editing?.nama_mahasiswa || ''}
              placeholder="Nama lengkap"
            />
          </div>
          <div>
            <label className="form-label">Program Studi</label>
            <input
              className="form-input"
              type="text"
              name="program_studi"
              required
              defaultValue={editing?.program_studi || ''}
              placeholder="Teknik Informatika"
            />
          </div>
          <div>
            <label className="form-label">Email</label>
            <input
              className="form-input"
              type="email"
              name="email"
              defaultValue={editing?.email || ''}
              placeholder="nama@student.univ.ac.id"
            />
          </div>

          {!editing && (
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="form-label">Mata Kuliah</label>
                <select className="form-input" name="id_matkul" required defaultValue="">
                  <option value="">Pilih mata kuliah</option>
                  {courses.map((course) => (
                    <option key={course.id_matkul} value={course.id_matkul}>
                      {course.kode_matkul} - {course.nama_matkul}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="form-label">Tahun Ajaran</label>
                <input
                  className="form-input"
                  type="text"
                  name="tahun_ajaran"
                  required
                  defaultValue={`${currentYear}/${currentYear + 1}`}
                />
              </div>
            </div>
          )}

          <div className="flex flex-col gap-3 sm:flex-row">
            <button className="btn-primary flex-1" type="submit">
              <i className="fas fa-floppy-disk" />
              Simpan
            </button>
            {editing && (
              <a className="btn-muted justify-center" href="/mahasiswa">
                <i className="fas fa-xmark" />
                Batal
              </a>
            )}
          </div>
        </form>
      </div>

      <div className="space-y-4">
        <div className="panel">
          <form method="GET" className="flex flex-col gap-3 sm:flex-row">
            <div className="form-icon-wrap flex-1">
              <i className="fas fa-search" />
              <input
                className="form-input pl-11"
                type="search"
                name="q"
                defaultValue={keyword}
                placeholder="Cari NPM, nama, atau program studi"
              />
            </div>
            <button className="btn-muted justify-center" type="submit">
              <i className="fas fa-magnifying-glass" />
              Cari
            </button>
          </form>
        </div>

        <div className="panel overflow-hidden p-0">
          <div className="border-b border-slate-100 p-5">
            <h2 className="panel-title">Daftar Mahasiswa</h2>
            <p className="panel-subtitle">{students.length} mahasiswa ditemukan.</p>
          </div>
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Mahasiswa</th>
                  <th>Program Studi</th>
                  <th>Angkatan</th>
                  <th className="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                {students.map((student) => (
                  <tr key={student.id_mahasiswa}>
                    <td>
                      <div className="flex items-center gap-3">
                        <div className="avatar">{student.nama_mahasiswa.substring(0, 1)}</div>
                        <div>
                          <p className="font-bold">{student.nama_mahasiswa}</p>
                          <p className="text-xs text-slate-500">
                            {student.npm}
                            {student.email ? ` - ${student.email}` : ''}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td className="font-medium text-slate-600">{student.program_studi}</td>
                    <td>{student.angkatan}</td>
                    <td>
                      <div className="flex justify-end gap-2">
                        <a
                          className="icon-button text-blue-600 hover:bg-blue-50"
                          href={`/mahasiswa?edit=${student.id_mahasiswa}`}
                          title="Edit"
                        >
                          <i className="fas fa-pen" />
                        </a>
                        <ConfirmForm
                          action={deleteStudentAction}
                          confirmMessage="Hapus mahasiswa ini dari kelas Anda?"
                        >
                          <input type="hidden" name="id_mahasiswa" value={student.id_mahasiswa} />
                          <button
                            className="icon-button text-rose-600 hover:bg-rose-50"
                            type="submit"
                            title="Hapus"
                          >
                            <i className="fas fa-trash" />
                          </button>
                        </ConfirmForm>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {students.length === 0 && (
            <div className="empty-state p-10">
              <i className="fas fa-user-graduate" />
              <p>Belum ada mahasiswa yang cocok.</p>
            </div>
          )}
        </div>
      </div>
    </section>
  );
}
