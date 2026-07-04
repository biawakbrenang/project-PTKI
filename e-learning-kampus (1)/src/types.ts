export type Role = 'lecturer' | 'student' | 'admin';

export interface User {
  id: string;
  name: string;
  role: Role;
  email: string;
  avatarUrl: string;
  identifier: string; // NIDN for lecturer, NPM for student, NIP for admin
  originalIdentifier?: string; // Original SQL identifier before custom NPM pattern
  password?: string; // Password from the SQL database
  department?: string;
  semester?: number;
  phone?: string;
  suspended?: boolean;
  enrolledCourseIds?: string[];
}

export interface Course {
  id: string;
  code: string;
  name: string;
  lecturerName: string;
  studentsCount: number;
  progress: number; // percentage
  sks: number;
  classRoom: string;
  scheduleDay: string;
  scheduleTime: string;
}

export interface AttendanceStudent {
  studentId: string;
  studentName: string;
  npm: string;
  status: 'present' | 'sick' | 'permit' | 'absent';
  notes: string;
}

export interface AttendanceSession {
  courseId: string;
  subject: string;
  meetingNumber: number;
  attendanceRate: number;
  students: AttendanceStudent[];
  token?: string;
}

export interface Material {
  id: string;
  courseId: string;
  sessionNumber: number;
  title: string;
  description: string;
  fileUrl?: string;
  fileName?: string;
  fileSize?: string;
  videoUrl?: string;
  externalLink?: string;
  isPublished: boolean;
  publishDate?: string;
}

export interface Assignment {
  id: string;
  courseId: string;
  sessionNumber: number;
  title: string;
  instructions: string;
  dueDate: string;
  allowedFormats: string[];
  maxScore: number;
  isPublished: boolean;
}

export interface Submission {
  id: string;
  assignmentId: string;
  studentId: string;
  studentName: string;
  submittedAt: string;
  fileName: string;
  fileSize: string;
  status: 'submitted' | 'graded';
  score?: number;
  feedback?: string;
  comments: {
    authorName: string;
    text: string;
    timestamp: string;
  }[];
}

export interface StudentGrade {
  studentId: string;
  studentName: string;
  npm: string;
  attendanceScore: number; // out of 100
  assignmentScore: number; // out of 100
  utsScore: number; // out of 100
  uasScore: number; // out of 100
  finalScore?: number;
  gradeLetter?: 'A' | 'B' | 'C' | 'D' | 'F';
}

export interface CourseGrades {
  courseId: string;
  weights: {
    attendance: number; // default 10%
    assignments: number; // default 20%
    uts: number; // default 30%
    uas: number; // default 40%
  };
  studentGrades: StudentGrade[];
}

export interface Announcement {
  id: string;
  title: string;
  content: string;
  category: 'academic' | 'general' | 'event';
  date: string;
  author: string;
}

export interface ActivityLog {
  id: string;
  actor: string;
  role: string;
  action: string;
  timestamp: string;
}

export interface SystemStats {
  totalStudents: number;
  totalLecturers: number;
  activeCourses: number;
}

export interface EcoAppState {
  currentUser: User | null;
  currentRole: Role | null;
  users: User[];
  courses: Course[];
  attendanceSessions: AttendanceSession[];
  materials: Material[];
  assignments: Assignment[];
  submissions: Submission[];
  courseGrades: CourseGrades[];
  announcements: Announcement[];
  activityLogs: ActivityLog[];
  systemStats: SystemStats;
  streakDays: number;
  academicProgressIndex: number; // 0-based learning step progress index
}

