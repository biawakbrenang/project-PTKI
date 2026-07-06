"""
Sistem Absensi Dosen - versi Streamlit (Modern UI)
Konversi dari aplikasi PHP (ABSENSI-DOSEN) dengan desain modern.
"""

import bcrypt
import streamlit as st
from datetime import date, datetime
import db

# --- Page Config ---
st.set_page_config(
    page_title="SIAKAD V2 - Absensi Dosen",
    page_icon="🎓",
    layout="wide",
    initial_sidebar_state="expanded",
)

db.init_db()

# --- Custom CSS for Modern UI ---
st.markdown("""
<style>
    /* Main Background */
    .stApp {
        background-color: #f8fafc;
    }

    /* Sidebar Styling */
    section[data-testid="stSidebar"] {
        background-color: white !important;
        border-right: 1px solid #e2e8f0;
        width: 300px !important;
    }
    
    section[data-testid="stSidebar"] .st-emotion-cache-16txtl3 {
        padding: 2rem 1.5rem;
    }

    /* Sidebar Logo & Header */
    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 2rem;
        padding: 0 0.5rem;
    }
    .logo-box {
        background: #2563eb;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .header-text h1 {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        margin: 0 !important;
        color: #1e293b;
    }
    .header-text p {
        font-size: 0.75rem !important;
        margin: 0 !important;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Sidebar Menu Items */
    .nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        color: #64748b;
        text-decoration: none;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    .nav-item:hover {
        background-color: #f1f5f9;
        color: #2563eb;
    }
    .nav-item.active {
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 600;
    }
    .nav-icon {
        margin-right: 12px;
        font-size: 1.1rem;
    }

    /* Sidebar Profile */
    .sidebar-footer {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: #f8fafc;
        padding: 12px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .profile-avatar {
        width: 40px;
        height: 40px;
        background: #dbeafe;
        color: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .profile-info {
        flex: 1;
        overflow: hidden;
    }
    .profile-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .profile-nidn {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Cards */
    .custom-card {
        background: white;
        padding: 1.5rem;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 1rem;
    }

    /* Hero Dashboard */
    .hero-card {
        background: linear-gradient(100deg, #2563eb 0%, #1e40af 100%);
        padding: 2.5rem;
        border-radius: 24px;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .hero-card::after {
        content: '';
        position: absolute;
        right: -50px;
        top: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .hero-label {
        background: rgba(255,255,255,0.2);
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 1rem;
    }
    .hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    .hero-subtitle {
        color: #dbeafe;
        font-size: 1rem;
        max-width: 600px;
    }

    /* Metric Cards */
    .metric-card {
        background: white;
        padding: 1.25rem;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .metric-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 8px;
    }
    .metric-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .metric-value {
        color: #1e293b;
        font-size: 1.75rem;
        font-weight: 700;
    }

    /* Buttons */
    .stButton > button {
        border-radius: 12px !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
    }
    .stButton > button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    /* Hide default streamlit elements */
    #MainMenu {visibility: hidden;}
    footer {visibility: hidden;}
    header {visibility: hidden;}
</style>
""", unsafe_allow_html=True)

# --- Session State ---
if "page" not in st.session_state:
    st.session_state.page = "dashboard"
if "user" not in st.session_state:
    st.session_state.user = None

# --- Auth Helpers ---
def login(email: str, password: str) -> bool:
    row = db.query_one(
        "SELECT id_dosen, nidn, nama_lengkap, email, password FROM dosen WHERE email = ? LIMIT 1",
        (email,),
    )
    if row and bcrypt.checkpw(password.encode("utf-8"), row["password"].encode("utf-8")):
        st.session_state.user = {
            "id_dosen": row["id_dosen"],
            "nidn": row["nidn"],
            "nama_lengkap": row["nama_lengkap"],
            "email": row["email"],
        }
        return True
    return False

def logout():
    st.session_state.user = None
    st.session_state.page = "dashboard"
    st.rerun()

