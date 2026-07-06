'use client';

import { usePathname } from 'next/navigation';
import { useEffect, useState } from 'react';
import { logoutAction } from '../lib/actions';

const NAV_ITEMS = [
  { href: '/dashboard', label: 'Dashboard', icon: 'fa-chart-pie' },
  { href: '/absensi', label: 'Input Absensi', icon: 'fa-clipboard-check' },
  { href: '/rekap', label: 'Rekap Absensi', icon: 'fa-file-lines' },
  { href: '/mahasiswa', label: 'Mahasiswa', icon: 'fa-users' },
];

const PAGE_TITLES = {
  '/dashboard': 'Dashboard',
  '/absensi': 'Input Absensi',
  '/rekap': 'Rekap Absensi',
  '/mahasiswa': 'Data Mahasiswa',
};

export default function AppShell({ session, children }) {
  const pathname = usePathname();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [darkMode, setDarkMode] = useState(false);

  useEffect(() => {
    const saved = window.localStorage.getItem('absensi-theme');
    if (saved === 'dark') setDarkMode(true);
  }, []);

  useEffect(() => {
    document.body.classList.toggle('dark-mode', darkMode);
  }, [darkMode]);

  const toggleTheme = () => {
    setDarkMode((prev) => {
      const next = !prev;
      window.localStorage.setItem('absensi-theme', next ? 'dark' : 'light');
      return next;
    });
  };

  const currentTitle = PAGE_TITLES[pathname] || 'Halaman';
  const today = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });

  return (
    <>
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}
      <aside
        className={`fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-full flex-col">
          <div className="border-b border-slate-100 p-6">
            <a href="/dashboard" className="flex items-center gap-3">
              <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                <i className="fas fa-graduation-cap text-lg" />
              </span>
              <span>
                <span className="block text-xl font-black tracking-tight">SIAKAD V2</span>
                <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                  Absensi Dosen
                </span>
              </span>
            </a>
          </div>

          <nav className="flex-1 space-y-1 overflow-y-auto p-4">
            {NAV_ITEMS.map((item) => {
              const active = pathname === item.href;
              return (
                <a
                  key={item.href}
                  href={item.href}
                  className={`nav-link ${active ? 'nav-link-active' : ''}`}
                >
                  <i className={`fas ${item.icon} w-5`} />
                  <span>{item.label}</span>
                </a>
              );
            })}
          </nav>

          <div className="border-t border-slate-100 p-4">
            <div className="rounded-2xl bg-slate-50 p-4">
              <div className="flex items-center gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                  {(session?.nama_lengkap || 'D').substring(0, 1)}
                </div>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-sm font-bold">{session?.nama_lengkap || 'Dosen'}</p>
                  <p className="truncate text-xs text-slate-500">{session?.nidn || '-'}</p>
                </div>
                <form action={logoutAction}>
                  <button
                    className="icon-button text-slate-400 hover:text-red-600"
                    title="Keluar"
                    type="submit"
                  >
                    <i className="fas fa-right-from-bracket" />
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <div className="min-h-screen lg:pl-72">
        <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
          <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
            <div className="flex items-center gap-3">
              <button
                className="icon-button lg:hidden"
                type="button"
                aria-label="Buka menu"
                onClick={() => setSidebarOpen((v) => !v)}
              >
                <i className="fas fa-bars" />
              </button>
              <div>
                <h1 className="text-lg font-black sm:text-xl">{currentTitle}</h1>
                <p className="hidden text-xs text-slate-500 sm:block">{today}</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button
                className="icon-button"
                type="button"
                title="Ganti mode tampilan"
                onClick={toggleTheme}
              >
                <i className={`fas ${darkMode ? 'fa-sun' : 'fa-moon'}`} />
              </button>
              <form action={logoutAction} className="hidden sm:block">
                <button
                  className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-700"
                  type="submit"
                >
                  Keluar
                </button>
              </form>
            </div>
          </div>
        </header>

        <main className="p-4 sm:p-6 lg:p-8">{children}</main>
      </div>
    </>
  );
}
