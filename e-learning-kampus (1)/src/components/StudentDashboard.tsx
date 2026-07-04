import React, { useState } from 'react';
import { 
  LayoutDashboard, Calendar, Video, User, Award, Flame, 
  BookOpen, Clock, AlertTriangle, ArrowRight, Download, 
  ExternalLink, Upload, CheckCircle2, MessageSquare, Save, 
  TrendingUp, Compass, LogOut, Check, X, HelpCircle, ChevronRight
} from 'lucide-react';
import { EcoAppState, Submission, Course } from '../types';

interface StudentDashboardProps {
  state: EcoAppState;
  setState: React.Dispatch<React.SetStateAction<EcoAppState>>;
  onLogout: () => void;
}

export default function StudentDashboard({ state, setState, onLogout }: StudentDashboardProps) {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'schedule' | 'session' | 'profile' | 'results'>('dashboard');
  
  // Profile settings state
  const [profileName, setProfileName] = useState(state.currentUser?.name || 'Alex Rivera');
  const [profileEmail, setProfileEmail] = useState(state.currentUser?.email || 'alex.rivera@student.eco.ac.id');
  const [profilePhone, setProfilePhone] = useState(state.currentUser?.phone || '+62 821-9876-5432');
  const [profileMajor, setProfileMajor] = useState(state.currentUser?.department || 'Environmental Science');
  const [profileSemester, setProfileSemester] = useState(state.currentUser?.semester || 4);

  // Video session interactive state
  const [isVideoPlaying, setIsVideoPlaying] = useState(false);
  const [commentText, setCommentText] = useState('');
  
  // KRS Class Search
  const [krsSearch, setKrsSearch] = useState('');

  const currentStudentId = state.currentUser?.id || 's-1';
  const enrolledCourseIds = state.currentUser?.enrolledCourseIds || ['c-1', 'c-2', 'c-3'];
  const enrolledCourses = state.courses.filter(c => enrolledCourseIds.includes(c.id));

  // Course & Session dynamic selection
  const [selectedCourseId, setSelectedCourseId] = useState<string>(enrolledCourses[0]?.id || 'c-1');
  const [selectedSessionNum, setSelectedSessionNum] = useState<number>(1);
  const [studentFileName, setStudentFileName] = useState('');

  const activeCourse = state.courses.find(c => c.id === selectedCourseId) || enrolledCourses[0] || state.courses[0];
  
  // Find materials for this course and session
  const courseMaterials = state.materials.filter(m => m.courseId === selectedCourseId);
  const rawActiveMaterial = courseMaterials.find(m => m.sessionNumber === selectedSessionNum);
  const activeMaterial = rawActiveMaterial || {
    id: `temp-m-${selectedCourseId}-${selectedSessionNum}`,
    courseId: selectedCourseId,
    sessionNumber: selectedSessionNum,
    title: `Modul Pembelajaran Sesi ${selectedSessionNum}`,
    description: `Materi pembelajaran mendalam Sesi ${selectedSessionNum} untuk mata kuliah ${activeCourse.name}. Pelajari konsep inti melalui referensi yang dibagikan.`,
    fileName: `${activeCourse.code}_Sesi${selectedSessionNum}_Materi.pdf`,
    fileSize: '3.5 MB',
    videoUrl: 'https://www.w3schools.com/html/mov_bbb.mp4',
    externalLink: 'https://www.iucn.org'
  };

  // Find assignment for this course and session
  const courseAssignments = state.assignments.filter(a => a.courseId === selectedCourseId);
  const rawActiveAssignment = courseAssignments.find(a => a.sessionNumber === selectedSessionNum);
  const activeAssignment = rawActiveAssignment || {
    id: `temp-asg-${selectedCourseId}-${selectedSessionNum}`,
    courseId: selectedCourseId,
    sessionNumber: selectedSessionNum,
    title: `Tugas Laporan Mandiri Sesi ${selectedSessionNum}`,
    instructions: `Lakukan analisis tinjauan pustaka dan buat resume ringkas sebanyak 2-3 halaman mengenai topik Sesi ${selectedSessionNum} untuk mata kuliah ${activeCourse.name}. Unggah dalam format PDF.`,
    dueDate: '2026-07-20',
    allowedFormats: ['PDF', 'DOCX'],
    maxScore: 100,
    isPublished: true
  };

  const activeSubmission = state.submissions.find(s => s.assignmentId === activeAssignment.id && s.studentId === currentStudentId);

  const handlePostComment = (e: React.FormEvent) => {
    e.preventDefault();
    if (!commentText.trim()) return;

    setState(prev => {
      const updatedSubmissions = prev.submissions.map(sub => {
        if (sub.assignmentId === activeAssignment.id && sub.studentId === currentStudentId) {
          return {
            ...sub,
            comments: [
              ...sub.comments,
              {
                authorName: prev.currentUser?.name || 'Alex Rivera',
                text: commentText,
                timestamp: new Date().toLocaleTimeString().substring(0, 5)
              }
            ]
          };
        }
        return sub;
      });
      return { ...prev, submissions: updatedSubmissions };
    });

    setCommentText('');
  };

  const handleUploadSubmission = (e: React.FormEvent) => {
    e.preventDefault();
    if (!studentFileName.trim()) return;

    const formattedName = studentFileName.toLowerCase().endsWith('.pdf') || studentFileName.toLowerCase().endsWith('.docx')
      ? studentFileName 
      : `${studentFileName}.pdf`;

    const newSub: Submission = {
      id: `sub-${Date.now()}`,
      assignmentId: activeAssignment.id,
      studentId: currentStudentId,
      studentName: state.currentUser?.name || 'Alex Rivera',
      submittedAt: new Date().toISOString().replace('T', ' ').substring(0, 16),
      fileName: formattedName,
      fileSize: '2.8 MB',
      status: 'submitted',
      comments: []
    };

    setState(prev => {
      // Remove any existing submission for this assignment/student to support re-submission
      const filtered = prev.submissions.filter(s => !(s.assignmentId === activeAssignment.id && s.studentId === currentStudentId));
      
      const newLog = {
        id: `log-${Date.now()}`,
        actor: prev.currentUser?.name || 'Alex Rivera',
        role: 'Student',
        action: `Mengunggah tugas "${newSub.fileName}" untuk kelas ${activeCourse.name} Sesi ${selectedSessionNum}`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        submissions: [...filtered, newSub],
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });

    setStudentFileName('');
    alert('Tugas Anda berhasil diunggah dan disimpan ke database akademik!');
  };

  const handleEnrollCourse = (courseId: string) => {
    setState(prev => {
      if (!prev.currentUser) return prev;
      
      const currentEnrolled = prev.currentUser.enrolledCourseIds || [];
      if (currentEnrolled.includes(courseId)) return prev;

      const newEnrolled = [...currentEnrolled, courseId];
      const updatedUser = {
        ...prev.currentUser,
        enrolledCourseIds: newEnrolled
      };

      const updatedUsers = prev.users.map(u => {
        if (u.id === prev.currentUser?.id) {
          return updatedUser;
        }
        return u;
      });

      const targetCourse = prev.courses.find(c => c.id === courseId);
      const updatedCourses = prev.courses.map(c => {
        if (c.id === courseId) {
          return {
            ...c,
            studentsCount: c.studentsCount + 1
          };
        }
        return c;
      });

      const newLog = {
        id: `log-${Date.now()}`,
        actor: prev.currentUser.name,
        role: 'Student',
        action: `Mengontrak (mengambil) kelas kuliah: "${targetCourse?.name || ''}"`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        currentUser: updatedUser,
        users: updatedUsers,
        courses: updatedCourses,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Kelas berhasil dikontrak! Jadwal kuliah dan materi telah disinkronkan.');
  };

  const handleDropCourse = (courseId: string) => {
    if (!confirm('Apakah Anda yakin ingin membatalkan (drop) kelas kuliah ini?')) return;
    
    setState(prev => {
      if (!prev.currentUser) return prev;
      
      const currentEnrolled = prev.currentUser.enrolledCourseIds || [];
      const newEnrolled = currentEnrolled.filter(id => id !== courseId);
      const updatedUser = {
        ...prev.currentUser,
        enrolledCourseIds: newEnrolled
      };

      const updatedUsers = prev.users.map(u => {
        if (u.id === prev.currentUser?.id) {
          return updatedUser;
        }
        return u;
      });

      const targetCourse = prev.courses.find(c => c.id === courseId);
      const updatedCourses = prev.courses.map(c => {
        if (c.id === courseId && c.studentsCount > 0) {
          return {
            ...c,
            studentsCount: c.studentsCount - 1
          };
        }
        return c;
      });

      const newLog = {
        id: `log-${Date.now()}`,
        actor: prev.currentUser.name,
        role: 'Student',
        action: `Membatalkan (drop) kelas kuliah: "${targetCourse?.name || ''}"`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        currentUser: updatedUser,
        users: updatedUsers,
        courses: updatedCourses,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Kelas berhasil dibatalkan dari KRS digital Anda.');
  };

  const handleSaveProfile = (e: React.FormEvent) => {
    e.preventDefault();
    setState(prev => {
      const updatedUser = prev.currentUser ? {
        ...prev.currentUser,
        name: profileName,
        email: profileEmail,
        phone: profilePhone,
        department: profileMajor,
        semester: Number(profileSemester)
      } : null;

      // Also update the user in the global users array so the login system recognizes the updated details
      const updatedUsers = prev.users.map(u => {
        if (u.id === prev.currentUser?.id) {
          return {
            ...u,
            name: profileName,
            email: profileEmail,
            phone: profilePhone,
            department: profileMajor,
            semester: Number(profileSemester)
          };
        }
        return u;
      });

      const newLog = {
        id: `log-${Date.now()}`,
        actor: profileName,
        role: 'Student',
        action: `Memperbarui profil diri dan data identitas pribadi`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        currentUser: updatedUser,
        users: updatedUsers,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Informasi profil Anda berhasil diperbarui secara lokal!');
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-800 flex font-sans">
      
      {/* SIDE NAV - Clean Modern Light Theme */}
      <aside className="w-64 bg-white text-slate-700 flex flex-col shrink-0 border-r border-slate-200">
        
        {/* Brand Header */}
        <div className="p-6 border-b border-slate-100 flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h1 className="font-display font-bold text-base leading-tight tracking-wide text-slate-900">ECO-LEARNING</h1>
            <span className="text-[10px] text-emerald-600 font-mono tracking-widest font-semibold uppercase">MAHASISWA PORTAL</span>
          </div>
        </div>

        {/* Student Mini Profile Card */}
        <div className="p-6 flex items-center gap-3 border-b border-slate-100">
          <img 
            className="w-10 h-10 rounded-full object-cover border-2 border-emerald-500" 
            src={state.currentUser?.avatarUrl} 
            alt="Alex Rivera"
            referrerPolicy="no-referrer"
          />
          <div>
            <h4 className="text-xs font-semibold leading-none text-slate-850">{profileName}</h4>
            <span className="text-[10px] text-slate-500 font-mono mt-1 block">NPM {state.currentUser?.identifier}</span>
          </div>
        </div>

        {/* Links Navigation */}
        <nav className="flex-1 p-4 space-y-1 overflow-y-auto max-h-[calc(100vh-250px)]">
          <button 
            onClick={() => setActiveTab('dashboard')}
            className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors ${activeTab === 'dashboard' ? 'bg-emerald-50 text-emerald-700 shadow-sm border-l-4 border-emerald-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}
          >
            <LayoutDashboard className="w-4 h-4 shrink-0" />
            Dashboard
          </button>
          <button 
            onClick={() => setActiveTab('schedule')}
            className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors ${activeTab === 'schedule' ? 'bg-emerald-50 text-emerald-700 shadow-sm border-l-4 border-emerald-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}
          >
            <Calendar className="w-4 h-4 shrink-0" />
            Rencana KRS & Jadwal
          </button>
          <button 
            onClick={() => setActiveTab('session')}
            className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors ${activeTab === 'session' ? 'bg-emerald-50 text-emerald-700 shadow-sm border-l-4 border-emerald-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}
          >
            <Video className="w-4 h-4 shrink-0" />
            Sesi Detail Kelas
          </button>
          <button 
            onClick={() => setActiveTab('results')}
            className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors ${activeTab === 'results' ? 'bg-emerald-50 text-emerald-700 shadow-sm border-l-4 border-emerald-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}
          >
            <Award className="w-4 h-4 shrink-0" />
            Hasil Studi (KHS)
          </button>
          <button 
            onClick={() => setActiveTab('profile')}
            className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors ${activeTab === 'profile' ? 'bg-emerald-50 text-emerald-700 shadow-sm border-l-4 border-emerald-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}
          >
            <User className="w-4 h-4 shrink-0" />
            Profil Saya
          </button>

          {/* Dynamic Class Selection List */}
          <div className="pt-4 mt-4 border-t border-slate-100">
            <span className="text-[10px] text-slate-400 font-mono font-bold tracking-wider block px-2 mb-2 uppercase">KELAS SAYA ({enrolledCourses.length})</span>
            <div className="space-y-1">
              {enrolledCourses.map(c => {
                const isSelected = selectedCourseId === c.id;
                return (
                  <button
                    key={c.id}
                    onClick={() => {
                      setSelectedCourseId(c.id);
                      setActiveTab('session');
                    }}
                    className={`w-full text-left px-3 py-2 rounded-xl transition-all flex flex-col gap-0.5 border ${isSelected ? 'bg-emerald-600 border-emerald-500 text-white shadow-sm' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900'}`}
                  >
                    <div className="flex justify-between items-center w-full">
                      <span className={`text-[8px] font-mono font-bold px-1 py-0.2 rounded ${isSelected ? 'bg-emerald-750 text-white' : 'bg-slate-100 text-slate-600'}`}>
                        {c.code}
                      </span>
                      <span className={`text-[8px] font-mono ${isSelected ? 'text-emerald-200' : 'text-slate-450'}`}>
                        {c.sks} SKS
                      </span>
                    </div>
                    <span className="text-[11px] font-bold truncate block w-full">
                      {c.name}
                    </span>
                  </button>
                );
              })}
            </div>
          </div>
        </nav>

        {/* Footer Logout */}
        <div className="p-4 border-t border-slate-100">
          <button 
            onClick={onLogout}
            className="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs text-red-600 hover:bg-red-50 transition-colors font-medium text-left"
          >
            <LogOut className="w-4 h-4" />
            Keluar Sistem
          </button>
          <div className="mt-4 text-center text-[9px] text-slate-400 font-mono">
            Eco-Learning Mahasiswa v1.2
          </div>
        </div>
      </aside>

      {/* MAIN SCREEN PANEL */}
      <main className="flex-1 overflow-y-auto p-8 flex flex-col bg-slate-50">
        
        {/* Header bar */}
        <header className="flex justify-between items-center mb-8 pb-4 border-b border-slate-200">
          <div>
            <span className="text-[10px] text-emerald-600 font-bold uppercase tracking-wider font-mono">Eco-Learning Academic Environment</span>
            <h2 className="text-2xl font-display font-bold text-slate-800 mt-1">
              {activeTab === 'dashboard' && "Dasbor Belajar Mandiri"}
              {activeTab === 'schedule' && "Rencana Studi Digital (KRS) & Jadwal Kuliah"}
              {activeTab === 'session' && "Sesi Interaktif Pembelajaran Lingkungan"}
              {activeTab === 'profile' && "Pusat Profil & Data Pengenal Mahasiswa"}
              {activeTab === 'results' && "Transkrip Nilai Akademik & Evaluasi KHS"}
            </h2>
          </div>

          <div className="flex items-center gap-4">
            {/* Streak Counter widget */}
            <div className="flex items-center gap-2 bg-amber-50 border border-amber-200 px-4 py-2 rounded-xl">
              <Flame className="w-4 h-4 text-orange-500 animate-bounce" />
              <span className="text-xs font-mono font-bold text-amber-800">
                STREAK: {state.streakDays} HARI BELAJAR!
              </span>
            </div>

            <div className="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-bold text-xs border border-slate-300">
              AL
            </div>
          </div>
        </header>

        {/* TAB 1: STUDENT DASHBOARD */}
        {activeTab === 'dashboard' && (
          <div className="space-y-6">
            
            {/* Top welcome banner with dynamic gradient layout */}
            <div className="bg-gradient-to-r from-emerald-600 to-teal-700 border border-emerald-500/30 rounded-2xl p-6 shadow-md relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div className="relative z-10 max-w-xl">
                <span className="px-2.5 py-0.5 rounded bg-white/20 border border-white/30 text-white text-[10px] font-bold tracking-wider font-mono">
                  LOG MASUK BERHASIL
                </span>
                <h3 className="text-2xl font-display font-bold text-white mt-3">Welcome back, {profileName}!</h3>
                <p className="text-emerald-50 text-xs mt-2 leading-relaxed">
                  Semoga hari Anda menyenangkan. Anda memiliki <strong className="text-white">2 penugasan mandiri</strong> yang mendekati tenggat waktu akhir minggu ini. Mari asah kompetensi dan pertahankan indeks prestasi akademik Anda!
                </p>
              </div>
            </div>

            {/* Dashboard items: Funnel stages and schedules */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Learning Funnel Academic Progress */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl lg:col-span-2 space-y-4 shadow-sm">
                <h4 className="font-display font-bold text-sm text-slate-800">Laju Pembelajaran Sesi Aktif (Learning Steps)</h4>
                
                {/* Horizontal Progress Flow indicators */}
                <div className="grid grid-cols-4 gap-2 pt-2">
                  {[
                    { title: 'Sesi 1: Ecosystems', status: 'completed' },
                    { title: 'Sesi 2: Permaculture', status: 'completed' },
                    { title: 'Sesi 3: Solar Tech', status: 'completed' },
                    { title: 'Sesi 4: Biodiversity', status: 'active' }
                  ].map((step, idx) => (
                    <div 
                      key={idx} 
                      onClick={() => idx === 3 && setActiveTab('session')}
                      className={`p-3 rounded-xl border transition-all text-center cursor-pointer ${step.status === 'completed' ? 'bg-emerald-50 border-emerald-200' : 'bg-emerald-600 border-emerald-500 text-white shadow-sm'}`}
                    >
                      <span className={`block text-[9px] font-bold font-mono mb-1 ${step.status === 'completed' ? 'text-slate-400' : 'text-emerald-100'}`}>LANGKAH {idx+1}</span>
                      <h5 className={`font-semibold text-xs truncate ${step.status === 'completed' ? 'text-slate-700' : 'text-white'}`}>{step.title}</h5>
                      <span className={`text-[9px] mt-2 block font-mono font-semibold ${step.status === 'completed' ? 'text-emerald-600' : 'text-white'}`}>
                        {step.status === 'completed' ? '✓ SELESAI' : '• SEDANG BELAJAR'}
                      </span>
                    </div>
                  ))}
                </div>

                {/* Main ecological course highlight */}
                <div className="bg-emerald-50/50 border border-emerald-100 p-5 rounded-2xl flex justify-between items-center mt-4">
                  <div>
                    <span className="text-[9px] font-bold text-emerald-700 uppercase tracking-widest font-mono">Bahan Kajian Minggu Ini</span>
                    <h5 className="font-bold text-base text-slate-800 mt-1">Biodiversitas Hutan Tropis & Ekologi Urban</h5>
                    <p className="text-xs text-slate-600 mt-2 max-w-md">
                      Materi mencakup keanekaragaman hayati, metode restorasi terumbu karang, dan analisis sirkulasi angin makro perkotaan.
                    </p>
                    <button 
                      onClick={() => setActiveTab('session')}
                      className="mt-4 inline-flex items-center gap-1.5 text-xs text-emerald-600 font-bold hover:underline"
                    >
                      Masuk ke Ruang Sesi <ArrowRight className="w-3.5 h-3.5" />
                    </button>
                  </div>
                  
                  <div className="hidden sm:block w-20 h-20 bg-white rounded-2xl border border-slate-200 flex items-center justify-center font-bold text-emerald-600 font-mono text-lg shadow-sm">
                    85%
                  </div>
                </div>
              </div>

              {/* Deadlines sidebar */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl flex flex-col justify-between shadow-sm">
                <div>
                  <h4 className="font-display font-bold text-sm text-slate-800 mb-4">Tenggat Waktu Mendesak</h4>
                  <div className="space-y-3">
                    <div className="p-3 bg-red-50 border border-red-100 rounded-xl">
                      <div className="flex justify-between items-start">
                        <span className="text-xs font-bold text-red-950">Analisis Biodiversitas</span>
                        <span className="text-[9px] bg-red-200 text-red-800 font-mono font-bold px-1.5 py-0.5 rounded">3 Hari Lagi</span>
                      </div>
                      <span className="text-[10px] text-slate-500 block mt-1">SOL-410 • Intro to Solar Tech</span>
                    </div>

                    <div className="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                      <div className="flex justify-between items-start">
                        <span className="text-xs font-bold text-amber-950">Kuis Struktur Tanah</span>
                        <span className="text-[9px] bg-amber-200 text-amber-800 font-mono font-bold px-1.5 py-0.5 rounded">6 Hari Lagi</span>
                      </div>
                      <span className="text-[10px] text-slate-500 block mt-1">PERM-101 • Permaculture</span>
                    </div>
                  </div>
                </div>

                <div className="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-700 mt-4 font-medium">
                  🔥 Streak belajar Anda saat ini sangat bagus! Jaga performa pengumpulan tugas agar indeks prestasi tetap tinggi.
                </div>
              </div>

            </div>

            {/* Academic active tracks list */}
            <div className="space-y-4">
              <h4 className="font-display font-bold text-sm text-slate-800">Mata Kuliah Aktif Anda Semester Ini</h4>
              <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                {/* GPA Display Block Card */}
                <div className="bg-gradient-to-br from-teal-800 to-emerald-900 border border-teal-700 rounded-2xl p-6 flex flex-col justify-between shadow-sm">
                  <div>
                    <span className="text-emerald-100 text-xs font-semibold">Indeks Prestasi Kumulatif</span>
                    <h5 className="text-4xl font-display font-bold text-white mt-2 font-mono">3.88</h5>
                  </div>
                  <div className="mt-4 pt-4 border-t border-emerald-800">
                    <span className="text-[10px] text-emerald-300 font-mono font-bold uppercase tracking-wider flex items-center gap-1">
                      <TrendingUp className="w-3.5 h-3.5" /> Predikat: Dengan Pujian
                    </span>
                  </div>
                </div>

                {enrolledCourses.slice(0, 3).map(c => (
                  <div key={c.id} className="bg-white border border-slate-200 rounded-2xl p-5 hover:border-emerald-300 transition-all flex flex-col justify-between shadow-sm">
                    <div>
                      <div className="flex justify-between items-start mb-3">
                        <span className="text-[9px] font-mono bg-slate-100 border border-slate-200 text-slate-600 px-1.5 py-0.5 rounded">
                          {c.code}
                        </span>
                        <span className="text-[10px] text-slate-500 font-mono">{c.sks} SKS</span>
                      </div>
                      <h5 className="font-bold text-sm text-slate-800">{c.name}</h5>
                      <span className="text-xs text-slate-500 block mt-1">{c.lecturerName}</span>
                    </div>
                    
                    <div className="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-[10px] font-mono text-slate-500">
                      <span>Progres Silabus</span>
                      <strong className="text-emerald-600">{c.progress}%</strong>
                    </div>
                  </div>
                ))}

              </div>
            </div>

          </div>
        )}

        {/* TAB 2: ACADEMIC SCHEDULE & KRS */}
        {activeTab === 'schedule' && (
          <div className="space-y-6">
            
            {/* KRS Stats Row */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm">
                <span className="text-slate-500 text-xs font-medium">Beban SKS Diambil</span>
                <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">{enrolledCourses.reduce((sum, c) => sum + c.sks, 0)} / 24</h4>
                <p className="text-[10px] text-emerald-600 mt-2 font-mono">Kontrak Belajar SKS Maksimum</p>
              </div>
              <div className="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm">
                <span className="text-slate-500 text-xs font-medium">Total Kursus Aktif</span>
                <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">{enrolledCourses.length < 10 ? `0${enrolledCourses.length}` : enrolledCourses.length}</h4>
                <p className="text-[10px] text-slate-500 mt-2 font-mono">Kelas Teori & Praktikum</p>
              </div>
              <div className="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm">
                <span className="text-slate-500 text-xs font-medium">Indeks Prestasi IPS</span>
                <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">3.88</h4>
                <p className="text-[10px] text-emerald-600 mt-2 font-mono">Evaluasi Semester Lalu</p>
              </div>
              <div className="bg-white p-5 border border-slate-200 rounded-2xl shadow-sm">
                <span className="text-slate-500 text-xs font-medium">Sisa SKS Kelulusan</span>
                <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">62 SKS</h4>
                <p className="text-[10px] text-slate-500 mt-2 font-mono">Target SKS Total: 144 SKS</p>
              </div>
            </div>

            {/* Weekly Timetable Calendar grid representation */}
            <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
              <h3 className="font-display font-bold text-base text-slate-800 mb-6">Weekly Timetable (Jadwal Mingguan)</h3>
              
              <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                {['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'].map(day => (
                  <div key={day} className="space-y-3">
                    <span className="block text-center text-xs font-bold font-mono py-1.5 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg">
                      {day}
                    </span>
                    <div className="space-y-2 min-h-60 bg-slate-50 border border-slate-200 rounded-xl p-2">
                      {enrolledCourses.filter(c => c.scheduleDay === day).length === 0 ? (
                        <span className="block text-center text-[10px] text-slate-400 italic py-8">Tidak ada kelas</span>
                      ) : (
                        enrolledCourses.filter(c => c.scheduleDay === day).map(c => (
                          <div key={c.id} className="p-2.5 bg-white border border-slate-200 hover:border-emerald-300 rounded-lg text-left transition-all shadow-sm">
                            <span className="text-[8px] font-bold font-mono bg-emerald-50 px-1 py-0.5 rounded text-emerald-700 block w-max mb-1">
                              {c.code}
                            </span>
                            <h5 className="font-bold text-[10px] text-slate-800 line-clamp-2 leading-tight">{c.name}</h5>
                            <span className="text-[8px] text-slate-500 block font-mono mt-1">{c.scheduleTime}</span>
                            <span className="text-[8px] text-slate-400 block truncate mt-0.5">{c.classRoom}</span>
                          </div>
                        ))
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Digital KRS Contract List */}
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
              <div className="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-800">Kartu Rencana Studi Digital (Digital KRS)</h3>
                  <p className="text-xs text-slate-500 mt-1">Gunakan tabel ini untuk menambah atau membatalkan pilihan mata kuliah Anda secara mandiri.</p>
                </div>
                <div className="flex items-center gap-3 w-full sm:w-auto">
                  <input 
                    type="text" 
                    placeholder="Cari mata kuliah atau dosen..."
                    className="w-full sm:w-64 bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-2 text-xs text-slate-800 placeholder-slate-450"
                    value={krsSearch}
                    onChange={(e) => setKrsSearch(e.target.value)}
                  />
                  <span className="text-xs text-slate-500 font-mono shrink-0">TA: 2026/2027 Ganjil</span>
                </div>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse min-w-[600px]">
                  <thead>
                    <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                      <th className="p-4 pl-6">Kode</th>
                      <th className="p-4">Nama Matakuliah</th>
                      <th className="p-4 text-center">SKS</th>
                      <th className="p-4">Dosen Pembina</th>
                      <th className="p-4">Jadwal Sesi</th>
                      <th className="p-4 text-center pr-6">Status & Aksi</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 text-xs">
                    {state.courses
                      .filter(c => 
                        c.name.toLowerCase().includes(krsSearch.toLowerCase()) ||
                        c.code.toLowerCase().includes(krsSearch.toLowerCase()) ||
                        c.lecturerName.toLowerCase().includes(krsSearch.toLowerCase())
                      )
                      .map(c => {
                        const isEnrolled = enrolledCourseIds.includes(c.id);
                        return (
                          <tr key={c.id} className="hover:bg-slate-50/50 transition-colors">
                            <td className="p-4 pl-6 font-mono text-emerald-600 font-semibold">{c.code}</td>
                            <td className="p-4 font-bold text-slate-800">{c.name}</td>
                            <td className="p-4 text-center font-mono font-bold text-slate-600">{c.sks} SKS</td>
                            <td className="p-4 text-slate-600">{c.lecturerName}</td>
                            <td className="p-4 text-slate-500 font-mono">{c.scheduleDay}, {c.scheduleTime}</td>
                            <td className="p-4 text-center pr-6">
                              <div className="flex items-center justify-center gap-2">
                                {isEnrolled ? (
                                  <>
                                    <span className="inline-block px-2 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold font-mono rounded text-[10px]">
                                      TERKONTRAK
                                    </span>
                                    <button
                                      onClick={() => handleDropCourse(c.id)}
                                      className="px-2.5 py-1 bg-red-600 hover:bg-red-500 text-white rounded-lg text-[10px] font-semibold transition-colors shadow-sm cursor-pointer"
                                    >
                                      Batalkan
                                    </button>
                                  </>
                                ) : (
                                  <>
                                    <span className="inline-block px-2 py-0.5 bg-slate-50 border border-slate-200 text-slate-500 font-bold font-mono rounded text-[10px]">
                                      BELUM KONTRAK
                                    </span>
                                    <button
                                      onClick={() => handleEnrollCourse(c.id)}
                                      className="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-semibold transition-colors shadow-sm cursor-pointer"
                                    >
                                      Ambil Kelas
                                    </button>
                                  </>
                                )}
                              </div>
                            </td>
                          </tr>
                        );
                      })}
                    {state.courses.filter(c => 
                      c.name.toLowerCase().includes(krsSearch.toLowerCase()) ||
                      c.code.toLowerCase().includes(krsSearch.toLowerCase()) ||
                      c.lecturerName.toLowerCase().includes(krsSearch.toLowerCase())
                    ).length === 0 && (
                      <tr>
                        <td colSpan={6} className="p-8 text-center text-slate-400 italic">
                          Mata kuliah tidak ditemukan untuk pencarian "{krsSearch}"
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        )}

        {/* TAB 3: LEARNING SESSION (DYNAMIC COURSES & SESSIONS) */}
        {activeTab === 'session' && (
          <div className="space-y-6">
            
            {/* Horizontal Session Selector Bar */}
            <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
              <div>
                <span className="text-[9px] text-slate-400 font-mono font-bold tracking-widest uppercase block mb-1">NAVIGASI AKADEMIK</span>
                <h4 className="text-xs font-bold text-slate-800 uppercase font-mono">PILIH SESI PERKULIAHAN:</h4>
              </div>
              <div className="flex gap-1.5 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-thin">
                {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14].map(num => {
                  const isSelected = selectedSessionNum === num;
                  const hasCustomMaterial = state.materials.some(m => m.courseId === selectedCourseId && m.sessionNumber === num);
                  const hasCustomAsg = state.assignments.some(a => a.courseId === selectedCourseId && a.sessionNumber === num);
                  
                  return (
                    <button
                      key={num}
                      onClick={() => setSelectedSessionNum(num)}
                      className={`px-3 py-1.5 rounded-lg text-xs font-mono font-bold transition-all shrink-0 border ${isSelected ? 'bg-emerald-600 border-emerald-500 text-white shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200'} cursor-pointer`}
                    >
                      Sesi {num} {(hasCustomMaterial || hasCustomAsg) && '•'}
                    </button>
                  );
                })}
              </div>
            </div>

            <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
              <div className="flex flex-col sm:flex-row justify-between items-start gap-4 mb-4">
                <div>
                  <span className="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded inline-block uppercase">
                    SESI {String(selectedSessionNum).padStart(2, '0')} • {activeCourse.code}
                  </span>
                  <h3 className="text-xl font-display font-bold text-slate-900 mt-2.5">{activeMaterial.title}</h3>
                  <p className="text-xs text-slate-500 mt-1">{activeCourse.name} — Diampu oleh <strong className="text-slate-700">{activeCourse.lecturerName}</strong></p>
                </div>
                <div className="text-left sm:text-right">
                  <span className="text-xs text-slate-500 font-mono block">Status Kelas Mandiri</span>
                  <strong className="text-emerald-650 font-mono text-base block">
                    {activeSubmission ? '✓ Tugas Dikirim' : '• Menunggu Penyerahan'}
                  </strong>
                </div>
              </div>

              {/* Grid: Video Player + Resources list */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">
                
                {/* Simulated Custom Video Player */}
                <div className="lg:col-span-2 space-y-3">
                  <div className="relative aspect-video rounded-2xl bg-slate-900 border border-slate-200 overflow-hidden group flex flex-col justify-end">
                    
                    {/* Real/Simulated Video embed or placeholder */}
                    <video 
                      className="absolute inset-0 w-full h-full object-cover"
                      src={activeMaterial.videoUrl || "https://www.w3schools.com/html/mov_bbb.mp4"}
                      controls
                      referrerPolicy="no-referrer"
                    />

                    {/* Dark gradient overlay for controls */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-4 flex flex-col justify-end pointer-events-none">
                      <div className="flex justify-between items-center text-xs text-white pointer-events-auto">
                        <span>Sesi Kuliah Daring — {activeMaterial.title}</span>
                        <span className="font-mono bg-emerald-600 px-2 py-0.5 rounded text-[10px]">LIVE REPLAY</span>
                      </div>
                    </div>
                  </div>
                  <span className="text-[10px] text-slate-400 font-mono block">Pemutar Video Terenkripsi LPTIK Academic System — Server Cloud Run</span>
                </div>

                {/* Resource List */}
                <div className="bg-slate-50 border border-slate-200 p-5 rounded-2xl flex flex-col justify-between shadow-sm">
                  <div>
                    <h4 className="font-display font-bold text-sm text-slate-800 mb-4">Materi Pendukung Sesi</h4>
                    <div className="space-y-3">
                      <a 
                        href="#download" 
                        onClick={(e) => { 
                          e.preventDefault(); 
                          alert(`Mengunduh berkas materi kuliah: ${activeMaterial.fileName}`); 
                        }} 
                        className="flex items-center justify-between p-3 bg-white border border-slate-200 hover:border-emerald-300 rounded-xl transition-all shadow-sm"
                      >
                        <div className="flex gap-2 items-center min-w-0">
                          <Download className="w-4 h-4 text-emerald-600 shrink-0" />
                          <div className="min-w-0">
                            <h5 className="text-xs font-bold text-slate-800 truncate">{activeMaterial.fileName}</h5>
                            <span className="text-[9px] text-slate-500 block font-mono">PDF • {activeMaterial.fileSize || '3.5 MB'}</span>
                          </div>
                        </div>
                        <ChevronRight className="w-3.5 h-3.5 text-slate-400" />
                      </a>

                      <a 
                        href={activeMaterial.externalLink || "https://www.iucn.org"} 
                        target="_blank" 
                        rel="noreferrer" 
                        className="flex items-center justify-between p-3 bg-white border border-slate-200 hover:border-emerald-300 rounded-xl transition-all shadow-sm"
                      >
                        <div className="flex gap-2 items-center min-w-0">
                          <ExternalLink className="w-4 h-4 text-blue-600 shrink-0" />
                          <div className="min-w-0">
                            <h5 className="text-xs font-bold text-slate-800 truncate">Riset & Referensi Luar</h5>
                            <span className="text-[9px] text-slate-500 block font-mono">Tautan Eksternal Akademik</span>
                          </div>
                        </div>
                        <ChevronRight className="w-3.5 h-3.5 text-slate-400" />
                      </a>
                    </div>
                  </div>

                  <div className="bg-emerald-50 border border-emerald-100 p-3.5 rounded-xl text-[10px] text-emerald-800 leading-relaxed font-mono mt-4">
                    💡 <strong>Saran {activeCourse.lecturerName}:</strong> Baca seluruh ringkasan teori pada dokumen {activeMaterial.fileName} sebelum menyelesaikan tugas akademik mandiri di bawah ini.
                  </div>
                </div>

              </div>
            </div>

            {/* Assignments & Submission board */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Submission Area */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl lg:col-span-2 space-y-5 shadow-sm">
                <div className="flex justify-between items-center">
                  <h4 className="font-display font-bold text-sm text-slate-800">Tugas: {activeAssignment.title}</h4>
                  <span className="text-xs text-red-600 font-mono font-semibold">Tenggat: {activeAssignment.dueDate}</span>
                </div>

                <div className="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 leading-relaxed">
                  <strong className="text-slate-800">Petunjuk Tugas:</strong> {activeAssignment.instructions}
                </div>

                {/* Submission Form / Submission State card */}
                {!activeSubmission ? (
                  <form onSubmit={handleUploadSubmission} className="space-y-4 border-2 border-dashed border-slate-200 rounded-2xl p-6 bg-slate-50/50 flex flex-col items-center justify-center text-center">
                    <div className="w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-1">
                      <Upload className="w-5 h-5" />
                    </div>
                    <div>
                      <h5 className="font-bold text-xs text-slate-800">Unggah Laporan Hasil Tugas</h5>
                      <p className="text-[10px] text-slate-500 mt-1 max-w-sm">
                        Format yang didukung: PDF, DOCX (Maks. 10MB). Ketik nama berkas Anda di bawah untuk mengunggah tugas simulasi secara instan.
                      </p>
                    </div>

                    <div className="w-full max-w-md mt-2">
                      <input 
                        type="text"
                        placeholder="Ketik nama berkas tugas (Contoh: Tugas_Sesi_04_Alex_Rivera.pdf)"
                        required
                        className="w-full bg-white border border-slate-250 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl px-4 py-2.5 text-xs text-slate-800 font-mono placeholder-slate-400 shadow-sm"
                        value={studentFileName}
                        onChange={(e) => setStudentFileName(e.target.value)}
                      />
                    </div>

                    <button 
                      type="submit"
                      className="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm transition-all cursor-pointer mt-1"
                    >
                      Kirim Laporan Tugas ke Dosen
                    </button>
                  </form>
                ) : (
                  <div className="space-y-4">
                    <div className="bg-emerald-50 border border-emerald-150 p-5 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                          <CheckCircle2 className="w-6 h-6" />
                        </div>
                        <div>
                          <h5 className="font-bold text-xs text-emerald-950 uppercase">STATUS: TUGAS BERHASIL DIKIRIMKAN</h5>
                          <span className="text-[10px] text-slate-500 block font-mono mt-0.5">Dikirim pada: {activeSubmission.submittedAt}</span>
                          <span className="text-[10px] text-emerald-700 block font-mono font-semibold mt-1">Berkas: {activeSubmission.fileName} ({activeSubmission.fileSize})</span>
                        </div>
                      </div>
                      
                      <div className="bg-white rounded-xl px-4 py-2.5 text-center border border-slate-200 shadow-sm shrink-0 w-full sm:w-auto">
                        <span className="text-[9px] font-bold text-slate-500 font-mono block">NILAI DOSEN</span>
                        {activeSubmission.status === 'graded' ? (
                          <strong className="text-emerald-700 font-mono text-base block font-bold">{activeSubmission.score} / 100</strong>
                        ) : (
                          <strong className="text-amber-600 font-mono text-xs block font-bold">BELUM DINILAI</strong>
                        )}
                      </div>
                    </div>

                    {/* Resubmit helper button */}
                    <div className="flex justify-end">
                      <button
                        onClick={() => {
                          if (confirm('Apakah Anda yakin ingin membatalkan kiriman saat ini dan mengunggah berkas baru?')) {
                            setState(prev => ({
                              ...prev,
                              submissions: prev.submissions.filter(s => !(s.assignmentId === activeAssignment.id && s.studentId === currentStudentId))
                            }));
                          }
                        }}
                        className="text-[10px] text-red-600 hover:underline font-semibold font-mono"
                      >
                        [ Batalkan Kiriman & Unggah Ulang Berkas ]
                      </button>
                    </div>
                  </div>
                )}

                {/* Lecturer comments / feedback block */}
                {activeSubmission && (
                  <div className="border-t border-slate-150 pt-5 space-y-4">
                    <h5 className="font-bold text-xs text-slate-800 flex items-center gap-2">
                      <MessageSquare className="w-4 h-4 text-emerald-600" />
                      Forum Diskusi & Umpan Balik Akademik Sesi
                    </h5>
                    
                    <div className="space-y-3 max-h-60 overflow-y-auto pr-2">
                      {activeSubmission.comments.length === 0 ? (
                        <p className="text-xs text-slate-400 italic py-2">Belum ada tanggapan atau komentar diskusi. Silakan tulis pesan di bawah untuk memulai obrolan dengan dosen.</p>
                      ) : (
                        activeSubmission.comments.map((comment, cIdx) => {
                          const isAuthorLecturer = state.users.find(u => u.name === comment.authorName)?.role === 'lecturer' || comment.authorName === activeCourse.lecturerName;
                          return (
                            <div key={cIdx} className={`p-3.5 rounded-xl text-xs ${isAuthorLecturer ? 'bg-sky-50 border border-sky-100 text-sky-950 ml-8 shadow-sm' : 'bg-slate-50 border border-slate-200 mr-8 shadow-sm'}`}>
                              <div className="flex justify-between text-[10px] text-slate-500 font-bold mb-1.5 font-mono">
                                <span className="flex items-center gap-1">
                                  {comment.authorName}
                                  {isAuthorLecturer && <span className="text-[8px] bg-sky-200 text-sky-800 px-1 rounded font-bold font-mono">DOSEN</span>}
                                </span>
                                <span>{comment.timestamp}</span>
                              </div>
                              <p className="text-slate-700 leading-relaxed font-sans">{comment.text}</p>
                            </div>
                          );
                        })
                      )}
                    </div>

                    {/* New comment form */}
                    <form onSubmit={handlePostComment} className="flex gap-2 pt-2">
                      <input 
                        type="text" 
                        placeholder={`Tulis pesan atau pertanyaan diskusi untuk ${activeCourse.lecturerName}...`}
                        required
                        className="flex-1 bg-white border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-2.5 text-xs text-slate-850 focus:outline-none placeholder-slate-400"
                        value={commentText}
                        onChange={(e) => setCommentText(e.target.value)}
                      />
                      <button 
                        type="submit" 
                        className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer shrink-0"
                      >
                        Kirim Balasan
                      </button>
                    </form>
                  </div>
                )}
              </div>

              {/* Grade Weighting card */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl flex flex-col justify-between shadow-sm">
                <div>
                  <h4 className="font-display font-bold text-sm text-slate-800 mb-4">Informasi Penilaian</h4>
                  <p className="text-xs text-slate-600 leading-relaxed">
                    Sistem evaluasi akademik Eco-Learning berpusat pada integritas ilmiah, keakuratan olah data lingkungan, kegesitan analisis, serta orisinalitas pemikiran.
                  </p>
                  <div className="mt-6 space-y-4">
                    <div className="flex justify-between items-center text-xs font-mono">
                      <span className="text-slate-500">Kepatuhan Tenggat:</span>
                      <strong className="text-emerald-700 font-semibold">Tepat Waktu (On-Time)</strong>
                    </div>
                    <div className="flex justify-between items-center text-xs font-mono border-t border-slate-100 pt-3">
                      <span className="text-slate-500">Skor Maksimal:</span>
                      <strong className="text-slate-800 font-semibold">{activeAssignment.maxScore || 100} Poin</strong>
                    </div>
                  </div>
                </div>

                <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-xl text-center mt-6">
                  <span className="text-[10px] text-emerald-800 font-mono font-bold block uppercase">Bobot Tugas Sesi Ini</span>
                  <strong className="text-emerald-950 text-xl font-mono mt-1 block font-bold">20% Bobot Nilai Akhir</strong>
                </div>
              </div>

            </div>

          </div>
        )}

        {/* TAB 4: PROFILE MANAGEMENT */}
        {activeTab === 'profile' && (
          <div className="space-y-6">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Form card details */}
              <div className="bg-white border border-slate-200 rounded-2xl p-6 lg:col-span-2 shadow-sm">
                <h3 className="font-display font-bold text-base text-slate-800 mb-6">Ubah Profil Pengguna</h3>
                
                <form onSubmit={handleSaveProfile} className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">NAMA LENGKAP</label>
                      <input 
                        type="text" 
                        required
                        className="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800"
                        value={profileName}
                        onChange={(e) => setProfileName(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">EMAIL INSTITUSI</label>
                      <input 
                        type="email" 
                        required
                        className="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800"
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
                        className="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800 font-mono"
                        value={profilePhone}
                        onChange={(e) => setProfilePhone(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">PRODI / PROGRAM STUDI</label>
                      <input 
                        type="text" 
                        disabled
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-semibold text-slate-500 font-sans"
                        value={profileMajor}
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-slate-500 uppercase font-mono mb-1">SEMESTER AKTIF</label>
                      <input 
                        type="number" 
                        className="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl p-3 text-xs font-semibold text-slate-800 font-mono"
                        value={profileSemester}
                        onChange={(e) => setProfileSemester(Number(e.target.value))}
                      />
                    </div>
                  </div>

                  <div className="pt-4 flex justify-end">
                    <button 
                      type="submit"
                      className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition-all cursor-pointer"
                    >
                      <span className="flex items-center gap-1.5">
                        <Save className="w-4 h-4" /> Simpan Perubahan Profil
                      </span>
                    </button>
                  </div>
                </form>
              </div>

              {/* Bio summary card stats */}
              <div className="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between shadow-sm">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-800 mb-4">Statistik Pendidikan</h3>
                  <div className="space-y-4">
                    <div className="p-4 bg-slate-50 border border-slate-150 rounded-2xl">
                      <span className="text-slate-500 text-[10px] font-mono font-bold block uppercase">Total SKS Diperoleh</span>
                      <strong className="text-xl text-slate-800 font-mono block mt-1">84 SKS</strong>
                    </div>
                    <div className="p-4 bg-slate-50 border border-slate-150 rounded-2xl">
                      <span className="text-slate-500 text-[10px] font-mono font-bold block uppercase">Indeks Prestasi Kumulatif (CGPA)</span>
                      <strong className="text-xl text-emerald-700 font-mono block mt-1">3.88 / 4.00</strong>
                    </div>
                    <div className="p-4 bg-slate-50 border border-slate-150 rounded-2xl">
                      <span className="text-slate-500 text-[10px] font-mono font-bold block uppercase">Kemajuan Studi (Study Progress)</span>
                      <strong className="text-xl text-slate-800 font-mono block mt-1">65% Selesai</strong>
                    </div>
                  </div>
                </div>

                <p className="text-[10px] text-slate-400 font-mono leading-relaxed mt-6">
                  Akun diverifikasi resmi LPTIK Universitas Eco-Learning.
                </p>
              </div>

            </div>
          </div>
        )}

        {/* TAB 5: SEMESTER RESULTS */}
        {activeTab === 'results' && (
          <div className="space-y-6">
            
            {/* Presence Donut and CGPA blocks */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Presence Donut simulation using custom CSS and SVG */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl flex flex-col justify-between items-center text-center shadow-sm">
                <h4 className="font-display font-bold text-sm text-slate-800 w-full text-left mb-4">Laju Absensi Semester</h4>
                
                {/* SVG Donut */}
                <div className="relative w-32 h-32 flex items-center justify-center">
                  <svg className="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                    <path
                      className="text-slate-100"
                      strokeWidth="3"
                      stroke="currentColor"
                      fill="none"
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                    <path
                      className="text-emerald-600"
                      strokeDasharray="93, 100"
                      strokeWidth="3.2"
                      strokeLinecap="round"
                      stroke="currentColor"
                      fill="none"
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                  </svg>
                  <div className="absolute font-mono text-xl font-bold text-slate-800">
                    93%
                  </div>
                </div>

                <div className="mt-4 text-xs text-slate-600 px-2 leading-relaxed">
                  Total kehadiran kelas Anda semester berjalan adalah <strong className="text-slate-800 font-bold">93%</strong> (Hadir penuh pada 112 dari 120 sesi perkuliahan aktif).
                </div>
              </div>

              {/* Progress completion bar */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl flex flex-col justify-between shadow-sm">
                <div>
                  <h4 className="font-display font-bold text-sm text-slate-800 mb-4">Kemajuan Gelar Kelulusan</h4>
                  <div className="space-y-4">
                    <div>
                      <div className="flex justify-between text-xs font-mono font-semibold text-slate-500 mb-1.5">
                        <span>SKS Semester Berjalan:</span>
                        <span>24 / 24 SKS</span>
                      </div>
                      <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                        <div className="bg-emerald-600 h-2" style={{ width: '100%' }} />
                      </div>
                    </div>

                    <div>
                      <div className="flex justify-between text-xs font-mono font-semibold text-slate-500 mb-1.5">
                        <span>Pencapaian Prasyarat Skripsi:</span>
                        <span>84 / 144 SKS</span>
                      </div>
                      <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                        <div className="bg-blue-600 h-2" style={{ width: '58.3%' }} />
                      </div>
                    </div>
                  </div>
                </div>

                <p className="text-[11px] text-emerald-700 font-mono mt-6 leading-relaxed font-medium">
                  ✓ Anda memenuhi batas minimal 80 SKS untuk mulai mengambil mata kuliah persiapan proposal skripsi.
                </p>
              </div>

              {/* Cumulative GPA stats */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl flex flex-col justify-between shadow-sm">
                <div>
                  <h4 className="font-display font-bold text-sm text-slate-800 mb-4">Evaluasi Prestasi Kumulatif</h4>
                  <div className="space-y-3 font-mono text-xs text-slate-500">
                    <div className="flex justify-between">
                      <span>Semester 1 IPS:</span>
                      <strong className="text-slate-800">3.82</strong>
                    </div>
                    <div className="flex justify-between">
                      <span>Semester 2 IPS:</span>
                      <strong className="text-slate-800">3.90</strong>
                    </div>
                    <div className="flex justify-between">
                      <span>Semester 3 IPS:</span>
                      <strong className="text-slate-800">3.92</strong>
                    </div>
                    <div className="flex justify-between border-t border-slate-100 pt-2 text-slate-800 font-bold">
                      <span>Proyeksi Semester 4 IPS:</span>
                      <strong className="text-emerald-700">3.88</strong>
                    </div>
                  </div>
                </div>

                <div className="p-3 bg-emerald-600 border border-emerald-500 rounded-xl text-center text-[10px] font-mono text-white mt-6 font-bold shadow-sm">
                  PREDIKAT AKADEMIK: <strong>MAGNA CUM LAUDE</strong>
                </div>
              </div>

            </div>

            {/* Final Grades current table */}
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
              <div className="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 className="font-display font-bold text-base text-slate-800">Hasil Evaluasi Studi Semester Berjalan (KHS Digital)</h3>
                <span className="text-xs text-slate-500 font-mono">Diverifikasi LPTIK Akademik</span>
              </div>
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                    <th className="p-4 pl-6">Kode Matakuliah</th>
                    <th className="p-4">Nama Kelas Kuliah</th>
                    <th className="p-4 text-center">Beban SKS</th>
                    <th className="p-4 text-center">Skor Akhir</th>
                    <th className="p-4 text-center">Indeks Huruf</th>
                    <th className="p-4 text-center pr-6">Status Kelulusan</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-xs">
                  {enrolledCourses.map((c, index) => {
                    const courseGradeRecord = state.courseGrades.find(cg => cg.courseId === c.id);
                    const studentGradeRecord = courseGradeRecord?.studentGrades.find(sg => sg.studentId === currentStudentId);
                    
                    const roundedScore = studentGradeRecord ? studentGradeRecord.finalScore : Math.round(88 - (index * 2));
                    const grade = studentGradeRecord ? studentGradeRecord.gradeLetter : (roundedScore >= 80 ? 'A' : roundedScore >= 70 ? 'B' : roundedScore >= 60 ? 'C' : 'D');
                    const isPassed = grade !== 'F' && grade !== 'D';

                    return (
                      <tr key={c.id} className="hover:bg-slate-50/50">
                        <td className="p-4 pl-6 font-mono text-emerald-600 font-semibold">{c.code}</td>
                        <td className="p-4 font-bold text-slate-800">{c.name}</td>
                        <td className="p-4 text-center font-mono text-slate-600 font-bold">{c.sks} SKS</td>
                        <td className="p-4 text-center font-mono font-bold text-slate-800">{roundedScore}</td>
                        <td className="p-4 text-center">
                          <span className="inline-block w-8 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-center rounded text-[10px]">
                            {grade}
                          </span>
                        </td>
                        <td className="p-4 text-center pr-6">
                          {isPassed ? (
                            <span className="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold font-mono">
                              <Check className="w-3.5 h-3.5" /> LULUS
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 text-[10px] text-rose-600 font-bold font-mono">
                              <X className="w-3.5 h-3.5" /> MENGULANG
                            </span>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

          </div>
        )}

      </main>
    </div>
  );
}