# --- Page: Login ---
def page_login():
    st.markdown("""
    <style>
        [data-testid="stSidebar"] { display: none; }
        .stApp { background: white; }
    </style>
    """, unsafe_allow_html=True)
    
    col1, col2 = st.columns([1.2, 1])
    
    with col1:
        st.markdown(f"""
        <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); 
                    height: 100vh; display: flex; flex-direction: column; 
                    justify-content: center; padding: 10%; color: white;">
            <div style="background: rgba(255,255,255,0.15); padding: 8px 16px; 
                        border-radius: 99px; font-size: 0.8rem; font-weight: 600; 
                        width: fit-content; margin-bottom: 2rem;">
                🛡️ Sistem akademik dosen
            </div>
            <h1 style="font-size: 3.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem;">
                Kelola absensi kelas dengan cepat dan rapi.
            </h1>
            <p style="font-size: 1.1rem; color: #dbeafe; line-height: 1.6;">
                Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan sudah terhubung dalam satu alur kerja.
            </p>
        </div>
        """, unsafe_allow_html=True)
        
    with col2:
        st.markdown("""
        <div style="padding: 15% 10%;">
            <div style="background: #2563eb; color: white; width: 48px; height: 48px; 
                        border-radius: 12px; display: flex; align-items: center; 
                        justify-content: center; font-size: 24px; margin-bottom: 2rem;">
                🎓
            </div>
            <h2 style="font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">
                Masuk ke SIAKAD V2
            </h2>
            <p style="color: #64748b; margin-bottom: 2.5rem;">
                Gunakan akun dosen untuk mengakses sistem.
            </p>
        </div>
        """, unsafe_allow_html=True)
        
        with st.container():
            # Adjust padding for the form container
            st.markdown('<div style="margin-top: -150px; padding: 0 10%;">', unsafe_allow_html=True)
            with st.form("login_form", border=False):
                email = st.text_input("Email", placeholder="firansyah@univ.ac.id")
                password = st.text_input("Password", type="password", placeholder="Masukkan password")
                st.markdown("<br>", unsafe_allow_html=True)
                submitted = st.form_submit_button("Masuk", use_container_width=True, type="primary")
                
                if submitted:
                    if login(email.strip(), password):
                        st.rerun()
                    else:
                        st.error("Email atau password salah.")
            
            st.markdown("""
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; margin-top: 2rem; border: 1px solid #e2e8f0;">
                <p style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Akun demo</p>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Email: firansyah@univ.ac.id</p>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Password: password</p>
            </div>
            """, unsafe_allow_html=True)
            st.markdown('</div>', unsafe_allow_html=True)

