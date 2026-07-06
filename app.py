"""
Sistem Absensi Dosen - Streamlit UI Redesign
Fitur: Sidebar Layout, Modern Styling, Prioritas Halaman Absensi
"""

import bcrypt
import streamlit as st
from datetime import date, datetime

# Import modul database
try:
    import db
except ImportError:
    # Fallback jika run sebagai script langsung tanpa import db module
    pass 

# --- CUSTOM STYLES ---
# Ini adalah inti dari perubahan tampilan agar "mirip dan rapi".
def set_app_config():
    st.set_page_config(
        page_title="SIKAD V2 - Absensi",
        page_icon="🎓",
        layout="wide",
        initial_sidebar_state="expanded"
    )
    
    # CSS Injeksi untuk Custom Styling
    st.markdown("""
    <style>
        /* --- Sidebar Styling --- */
        [data-testid="stSidebar"] {
            background-color: #f8fafc !important; /* Warna latar sidebar abu-abu muda */
            border-right: 1px solid #e2e8f0;
        }
        
        /* Logo & Header Sidebar */
        .sidebar-header {
            padding: 20px 25px 10px 25px;
        }
        .app-logo-text {
            font-size: 1.4rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;
        }
        .app-subtitle {
            font-size: 0.75rem; color: #64748b; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
        }

        /* --- Navigation Buttons --- */
        .menu-btn-container {
            padding: 5px 10px;
        }
        /* Tombol menu umum */
        div[data-testid="stButton"] > button {
            border-radius: 8px; width: 100%; text-align: left;
            padding: 10px 15px; margin-bottom: 4px;
            color: #475569; background-color: transparent !important;
            border: none; box-shadow: none; transition: all 0.2s ease;
        }
        div[data-testid="stButton"] > button:hover:not([aria-pressed=true]) {
            background-color: #e2e8f0; color: #0f172a; transform: translateX(3px);
        }
        
        /* Highlight tombol aktif */
        div[data-testid="stButton"] > button[aria-pressed=true] {
            background-color: #dbeafe !important; /* Biru muda */
            color: #1d4ed8 !important; /* Biru tua */
            font-weight: 600;
        }
        
        /* Tombol Logout */
        .logout-btn > button {
            color: #ef4444; background: transparent !important; border: 1px solid #fca5a5 !important; margin-top: auto;
        }
        .logout-btn > button:hover {
            background-color: #fef2f2; color: #dc2626;
        }

        /* --- User Profile di Bawah Sidebar --- */
        .user-profile-section {
            margin-top: auto; padding: 20px; border-top: 1px solid #e2e8f0;
            background-color: #f1f5f9; border-radius: 0 0 0 12px;
        }
        .user-avatar {
            width: 40px; height: 40px; background-color: #bfdbfe; color: #1e3a8a;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.1rem;
        }
        .user-info { margin-left: 12px; display: flex; flex-direction: column; }
        .user-name { font-weight: 700; font-size: 0.95rem; color: #1e293b; }
        .user-role { font-size: 0.75rem; color: #64748b; }

        /* --- Main Content Styling --- */
        /* Container putih dengan bayangan untuk section utama */
        .content-card {
            background-color: white; border-radius: 12px; padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px; border: 1px solid #f1f5f9;
        }
        
        /* Header Gradient Dashboard */
        .dashboard-hero {
            background: linear-gradient(135deg, #2563eb, #0f172a);
            color: white; border-radius: 12px; padding: 30px 30px;
            margin-bottom: 24px;
        }
        .hero-title { font-size: 2.2rem; font-weight: 800; line-height: 1.1; }
        .hero-subtitle { opacity: 0.9; font-size: 1rem; margin-top: 10px; }

        /* Metrics Cards */
        .metric-value { font-size: 2rem !important; font-weight: 800 !important; color: #0f172a; }
        .metric-label { color: #64748b; font-size: 0.9rem; margin-bottom: 4px; display: block; }

        /* Tables */
        thead tr th { color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; }
        
        /* Form Elements */
        input[type="text"], select { border-radius: 6px; border: 1px solid #cbd5e1 !important; }
        input[type="text"]:focus, select:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 2px rgba(59,130,246,0.2) !important; }

        /* Progress Bar Rekap */
        .stProgress > div { border-radius: 999px !important; height: 10px !important; }
        .stProgress { background-color: #e2e8f0 !important; }
    </style>
    """, unsafe_allow_html=True)


set_app_config()
# Inisialisasi Database
try:
    db.init_db()
except:
    pass

VALID_STATUSES = ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"]