export const RAW_SQL_USERS = `
(1, '15123416', 'Admin658', 'Administrator Akademik LPTIK', 'admin.lptik@unas.ac.id', 'admin', 'aktif'),
(2, '15123417', 'Endang587', 'Dra. Endang Sulistyowati', 'endang.s@unas.ac.id', 'admin', 'aktif'),
(3, '35112001', 'Rendra996', 'Dr. Rendra Kusuma, M.T.', 'rendra.kusuma@unas.ac.id', 'dosen', 'aktif'),
(4, '35112002', 'H755', 'Ir. H. Gunawan Wibisono, M.T.', 'gunawan.wibisono@unas.ac.id', 'dosen', 'aktif'),
(5, '35112003', 'Indah646', 'Dr. Indah Lestari, M.Kom.', 'indah.lestari@unas.ac.id', 'dosen', 'aktif'),
(6, '35112004', 'Siti825', 'Siti Rahmawati, S.Kom., M.MSI.', 'siti.rahmawati@unas.ac.id', 'dosen', 'aktif'),
(7, '35112005', 'Budi337', 'Budi Hermawan, M.T.', 'budi.hermawan@unas.ac.id', 'dosen', 'aktif'),
(8, '35112006', 'Yusuf843', 'Yusuf Wijaya, M.Kom.', 'yusuf.wijaya@unas.ac.id', 'dosen', 'aktif'),
(9, '25211101', 'Ahmad689', 'Ahmad Pratama', 'ahmad.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(10, '25211002', 'Ahmad822', 'Ahmad Pratama', 'ahmad.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(11, '25211003', 'Budi688', 'Budi Pratama', 'budi.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(12, '25211004', 'Chandra638', 'Chandra Pratama', 'chandra.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(13, '25211005', 'Dedi213', 'Dedi Pratama', 'dedi.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(14, '25211006', 'Eko455', 'Eko Pratama', 'eko.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(15, '25211007', 'Fajar452', 'Fajar Pratama', 'fajar.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(16, '25211008', 'Guntur251', 'Guntur Pratama', 'guntur.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(17, '25211009', 'Hendra177', 'Hendra Pratama', 'hendra.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(18, '25211010', 'Iwan679', 'Iwan Pratama', 'iwan.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(19, '25211011', 'Joko234', 'Joko Pratama', 'joko.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(20, '25211012', 'Kurniawan144', 'Kurniawan Pratama', 'kurniawan.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(21, '25211013', 'Lutfi912', 'Lutfi Pratama', 'lutfi.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(22, '25211014', 'Mulyono269', 'Mulyono Pratama', 'mulyono.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(23, '25211015', 'Novi919', 'Novi Pratama', 'novi.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(24, '25211016', 'Oki291', 'Oki Pratama', 'oki.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(25, '25211017', 'Prabowo189', 'Prabowo Pratama', 'prabowo.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(26, '25211018', 'Rian954', 'Rian Pratama', 'rian.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(27, '25211019', 'Setyawan717', 'Setyawan Pratama', 'setyawan.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(28, '25211020', 'Taufik898', 'Taufik Pratama', 'taufik.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(29, '25211021', 'Umar311', 'Umar Pratama', 'umar.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(30, '25211022', 'Wahyu184', 'Wahyu Pratama', 'wahyu.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(31, '25211023', 'Yanto431', 'Yanto Pratama', 'yanto.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(32, '25211024', 'Zainal589', 'Zainal Pratama', 'zainal.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(33, '25211025', 'Aditya559', 'Aditya Pratama', 'aditya.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(34, '25211026', 'Bagus995', 'Bagus Pratama', 'bagus.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(35, '25211027', 'Cahyo863', 'Cahyo Pratama', 'cahyo.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(36, '25211028', 'Dharma472', 'Dharma Pratama', 'dharma.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(37, '25211029', 'Erwin247', 'Erwin Pratama', 'erwin.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(38, '25211030', 'Farhan833', 'Farhan Pratama', 'farhan.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(39, '25211031', 'Gilang543', 'Gilang Pratama', 'gilang.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(40, '25211032', 'Hari722', 'Hari Pratama', 'hari.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(41, '25211033', 'Indra477', 'Indra Pratama', 'indra.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(42, '25211034', 'Joni814', 'Joni Pratama', 'joni.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(43, '25211035', 'Kevin664', 'Kevin Pratama', 'kevin.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(44, '25211036', 'Lukman615', 'Lukman Pratama', 'lukman.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(45, '25211037', 'Mahendra483', 'Mahendra Pratama', 'mahendra.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(46, '25211038', 'Nugroho767', 'Nugroho Pratama', 'nugroho.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(47, '25211039', 'Oka635', 'Oka Pratama', 'oka.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(48, '25211040', 'Putra424', 'Putra Pratama', 'putra.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(49, '25211041', 'Rendy578', 'Rendy Pratama', 'rendy.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(50, '25211042', 'Soni417', 'Soni Pratama', 'soni.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(51, '25211043', 'Teguh557', 'Teguh Pratama', 'teguh.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(52, '25211044', 'Utomo871', 'Utomo Pratama', 'utomo.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(53, '25211045', 'Wawan931', 'Wawan Pratama', 'wawan.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(54, '25211046', 'Yudi273', 'Yudi Pratama', 'yudi.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(55, '25211047', 'Zul282', 'Zul Pratama', 'zul.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(56, '25211048', 'Aris661', 'Aris Pratama', 'aris.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(57, '25211049', 'Beni817', 'Beni Pratama', 'beni.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(58, '25211050', 'Dani437', 'Dani Pratama', 'dani.pratama@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(59, '25221001', 'Budi459', 'Budi Santoso', 'budi.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(60, '25221002', 'Chandra249', 'Chandra Santoso', 'chandra.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(61, '25221003', 'Dedi712', 'Dedi Santoso', 'dedi.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(62, '25221004', 'Eko978', 'Eko Santoso', 'eko.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(63, '25221005', 'Fajar697', 'Fajar Santoso', 'fajar.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(64, '25221006', 'Guntur449', 'Guntur Santoso', 'guntur.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(65, '25221007', 'Hendra252', 'Hendra Santoso', 'hendra.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(66, '25221008', 'Iwan462', 'Iwan Santoso', 'iwan.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(67, '25221009', 'Joko568', 'Joko Santoso', 'joko.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(68, '25221010', 'Kurniawan521', 'Kurniawan Santoso', 'kurniawan.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(69, '25221011', 'Lutfi582', 'Lutfi Santoso', 'lutfi.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(70, '25221012', 'Mulyono447', 'Mulyono Santoso', 'mulyono.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(71, '25221013', 'Novi737', 'Novi Santoso', 'novi.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(72, '25221014', 'Oki766', 'Oki Santoso', 'oki.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(73, '25221015', 'Prabowo933', 'Prabowo Santoso', 'prabowo.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(74, '25221016', 'Rian199', 'Rian Santoso', 'rian.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(75, '25221017', 'Setyawan723', 'Setyawan Santoso', 'setyawan.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(76, '25221018', 'Taufik271', 'Taufik Santoso', 'taufik.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(77, '25221019', 'Umar694', 'Umar Santoso', 'umar.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(78, '25221020', 'Wahyu499', 'Wahyu Santoso', 'wahyu.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(79, '25221021', 'Yanto433', 'Yanto Santoso', 'yanto.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(80, '25221022', 'Zainal352', 'Zainal Santoso', 'zainal.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(81, '25221023', 'Aditya788', 'Aditya Santoso', 'aditya.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(82, '25221024', 'Bagus969', 'Bagus Santoso', 'bagus.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(83, '25221025', 'Cahyo194', 'Cahyo Santoso', 'cahyo.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(84, '25221026', 'Dharma598', 'Dharma Santoso', 'dharma.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(85, '25221027', 'Erwin866', 'Erwin Santoso', 'erwin.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(86, '25221028', 'Farhan553', 'Farhan Santoso', 'farhan.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(87, '25221029', 'Gilang787', 'Gilang Santoso', 'gilang.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(88, '25221030', 'Hari116', 'Hari Santoso', 'hari.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(89, '25221031', 'Indra667', 'Indra Santoso', 'indra.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(90, '25221032', 'Joni357', 'Joni Santoso', 'joni.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(91, '25221033', 'Kevin695', 'Kevin Santoso', 'kevin.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(92, '25221034', 'Lukman547', 'Lukman Santoso', 'lukman.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(93, '25221035', 'Mahendra854', 'Mahendra Santoso', 'mahendra.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(94, '25221036', 'Nugroho847', 'Nugroho Santoso', 'nugroho.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(95, '25221037', 'Oka556', 'Oka Santoso', 'oka.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(96, '25221038', 'Putra551', 'Putra Santoso', 'putra.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(97, '25221039', 'Rendy873', 'Rendy Santoso', 'rendy.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(98, '25221040', 'Soni885', 'Soni Santoso', 'soni.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(99, '25221041', 'Teguh792', 'Teguh Santoso', 'teguh.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(100, '25221042', 'Utomo493', 'Utomo Santoso', 'utomo.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(101, '25221043', 'Wawan968', 'Wawan Santoso', 'wawan.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(102, '25221044', 'Yudi389', 'Yudi Santoso', 'yudi.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(103, '25221045', 'Zul518', 'Zul Santoso', 'zul.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(104, '25221046', 'Aris853', 'Aris Santoso', 'aris.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(105, '25221047', 'Beni178', 'Beni Santoso', 'beni.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(106, '25221048', 'Dani548', 'Dani Santoso', 'dani.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(107, '25221049', 'Eka442', 'Eka Santoso', 'eka.santoso@mhs.unas.ac.id', 'mahasiswa', 'aktif'),
(108, '25221050', 'Ahmad586', 'Ahmad Wijaya', 'ahmad.wijaya@mhs.unas.ac.id', 'mahasiswa', 'aktif')
`;