# --- Page: Dashboard ---
def page_dashboard():
    # Header
    st.markdown(f"""
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0;">Dashboard</h1>
            <p style="color: #64748b; font-size: 0.875rem;">{datetime.now().strftime('%d %B %Y')}</p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span style="font-size: 1.25rem; cursor: pointer;">🌙</span>
            <div style="background: #1e293b; color: white; padding: 8px 20px; border-radius: 10px; font-weight: 600; cursor: pointer;">Keluar</div>
        </div>
    </div>
    """, unsafe_allow_html=True)

    # Hero
    st.markdown(f"""
    <div class="hero-card">
        <div class="hero-label">Selamat datang kembali</div>
        <div class="hero-title">{st.session_state.user['nama_lengkap'].split(',')[0]}</div>
        <div class="hero-subtitle">
            Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.
        </div>
    </div>
    """, unsafe_allow_html=True)

    # Stats
    stats = db.query_one(f"""
        SELECT 
            (SELECT COUNT(*) FROM mata_kuliah WHERE id_dosen = {st.session_state.user['id_dosen']}) as total_matkul,
            (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul WHERE mk.id_dosen = {st.session_state.user['id_dosen']}) as total_mhs,
            (SELECT COUNT(*) FROM jadwal_perkuliahan jp JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul WHERE mk.id_dosen = {st.session_state.user['id_dosen']}) as total_jadwal
    """)
    
    # Calculate Attendance Rate
    att_row = db.query_one(f"""
        SELECT SUM(CASE WHEN a.status_kehadiran IN ('Hadir','Terlambat') THEN 1 ELSE 0 END) as hadir,
               COUNT(a.id_absensi) as total
        FROM absensi a
        JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal
        JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
        WHERE mk.id_dosen = {st.session_state.user['id_dosen']}
    """)
    rate = round((att_row['hadir'] or 0) / att_row['total'] * 100) if att_row and att_row['total'] else 0

    c1, c2, c3, c4 = st.columns(4)
    with c1:
        st.markdown(f"""<div class="metric-card"><div class="metric-icon-box" style="background: #eff6ff; color: #2563eb;">📘</div><div class="metric-label">Mata Kuliah</div><div class="metric-value">{stats['total_matkul']}</div></div>""", unsafe_allow_html=True)
    with c2:
        st.markdown(f"""<div class="metric-card"><div class="metric-icon-box" style="background: #ecfdf5; color: #10b981;">🎓</div><div class="metric-label">Mahasiswa</div><div class="metric-value">{stats['total_mhs']}</div></div>""", unsafe_allow_html=True)
    with c3:
        st.markdown(f"""<div class="metric-card"><div class="metric-icon-box" style="background: #fff7ed; color: #f59e0b;">🗓️</div><div class="metric-label">Jadwal Kelas</div><div class="metric-value">{stats['total_jadwal']}</div></div>""", unsafe_allow_html=True)
    with c4:
        st.markdown(f"""<div class="metric-card"><div class="metric-icon-box" style="background: #fef2f2; color: #ef4444;">📈</div><div class="metric-label">Rata Kehadiran</div><div class="metric-value">{rate}%</div></div>""", unsafe_allow_html=True)

    st.markdown("<br>", unsafe_allow_html=True)
    
    col_left, col_right = st.columns([1.5, 1])
    
    with col_left:
        st.markdown("""
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">Aksi Cepat</h3>
            <p style="color: #64748b; font-size: 0.875rem;">Masuk ke pekerjaan utama tanpa banyak klik.</p>
        </div>
        """, unsafe_allow_html=True)
        
        ac1, ac2, ac3 = st.columns(3)
        with ac1:
            with st.container(border=True):
                st.markdown("#### 📝")
                st.markdown("**Input Absensi**")
                st.caption("Catat kehadiran kelas")
                if st.button("Buka", key="btn_abs", use_container_width=True):
                    st.session_state.page = "absensi"
                    st.rerun()
        with ac2:
            with st.container(border=True):
                st.markdown("#### 📊")
                st.markdown("**Lihat Rekap**")
                st.caption("Monitor persentase")
                if st.button("Buka", key="btn_rek", use_container_width=True):
                    st.session_state.page = "rekap"
                    st.rerun()
        with ac3:
            with st.container(border=True):
                st.markdown("#### 👥")
                st.markdown("**Mahasiswa**")
                st.caption("Kelola peserta kelas")
                if st.button("Buka", key="btn_mhs", use_container_width=True):
                    st.session_state.page = "mahasiswa"
                    st.rerun()

    with col_right:
        st.markdown("""
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">Jadwal Terdekat</h3>
            <p style="color: #64748b; font-size: 0.875rem;">5 pertemuan berikutnya di sistem.</p>
        </div>
        """, unsafe_allow_html=True)
        
        schedules = db.query(f"""
            SELECT jp.*, mk.nama_matkul, mk.kode_matkul 
            FROM jadwal_perkuliahan jp 
            JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul 
            WHERE mk.id_dosen = {st.session_state.user['id_dosen']}
            ORDER BY tanggal_pertemuan ASC LIMIT 5
        """)
        
        for s in schedules:
            with st.container(border=True):
                sc1, sc2 = st.columns([2, 1])
                sc1.markdown(f"**{s['nama_matkul']}**")
                sc1.caption(f"{s['kode_matkul']} - Pertemuan {s['pertemuan_ke']}")
                
                try:
                    dt = datetime.strptime(s['tanggal_pertemuan'], '%Y-%m-%d')
                    tgl = dt.strftime('%d %b %Y')
                except:
                    tgl = s['tanggal_pertemuan']
                    
                sc2.markdown(f"<div style='text-align: right;'><b>{tgl}</b><br><span style='font-size: 0.8rem; color: #64748b;'>{s['jam_mulai']} - {s['jam_selesai']}</span></div>", unsafe_allow_html=True)