# ============================================================================
# --- FUNGSI DATABASE & LOGIC (Copy-Paste dari kode sebelumnya) ---
# ============================================================================
# Fungsi login, logout, helper session
def login(email, password):
    try:
        row = db.query_one("SELECT id_dosen, nidn, nama_lengkap, email, password FROM dosen WHERE email=? LIMIT 1", (email,))
        if row and bcrypt.checkpw(password.encode("utf-8"), row["password"].encode("utf-8")):
            st.session_state.user = {"id_dosen": row["id_dosen"], "nidn": row["nidn"], "nama_lengkap": row["nama_lengkap"], "email": row["email"]}
            return True
    except: pass
    return False

def logout():
    st.session_state.pop("user", None)
    st.session_state.page = "login"

def is_logged_in(): return "user" in st.session_state
def current_id(): return st.session_state.get("user", {}).get("id_dosen")

# Fungsi Query (Disederhanakan untuk copy-paste aman, pastikan db.py ada)
def get_courses_by_dosen(id_dosen):
    try: return db.query("SELECT mk.*, (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km WHERE km.id_matkul = mk.id_matkul) AS total_mhs, (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul = mk.id_matkul) AS total_jadw FROM mata_kuliah mk WHERE mk.id_dosen = ? ORDER BY mk.nama_matkul ASC", (id_dosen,))
    except: return []

def get_schedules(id_matkul):
    try: return db.query("SELECT * FROM jadwal_perkuliahan WHERE id_matkul = ? ORDER BY pertemuan_ke ASC", (id_matkul,))
    except: return []

def get_students(id_matkul):
    try: return db.query("SELECT m.* FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa WHERE km.id_matkul = ? ORDER BY m.nama_mahasiswa", (id_matkul,))
    except: return []

def get_attendance(id_jadwal):
    try: 
        rows = db.query("SELECT id_mahasiswa, status_kehadiran, keterangan FROM absensi WHERE id_jadwal=?", (id_jadwal,))
        return {r['id_mahasiswa']: r for r in rows}
    except: return {}

def save_attend(m_id, j_id, status, note):
    try:
        existing = db.query_one("SELECT id_absensi FROM absensi WHERE id_mahasiswa=? AND id_jadwal=?", (m_id, j_id))
        if existing:
            db.execute("UPDATE absensi SET status_kehadiran=?, keterangan=?, waktu_input=CURRENT_TIMESTAMP WHERE id_absensi=?", (status, note, existing['id_absensi']))
        else:
            db.execute("INSERT INTO absensi (id_mahasiswa, id_jadwal, status_kehadiran, keterangan) VALUES (?, ?, ?, ?)", (m_id, j_id, status, note))
        return True
    except: return False

def get_stats(d_id):
    try:
        return {
            "mk": db.query_one("SELECT COUNT(*) as c FROM mata_kuliah WHERE id_dosen=?", (d_id,)).get('c', 0),
            "mhs": db.query_one("SELECT COUNT(DISTINCT km.id_mahasiswa) as c FROM kelas_mahasiswa km JOIN mata_kuliah mk ON km.id_matkul=mk.id_matkul WHERE mk.id_dosen=?", (d_id,)).get('c', 0),
            "jad": db.query_one("SELECT COUNT(*) as c FROM jadwal_perkuliahan jp JOIN mata_kuliah mk ON jp.id_matkul=mk.id_matkul WHERE mk.id_dosen=?", (d_id,)).get('c', 0),
        }
    except: return {"mk": 0, "mhs": 0, "jad": 0}

def get_recap(id_mk):
    try:
        return db.query("""
            SELECT m.npm, m.nama_mahasiswa, 
               SUM(CASE WHEN a.status_kehadiran='Hadir' THEN 1 ELSE 0 END) as h,
               SUM(CASE WHEN a.status_kehadiran='Terlambat' THEN 1 ELSE 0 END) as t,
               SUM(CASE WHEN a.status_kehadiran='Sakit' THEN 1 ELSE 0 END) as s,
               SUM(CASE WHEN a.status_kehadiran='Izin' THEN 1 ELSE 0 END) as i,
               SUM(CASE WHEN a.status_kehadiran='Alpa' THEN 1 ELSE 0 END) as a,
               (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul=?) as tot
            FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa=km.id_mahasiswa 
            LEFT JOIN jadwal_perkuliahan jp ON km.id_matkul=jp.id_matkul 
            LEFT JOIN absensi a ON m.id_mahasiswa=a.id_mahasiswa AND jp.id_jadwal=a.id_jadwal
            WHERE km.id_matkul=? GROUP BY m.id_mahasiswa ORDER BY m.nama_mahasiswa""", (id_mk, id_mk))
    except: return []

