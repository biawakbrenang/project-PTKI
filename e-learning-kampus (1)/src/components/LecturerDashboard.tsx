import React, { useState } from 'react';
import { 
  LayoutDashboard, BookOpen, Calendar, FileSpreadsheet, 
  Settings, Award, GraduationCap, ChevronRight, Plus, 
  ArrowRight, Search, CheckCircle, Clock, AlertCircle, 
  User, Check, X, ShieldAlert, Key, Upload, Link2, 
  ChevronDown, FileText, BarChart3, TrendingUp, Sparkles, LogOut
} from 'lucide-react';
import { EcoAppState, Course, AttendanceStudent, Material, Assignment, StudentGrade, AttendanceSession } from '../types';

interface LecturerDashboardProps {
  state: EcoAppState;
  setState: React.Dispatch<React.SetStateAction<EcoAppState>>;
  onLogout: () => void;
}

export default function LecturerDashboard({ state, setState, onLogout }: LecturerDashboardProps) {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'courses' | 'attendance' | 'materials' | 'grades'>('dashboard');
  const [searchQuery, setSearchQuery] = useState('');
  
  const lecturerCourses = state.courses.filter(c => c.lecturerName === state.currentUser?.name);
  const defaultCourseId = lecturerCourses[0]?.id || 'c-3';
  // Tab 'attendance' state
  const [selectedCourseId, setSelectedCourseId] = useState<string>(defaultCourseId);
  const [meetingNumber, setMeetingNumber] = useState<number>(12);
  const [showTokenModal, setShowTokenModal] = useState(false);
  const [tokenText, setTokenText] = useState('ECO-992A');

  // Tab 'materials' state
  const [materialTitle, setMaterialTitle] = useState('');
  const [materialDesc, setMaterialDesc] = useState('');
  const [isAssignmentEnabled, setIsAssignmentEnabled] = useState(true);
  const [asgTitle, setAsgTitle] = useState('');
  const [asgInstructions, setAsgInstructions] = useState('');
  const [asgDueDate, setAsgDueDate] = useState('2026-07-20');
  const [materialSessionNum, setMaterialSessionNum] = useState(13);
  const [selectedUploadFile, setSelectedUploadFile] = useState<File | null>(null);
  const [uploadFileName, setUploadFileName] = useState('');

  // Material/Assignment Edit Modals state
  const [showEditMaterialModal, setShowEditMaterialModal] = useState(false);
  const [editMaterialId, setEditMaterialId] = useState('');
  const [editMaterialTitle, setEditMaterialTitle] = useState('');
  const [editMaterialDesc, setEditMaterialDesc] = useState('');
  const [editMaterialSessionNum, setEditMaterialSessionNum] = useState(1);

  const [showEditAssignmentModal, setShowEditAssignmentModal] = useState(false);
  const [editAssignmentId, setEditAssignmentId] = useState('');
  const [editAssignmentTitle, setEditAssignmentTitle] = useState('');
  const [editAssignmentInstructions, setEditAssignmentInstructions] = useState('');
  const [editAssignmentDueDate, setEditAssignmentDueDate] = useState('');

  const handleOpenEditMaterial = (m: Material) => {
    setEditMaterialId(m.id);
    setEditMaterialTitle(m.title);
    setEditMaterialDesc(m.description || '');
    setEditMaterialSessionNum(m.sessionNumber);
    setShowEditMaterialModal(true);
  };

  const handleSaveMaterialEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editMaterialTitle) {
      alert('Judul materi tidak boleh kosong!');
      return;
    }
    setState(prev => {
      const updatedMaterials = prev.materials.map(m => {
        if (m.id === editMaterialId) {
          return {
            ...m,
            title: editMaterialTitle,
            description: editMaterialDesc,
            sessionNumber: editMaterialSessionNum
          };
        }
        return m;
      });
      const newLog = {
        id: `log-${Date.now()}`,
        actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
        role: 'Lecturer',
        action: `Mengedit materi kuliah Sesi ${editMaterialSessionNum} "${editMaterialTitle}"`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        materials: updatedMaterials,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    setShowEditMaterialModal(false);
    alert('Materi kuliah berhasil diperbarui!');
  };

  const handleOpenEditAssignment = (asg: Assignment) => {
    setEditAssignmentId(asg.id);
    setEditAssignmentTitle(asg.title);
    setEditAssignmentInstructions(asg.instructions || '');
    setEditAssignmentDueDate(asg.dueDate);
    setShowEditAssignmentModal(true);
  };

  const handleSaveAssignmentEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editAssignmentTitle) {
      alert('Judul penugasan tidak boleh kosong!');
      return;
    }
    setState(prev => {
      const updatedAsgs = prev.assignments.map(a => {
        if (a.id === editAssignmentId) {
          return {
            ...a,
            title: editAssignmentTitle,
            instructions: editAssignmentInstructions,
            dueDate: editAssignmentDueDate
          };
        }
        return a;
      });
      const newLog = {
        id: `log-${Date.now()}`,
        actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
        role: 'Lecturer',
        action: `Mengedit penugasan kuliah "${editAssignmentTitle}"`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        assignments: updatedAsgs,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    setShowEditAssignmentModal(false);
    alert('Penugasan kuliah berhasil diperbarui!');
  };

  const handleDeleteMaterial = (materialId: string) => {
    if (!confirm('Apakah Anda yakin ingin menghapus materi kuliah ini?')) return;
    setState(prev => {
      const target = prev.materials.find(m => m.id === materialId);
      const newLog = {
        id: `log-${Date.now()}`,
        actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
        role: 'Lecturer',
        action: `Menghapus materi kuliah: "${target?.title || ''}"`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        materials: prev.materials.filter(m => m.id !== materialId),
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Materi kuliah berhasil dihapus!');
  };

  const handleDeleteAssignment = (assignmentId: string) => {
    if (!confirm('Apakah Anda yakin ingin menghapus penugasan ini?')) return;
    setState(prev => {
      const target = prev.assignments.find(a => a.id === assignmentId);
      const newLog = {
        id: `log-${Date.now()}`,
        actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
        role: 'Lecturer',
        action: `Menghapus penugasan kuliah: "${target?.title || ''}"`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        assignments: prev.assignments.filter(a => a.id !== assignmentId),
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Penugasan kuliah berhasil dihapus!');
  };

  // Course management modal state
  const [showNewCourseModal, setShowNewCourseModal] = useState(false);
  const [newCourseName, setNewCourseName] = useState('');
  const [newCourseCode, setNewCourseCode] = useState('');
  const [newCourseSKS, setNewCourseSKS] = useState(3);
  const [newCourseClass, setNewCourseClass] = useState('Lab Arsitektur 2A');

  const selectedCourse = state.courses.find(c => c.id === selectedCourseId) || state.courses[0];
  const attendanceSession: AttendanceSession = state.attendanceSessions.find(s => s.courseId === selectedCourseId && s.meetingNumber === meetingNumber) || {
    courseId: selectedCourseId,
    subject: selectedCourse?.name || '',
    meetingNumber: meetingNumber,
    attendanceRate: 100,
    students: []
  };

  // Quick actions
  const handleMarkAllPresent = () => {
    setState(prev => {
      const updatedSessions = prev.attendanceSessions.map(sess => {
        if (sess.courseId === selectedCourseId && sess.meetingNumber === meetingNumber) {
          return {
            ...sess,
            attendanceRate: 100,
            students: sess.students.map(s => ({ ...s, status: 'present' as const }))
          };
        }
        return sess;
      });
      
      const newLog = {
        id: `log-${Date.now()}`,
        actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
        role: 'Lecturer',
        action: `Mengubah status semua mahasiswa hadir pada Sesi ${meetingNumber} ${selectedCourse.name}`,
        timestamp: new Date().toLocaleString()
      };

      return {
        ...prev,
        attendanceSessions: updatedSessions,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
  };

  const handleStatusChange = (studentId: string, newStatus: 'present' | 'sick' | 'permit' | 'absent') => {
    setState(prev => {
      const updatedSessions = prev.attendanceSessions.map(sess => {
        if (sess.courseId === selectedCourseId && sess.meetingNumber === meetingNumber) {
          const updatedStudents = sess.students.map(s => {
            if (s.studentId === studentId) {
              return { ...s, status: newStatus };
            }
            return s;
          });

          // Recalculate attendance rate
          const presentCount = updatedStudents.filter(s => s.status === 'present' || s.status === 'sick' || s.status === 'permit').length;
          const rate = Math.round((presentCount / updatedStudents.length) * 100);

          return {
            ...sess,
            attendanceRate: rate,
            students: updatedStudents
          };
        }
        return sess;
      });

      return { ...prev, attendanceSessions: updatedSessions };
    });
  };

  const handleNotesChange = (studentId: string, text: string) => {
    setState(prev => {
      const updatedSessions = prev.attendanceSessions.map(sess => {
        if (sess.courseId === selectedCourseId && sess.meetingNumber === meetingNumber) {
          return {
            ...sess,
            students: sess.students.map(s => s.studentId === studentId ? { ...s, notes: text } : s)
          };
        }
        return sess;
      });
      return { ...prev, attendanceSessions: updatedSessions };
    });
  };

  const handleSaveAttendanceRecap = () => {
    setState(prev => {
      const newLog = {
        id: `log-${Date.now()}`,
        actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
        role: 'Lecturer',
        action: `Menyimpan rekapitulasi kehadiran Sesi ${meetingNumber} ${selectedCourse.name} (${attendanceSession.attendanceRate}% hadir)`,
        timestamp: new Date().toLocaleString()
      };
      return {
        ...prev,
        activityLogs: [newLog, ...prev.activityLogs]
      };
    });
    alert('Rekapitulasi absensi berhasil disimpan ke basis data akademik!');
  };

  const handleGenerateToken = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
    let token = 'ECO-';
    for (let i = 0; i < 5; i++) {
      token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    setTokenText(token);
    
    setState(prev => {
      const updatedSessions = prev.attendanceSessions.map(sess => {
        if (sess.courseId === selectedCourseId && sess.meetingNumber === meetingNumber) {
          return { ...sess, token };
        }
        return sess;
      });
      return { ...prev, attendanceSessions: updatedSessions };
    });
    setShowTokenModal(true);
  };

  // Handle grade editing
  const handleGradeChange = (studentId: string, field: 'attendanceScore' | 'assignmentScore' | 'utsScore' | 'uasScore', val: number) => {
    const clampedVal = Math.max(0, Math.min(100, val));
    setState(prev => {
      const updatedGrades = prev.courseGrades.map(cg => {
        if (cg.courseId === selectedCourseId) {
          const updatedStudentGrades = cg.studentGrades.map(sg => {
            if (sg.studentId === studentId) {
              const fresh = { ...sg, [field]: clampedVal };
              // Recalculate final
              const final = Math.round(
                (fresh.attendanceScore * (cg.weights.attendance / 100)) +
                (fresh.assignmentScore * (cg.weights.assignments / 100)) +
                (fresh.utsScore * (cg.weights.uts / 100)) +
                (fresh.uasScore * (cg.weights.uas / 100))
              );
              
              let gradeLetter: 'A' | 'B' | 'C' | 'D' | 'F' = 'A';
              if (final < 50) gradeLetter = 'F';
              else if (final < 60) gradeLetter = 'D';
              else if (final < 75) gradeLetter = 'C';
              else if (final < 85) gradeLetter = 'B';

              return {
                ...fresh,
                finalScore: final,
                gradeLetter
              };
            }
            return sg;
          });
          return { ...cg, studentGrades: updatedStudentGrades };
        }
        return cg;
      });
      return { ...prev, courseGrades: updatedGrades };
    });
  };

  // Add material & assignment
  const handlePublishMaterials = (e: React.FormEvent) => {
    e.preventDefault();
    if (!materialTitle) {
      alert('Judul materi wajib diisi!');
      return;
    }

    const newMaterial: Material = {
      id: `m-${Date.now()}`,
      courseId: selectedCourseId,
      sessionNumber: materialSessionNum,
      title: materialTitle,
      description: materialDesc,
      fileName: uploadFileName || 'Materi_Pendukung_Kuliah.pdf',
      fileSize: '3.4 MB',
      isPublished: true,
      publishDate: new Date().toISOString().split('T')[0]
    };

    let newAsg: Assignment | null = null;
    if (isAssignmentEnabled && asgTitle) {
      newAsg = {
        id: `asg-${Date.now()}`,
        courseId: selectedCourseId,
        sessionNumber: materialSessionNum,
        title: asgTitle,
        instructions: asgInstructions,
        dueDate: asgDueDate,
        allowedFormats: ['PDF', 'DOCX'],
        maxScore: 100,
        isPublished: true
      };
    }

    setState(prev => {
      const logs = [
        {
          id: `log-${Date.now()}`,
          actor: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
          role: 'Lecturer',
          action: `Mempublikasikan materi Sesi ${materialSessionNum} "${materialTitle}" pada ${selectedCourse.name}`,
          timestamp: new Date().toLocaleString()
        }
      ];

      return {
        ...prev,
        materials: [...prev.materials, newMaterial],
        assignments: newAsg ? [...prev.assignments, newAsg] : prev.assignments,
        activityLogs: [...logs, ...prev.activityLogs]
      };
    });

    alert('Materi dan Tugas berhasil dipublikasikan!');
    setMaterialTitle('');
    setMaterialDesc('');
    setAsgTitle('');
    setAsgInstructions('');
    setUploadFileName('');
    setSelectedUploadFile(null);
  };

  // New course creator
  const handleCreateCourse = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newCourseName || !newCourseCode) {
      alert('Kode dan Nama kelas wajib diisi!');
      return;
    }

    const newCourseObj: Course = {
      id: `c-${Date.now()}`,
      code: newCourseCode,
      name: newCourseName,
      lecturerName: state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.',
      studentsCount: 20,
      progress: 0,
      sks: Number(newCourseSKS),
      classRoom: newCourseClass,
      scheduleDay: 'Senin',
      scheduleTime: '13:00 - 15:30'
    };

    setState(prev => {
      return {
        ...prev,
        courses: [...prev.courses, newCourseObj]
      };
    });

    setShowNewCourseModal(false);
    setNewCourseName('');
    setNewCourseCode('');
    alert(`Kelas ${newCourseName} baru berhasil ditambahkan!`);
  };

  // Grade statistics computed from current selection
  const courseGradeSet = state.courseGrades.find(cg => cg.courseId === selectedCourseId);
  const gradesList = courseGradeSet?.studentGrades || [];
  const averageScore = gradesList.length > 0 
    ? (gradesList.reduce((acc, curr) => acc + (curr.finalScore || 0), 0) / gradesList.length).toFixed(1)
    : '0.0';
  const passRate = gradesList.length > 0
    ? ((gradesList.filter(sg => (sg.finalScore || 0) >= 60).length / gradesList.length) * 100).toFixed(1)
    : '0.0';

  // Letter grades count
  const distribution = { A: 0, B: 0, C: 0, D: 0, F: 0 };
  gradesList.forEach(g => {
    if (g.gradeLetter) {
      distribution[g.gradeLetter]++;
    }
  });

  return (
    <div className="min-h-screen bg-[#F8F9FA] text-[#1E293B] flex font-sans">
      
      {/* SIDE NAV - Elegant Forest Green Theme */}
      <aside className="w-64 bg-[#0F291E] text-white flex flex-col shrink-0 border-r border-[#1B4332]">
        
        {/* Brand / Logo */}
        <div className="p-6 border-b border-[#1B4332]/50 flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-[#1B4332] border border-[#2D6A4F] flex items-center justify-center text-[#52B788]">
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h1 className="font-display font-bold text-base leading-tight tracking-wide">ECO-LEARNING</h1>
            <span className="text-[10px] text-[#52B788] font-mono tracking-widest font-semibold uppercase">LECTURER SUITE</span>
          </div>
        </div>

        {/* Lecturer Quick Profile info */}
        <div className="p-6 flex items-center gap-3 border-b border-[#1B4332]/30">
          <img 
            className="w-10 h-10 rounded-full object-cover border-2 border-[#52B788]" 
            src={state.currentUser?.avatarUrl} 
            alt={state.currentUser?.name || 'Lecturer'}
            referrerPolicy="no-referrer"
          />
          <div>
            <h4 className="text-xs font-semibold leading-none">{state.currentUser?.name}</h4>
            <span className="text-[10px] text-slate-400 font-mono mt-1 block">NIDN {state.currentUser?.identifier}</span>
          </div>
        </div>

        {/* Sidebar Links */}
        <nav className="flex-1 p-4 space-y-1">
          <button 
            onClick={() => setActiveTab('dashboard')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors ${activeTab === 'dashboard' ? 'bg-[#1B4332] text-white shadow-md' : 'text-[#A3B899] hover:bg-[#1B4332]/40 hover:text-white'}`}
          >
            <LayoutDashboard className="w-4 h-4 shrink-0" />
            Dashboard
          </button>
          <button 
            onClick={() => setActiveTab('courses')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors ${activeTab === 'courses' ? 'bg-[#1B4332] text-white shadow-md' : 'text-[#A3B899] hover:bg-[#1B4332]/40 hover:text-white'}`}
          >
            <BookOpen className="w-4 h-4 shrink-0" />
            Kelas Saya
          </button>
          <button 
            onClick={() => setActiveTab('attendance')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors ${activeTab === 'attendance' ? 'bg-[#1B4332] text-white shadow-md' : 'text-[#A3B899] hover:bg-[#1B4332]/40 hover:text-white'}`}
          >
            <Calendar className="w-4 h-4 shrink-0" />
            Absensi
          </button>
          <button 
            onClick={() => setActiveTab('materials')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors ${activeTab === 'materials' ? 'bg-[#1B4332] text-white shadow-md' : 'text-[#A3B899] hover:bg-[#1B4332]/40 hover:text-white'}`}
          >
            <FileSpreadsheet className="w-4 h-4 shrink-0" />
            Bahan & Tugas
          </button>
          <button 
            onClick={() => setActiveTab('grades')}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors ${activeTab === 'grades' ? 'bg-[#1B4332] text-white shadow-md' : 'text-[#A3B899] hover:bg-[#1B4332]/40 hover:text-white'}`}
          >
            <Award className="w-4 h-4 shrink-0" />
            Rekap Nilai
          </button>
        </nav>

        {/* Footer info & Logout */}
        <div className="p-4 border-t border-[#1B4332]/50">
          <button 
            onClick={onLogout}
            className="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs text-[#FCA3B7] hover:bg-red-950/30 transition-colors font-medium text-left"
          >
            <LogOut className="w-4 h-4" />
            Keluar dari Sistem
          </button>
          <div className="mt-4 text-center text-[9px] text-slate-500 font-mono">
            Eco-Learning Portal v1.2
          </div>
        </div>
      </aside>

      {/* MAIN CONTAINER */}
      <main className="flex-1 overflow-y-auto p-8 relative flex flex-col">
        
        {/* TOP BAR / Header details */}
        <header className="flex justify-between items-center mb-8 pb-4 border-b border-slate-200">
          <div>
            <span className="text-[10px] text-emerald-600 font-bold uppercase tracking-wider font-mono">Fakultas Desain & Arsitektur Berkelanjutan</span>
            <h2 className="text-2xl font-display font-bold text-slate-900 mt-1">
              {activeTab === 'dashboard' && "Dasbor Akademik"}
              {activeTab === 'courses' && "Manajemen Kelas Saya"}
              {activeTab === 'attendance' && "Absensi Kuliah Real-Time"}
              {activeTab === 'materials' && "Pusat Materi & Penugasan"}
              {activeTab === 'grades' && "Portal Rekapitulasi Nilai UTS / UAS"}
            </h2>
          </div>

          <div className="flex items-center gap-4">
            {/* Global class scope switcher */}
            <div className="flex items-center gap-2 bg-white px-4 py-2.5 border border-slate-200 rounded-xl shadow-sm">
              <span className="text-xs text-slate-500 font-medium font-mono">Mata Kuliah:</span>
              <select 
                className="text-xs font-semibold text-slate-800 bg-transparent focus:outline-none cursor-pointer"
                value={selectedCourseId}
                onChange={(e) => setSelectedCourseId(e.target.value)}
              >
                {state.courses.filter(c => c.lecturerName === (state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.')).map(c => (
                  <option key={c.id} value={c.id}>{c.code} - {c.name}</option>
                ))}
              </select>
            </div>
            
            {/* Soft Indicator */}
            <div className="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold font-mono text-xs">
              {state.currentUser?.name.split(' ').map(n => n[0]).join('').slice(0, 2) || 'DS'}
            </div>
          </div>
        </header>

        {/* TAB 1: DASHBOARD */}
        {activeTab === 'dashboard' && (
          <div className="space-y-6">
            
            {/* Greetings Banner Card */}
            <div className="relative overflow-hidden bg-gradient-to-r from-[#143225] to-[#1F4E3A] rounded-2xl p-8 text-white shadow-md flex justify-between items-center">
              <div className="absolute right-0 top-0 bottom-0 opacity-10 pointer-events-none">
                {/* SVG Leaf Pattern */}
                <svg className="w-80 h-full" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
                  <path d="M10 80 Q 52.5 10, 95 80 T 10 80" />
                </svg>
              </div>
              
              <div className="relative z-10 max-w-xl">
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-900/40 text-[#74C69D] text-[10px] font-bold tracking-widest uppercase mb-4 border border-emerald-800/50">
                  <Sparkles className="w-3 h-3 text-[#52B788]" />
                  Hari Akademik Aktif
                </span>
                <h3 className="text-3xl font-display font-bold">Selamat Datang, {state.currentUser?.name}</h3>
                <p className="text-[#A3B899] text-sm mt-2 leading-relaxed">
                  Selamat datang kembali di Eco-Learning Portal. Rata-rata kehadiran kelas Anda minggu ini berada pada <strong className="text-white font-semibold">92%</strong>, dengan performa akademik tertinggi di kelas <em className="text-white underline">Sustainable Urban Design</em>.
                </p>
                <button 
                  onClick={() => setActiveTab('materials')}
                  className="mt-6 inline-flex items-center gap-2 px-5 py-2.5 bg-[#52B788] hover:bg-[#40916C] text-[#0F291E] text-xs font-bold rounded-xl transition-all shadow-md active:scale-[0.98] cursor-pointer"
                >
                  Buat Materi Baru
                  <ArrowRight className="w-4 h-4" />
                </button>
              </div>
              
              {/* Organic visual display */}
              <div className="hidden lg:block w-40 h-40 shrink-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-emerald-800 to-transparent flex items-center justify-center">
                <div className="w-24 h-24 rounded-3xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center animate-pulse-glow">
                  <svg className="w-14 h-14 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                </div>
              </div>
            </div>

            {/* Quick Metrics Panels */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Total Kelas Diampu</span>
                  <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">08</h4>
                  <span className="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-2">
                    <TrendingUp className="w-3 h-3" /> Semester Ganjil
                  </span>
                </div>
                <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                  <BookOpen className="w-6 h-6" />
                </div>
              </div>

              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Tugas Perlu Diulas</span>
                  <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">12</h4>
                  <span className="text-[10px] text-amber-600 font-semibold flex items-center gap-1 mt-2">
                    <Clock className="w-3 h-3" /> Tindak lanjut segera
                  </span>
                </div>
                <div className="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                  <FileText className="w-6 h-6" />
                </div>
              </div>

              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Siswa Aktif</span>
                  <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">245</h4>
                  <span className="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-2">
                    <TrendingUp className="w-3 h-3" /> Terdaftar SKS penuh
                  </span>
                </div>
                <div className="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                  <User className="w-6 h-6" />
                </div>
              </div>

              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                  <span className="text-xs text-slate-500 font-medium">Laju Kehadiran</span>
                  <h4 className="text-3xl font-display font-bold text-slate-800 mt-1">92%</h4>
                  <span className="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-2">
                    +1.2% dari bulan lalu
                  </span>
                </div>
                <div className="w-12 h-12 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                  <TrendingUp className="w-6 h-6" />
                </div>
              </div>
            </div>

            {/* Dashboard grid: Teaching schedules & recent submissions */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Teaching Schedule */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm lg:col-span-2">
                <div className="flex justify-between items-center mb-6">
                  <h3 className="font-display font-bold text-base text-slate-800">Jadwal Mengajar Hari Ini</h3>
                  <span className="text-xs text-slate-400 font-medium">Kamis, 02 Juli 2026</span>
                </div>
                <div className="space-y-4">
                  {state.courses.filter(c => c.scheduleDay === 'Kamis').map(c => (
                    <div key={c.id} className="p-4 border border-slate-100 rounded-xl hover:border-emerald-200 hover:bg-emerald-50/10 transition-all flex justify-between items-center">
                      <div className="flex gap-4">
                        <div className="w-11 h-11 rounded-lg bg-emerald-50 flex flex-col items-center justify-center text-emerald-700 font-bold font-mono text-xs">
                          {c.code.split('-')[0]}
                        </div>
                        <div>
                          <h4 className="font-semibold text-sm text-slate-800">{c.name}</h4>
                          <span className="text-xs text-slate-400 font-mono mt-0.5 block">{c.classRoom} • {c.scheduleTime}</span>
                        </div>
                      </div>
                      <div className="flex gap-2">
                        <button 
                          onClick={() => { setSelectedCourseId(c.id); setActiveTab('attendance'); }}
                          className="px-3.5 py-1.5 bg-white border border-slate-200 hover:border-emerald-500 hover:text-emerald-600 transition-colors rounded-lg text-xs font-medium cursor-pointer"
                        >
                          Absensi
                        </button>
                        <button 
                          onClick={() => { setSelectedCourseId(c.id); setActiveTab('materials'); }}
                          className="px-3.5 py-1.5 bg-emerald-600 text-white hover:bg-emerald-500 transition-colors rounded-lg text-xs font-medium cursor-pointer"
                        >
                          Kelola Sesi
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Recent Submissions */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex flex-col">
                <h3 className="font-display font-bold text-base text-slate-800 mb-6">Penyerahan Tugas Terbaru</h3>
                <div className="space-y-4 flex-1">
                  {state.submissions.slice(0, 3).map(sub => {
                    const linkedCourse = state.courses.find(c => c.id === 'c-3'); // solar tech
                    return (
                      <div key={sub.id} className="flex gap-3 items-start p-3 border border-slate-50 hover:bg-slate-50 rounded-xl transition-all">
                        <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-xs uppercase">
                          {sub.studentName.charAt(0)}
                        </div>
                        <div className="flex-1 min-w-0">
                          <h5 className="font-semibold text-xs text-slate-800 truncate">{sub.studentName}</h5>
                          <span className="text-[10px] text-emerald-600 font-semibold block">{linkedCourse?.name}</span>
                          <span className="text-[10px] text-slate-400 truncate block mt-0.5 font-mono">{sub.fileName}</span>
                        </div>
                        <button 
                          onClick={() => { setSelectedCourseId('c-3'); setActiveTab('grades'); }}
                          className="p-1 text-slate-400 hover:text-emerald-600"
                        >
                          <ChevronRight className="w-4 h-4" />
                        </button>
                      </div>
                    );
                  })}
                </div>
                <div className="pt-4 border-t border-slate-100 mt-4">
                  <button 
                    onClick={() => setActiveTab('grades')}
                    className="w-full text-center text-xs text-emerald-600 font-semibold hover:underline flex items-center justify-center gap-1"
                  >
                    Lihat Semua Penyerahan <ChevronRight className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>

            {/* Announcements section */}
            <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm">
              <h3 className="font-display font-bold text-base text-slate-800 mb-6">Pengumuman Akademik</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {state.announcements.map(ann => (
                  <div key={ann.id} className="p-5 bg-[#F0F7F4]/40 border border-emerald-500/10 rounded-2xl">
                    <span className={`inline-block px-2.5 py-0.5 text-[9px] font-bold rounded-md uppercase tracking-wider font-mono mb-3 ${ann.category === 'academic' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'}`}>
                      {ann.category}
                    </span>
                    <h4 className="font-bold text-sm text-slate-800 mb-1.5">{ann.title}</h4>
                    <p className="text-xs text-slate-500 leading-relaxed line-clamp-2">{ann.content}</p>
                    <div className="mt-4 flex justify-between items-center text-[10px] text-slate-400 font-mono">
                      <span>Oleh: {ann.author}</span>
                      <span>{ann.date}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* TAB 2: ACTIVE CLASSES */}
        {activeTab === 'courses' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center mb-4">
              <p className="text-sm text-slate-500">Mata kuliah yang Anda ampu pada semester aktif ini.</p>
              <button 
                onClick={() => setShowNewCourseModal(true)}
                className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl transition-all shadow-sm active:scale-[0.98] cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                Kelas Baru
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {state.courses.filter(c => c.lecturerName === (state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.')).map(course => (
                <div key={course.id} className="bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all flex flex-col overflow-hidden">
                  <div className="p-6 border-b border-slate-100 flex-1">
                    <div className="flex justify-between items-start mb-4">
                      <span className="text-[10px] font-mono font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">
                        {course.code}
                      </span>
                      <span className="text-xs text-slate-400 font-mono font-semibold">
                        {course.sks} SKS
                      </span>
                    </div>

                    <h4 className="font-display font-bold text-base text-slate-800 mb-2">{course.name}</h4>
                    <p className="text-xs text-slate-400 mb-4 font-mono">{course.classRoom}</p>

                    {/* Progress slider representation */}
                    <div className="space-y-1.5 mt-6">
                      <div className="flex justify-between text-[11px] font-semibold text-slate-600 font-mono">
                        <span>Syllabus Progress</span>
                        <span>{course.progress}%</span>
                      </div>
                      <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div 
                          className="bg-emerald-600 h-2 rounded-full transition-all duration-500" 
                          style={{ width: `${course.progress}%` }}
                        />
                      </div>
                    </div>

                    <div className="mt-4 flex items-center justify-between text-xs text-slate-400 font-mono pt-4 border-t border-slate-50">
                      <span>Siswa: <strong>{course.studentsCount}</strong></span>
                      <span>Hari: <strong>{course.scheduleDay}</strong></span>
                    </div>
                  </div>

                  {/* Actions footer of the card */}
                  <div className="bg-slate-50 p-4 border-t border-slate-100 grid grid-cols-2 gap-3">
                    <button 
                      onClick={() => { setSelectedCourseId(course.id); setActiveTab('materials'); }}
                      className="w-full text-center py-2 bg-white border border-slate-200 hover:border-emerald-600 text-slate-700 hover:text-emerald-600 rounded-xl text-xs font-semibold transition-all cursor-pointer"
                    >
                      Bahan Kuliah
                    </button>
                    <button 
                      onClick={() => { setSelectedCourseId(course.id); setActiveTab('grades'); }}
                      className="w-full text-center py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold transition-all cursor-pointer"
                    >
                      Daftar Nilai
                    </button>
                  </div>
                </div>
              ))}
            </div>

            {/* New Course Creator Modal */}
            {showNewCourseModal && (
              <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                <div className="bg-white rounded-2xl w-full max-w-md p-6 border border-slate-200 shadow-2xl relative">
                  <button 
                    onClick={() => setShowNewCourseModal(false)}
                    className="absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                  >
                    <X className="w-5 h-5" />
                  </button>
                  
                  <h3 className="font-display font-bold text-lg text-slate-900 mb-4">Tambah Kelas Baru</h3>
                  <form onSubmit={handleCreateCourse} className="space-y-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">KODE MATAKULIAH</label>
                      <input 
                        type="text" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-3 text-sm" 
                        placeholder="Contoh: ARC-412"
                        value={newCourseCode}
                        onChange={(e) => setNewCourseCode(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">NAMA MATAKULIAH</label>
                      <input 
                        type="text" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-3 text-sm" 
                        placeholder="Contoh: Energi Angin Terapan"
                        value={newCourseName}
                        onChange={(e) => setNewCourseName(e.target.value)}
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">SKS</label>
                        <select 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-3 text-sm"
                          value={newCourseSKS}
                          onChange={(e) => setNewCourseSKS(Number(e.target.value))}
                        >
                          <option value={2}>2 SKS</option>
                          <option value={3}>3 SKS</option>
                          <option value={4}>4 SKS</option>
                        </select>
                      </div>
                      <div>
                        <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">RUANG</label>
                        <input 
                          type="text" 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-3 text-sm" 
                          placeholder="Contoh: Lab 2B"
                          value={newCourseClass}
                          onChange={(e) => setNewCourseClass(e.target.value)}
                        />
                      </div>
                    </div>
                    
                    <button 
                      type="submit" 
                      className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition-all mt-4 cursor-pointer"
                    >
                      Konfirmasi Tambah Kelas
                    </button>
                  </form>
                </div>
              </div>
            )}
          </div>
        )}

        {/* TAB 3: ATTENDANCE MANAGEMENT */}
        {activeTab === 'attendance' && (
          <div className="space-y-6">
            
            {/* Quick selectors & filters */}
            <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div className="flex flex-wrap items-center gap-4">
                <div>
                  <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 font-mono">KELAS AKTIF</label>
                  <select 
                    className="border border-slate-200 focus:border-emerald-500 rounded-xl px-4 py-2 text-xs font-semibold text-slate-700 bg-transparent"
                    value={selectedCourseId}
                    onChange={(e) => setSelectedCourseId(e.target.value)}
                  >
                    {state.courses.filter(c => c.lecturerName === (state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.')).map(c => (
                      <option key={c.id} value={c.id}>{c.code} - {c.name}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 font-mono">PERTEMUAN KE-</label>
                  <select 
                    className="border border-slate-200 focus:border-emerald-500 rounded-xl px-4 py-2 text-xs font-semibold text-slate-700 bg-transparent font-mono"
                    value={meetingNumber}
                    onChange={(e) => setMeetingNumber(Number(e.target.value))}
                  >
                    {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16].map(num => (
                      <option key={num} value={num}>Pertemuan ke-{num}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="flex gap-2">
                <button 
                  onClick={handleMarkAllPresent}
                  className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer"
                >
                  Set Semua Hadir
                </button>
                <button 
                  onClick={handleGenerateToken}
                  className="px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer"
                >
                  <Key className="w-4 h-4" />
                  Token Mandiri
                </button>
                <button 
                  onClick={handleSaveAttendanceRecap}
                  className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition-all cursor-pointer"
                >
                  Simpan Rekapitulasi
                </button>
              </div>
            </div>

            {/* Attendance rate banner */}
            <div className="bg-emerald-50 border border-emerald-100 p-5 rounded-2xl flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-800 font-bold font-mono">
                  {attendanceSession.attendanceRate}%
                </div>
                <div>
                  <h4 className="font-bold text-sm text-emerald-900">Rasio Kehadiran Sesi {meetingNumber}</h4>
                  <p className="text-xs text-emerald-700 mt-0.5">
                    Sebanyak {attendanceSession.students.filter(s => s.status === 'present').length} dari {attendanceSession.students.length} mahasiswa hadir di kelas.
                  </p>
                </div>
              </div>
              {attendanceSession.token && (
                <div className="bg-white border border-emerald-200 rounded-xl px-4 py-2 text-center shadow-sm">
                  <span className="text-[9px] uppercase tracking-wider text-slate-400 font-bold font-mono block">TOKEN MAHASISWA</span>
                  <span className="text-sm font-mono font-bold text-emerald-600">{attendanceSession.token}</span>
                </div>
              )}
            </div>

            {/* Student registry table for attendance */}
            <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                    <th className="p-4 pl-6">NPM / Identitas</th>
                    <th className="p-4">Nama Lengkap</th>
                    <th className="p-4 text-center">Status Presensi</th>
                    <th className="p-4">Catatan Dosen</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-sm">
                  {attendanceSession.students.length === 0 ? (
                    <tr>
                      <td colSpan={4} className="p-8 text-center text-slate-400 italic">
                        Tidak ada catatan siswa untuk sesi ini. Coba ganti filter kelas ke "Introduction to Solar Tech" Pertemuan ke-12.
                      </td>
                    </tr>
                  ) : (
                    attendanceSession.students.map(std => (
                      <tr key={std.studentId} className="hover:bg-slate-50/50 transition-all">
                        <td className="p-4 pl-6 font-mono text-xs text-slate-500 font-medium">{std.npm}</td>
                        <td className="p-4 font-bold text-slate-800">{std.studentName}</td>
                        <td className="p-4">
                          <div className="flex justify-center gap-2">
                            {/* Radio status options */}
                            <button
                              onClick={() => handleStatusChange(std.studentId, 'present')}
                              className={`w-10 h-8 rounded-lg text-xs font-bold transition-all border flex items-center justify-center cursor-pointer ${std.status === 'present' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-500 border-slate-200'}`}
                              title="Hadir"
                            >
                              H
                            </button>
                            <button
                              onClick={() => handleStatusChange(std.studentId, 'sick')}
                              className={`w-10 h-8 rounded-lg text-xs font-bold transition-all border flex items-center justify-center cursor-pointer ${std.status === 'sick' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-500 border-slate-200'}`}
                              title="Sakit"
                            >
                              S
                            </button>
                            <button
                              onClick={() => handleStatusChange(std.studentId, 'permit')}
                              className={`w-10 h-8 rounded-lg text-xs font-bold transition-all border flex items-center justify-center cursor-pointer ${std.status === 'permit' ? 'bg-blue-500 text-white border-blue-500 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-500 border-slate-200'}`}
                              title="Izin"
                            >
                              I
                            </button>
                            <button
                              onClick={() => handleStatusChange(std.studentId, 'absent')}
                              className={`w-10 h-8 rounded-lg text-xs font-bold transition-all border flex items-center justify-center cursor-pointer ${std.status === 'absent' ? 'bg-red-500 text-white border-red-500 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-500 border-slate-200'}`}
                              title="Alpa"
                            >
                              A
                            </button>
                          </div>
                        </td>
                        <td className="p-4">
                          <input 
                            type="text"
                            placeholder="Catatan keaktifan/alasan..."
                            className="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-lg px-3 py-1.5 text-xs text-slate-700 transition-colors"
                            value={std.notes}
                            onChange={(e) => handleNotesChange(std.studentId, e.target.value)}
                          />
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Token generator modal */}
            {showTokenModal && (
              <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                <div className="bg-white rounded-2xl w-full max-w-sm p-6 border border-slate-200 shadow-2xl relative text-center">
                  <button 
                    onClick={() => setShowTokenModal(false)}
                    className="absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                  >
                    <X className="w-5 h-5" />
                  </button>
                  
                  <div className="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-4">
                    <Key className="w-6 h-6" />
                  </div>
                  
                  <h3 className="font-display font-bold text-lg text-slate-900 mb-2">Self-Attendance Token</h3>
                  <p className="text-xs text-slate-400 mb-6 leading-relaxed px-2">
                    Bagikan token acak berikut ke kelas Anda agar mahasiswa dapat melakukan absensi mandiri melalui portal mahasiswa mereka.
                  </p>
                  
                  <div className="bg-slate-100 rounded-2xl py-4 px-6 border border-slate-200 mb-6 font-mono text-2xl font-bold tracking-widest text-emerald-600 select-all">
                    {tokenText}
                  </div>

                  <p className="text-[10px] font-mono text-amber-600 bg-amber-50 py-1.5 px-3 rounded-lg inline-block">
                    Masa Berlaku Token: <strong>15 Menit</strong>
                  </p>
                  
                  <button 
                    onClick={() => setShowTokenModal(false)}
                    className="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2.5 rounded-xl transition-all mt-6 cursor-pointer"
                  >
                    Selesai & Tutup
                  </button>
                </div>
              </div>
            )}
          </div>
        )}

        {/* TAB 4: MATERIALS & ASSIGNMENT HUB */}
        {activeTab === 'materials' && (
          <div className="space-y-6">
            <form onSubmit={handlePublishMaterials} className="space-y-6">
              
              {/* Publication Context Card */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm space-y-4">
                <h3 className="font-display font-bold text-base text-slate-800">1. Konteks Sesi & Publikasi</h3>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono">MATAKULIAH SASARAN</label>
                    <select 
                      className="w-full border border-slate-200 focus:border-emerald-500 rounded-xl px-4 py-2.5 text-xs text-slate-700 bg-transparent focus:outline-none"
                      value={selectedCourseId}
                      onChange={(e) => setSelectedCourseId(e.target.value)}
                    >
                      {state.courses.filter(c => c.lecturerName === (state.currentUser?.name || 'Dr. Rendra Kusuma, M.T.')).map(c => (
                        <option key={c.id} value={c.id}>{c.code} - {c.name}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono">SESI KULIAH KE-</label>
                    <input 
                      type="number" 
                      min={1} 
                      max={16}
                      required
                      className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-2 text-xs font-mono"
                      value={materialSessionNum}
                      onChange={(e) => setMaterialSessionNum(Number(e.target.value))}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono">PENGATURAN TAYANG</label>
                    <select className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-2.5 text-xs text-slate-700">
                      <option value="immediate">Segera Publikasikan (Instan)</option>
                      <option value="schedule">Jadwalkan Sesuai Jam Kuliah</option>
                      <option value="draft">Simpan Sebagai Draft</option>
                    </select>
                  </div>
                </div>
              </div>

              {/* Course materials and file drag/drop */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm space-y-4">
                <h3 className="font-display font-bold text-base text-slate-800">2. Modul Bahan Kuliah</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono font-semibold">JUDUL MATERI UTAMA</label>
                    <input 
                      type="text" 
                      required
                      placeholder="Masukkan nama modul materi kuliah..."
                      className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-2.5 text-xs"
                      value={materialTitle}
                      onChange={(e) => setMaterialTitle(e.target.value)}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono font-semibold">DESKRIPSI / RENCANA PEMBELAJARAN</label>
                    <textarea 
                      rows={3}
                      placeholder="Tulis ringkasan kompetensi akhir yang diharapkan..."
                      className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-3 text-xs"
                      value={materialDesc}
                      onChange={(e) => setMaterialDesc(e.target.value)}
                    />
                  </div>

                  {/* Drag and Drop area */}
                  <div>
                    <span className="block text-xs font-semibold text-slate-500 uppercase mb-1.5 font-mono">UNGGAH BERKAS MODUL (PDF, PPTX, MP4)</span>
                    <div className="border-2 border-dashed border-slate-200 hover:border-emerald-500 hover:bg-slate-50/50 rounded-2xl p-6 text-center cursor-pointer transition-all relative">
                      <input 
                        type="file" 
                        className="absolute inset-0 opacity-0 cursor-pointer"
                        onChange={(e) => {
                          const file = e.target.files?.[0];
                          if (file) {
                            setSelectedUploadFile(file);
                            setUploadFileName(file.name);
                          }
                        }}
                      />
                      <Upload className="w-8 h-8 text-slate-400 mx-auto mb-3" />
                      <p className="text-xs text-slate-700 font-semibold">
                        {uploadFileName ? `Terpilih: ${uploadFileName}` : "Seret & Taruh berkas di sini, atau Klik untuk memilih"}
                      </p>
                      <span className="text-[10px] text-slate-400 block mt-1">Maksimum ukuran berkas: 25 MB</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Assignment Toggler Card */}
              <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div className="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center">
                  <div className="flex items-center gap-2">
                    <input 
                      type="checkbox" 
                      id="asgCheck"
                      className="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 cursor-pointer"
                      checked={isAssignmentEnabled}
                      onChange={(e) => setIsAssignmentEnabled(e.target.checked)}
                    />
                    <label htmlFor="asgCheck" className="font-display font-bold text-base text-slate-800 cursor-pointer">
                      Sertakan Tugas Kuliah (Assignment)
                    </label>
                  </div>
                  <span className={`px-2 py-0.5 text-[9px] font-bold rounded-md font-mono ${isAssignmentEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500'}`}>
                    {isAssignmentEnabled ? 'AKTIF' : 'NON-AKTIF'}
                  </span>
                </div>

                {isAssignmentEnabled && (
                  <div className="p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono">JUDUL TUGAS</label>
                        <input 
                          type="text" 
                          placeholder="Masukkan judul tugas..."
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-2.5 text-xs font-semibold"
                          value={asgTitle}
                          onChange={(e) => setAsgTitle(e.target.value)}
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono">BATAS PENGUMPULAN (DUE DATE)</label>
                        <input 
                          type="date" 
                          className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-2 text-xs font-mono"
                          value={asgDueDate}
                          onChange={(e) => setAsgDueDate(e.target.value)}
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-semibold text-slate-500 uppercase mb-1 font-mono">PETUNJUK PENGERJAAN TUGAS</label>
                      <textarea 
                        rows={3}
                        placeholder="Tulis instruksi pengerjaan tugas secara rinci..."
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl px-4 py-3 text-xs"
                        value={asgInstructions}
                        onChange={(e) => setAsgInstructions(e.target.value)}
                      />
                    </div>
                  </div>
                )}
              </div>

              {/* Submit panel */}
              <div className="flex justify-end pt-2">
                <button 
                  type="submit"
                  className="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-[0.99] cursor-pointer"
                >
                  Publikasikan Sesi Kuliah Sekarang
                </button>
              </div>

            </form>

            {/* List of Published Materials and Assignments */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-12">
              
              {/* Materials Card */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm space-y-4">
                <div className="flex justify-between items-center pb-2 border-b">
                  <h3 className="font-display font-bold text-base text-slate-800 flex items-center gap-2">
                    <BookOpen className="w-5 h-5 text-emerald-600" />
                    Daftar Materi Kuliah Terbit ({state.materials.filter(m => m.courseId === selectedCourseId).length})
                  </h3>
                  <span className="text-[10px] text-slate-400 font-mono">Dosen Pengampu</span>
                </div>

                <div className="space-y-3 max-h-[400px] overflow-y-auto pr-1">
                  {state.materials.filter(m => m.courseId === selectedCourseId).length === 0 ? (
                    <div className="text-center py-8 text-slate-400 text-xs">Belum ada materi kuliah terbit untuk kelas ini.</div>
                  ) : (
                    state.materials.filter(m => m.courseId === selectedCourseId).map(m => (
                      <div key={m.id} className="p-4 bg-slate-50 border rounded-xl flex justify-between items-start hover:border-emerald-300 transition-all">
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="px-2 py-0.5 text-[9px] bg-emerald-100 text-emerald-800 font-bold rounded font-mono">SESI {m.sessionNumber}</span>
                            <h4 className="font-bold text-slate-900 text-xs">{m.title}</h4>
                          </div>
                          <p className="text-[11px] text-slate-500 line-clamp-2">{m.description || 'Tidak ada deskripsi.'}</p>
                          {m.fileName && (
                            <span className="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-mono mt-1">
                              <FileText className="w-3 h-3" /> {m.fileName}
                            </span>
                          )}
                        </div>
                        <div className="flex items-center gap-1 ml-2 shrink-0">
                          <button 
                            onClick={() => handleOpenEditMaterial(m)}
                            className="p-1.5 hover:bg-slate-200 text-slate-600 rounded-lg text-[10px] font-bold"
                            title="Edit Materi"
                          >
                            Edit
                          </button>
                          <button 
                            onClick={() => handleDeleteMaterial(m.id)}
                            className="p-1.5 hover:bg-red-50 text-red-600 rounded-lg text-[10px] font-bold"
                            title="Hapus Materi"
                          >
                            Hapus
                          </button>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              {/* Assignments Card */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm space-y-4">
                <div className="flex justify-between items-center pb-2 border-b">
                  <h3 className="font-display font-bold text-base text-slate-800 flex items-center gap-2">
                    <Award className="w-5 h-5 text-emerald-600" />
                    Daftar Penugasan Terbit ({state.assignments.filter(a => a.courseId === selectedCourseId).length})
                  </h3>
                  <span className="text-[10px] text-slate-400 font-mono">KRS Aktif</span>
                </div>

                <div className="space-y-3 max-h-[400px] overflow-y-auto pr-1">
                  {state.assignments.filter(a => a.courseId === selectedCourseId).length === 0 ? (
                    <div className="text-center py-8 text-slate-400 text-xs">Belum ada penugasan terbit untuk kelas ini.</div>
                  ) : (
                    state.assignments.filter(a => a.courseId === selectedCourseId).map(a => (
                      <div key={a.id} className="p-4 bg-slate-50 border rounded-xl flex justify-between items-start hover:border-emerald-300 transition-all">
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="px-2 py-0.5 text-[9px] bg-blue-100 text-blue-800 font-bold rounded font-mono">SESI {a.sessionNumber}</span>
                            <h4 className="font-bold text-slate-900 text-xs">{a.title}</h4>
                          </div>
                          <p className="text-[11px] text-slate-500 line-clamp-2">{a.instructions || 'Tidak ada petunjuk.'}</p>
                          <span className="block text-[10px] text-red-600 font-mono mt-1">
                            Deadline: {a.dueDate}
                          </span>
                        </div>
                        <div className="flex items-center gap-1 ml-2 shrink-0">
                          <button 
                            onClick={() => handleOpenEditAssignment(a)}
                            className="p-1.5 hover:bg-slate-200 text-slate-600 rounded-lg text-[10px] font-bold"
                            title="Edit Tugas"
                          >
                            Edit
                          </button>
                          <button 
                            onClick={() => handleDeleteAssignment(a.id)}
                            className="p-1.5 hover:bg-red-50 text-red-600 rounded-lg text-[10px] font-bold"
                            title="Hapus Tugas"
                          >
                            Hapus
                          </button>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

            </div>

            {/* EDIT MATERIAL MODAL */}
            {showEditMaterialModal && (
              <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                <div className="bg-white rounded-2xl w-full max-w-md p-6 border border-slate-200 shadow-2xl relative">
                  <button 
                    onClick={() => setShowEditMaterialModal(false)}
                    className="absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                  >
                    <X className="w-5 h-5" />
                  </button>
                  
                  <h3 className="font-display font-bold text-lg text-slate-900 mb-4">Edit Materi Kuliah</h3>
                  <form onSubmit={handleSaveMaterialEdit} className="space-y-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">SESI KE-</label>
                      <input 
                        type="number" 
                        min={1}
                        max={16}
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs font-mono"
                        value={editMaterialSessionNum}
                        onChange={(e) => setEditMaterialSessionNum(Number(e.target.value))}
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">JUDUL MATERI</label>
                      <input 
                        type="text" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs font-semibold"
                        value={editMaterialTitle}
                        onChange={(e) => setEditMaterialTitle(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">DESKRIPSI / RENCANA BELAJAR</label>
                      <textarea 
                        rows={4}
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs"
                        value={editMaterialDesc}
                        onChange={(e) => setEditMaterialDesc(e.target.value)}
                      />
                    </div>
                    
                    <button 
                      type="submit" 
                      className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition-all mt-4 cursor-pointer"
                    >
                      Simpan Perubahan Materi
                    </button>
                  </form>
                </div>
              </div>
            )}

            {/* EDIT ASSIGNMENT MODAL */}
            {showEditAssignmentModal && (
              <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                <div className="bg-white rounded-2xl w-full max-w-md p-6 border border-slate-200 shadow-2xl relative">
                  <button 
                    onClick={() => setShowEditAssignmentModal(false)}
                    className="absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                  >
                    <X className="w-5 h-5" />
                  </button>
                  
                  <h3 className="font-display font-bold text-lg text-slate-900 mb-4">Edit Penugasan Kuliah</h3>
                  <form onSubmit={handleSaveAssignmentEdit} className="space-y-4">
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">JUDUL PENUGASAN</label>
                      <input 
                        type="text" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs font-semibold"
                        value={editAssignmentTitle}
                        onChange={(e) => setEditAssignmentTitle(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">BATAS PENGUMPULAN</label>
                      <input 
                        type="date" 
                        required
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs font-mono"
                        value={editAssignmentDueDate}
                        onChange={(e) => setEditAssignmentDueDate(e.target.value)}
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-slate-600 uppercase mb-1">PETUNJUK PENGERJAAN</label>
                      <textarea 
                        rows={4}
                        className="w-full border border-slate-200 focus:border-emerald-500 focus:outline-none rounded-xl p-2.5 text-xs"
                        value={editAssignmentInstructions}
                        onChange={(e) => setEditAssignmentInstructions(e.target.value)}
                      />
                    </div>
                    
                    <button 
                      type="submit" 
                      className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition-all mt-4 cursor-pointer"
                    >
                      Simpan Perubahan Tugas
                    </button>
                  </form>
                </div>
              </div>
            )}

          </div>
        )}

        {/* TAB 5: GRADE MANAGEMENT */}
        {activeTab === 'grades' && (
          <div className="space-y-6">
            
            {/* Statistics and Distribution summary cards */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              {/* Score breakdown metrics */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex flex-col justify-between">
                <div>
                  <h3 className="font-display font-bold text-base text-slate-800 mb-4">Statistik Kelas Aktif</h3>
                  <div className="space-y-4">
                    <div className="flex justify-between items-center p-3.5 bg-slate-50 rounded-xl">
                      <span className="text-xs text-slate-500 font-medium">Nilai Rata-rata Kelas</span>
                      <strong className="text-lg font-mono font-bold text-slate-800">{averageScore}</strong>
                    </div>
                    <div className="flex justify-between items-center p-3.5 bg-slate-50 rounded-xl">
                      <span className="text-xs text-slate-500 font-medium">Tingkat Kelulusan (Pass Rate)</span>
                      <strong className="text-lg font-mono font-bold text-emerald-600">{passRate}%</strong>
                    </div>
                  </div>
                </div>
                <div className="mt-4 p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-800">
                  Ambang batas kelulusan akademik (KKM): <strong className="font-bold">60.0</strong> (Grade D)
                </div>
              </div>

              {/* Grade Weightings */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm">
                <h3 className="font-display font-bold text-base text-slate-800 mb-4">Bobot Penilaian (%)</h3>
                <div className="space-y-3 font-mono text-xs text-slate-600">
                  <div className="flex justify-between items-center">
                    <span>Partisipasi & Absensi:</span>
                    <strong className="text-slate-800">{courseGradeSet?.weights.attendance}%</strong>
                  </div>
                  <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div className="bg-blue-500 h-1.5" style={{ width: `${courseGradeSet?.weights.attendance}%` }} />
                  </div>
                  
                  <div className="flex justify-between items-center pt-1">
                    <span>Tugas & Kuis Mandiri:</span>
                    <strong className="text-slate-800">{courseGradeSet?.weights.assignments}%</strong>
                  </div>
                  <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div className="bg-amber-500 h-1.5" style={{ width: `${courseGradeSet?.weights.assignments}%` }} />
                  </div>

                  <div className="flex justify-between items-center pt-1">
                    <span>Ujian Tengah Semester (UTS):</span>
                    <strong className="text-slate-800">{courseGradeSet?.weights.uts}%</strong>
                  </div>
                  <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div className="bg-indigo-500 h-1.5" style={{ width: `${courseGradeSet?.weights.uts}%` }} />
                  </div>

                  <div className="flex justify-between items-center pt-1">
                    <span>Ujian Akhir Semester (UAS):</span>
                    <strong className="text-slate-800">{courseGradeSet?.weights.uas}%</strong>
                  </div>
                  <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div className="bg-emerald-600 h-1.5" style={{ width: `${courseGradeSet?.weights.uas}%` }} />
                  </div>
                </div>
              </div>

              {/* Grade distribution SVG representation */}
              <div className="bg-white p-6 border border-slate-200 rounded-2xl shadow-sm flex flex-col justify-between">
                <h3 className="font-display font-bold text-base text-slate-800 mb-2">Distribusi Nilai Akhir</h3>
                
                {/* SVG Chart */}
                <div className="flex-1 flex items-end justify-between h-24 pt-4 px-2 font-mono text-xs">
                  {Object.entries(distribution).map(([letter, count]) => {
                    const max = Math.max(...Object.values(distribution)) || 1;
                    const pct = (count / max) * 100;
                    return (
                      <div key={letter} className="flex flex-col items-center flex-1 gap-2 group">
                        <span className="text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity font-bold">{count} orang</span>
                        <div className="w-7 bg-emerald-600/20 group-hover:bg-emerald-600 transition-all rounded-t-lg relative" style={{ height: `${pct}%`, minHeight: '4px' }}>
                          <div className="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.2),transparent)] rounded-t-lg" />
                        </div>
                        <span className="font-bold text-slate-700">{letter}</span>
                      </div>
                    );
                  })}
                </div>
              </div>

            </div>

            {/* Editable Grades list */}
            <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
              <div className="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 className="font-display font-bold text-base text-slate-800">Registrasi Mahasiswa & Nilai Akhir</h3>
                <span className="text-xs text-slate-400 font-mono">Modul input interaktif (Nilai UTS / UAS dapat disunting langsung)</span>
              </div>
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
                    <th className="p-4 pl-6">NPM</th>
                    <th className="p-4">Nama Lengkap</th>
                    <th className="p-4 text-center">Absensi (10%)</th>
                    <th className="p-4 text-center">Tugas (20%)</th>
                    <th className="p-4 text-center">UTS (30%)</th>
                    <th className="p-4 text-center">UAS (40%)</th>
                    <th className="p-4 text-center">Nilai Akhir</th>
                    <th className="p-4 text-center pr-6">Grade</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-sm">
                  {gradesList.map(sg => (
                    <tr key={sg.studentId} className="hover:bg-slate-50/40 transition-all">
                      <td className="p-4 pl-6 font-mono text-xs text-slate-400">{sg.npm}</td>
                      <td className="p-4 font-bold text-slate-800">{sg.studentName}</td>
                      <td className="p-4 text-center font-mono font-medium">{sg.attendanceScore}</td>
                      <td className="p-4 text-center font-mono font-medium">{sg.assignmentScore}</td>
                      
                      {/* Interactive UTS */}
                      <td className="p-4 text-center">
                        <input 
                          type="number" 
                          className="w-16 text-center font-mono font-semibold bg-slate-50 border border-slate-200 hover:border-slate-300 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-lg px-2 py-1 text-xs"
                          value={sg.utsScore}
                          onChange={(e) => handleGradeChange(sg.studentId, 'utsScore', Number(e.target.value))}
                        />
                      </td>

                      {/* Interactive UAS */}
                      <td className="p-4 text-center">
                        <input 
                          type="number" 
                          className="w-16 text-center font-mono font-semibold bg-slate-50 border border-slate-200 hover:border-slate-300 focus:border-emerald-500 focus:bg-white focus:outline-none rounded-lg px-2 py-1 text-xs"
                          value={sg.uasScore}
                          onChange={(e) => handleGradeChange(sg.studentId, 'uasScore', Number(e.target.value))}
                        />
                      </td>

                      <td className="p-4 text-center font-mono font-bold text-slate-900">{sg.finalScore}</td>
                      <td className="p-4 text-center pr-6">
                        <span className={`inline-block w-8 py-1 rounded-md text-xs font-bold text-center ${sg.gradeLetter === 'A' ? 'bg-emerald-100 text-emerald-800' : sg.gradeLetter === 'B' ? 'bg-blue-100 text-blue-800' : sg.gradeLetter === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'}`}>
                          {sg.gradeLetter}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

          </div>
        )}

      </main>
    </div>
  );
}