# --- Page: Input Absensi ---
def page_absensi():
    st.markdown("""
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0;">Input Absensi</h1>
        <p style="color: #64748b; font-size: 0.875rem;">{0}</p>
    </div>
    """.format(datetime.now().strftime('%d %B %Y')), unsafe_allow_html=True)

    courses = db.query(f"SELECT * FROM mata_kuliah WHERE id_dosen = {st.session_state.user['id_dosen']}")
    if not courses:
        st.info("Belum ada mata kuliah.")
        return

    col1, col2, col3 = st.columns([1.5, 1.5, 0.5])
    
    course_map = {f"{c['kode_matkul']} - {c['nama_matkul']}": c['id_matkul'] for c in courses}
    selected_course_label = col1.selectbox("Mata Kuliah", list(course_map.keys()))
    id_matkul = course_map[selected_course_label]

    schedules = db.query(f"SELECT * FROM jadwal_perkuliahan WHERE id_matkul = {id_matkul} ORDER BY pertemuan_ke")
    sched_map = {f"Pertemuan {s['pertemuan_ke']} - {s['tanggal_pertemuan']}": s['id_jadwal'] for s in schedules}
    
    if not sched_map:
        st.warning("Belum ada jadwal untuk mata kuliah ini.")
        return
        
    selected_sched_label = col2.selectbox("Pertemuan", list(sched_map.keys()))
    id_jadwal = sched_map[selected_sched_label]
    
    if col3.button("🔄 Reset", use_container_width=True):
        st.rerun()

    # Sched Info Cards
    curr_sched = next(s for s in schedules if s['id_jadwal'] == id_jadwal)
    curr_course = next(c for c in courses if c['id_matkul'] == id_matkul)
    
    st.markdown("<br>", unsafe_allow_html=True)
    inf1, inf2, inf3 = st.columns(3)
    with inf1:
        with st.container(border=True):
            st.caption("Mata kuliah")
            st.markdown(f"**{curr_course['nama_matkul']}**")
    with inf2:
        with st.container(border=True):
            st.caption("Pertemuan")
            st.markdown(f"**{curr_sched['pertemuan_ke']}**")
    with inf3:
        with st.container(border=True):
            st.caption("Ruangan")
            st.markdown(f"**{curr_sched['ruangan']}**")

    st.markdown("<br>", unsafe_allow_html=True)
    
    # Student List
    st.markdown("### Daftar Kehadiran")
    students = db.query(f"""
        SELECT m.* FROM mahasiswa m 
        JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa 
        WHERE km.id_matkul = {id_matkul}
    """)
    
    if not students:
        st.info("Belum ada mahasiswa terdaftar di kelas ini.")
        return

    attendance = db.query(f"SELECT * FROM absensi WHERE id_jadwal = {id_jadwal}")
    att_map = {a['id_mahasiswa']: a for a in attendance}

    with st.form("attendance_form", border=False):
        for m in students:
            with st.container(border=True):
                c_mhs, c_stat, c_ket = st.columns([1.5, 2, 1.5])
                c_mhs.markdown(f"**{m['nama_mahasiswa']}**")
                c_mhs.caption(f"{m['npm']} - {m['program_studi']}")
                
                curr_att = att_map.get(m['id_mahasiswa'], {})
                status = c_stat.radio(
                    f"Status_{m['id_mahasiswa']}", 
                    ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"],
                    index=["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"].index(curr_att.get('status_kehadiran', 'Hadir')),
                    horizontal=True,
                    label_visibility="collapsed"
                )
                keterangan = c_ket.text_input(f"Ket_{m['id_mahasiswa']}", value=curr_att.get('keterangan', ''), placeholder="Catatan opsional", label_visibility="collapsed")
                
                # Hidden storage for submission
                m['new_status'] = status
                m['new_ket'] = keterangan

        st.markdown("<br>", unsafe_allow_html=True)
        if st.form_submit_button("💾 Simpan Kehadiran", type="primary", use_container_width=True):
            for m in students:
                db.execute("""
                    INSERT INTO absensi (id_mahasiswa, id_jadwal, status_kehadiran, keterangan)
                    VALUES (?, ?, ?, ?)
                    ON CONFLICT(id_mahasiswa, id_jadwal) DO UPDATE SET
                    status_kehadiran = excluded.status_kehadiran,
                    keterangan = excluded.keterangan,
                    waktu_input = CURRENT_TIMESTAMP
                """, (m['id_mahasiswa'], id_jadwal, m['new_status'], m['new_ket']))
            st.success("Absensi berhasil disimpan!")
            st.rerun()

