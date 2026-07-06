import { getSession } from '../../../lib/auth';
import { getStats, getTodaySchedules } from '../../../lib/models/dashboardModel';

export const metadata = { title: 'Dashboard - Sistem Absensi Dosen' };

function formatTanggal(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

export default async function DashboardPage() {
  const session = getSession();
  const [stats, todaySchedules] = await Promise.all([
    getStats(session.user_id),
    getTodaySchedules(session.user_id),
  ]);

  const firstName = (session.nama_lengkap || '').split(',')[0];

  return (
    <section className="space-y-6">
      <div className="hero-band rounded-3xl p-6 text-white shadow-xl shadow-blue-100 sm:p-8">
        <div className="max-w-3xl">
          <p className="text-sm font-bold uppercase tracking-wider text-blue-100">
            Selamat datang kembali
          </p>
          <h2 className="mt-2 text-3xl font-black sm:text-4xl">{firstName}</h2>
          <p className="mt-3 text-blue-50">
            Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu
            dashboard.
          </p>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article className="stat-card">
          <span className="stat-icon bg-blue-50 text-blue-600">
            <i className="fas fa-book" />
          </span>
          <p className="stat-label">Mata Kuliah</p>
          <p className="stat-value">{stats.total_matkul ?? 0}</p>
        </article>
        <article className="stat-card">
          <span className="stat-icon bg-emerald-50 text-emerald-600">
            <i className="fas fa-user-graduate" />
          </span>
          <p className="stat-label">Mahasiswa</p>
          <p className="stat-value">{stats.total_mahasiswa ?? 0}</p>
        </article>
        <article className="stat-card">
          <span className="stat-icon bg-amber-50 text-amber-600">
            <i className="fas fa-calendar-check" />
          </span>
          <p className="stat-label">Jadwal Kelas</p>
          <p className="stat-value">{stats.total_jadwal ?? 0}</p>
        </article>
        <article className="stat-card">
          <span className="stat-icon bg-rose-50 text-rose-600">
            <i className="fas fa-chart-line" />
          </span>
          <p className="stat-label">Rata Kehadiran</p>
          <p className="stat-value">{stats.rata_kehadiran ?? 0}%</p>
        </article>
      </div>

      <div className="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
        <section className="panel">
          <div className="panel-header">
            <div>
              <h3 className="panel-title">Aksi Cepat</h3>
              <p className="panel-subtitle">Masuk ke pekerjaan utama tanpa banyak klik.</p>
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-3">
            <a href="/absensi" className="quick-action">
              <i className="fas fa-clipboard-check text-blue-600" />
              <span>Input Absensi</span>
              <small>Catat kehadiran kelas</small>
            </a>
            <a href="/rekap" className="quick-action">
              <i className="fas fa-file-lines text-emerald-600" />
              <span>Lihat Rekap</span>
              <small>Monitor persentase</small>
            </a>
            <a href="/mahasiswa" className="quick-action">
              <i className="fas fa-users text-amber-600" />
              <span>Mahasiswa</span>
              <small>Kelola peserta kelas</small>
            </a>
          </div>
        </section>

        <section className="panel">
          <div className="panel-header">
            <div>
              <h3 className="panel-title">Jadwal Terdekat</h3>
              <p className="panel-subtitle">5 pertemuan berikutnya di sistem.</p>
            </div>
          </div>
          <div className="space-y-3">
            {todaySchedules.map((schedule) => (
              <div className="schedule-row" key={schedule.id_jadwal}>
                <div>
                  <p className="font-bold">{schedule.nama_matkul}</p>
                  <p className="text-sm text-slate-500">
                    {schedule.kode_matkul} - Pertemuan {schedule.pertemuan_ke}
                  </p>
                </div>
                <div className="text-right text-sm">
                  <p className="font-bold text-slate-700">
                    {formatTanggal(schedule.tanggal_pertemuan)}
                  </p>
                  <p className="text-slate-500">
                    {String(schedule.jam_mulai).slice(0, 5)} - {String(schedule.jam_selesai).slice(0, 5)}
                  </p>
                </div>
              </div>
            ))}
            {todaySchedules.length === 0 && (
              <div className="empty-state py-8">
                <i className="far fa-calendar" />
                <p>Belum ada jadwal perkuliahan.</p>
              </div>
            )}
          </div>
        </section>
      </div>
    </section>
  );
}
