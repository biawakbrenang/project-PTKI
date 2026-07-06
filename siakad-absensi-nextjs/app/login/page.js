import { redirect } from 'next/navigation';
import { getSession } from '../../lib/auth';
import LoginForm from '../../components/LoginForm';

export const metadata = { title: 'Login - Sistem Absensi Dosen' };

export default function LoginPage() {
  const session = getSession();
  if (session) {
    redirect('/dashboard');
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
      <section className="login-panel hidden items-center justify-center p-12 text-white lg:flex">
        <div className="max-w-xl">
          <div className="mb-8 inline-flex items-center gap-3 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">
            <i className="fas fa-shield-halved" />
            Sistem akademik dosen
          </div>
          <h1 className="text-5xl font-black leading-tight">
            Kelola absensi kelas dengan cepat dan rapi.
          </h1>
          <p className="mt-5 text-lg text-blue-100">
            Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan sudah terhubung
            dalam satu alur kerja.
          </p>
        </div>
      </section>

      <section className="flex items-center justify-center p-6">
        <div className="w-full max-w-md rounded-3xl border border-white bg-white p-8 shadow-xl shadow-slate-200 sm:p-10">
          <div className="mb-8">
            <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
              <i className="fas fa-graduation-cap text-2xl" />
            </div>
            <h2 className="text-2xl font-black text-slate-900">Masuk ke SIAKAD V2</h2>
            <p className="mt-2 text-sm text-slate-500">
              Gunakan akun dosen untuk mengakses sistem.
            </p>
          </div>

          <LoginForm />

          <div className="mt-8 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
            <p className="font-bold text-slate-800">Akun demo</p>
            <p>Email: firansyah@univ.ac.id</p>
            <p>Password: password</p>
          </div>
        </div>
      </section>
    </div>
  );
}