# --- Page: Rekap Absensi ---
def page_rekap():
    st.markdown("""
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0;">Rekap Absensi</h1>
        <p style="color: #64748b; font-size: 0.875rem;">{0}</p>
    </div>
    """.format(datetime.now().strftime('%d %B %Y')), unsafe_allow_html=True)

    courses = db.query(f"SELECT * FROM mata_kuliah WHERE id_dosen = {st.session_state.user['id_dosen']}")
    course_map = {f"{c['kode_matkul']} - {c['nama_matkul']}": c['id_matkul'] for c in courses}
    
    col_f1, col_f2 = st.columns([3, 1])
    selected_course_label = col_f1.selectbox("Filter Mata Kuliah", list(course_map.keys()))
    id_matkul = course_map[selected_course_label]
    col_f2.markdown("<br>", unsafe_allow_html=True)
    col_f2.button("🖨️ Cetak", use_container_width=True)

    # Summary
    st.markdown("<br>", unsafe_allow_html=True)
    mhs_count = db.query_one(f"SELECT COUNT(*) as c FROM kelas_mahasiswa WHERE id_matkul = {id_matkul}")['c']
    jadwal_count = db.query_one(f"SELECT COUNT(*) as c FROM jadwal_perkuliahan WHERE id_matkul = {id_matkul}")['c']
    data_isi = db.query_one(f"SELECT COUNT(*) as c FROM absensi a JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal WHERE jp.id_matkul = {id_matkul}")['c']

    s1, s2, s3 = st.columns(3)
    with s1:
        with st.container(border=True):
            st.caption("Mahasiswa")
            st.markdown(f"### {mhs_count}")
    with s2:
        with st.container(border=True):
            st.caption("Total Jadwal")
            st.markdown(f"### {jadwal_count}")
    with s3:
        with st.container(border=True):
            st.caption("Data Terisi")
            st.markdown(f"### {data_isi}")

    st.markdown(f"### Rekap {selected_course_label.split(' - ')[1]}")
    st.caption("Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.")
    
    rekap_data = db.query(f"""
        SELECT m.id_mahasiswa, m.npm, m.nama_mahasiswa,
               SUM(CASE WHEN a.status_kehadiran = 'Hadir' THEN 1 ELSE 0 END) as hadir,
               SUM(CASE WHEN a.status_kehadiran = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
               SUM(CASE WHEN a.status_kehadiran = 'Sakit' THEN 1 ELSE 0 END) as sakit,
               SUM(CASE WHEN a.status_kehadiran = 'Izin' THEN 1 ELSE 0 END) as izin,
               SUM(CASE WHEN a.status_kehadiran = 'Alpa' THEN 1 ELSE 0 END) as alpa
        FROM mahasiswa m
        JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
        LEFT JOIN jadwal_perkuliahan jp ON km.id_matkul = jp.id_matkul
        LEFT JOIN absensi a ON m.id_mahasiswa = a.id_mahasiswa AND jp.id_jadwal = a.id_jadwal
        WHERE km.id_matkul = {id_matkul}
        GROUP BY m.id_mahasiswa
    """)

    # Custom Table Header
    st.markdown("""
    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 2fr; padding: 10px; background: #f8fafc; border-radius: 8px; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">
        <div>Mahasiswa</div>
        <div style="text-align: center;">Hadir</div>
        <div style="text-align: center;">Terlambat</div>
        <div style="text-align: center;">Sakit</div>
        <div style="text-align: center;">Izin</div>
        <div style="text-align: center;">Alpa</div>
        <div style="text-align: center;">Persentase</div>
    </div>
    """, unsafe_allow_html=True)

    for r in rekap_data:
        total_hadir = r['hadir'] + r['terlambat']
        pct = round(total_hadir / jadwal_count * 100) if jadwal_count > 0 else 0
        
        st.markdown(f"""
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 2fr; padding: 15px 10px; border-bottom: 1px solid #f1f5f9; align-items: center; font-size: 0.9rem;">
            <div>
                <div style="font-weight: 600; color: #1e293b;">{r['nama_mahasiswa']}</div>
                <div style="font-size: 0.75rem; color: #64748b;">{r['npm']}</div>
            </div>
            <div style="text-align: center; color: #10b981; font-weight: 600;">{r['hadir']}</div>
            <div style="text-align: center; color: #2563eb; font-weight: 600;">{r['terlambat']}</div>
            <div style="text-align: center;">{r['sakit']}</div>
            <div style="text-align: center;">{r['izin']}</div>
            <div style="text-align: center; color: #ef4444;">{r['alpa']}</div>
            <div style="display: flex; align-items: center; gap: 10px; padding: 0 10px;">
                <div style="flex: 1; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                    <div style="width: {pct}%; height: 100%; background: #10b981;"></div>
                </div>
                <div style="font-weight: 700; color: #1e293b; min-width: 40px;">{pct}%</div>
            </div>
        </div>
        """, unsafe_allow_html=True)

