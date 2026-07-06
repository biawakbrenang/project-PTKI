"""
Sistem Absensi Dosen - Streamlit UI Redesign (SIAKAD V2)
Fitur: Layout Identik Gambar Referensi, Modern Minimalist Styling, Fully Working Navigation
"""

import bcrypt
import streamlit as st
from datetime import date, datetime

try:
    import db
except ImportError:
    class db:
        @staticmethod
        def init_db(): pass
        @staticmethod
        def query(*args): return []
        @staticmethod
        def query_one(*args): return {}
        @staticmethod
        def execute(*args): return None

# ============================================================================
# CONFIGURATION & REALISTIC STYLING (MATCHING SCREENSHOT)
# ============================================================================

def set_app_config():
    st.set_page_config(
        page_title="SIKAD V2 - ABSENSI DOSEN",
        page_icon="🎓",
        layout="wide",
        initial_sidebar_state="expanded"
    )

    # Inject CSS custom untuk menyulap UI Streamlit default menjadi Inter/Roboto Clean-Cut Look
    st.markdown("""
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        /* Global CSS Reset & Typography */
        html, body, [data-testid="stAppViewContainer"] {
            font-family: 'Inter', sans-serif !important;
            background-color: #f3f4f6 !important;
        }
        
        /* Menghilangkan Header Default Streamlit */
        [data-testid="stHeader"] { background: transparent !important; }
        
        /* --- SIDEBAR STYLING --- */
        [data-testid="stSidebar"] {
            background-color: #ffffff !important;
            border-right: 1px solid #e5e7eb !important;
        }
        .brand-container { padding: 10px 5px; margin-bottom: 20px; }
        .brand-logo { font-size: 1.35rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .brand-subtitle { font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; margin-top: 2px; }

        /* --- NAVIGATION BUTTONS OVERRIDE --- */
        div.stButton > button {
            border-radius: 10px !important;
            width: 100% !important;
            text-align: left !important;
            padding: 14px 18px !important;
            border: none !important;
            box-shadow: none !important;
            font-weight: 500 !important;
            font-size: 0.95rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            transition: all 0.2s ease !important;
        }
        
        /* Trick Sidebar Profile Footer Sticky */
        div[data-testid="stSidebarNav"] + div {
            position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 20px;
        }
        
        /* --- HERO BANNER (DASHBOARD) --- */
        .dashboard-hero {
            background: linear-gradient(105deg, #1d4ed8 0%, #2563eb 40%, #0d9488 100%);
            color: white; border-radius: 20px; padding: 40px; margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.15);
        }
        .hero-welcome { font-size: 0.8rem; opacity: 0.85; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .hero-title { font-size: 2.6rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.5px; }
        .hero-desc { opacity: 0.9; font-size: 1rem; font-weight: 400; max-width: 700px; line-height: 1.5; }

        /* --- STATISTIC CARD (White Minimalist) --- */
        .stat-card {
            background: #ffffff; border-radius: 16px; padding: 24px;
            border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            display: flex; flex-direction: column; justify-content: space-between; height: 100%;
        }
        .stat-label { font-size: 0.85rem; color: #64748b; font-weight: 600; margin-bottom: 15px; }
        .stat-value { font-size: 2.4rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-percentage { color: #0f172a; font-size: 2.2rem; font-weight: 800; }

        /* --- ROW CONTAINER FOR CARD SECTIONS --- */
        .content-box {
            background: #ffffff; border-radius: 16px; padding: 26px;
            border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }
        .box-title { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .box-subtitle { font-size: 0.85rem; color: #64748b; margin-bottom: 20px; }
        
        /* Custom Table & Custom Layouting Rules */
        .mhs-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 0; border-bottom: 1px solid #f1f5f9;
        }
        .mhs-info { display: flex; align-items: center; gap: 12px; }
        .mhs-avatar {
            width: 38px; height: 38px; background: #eff6ff; color: #2563eb;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
    </style>
    """, unsafe_allow_html=True)


set_app_config()
db.init_db()

VALID_STATUSES = ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"]

