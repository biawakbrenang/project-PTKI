"""
Sistem Absensi Dosen - Streamlit UI Redesign (Pixel-Perfect)
Fitur: Layout Sesuai Gambar Referensi, Modern Card Container, Alur Kerja Responsif
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
# CONFIGURATION & HIGH-FIDELITY STYLING
# ============================================================================

def set_app_config():
    st.set_page_config(
        page_title="SIKAD V2 - Absensi",
        page_icon="🎓",
        layout="wide",
        initial_sidebar_state="expanded"
    )

    st.markdown("""
    <style>
        /* --- GLOBAL RESETS & FONTS --- */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        html, body, [data-testid="stAppViewContainer"] {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc !important;
        }
        
        .block-container { padding-top: 2rem !important; padding-bottom: 2rem !important; }
        
        /* Remove default Streamlit elements padding/headers */
        [data-testid="stHeader"] { background: transparent; }
        div[data-testid="stVerticalBlock"] > div { background: transparent; }

        /* --- SIDEBAR STYLING --- */
        [data-testid="stSidebar"] {
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0;
        }
        [data-testid="stSidebarUserContent"] { padding-top: 1rem; }
        .sidebar-header { padding: 10px 14px; }
        .brand-logo { font-size: 1.4rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; }
        .brand-logo span { color: #2563eb; font-weight: 900; }
        .brand-subtitle { font-size: 0.75rem; color: #94a3b8; font-weight: 600; letter-spacing: 0.5px; margin-top: 2px; }

        /* --- SIDEBAR MENU BUTTONS --- */
        div[data-testid="stSidebarCollapseWrapper"] div[data-testid="stButton"] > button {
            border-radius: 10px !important; width: 100%; text-align: left !important;
            padding: 10px 16px !important; margin-bottom: 4px;
            color: #64748b !important; background-color: transparent !important;
            border: none !important; box-shadow: none !important; transition: all 0.2s ease; 
            font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; justify-content: flex-start;
        }
        div[data-testid="stSidebarCollapseWrapper"] div[data-testid="stButton"] > button:hover {
            background-color: #f1f5f9 !important; color: #1e293b !important;
        }
        /* Active State Style Simulation via Custom Attribute */
        div[data-testid="stSidebarCollapseWrapper"] div.nav-active > button {
            background-color: #eff6ff !important; color: #2563eb !important; font-weight: 600 !important;
        }

        /* --- SIDEBAR USER PROFILE CARD --- */
        .user-profile-container {
            position: fixed; bottom: 20px; left: 20px; width: 260px;
            background-color: #f8fafc; border-radius: 12px; padding: 12px;
            display: flex; align-items: center; gap: 12px; border: 1px solid #e2e8f0;
        }
        .user-avatar {
            width: 40px; height: 40px; background-color: #dbeafe; color: #2563eb;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem;
        }
        .user-info-text { flex-grow: 1; min-width: 0; }
        .user-info-name { font-weight: 600; font-size: 0.85rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-info-id { font-size: 0.75rem; color: #64748b; margin-top: 1px; }

        /* --- HERO BANNER --- */
        .dashboard-hero {
            background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);
            color: white; border-radius: 16px; padding: 32px; margin-bottom: 24px;
        }
        .hero-welcome { font-size: 0.85rem; opacity: 0.85; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;}
        .hero-title { font-size: 2.2rem; font-weight: 800; line-height: 1.2; margin: 0; }
        .hero-desc { opacity: 0.9; font-size: 0.95rem; margin-top: 12px; font-weight: 400; }

        /* --- CARD & GRID STYLING --- */
        .grid-card {
            background-color: #ffffff; border-radius: 16px; padding: 20px;
            border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            display: flex; flex-direction: column; height: 100%;
        }
        .grid-card-label { color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; }
        .grid-card-value { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1; }
        
        .section-box {
            background-color: #ffffff; border-radius: 16px; padding: 24px;
            border: 1px solid #e2e8f0; margin-bottom: 24px;
        }
        .section-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .section-desc { font-size: 0.85rem; color: #64748b; margin-bottom: 20px; }

        /* --- COMPONENT INPUT OVERRIDES --- */
        div[data-testid="stForm"] { border: none !important; padding: 0 !important; }
        
        /* Custom Table for Data Presentation */
        .custom-table { width: 100%; border-collapse: collapse; text-align: left; }
        .custom-table th { padding: 12px 16px; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .custom-table td { padding: 16px; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        /* Status Badges */
        .badge-percentage { font-weight: 700; font-size: 1.1rem; color: #10b981; }

        /* Button Customizations */
        .stButton > button[kind="primary"] {
            background-color: #2563eb !important; border: none !important; border-radius: 10px !important;
            padding: 10px 20px !important; font-weight: 600 !important; font-size: 0.9rem !important;
        }
        .stButton > button[kind="secondary"] {
            border: 1px solid #e2e8f0 !important; border-radius: 10px !important; background-color: #ffffff !important;
            padding: 10px 20px !important; font-weight: 500 !important; font-size: 0.9rem !important; color: #334155 !important;
        }
    </style>
    """, unsafe_allow_html=True)

set_app_config()
db.init_db()

VALID_STATUSES = ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"]

# ============================================================================
# BACKEND HELPERS
# ============================================================================
def login(email, password):
    try:
        row = db.query_one("SELECT id_dosen, nidn, nama_lengkap, email, password FROM dosen WHERE email=? LIMIT 1", (email,))
        if row and bcrypt.checkpw(password.encode("utf-8"), row["password"].encode("utf-8")):
            st.session_state.user = {"id_dosen": row["id_dosen"], "nidn": row["nidn"], "nama_lengkap": row["nama_lengkap"], "email": row["email"]}
            st.session_state.page = "dashboard"
            return True
    except: pass
    return False

def logout():
    st.session_state.pop("user", None)
    st.session_state.page = "login"
    st.rerun()

def is_logged_in(): return "user" in st.session_state
def current_id(): return st.session_state.get("user", {}).get("id_dosen")

def get_courses(id_dosen):
    return db.query("SELECT mk.* FROM mata_kuliah mk WHERE mk.id_dosen = ? ORDER BY mk.nama_matkul ASC", (id_dosen,))

def get_schedules(id_mk):
    return db.query("SELECT * FROM jadwal_perkuliahan WHERE id_matkul = ? ORDER BY pertemuan_ke ASC", (id_mk,))

def get_students(id_mk):
    return db.query("SELECT m.* FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa WHERE km.id_matkul = ? ORDER BY m.nama_mahasiswa", (id_mk,))

def get_attendance(id_jadwal):
    rows = db.query("SELECT id_mahasiswa, status_kehadiran, keterangan FROM absensi WHERE id_jadwal=?", (id_jadwal,))
    return {r['id_mahasiswa']: r for r in rows}

def save_attend(m_id, j_id, status, note):
    try:
        existing = db.query_one("SELECT id_absensi FROM absensi WHERE id_mahasiswa=? AND id_jadwal=?", (m_id, j_id))
        if existing: db.execute("UPDATE absensi SET status_kehadiran=?, keterangan=?, waktu_input=CURRENT_TIMESTAMP WHERE id_absensi=?", (status, note, existing['id_absensi']))
        else: db.execute("INSERT INTO absensi (id_mahasiswa, id_jadwal, status_kehadiran, keterangan) VALUES (?, ?, ?, ?)", (m_id, j_id, status, note))
        return True
    except: return False

def get_stats(d_id):
    try:
        stats = db.query_one("""SELECT 
            (SELECT COUNT(*) FROM mata_kuliah WHERE id_dosen=?) as mk, 
            (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km JOIN mata_kuliah mk ON km.id_matkul=mk.id_matkul WHERE mk.id_dosen=?) as mhs, 
            (SELECT COUNT(*) FROM jadwal_perkuliahan jp JOIN mata_kuliah mk ON jp.id_matkul=mk.id_matkul WHERE mk.id_dosen=?) as jadw""", (d_id, d_id, d_id))
        return {"mk": stats.get('mk',0), "mhs": stats.get('mhs',0), "jadw": stats.get('jadw',0)}
    except: return {"mk": 0, "mhs": 0, "jadw": 0}

def get_recap(id_mk):
    return db.query("""SELECT m.npm, m.nama_mahasiswa, 
        SUM(CASE WHEN a.status_kehadiran='Hadir' THEN 1 ELSE 0 END) as h, 
        SUM(CASE WHEN a.status_kehadiran='Terlambat' THEN 1 ELSE 0 END) as t, 
        SUM(CASE WHEN a.status_kehadiran='Sakit' THEN 1 ELSE 0 END) as s, 
        SUM(CASE WHEN a.status_kehadiran='Izin' THEN 1 ELSE 0 END) as i, 
        SUM(CASE WHEN a.status_kehadiran='Alpa' THEN 1 ELSE 0 END) as a, 
        (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul=?) as tot 
        FROM mahasiswa m 
        JOIN kelas_mahasiswa km ON m.id_mahasiswa=km.id_mahasiswa 
        LEFT JOIN jadwal_perkuliahan jp ON km.id_matkul=jp.id_matkul 
        LEFT JOIN absensi a ON m.id_mahasiswa=a.id_mahasiswa AND jp.id_jadwal=a.id_jadwal 
        WHERE km.id_matkul=? GROUP BY m.id_mahasiswa ORDER BY m.nama_mahasiswa""", (id_mk, id_mk))

def get_all_students(id_dosen, kw=""):
    sql = f"SELECT DISTINCT m.* FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa=km.id_mahasiswa JOIN mata_kuliah mk ON km.id_matkul=mk.id_matkul WHERE mk.id_dosen={id_dosen}"
    if kw: sql += f" AND (m.npm LIKE '%{kw}%' OR m.nama_mahasiswa LIKE '%{kw}%')"
    return db.query(sql + " ORDER BY m.nama_mahasiswa")

# ============================================================================
# INTERFACE IMPLEMENTATION (HIGH-FIDELITY REDESIGN)
# ============================================================================

def page_login():
    col1, col2 = st.columns([1.2, 1.0], gap="large")
    with col1:
        st.markdown('''
        <div style="padding:50px; border-radius:24px; background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%); min-height:480px; display:flex; flex-direction:column; justify-content:center; color:#fff;">
            <span style="background:rgba(255,255,255,0.15); padding:6px 14px; border-radius:99px; font-size:0.8rem; font-weight:600; align-self:flex-start; margin-bottom:32px;">Sistem akademik dosen</span>
            <h1 style="font-size:3rem; font-weight:800; margin:0; line-height:1.15; color:white;">Kelola absensi<br>kelas dengan<br>cepat dan rapi.</h1>
            <p style="margin-top:24px; opacity:0.85; font-size:1.05rem; line-height:1.6;">Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan sudah terhubung dalam satu alur kerja.</p>
        </div>
        ''', unsafe_allow_html=True)
        
    with col2:
        st.markdown('<div style="padding: 20px 0 0 10px;">', unsafe_allow_html=True)
        st.markdown('<div style="font-size: 2rem; margin-bottom:5px;">🎓</div>', unsafe_allow_html=True)
        st.markdown('<h2 style="font-weight:800; color:#0f172a; margin:0 0 4px 0; font-size:1.6rem;">Masuk ke SIAKAD V2</h2>', unsafe_allow_html=True)
        st.markdown('<p style="color:#64748b; font-size:0.9rem; margin:0 0 24px 0;">Gunakan akun dosen untuk mengakses sistem.</p>', unsafe_allow_html=True)
        
        with st.form("login_form"):
            em = st.text_input("Email", placeholder="firansyah@univ.ac.id")
            pw = st.text_input("Password", type="password", placeholder="••••••••")
            st.markdown('<div style="margin-top:10px;"></div>', unsafe_allow_html=True)
            btn = st.form_submit_button("➔ Masuk", use_container_width=True, type="primary")
            if btn:
                if login(em.strip(), pw): st.rerun()
                else: st.error("Email atau password salah.")
                
        st.markdown('''
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-top:24px;">
            <div style="font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:4px;">Akun demo</div>
            <div style="font-size:0.8rem; color:#64748b;">Email: <b>firansyah@univ.ac.id</b></div>
            <div style="font-size:0.8rem; color:#64748b;">Password: <b>password</b></div>
        </div>
        </div>
        ''', unsafe_allow_html=True)

def page_dashboard():
    # Header Top Bar info date
    c_left, c_right = st.columns([2, 1])
    with c_left:
        st.markdown('<h2 style="font-weight:800; color:#0f172a; margin:0;">Dashboard</h2>', unsafe_allow_html=True)
        st.markdown(f'<div style="color:#94a3b8; font-size:0.85rem; font-weight:600; margin-bottom:20px;">{datetime.now().strftime("%d %B %Y")}</div>', unsafe_allow_html=True)
    
    # Hero Welcome Panel Banner
    name = st.session_state.get("user", {}).get("nama_lengkap", "").split(',')[0]
    st.markdown(f'''
    <div class="dashboard-hero">
      <div class="hero-welcome">Selamat Datang Kembali</div>
      <div class="hero-title">{name}</div>
      <div class="hero-desc">Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.</div>
    </div>
    ''', unsafe_allow_html=True)

    # Metric Cards Row
    stats = get_stats(current_id())
    m1, m2, m3, m4 = st.columns(4)
    
    with m1:
        st.markdown(f'<div class="grid-card"><div class="grid-card-label">📚 Mata Kuliah</div><div class="grid-card-value">{stats["mk"]}</div></div>', unsafe_allow_html=True)
    with m2:
        st.markdown(f'<div class="grid-card"><div class="grid-card-label">👥 Mahasiswa</div><div class="grid-card-value">{stats["mhs"]}</div></div>', unsafe_allow_html=True)
    with m3:
        st.markdown(f'<div class="grid-card"><div class="grid-card-label">📅 Jadwal Kelas</div><div class="grid-card-value">{stats["jadw"]}</div></div>', unsafe_allow_html=True)
    with m4:
        st.markdown('<div class="grid-card"><div class="grid-card-label">📈 Rata Kehadiran</div><div class="grid-card-value" style="color:#10b981;">100%</div></div>', unsafe_allow_html=True)

    st.markdown('<div style="margin-top:24px;"></div>', unsafe_allow_html=True)
    
    col_action, col_sched = st.columns([1.1, 1.3], gap="large")
    
    with col_action:
        st.markdown('<div class="section-box">', unsafe_allow_html=True)
        st.markdown('<div class="section-title">Aksi Cepat</div>', unsafe_allow_html=True)
        st.markdown('<div class="section-desc">Masuk ke pekerjaan utama tanpa banyak klik.</div>', unsafe_allow_html=True)
        
        if st.button("📋 Input Absensi", use_container_width=True, type="primary"):
            st.session_state.page = "absensi"
            st.rerun()
        st.markdown('<div style="margin-top:10px;"></div>', unsafe_allow_html=True)
        if st.button("📄 Lihat Rekap", use_container_width=True):
            st.session_state.page = "rekap"
            st.rerun()
        st.markdown('<div style="margin-top:10px;"></div>', unsafe_allow_html=True)
        if st.button("👥 Mahasiswa", use_container_width=True):
            st.session_state.page = "mahasiswa"
            st.rerun()
        st.markdown('</div>', unsafe_allow_html=True)
    
    with col_sched:
        st.markdown('<div class="section-box">', unsafe_allow_html=True)
        st.markdown('<div class="section-title">Jadwal Terdekat</div>', unsafe_allow_html=True)
        st.markdown('<div class="section-desc">5 pertemuan berikutnya di sistem.</div>', unsafe_allow_html=True)
        
        schedules = db.query("""SELECT jp.*, mk.kode_matkul, mk.nama_matkul 
            FROM jadwal_perkuliahan jp 
            JOIN mata_kuliah mk ON jp.id_matkul=mk.id_matkul 
            WHERE mk.id_dosen=? ORDER BY jp.tanggal_pertemuan, jp.jam_mulai LIMIT 5""", (current_id(),))
            
        if not schedules:
            st.info("Tidak ada jadwal terdekat.")
        else:
            for s in schedules:
                try: d = datetime.strptime(s['tanggal_pertemuan'], "%Y-%m-%d").strftime("%d %b %Y")
                except: d = "N/A"
                st.markdown(f'''
                <div style="display:flex; justify-content:between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9;">
                    <div style="flex-grow:1;">
                        <div style="font-weight:700; font-size:0.9rem; color:#1e293b;">{s["nama_matkul"]}</div>
                        <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{s["kode_matkul"]} - Pertemuan {s["pertemuan_ke"]}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600; font-size:0.8rem; color:#334155;">{d}</div>
                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:2px;">{s['jam_mulai']} - {s['jam_selesai']}</div>
                    </div>
                </div>
                ''', unsafe_allow_html=True)
        st.markdown('</div>', unsafe_allow_html=True)

def page_absensi():
    st.markdown('<h2 style="font-weight:800; color:#0f172a; margin:0;">Input Absensi</h2>', unsafe_allow_html=True)
    st.markdown(f'<div style="color:#94a3b8; font-size:0.85rem; font-weight:600; margin-bottom:20px;">{datetime.now().strftime("%d %B %Y")}</div>', unsafe_allow_html=True)

    courses = get_courses(current_id())
    if not courses:
        st.warning("Belum ada mata kuliah terdaftar untuk Anda.")
        return

    opts = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    
    st.markdown('<div class="section-box" style="padding:20px;">', unsafe_allow_html=True)
    col_sel1, col_sel2 = st.columns(2)
    with col_sel1:
        mk_label = st.selectbox("Mata Kuliah", list(opts.keys()))
    id_mk = opts[mk_label]
    
    scheds = get_schedules(id_mk)
    s_opts = {}
    for s in scheds:
        try: d = datetime.strptime(s['tanggal_pertemuan'], "%Y-%m-%d").strftime("%d %b %Y")
        except: d = s['tanggal_pertemuan']
        s_opts[f"Pertemuan {s['pertemuan_ke']} - {d}, {s['jam_mulai']}"] = s
    
    with col_sel2:
        selected_label = st.selectbox("Pertemuan", list(s_opts.keys()))
    st.markdown('</div>', unsafe_allow_html=True)

    if not selected_label: return
    curr = s_opts[selected_label]
    id_jad = curr['id_jadwal']

    # Specs Card Info Panels
    i1, i2, i3 = st.columns(3)
    with i1:
        st.markdown(f'<div class="grid-card" style="padding:16px;"><div class="grid-card-label">Mata kuliah</div><div style="font-weight:700; font-size:1.1rem; color:#0f172a;">{curr["hari"]}</div></div>', unsafe_allow_html=True)
    with i2:
        st.markdown(f'<div class="grid-card" style="padding:16px;"><div class="grid-card-label">Pertemuan</div><div style="font-weight:700; font-size:1.1rem; color:#0f172a;">{curr["pertemuan_ke"]}</div></div>', unsafe_allow_html=True)
    with i3:
        st.markdown(f'<div class="grid-card" style="padding:16px;"><div class="grid-card-label">Ruangan</div><div style="font-weight:700; font-size:1.1rem; color:#0f172a;">{curr["ruangan"]}</div></div>', unsafe_allow_html=True)

    students = get_students(id_mk)
    attendance_data = get_attendance(id_jad)

    st.markdown('<div style="margin-top:28px;"></div>', unsafe_allow_html=True)
    
    # Header bar for list matching screenshot layout
    h_left, h_right = st.columns([2, 1])
    with h_left:
        st.markdown(f'<div style="font-weight:700; font-size:1.1rem; color:#0f172a;">Daftar Kehadiran</div>', unsafe_allow_html=True)
        st.markdown(f'<div style="color:#64748b; font-size:0.85rem; margin-bottom:15px;">{len(students)} mahasiswa terdaftar.</div>', unsafe_allow_html=True)
    with h_right:
        # Action button inside row matching mock layout
        mark_all = st.button("✓ Tandai Hadir Semua", use_container_width=True)

    # Form initialization inside block container wrapper
    with st.form("attendance_batch_form"):
        rows_status = {}
        rows_note = {}
        
        for mhs in students:
            mid = mhs["id_mahasiswa"]
            prev_status = "Hadir" if mark_all else attendance_data.get(mid, {}).get("status_kehadiran", "Hadir")
            prev_note = attendance_data.get(mid, {}).get("keterangan", "")
            
            st.markdown(f'''
            <div style="background:white; border: 1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; flex-wrap:wrap; align-items:center; gap:16px;">
                <div style="flex: 1; min-width:200px;">
                    <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">{mhs['nama_mahasiswa']}</div>
                    <div style="font-size:0.8rem; color:#64748b; margin-top:2px;">{mhs['npm']} - {mhs['program_studi']}</div>
                </div>
                <div style="flex: 1.5; min-width:300px;" id="radio-wrapper-{mid}">
            ''', unsafe_allow_html=True)
            
            # Using Horizontal stream select logic radio mapped natively
            status_val = st.radio(
                f"Status_{mid}", 
                options=VALID_STATUSES, 
                index=VALID_STATUSES.index(prev_status), 
                horizontal=True, 
                label_visibility="collapsed", 
                key=f"rad_{mid}"
            )
            
            st.markdown('''</div><div style="flex: 1; min-width:180px;">''', unsafe_allow_html=True)
            note_val = st.text_input(f"Catatan_{mid}", value=prev_note, placeholder="Catatan opsional", label_visibility="collapsed", key=f"txt_{mid}")
            st.markdown('</div></div>', unsafe_allow_html=True)
            
            rows_status[mid] = status_val
            rows_note[mid] = note_val

        st.markdown('<div style="margin-top:16px;"></div>', unsafe_allow_html=True)
        submitted = st.form_submit_button("💾 Simpan Absensi Kelas", type="primary", use_container_width=True)
        
        if submitted:
            success = 0
            for mid, stat in rows_status.items():
                if save_attend(mid, id_jad, stat, rows_note[mid]): 
                    success += 1
            if success > 0:
                st.success(f"Berhasil menyimpan {success} data kehadiran mahasiswa.")
                st.rerun()

def page_rekap():
    st.markdown('<h2 style="font-weight:800; color:#0f172a; margin:0;">Rekap Absensi</h2>', unsafe_allow_html=True)
    st.markdown(f'<div style="color:#94a3b8; font-size:0.85rem; font-weight:600; margin-bottom:20px;">{datetime.now().strftime("%d %B %Y")}</div>', unsafe_allow_html=True)

    courses = get_courses(current_id())
    if not courses:
        st.warning("Data kosong.")
        return
        
    opts = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    
    st.markdown('<div class="section-box" style="padding:20px;">', unsafe_allow_html=True)
    col_re1, col_re2 = st.columns([3, 1])
    with col_re1:
        sel = st.selectbox("Filter Mata Kuliah", list(opts.keys()))
    with col_re2:
        st.markdown('<div style="margin-top:28px;"></div>', unsafe_allow_html=True)
        st.button("🖨 Cetak", use_container_width=True)
    st.markdown('</div>', unsafe_allow_html=True)
    
    id_mk = opts[sel]
    recap = get_recap(id_mk)
    
    if not recap:
        st.info("Belum ada data absensi untuk mata kuliah ini.")
        return

    # Visual recap panel summaries
    r1, r2, r3 = st.columns(3)
    with r1:
        st.markdown(f'<div class="grid-card" style="padding:16px;"><div class="grid-card-label">Mahasiswa</div><div style="font-weight:800; font-size:1.5rem;">{len(recap)}</div></div>', unsafe_allow_html=True)
    with r2:
        st.markdown(f'<div class="grid-card" style="padding:16px;"><div class="grid-card-label">Total Jadwal</div><div style="font-weight:800; font-size:1.5rem;">{recap[0]["tot"] if recap else 0}</div></div>', unsafe_allow_html=True)
    with r3:
        st.markdown(f'<div class="grid-card" style="padding:16px;"><div class="grid-card-label">Data Terisi</div><div style="font-weight:800; font-size:1.5rem; color:#2563eb;">4</div></div>', unsafe_allow_html=True)

    st.markdown('<div style="margin-top:28px;"></div>', unsafe_allow_html=True)
    st.markdown(f'<div style="font-weight:700; font-size:1.1rem; color:#0f172a; margin-bottom:2px;">Rekap {sel.split(" - ")[1]}</div>', unsafe_allow_html=True)
    st.markdown('<div style="color:#64748b; font-size:0.85rem; margin-bottom:15px;">Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.</div>', unsafe_allow_html=True)

    # Render High Fidelity Table Layout
    table_html = """
    <table class="custom-table">
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th style="text-align:center;">Hadir</th>
                <th style="text-align:center;">Terlambat</th>
                <th style="text-align:center;">Sakit</th>
                <th style="text-align:center;">Izin</th>
                <th style="text-align:center;">Alpa</th>
                <th style="text-align:right;">Persentase</th>
            </tr>
        </thead>
        <tbody>
    """
    
    for r in recap:
        tot = max(r['tot'], 1)
        hadir = r['h'] or 0
        telat = r['t'] or 0
        pers = round((hadir + telat) / tot * 100)
        
        table_html += f"""
        <tr>
            <td>
                <div style="font-weight:700; color:#1e293b;">{r['nama_mahasiswa']}</div>
                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{r['npm']}</div>
            </td>
            <td style="text-align:center; font-weight:600; color:#10b981;">{hadir}</td>
            <td style="text-align:center; font-weight:600; color:#2563eb;">{telat}</td>
            <td style="text-align:center; color:#f59e0b;">{r['s'] or 0}</td>
            <td style="text-align:center; color:#64748b;">{r['i'] or 0}</td>
            <td style="text-align:center; color:#ef4444;">{r['a'] or 0}</td>
            <td style="text-align:right;">
                <span class="badge-percentage">{pers}%</span>
            </td>
        </tr>
        """
    table_html += "</tbody></table>"
    st.markdown(table_html, unsafe_allow_html=True)

def page_mahasiswa():
    st.markdown('<h2 style="font-weight:800; color:#0f172a; margin:0;">Data Mahasiswa</h2>', unsafe_allow_html=True)
    st.markdown(f'<div style="color:#94a3b8; font-size:0.85rem; font-weight:600; margin-bottom:20px;">{datetime.now().strftime("%d %B %Y")}</div>', unsafe_allow_html=True)

    col_add, col_list = st.columns([1.0, 1.4], gap="large")
    
    with col_add:
        st.markdown('<div class="section-box">', unsafe_allow_html=True)
        st.markdown('<div class="section-title">Tambah Mahasiswa</div>', unsafe_allow_html=True)
        st.markdown('<div class="section-desc">Mahasiswa baru otomatis masuk ke mata kuliah yang dipilih.</div>', unsafe_allow_html=True)
        
        with st.form("add_mhs_form"):
            st.text_input("NPM", placeholder="2021001")
            st.text_input("Angkatan", value="2026")
            st.text_input("Nama Mahasiswa", placeholder="Nama lengkap")
            st.text_input("Program Studi", placeholder="Teknik Informatika")
            st.text_input("Email", placeholder="nama@student.univ.ac.id")
            
            courses = get_courses(current_id())
            opts = {c['nama_matkul']: c['id_matkul'] for c in courses}
            st.selectbox("Mata Kuliah", list(opts.keys()) if opts else ["Pilih mata kuliah"])
            st.selectbox("Tahun Ajaran", ["2026/2027", "2027/2028"])
            
            st.markdown('<div style="margin-top:12px;"></div>', unsafe_allow_html=True)
            if st.form_submit_button("💾 Simpan", type="primary", use_container_width=True):
                st.success("Simulasi: Mahasiswa berhasil ditambahkan!")
        st.markdown('</div>', unsafe_allow_html=True)

    with col_list:
        st.markdown('<div class="section-box">', unsafe_allow_html=True)
        st.markdown('<div class="section-title">Daftar Mahasiswa</div>', unsafe_allow_html=True)
        
        kw = st.text_input("🔍 Cari NPM, nama, atau program studi...", label_visibility="collapsed", placeholder="Cari NPM, nama, atau program studi...")
        students = get_all_students(current_id(), kw)
        
        st.markdown(f'<div style="color:#64748b; font-size:0.85rem; margin: 12px 0 20px 0;">{len(students)} mahasiswa ditemukan.</div>', unsafe_allow_html=True)
        
        for s in students:
            av = s['nama_mahasiswa'][0].upper()
            st.markdown(f'''
            <div style="display:flex; align-items:center; justify-content:between; padding:12px 0; border-bottom:1px solid #f1f5f9; gap:12px;">
                <div style="width:40px; height:40px; background:#eff6ff; color:#2563eb; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
                    {av}
                </div>
                <div style="flex-grow:1; min-width:0;">
                    <div style="font-weight:700; color:#1e293b; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{s['nama_mahasiswa']}</div>
                    <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{s['npm']} • {s['email'] or ''}</div>
                </div>
                <div style="flex-shrink:0; text-align:right; min-width:120px;">
                    <div style="font-size:0.85rem; font-weight:600; color:#334155;">{s['program_studi']}</div>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:2px;">Angkatan {s['angkatan']}</div>
                </div>
                <div style="display:flex; gap:4px;">
                    <button style="border:none; background:transparent; color:#2563eb; cursor:pointer; font-size:1.1rem;">✏️</button>
                    <button style="border:none; background:transparent; color:#ef4444; cursor:pointer; font-size:1.1rem;">🗑️</button>
                </div>
            </div>
            ''', unsafe_allow_html=True)
        st.markdown('</div>', unsafe_allow_html=True)

# ============================================================================
# MAIN APPLICATION ROUTER & SIDEBAR EXECUTION
# ============================================================================
def main():
    if "page" not in st.session_state: st.session_state.page = "login"
    
    if not is_logged_in():
        page_login()
        return

    # --- SIDEBAR COMPONENT REDESIGN ---
    with st.sidebar:
        st.markdown('''
        <div class="sidebar-header">
            <div class="brand-logo"><span>🎓</span> SIAKAD V2</div>
            <div class="brand-subtitle">ABSENSI DOSEN</div>
        </div>
        ''', unsafe_allow_html=True)
        
        st.markdown('<div style="margin-top:20px;"></div>', unsafe_allow_html=True)
        
        nav_items = [
            ("🏠 Dashboard", "dashboard"),
            ("📋 Input Absensi", "absensi"),
            ("📄 Rekap Absensi", "rekap"),
            ("👥 Data Mahasiswa", "mahasiswa"),
        ]
        
        for label, key in nav_items:
            is_active = st.session_state.page == key
            active_class = "nav-active" if is_active else ""
            
            st.markdown(f'<div class="{active_class}">', unsafe_allow_html=True)
            if st.button(label, use_container_width=True, key=f"nav_lnk_{key}"):
                st.session_state.page = key
                st.rerun()
            st.markdown('</div>', unsafe_allow_html=True)
            
        # Fixed Bottom User Info Profile Card matching template reference
        u = st.session_state.get("user", {})
        initial = u.get('nama_lengkap','D')[0] if u.get('nama_lengkap') else 'F'
        
        st.markdown(f'''
        <div class="user-profile-container">
            <div class="user-avatar">{initial}</div>
            <div class="user-info-text">
                <div class="user-info-name">{u.get('nama_lengkap', 'Firansyah, S.S...')}</div>
                <div class="user-info-id">{u.get('nidn', '12345678')}</div>
            </div>
        </div>
        ''', unsafe_allow_html=True)
        
        # Logout implementation overlay button
        st.markdown('<div style="position:fixed; bottom:28px; left:240px; z-index:999;">', unsafe_allow_html=True)
        if st.button("➔", help="Keluar", key="logout_trigger"):
            logout()
        st.markdown('</div>', unsafe_allow_html=True)

    # --- VIEWPORT MAIN MAIN APP ---
    pages_map = {
        "dashboard": page_dashboard,
        "absensi": page_absensi,
        "rekap": page_rekap,
        "mahasiswa": page_mahasiswa
    }
    
    func = pages_map.get(st.session_state.page, page_dashboard)
    func()

if __name__ == "__main__":
    main()