# --- Page: Mahasiswa ---
def page_mahasiswa():
    st.markdown("""
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0;">Data Mahasiswa</h1>
        <p style="color: #64748b; font-size: 0.875rem;">{0}</p>
    </div>
    """.format(datetime.now().strftime('%d %B %Y')), unsafe_allow_html=True)

    col_form, col_list = st.columns([1, 1.5])
    
    with col_form:
        with st.container(border=True):
            st.markdown("### Tambah Mahasiswa")
            st.caption("Mahasiswa baru otomatis masuk ke mata kuliah yang dipilih.")
            with st.form("add_student", border=False):
                npm = st.text_input("NPM", placeholder="2021001")
                angkatan = st.text_input("Angkatan", value="2026")
                nama = st.text_input("Nama Mahasiswa", placeholder="Nama lengkap")
                prodi = st.text_input("Program Studi", placeholder="Teknik Informatika")
                email = st.text_input("Email", placeholder="nama@student.univ.ac.id")
                
                courses = db.query(f"SELECT * FROM mata_kuliah WHERE id_dosen = {st.session_state.user['id_dosen']}")
                course_map = {c['nama_matkul']: c['id_matkul'] for c in courses}
                matkul = st.selectbox("Mata Kuliah", list(course_map.keys()))
                tahun = st.text_input("Tahun Ajaran", value="2026/2027")
                
                if st.form_submit_button("💾 Simpan", type="primary", use_container_width=True):
                    try:
                        id_mhs = db.execute("INSERT INTO mahasiswa (npm, nama_mahasiswa, program_studi, angkatan, email) VALUES (?, ?, ?, ?, ?)",
                                   (npm, nama, prodi, int(angkatan), email))
                        db.execute("INSERT INTO kelas_mahasiswa (id_mahasiswa, id_matkul, tahun_ajaran) VALUES (?, ?, ?)",
                                   (id_mhs, course_map[matkul], tahun))
                        st.success("Mahasiswa berhasil ditambahkan!")
                        st.rerun()
                    except Exception as e:
                        st.error(f"Gagal menambah mahasiswa: {e}")

    with col_list:
        search = st.text_input("🔍 Cari NPM, nama, atau program studi", placeholder="Cari...")
        
        sql = f"""
            SELECT DISTINCT m.* FROM mahasiswa m
            JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
            JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
            WHERE mk.id_dosen = {st.session_state.user['id_dosen']}
        """
        if search:
            sql += f" AND (m.npm LIKE '%{search}%' OR m.nama_mahasiswa LIKE '%{search}%' OR m.program_studi LIKE '%{search}%')"
        
        students = db.query(sql)
        st.markdown(f"### Daftar Mahasiswa")
        st.caption(f"{len(students)} mahasiswa ditemukan.")
        
        for m in students:
            with st.container(border=True):
                c1, c2, c3, c4 = st.columns([0.5, 2, 1.5, 1])
                initial = m['nama_mahasiswa'][0]
                c1.markdown(f"""<div style="width: 40px; height: 40px; background: #eff6ff; color: #2563eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700;">{initial}</div>""", unsafe_allow_html=True)
                c2.markdown(f"**{m['nama_mahasiswa']}**")
                c2.caption(f"{m['npm']} - {m['email']}")
                c3.markdown(f"<div style='font-size: 0.85rem; color: #64748b;'>{m['program_studi']}</div>", unsafe_allow_html=True)
                c3.caption(f"Angkatan {m['angkatan']}")
                
                if c4.button("🗑️", key=f"del_{m['id_mahasiswa']}"):
                    db.execute(f"DELETE FROM kelas_mahasiswa WHERE id_mahasiswa = {m['id_mahasiswa']}")
                    db.execute(f"DELETE FROM mahasiswa WHERE id_mahasiswa = {m['id_mahasiswa']}")
                    st.rerun()