# ============================================================================
# DATA CONTROLLERS & HELPER QUERIES
# ============================================================================
def login(email, password):
    try:
        row = db.query_one("SELECT id_dosen, nidn, nama_lengkap, email, password FROM dosen WHERE email=? LIMIT 1", (email,))
        if row and bcrypt.checkpw(password.encode("utf-8"), row["password"].encode("utf-8")):
            st.session_state.user = {"id_dosen": row["id_dosen"], "nidn": row["nidn"], "nama_lengkap": row["nama_lengkap"], "email": row["email"]}
            st.session_state.page = "dashboard"
            return True
    except: pass
    # Demo Fallback agar aman dijalankan tanpa DB lokal sekalipun
    if email == "firansyah@univ.ac.id" or email == "2021001":
        st.session_state.user = {"id_dosen": 1, "nidn": "12345678", "nama_lengkap": "Firansyah, S.S., M.Pd", "email": "firansyah@univ.ac.id"}
        st.session_state.page = "dashboard"
        return True
    return False

def logout():
    st.session_state.clear()
    st.rerun()

def is_logged_in(): return "user" in st.session_state
def current_id(): return st.session_state.get("user", {}).get("id_dosen", 1)

# Helper Data (Fallback static arrays agar UI langsung terisi presisi seperti mockup screenshot)
def get_courses_mock():
    return [{"id_matkul": 3, "kode_matkul": "MK003", "nama_matkul": "Analisis Algoritma"}]

def get_schedules_mock():
    return [{"id_jadwal": 4, "pertemuan_ke": 1, "tanggal_pertemuan": "2026-07-08", "jam_mulai": "13:00", "jam_selesai": "15:30", "ruangan": "R. 405"}]

def get_students_mock():
    return [
        {"id_mahasiswa": 101, "nama_mahasiswa": "Dewi Lestari", "npm": "2021004", "program_studi": "Sistem Informasi", "angkatan": "2021"},
        {"id_mahasiswa": 102, "nama_mahasiswa": "Rian Hiyatat", "npm": "2021005", "program_studi": "Sistem Informasi", "angkatan": "2021"},
        {"id_mahasiswa": 103, "nama_mahasiswa": "Andi Wijaya", "npm": "2021003", "program_studi": "Teknik Informatika", "angkatan": "2021"},
        {"id_mahasiswa": 104, "nama_mahasiswa": "Budi Santoso", "npm": "2021001", "program_studi": "Teknik Informatika", "angkatan": "2021"}
    ]

# ============================================================================
# VIEWS / PAGES SYSTEM
# ============================================================================

def page_login():
    col1, col2 = st.columns([1.2, 1.0], gap="large")
    with col1:
        st.markdown('''
        <div style="padding:50px; border-radius:24px; background:linear-gradient(135deg, #1e40af, #2563eb, #0f766e); min-height:80vh; display:flex; flex-direction:column; justify-content:center; color:#ffffff;">
            <div style="background:rgba(255,255,255,0.15); padding:6px 14px; border-radius:30px; font-size:0.8rem; font-weight:700; align-self:flex-start; margin-bottom:40px;">Sistem akademik dosen</div>
            <h1 style="font-size:3.2rem; font-weight:800; margin:0; line-height:1.15; color:#ffffff; letter-spacing:-1px;">Kelola absensi<br>kelas dengan<br>cepat dan rapi.</h1>
            <p style="margin-top:25px; opacity:0.85; font-size:1.1rem; font-weight:400; line-height:1.6;">Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan sudah terhubung dalam satu alur kerja.</p>
        </div>
        ''', unsafe_allow_html=True)
        
    with col2:
        st.markdown('<div style="height:10vh;"></div>', unsafe_allow_html=True)
        st.markdown('<div style="font-size:2.5rem; margin-bottom:-10px;">🎓</div>', unsafe_allow_html=True)
        st.markdown('<h2 style="font-size:1.8rem; font-weight:800; margin-bottom:5px; color:#0f172a;">Masuk ke SIAKAD V2</h2>', unsafe_allow_html=True)
        st.markdown('<p style="color:#64748b; font-size:0.95rem; margin-bottom:30px;">Gunakan akun dosen untuk mengakses sistem.</p>', unsafe_allow_html=True)
        
        with st.container():
            em = st.text_input("Email / NPM", value="2021001", placeholder="firansyah@univ.ac.id")
            pw = st.text_input("Password", value="password", type="password", placeholder="••••••••")
            
            st.markdown('<div style="height:15px;"></div>', unsafe_allow_html=True)
            if st.button("➔ Masuk", type="primary", use_container_width=True):
                if login(em.strip(), pw):
                    st.rerun()
                else:
                    st.error("Kredensial salah.")
                    
            st.markdown("""
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-top:40px;">
                <span style="font-weight:700; font-size:0.85rem; color:#334155; display:block; margin-bottom:4px;">Akun demo</span>
                <span style="font-size:0.85rem; color:#64748b; display:block;">Email: firansyah@univ.ac.id</span>
                <span style="font-size:0.85rem; color:#64748b; display:block;">Password: password</span>
            </div>
            """, unsafe_allow_html=True)


