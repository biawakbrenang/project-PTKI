import { useState } from "react";
import {
  GraduationCap, LayoutDashboard, BookOpen, ClipboardList, Upload, UserCheck,
  Megaphone, Award, LogOut, Menu, Search, Bell, ChevronRight, Eye, Edit, Trash2,
  Plus, FileText, Users, CheckCircle2, Clock, AlertCircle, TrendingUp,
  ChevronDown, School, UserPen, User, Library, BarChart3, Settings,
  Download, Check, X, Paperclip, FileCheck, CalendarClock
} from "lucide-react";
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell, LineChart, Line, Legend
} from "recharts";

/* =========================================================================
   DATA MOCK (simulasi hasil query dari database MySQL / phpMyAdmin)
   ========================================================================= */

type User = { id: number; nama: string; role: string; email: string };

const users: User[] = [
  { id: 1, nama: "Dr. Andi Wijaya, M.Kom", role: "Dosen", email: "andi.wijaya@campus.ac.id" },
  { id: 2, nama: "Siti Rahmawati, S.Pd, M.Pd", role: "Dosen", email: "siti.r@campus.ac.id" },
  { id: 3, nama: "Budi Santoso", role: "Mahasiswa", email: "budisantoso@mhs.campus.ac.id" },
  { id: 4, nama: "Dewi Lestari", role: "Mahasiswa", email: "dewi.lestari@mhs.campus.ac.id" },
  { id: 5, nama: "Agus Pratama", role: "Mahasiswa", email: "agus.p@mhs.campus.ac.id" },
  { id: 6, nama: "Admin Pusat", role: "Admin", email: "admin@campus.ac.id" },
];

const dosenKelas = [
  { nama: "IF-3A", matkul: "Pemrograman Web", jumlah: 32 },
  { nama: "IF-5B", matkul: "Basis Data", jumlah: 28 },
  { nama: "TI-2A", matkul: "Algoritma & Struktur Data", jumlah: 35 },
  { nama: "IF-1C", matkul: "Pemrograman Berorientasi Objek", jumlah: 30 },
];

const submissions = [
  { nama: "Budi Santoso", file: "tugas1_budi.zip", waktu: "2026-02-10 09:12", nilai: 88 },
  { nama: "Dewi Lestari", file: "tugas1_dewi.pdf", waktu: "2026-02-10 10:45", nilai: 92 },
  { nama: "Agus Pratama", file: "tugas1_agus.rar", waktu: "2026-02-10 11:30", nilai: 75 },
  { nama: "Rina Marlina", file: "tugas1_rina.docx", waktu: "2026-02-11 08:05", nilai: null },
];

const absensi = [
  { nama: "Budi Santoso", status: "Hadir" },
  { nama: "Dewi Lestari", status: "Hadir" },
  { nama: "Agus Pratama", status: "Izin" },
  { nama: "Rina Marlina", status: "Alpha" },
  { nama: "Fajar Nugroho", status: "Hadir" },
  { nama: "Sri Wahyuni", status: "Hadir" },
  { nama: "Hendra Gunawan", status: "Izin" },
  { nama: "Maya Sari", status: "Alpha" },
];

const pengumuman = [
  { judul: "Jadwal UTS Semester Genap", isi: "Ujian Tengah Semester akan dilaksanakan mulai 15 Maret 2026. Harap mempersiapkan diri dengan baik.", tanggal: "2026-02-12" },
  { judul: "Perubahan Ruang Kuliah Basis Data", isi: "Kuliah Basis Data IF-5B dipindah ke Lab Komputer 3 mulai minggu depan.", tanggal: "2026-02-10" },
  { judul: "Batas Akhir Pembayaran UKT", isi: "Pembayaran Uang Kuliah Tunggal untuk angkatan 2025 dilakukan paling lambat 28 Februari 2026.", tanggal: "2026-02-08" },
];

const mhsKelas = [
  { nama: "IF-3A", matkul: "Pemrograman Web", dosen: "Dr. Andi Wijaya, M.Kom" },
  { nama: "IF-5B", matkul: "Basis Data", dosen: "Dr. Andi Wijaya, M.Kom" },
  { nama: "TI-2A", matkul: "Algoritma & Struktur Data", dosen: "Siti Rahmawati, M.Pd" },
];

const mhsTugas = [
  { judul: "Tugas 1 - HTML & CSS", deadline: "2026-02-14 23:59", status: "Belum Selesai" },
  { judul: "Tugas 2 - PHP Native CRUD", deadline: "2026-02-18 23:59", status: "Belum Selesai" },
  { judul: "Tugas 1 - SQL Query", deadline: "2026-02-05 23:59", status: "Selesai" },
  { judul: "Tugas 3 - Laravel Blade", deadline: "2026-02-25 23:59", status: "Belum Selesai" },
];

const mhsNilai = [
  { tugas: "Tugas 1 - HTML & CSS", nilai: 88, feedback: "Struktur bagus, lanjutkan." },
  { tugas: "Tugas 1 - SQL Query", nilai: 92, feedback: "Sangat memuaskan." },
  { tugas: "Tugas 2 - JavaScript DOM", nilai: 79, feedback: "Perhatikan error handling." },
  { tugas: "Quiz 1 - Algoritma", nilai: 85, feedback: "Baik." },
];