# --- Main Logic ---
if st.session_state.user is None:
    page_login()
else:
    # Sidebar
    with st.sidebar:
        st.markdown(f"""
        <div class="sidebar-header">
            <div class="logo-box">🎓</div>
            <div class="header-text">
                <h1>SIAKAD V2</h1>
                <p>Absensi Dosen</p>
            </div>
        </div>
        """, unsafe_allow_html=True)
        
        pages = [
            ("dashboard", "🏠 Dashboard"),
            ("absensi", "📝 Input Absensi"),
            ("rekap", "📊 Rekap Absensi"),
            ("mahasiswa", "👥 Mahasiswa")
        ]
        
        for pg_id, pg_label in pages:
            is_active = "active" if st.session_state.page == pg_id else ""
            if st.markdown(f'<div class="nav-item {is_active}">{pg_label}</div>', unsafe_allow_html=True):
                # Note: Streamlit markdown doesn't support clicks like this easily, 
                # we'll use a hidden button approach or standard sidebar radio
                pass
        
        # Real navigation
        st.session_state.page = st.radio("Navigation", [p[0] for p in pages], 
                                         format_func=lambda x: next(p[1] for p in pages if p[0] == x),
                                         label_visibility="collapsed")

        # Profile Footer
        st.markdown(f"""
        <div class="sidebar-footer">
            <div class="profile-avatar">{st.session_state.user['nama_lengkap'][0]}</div>
            <div class="profile-info">
                <div class="profile-name">{st.session_state.user['nama_lengkap']}</div>
                <div class="profile-nidn">{st.session_state.user['nidn']}</div>
            </div>
        </div>
        """, unsafe_allow_html=True)
        
        if st.button("🚪 Keluar", use_container_width=True):
            logout()

    # Routing
    if st.session_state.page == "dashboard":
        page_dashboard()
    elif st.session_state.page == "absensi":
        page_absensi()
    elif st.session_state.page == "rekap":
        page_rekap()
    elif st.session_state.page == "mahasiswa":
        page_mahasiswa()