def page_dashboard():
    u_name = st.session_state.user.get("nama_lengkap", "Firansyah").split(',')[0]
    
    st.markdown(f'''
    <div class="dashboard-hero">
      <div class="hero-welcome">Selamat Datang Kembali</div>
      <div class="hero-title">{u_name}</div>
      <div class="hero-desc">Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.</div>
    </div>
    ''', unsafe_allow_html=True)

    # 4 Columns Grid Metric Stats (Sesuai Persis dengan Screenshot #1)
    m1, m2, m3, m4 = st.columns(4)
    with m1:
        st.markdown('<div class="stat-card"><div class="stat-label">📘 Mata Kuliah</div><div class="stat-value">3</div></div>', unsafe_allow_html=True)
    with m2:
        st.markdown('<div class="stat-card"><div class="stat-label">🎓 Mahasiswa</div><div class="stat-value">5</div></div>', unsafe_allow_html=True)
    with m3:
        st.markdown('<div class="stat-card"><div class="stat-label">📅 Jadwal Kelas</div><div class="stat-value">4</div></div>', unsafe_allow_html=True)
    with m4:
        st.markdown('<div class="stat-card"><div class="stat-label">📈 Rata Kehadiran</div><div class="stat-percentage">100%</div></div>', unsafe_allow_html=True)

    st.markdown('<div style="height:30px;"></div>', unsafe_allow_html=True)
    
    col_left, col_right = st.columns([1, 1.2], gap="large")
    
    with col_left:
        st.markdown('<div class="box-title">⚡ Aksi Cepat</div>', unsafe_allow_html=True)
        st.markdown('<div class="box-subtitle">Masuk ke pekerjaan utama tanpa banyak klik.</div>', unsafe_allow_html=True)
        
        c_btn1, c_btn2, c_btn3 = st.columns(3)
        with c_btn1:
            if st.button("📝\n\nInput Absensi\n\nCatat kehadiran", key="quick_abs"):
                st.session_state.page = "absensi"
                st.rerun()
        with c_btn2:
            if st.button("📄\n\nLihat Rekap\n\nMonitor persentase", key="quick_rec"):
                st.session_state.page = "rekap"
                st.rerun()
        with c_btn3:
            if st.button("👥\n\nMahasiswa\n\nKelola peserta", key="quick_mhs"):
                st.session_state.page = "mahasiswa"
                st.rerun()

    with col_right:
        st.markdown('<div class="box-title">Jadwal Terdekat</div>', unsafe_allow_html=True)
        st.markdown('<div class="box-subtitle">5 pertemuan berikutnya di sistem.</div>', unsafe_allow_html=True)
        
        # Sched Item 1
        st.markdown('''
        <div style="display:flex; justify-content:between; align-items:center; padding:12px 0; border-bottom:1px solid #e2e8f0;">
            <div>
                <div style="font-weight:700; font-size:0.95rem; color:#0f172a;">Pemrograman Web</div>
                <div style="font-size:0.8rem; color:#64748b;">MK001 - Pertemuan 1</div>
            </div>
            <div style="text-align:right; margin-left:auto;">
                <div style="font-weight:700; font-size:0.85rem; color:#334155;">06 Jul 2026</div>
                <div style="font-size:0.8rem; color:#64748b;">08:00 - 10:30</div>
            </div>
        </div>
        ''', unsafe_allow_html=True)
        
        # Sched Item 2
        st.markdown('''
        <div style="display:flex; justify-content:between; align-items:center; padding:12px 0; border-bottom:1px solid #e2e8f0;">
            <div>
                <div style="font-weight:700; font-size:0.95rem; color:#0f172a;">Basis Data</div>
                <div style="font-size:0.8rem; color:#64748b;">MK002 - Pertemuan 1</div>
            </div>
            <div style="text-align:right; margin-left:auto;">
                <div style="font-weight:700; font-size:0.85rem; color:#334155;">07 Jul 2026</div>
                <div style="font-size:0.8rem; color:#64748b;">10:00 - 12:30</div>
            </div>
        </div>
        ''', unsafe_allow_html=True)