const mhsAbsensi = [
  { tanggal: "2026-02-10", matkul: "Pemrograman Web", status: "Hadir" },
  { tanggal: "2026-02-09", matkul: "Basis Data", status: "Hadir" },
  { tanggal: "2026-02-08", matkul: "Algoritma", status: "Izin" },
  { tanggal: "2026-02-07", matkul: "Pemrograman Web", status: "Hadir" },
  { tanggal: "2026-02-06", matkul: "Basis Data", status: "Alpha" },
];

const matkul = [
  { kode: "IF101", nama: "Pemrograman Web", sks: 3 },
  { kode: "IF102", nama: "Basis Data", sks: 3 },
  { kode: "IF103", nama: "Algoritma & Struktur Data", sks: 4 },
  { kode: "IF104", nama: "Pemrograman Berorientasi Objek", sks: 3 },
];

const adminKelas = [
  { nama: "IF-3A", dosen: "Dr. Andi Wijaya, M.Kom", matkul: "Pemrograman Web" },
  { nama: "IF-5B", dosen: "Dr. Andi Wijaya, M.Kom", matkul: "Basis Data" },
  { nama: "TI-2A", dosen: "Siti Rahmawati, M.Pd", matkul: "Algoritma & Struktur Data" },
  { nama: "IF-1C", dosen: "Siti Rahmawati, M.Pd", matkul: "Pemrograman Berorientasi Objek" },
];

const reportNilai = [
  { matkul: "Pemrograman Web", a: 12, b: 18, c: 6, d: 1 },
  { matkul: "Basis Data", a: 10, b: 14, c: 9, d: 0 },
  { matkul: "Algoritma", a: 15, b: 12, c: 5, d: 2 },
];

const reportKehadiran = [
  { bulan: "Sep", persen: 90 },
  { bulan: "Okt", persen: 88 },
  { bulan: "Nov", persen: 92 },
  { bulan: "Des", persen: 85 },
  { bulan: "Jan", persen: 94 },
  { bulan: "Feb", persen: 91 },
];

const pieData = [
  { name: "Dosen", value: 8, color: "#2563eb" },
  { name: "Mahasiswa", value: 1240, color: "#16a34a" },
  { name: "Admin", value: 4, color: "#f59e0b" },
];

/* =========================================================================
   REUSABLE UI PRIMITIVES
   ========================================================================= */