export function parseSQLUsers(raw: string): User[] {
  const users: User[] = [];
  const lines = raw.trim().split('\n');
  for (const line of lines) {
    if (!line.trim()) continue;
    // Match: (id, 'username', 'password', 'nama_lengkap', 'email', 'role', 'status')
    const match = line.match(/\(\s*(\d+)\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*\)/);
    if (match) {
      const [, sId, username, password, name, email, roleVal, status] = match;
      const isSuspended = status === 'nonaktif';
      
      let role: Role = 'student';
      if (roleVal === 'admin') role = 'admin';
      else if (roleVal === 'dosen') role = 'lecturer';
      else if (roleVal === 'mahasiswa') role = 'student';

      let identifier = username;
      const originalIdentifier = username;
      let department = '';

      if (role === 'student') {
        // Map student NPM to requested format: e.g. 25211008 -> 2204101008
        if (username.startsWith('252110')) {
          identifier = username.replace('252110', '22041010');
        } else if (username.startsWith('252111')) {
          identifier = username.replace('252111', '22041011');
        } else if (username.startsWith('252210')) {
          identifier = username.replace('252210', '22041020');
        } else if (username.startsWith('25211')) {
          identifier = username.replace('25211', '2204101');
        } else if (username.startsWith('25221')) {
          identifier = username.replace('25221', '2204102');
        }
        
        if (identifier.startsWith('2204101')) {
          department = 'Sistem Informasi';
        } else if (identifier.startsWith('2204102')) {
          department = 'Teknik Informatika';
        } else {
          department = 'Sistem Informasi';
        }
      } else if (role === 'lecturer') {
        const docentsSI = ['35112001', '35112002', '35112003', '35112004'];
        department = docentsSI.includes(username) ? 'Sistem Informasi' : 'Teknik Informatika';
      } else {
        department = 'Akademik Kampus';
      }

      // Generate Unsplash avatars based on IDs
      const avatarUrl = role === 'student'
        ? `https://images.unsplash.com/photo-${1500000000000 + Number(sId) * 100000}?auto=format&fit=crop&q=80&w=200`
        : role === 'lecturer'
          ? `https://images.unsplash.com/photo-${1472099645785 + Number(sId) * 50000}?auto=format&fit=crop&q=80&w=200`
          : `https://images.unsplash.com/photo-1534528741775?auto=format&fit=crop&q=80&w=200`;

      let enrolledCourseIds: string[] | undefined = undefined;
      if (role === 'student') {
        if (identifier.startsWith('2204101')) {
          enrolledCourseIds = ['c-1', 'c-2', 'c-3'];
        } else if (identifier.startsWith('2204102')) {
          enrolledCourseIds = ['c-4', 'c-5'];
        } else {
          enrolledCourseIds = ['c-1', 'c-2', 'c-3'];
        }
      }

      users.push({
        id: role === 'student' ? `s-${sId}` : role === 'lecturer' ? `l-${sId}` : `a-${sId}`,
        name,
        role,
        email,
        avatarUrl,
        identifier,
        originalIdentifier,
        password,
        department,
        semester: role === 'student' ? 1 : undefined,
        suspended: isSuspended,
        enrolledCourseIds
      });
    }
  }
  return users;
}

