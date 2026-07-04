import React, { useState } from 'react';
import { 
  Users, UserCheck, ShieldAlert, FileText, Megaphone, 
  Search, Power, RefreshCw, BarChart3, Clock, Trash2, 
  PlusCircle, LayoutGrid, ToggleLeft, ToggleRight, LogOut, Check, X,
  User as UserIcon
} from 'lucide-react';
import { EcoAppState, Announcement, ActivityLog, User } from '../types';

interface AdminDashboardProps {
  state: EcoAppState;
  setState: React.Dispatch<React.SetStateAction<EcoAppState>>;
  onLogout: () => void;
}

export default function AdminDashboard({ state, setState, onLogout }: AdminDashboardProps) {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'users' | 'classes' | 'profile'>('dashboard');
  
  // Profile Management state
  const [profileName, setProfileName] = useState(state.currentUser?.name || 'Admin Chief');
  const [profileEmail, setProfileEmail] = useState(state.currentUser?.email || 'chief.admin@eco.ac.id');
  const [profilePhone, setProfilePhone] = useState(state.currentUser?.phone || '+62 811-1111-2222');
  const [profileDept, setProfileDept] = useState(state.currentUser?.department || 'Academic Operations');
  const [profileIdentifier, setProfileIdentifier] = useState(state.currentUser?.identifier || '19800101009');
  const [profileAvatarUrl, setProfileAvatarUrl] = useState(state.currentUser?.avatarUrl || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e');

  const handleSaveProfile = (e: React.FormEvent) => {
    e.preventDefault();
    setState(prev => {
      const updatedUser = prev.currentUser ? {
        ...prev.currentUser,
        name: profileName,
        email: profileEmail,
        phone: profilePhone,
        department: profileDept,
        identifier: profileIdentifier,
        avatarUrl: profileAvatarUrl
      } : null;

      const newLog = {
        id: `log-${Date.now()}`,
        actor: profileName,
        role: 'Administrator',
        action: `Memperbarui data profil administrator`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        currentUser: updatedUser,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Informasi profil Anda berhasil diperbarui secara lokal!');
  };

  // Class/Course Management state
  const [showAddClassModal, setShowAddClassModal] = useState(false);
  const [newClassCode, setNewClassCode] = useState('');
  const [newClassName, setNewClassName] = useState('');
  const [newClassLecturer, setNewClassLecturer] = useState('');
  const [newClassSks, setNewClassSks] = useState<number>(3);
  const [newClassRoom, setNewClassRoom] = useState('');
  const [newClassDay, setNewClassDay] = useState('Senin');
  const [newClassTime, setNewClassTime] = useState('08:00 - 10:30');
  const [classSearch, setClassSearch] = useState('');

  // Announcement broadcast state
  const [annTitle, setAnnTitle] = useState('');
  const [annContent, setAnnContent] = useState('');
  const [annCategory, setAnnCategory] = useState<'academic' | 'general' | 'event'>('academic');

  // User management state
  const [userSearch, setUserSearch] = useState('');
  const [userRoleFilter, setUserRoleFilter] = useState<string>('all');
  
  // New user creator state
  const [showAddUserModal, setShowAddUserModal] = useState(false);
  const [newUserName, setNewUserName] = useState('');
  const [newUserEmail, setNewUserEmail] = useState('');
  const [newUserRole, setNewUserRole] = useState<'student' | 'lecturer' | 'admin'>('student');
  const [newUserIdentifier, setNewUserIdentifier] = useState('');
  const [newUserDepartment, setNewUserDepartment] = useState('');

  const handleBroadcast = (e: React.FormEvent) => {
    e.preventDefault();
    if (!annTitle || !annContent) {
      alert('Judul dan Konten pengumuman wajib diisi!');
      return;
    }

    const newAnnouncement: Announcement = {
      id: `ann-${Date.now()}`,
      title: annTitle,
      content: annContent,
      category: annCategory,
      date: new Date().toISOString().split('T')[0],
      author: 'Administrator Akademik'
    };

    const newLog: ActivityLog = {
      id: `log-${Date.now()}`,
      actor: 'Admin Chief',
      role: 'Administrator',
      action: `Mempublikasikan pengumuman global: "${annTitle}"`,
      timestamp: new Date().toLocaleString()
    };

    setState(prev => {
      return {
        ...prev,
        announcements: [newAnnouncement, ...prev.announcements],
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });

    setAnnTitle('');
    setAnnContent('');
    alert('Pengumuman global berhasil disiarkan ke dasbor Dosen & Mahasiswa!');
  };

  const handleToggleSuspension = (userId: string) => {
    setState(prev => {
      const targetUser = prev.users.find(u => u.id === userId);
      if (!targetUser) return prev;

      const isCurrentlySuspended = !!targetUser.suspended;
      const updatedUsers = prev.users.map(u => {
        if (u.id === userId) {
          return { ...u, suspended: !isCurrentlySuspended };
        }
        return u;
      });

      const newLog = {
        id: `log-${Date.now()}`,
        actor: 'Admin Chief',
        role: 'Administrator',
        action: `${isCurrentlySuspended ? 'Mengaktifkan kembali' : 'Menangguhkan'} akun user: ${targetUser.name} (${targetUser.identifier})`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        users: updatedUsers,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Status suspensi akun pengguna berhasil diperbarui!');
  };

  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUserName || !newUserIdentifier || !newUserEmail) {
      alert('Nama, Identitas (NIP/NIDN/NPM), dan Email wajib diisi!');
      return;
    }

    const newUserObj: User = {
      id: `u-${Date.now()}`,
      name: newUserName,
      email: newUserEmail,
      role: newUserRole,
      identifier: newUserIdentifier,
      department: newUserDepartment || 'Academic Systems',
      avatarUrl: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=200',
      suspended: false
    };

    setState(prev => {
      const newLog = {
        id: `log-${Date.now()}`,
        actor: 'Admin Chief',
        role: 'Administrator',
        action: `Mendaftarkan akun ${newUserRole.toUpperCase()} baru: ${newUserName} (${newUserIdentifier})`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        users: [newUserObj, ...prev.users],
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });

    setShowAddUserModal(false);
    setNewUserName('');
    setNewUserEmail('');
    setNewUserIdentifier('');
    setNewUserDepartment('');
    alert(`User ${newUserName} berhasil didaftarkan ke dalam sistem!`);
  };

  const handleDeleteUser = (userId: string) => {
    if (!confirm('Apakah Anda yakin ingin menghapus permanen pengguna ini dari database akademik?')) return;
    
    setState(prev => {
      const targetUser = prev.users.find(u => u.id === userId);
      if (!targetUser) return prev;

      const newLog = {
        id: `log-${Date.now()}`,
        actor: 'Admin Chief',
        role: 'Administrator',
        action: `Menghapus akun pengguna permanen: ${targetUser.name} (${targetUser.identifier})`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        users: prev.users.filter(u => u.id !== userId),
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Pengguna berhasil dihapus secara permanen!');
  };

  const handleCreateCourse = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newClassCode || !newClassName || !newClassLecturer) {
      alert('Kode Kelas/MK, Nama Kelas/MK, dan Dosen Pengampu wajib diisi!');
      return;
    }

    const newCourseObj = {
      id: `c-${Date.now()}`,
      code: newClassCode,
      name: newClassName,
      lecturerName: newClassLecturer,
      studentsCount: Math.floor(Math.random() * 20) + 15,
      progress: 0,
      sks: Number(newClassSks),
      classRoom: newClassRoom || 'Ruang Seminar 2B',
      scheduleDay: newClassDay,
      scheduleTime: newClassTime || '08:00 - 10:30'
    };

    setState(prev => {
      const newLog = {
        id: `log-${Date.now()}`,
        actor: 'Admin Chief',
        role: 'Administrator',
        action: `Membuat Kelas/Mata Kuliah Baru: [${newClassCode}] ${newClassName} (Dosen: ${newClassLecturer})`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        courses: [...prev.courses, newCourseObj],
        activityLogs: [newLog, ...prev.activityLogs],
        systemStats: {
          ...prev.systemStats,
          activeCourses: prev.systemStats.activeCourses + 1
        }
      };
    });

    setShowAddClassModal(false);
    setNewClassCode('');
    setNewClassName('');
    setNewClassLecturer('');
    setNewClassRoom('');
    setNewClassDay('Senin');
    setNewClassTime('08:00 - 10:30');
    alert(`Kelas ${newClassName} berhasil didaftarkan ke sistem!`);
  };

  const handleDeleteCourse = (courseId: string, courseName: string) => {
    if (window.confirm(`Apakah Anda yakin ingin menghapus kelas "${courseName}"?`)) {
      setState(prev => {
        const newLog = {
          id: `log-${Date.now()}`,
          actor: 'Admin Chief',
          role: 'Administrator',
          action: `Menghapus Kelas/Mata Kuliah: ${courseName}`,
          timestamp: new Date().toLocaleString()
        };
        return {
          ...prev,
          courses: prev.courses.filter(c => c.id !== courseId),
          activityLogs: [newLog, ...prev.activityLogs],
          systemStats: {
            ...prev.systemStats,
            activeCourses: Math.max(0, prev.systemStats.activeCourses - 1)
          }
        };
      });
      alert('Kelas berhasil dihapus.');
    }
  };

  // Filtered users for table search
  const filteredUsers = state.users.filter(user => {
    const matchesSearch = 
      user.name.toLowerCase().includes(userSearch.toLowerCase()) ||
      user.identifier.includes(userSearch) ||
      user.email.toLowerCase().includes(userSearch.toLowerCase());
    
    const matchesRole = userRoleFilter === 'all' || user.role === userRoleFilter;

    return matchesSearch && matchesRole;
  });

  return (
    <div className="min-h-screen bg-[#F1F5F9] text-slate-800 flex font-sans">
      
      {/* SIDEBAR - Professional Administration Navy/Dark Slate theme */}
      <aside className="w-64 bg-[#1E293B] text-slate-200 flex flex-col shrink-0 border-r border-[#334155]">
        
        {/* Brand */}
        <div className="p-6 border-b border-[#334155]/50 flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-[#334155] flex items-center justify-center text-emerald-400">
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <div>
            <h1 className="font-display font-bold text-base leading-tight tracking-wide text-white">ECO-LEARNING</h1>
            <span className="text-[10px] text-emerald-400 font-mono tracking-widest font-semibold uppercase">ADMINISTRASI</span>
          </div>
        </div>

        {/* Profile */}
        <div className="p-6 flex items-center gap-3 border-b border-[#334155]/30">
          <img 
            className="w-10 h-10 rounded-full object-cover border-2 border-slate-500" 
            src={state.currentUser?.avatarUrl} 
            alt="Admin Chief"
            referrerPolicy="no-referrer"
          />
          <div>
            <h4 className="text-xs font-semibold leading-none text-white">{state.currentUser?.name}</h4>
            <span className="text-[10px] text-emerald-400 font-mono mt-1 block">NIP {state.currentUser?.identifier}</span>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 p-4 space-y-1">
          <button 
            onClick={() => setActiveTab('dashboard')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all ${activeTab === 'dashboard' ? 'bg-[#334155] text-white shadow-md border-l-4 border-emerald-400' : 'text-slate-400 hover:bg-[#334155]/40 hover:text-white'}`}
          >
            <BarChart3 className="w-4 h-4 shrink-0" />
            Dasbor Global
          </button>
          <button 
            onClick={() => setActiveTab('users')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all ${activeTab === 'users' ? 'bg-[#334155] text-white shadow-md border-l-4 border-emerald-400' : 'text-slate-400 hover:bg-[#334155]/40 hover:text-white'}`}
          >
            <Users className="w-4 h-4 shrink-0" />
            Pengguna & Akun
          </button>
          <button 
            onClick={() => setActiveTab('classes')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all ${activeTab === 'classes' ? 'bg-[#334155] text-white shadow-md border-l-4 border-emerald-400' : 'text-slate-400 hover:bg-[#334155]/40 hover:text-white'}`}
          >
            <FileText className="w-4 h-4 shrink-0" />
            Kelola Kelas & MK
          </button>
          <button 
            onClick={() => setActiveTab('profile')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all ${activeTab === 'profile' ? 'bg-[#334155] text-white shadow-md border-l-4 border-emerald-400' : 'text-slate-400 hover:bg-[#334155]/40 hover:text-white'}`}
          >
            <UserIcon className="w-4 h-4 shrink-0" />
            Profil Saya
          </button>
        </nav>

        {/* Logout */}
        <div className="p-4 border-t border-[#334155]/50">
          <button 
            onClick={onLogout}
            className="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs text-[#FCA3B7] hover:bg-red-950/20 transition-colors font-medium text-left"
          >
            <LogOut className="w-4 h-4" />
            Log Out Portal
          </button>
          <div className="mt-4 text-center text-[9px] text-slate-500 font-mono">
            Eco-Admin Console v1.0
          </div>
        </div>
      </aside>

      {/* MAIN ADMIN VIEWPORT */}
      <main className="flex-1 overflow-y-auto p-8 flex flex-col">
        
        {/* Header bar */}
        <header className="flex justify-between items-center mb-8 pb-4 border-b border-slate-200">
          <div>
            <span className="text-[10px] text-slate-500 font-bold uppercase tracking-wider font-mono">Academic System Control Center</span>
            <h2 className="text-2xl font-display font-bold text-slate-900 mt-1">
              {activeTab === 'dashboard' && "Dasbor Global & Utilitas Siaran"}
              {activeTab === 'users' && "Akun Pengguna & Verifikasi Log"}
              {activeTab === 'classes' && "Manajemen Kelas & Kurikulum Akademik"}
              {activeTab === 'profile' && "Pusat Profil & Data Pengenal Administrator"}
            </h2>
          </div>

          <div className="flex items-center gap-2 font-mono text-xs bg-white border border-slate-200 px-4 py-2 rounded-xl text-slate-600 shadow-sm">
            STATUS NODE: <span className="text-emerald-600 font-bold">ONLINE</span>
          </div>
        </header>

        {/* TAB 1: GLOBAL DASHBOARD */}
        {activeTab === 'dashboard' && (
          <div className="space-y-6">
            
            {/* Massive Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Siswa Terdaftar (Total)</span>
                  <h4 className="text-3xl font-display font-bold text-slate-900 mt-1">12,842</h4>
                  <p className="text-[10px] text-emerald-600 mt-1 font-mono">✓ Database sinkronisasi otomatis</p>
                </div>
                <div className="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-xl flex items-center justify-center">
                  <Users className="w-6 h-6" />
                </div>
              </div>

              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Dosen & Pengajar Aktif</span>
                  <h4 className="text-3xl font-display font-bold text-slate-900 mt-1">486</h4>
                  <p className="text-[10px] text-slate-400 mt-1 font-mono">Dosen ber-NIDN penuh</p>
                </div>
                <div className="w-12 h-12 bg-blue-50 text-blue-700 rounded-xl flex items-center justify-center">
                  <UserCheck className="w-6 h-6" />
                </div>
              </div>

              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Mata Kuliah Terbuka</span>
                  <h4 className="text-3xl font-display font-bold text-slate-900 mt-1">1,204</h4>
                  <p className="text-[10px] text-slate-400 mt-1 font-mono">Kelas KRS semester aktif</p>
                </div>
                <div className="w-12 h-12 bg-indigo-50 text-indigo-700 rounded-xl flex items-center justify-center">
                  <FileText className="w-6 h-6" />
                </div>
              </div>
            </div>

            {/* Grid for broadcaster form & graph log representation */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Broadcaster form */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm lg:col-span-1">
                <h3 className="font-display font-bold text-base text-slate-900 mb-4 flex items-center gap-2">
                  <Megaphone className="w-5 h-5 text-emerald-600" />
                  Siaran Pengumuman Global
                </h3>
                <form onSubmit={handleBroadcast} className="space-y-4">
                  <div>
                    <label className="block text-xs font-semibold text-slate-600 mb-1">JUDUL SIARAN</label>
                    <input 
                      type="text" 
                      required
                      placeholder="Masukkan judul pengumuman..."
                      className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800"
                      value={annTitle}
                      onChange={(e) => setAnnTitle(e.target.value)}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-slate-600 mb-1">KATEGORI</label>
                    <select 
                      className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-700 bg-transparent"
                      value={annCategory}
                      onChange={(e) => setAnnCategory(e.target.value as any)}
                    >
                      <option value="academic">Akademik / KRS</option>
                      <option value="general">Informasi Umum</option>
                      <option value="event">Kuliah Umum / Event</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-slate-600 mb-1">KONTEN SIARAN</label>
                    <textarea 
                      rows={4}
                      required
                      placeholder="Ketik isi pesan siaran pengumuman di sini secara rinci..."
                      className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800"
                      value={annContent}
                      onChange={(e) => setAnnContent(e.target.value)}
                    />
                  </div>

                  <button 
                    type="submit"
                    className="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2.5 rounded-xl transition-all cursor-pointer"
                  >
                    Siarkan Sekarang
                  </button>
                </form>
              </div>

              {/* Graphical System Activity Log represented using elegant custom SVGs */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm lg:col-span-2 flex flex-col justify-between">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-900 mb-2">Aktivitas Server & Beban Akses</h3>
                  <span className="text-xs text-slate-400">Jumlah login per jam terakhir (Laju lalu lintas API)</span>
                </div>

                <div className="h-44 flex items-end justify-between px-4 pt-6 border-b border-slate-100 pb-2">
                  {[
                    { hr: '13:00', load: 85 },
                    { hr: '14:00', load: 120 },
                    { hr: '15:00', load: 150 },
                    { hr: '16:00', load: 90 },
                    { hr: '17:00', load: 110 },
                    { hr: '18:00', load: 180 },
                    { hr: '19:00', load: 240 },
                    { hr: '20:00', load: 160 }
                  ].map((pt, idx) => {
                    const pct = (pt.load / 240) * 100;
                    return (
                      <div key={idx} className="flex flex-col items-center flex-1 gap-1.5 group">
                        <span className="text-[8px] font-bold text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity font-mono">{pt.load} req</span>
                        <div className="w-6 bg-slate-100 group-hover:bg-emerald-600 rounded-t-md relative" style={{ height: `${pct}%`, minHeight: '6px' }}>
                          <div className="absolute inset-0 bg-gradient-to-t from-emerald-600/10 to-transparent" />
                        </div>
                        <span className="text-[9px] text-slate-400 font-mono font-medium">{pt.hr}</span>
                      </div>
                    );
                  })}
                </div>

                <div className="pt-3 text-[10px] text-slate-400 font-mono flex justify-between items-center">
                  <span>Sistem Backup Database: <strong>02:00 UTC (Ok)</strong></span>
                  <span>Port Ingress: <strong className="text-emerald-600">3000/Nginx</strong></span>
                </div>
              </div>

            </div>

            {/* Latest system logs */}
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
              <div className="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 className="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                  <Clock className="w-5 h-5 text-slate-500" />
                  Log Aktivitas Sistem Real-Time
                </h3>
                <span className="text-xs text-slate-400 font-mono">Sistem Keamanan & Verifikasi Log</span>
              </div>
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                    <th className="p-4 pl-6">Pelaku / Aktor</th>
                    <th className="p-4">Peran</th>
                    <th className="p-4">Rincian Tindakan (Action Log)</th>
                    <th className="p-4 text-center pr-6">Waktu Kejadian</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-xs font-mono text-slate-600">
                  {state.activityLogs.map(log => (
                    <tr key={log.id} className="hover:bg-slate-50/50">
                      <td className="p-4 pl-6 font-bold text-slate-950">{log.actor}</td>
                      <td className="p-4">
                        <span className={`inline-block px-2 py-0.5 rounded text-[9px] font-bold ${log.role === 'Lecturer' ? 'bg-emerald-100 text-emerald-800' : log.role === 'Student' ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-800'}`}>
                          {log.role}
                        </span>
                      </td>
                      <td className="p-4 font-sans text-slate-700">{log.action}</td>
                      <td className="p-4 text-center text-slate-400 pr-6">{log.timestamp}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

          </div>
        )}

        {/* TAB 2: USER MANAGEMENT */}
        {activeTab === 'users' && (
          <div className="space-y-6">
            
            {/* Filters panel search */}
            <div className="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
              <div className="flex flex-1 gap-3 w-full">
                <div className="relative flex-1">
                  <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                  <input 
                    type="text" 
                    placeholder="Cari user berdasarkan nama, NPM, NIDN, atau email..."
                    className="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl text-xs text-slate-800"
                    value={userSearch}
                    onChange={(e) => setUserSearch(e.target.value)}
                  />
                </div>
                
                <select 
                  className="border border-slate-200 focus:border-emerald-500 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-700 bg-transparent focus:outline-none"
                  value={userRoleFilter}
                  onChange={(e) => setUserRoleFilter(e.target.value)}
                >
                  <option value="all">Semua Peran (All Roles)</option>
                  <option value="lecturer">Dosen (Instructors)</option>
                  <option value="student">Mahasiswa (Students)</option>
                  <option value="admin">Administrator</option>
                </select>
              </div>

              <button 
                onClick={() => setShowAddUserModal(true)}
                className="w-full md:w-auto inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
              >
                <PlusCircle className="w-4 h-4" />
                Daftarkan User Baru
              </button>
            </div>

            {/* Users Account Table */}
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                    <th className="p-4 pl-6">NPM / NIDN / NIP</th>
                    <th className="p-4">Identitas Pengguna</th>
                    <th className="p-4">Email</th>
                    <th className="p-4">Departemen / Prodi</th>
                    <th className="p-4 text-center">Peran</th>
                    <th className="p-4 text-center">Status Akun</th>
                    <th className="p-4 text-center pr-6">Tindakan</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-xs">
                  {filteredUsers.map(user => {
                    const isSuspended = !!user.suspended;
                    return (
                      <tr key={user.id} className={`hover:bg-slate-50/50 transition-all ${isSuspended ? 'bg-red-50/25 opacity-85' : ''}`}>
                        <td className="p-4 pl-6 font-mono text-slate-500 font-medium">{user.identifier}</td>
                        <td className="p-4">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-[11px] uppercase border">
                              {user.name.charAt(0)}
                            </div>
                            <div>
                              <h5 className="font-bold text-slate-900 text-sm leading-none">{user.name}</h5>
                            </div>
                          </div>
                        </td>
                        <td className="p-4 text-slate-600 font-mono">{user.email}</td>
                        <td className="p-4 text-slate-600 font-sans">{user.department || 'Academic'}</td>
                        <td className="p-4 text-center">
                          <span className={`inline-block px-2.5 py-0.5 rounded text-[9px] font-bold uppercase ${user.role === 'lecturer' ? 'bg-emerald-100 text-emerald-800' : user.role === 'student' ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-800'}`}>
                            {user.role}
                          </span>
                        </td>
                        <td className="p-4 text-center">
                          <button
                            onClick={() => handleToggleSuspension(user.id)}
                            className={`inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${isSuspended ? 'bg-red-100 hover:bg-red-200 text-red-800 border border-red-200' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-200'}`}
                          >
                            {isSuspended ? (
                              <>
                                <ToggleLeft className="w-4 h-4 shrink-0 text-red-600" />
                                Suspended
                              </>
                            ) : (
                              <>
                                <ToggleRight className="w-4 h-4 shrink-0 text-emerald-600" />
                                Active Account
                              </>
                            )}
                          </button>
                        </td>
                        <td className="p-4 text-center pr-6">
                          <button
                            onClick={() => handleDeleteUser(user.id)}
                            disabled={user.id === state.currentUser?.id}
                            className={`p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed`}
                            title="Hapus Akun Permanen"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

            {/* New User Creator Modal */}
            {showAddUserModal && (
              <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                <div className="bg-white rounded-2xl w-full max-w-md p-6 border border-slate-200 shadow-2xl relative">
                  <button 
                    onClick={() => setShowAddUserModal(false)}
                    className="absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                  >
                    <X className="w-5 h-5" />
                  </button>
                  
                  <h3 className="font-display font-bold text-lg text-slate-900 mb-4">Pendaftaran User Baru</h3>
                  <form onSubmit={handleCreateUser} className="space-y-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">NAMA LENGKAP</label>
                      <input 
                        type="text" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800" 
                        placeholder="Contoh: Marcus Aurelius"
                        value={newUserName}
                        onChange={(e) => setNewUserName(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">EMAIL PENGGUNA</label>
                      <input 
                        type="email" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800 font-mono" 
                        placeholder="Contoh: marcus@student.eco.ac.id"
                        value={newUserEmail}
                        onChange={(e) => setNewUserEmail(e.target.value)}
                      />
                    </div>
                    
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">PERAN (ROLE)</label>
                        <select 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-700 bg-transparent"
                          value={newUserRole}
                          onChange={(e) => setNewUserRole(e.target.value as any)}
                        >
                          <option value="student">Mahasiswa</option>
                          <option value="lecturer">Dosen / Pengajar</option>
                          <option value="admin">Administrator</option>
                        </select>
                      </div>
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">NPM / NIDN / NIP</label>
                        <input 
                          type="text" 
                          required
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800 font-mono" 
                          placeholder="Kode Identifikasi..."
                          value={newUserIdentifier}
                          onChange={(e) => setNewUserIdentifier(e.target.value)}
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">PRODI / DEPARTEMEN</label>
                      <input 
                        type="text" 
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800" 
                        placeholder="Contoh: Environmental Science"
                        value={newUserDepartment}
                        onChange={(e) => setNewUserDepartment(e.target.value)}
                      />
                    </div>
                    
                    <button 
                      type="submit" 
                      className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition-all mt-4 cursor-pointer"
                    >
                      Daftarkan Pengguna
                    </button>
                  </form>
                </div>
              </div>
            )}
          </div>
        )}

        {/* TAB 3: CLASS MANAGEMENT */}
        {activeTab === 'classes' && (
          <div className="space-y-6">
            
            {/* Filters panel search */}
            <div className="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
              <div className="relative flex-1 w-full">
                <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                <input 
                  type="text" 
                  placeholder="Cari kelas berdasarkan nama, kode, atau nama dosen..."
                  className="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl text-xs text-slate-800"
                  value={classSearch}
                  onChange={(e) => setClassSearch(e.target.value)}
                />
              </div>

              <button 
                onClick={() => setShowAddClassModal(true)}
                className="w-full md:w-auto inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer whitespace-nowrap"
              >
                <PlusCircle className="w-4 h-4" />
                Tambah Kelas Baru
              </button>
            </div>

            {/* Classes Master Table */}
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                    <th className="p-4 pl-6">Kode Kelas</th>
                    <th className="p-4">Nama Mata Kuliah</th>
                    <th className="p-4 text-center">SKS</th>
                    <th className="p-4">Dosen Pengampu</th>
                    <th className="p-4">Jadwal / Hari</th>
                    <th className="p-4">Ruangan</th>
                    <th className="p-4 text-center">Mahasiswa</th>
                    <th className="p-4 text-center pr-6">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-xs text-slate-700">
                  {state.courses
                    .filter(c => 
                      c.name.toLowerCase().includes(classSearch.toLowerCase()) ||
                      c.code.toLowerCase().includes(classSearch.toLowerCase()) ||
                      c.lecturerName.toLowerCase().includes(classSearch.toLowerCase())
                    )
                    .map(course => (
                      <tr key={course.id} className="hover:bg-slate-50/50 transition-all">
                        <td className="p-4 pl-6 font-mono text-emerald-600 font-bold">{course.code}</td>
                        <td className="p-4 font-bold text-slate-900">{course.name}</td>
                        <td className="p-4 text-center font-bold font-mono text-slate-700">{course.sks} SKS</td>
                        <td className="p-4 text-slate-700 font-medium">{course.lecturerName}</td>
                        <td className="p-4 text-slate-600 font-mono">{course.scheduleDay}, {course.scheduleTime}</td>
                        <td className="p-4 text-slate-600">{course.classRoom}</td>
                        <td className="p-4 text-center font-mono font-semibold text-slate-800">{course.studentsCount} Mhs</td>
                        <td className="p-4 text-center pr-6">
                          <button
                            onClick={() => handleDeleteCourse(course.id, course.name)}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-100 rounded-lg text-xs font-bold transition-all cursor-pointer"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                            Hapus
                          </button>
                        </td>
                      </tr>
                    ))}
                  {state.courses.filter(c => 
                    c.name.toLowerCase().includes(classSearch.toLowerCase()) ||
                    c.code.toLowerCase().includes(classSearch.toLowerCase()) ||
                    c.lecturerName.toLowerCase().includes(classSearch.toLowerCase())
                  ).length === 0 && (
                    <tr>
                      <td colSpan={8} className="text-center py-8 text-slate-400 italic">
                        Tidak ada kelas yang cocok dengan pencarian Anda.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* New Class Creator Modal */}
            {showAddClassModal && (
              <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                <div className="bg-white rounded-2xl w-full max-w-md p-6 border border-slate-200 shadow-2xl relative animate-fade-in">
                  <button 
                    onClick={() => setShowAddClassModal(false)}
                    className="absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                  >
                    <X className="w-5 h-5" />
                  </button>
                  
                  <h3 className="font-display font-bold text-lg text-slate-900 mb-4">Pendaftaran Kelas & Mata Kuliah Baru</h3>
                  <form onSubmit={handleCreateCourse} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">KODE MK</label>
                        <input 
                          type="text" 
                          required
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800 font-mono" 
                          placeholder="Contoh: ECO-301"
                          value={newClassCode}
                          onChange={(e) => setNewClassCode(e.target.value)}
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">BOBOT SKS</label>
                        <select 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-700 bg-white"
                          value={newClassSks}
                          onChange={(e) => setNewClassSks(Number(e.target.value))}
                        >
                          <option value={1}>1 SKS</option>
                          <option value={2}>2 SKS</option>
                          <option value={3}>3 SKS</option>
                          <option value={4}>4 SKS</option>
                          <option value={6}>6 SKS</option>
                        </select>
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">NAMA MATA KULIAH / KELAS</label>
                      <input 
                        type="text" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800" 
                        placeholder="Contoh: Energi Terbarukan & Fotovoltaik"
                        value={newClassName}
                        onChange={(e) => setNewClassName(e.target.value)}
                      />
                    </div>

                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">DOSEN PENGAMPU</label>
                      <select 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-700 bg-white"
                        value={newClassLecturer}
                        onChange={(e) => setNewClassLecturer(e.target.value)}
                      >
                        <option value="">Pilih Dosen...</option>
                        {state.users.filter(u => u.role === 'lecturer').map(u => (
                          <option key={u.id} value={u.name}>{u.name} ({u.identifier})</option>
                        ))}
                      </select>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">RUANG KELAS</label>
                        <input 
                          type="text" 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800" 
                          placeholder="Contoh: Lab Arsitektur 2A"
                          value={newClassRoom}
                          onChange={(e) => setNewClassRoom(e.target.value)}
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">HARI JADWAL</label>
                        <select 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-700 bg-white"
                          value={newClassDay}
                          onChange={(e) => setNewClassDay(e.target.value)}
                        >
                          <option value="Senin">Senin</option>
                          <option value="Selasa">Selasa</option>
                          <option value="Rabu">Rabu</option>
                          <option value="Kamis">Kamis</option>
                          <option value="Jumat">Jumat</option>
                        </select>
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">WAKTU JADWAL</label>
                      <input 
                        type="text" 
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs text-slate-800 font-mono" 
                        placeholder="Contoh: 13:00 - 15:30"
                        value={newClassTime}
                        onChange={(e) => setNewClassTime(e.target.value)}
                      />
                    </div>
                    
                    <button 
                      type="submit" 
                      className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition-all mt-4 cursor-pointer"
                    >
                      Daftarkan Kelas Baru
                    </button>
                  </form>
                </div>
              </div>
            )}
          </div>
        )}

        {/* TAB 4: PROFILE MANAGEMENT */}
        {activeTab === 'profile' && (
          <div className="space-y-6">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Form card details */}
              <div className="bg-white border border-slate-200 rounded-2xl p-6 lg:col-span-2 shadow-sm">
                <h3 className="font-display font-bold text-base text-slate-900 mb-6">Ubah Profil Administrator</h3>
                
                <form onSubmit={handleSaveProfile} className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">NAMA LENGKAP</label>
                      <input 
                        type="text" 
                        required
                        className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800"
                        value={profileName}
                        onChange={(e) => setProfileName(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">EMAIL INSTITUSI</label>
                      <input 
                        type="email" 
                        required
                        className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800"
                        value={profileEmail}
                        onChange={(e) => setProfileEmail(e.target.value)}
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">NOMOR HP</label>
                      <input 
                        type="text" 
                        className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800 font-mono"
                        value={profilePhone}
                        onChange={(e) => setProfilePhone(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">DIVISI / UNIT</label>
                      <input 
                        type="text" 
                        required
                        className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800"
                        value={profileDept}
                        onChange={(e) => setProfileDept(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">NIP ADMINISTRATOR</label>
                      <input 
                        type="text" 
                        required
                        className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800 font-mono"
                        value={profileIdentifier}
                        onChange={(e) => setProfileIdentifier(e.target.value)}
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">TAUTAN FOTO PROFIL (AVATAR URL)</label>
                    <input 
                      type="url" 
                      required
                      className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800 font-mono"
                      value={profileAvatarUrl}
                      onChange={(e) => setProfileAvatarUrl(e.target.value)}
                    />
                    <p className="text-[10px] text-slate-400 mt-1 font-mono">
                      Gunakan tautan Unsplash atau tautan gambar valid lainnya untuk memperbarui foto profil.
                    </p>
                  </div>

                  <div className="pt-4 flex justify-end">
                    <button 
                      type="submit"
                      className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-1.5"
                    >
                      <Check className="w-4 h-4" /> Simpan Perubahan Profil
                    </button>
                  </div>
                </form>
              </div>

              {/* Security info card stats */}
              <div className="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between shadow-sm">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-900 mb-4 font-sans">Level Hak Akses</h3>
                  <div className="space-y-4">
                    <div className="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                      <span className="text-slate-400 text-[10px] font-mono font-bold block uppercase">Peran Sistem</span>
                      <strong className="text-lg text-emerald-600 font-sans block mt-1">Super Administrator</strong>
                    </div>
                    <div className="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                      <span className="text-slate-400 text-[10px] font-mono font-bold block uppercase">Otoritas Keputusan</span>
                      <strong className="text-sm text-slate-800 font-sans block mt-1">Sertifikasi & Kurikulum Global</strong>
                    </div>
                    <div className="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                      <span className="text-slate-400 text-[10px] font-mono font-bold block uppercase">Tingkat Enkripsi Log</span>
                      <strong className="text-sm text-slate-800 font-sans block mt-1 font-mono">AES-256 Verified</strong>
                    </div>
                  </div>
                </div>

                <div className="p-4 bg-amber-50 border border-amber-100 rounded-xl text-[10px] font-mono text-amber-800 leading-relaxed mt-6">
                  ⚠️ <strong>PENTING:</strong> Segala perubahan data profil admin akan direkam langsung dalam log aktivitas keamanan sistem dan diverifikasi oleh LPTIK.
                </div>
              </div>

            </div>
          </div>
        )}

      </main>
    </div>
  );
}