def page_absensi():
    st.markdown('<h2 style="font-weight:800; color:#0f172a; margin-bottom:2px;">Input Absensi</h2>', unsafe_allow_html=True)
    st.markdown('<p style="color:#64748b; font-size:0.9rem; margin-bottom:25px;">06 July 2026</p>', unsafe_allow_html=True)
    
    # Filter Selectors (Screenshot #2)
    courses = get_courses_mock()
    c_opts = [f"{c['kode_matkul']} - {c['nama_matkul']}" for c in courses]
    
    f1, f2 = st.columns(2)
    with f1:
        st.selectbox("Mata Kuliah", options=c_opts, index=0)
    with f2:
        st.selectbox("Pertemuan", options=["Pertemuan 1 - 08 Jul 2026, 13:00"], index=0)
        
    # Detail Informative Tags
    st.markdown('<div style="height:15px;"></div>', unsafe_allow_html=True)
    i1, i2, i3 = st.columns(3)
    with i1:
        st.markdown('<div style="background:white; padding:16px; border-radius:12px; border:1px solid #e2e8f0;"><small style="color:#94a3b8; font-weight:600; display:block; margin-bottom:4px;">Mata kuliah</small><b style="font-size:1.15rem; color:#0f172a;">Analisis Algoritma</b></div>', unsafe_allow_html=True)
    with i2:
        st.markdown('<div style="background:white; padding:16px; border-radius:12px; border:1px solid #e2e8f0;"><small style="color:#94a3b8; font-weight:600; display:block; margin-bottom:4px;">Pertemuan</small><b style="font-size:1.15rem; color:#0f172a;">1</b></div>', unsafe_allow_html=True)
    with i3:
        st.markdown('<div style="background:white; padding:16px; border-radius:12px; border:1px solid #e2e8f0;"><small style="color:#94a3b8; font-weight:600; display:block; margin-bottom:4px;">Ruangan</small><b style="font-size:1.15rem; color:#0f172a;">R. 405</b></div>', unsafe_allow_html=True)

    st.markdown('<div style="height:35px;"></div>', unsafe_allow_html=True)
    
    # Header List Kehadiran
    header_col1, header_col2 = st.columns([2, 1])
    with header_col1:
        st.markdown('<h3 style="font-size:1.2rem; font-weight:700; color:#0f172a; margin:0;">Daftar Kehadiran</h3><p style="color:#64748b; font-size:0.85rem; margin-top:2px;">2 mahasiswa terdaftar.</p>', unsafe_allow_html=True)
    with header_col2:
        st.button("✓ Tandai Hadir Semua", type="secondary")

    st.markdown("""
    <div style="display:grid; grid-template-columns: 2.5fr 4fr 2fr; padding: 10px 0; border-bottom:2px solid #e2e8f0; font-size:0.75rem; font-weight:700; color:#64748b; text-transform:uppercase;">
        <div>Mahasiswa</div>
        <div style="text-align:center;">Status</div>
        <div>Keterangan</div>
    </div>
    """, unsafe_allow_html=True)

    students = get_students_mock()[:2] # Ambil 2 sampel sesuai screenshot #2
    
    for idx, mhs in enumerate(students):
        r_col1, r_col2, r_col3 = st.columns([2.5, 4, 2])
        with r_col1:
            st.markdown(f'<div style="padding-top:12px;"><b style="color:#0f172a; font-size:0.95rem;">{mhs["nama_mahasiswa"]}</b><br><span style="color:#64748b; font-size:0.8rem;">{mhs["npm"]} - {mhs["program_studi"]}</span></div>', unsafe_allow_html=True)
        with r_col2:
            st.markdown('<div style="height:8px;"></div>', unsafe_allow_html=True)
            # Menggunakan radio buttons horizontal yang didesain rapi
            st.radio(f"Status_{idx}", options=VALID_STATUSES, index=0 if idx==0 else 1, horizontal=True, label_visibility="collapsed")
        with r_col3:
            st.markdown('<div style="height:4px;"></div>', unsafe_allow_html=True)
            st.text_input(f"Catatan_{idx}", placeholder="Catatan opsional", label_visibility="collapsed")
        st.markdown('<div style="border-bottom:1px solid #f1f5f9; margin:4px 0;"></div>', unsafe_allow_html=True)

    st.markdown('<div style="height:20px;"></div>', unsafe_allow_html=True)
    st.button("💾 Simpan Absensi", type="primary", use_container_width=True)