export const ALL_USERS: User[] = parseSQLUsers(RAW_SQL_USERS);

// Initial Mock Data matching the 14 reference screens precisely
export function getInitialState(): EcoAppState {
  const users = ALL_USERS;

  const courses: Course[] = [
    {
      id: 'c-1',
      code: 'SI101',
      name: 'Algoritma & Pemrograman',
      lecturerName: 'Dr. Rendra Kusuma, M.T.',
      studentsCount: 50,
      progress: 85,
      sks: 3,
      classRoom: 'Ruang Lab Rekayasa Perangkat Lunak',
      scheduleDay: 'Senin',
      scheduleTime: '08:00 - 09:40'
    },
    {
      id: 'c-2',
      code: 'SI102',
      name: 'Basis Data Terdistribusi',
      lecturerName: 'Dr. Rendra Kusuma, M.T.',
      studentsCount: 50,
      progress: 60,
      sks: 3,
      classRoom: 'Ruang Lab Jaringan Terdistribusi',
      scheduleDay: 'Selasa',
      scheduleTime: '10:00 - 11:40'
    },
    {
      id: 'c-3',
      code: 'SI103',
      name: 'Sistem Informasi Manajemen',
      lecturerName: 'Dr. Indah Lestari, M.Kom.',
      studentsCount: 50,
      progress: 92,
      sks: 2,
      classRoom: 'Gedung Kuliah Utama Ruang 302',
      scheduleDay: 'Rabu',
      scheduleTime: '13:00 - 14:40'
    },
    {
      id: 'c-4',
      code: 'TI101',
      name: 'Pemrograman Berorientasi Objek',
      lecturerName: 'Ir. H. Gunawan Wibisono, M.T.',
      studentsCount: 50,
      progress: 75,
      sks: 3,
      classRoom: 'Ruang Lab Komputer 2',
      scheduleDay: 'Kamis',
      scheduleTime: '08:00 - 09:40'
    },
    {
      id: 'c-5',
      code: 'TI102',
      name: 'Kecerdasan Buatan',
      lecturerName: 'Budi Hermawan, M.T.',
      studentsCount: 50,
      progress: 50,
      sks: 3,
      classRoom: 'Gedung Pascasarjana Ruang 105',
      scheduleDay: 'Jumat',
      scheduleTime: '14:00 - 15:40'
    },
    {
      id: 'c-6',
      code: 'TI103',
      name: 'Jaringan Komputer',
      lecturerName: 'Yusuf Wijaya, M.Kom.',
      studentsCount: 50,
      progress: 40,
      sks: 3,
      classRoom: 'Ruang Lab Jaringan Terdistribusi',
      scheduleDay: 'Selasa',
      scheduleTime: '13:00 - 14:40'
    }
  ];

  // Helper to find all student details enrolled in a course
  const getEnrolledStudents = (courseId: string) => {
    return users
      .filter((u) => u.role === 'student' && u.enrolledCourseIds?.includes(courseId))
      .map((u) => ({
        id: u.id,
        name: u.name,
        npm: u.identifier
      }));
  };

  // Dynamic Attendance matching student list
  const attendanceSessions: AttendanceSession[] = [
    {
      courseId: 'c-1',
      subject: 'Algoritma & Pemrograman',
      meetingNumber: 1,
      attendanceRate: 94,
      token: 'ALG-101A',
      students: getEnrolledStudents('c-1').slice(0, 15).map((s, idx) => ({
        studentId: s.id,
        studentName: s.name,
        npm: s.npm,
        status: idx === 3 ? 'sick' : idx === 7 ? 'absent' : 'present',
        notes: idx === 3 ? 'Izin sakit dengan surat dokter' : idx === 7 ? 'Tanpa kabar' : ''
      }))
    },
    {
      courseId: 'c-3',
      subject: 'Sistem Informasi Manajemen',
      meetingNumber: 1,
      attendanceRate: 98,
      token: 'SIM-103A',
      students: getEnrolledStudents('c-3').slice(0, 15).map((s, idx) => ({
        studentId: s.id,
        studentName: s.name,
        npm: s.npm,
        status: idx === 5 ? 'sick' : 'present',
        notes: idx === 5 ? 'Izin sakit' : ''
      }))
    },
    {
      courseId: 'c-4',
      subject: 'Pemrograman Berorientasi Objek',
      meetingNumber: 1,
      attendanceRate: 96,
      token: 'PBO-101A',
      students: getEnrolledStudents('c-4').slice(0, 15).map((s, idx) => ({
        studentId: s.id,
        studentName: s.name,
        npm: s.npm,
        status: idx === 2 ? 'sick' : 'present',
        notes: idx === 2 ? 'Sakit flu berat' : ''
      }))
    }
  ];

  const materials: Material[] = [
    {
      id: 'm-1',
      courseId: 'c-1',
      sessionNumber: 1,
      title: 'Pengenalan Logika Algoritma',
      description: 'Sesi perdana menerangkan flowchart, psuedocode, dan logika percabangan IF-ELSE.',
      fileName: 'Logika_Algoritma_Sesi01.pdf',
      fileSize: '3.5 MB',
      fileUrl: '#',
      videoUrl: 'https://www.w3schools.com/html/mov_bbb.mp4',
      isPublished: true,
      publishDate: '2026-07-02'
    },
    {
      id: 'm-2',
      courseId: 'c-1',
      sessionNumber: 2,
      title: 'Struktur Kontrol Loop',
      description: 'Pembahasan iterasi menggunakan FOR, WHILE, dan DO-WHILE.',
      fileName: 'Struktur_Loop_Sesi02.pdf',
      fileSize: '4.2 MB',
      fileUrl: '#',
      isPublished: true,
      publishDate: '2026-07-09'
    },
    {
      id: 'm-3',
      courseId: 'c-3',
      sessionNumber: 1,
      title: 'Konsep Dasar Sistem Informasi',
      description: 'Memahami definisi, komponen, serta peran strategis sistem informasi bagi pengambilan keputusan bisnis modern.',
      fileName: 'SIM_Sesi01_Konsep_Dasar.pdf',
      fileSize: '5.1 MB',
      fileUrl: '#',
      isPublished: true,
      publishDate: '2026-07-02'
    },
    {
      id: 'm-4',
      courseId: 'c-4',
      sessionNumber: 1,
      title: 'Pengenalan Pemrograman Berorientasi Objek',
      description: 'Sesi pertama membahas Class, Object, Attribute, Method, dan Enkapsulasi Dasar.',
      fileName: 'PBO_Sesi01_Intro.pdf',
      fileSize: '4.8 MB',
      fileUrl: '#',
      isPublished: true,
      publishDate: '2026-07-02'
    }
  ];

  const assignments: Assignment[] = [
    {
      id: 'asg-1',
      courseId: 'c-1',
      sessionNumber: 1,
      title: 'Tugas Mandiri 1: Implementasi Flowchart',
      instructions: 'Buatlah diagram alir flowchart dan pseudocode untuk menentukan bilangan ganjil-genap dan mengurutkan 3 bilangan acak.',
      dueDate: '2026-07-15',
      allowedFormats: ['PDF', 'DOCX'],
      maxScore: 100,
      isPublished: true
    },
    {
      id: 'asg-2',
      courseId: 'c-3',
      sessionNumber: 1,
      title: 'Tugas Mandiri 1: Analisis Sistem Informasi Perusahaan',
      instructions: 'Pilihlah satu perusahaan digital (e.g. Gojek, Tokopedia, Grab) lalu analisislah 5 komponen SI penyusun layanannya.',
      dueDate: '2026-07-16',
      allowedFormats: ['PDF'],
      maxScore: 100,
      isPublished: true
    }
  ];

  const submissions: Submission[] = [
    {
      id: 'sub-1',
      assignmentId: 'asg-1',
      studentId: 's-9', // Ahmad Pratama
      studentName: 'Ahmad Pratama',
      submittedAt: '2026-07-02 14:32',
      fileName: 'Tugas_Flowchart_Ahmad_Pratama.pdf',
      fileSize: '2.1 MB',
      status: 'submitted',
      comments: [
        {
          authorName: 'Ahmad Pratama',
          text: 'Yth. Bapak Rendra, berikut adalah tugas mandiri 1 flowchart saya. Mohon bimbingannya, terima kasih.',
          timestamp: '2026-07-02 14:35'
        }
      ]
    }
  ];

  const courseGrades: CourseGrades[] = ['c-1', 'c-2', 'c-3', 'c-4', 'c-5', 'c-6'].map((cId) => {
    const courseStudents = getEnrolledStudents(cId);
    return {
      courseId: cId,
      weights: {
        attendance: 10,
        assignments: 20,
        uts: 30,
        uas: 40
      },
      studentGrades: courseStudents.map((s, idx) => {
        const base = 85 - (idx % 15) * 1.2 + Math.sin(idx) * 6;
        const attendanceScore = (idx % 12) === 4 ? 80 : 100;
        const assignmentScore = Math.min(100, Math.round(base + 4));
        const utsScore = Math.min(100, Math.round(base - 2));
        const uasScore = Math.min(100, Math.round(base + 1));
        const finalScore = Math.round(
          (attendanceScore * 0.1) +
          (assignmentScore * 0.2) +
          (utsScore * 0.3) +
          (uasScore * 0.4)
        );
        let gradeLetter: 'A' | 'B' | 'C' | 'D' | 'F' = 'A';
        if (finalScore < 50) gradeLetter = 'F';
        else if (finalScore < 60) gradeLetter = 'D';
        else if (finalScore < 75) gradeLetter = 'C';
        else if (finalScore < 85) gradeLetter = 'B';

        return {
          studentId: s.id,
          studentName: s.name,
          npm: s.npm,
          attendanceScore,
          assignmentScore,
          utsScore,
          uasScore,
          finalScore,
          gradeLetter
        };
      })
    };
  });

  const announcements: Announcement[] = [
    {
      id: 'ann-1',
      title: 'Ujian Tengah Semester Ganjil Dimulai',
      content: 'Diberitahukan kepada seluruh mahasiswa bahwa UTS Semester Ganjil akan dilaksanakan mulai tanggal 10 Juli secara daring dan luring sesuai jadwal mata kuliah masing-masing.',
      category: 'academic',
      date: '2026-07-01',
      author: 'Akademik Eco-Learning'
    },
    {
      id: 'ann-2',
      title: 'Workshop Solar Energy Systems',
      content: 'Ayo ikuti kuliah umum dan workshop praktis sistem solar energi di Lab Panel Surya pada hari Sabtu, 5 Juli 2026. Free Sertifikat & SKS tambahan.',
      category: 'event',
      date: '2026-06-29',
      author: 'Himpunan Teknik Lingkungan'
    }
  ];

  const activityLogs: ActivityLog[] = [
    {
      id: 'log-1',
      actor: 'Dr. Sarah Green',
      role: 'Lecturer',
      action: 'Mengisi Absensi Sesi 12 Mata Kuliah Introduction to Solar Tech',
      timestamp: '2026-07-02 18:45'
    },
    {
      id: 'log-2',
      actor: 'Alex Rivera',
      role: 'Student',
      action: 'Mengunggah tugas Sesi 04 Keanekaragaman Hayati',
      timestamp: '2026-07-02 14:32'
    },
    {
      id: 'log-3',
      actor: 'Dr. Sarah Green',
      role: 'Lecturer',
      action: 'Mempublikasikan modul Sesi 12 Solar Inverters',
      timestamp: '2026-07-02 10:15'
    },
    {
      id: 'log-4',
      actor: 'System Auto',
      role: 'System',
      action: 'Backup database akademik mingguan berhasil',
      timestamp: '2026-07-01 02:00'
    }
  ];

  return {
    currentUser: users[0], // default sarah green
    currentRole: 'lecturer', // default lecturer
    users,
    courses,
    attendanceSessions,
    materials,
    assignments,
    submissions,
    courseGrades,
    announcements,
    activityLogs,
    systemStats: {
      totalStudents: 245,
      totalLecturers: 12,
      activeCourses: 8
    },
    streakDays: 12,
    academicProgressIndex: 3 // 'Step 4' biodiversity
  };
}

export function loadEcoState(): EcoAppState {
  try {
    const saved = localStorage.getItem('eco_learning_state');
    if (saved) {
      const parsed = JSON.parse(saved);
      // Automatically detect and migrate state if user list or courses list is outdated
      const hasNewUsers = parsed.users && parsed.users.some((u: any) => u.identifier.startsWith('22041010') || u.identifier.startsWith('22041011'));
      const hasNewCourses = parsed.courses && parsed.courses.some((c: any) => c.code === 'SI101');
      if (!parsed.users || parsed.users.length === 0 || !hasNewUsers || !hasNewCourses) {
        const freshState = getInitialState();
        saveEcoState(freshState);
        return freshState;
      }
      return parsed;
    }
  } catch (e) {
    console.error('Error reading state from localStorage:', e);
  }
  return getInitialState();
}

export function saveEcoState(state: EcoAppState) {
  try {
    localStorage.setItem('eco_learning_state', JSON.stringify(state));
  } catch (e) {
    console.error('Error writing state to localStorage:', e);
  }
}
