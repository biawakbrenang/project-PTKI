import { redirect } from 'next/navigation';
import { getSession } from '../../../lib/auth';
import {
  getCoursesByDosen,
  getCourseByDosen,
  getSchedulesByCourse,
  getScheduleByCourse,
  getStudentsByCourse,
  getAttendanceBySchedule,
} from '../../../lib/models/attendanceModel';
import { parseFlash, flashQuery } from '../../../lib/flash';
import FlashMessage from '../../../components/FlashMessage';
import AutoSubmitSelect from '../../../components/AutoSubmitSelect';
import MarkAllButton from '../../../components/MarkAllButton';
import { saveAbsensiAction } from './actions';

export const metadata = { title: 'Input Absensi - Sistem Absensi Dosen' };

const VALID_STATUSES = ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa'];

function formatTanggal(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

export default async function AbsensiPage({ searchParams }) {
  const session = getSession();
  const selectedMatkul = searchParams.id_matkul || '';
  const selectedJadwal = searchParams.id_jadwal || '';
  const flash = parseFlash(searchParams);

  const courses = await getCoursesByDosen(session.user_id);

  let selectedCourse = null;
  let schedules = [];
  if (selectedMatkul) {
    selectedCourse = await getCourseByDosen(selectedMatkul, session.user_id);
    if (!selectedCourse) {
      redirect(
        `/absensi?${flashQuery('error', 'Mata kuliah tidak ditemukan atau bukan milik akun Anda.')}`
      );
    }
    schedules = await getSchedulesByCourse(selectedMatkul);
  }

  let selectedSchedule = null;
  let students = [];
  let attendanceRows = {};
  if (selectedJadwal && selectedMatkul) {
    selectedSchedule = await getScheduleByCourse(selectedJadwal, selectedMatkul);
    if (!selectedSchedule) {
      redirect(
        `/absensi?id_matkul=${selectedMatkul}&${flashQuery('error', 'Jadwal tidak sesuai dengan mata kuliah yang dipilih.')}`
      );
    }
    students = await getStudentsByCourse(selectedMatkul);
    attendanceRows = await getAttendanceBySchedule(selectedJadwal);
  }

  return (
    <section className="space-y-6">
      <FlashMessage flash={flash} />

      <div className="panel">
        <form method="GET" className="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
          <div>
            <label className="form-label">Mata Kuliah</label>
            <AutoSubmitSelect name="id_matkul" className="form-input" defaultValue={selectedMatkul}>
              <option value="">Pilih mata kuliah</option>
              {courses.map((course) => (
                <option key={course.id_matkul} value={course.id_matkul}>
                  {course.kode_matkul} - {course.nama_matkul}
                </option>
              ))}
            </AutoSubmitSelect>
          </div>
          <div>
            <label className="form-label">Pertemuan</label>
            <AutoSubmitSelect
              name="id_jadwal"
              className="form-input"
              defaultValue={selectedJadwal}
              disabled={schedules.length === 0}
            >
              <option value="">Pilih jadwal</option>
              {schedules.map((schedule) => (
                <option key={schedule.id_jadwal} value={schedule.id_jadwal}>
                  Pertemuan {schedule.pertemuan_ke} - {formatTanggal(schedule.tanggal_pertemuan)},{' '}
                  {String(schedule.jam_mulai).slice(0, 5)}
                </option>
              ))}
            </AutoSubmitSelect>
          </div>
          <a href="/absensi" className="btn-muted justify-center">
            <i className="fas fa-rotate-left" />
            Reset
          </a>
        </form>
      </div>

      {selectedCourse && selectedSchedule && (
        <div className="grid gap-4 sm:grid-cols-3">
          <article className="mini-card">
            <span>Mata kuliah</span>
            <strong>{selectedCourse.nama_matkul}</strong>
          </article>
          <article className="mini-card">
            <span>Pertemuan</span>
            <strong>{selectedSchedule.pertemuan_ke}</strong>
          </article>
          <article className="mini-card">
            <span>Ruangan</span>
            <strong>{selectedSchedule.ruangan}</strong>
          </article>
        </div>
      )}

      {selectedJadwal && students.length > 0 ? (
        <form action={saveAbsensiAction} className="panel overflow-hidden p-0">
          <input type="hidden" name="id_matkul" value={selectedMatkul} />
          <input type="hidden" name="id_jadwal" value={selectedJadwal} />
          <div className="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="panel-title">Daftar Kehadiran</h2>
              <p className="panel-subtitle">{students.length} mahasiswa terdaftar.</p>
            </div>
            <MarkAllButton status="Hadir">
              <i className="fas fa-check-double" />
              Tandai Hadir Semua
            </MarkAllButton>
          </div>
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Mahasiswa</th>
                  <th className="text-center">Status</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                {students.map((student) => {
                  const current =
                    attendanceRows[student.id_mahasiswa]?.status_kehadiran || 'Hadir';
                  const note = attendanceRows[student.id_mahasiswa]?.keterangan || '';
                  return (
                    <tr key={student.id_mahasiswa}>
                      <td>
                        <p className="font-bold">{student.nama_mahasiswa}</p>
                        <p className="text-xs text-slate-500">
                          {student.npm} - {student.program_studi}
                        </p>
                      </td>
                      <td>
                        <div className="status-grid">
                          {VALID_STATUSES.map((status) => (
                            <label className="status-option" key={status}>
                              <input
                                type="radio"
                                name={`status[${student.id_mahasiswa}]`}
                                value={status}
                                defaultChecked={current === status}
                                required
                              />
                              <span>{status}</span>
                            </label>
                          ))}
                        </div>
                      </td>
                      <td>
                        <input
                          className="form-input min-w-56"
                          type="text"
                          name={`keterangan[${student.id_mahasiswa}]`}
                          defaultValue={note}
                          placeholder="Catatan opsional"
                        />
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          <div className="flex justify-end border-t border-slate-100 bg-slate-50 p-5">
            <button className="btn-primary" type="submit">
              <i className="fas fa-floppy-disk" />
              Simpan Absensi
            </button>
          </div>
        </form>
      ) : selectedJadwal ? (
        <div className="empty-state panel">
          <i className="fas fa-user-slash" />
          <p>Belum ada mahasiswa di mata kuliah ini.</p>
        </div>
      ) : (
        <div className="empty-state panel">
          <i className="fas fa-clipboard-list" />
          <p>Pilih mata kuliah dan pertemuan untuk mulai input absensi.</p>
        </div>
      )}
    </section>
  );
}