def get_all_students(id_dosen, kw=""):
    try:
        sql = f"SELECT DISTINCT m.* FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa=km.id_mahasiswa JOIN mata_kuliah mk ON km.id_matkul=mk.id_matkul WHERE mk.id_dosen={id_dosen}"
        if kw: sql += f" AND (m.npm LIKE '%{kw}%' OR m.nama_mahasiswa LIKE '%{kw}%')"
        return db.query(sql + " ORDER BY m.nama_mahasiswa")
    except: return []

def delete_student(sid, did):
    try:
        db.execute("DELETE FROM kelas_mahasiswa WHERE id_mahasiswa=? AND id_matkul IN (SELECT id_matkul FROM mata_kuliah WHERE id_dosen=?", (sid, did)))
        db.execute("DELETE FROM mahasiswa WHERE id_mahasiswa=? AND NOT EXISTS (SELECT 1 FROM kelas_mahasiswa WHERE id_mahasiswa=?)", (sid, sid))
        return True
    except: return False

# ============================================================================
# --- HALAMAN / PAGES ---
# ============================================================================

def page_login():
    col1, col2 = st.columns([1.1, 0.9])
    with col1:
        st.markdown("<div style='padding:50px;border-radius:24px;background:linear-gradient(135deg,#2563eb,#0f172a);height:100%;display:flex;flex-direction:column;justify-content:center;color:#fff'>", unsafe_allow_html=True)
        st.markdown("<span style='background:rgba(255,255,255,0.2);padding:6px 16px;border-radius:99px;font-size:0.8rem;font-weight:600'>Sistem akademik dosen</span>", unsafe_allow_html=True)
        st.markdown("<h1 style='font-size:3em;font-weight:800;margin:20px 0;line-height:1.1'>Kelola absensi kelas<br>dengan cepat.</h1>", unsafe_allow_html=True)
        st.markdown("<p>Dashboard, input kehadiran, dan rekap data terintegrasi dalam satu platform.</p>", unsafe_allow_html=True)
    with col2:
        st.markdown("## 🎓 Masuk ke SIAKAD")
        st.caption("Gunakan akun dosen")
        with st.form("login_form"):
            em = st.text_input("Email", placeholder="firansyah@univ.ac.id")
            pw = st.text_input("Password", type="password", placeholder="password")
            btn = st.form_submit_button("➤ Masuk", use_container_width=True, type="primary")
            if btn:
                if login(em.strip(), pw): st.rerun()
                else: st.error("Login gagal.")
        st.info("**Demo:** firansyah@univ.ac.id | Pass: password")


def page_dashboard():
    name = st.session_state.get("user", {}).get("nama_lengkap", "").split(',')[0]
    
    # Hero Section Gradient
    st.markdown(f"""
    <div class="dashboard-hero">
      <div style="opacity:0.8;font-size:0.9rem;margin-bottom:8px">SELAMAT DATANG KEMBALI,</div>
      <div class="hero-title">{name}</div>
      <div class="hero-subtitle">Pantau jadwal, kelola presensi, dan cek statistik kehadiran.</div>
    </div>
    """, unsafe_allow_html=True)

    stats = get_stats(current_id())
    c1,c2,c3 = st.columns(3)
    labels = ["Mata Kuliah", "Mahasiswa", "Jadwal Kelas"]
    values = [stats["mk"], stats["mhs"], stats["jad"]]
    
    for col, lbl, val in zip([c1,c2,c3], labels, values):
        st.markdown(f'<div class="metric-label">{lbl}</div><div class="metric-value">{val}</div>', unsafe_allow_html=True)

    st.divider()
    
    # Prioritas Utama: Tampilkan akses cepat ke Absensi
    col_action, col_sched = st.columns([1, 1.2])
    with col_action:
        st.markdown("### ⚡ Aksi Cepat")
        if st.button("📋 Buka Input Absensi Sekarang", use_container_width=True, type="primary"):
            st.session_state.page = "absensi"
            st.rerun()
        st.divider()
        st.button("📄 Lihat Rekapitulasi", use_container_width=True)
        st.button("👥 Kelola Data Mahasiswa", use_container_width=True)
    
    with col_sched:
        st.markdown("### 📅 Jadwal Terdekat")
        # Query sederhana untuk jadwal terdekat (simulasi data)
        schedules = db.query("SELECT jp.*, mk.kode_matkul, mk.nama_matkul FROM jadwal_perkuliahan jp JOIN mata_kuliah mk ON jp.id_matkul=mk.id_matkul WHERE mk.id_dosen=? ORDER BY jp.tanggal_pertemuan,jp.jam_mulai LIMIT 5", (current_id(),))
        if not schedules: st.info("Tidak ada jadwal.")
        for s in schedules:
            d = datetime.strptime(s['tanggal_pertemuan'], "%Y-%m-%d").strftime("%d %b %Y".upper())
            st.markdown(f"**{s['nama_matkul']}** - Ptg {s['pertemuan_ke']}")
            st.caption(f"{d} | {s['jam_mulai']}-{s['jam_selesai']}")
            st.divider()