function Badge({ children, color = "blue" }: { children: React.ReactNode; color?: string }) {
  const colors: Record<string, string> = {
    blue: "bg-blue-50 text-blue-700 ring-blue-600/20",
    green: "bg-green-50 text-green-700 ring-green-600/20",
    red: "bg-red-50 text-red-700 ring-red-600/20",
    yellow: "bg-amber-50 text-amber-700 ring-amber-600/20",
    gray: "bg-slate-100 text-slate-600 ring-slate-500/20",
    purple: "bg-purple-50 text-purple-700 ring-purple-600/20",
  };
  return (
    <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${colors[color]}`}>
      {children}
    </span>
  );
}

function statusBadge(status: string) {
  const s = status.toLowerCase();
  if (s.includes("hadir")) return <Badge color="green"><CheckCircle2 className="h-3 w-3" />Hadir</Badge>;
  if (s.includes("izin")) return <Badge color="yellow"><Clock className="h-3 w-3" />Izin</Badge>;
  if (s.includes("alpha")) return <Badge color="red"><AlertCircle className="h-3 w-3" />Alpha</Badge>;
  if (s.includes("selesai")) return <Badge color="green"><CheckCircle2 className="h-3 w-3" />Selesai</Badge>;
  if (s.includes("belum")) return <Badge color="yellow"><Clock className="h-3 w-3" />Belum Selesai</Badge>;
  return <Badge color="gray">{status}</Badge>;
}

function Card({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) {
  return (
    <div style={style} className={`rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md ${className}`}>
      {children}
    </div>
  );
}

function StatCard({ icon: Icon, label, value, color, trend, delay = 0 }: {
  icon: React.ComponentType<{ className?: string }>;
  label: string; value: string | number; color: string; trend?: string; delay?: number;
}) {
  return (
    <Card className="animate-fade-up p-5" style={{ animationDelay: `${delay}ms` } as React.CSSProperties}>
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-slate-500">{label}</p>
          <p className="mt-1 text-2xl font-bold text-slate-800">{value}</p>
        </div>
        <div className={`flex h-12 w-12 items-center justify-center rounded-lg ${color}`}>
          <Icon className="h-6 w-6 text-white" />
        </div>
      </div>
      {trend && (
        <div className="mt-3 flex items-center gap-1 text-xs text-slate-500">
          <TrendingUp className="h-3.5 w-3.5 text-green-500" /> {trend} dibanding minggu lalu
        </div>
      )}
    </Card>
  );
}

function Button({ children, variant = "blue", size = "md", icon: Icon, className = "", ...props }: any) {
  const variants: Record<string, string> = {
    blue: "bg-blue-600 hover:bg-blue-700 text-white shadow-sm",
    green: "bg-green-600 hover:bg-green-700 text-white shadow-sm",
    red: "bg-red-600 hover:bg-red-700 text-white shadow-sm",
    gray: "bg-slate-100 hover:bg-slate-200 text-slate-700",
    outline: "border border-slate-300 bg-white hover:bg-slate-50 text-slate-700",
  };
  const sizes: Record<string, string> = {
    sm: "px-2.5 py-1.5 text-xs",
    md: "px-4 py-2 text-sm",
    lg: "px-5 py-2.5 text-base",
  };
  return (
    <button
      className={`inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-all duration-200 active:scale-95 ${variants[variant]} ${sizes[size]} ${className}`}
      {...props}
    >
      {Icon && <Icon className="h-4 w-4" />}
      {children}
    </button>
  );
}

function SectionTitle({ title, subtitle, action }: { title: string; subtitle?: string; action?: React.ReactNode }) {
  return (
    <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 className="text-lg font-semibold text-slate-800">{title}</h2>
        {subtitle && <p className="text-sm text-slate-500">{subtitle}</p>}
      </div>
      {action}
    </div>
  );
}

function Table({ headers, children }: { headers: string[]; children: React.ReactNode }) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full border-collapse text-sm">
        <thead>
          <tr className="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            {headers.map((h) => (
              <th key={h} className="px-4 py-3 font-semibold">{h}</th>
            ))}
          </tr>
        </thead>
        <tbody>{children}</tbody>
      </table>
    </div>
  );
}

/* =========================================================================
   SIDEBAR + TOPBAR (shared by Dosen / Mahasiswa / Admin)
   ========================================================================= */

function DashboardShell({ role, menu, active, setActive, children, user }: {
  role: string; menu: { key: string; label: string; icon: React.ComponentType<{ className?: string }> }[];
  active: string; setActive: (k: string) => void; children: React.ReactNode; user: string;
}) {
  const [open, setOpen] = useState(false);
  return (
    <div className="flex min-h-screen bg-slate-100">
      {/* Sidebar */}
      <aside className={`fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-gradient-to-b from-blue-800 to-blue-900 text-white transition-transform duration-300 lg:static lg:translate-x-0 ${open ? "translate-x-0" : "-translate-x-full"}`}>
        <div className="flex items-center gap-3 border-b border-white/10 px-5 py-4">
          <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15">
            <GraduationCap className="h-6 w-6" />
          </div>
          <div className="leading-tight">
            <p className="text-sm font-bold">E-Learning</p>
            <p className="text-xs text-blue-200">Akademik System</p>
          </div>
        </div>

        <div className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-blue-300">Panel {role}</div>

        <nav className="scroll-slim flex-1 space-y-1 overflow-y-auto px-3 pb-4">
          {menu.map((m) => {
            const Icon = m.icon;
            const isActive = active === m.key;
            return (
              <button
                key={m.key}
                onClick={() => { setActive(m.key); setOpen(false); }}
                className={`group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 ${isActive ? "bg-white text-blue-800 shadow" : "text-blue-100 hover:bg-white/10"}`}
              >
                <Icon className={`h-5 w-5 transition-transform duration-200 group-hover:scale-110 ${isActive ? "text-blue-700" : ""}`} />
                {m.label}
                {isActive && <ChevronRight className="ml-auto h-4 w-4" />}
              </button>
            );
          })}
        </nav>

        <div className="border-t border-white/10 p-4">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-sm font-bold">
              {user.split(" ").slice(0, 2).map((w) => w[0]).join("")}
            </div>
            <div className="min-w-0 leading-tight">
              <p className="truncate text-sm font-medium">{user}</p>
              <p className="text-xs text-blue-200">{role}</p>
            </div>
          </div>
        </div>
      </aside>

      {/* Overlay mobile */}
      {open && <div className="fixed inset-0 z-40 bg-black/40 lg:hidden" onClick={() => setOpen(false)} />}

      {/* Main */}
      <div className="flex flex-1 flex-col">
        <header className="sticky top-0 z-30 flex items-center gap-4 border-b border-slate-200 bg-white px-5 py-3 shadow-sm">
          <button onClick={() => setOpen(true)} className="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
            <Menu className="h-5 w-5" />
          </button>
          <div className="relative hidden flex-1 max-w-md md:block">
            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
              placeholder="Cari mahasiswa, tugas, mata kuliah..."
              className="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
            />
          </div>
          <div className="ml-auto flex items-center gap-3">
            <button className="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100">
              <Bell className="h-5 w-5" />
              <span className="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white" />
            </button>
            <div className="hidden items-center gap-2 border-l border-slate-200 pl-3 sm:flex">
              <div className="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                {user.split(" ").slice(0, 2).map((w) => w[0]).join("")}
              </div>
              <div className="leading-tight">
                <p className="text-sm font-medium text-slate-700">{user}</p>
                <p className="text-xs text-slate-400">{role}</p>
              </div>
            </div>
          </div>
        </header>

        <main key={active} className="animate-fade-in flex-1 p-5 lg:p-7">
          {children}
        </main>
      </div>
    </div>
  );
}

/* =========================================================================
   LOGIN PAGE
   ========================================================================= */

function LoginPage({ onLogin }: { onLogin: (role: string) => void }) {
  const [role, setRole] = useState("Mahasiswa");
  const [email, setEmail] = useState("");
  const [pwd, setPwd] = useState("");

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 via-white to-slate-100 p-4">
      <div className="animate-pop grid w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl shadow-blue-200/40 md:grid-cols-2">
        {/* Branding panel */}
        <div className="relative hidden flex-col justify-between bg-gradient-to-br from-blue-700 to-blue-900 p-10 text-white md:flex">
          <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl" />
          <div className="absolute -bottom-12 -left-8 h-48 w-48 rounded-full bg-white/5 blur-2xl" />
          <div className="relative flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
              <GraduationCap className="h-7 w-7" />
            </div>
            <span className="text-lg font-bold">E-Learning Akademik</span>
          </div>
          <div className="relative">
            <h2 className="text-2xl font-bold leading-snug">Satu platform untuk belajar, mengajar, dan mengelola kampus.</h2>
            <p className="mt-3 text-sm text-blue-100">Sistem Informasi Akademik berbasis web — Admin, Dosen, dan Mahasiswa dalam satu dashboard.</p>
          </div>
          <div className="relative grid grid-cols-3 gap-3 text-center text-xs">
            <div className="rounded-lg bg-white/10 p-3"><School className="mx-auto mb-1 h-5 w-5" />Kampus</div>
            <div className="rounded-lg bg-white/10 p-3"><BookOpen className="mx-auto mb-1 h-5 w-5" />Pembelajaran</div>
            <div className="rounded-lg bg-white/10 p-3"><BarChart3 className="mx-auto mb-1 h-5 w-5" />Penilaian</div>
          </div>
        </div>

        {/* Form panel */}
        <div className="p-8 sm:p-10">
          <div className="mb-7 text-center md:text-left">
            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-200 md:hidden">
              <GraduationCap className="h-8 w-8 text-white" />
            </div>
            <h1 className="text-2xl font-bold text-slate-800">Selamat Datang</h1>
            <p className="text-sm text-slate-500">Masuk ke Academic E-Learning System</p>
          </div>

          <form onSubmit={(e) => { e.preventDefault(); onLogin(role); }} className="space-y-4">
            <div>
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Email / Username</label>
              <div className="relative">
                <User className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  required value={email} onChange={(e) => setEmail(e.target.value)}
                  placeholder="nama@campus.ac.id"
                  className="w-full rounded-lg border border-slate-200 py-2.5 pl-9 pr-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                />
              </div>
            </div>

            <div>
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
              <div className="relative">
                <Settings className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  required type="password" value={pwd} onChange={(e) => setPwd(e.target.value)}
                  placeholder="••••••••"
                  className="w-full rounded-lg border border-slate-200 py-2.5 pl-9 pr-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                />
              </div>
            </div>

            <div>
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Login sebagai</label>
              <div className="relative">
                <UserPen className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <select
                  value={role} onChange={(e) => setRole(e.target.value)}
                  className="w-full appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-9 pr-9 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                >
                  <option>Admin</option>
                  <option>Dosen</option>
                  <option>Mahasiswa</option>
                </select>
                <ChevronDown className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              </div>
            </div>

            <div className="flex items-center justify-between text-sm">
              <label className="flex items-center gap-2 text-slate-600">
                <input type="checkbox" className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-400" /> Ingat saya
              </label>
              <a className="font-medium text-blue-600 hover:underline">Lupa password?</a>
            </div>

            <Button type="submit" variant="blue" size="lg" className="w-full">Masuk ke Dashboard</Button>
          </form>

          <p className="mt-6 text-center text-xs text-slate-400">
            © 2026 Universitas Campus. Integrated with MySQL / phpMyAdmin.
          </p>
        </div>
      </div>
    </div>
  );
}

/* =========================================================================
   DOSEN MODULE
   ========================================================================= */

function DosenModule({ onLogout }: { onLogout: () => void }) {
  const [active, setActive] = useState("Dashboard");
  const menu = [
    { key: "Dashboard", label: "Dashboard", icon: LayoutDashboard },
    { key: "Kelas", label: "Kelas", icon: BookOpen },
    { key: "Tugas", label: "Tugas", icon: ClipboardList },
    { key: "Submission", label: "Submission", icon: Upload },
    { key: "Absensi", label: "Absensi", icon: UserCheck },
    { key: "Pengumuman", label: "Pengumuman", icon: Megaphone },
    { key: "Nilai", label: "Nilai", icon: Award },
    { key: "Logout", label: "Logout", icon: LogOut },
  ];

  const go = (k: string) => k === "Logout" ? onLogout() : setActive(k);

  return (
    <DashboardShell role="Dosen" menu={menu} active={active} setActive={go} user="Dr. Andi Wijaya, M.Kom">
      {active === "Dashboard" && (
        <div className="space-y-6">
          <SectionTitle title="Dashboard Dosen" subtitle="Ringkasan aktivitas mengajar Anda hari ini." />
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard icon={BookOpen} label="Total Kelas" value={4} color="bg-blue-500" trend="+1" delay={0} />
            <StatCard icon={ClipboardList} label="Tugas Aktif" value={6} color="bg-purple-500" trend="+2" delay={80} />
            <StatCard icon={Upload} label="Submission Baru" value={12} color="bg-amber-500" trend="+5" delay={160} />
            <StatCard icon={UserCheck} label="Kehadiran Mhs" value="92%" color="bg-green-500" trend="+3%" delay={240} />
          </div>
          <Card className="p-5">
            <SectionTitle title="Daftar Kelas" />
            <Table headers={["Nama Kelas", "Mata Kuliah", "Jumlah Mahasiswa", "Action"]}>
              {dosenKelas.map((k, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{k.nama}</td>
                  <td className="px-4 py-3 text-slate-600">{k.matkul}</td>
                  <td className="px-4 py-3"><Badge color="blue">{k.jumlah} Mhs</Badge></td>
                  <td className="px-4 py-3"><Button variant="outline" size="sm" icon={Eye}>View</Button></td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Kelas" && (
        <div className="space-y-6">
          <SectionTitle title="Manajemen Kelas" subtitle="Daftar kelas yang Anda ampu." />
          <Card className="p-5">
            <Table headers={["Nama Kelas", "Mata Kuliah", "Jumlah Mahasiswa", "Action"]}>
              {dosenKelas.map((k, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{k.nama}</td>
                  <td className="px-4 py-3 text-slate-600">{k.matkul}</td>
                  <td className="px-4 py-3"><Badge color="blue">{k.jumlah} Mhs</Badge></td>
                  <td className="px-4 py-3"><Button variant="outline" size="sm" icon={Eye}>View</Button></td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Tugas" && <TugasDosen />}

      {active === "Submission" && (
        <div className="space-y-6">
          <SectionTitle title="Daftar Submission" subtitle="Jawaban tugas yang dikumpulkan mahasiswa." />
          <Card className="p-5">
            <Table headers={["Nama Mahasiswa", "File Jawaban", "Waktu Submit", "Nilai", "Action"]}>
              {submissions.map((s, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{s.nama}</td>
                  <td className="px-4 py-3"><span className="inline-flex items-center gap-1.5 text-slate-600"><Paperclip className="h-4 w-4 text-blue-500" />{s.file}</span></td>
                  <td className="px-4 py-3 text-slate-500">{s.waktu}</td>
                  <td className="px-4 py-3">{s.nilai === null ? <Badge color="yellow">Belum dinilai</Badge> : <Badge color="green">{s.nilai}</Badge>}</td>
                  <td className="px-4 py-3 flex gap-2">
                    <Button variant="outline" size="sm" icon={Eye}>Nilai</Button>
                    <Button variant="gray" size="sm" icon={Download}>Unduh</Button>
                  </td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Absensi" && (
        <div className="space-y-6">
          <SectionTitle title="Rekap Absensi Mahasiswa" subtitle="Pertemuan 5 — Pemrograman Web (IF-3A)" action={<Button variant="green" icon={FileCheck}>Export</Button>} />
          <Card className="p-5">
            <Table headers={["Nama Mahasiswa", "Status"]}>
              {absensi.map((a, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{a.nama}</td>
                  <td className="px-4 py-3">{statusBadge(a.status)}</td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Pengumuman" && <PengumumanPage />}

      {active === "Nilai" && (
        <div className="space-y-6">
          <SectionTitle title="Input Nilai" subtitle="Nilai akhir per mata kuliah." />
          <Card className="p-5">
            <Table headers={["Nama Mahasiswa", "Tugas", "UTS", "UAS", "Nilai Akhir"]}>
              {submissions.map((s, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{s.nama}</td>
                  <td className="px-4 py-3">{s.nilai ?? "-"}</td>
                  <td className="px-4 py-3">78</td>
                  <td className="px-4 py-3">85</td>
                  <td className="px-4 py-3"><Badge color="green">{(Math.round((((s.nilai ?? 0) + 78 + 85) / 3)))}</Badge></td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}
    </DashboardShell>
  );
}

function TugasDosen() {
  const [showForm, setShowForm] = useState(false);
  return (
    <div className="space-y-6">
      <SectionTitle title="Manajemen Tugas" subtitle="Buat dan kelola tugas perkuliahan." action={<Button variant="blue" icon={Plus} onClick={() => setShowForm((v) => !v)}>Buat Tugas</Button>} />
      {showForm && (
        <Card className="animate-fade-up p-6">
          <h3 className="mb-4 flex items-center gap-2 text-base font-semibold text-slate-800"><FileText className="h-5 w-5 text-blue-600" />Form Buat Tugas</h3>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="md:col-span-2">
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Judul Tugas</label>
              <input className="input" placeholder="Contoh: Tugas 3 - CRUD Laravel" />
            </div>
            <div className="md:col-span-2">
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi</label>
              <textarea rows={3} className="input" placeholder="Tulis instruksi pengerjaan tugas..." />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Deadline</label>
              <input type="datetime-local" className="input" />
            </div>
            <div>
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Kelas</label>
              <select className="input">
                {dosenKelas.map((k) => <option key={k.nama}>{k.nama} — {k.matkul}</option>)}
              </select>
            </div>
            <div className="md:col-span-2">
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Upload File Materi</label>
              <label className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 py-6 text-sm text-slate-500 transition hover:border-blue-400 hover:bg-blue-50">
                <Upload className="mb-2 h-6 w-6 text-blue-500" />
                Klik atau seret file materi ke sini (.pdf, .zip, .docx)
                <input type="file" className="hidden" />
              </label>
            </div>
          </div>
          <div className="mt-5 flex justify-end gap-2">
            <Button variant="gray" onClick={() => setShowForm(false)} icon={X}>Batal</Button>
            <Button variant="green" icon={Check}>Simpan Tugas</Button>
          </div>
        </Card>
      )}
      <Card className="p-5">
        <Table headers={["Judul Tugas", "Mata Kuliah", "Deadline", "Status"]}>
          {mhsTugas.map((t, i) => (
            <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
              <td className="px-4 py-3 font-medium text-slate-700">{t.judul}</td>
              <td className="px-4 py-3 text-slate-600">Pemrograman Web</td>
              <td className="px-4 py-3 flex items-center gap-1.5 text-slate-500"><CalendarClock className="h-4 w-4 text-slate-400" />{t.deadline}</td>
              <td className="px-4 py-3">{statusBadge(t.status)}</td>
            </tr>
          ))}
        </Table>
      </Card>
    </div>
  );
}

function PengumumanPage() {
  return (
    <div className="space-y-6">
      <SectionTitle title="Pengumuman" subtitle="Informasi terbaru untuk mahasiswa." action={<Button variant="blue" icon={Plus}>Buat Pengumuman</Button>} />
      <div className="space-y-4">
        {pengumuman.map((p, i) => (
          <Card key={i} className="animate-fade-up p-5">
            <div className="flex items-start gap-4">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600"><Megaphone className="h-5 w-5" /></div>
              <div className="flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <h3 className="font-semibold text-slate-800">{p.judul}</h3>
                  <Badge color="gray">{p.tanggal}</Badge>
                </div>
                <p className="mt-1 text-sm text-slate-600">{p.isi}</p>
              </div>
            </div>
          </Card>
        ))}
      </div>
    </div>
  );
}

/* =========================================================================
   MAHASISWA MODULE
   ========================================================================= */

function MahasiswaModule({ onLogout }: { onLogout: () => void }) {
  const [active, setActive] = useState("Dashboard");
  const menu = [
    { key: "Dashboard", label: "Dashboard", icon: LayoutDashboard },
    { key: "Kelas Saya", label: "Kelas Saya", icon: BookOpen },
    { key: "Tugas", label: "Tugas", icon: ClipboardList },
    { key: "Submission", label: "Submission", icon: Upload },
    { key: "Absensi", label: "Absensi", icon: UserCheck },
    { key: "Nilai", label: "Nilai", icon: Award },
    { key: "Pengumuman", label: "Pengumuman", icon: Megaphone },
    { key: "Logout", label: "Logout", icon: LogOut },
  ];
  const go = (k: string) => k === "Logout" ? onLogout() : setActive(k);

  return (
    <DashboardShell role="Mahasiswa" menu={menu} active={active} setActive={go} user="Budi Santoso">
      {active === "Dashboard" && (
        <div className="space-y-6">
          <SectionTitle title="Dashboard Mahasiswa" subtitle="Halo Budi, selamat belajar hari ini!" />
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard icon={ClipboardList} label="Tugas Belum Selesai" value={3} color="bg-amber-500" delay={0} />
            <StatCard icon={Clock} label="Deadline Terdekat" value="2 Hari" color="bg-red-500" delay={80} />
            <StatCard icon={Award} label="Nilai Rata-rata" value="86.5" color="bg-blue-500" trend="+2.1" delay={160} />
            <StatCard icon={UserCheck} label="Persentase Kehadiran" value="94%" color="bg-green-500" trend="+1%" delay={240} />
          </div>
          <Card className="p-5">
            <SectionTitle title="Tugas Terdekat" />
            <Table headers={["Judul Tugas", "Deadline", "Status"]}>
              {mhsTugas.filter((t) => t.status !== "Selesai").map((t, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{t.judul}</td>
                  <td className="px-4 py-3 flex items-center gap-1.5 text-slate-500"><CalendarClock className="h-4 w-4 text-slate-400" />{t.deadline}</td>
                  <td className="px-4 py-3">{statusBadge(t.status)}</td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Kelas Saya" && (
        <div className="space-y-6">
          <SectionTitle title="Kelas Saya" />
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            {mhsKelas.map((k, i) => (
              <Card key={i} className="animate-fade-up p-5">
                <div className="flex items-center gap-3">
                  <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600"><BookOpen className="h-6 w-6" /></div>
                  <div>
                    <p className="font-semibold text-slate-800">{k.nama}</p>
                    <p className="text-sm text-slate-500">{k.matkul}</p>
                  </div>
                </div>
                <div className="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3 text-sm text-slate-600">
                  <UserPen className="h-4 w-4 text-slate-400" />{k.dosen}
                </div>
              </Card>
            ))}
          </div>
        </div>
      )}

      {active === "Tugas" && (
        <div className="space-y-6">
          <SectionTitle title="Daftar Tugas" />
          <Card className="p-5">
            <Table headers={["Judul Tugas", "Deadline", "Status"]}>
              {mhsTugas.map((t, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{t.judul}</td>
                  <td className="px-4 py-3 flex items-center gap-1.5 text-slate-500"><CalendarClock className="h-4 w-4 text-slate-400" />{t.deadline}</td>
                  <td className="px-4 py-3">{statusBadge(t.status)}</td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Submission" && (
        <div className="space-y-6">
          <SectionTitle title="Kumpulkan Tugas" subtitle="Unggah file jawaban Anda." />
          <Card className="p-6">
            <div className="mb-4">
              <label className="mb-1.5 block text-sm font-medium text-slate-700">Pilih Tugas</label>
              <select className="input">{mhsTugas.filter((t) => t.status !== "Selesai").map((t) => <option key={t.judul}>{t.judul}</option>)}</select>
            </div>
            <label className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 py-8 text-sm text-slate-500 transition hover:border-blue-400 hover:bg-blue-50">
              <Upload className="mb-2 h-7 w-7 text-blue-500" />
              Klik atau seret file jawaban ke sini
              <input type="file" className="hidden" />
            </label>
            <div className="mt-4 flex justify-end">
              <Button variant="green" icon={Check}>Submit Tugas</Button>
            </div>
          </Card>
        </div>
      )}

      {active === "Absensi" && (
        <div className="space-y-6">
          <SectionTitle title="Kehadiran Saya" subtitle="Lakukan presensi untuk pertemuan hari ini." action={<Button variant="blue" icon={UserCheck}>Hadir Sekarang</Button>} />
          <Card className="p-5">
            <Table headers={["Tanggal", "Mata Kuliah", "Status"]}>
              {mhsAbsensi.map((a, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 text-slate-600">{a.tanggal}</td>
                  <td className="px-4 py-3 font-medium text-slate-700">{a.matkul}</td>
                  <td className="px-4 py-3">{statusBadge(a.status)}</td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Nilai" && (
        <div className="space-y-6">
          <SectionTitle title="Nilai Saya" />
          <Card className="p-5">
            <Table headers={["Tugas", "Nilai", "Feedback"]}>
              {mhsNilai.map((n, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{n.tugas}</td>
                  <td className="px-4 py-3"><Badge color={n.nilai >= 85 ? "green" : n.nilai >= 75 ? "blue" : "red"}>{n.nilai}</Badge></td>
                  <td className="px-4 py-3 text-slate-600">{n.feedback}</td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Pengumuman" && <PengumumanPage />}
    </DashboardShell>
  );
}

/* =========================================================================
   ADMIN MODULE
   ========================================================================= */

function AdminModule({ onLogout }: { onLogout: () => void }) {
  const [active, setActive] = useState("Dashboard");
  const menu = [
    { key: "Dashboard", label: "Dashboard", icon: LayoutDashboard },
    { key: "User Management", label: "User Management", icon: Users },
    { key: "Dosen", label: "Dosen", icon: UserPen },
    { key: "Mahasiswa", label: "Mahasiswa", icon: UserCheck },
    { key: "Mata Kuliah", label: "Mata Kuliah", icon: Library },
    { key: "Kelas", label: "Kelas", icon: BookOpen },
    { key: "Reports", label: "Reports", icon: BarChart3 },
    { key: "Logout", label: "Logout", icon: LogOut },
  ];
  const go = (k: string) => k === "Logout" ? onLogout() : setActive(k);

  return (
    <DashboardShell role="Admin" menu={menu} active={active} setActive={go} user="Admin Pusat">
      {active === "Dashboard" && (
        <div className="space-y-6">
          <SectionTitle title="Dashboard Admin" subtitle="Statistik & ringkasan sistem akademik." />
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard icon={Users} label="Total User" value="1.252" color="bg-blue-500" trend="+12" delay={0} />
            <StatCard icon={UserPen} label="Total Dosen" value="8" color="bg-purple-500" delay={80} />
            <StatCard icon={GraduationCap} label="Total Mahasiswa" value="1.240" color="bg-green-500" trend="+30" delay={160} />
            <StatCard icon={BookOpen} label="Total Kelas" value="48" color="bg-amber-500" delay={240} />
          </div>
          <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card className="p-5 lg:col-span-2">
              <SectionTitle title="Distribusi Nilai per Mata Kuliah" />
              <ResponsiveContainer width="100%" height={260}>
                <BarChart data={reportNilai}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#eef2f7" />
                  <XAxis dataKey="matkul" tick={{ fontSize: 11, fill: "#64748b" }} />
                  <YAxis tick={{ fontSize: 11, fill: "#64748b" }} />
                  <Tooltip />
                  <Legend wrapperStyle={{ fontSize: 12 }} />
                  <Bar dataKey="a" stackId="a" fill="#16a34a" name="A" radius={[0,0,0,0]} />
                  <Bar dataKey="b" stackId="a" fill="#2563eb" name="B" />
                  <Bar dataKey="c" stackId="a" fill="#f59e0b" name="C" />
                  <Bar dataKey="d" stackId="a" fill="#ef4444" name="D" radius={[4,4,0,0]} />
                </BarChart>
              </ResponsiveContainer>
            </Card>
            <Card className="p-5">
              <SectionTitle title="Distribusi User" />
              <ResponsiveContainer width="100%" height={260}>
                <PieChart>
                  <Pie data={pieData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={80} label>
                    {pieData.map((d, i) => <Cell key={i} fill={d.color} />)}
                  </Pie>
                  <Tooltip />
                  <Legend wrapperStyle={{ fontSize: 12 }} />
                </PieChart>
              </ResponsiveContainer>
            </Card>
          </div>
        </div>
      )}

      {active === "User Management" && (
        <div className="space-y-6">
          <SectionTitle title="User Management" subtitle="Kelola semua akun pengguna sistem." action={<Button variant="blue" icon={Plus}>Tambah User</Button>} />
          <Card className="p-5">
            <Table headers={["ID", "Nama", "Role", "Email", "Action"]}>
              {users.map((u) => (
                <tr key={u.id} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 text-slate-500">#{u.id}</td>
                  <td className="px-4 py-3 font-medium text-slate-700">{u.nama}</td>
                  <td className="px-4 py-3">
                    {u.role === "Admin" ? <Badge color="purple">{u.role}</Badge> : u.role === "Dosen" ? <Badge color="blue">{u.role}</Badge> : <Badge color="green">{u.role}</Badge>}
                  </td>
                  <td className="px-4 py-3 text-slate-500">{u.email}</td>
                  <td className="px-4 py-3 flex gap-2">
                    <Button variant="outline" size="sm" icon={Edit}>Edit</Button>
                    <Button variant="gray" size="sm" icon={Trash2}>Hapus</Button>
                  </td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Dosen" && (
        <div className="space-y-6">
          <SectionTitle title="Data Dosen" action={<Button variant="blue" icon={Plus}>Tambah Dosen</Button>} />
          <Card className="p-5">
            <Table headers={["ID", "Nama Lengkap", "NIDN", "Email", "Action"]}>
              {users.filter((u) => u.role === "Dosen").map((u) => (
                <tr key={u.id} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 text-slate-500">#{u.id}</td>
                  <td className="px-4 py-3 font-medium text-slate-700">{u.nama}</td>
                  <td className="px-4 py-3 text-slate-500">00{u.id}2{u.id}4</td>
                  <td className="px-4 py-3 text-slate-500">{u.email}</td>
                  <td className="px-4 py-3 flex gap-2">
                    <Button variant="outline" size="sm" icon={Edit}>Edit</Button>
                    <Button variant="gray" size="sm" icon={Trash2}>Hapus</Button>
                  </td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Mahasiswa" && (
        <div className="space-y-6">
          <SectionTitle title="Data Mahasiswa" subtitle="1.240 mahasiswa terdaftar." action={<Button variant="blue" icon={Plus}>Tambah Mahasiswa</Button>} />
          <Card className="p-5">
            <Table headers={["NIM", "Nama", "Program Studi", "Angkatan", "Status"]}>
              {users.filter((u) => u.role === "Mahasiswa").map((u, i) => (
                <tr key={u.id} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 text-slate-500">2025{i}100{u.id}</td>
                  <td className="px-4 py-3 font-medium text-slate-700">{u.nama}</td>
                  <td className="px-4 py-3 text-slate-500">Informatika</td>
                  <td className="px-4 py-3 text-slate-500">2025</td>
                  <td className="px-4 py-3"><Badge color="green">Aktif</Badge></td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Mata Kuliah" && (
        <div className="space-y-6">
          <SectionTitle title="Manajemen Mata Kuliah" action={<Button variant="blue" icon={Plus}>Tambah Matkul</Button>} />
          <Card className="p-5">
            <Table headers={["Kode Mata Kuliah", "Nama Mata Kuliah", "SKS", "Action"]}>
              {matkul.map((m) => (
                <tr key={m.kode} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-blue-600">{m.kode}</td>
                  <td className="px-4 py-3 font-medium text-slate-700">{m.nama}</td>
                  <td className="px-4 py-3"><Badge color="blue">{m.sks} SKS</Badge></td>
                  <td className="px-4 py-3 flex gap-2">
                    <Button variant="outline" size="sm" icon={Edit}>Edit</Button>
                    <Button variant="gray" size="sm" icon={Trash2}>Hapus</Button>
                  </td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Kelas" && (
        <div className="space-y-6">
          <SectionTitle title="Manajemen Kelas" action={<Button variant="blue" icon={Plus}>Tambah Kelas</Button>} />
          <Card className="p-5">
            <Table headers={["Nama Kelas", "Dosen Pengampu", "Mata Kuliah", "Action"]}>
              {adminKelas.map((k, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{k.nama}</td>
                  <td className="px-4 py-3 text-slate-600">{k.dosen}</td>
                  <td className="px-4 py-3 text-slate-600">{k.matkul}</td>
                  <td className="px-4 py-3 flex gap-2">
                    <Button variant="outline" size="sm" icon={Edit}>Edit</Button>
                    <Button variant="gray" size="sm" icon={Trash2}>Hapus</Button>
                  </td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}

      {active === "Reports" && (
        <div className="space-y-6">
          <SectionTitle title="Laporan & Analitik" subtitle="Rekap performa akademik kampus." />
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard icon={GraduationCap} label="Rata-rata IPK" value="3.42" color="bg-blue-500" delay={0} />
            <StatCard icon={UserCheck} label="Rata-rata Kehadiran" value="91%" color="bg-green-500" delay={80} />
            <StatCard icon={ClipboardList} label="Tugas Terselesaikan" value="87%" color="bg-purple-500" delay={160} />
          </div>
          <Card className="p-5">
            <SectionTitle title="Tren Kehadiran Mahasiswa (6 Bulan)" />
            <ResponsiveContainer width="100%" height={280}>
              <LineChart data={reportKehadiran}>
                <CartesianGrid strokeDasharray="3 3" stroke="#eef2f7" />
                <XAxis dataKey="bulan" tick={{ fontSize: 12, fill: "#64748b" }} />
                <YAxis domain={[70, 100]} tick={{ fontSize: 12, fill: "#64748b" }} />
                <Tooltip />
                <Line type="monotone" dataKey="persen" stroke="#2563eb" strokeWidth={3} dot={{ r: 4, fill: "#2563eb" }} activeDot={{ r: 6 }} />
              </LineChart>
            </ResponsiveContainer>
          </Card>
          <Card className="p-5">
            <SectionTitle title="Rekap Nilai Akhir" />
            <Table headers={["Mata Kuliah", "A", "B", "C", "D"]}>
              {reportNilai.map((r, i) => (
                <tr key={i} className="border-b border-slate-100 transition-colors hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-slate-700">{r.matkul}</td>
                  <td className="px-4 py-3"><Badge color="green">{r.a}</Badge></td>
                  <td className="px-4 py-3"><Badge color="blue">{r.b}</Badge></td>
                  <td className="px-4 py-3"><Badge color="yellow">{r.c}</Badge></td>
                  <td className="px-4 py-3"><Badge color="red">{r.d}</Badge></td>
                </tr>
              ))}
            </Table>
          </Card>
        </div>
      )}
    </DashboardShell>
  );
}

/* =========================================================================
   ROOT APP
   ========================================================================= */

export default function App() {
  const [view, setView] = useState<{ page: string }>({ page: "login" });

  if (view.page === "login") {
    return <LoginPage onLogin={(role) => setView({ page: role })} />;
  }
  if (view.page === "Dosen") {
    return <DosenModule onLogout={() => setView({ page: "login" })} />;
  }
  if (view.page === "Mahasiswa") {
    return <MahasiswaModule onLogout={() => setView({ page: "login" })} />;
  }
  return <AdminModule onLogout={() => setView({ page: "login" })} />;
}
