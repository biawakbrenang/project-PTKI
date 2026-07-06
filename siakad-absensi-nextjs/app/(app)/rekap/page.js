import { redirect } from 'next/navigation';
import { getSession } from '../../../lib/auth';
import {
  getCoursesByDosen,
  getCourseByDosen,
  getAttendanceRecap,
  getCourseSummary,
} from '../../../lib/models/attendanceModel';
import { parseFlash, flashQuery } from '../../../lib/flash';
import FlashMessage from '../../../components/FlashMessage';
import AutoSubmitSelect from '../../../components/AutoSubmitSelect';
import PrintButton from '../../../components/PrintButton';

export const metadata = { title: 'Rekap Absensi - Sistem Absensi Dosen' };

export default async function RekapPage({ searchParams }) {
  const session = getSession();
  const selectedMatkul = searchParams.id_matkul || '';
  const flash = parseFlash(searchParams);

  const courses = await getCoursesByDosen(session.user_id);

  let selectedCourse = null;
  let recapData = [];
  let summary = null;

  if (selectedMatkul) {
    selectedCourse = await getCourseByDosen(selectedMatkul, session.user_id);
    if (!selectedCourse) {
      redirect(
        `/rekap?${flashQuery('error', 'Mata kuliah tidak ditemukan atau bukan milik akun Anda.')}`
      );
    }
    recapData = await getAttendanceRecap(selectedMatkul);
    summary = await getCourseSummary(selectedMatkul);
  }

  return (
    <section className="space-y-6">
      <FlashMessage flash={flash} />

      <div className="panel">
        <form method="GET" className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
          <div>
            <label className="form-label">Filter Mata Kuliah</label>
            <AutoSubmitSelect name="id_matkul" className="form-input" defaultValue={selectedMatkul}>
              <option value="">Pilih mata kuliah</option>
              {courses.map((course) => (
                <option key={course.id_matkul} value={course.id_matkul}>
                  {course.kode_matkul} - {course.nama_matkul}
                </option>
              ))}
            </AutoSubmitSelect>
          </div>
          {selectedMatkul && <PrintButton />}
        </form>
      </div>

      {summary && (
        <div className="grid gap-4 sm:grid-cols-3">
          <article className="mini-card">
            <span>Mahasiswa</span>
            <strong>{summary.total_mahasiswa ?? 0}</strong>
          </article>
          <article className="mini-card">
            <span>Total Jadwal</span>
            <strong>{summary.total_jadwal ?? 0}</strong>
          </article>
          <article className="mini-card">
            <span>Data Terisi</span>
            <strong>{summary.total_absensi ?? 0}</strong>
          </article>
        </div>
      )}

      {selectedMatkul && recapData.length > 0 ? (
        <div className="panel overflow-hidden p-0">
          <div className="border-b border-slate-100 p-5">
            <h2 className="panel-title">Rekap {selectedCourse.nama_matkul}</h2>
            <p className="panel-subtitle">
              Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.
            </p>
          </div>
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Mahasiswa</th>
                  <th className="text-center">Hadir</th>
                  <th className="text-center">Terlambat</th>
                  <th className="text-center">Sakit</th>
                  <th className="text-center">Izin</th>
                  <th className="text-center">Alpa</th>
                  <th className="text-center">Persentase</th>
                </tr>
              </thead>
              <tbody>
                {recapData.map((row) => {
                  const total = Math.max(Number(row.total_jadwal), 1);
                  const present = Number(row.hadir) + Number(row.terlambat);
                  const percentage = Math.min(100, Math.round((present / total) * 100));
                  const good = percentage >= 75;
                  return (
                    <tr key={row.npm}>
                      <td>
                        <p className="font-bold">{row.nama_mahasiswa}</p>
                        <p className="text-xs text-slate-500">{row.npm}</p>
                      </td>
                      <td className="text-center font-bold text-emerald-600">{row.hadir}</td>
                      <td className="text-center font-bold text-blue-600">{row.terlambat}</td>
                      <td className="text-center font-bold text-amber-600">{row.sakit}</td>
                      <td className="text-center font-bold text-sky-600">{row.izin}</td>
                      <td className="text-center font-bold text-rose-600">{row.alpa}</td>
                      <td>
                        <div className="flex min-w-40 items-center justify-center gap-3">
                          <div className="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                            <div
                              className={`h-full ${good ? 'bg-emerald-500' : 'bg-rose-500'}`}
                              style={{ width: `${percentage}%` }}
                            />
                          </div>
                          <span className={`font-black ${good ? 'text-emerald-600' : 'text-rose-600'}`}>
                            {percentage}%
                          </span>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      ) : selectedMatkul ? (
        <div className="empty-state panel">
          <i className="fas fa-file-circle-question" />
          <p>Belum ada mahasiswa atau data absensi untuk mata kuliah ini.</p>
        </div>
      ) : (
        <div className="empty-state panel">
          <i className="fas fa-filter" />
          <p>Pilih mata kuliah untuk melihat rekapitulasi.</p>
        </div>
      )}
    </section>
  );
}