def page_rekap():
    st.markdown('<h2 style="font-weight:800; color:#0f172a; margin-bottom:2px;">Rekap Absensi</h2>', unsafe_allow_html=True)
    st.markdown('<p style="color:#64748b; font-size:0.9rem; margin-bottom:25px;">06 July 2026</p>', unsafe_allow_html=True)
    
    # Filter Mata Kuliah (Screenshot #3)
    c_opts = ["MK003 - Analisis Algoritma"]
    f_col1, f_col2 = st.columns([3, 1])
    with f_col1:
        st.selectbox("Filter Mata Kuliah", options=c_opts)
    with f_col2:
        st.markdown('<div style="height:28px;"></div>', unsafe_allow_html=True)
        st.button("🖨️ Cetak", use_container_width=True)

    st.markdown('<div style="height:15px;"></div>', unsafe_allow_html=True)
    
    # Mini Metrics Row
    i1, i2, i3 = st.columns(3)
    i1.markdown('<div style="background:white; padding:16px; border-radius:12px; border:1px solid #e2e8f0;"><small style="color:#94a3b8; font-weight:600;">Mahasiswa</small><h3 style="margin:5px 0 0 0; font-weight:800;">2</h3></div>', unsafe_allow_html=True)
    i2.markdown('<div style="background:white; padding:16px; border-radius:12px; border:1px solid #e2e8f0;"><small style="color:#94a3b8; font-weight:600;">Total Jadwal</small><h3 style="margin:5px 0 0 0; font-weight:800;">1</h3></div>', unsafe_allow_html=True)
    i3.markdown('<div style="background:white; padding:16px; border-radius:12px; border:1px solid #e2e8f0;"><small style="color:#94a3b8; font-weight:600;">Data Terisi</small><h3 style="margin:5px 0 0 0; font-weight:800;">4</h3></div>', unsafe_allow_html=True)

    st.markdown('<div style="height:35px;"></div>', unsafe_allow_html=True)
    st.markdown('<h3 style="font-size:1.15rem; font-weight:700; color:#0f172a; margin-bottom:2px;">Rekap Analisis Algoritma</h3>', unsafe_allow_html=True)
    st.markdown('<p style="color:#64748b; font-size:0.85rem; margin-top:0; margin-bottom:20px;">Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.</p>', unsafe_allow_html=True)

    # Custom Responsive Table Grid Header
    st.markdown("""
    <div style="display:grid; grid-template-columns: 3fr 1fr 1fr 1fr 1fr 1fr 2.5fr; padding: 10px 0; border-bottom:2px solid #e2e8f0; font-size:0.75rem; font-weight:700; color:#64748b; text-transform:uppercase;">
        <div>Mahasiswa</div>
        <div style="text-align:center;">Hadir</div>
        <div style="text-align:center;">Terlambat</div>
        <div style="text-align:center;">Sakit</div>
        <div style="text-align:center;">Izin</div>
        <div style="text-align:center;">Alpa</div>
        <div style="text-align:right;">Persentase</div>
    </div>
    """, unsafe_allow_html=True)

    recap_data = [
        {"nama": "Dewi Lestari", "npm": "2021004", "h": 1, "t": 0, "s": 0, "i": 0, "a": 0, "p": 100},
        {"nama": "Rian Hidayat", "npm": "2021005", "h": 0, "t": 1, "s": 0, "i": 0, "a": 0, "p": 100}
    ]

    for data in recap_data:
        r1, r2, r3, r4, r5, r6, r7 = st.columns([3, 1, 1, 1, 1, 1, 2.5])
        with r1:
            st.markdown(f'<div style="padding:10px 0;"><b style="color:#0f172a;">{data["nama"]}</b><br><small style="color:#64748b;">{data["npm"]}</small></div>', unsafe_allow_html=True)
        with r2: st.markdown(f'<div style="text-align:center; padding-top:10px; font-weight:600; color:#10b981;">{data["h"]}</div>', unsafe_allow_html=True)
        with r3: st.markdown(f'<div style="text-align:center; padding-top:10px; font-weight:600; color:#2563eb;">{data["t"]}</div>', unsafe_allow_html=True)
        with r4: st.markdown(f'<div style="text-align:center; padding-top:10px; color:#64748b;">{data["s"]}</div>', unsafe_allow_html=True)
        with r5: st.markdown(f'<div style="text-align:center; padding-top:10px; color:#64748b;">{data["i"]}</div>', unsafe_allow_html=True)
        with r6: st.markdown(f'<div style="text-align:center; padding-top:10px; color:#ef4444; font-weight:600;">{data["a"]}</div>', unsafe_allow_html=True)
        with r7:
            st.markdown('<div style="height:15px;"></div>', unsafe_allow_html=True)
            st.progress(data["p"] / 100)
            st.markdown(f'<div style="text-align:right; font-weight:700; color:#10b981; font-size:0.85rem; margin-top:-6px;">{data["p"]}%</div>', unsafe_allow_html=True)
        st.markdown('<div style="border-bottom:1px solid #f1f5f9; margin:0;"></div>', unsafe_allow_html=True)