def page_absensi():
    st.markdown("## 📋 Input Kehadiran")
    courses = get_courses_by_dosen(current_id())
    if not courses: st.warning("Belum ada mata kuliah."); return

    opts = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    col_sel = st.columns([1.2, 0.8])
    mk_label = col_sel[0].selectbox("Mata Kuliah", list(opts.keys()))
    id_mk = opts[mk_label]
    
    scheds = get_schedules(id_mk)
    s_opts = {}
    for s in scheds:
        d = datetime.strptime(s['tanggal_pertemuan'], "%Y-%m-%d").strftime("%d %b %Y".upper())
        s_opts[f"Pert. {s['pertemuan_ke']} ({d}, {s['jam_mulai']})"] = s
    
    selected_label = col_sel[1].selectbox("Pertemuan", list(s_opts.keys()))
    if selected_label == "Select...": st.stop() # Placeholder logic if empty
    
    curr = s_opts[selected_label]
    id_jad = curr['id_jadwal']
    
    info_cols = st.columns(3)
    info_cols[0].markdown(f"**Ruangan:** {curr['ruangan']}")
    info_cols[1].markdown(f"**Jam:** {curr['jam_mulai']} - {curr['jam_selesai']}")
    
    students = get_students(id_mk)
    attendance_data = get_attendance(id_jad)
    
    mark_all_hadir = st.button("✅ Tandai Hadir Semua")

    st.markdown("---")
    st.markdown("### Daftar Mahasiswa")
    
    # Form untuk menyimpan semua sekaligus
    form = st.form("form_absensi_batch")
    rows_status = {}
    rows_note = {}
    
    for mhs in students:
        mid = mhs["id_mahasiswa"]
        prev_status = attendance_data.get(mid, {}).get("status_kehadiran", "Hadir")
        prev_note = attendance_data.get(mid, {}).get("keterangan", "")
        if mark_all_hadir: prev_status = "Hadir"
        
        r_c = st.columns([2, 4, 2])
        r_c[0].markdown(f"**{mhs['nama_mahasiswa']}**<br><small>{mhs['npm']}</small>", unsafe_allow_html=True)
        status_val = r_c[1].radio("", options=VALID_STATUSES, index=VALID_STATUSES.index(prev_status), horizontal=True, label_visibility="collapsed", key=f"stat_{mid}")
        note_val = r_c[2].text_input("", value=prev_note, placeholder="Catatan", label_visibility="collapsed", key=f"note_{mid}")
        
        rows_status[mid] = status_val
        rows_note[mid] = note_val

    submitted = form.form_submit_button("💾 Simpan Semua Data", type="primary", use_container_width=True)
    if submitted:
        success = 0
        for mid, stat in rows_status.items():
            if save_attend(mid, id_jad, stat, rows_note[mid]): success += 1
        if success > 0:
            st.success(f"Berhasil menyimpan {success} data kehadiran.")
            st.balloons()


def page_rekap():
    st.markdown("## 📊 Rekapitulasi Kehadiran")
    courses = get_courses_by_dosen(current_id())
    if not courses: st.warning("Data kosong."); return
    
    opts = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    sel = st.selectbox("Pilih Mata Kuliah", list(opts.keys()))
    if not sel: return
    id_mk = opts[sel]
    
    recap = get_recap(id_mk)
    if not recap: st.info("Belum ada data absensi."); return
    
    st.markdown("#### Detail per Mahasiswa")
    th = st.columns(["Nama", "H", "T", "S", "I", "A", "P"])
    for l in ["Nama Mhs", "Hadir", "Telat", "Sakit", "Izin", "Alpa", "Presentase"]: th[list(th).index(l)].markdown(f"**{l}**")
    
    for r in recap:
        tot = max(r['tot'], 1)
        hadir = r['h'] or 0
        telat = r['t'] or 0
        pers = round((hadir+telat)/tot * 100)
        
        cols = st.columns(["Nama", "H", "T", "S", "I", "A", "P"])
        cols[0].markdown(f"**{r['nama_mahasiswa']}**<br><small>{r['npm']}</small>", unsafe_allow_html=True)
        cols[1].write(hadir)
        cols[2].write(telat)
        cols[3].write(r['s'])
        cols[4].write(r['i'])
        cols[5].write(r['a'])
        
        prog_color = "#10b981" if pers >= 75 else "#f59e0b"
        cols[6].progress(pers/100)
        cols[6].markdown(f"<span style='color:{prog_color};font-weight:bold'>{pers}%</span>", unsafe_allow_html=True)
        st.divider()


