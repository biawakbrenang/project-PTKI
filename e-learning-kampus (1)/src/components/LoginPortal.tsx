import React, { useState } from 'react';
import { ShieldCheck, Eye, EyeOff, AlertTriangle } from 'lucide-react';
import { Role, User } from '../types';

interface LoginPortalProps {
  users: User[];
  onLogin: (role: Role, userIdentifier: string) => void;
}

export default function LoginPortal({ users, onLogin }: LoginPortalProps) {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!username || !password) {
      setError('Username/NPM dan Password wajib diisi.');
      return;
    }

    // Normalized username input lookup
    const normalizedInput = username.trim().toLowerCase();
    const matchedUser = users.find(u => {
      const cleanName = u.name.replace(/^(dr\.|prof\.|mr\.|mrs\.|ms\.)\s+/i, '').toLowerCase();
      const firstName = cleanName.split(' ')[0];
      return (
        u.identifier === username || 
        u.originalIdentifier === username || 
        firstName === normalizedInput || 
        u.email.toLowerCase() === normalizedInput
      );
    });

    if (!matchedUser) {
      setError('Identitas pengguna (Username/NPM/Email) tidak terdaftar.');
      return;
    }

    if (matchedUser.suspended) {
      setError('Akun Anda ditangguhkan oleh Administrator. Silakan hubungi bagian Akademik.');
      return;
    }

    // Verify password: first check exact database password, then check fallback pattern
    const inputPassword = password.trim();
    const dbPasswordMatches = matchedUser.password && matchedUser.password === inputPassword;
    
    let isPasswordValid = dbPasswordMatches;

    // Fallback: [FirstName][3 digits] dynamic pattern check
    let cleanName = matchedUser.name.replace(/^(dr\.|prof\.|mr\.|mrs\.|ms\.)\s+/i, '').toLowerCase();
    let firstName = cleanName.split(' ')[0];
    if (matchedUser.role === 'admin' && (firstName.startsWith('admin') || firstName === 'administrator')) {
      firstName = 'admin';
    }

    if (!isPasswordValid) {
      const match = inputPassword.match(/^([a-zA-Z]+)(\d{3})$/);
      if (match) {
        const passwordNamePrefix = match[1].toLowerCase();
        if (passwordNamePrefix === firstName) {
          isPasswordValid = true;
        }
      }
    }

    if (!isPasswordValid) {
      const samplePassword = matchedUser.password || `${firstName.charAt(0).toUpperCase() + firstName.slice(1)}123`;
      setError(`Sandi salah. Silakan masukkan kata sandi yang sesuai di database untuk akun ini (Contoh: ${samplePassword}).`);
      return;
    }

    onLogin(matchedUser.role, matchedUser.identifier);
  };

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 relative overflow-hidden font-sans">
      {/* Background accents */}
      <div className="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-emerald-100/30 blur-3xl" />
      <div className="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-green-100/20 blur-3xl" />

      {/* Grid Pattern */}
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-slate-50 via-slate-100 to-slate-100 opacity-95 z-0" />

      <div className="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-xl relative z-10">
        {/* Header */}
        <div className="flex flex-col items-center mb-8">
          <div className="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 mb-4 shadow-sm">
            <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h1 className="text-2xl font-display font-bold text-slate-900 tracking-tight text-center">
            ECO-LEARNING
          </h1>
          <p className="text-slate-500 text-sm mt-1 text-center">
            Portal Administrasi Akademik Lingkungan
          </p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-start gap-2">
            <AlertTriangle className="w-4 h-4 shrink-0 text-red-500" />
            <div>{error}</div>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          {/* Identity Field */}
          <div>
            <label className="block text-slate-500 text-xs font-semibold mb-1.5 uppercase tracking-wider">
              NPM / NIDN / Username
            </label>
            <input
              type="text"
              required
              className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition-colors font-mono"
              placeholder="NPM (e.g., 2204101008) atau Username"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
            />
          </div>

          {/* Password Field */}
          <div>
            <div className="flex justify-between items-center mb-1.5">
              <label className="block text-slate-500 text-xs font-semibold uppercase tracking-wider">
                Password
              </label>
            </div>
            <div className="relative">
              <input
                type={showPassword ? 'text' : 'password'}
                required
                className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl pl-4 pr-10 py-3 text-sm text-slate-800 placeholder-slate-400 transition-colors font-mono"
                placeholder="Kata sandi (e.g., Guntur251 atau Erwin247)"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-600"
              >
                {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>

          <button
            type="submit"
            className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-medium py-3 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2 text-sm mt-2 active:scale-[0.99] cursor-pointer font-bold"
          >
            <ShieldCheck className="w-4 h-4" />
            Masuk ke Sistem
          </button>
        </form>
      </div>
    </div>
  );
}