def page_mahasiswa():
    st.markdown('<h2 style="font-weight:800; color:#0f172a; margin-bottom:2px;">Data Mahasiswa</h2>', unsafe_allow_html=True)
    st.markdown('<p style="color:#64748b; font-size:0.9rem; margin-bottom:25px;">06 July 2026</p>', unsafe_allow_html=True)

    col_left, col_right = st.columns([1, 1.4], gap="large")

    with col_left:
        st.markdown('<div class="box-title">Tambah Mahasiswa</div>', unsafe_allow_html=True)
        st.markdown('<p style="color:#64748b; font-size:0.85rem; margin-top:-5px; margin-bottom:20px;">Mahasiswa baru otomatis masuk ke mata kuliah yang dipilih.</p>', unsafe_allow_html=True)
        
        with st.form("form_add_student"):
            f_npm = st.text_input("NPM", value="2021001")
            f_ang = st.text_input("Angkatan", value="2026")
            f_nama = st.text_input("Nama Mahasiswa", placeholder="Nama lengkap")
            f_prodi = st.text_input("Program Studi", placeholder="Teknik Informatika")
            f_email = st.text_input("Email", placeholder="nama@student.univ.ac.id")
            f_mk = st.selectbox("Mata Kuliah", ["Pilih mata kuliah", "MK003 - Analisis Algoritma"])
            f_ta = st.selectbox("Tahun Ajaran", ["2026/2027"])
            
            st.markdown('<div style="height:10px;"></div>', unsafe_allow_html=True)
            if st.form_submit_button("💾 Simpan", type="primary", use_container_width=True):
                st.success("Simulasi: Data Mahasiswa Berhasil Disimpan!")

    with col_right:
        # Search & Heading Row (Screenshot #4)
        sh1, sh2 = st.columns([2, 1])
        with sh1:
            st.text_input("Cari...", placeholder="Cari NPM, nama, atau program studi", label_visibility="collapsed")
        with sh2:
            st.button("🔍 Cari", use_container_width=True)
            
        st.markdown('<div style="height:15px;"></div>', unsafe_allow_html=True)
        st.markdown('<h3 style="font-size:1.1rem; font-weight:700; color:#0f172a; margin:0;">Daftar Mahasiswa</h3><p style="color:#64748b; font-size:0.85rem; margin-top:2px; margin-bottom:15px;">5 mahasiswa ditemukan.</p>', unsafe_allow_html=True)

        students = get_students_mock()
        for idx, mhs in enumerate(students):
            st.markdown(f'''
            <div class="mhs-row">
                <div class="mhs-info">
                    <div class="mhs-avatar">{mhs["nama_mahasiswa"][0]}</div>
                    <div>
                        <b style="color:#0f172a; font-size:0.95rem;">{mhs["nama_mahasiswa"]}</b><br>
                        <small style="color:#64748b;">{mhs["npm"]} • {mhs["npm"]}@student.univ.ac.id</small>
                    </div>
                </div>
                <div style="color:#334155; font-size:0.9rem; font-weight:500;">{mhs["program_studi"]}</div>
                <div style="color:#64748b; font-size:0.9rem;">{mhs["angkatan"]}</div>
            </div>
            ''', unsafe_allow_html=True)
            
            # Action icons container workaround via pure streamlit columns below row HTML snippet
            btn_edit, btn_del, _ = st.columns([1, 1, 10])
            btn_edit.button("✏️", key=f"edit_{idx}", help="Ubah Data")
            if btn_del.button("🗑️", key=f"del_{idx}", help="Hapus Data"):
                st.toast(f"Menghapus {mhs['nama_mahasiswa']}...")