def page_mahasiswa():
    st.markdown("## 👥 Manajemen Mahasiswa")
    kw = st.sidebar.text_input("Cari...") # Search di sidebar lebih efisien
    students = get_all_students(current_id(), kw)
    
    col_add, col_list = st.columns([0.8, 1.4])
    
    with col_add:
        st.markdown("### Tambah Baru")
        with st.form("add_mhs"):
            npm = st.text_input("NPM")
            nm = st.text_input("Nama")
            prodi = st.text_input("Program Studi")
            ang = st.number_input("Angkatan", min_value=2000, max_value=2099, value=2024)
            
            # Link to course
            courses = get_courses_by_dosen(current_id())
            opts = {c['nama_matkul']: c['id_matkul'] for c in courses}
            mk_sel = st.selectbox("Masukkan ke MK", list(opts.keys()))
            
            if st.form_submit_button("Simpan", type="primary"):
                # Logic dummy insert for demo, real insert in DB required
                st.success("Simulasi: Data ditambahkan!")
                
    with col_list:
        st.markdown(f"### Daftar ({len(students)})")
        for s in students:
            av = s['nama_mahasiswa'][0].upper()
            c1,c2,c3,c4 = st.columns([3,2,1,1])
            c1.markdown(f"<div style='display:flex;gap:10px;align-items:center'><div style='width:35px;height:35px;background:#e0e7ff;color:#4338ca;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold'>{av}</div><div><b>{s['nama_mahasiswa']}</b><br><small>{s['npm']}</small></div></div>", unsafe_allow_html=True)
            c2.write(s['program_studi'])
            c3.write(s['angkatan'])
            if c4.button("🗑️", help="Hapus"):
                if st.confirm(f"Hapus {s['nama_mahasiswa']}?"):
                    if delete_student(s['id_mahasiswa'], current_id()): st.success("Deleted"); st.rerun()
            st.divider()


# ============================================================================
# --- UTAMA & NAVIGASI ---
# ============================================================================
def main():
    if "page" not in st.session_state: st.session_state.page = "login"
    
    if not is_logged_in():
        page_login()
        return

    # --- SIDEBAR NAVIGATION ---
    with st.sidebar:
        st.markdown('<div class="sidebar-header"><div class="app-logo-text">🎓 SIAKAD V2</div><div class="app-subtitle">Absensi Dosen</div></div>', unsafe_allow_html=True)
        st.divider()
        
        nav_items = [
            ("Dashboard", "dashboard"),
            ("Input Absensi", "absensi"), # Prioritas visual
            ("Rekap Absensi", "rekap"),
            ("Data Mahasiswa", "mahasiswa"),
        ]
        
        for label, key in nav_items:
            pressed = st.session_state.page == key
            # Custom Class untuk tombol aktif
            container_class = "menu-btn-container"
            if key == "absensi" and pressed:
                 container_class += " active-absen" 
            
            # Implementasi tombol dengan highlight khusus
            if st.button(label, use_container_width=True, type="primary" if pressed else "secondary", key=f"nav_{key}"):
                st.session_state.page = key
                st.rerun()
        
        st.divider()
        
        # User Info Footer
        u = st.session_state.get("user", {})
        st.markdown(f'''
        <div class="user-profile-section">
            <div style="display:flex;align-items:center">
                <div class="user-avatar">{u.get('nama_lengkap','')[0]}</div>
                <div class="user-info">
                    <div class="user-name">{u.get('nama_lengkap','')}</div>
                    <div class="user-role">{u.get('nidn','')}</div>
                </div>
            </div>
            <button onclick="window.parent.location.reload()" style="margin-top:10px;width:100%;padding:8px;border-radius:6px;border:1px solid #cbd5e1;background:white;cursor:pointer;color:#64748b;font-size:0.85rem">🚪 Keluar</button>
        </div>
        ''', unsafe_allow_html=True)

    # --- CONTENT AREA ---
    pages_map = {
        "dashboard": page_dashboard,
        "absensi": page_absensi,
        "rekap": page_rekap,
        "mahasiswa": page_mahasiswa
    }
    
    # Jalankan fungsi halaman yang dipilih
    func = pages_map.get(st.session_state.page, page_dashboard)
    func()

if __name__ == "__main__":
    main()