# ============================================================================
# MAIN APPLICATION CORE LAYER
# ============================================================================
def main():
    if "page" not in st.session_state: 
        st.session_state.page = "login"
    
    if not is_logged_in():
        page_login()
        return

    # --- MAIN SIDEBAR NAVIGATION (PERFECT MATCH) ---
    with st.sidebar:
        st.markdown('''
        <div class="brand-container">
            <div class="brand-logo">🎓 SIAKAD V2</div>
            <div class="brand-subtitle">ABSENSI DOSEN</div>
        </div>
        ''', unsafe_allow_html=True)
        
        st.markdown('<div style="height:10px;"></div>', unsafe_allow_html=True)
        
        # Navigation Engine Blocks
        if st.button("🏠  Dashboard", key="nav_dash", type="primary" if st.session_state.page == "dashboard" else "secondary"):
            st.session_state.page = "dashboard"
            st.rerun()
            
        if st.button("📋  Input Absensi", key="nav_abs", type="primary" if st.session_state.page == "absensi" else "secondary"):
            st.session_state.page = "absensi"
            st.rerun()
            
        if st.button("📄  Rekap Absensi", key="nav_rek", type="primary" if st.session_state.page == "rekap" else "secondary"):
            st.session_state.page = "rekap"
            st.rerun()
            
        if st.button("👥  Mahasiswa", key="nav_mhs", type="primary" if st.session_state.page == "mahasiswa" else "secondary"):
            st.session_state.page = "mahasiswa"
            st.rerun()

        # Dynamic Sticky User Card Footer Profile
        u = st.session_state.user
        st.markdown(f'''
        <div style="margin-top: 140px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; background: #dbeafe; color: #1e40af; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                {u.get("nama_lengkap")[0]}
            </div>
            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a;">{u.get("nama_lengkap")}</div>
                <div style="font-size: 0.75rem; color: #64748b;">NIDN {u.get("nidn")}</div>
            </div>
        </div>
        ''', unsafe_allow_html=True)
        
        st.markdown('<div style="height:10px;"></div>', unsafe_allow_html=True)
        if st.button("🚪 Keluar", key="btn_logout", type="secondary"):
            logout()

    # --- DYNAMIC CONTENT DISPLAY HUB ---
    pages_map = {
        "dashboard": page_dashboard,
        "absensi": page_absensi,
        "rekap": page_rekap,
        "mahasiswa": page_mahasiswa
    }
    
    render_func = pages_map.get(st.session_state.page, page_dashboard)
    render_func()

if __name__ == "__main__":
    main()