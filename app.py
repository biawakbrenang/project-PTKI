"""
Sistem Absensi Dosen - Streamlit UI Redesign
Fitur: Layout Mirip Gambar Referensi, Modern Styling, Prioritas Absensi
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
# CONFIGURATION & STYLING
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
        /* --- GLOBAL RESETS --- */
        .stApp { background-color: #f8fafc; }
        
        /* --- SIDEBAR STYLING --- */
        [data-testid="stSidebar"] {
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0;
        }
        .sidebar-header { padding: 24px 24px 10px 24px; }
        .brand-logo { font-size: 1.5rem; font-weight: 900; color: #2563eb; display: flex; align-items: center; gap: 12px; margin-bottom: 5px; }
        .brand-subtitle { font-size: 0.75rem; color: #64748b; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 5px; }

        /* --- MENU BUTTONS --- */
        div[data-testid="stButton"] > button {
            border-radius: 12px; width: 100%; text-align: left;
            padding: 12px 16px; margin-bottom: 6px;
            color: #475569; background-color: transparent !important;
            border: none; box-shadow: none; transition: all 0.2s ease; font-weight: 500; font-size: 0.95rem;
        }
        div[data-testid="stButton"] > button:hover:not([aria-pressed=true]) {
            background-color: #eff6ff; color: #2563eb; transform: translateX(4px);
        }
        
        /* Tombol Aktif */
        div[data-testid="stButton"] > button[aria-pressed=true] {
            background-color: #dbeafe !important;
            color: #1e40af !important;
            font-weight: 700;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);
        }

        /* --- USER PROFILE FOOTER SIDEBAR --- */
        .user-profile-card {
            margin-top: auto; padding: 20px 24px; border-top: 1px solid #e2e8f0;
            background-color: #f8fafc; border-radius: 0 0 0 16px;
            display: flex; align-items: center; gap: 12px; cursor: pointer;
        }
        .user-avatar-circle {
            width: 42px; height: 42px; background-color: #bfdbfe; color: #1e3a8a;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.1rem; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .user-details { display: flex; flex-direction: column; overflow: hidden; }
        .user-name { font-weight: 700; font-size: 0.9rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-nidn { font-size: 0.75rem; color: #64748b; }

        /* --- MAIN CONTENT CARDS --- */
        .content-card {
            background-color: #ffffff; border-radius: 16px; padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px; border: 1px solid #f1f5f9;
        }
        
        /* Hero Section Dashboard */
        .dashboard-hero {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white; border-radius: 16px; padding: 32px 32px;
            margin-bottom: 24px; position: relative; overflow: hidden;
        }
        .hero-title { font-size: 2.5rem; font-weight: 800; line-height: 1.1; margin: 0; }
        .hero-welcome { font-size: 0.9rem; opacity: 0.9; margin-bottom: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;}
        .hero-desc { opacity: 0.9; font-size: 1.05rem; max-width: 80%; }

        /* Metric Cards */
        .metric-value { font-size: 2.2rem !important; font-weight: 800 !important; color: #0f172a; }
        .metric-label { color: #64748b; font-size: 0.95rem; margin-bottom: 6px; display: block; font-weight: 600; }
        .metric-icon { font-size: 1.5rem; margin-right: 10px; vertical-align: middle; }

        /* Form Elements */
        div[data-testid="stSelectbox"], div[data-testid="stTextualiInput"] {
            background-color: #f8fafc; border: 1px solid #cbd5e1 !important; border-radius: 8px !important;
        }
        input[type="text"]:focus, select:focus {
            border-color: #3b82f6 !important; box-shadow: 0 0 0 2px rgba(59,130,246,0.1) !important;
        }
        
        /* Table Header */
        thead tr th { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 12px !important; }
        
        /* Progress Bar */
        .stProgress > div { border-radius: 999px !important; height: 10px !important; }
        .stProgress { background-color: #e2e8f0 !important; }
    </style>
    """, unsafe_allow_html=True)


set_app_config()
db.init_db()

VALID_STATUSES = ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"]

# ============================================================================
# BACKEND HELPERS (Database & Auth)
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

def is_logged_in(): return "user" in st.session_state
def current_id(): return st.session_state.get("user", {}).get("id_dosen")

# Query Functions
def get_courses(id_dosen):
    try: return db.query("SELECT mk.*, (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km WHERE km.id_matkul = mk.id_matkul) AS total_mhs FROM mata_kuliah mk WHERE mk.id_dosen = ? ORDER BY mk.nama_matkul ASC", (id_dosen,))
    except: return []

def get_schedules(id_mk):
    try: return db.query("SELECT * FROM jadwal_perkuliahan WHERE id_matkul = ? ORDER BY pertemuan_ke ASC", (id_mk,))
    except: return []

def get_students(id_mk):
    try: return db.query("SELECT m.* FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa WHERE km.id_matkul = ? ORDER BY m.nama_mahasiswa", (id_mk,))
    except: return []

def get_attendance(id_jadwal):
    try: rows = db.query("SELECT id_mahasiswa, status_kehadiran, keterangan FROM absensi WHERE id_jadwal=?", (id_jadwal,)); return {r['id_mahasiswa']: r for r in rows}
    except: return {}

def save_attend(m_id, j_id, status, note):
    try:
        existing = db.query_one("SELECT id_absensi FROM absensi WHERE id_mahasiswa=? AND id_jadwal=?", (m_id, j_id))
        if existing: db.execute("UPDATE absensi SET status_kehadiran=?, keterangan=?, waktu_input=CURRENT_TIMESTAMP WHERE id_absensi=?", (status, note, existing['id_absensi']))
        else: db.execute("INSERT INTO absensi (id_mahasiswa, id_jadwal, status_kehadiran, keterangan) VALUES (?, ?, ?, ?)", (m_id, j_id, status, note))
        return True
    except: return False

def get_stats(d_id):
    try:
        stats = db.query_one("SELECT (SELECT COUNT(*) FROM mata_kuliah WHERE id_dosen=?) as mk, (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km JOIN mata_kuliah mk ON km.id_matkul=mk.id_matkul WHERE mk.id_dosen=?) as mhs, (SELECT COUNT(*) FROM jadwal_perkuliahan jp JOIN mata_kuliah mk ON jp.id_matkul=mk.id_matkul WHERE mk.id_dosen=?) as jadw", (d_id, d_id, d_id))
        return {"mk": stats.get('mk',0), "mhs": stats.get('mhs',0), "jadw": stats.get('jadw',0)}
    except: return {"mk": 0, "mhs": 0, "jadw": 0}

def get_recap(id_mk):
    try:
        return db.query("""SELECT m.npm, m.nama_mahasiswa, SUM(CASE WHEN a.status_kehadiran='Hadir' THEN 1 ELSE 0 END) as h, SUM(CASE WHEN a.status_kehadiran='Terlambat' THEN 1 ELSE 0 END) as t, SUM(CASE WHEN a.status_kehadiran='Sakit' THEN 1 ELSE 0 END) as s, SUM(CASE WHEN a.status_kehadiran='Izin' THEN 1 ELSE 0 END) as i, SUM(CASE WHEN a.status_kehadiran='Alpa' THEN 1 ELSE 0 END) as a, (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul=?) as tot FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa=km.id_mahasiswa LEFT JOIN jadwal_perkuliahan jp ON km.id_matkul=jp.id_matkul LEFT JOIN absensi a ON m.id_mahasiswa=a.id_mahasiswa AND jp.id_jadwal=a.id_jadwal WHERE km.id_matkul=? GROUP BY m.id_mahasiswa ORDER BY m.nama_mahasiswa""", (id_mk, id_mk))
    except: return []

def get_all_students(id_dosen, kw=""):
    try:
        sql = f"SELECT DISTINCT m.* FROM mahasiswa m JOIN kelas_mahasiswa km ON m.id_mahasiswa=km.id_mahasiswa JOIN mata_kuliah mk ON km.id_matkul=mk.id_matkul WHERE mk.id_dosen={id_dosen}"
        if kw: sql += f" AND (m.npm LIKE '%{kw}%' OR m.nama_mahasiswa LIKE '%{kw}%')"
        return db.query(sql + " ORDER BY m.nama_mahasiswa")
    except: return []

def delete_student(sid, did):
    try:
        # PERBAIKAN DI SINI: Menambahkan tanda kutip penutup string dan kurung parameter dengan benar
        db.execute("DELETE FROM kelas_mahasiswa WHERE id_mahasiswa=? AND id_matkul IN (SELECT id_matkul FROM mata_kuliah WHERE id_dosen=?)", (sid, did))
        db.execute("DELETE FROM mahasiswa WHERE id_mahasiswa=? AND NOT EXISTS (SELECT 1 FROM kelas_mahasiswa WHERE id_mahasiswa=?)", (sid, sid))
        return True
    except Exception as e:
        print(f"Error deleting student: {e}")
        return False


# ============================================================================
# PAGES
# ============================================================================

def page_login():
    col1, col2 = st.columns([1.2, 0.9])
    with col1:
        st.markdown('<div style="padding:40px;border-radius:24px;background:linear-gradient(135deg,#2563eb,#1e3a8a);height:100%;display:flex;flex-direction:column;justify-content:center;color:#fff;">', unsafe_allow_html=True)
        st.markdown('<span style="background:rgba(255,255,255,0.2);padding:6px 16px;border-radius:99px;font-size:0.75rem;font-weight:700;align-self:flex-start;margin-bottom:20px">Sistem akademik dosen</span>', unsafe_allow_html=True)
        st.markdown('<h1 style="font-size:3em;font-weight:800;margin:0;line-height:1.1">Kelola absensi<br>kelas dengan cepat.</h1>')
        st.markdown('<p style="margin-top:20px;opacity:0.9;font-size:1.1rem">Dashboard, input kehadiran, dan rekap data terintegrasi dalam satu platform.</p>')
    with col2:
        st.markdown("---")
        st.markdown("## 🎓 Masuk ke SIAKAD")
        st.caption("Gunakan akun dosen untuk mengakses sistem.")
        with st.form("login_form"):
            em = st.text_input("Email", placeholder="firansyah@univ.ac.id")
            pw = st.text_input("Password", type="password", placeholder="••••••••")
            btn = st.form_submit_button("➤ Masuk", use_container_width=True, type="primary")
            if btn:
                if login(em.strip(), pw): st.rerun()
                else: st.error("Email atau password salah.")
        st.info("**Demo Akun:** firansyah@univ.ac.id | Password: password")


def page_dashboard():
    name = st.session_state.get("user", {}).get("nama_lengkap", "").split(',')[0]
    
    st.markdown(f'''
    <div class="dashboard-hero">
      <div class="hero-welcome">Selamat Datang Kembali,</div>
      <div class="hero-title">{name}</div>
      <div class="hero-desc" style="margin-top:10px">Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.</div>
    </div>
    ''', unsafe_allow_html=True)

    stats = get_stats(current_id())
    c1,c2,c3 = st.columns(3)
    labels = ["Mata Kuliah", "Mahasiswa", "Jadwal Kelas"]
    values = [stats["mk"], stats["mhs"], stats["jadw"]]
    icons = ["📚", "👥", "📅"]
    
    for col, lbl, val, icon in zip([c1,c2,c3], labels, values, icons):
        st.markdown(f'<div class="content-card" style="border:none;box-shadow:none;padding:20px"><div class="metric-label">{icon} {lbl}</div><div class="metric-value">{val}</div></div>', unsafe_allow_html=True)

    st.divider()
    
    col_action, col_sched = st.columns([1, 1.2])
    with col_action:
        st.markdown("### ⚡ Aksi Cepat")
        st.caption("Masuk ke pekerjaan utama tanpa banyak klik.")
        if st.button("📋 Input Absensi Sekarang", use_container_width=True, type="primary", key="btn_quick_absen"):
            st.session_state.page = "absensi"
            st.rerun()
        st.divider()
        if st.button("📄 Lihat Rekapitulasi", use_container_width=True):
             st.session_state.page = "rekap"; st.rerun()
        if st.button("👥 Kelola Data Mahasiswa", use_container_width=True):
             st.session_state.page = "mahasiswa"; st.rerun()
    
    with col_sched:
        st.markdown("### 📅 Jadwal Terdekat")
        schedules = db.query("SELECT jp.*, mk.kode_matkul, mk.nama_matkul FROM jadwal_perkuliahan jp JOIN mata_kuliah mk ON jp.id_matkul=mk.id_matkul WHERE mk.id_dosen=? ORDER BY jp.tanggal_pertemuan,jp.jam_mulai LIMIT 4", (current_id(),))
        if not schedules: st.info("Tidak ada jadwal minggu ini."); st.divider(); return
        for s in schedules:
            try: d = datetime.strptime(s['tanggal_pertemuan'], "%Y-%m-%d").strftime("%d %b %Y".upper())
            except: d = "N/A"
            st.markdown(f'**{s["nama_matkul"]}** - Pertemuan {s["pertemuan_ke"]}')
            st.caption(f"{d} | {s['jam_mulai']} - {s['jam_selesai']}")
            st.divider()


def page_absensi():
    st.markdown("## 📋 Input Kehadiran")
    courses = get_courses(current_id())
    if not courses: st.warning("Belum ada mata kuliah."); return

    opts = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    col_sel = st.columns([1.2, 0.9])
    mk_label = col_sel[0].selectbox("Mata Kuliah", list(opts.keys()))
    id_mk = opts[mk_label]
    
    scheds = get_schedules(id_mk)
    s_opts = {}
    for s in scheds:
        try: d = datetime.strptime(s['tanggal_pertemuan'], "%Y-%m-%d").strftime("%d %b %Y".upper())
        except: d = s['tanggal_pertemuan']
        s_opts[f"Pert. {s['pertemuan_ke']} ({d}, {s['jam_mulai']})"] = s
    
    selected_label = col_sel[1].selectbox("Pertemuan", list(s_opts.keys()))
    if not selected_label: return
    
    curr = s_opts[selected_label]
    id_jad = curr['id_jadwal']
    
    info_cols = st.columns(3)
    info_cols[0].markdown(f"**Ruangan**: {curr['ruangan']}")
    info_cols[1].markdown(f"**Jam**: {curr['jam_mulai']} - {curr['jam_selesai']}")
    
    students = get_students(id_mk)
    attendance_data = get_attendance(id_jad)
    
    mark_all_hadir = st.button("✅ Tandai Hadir Semua")

    st.markdown("---")
    st.markdown("### Daftar Mahasiswa")
    
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
        note_val = r_c[2].text_input("", value=prev_note, placeholder="Catatan opsional", label_visibility="collapsed", key=f"note_{mid}")
        
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
    courses = get_courses(current_id())
    if not courses: st.warning("Data kosong."); return
    
    opts = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    sel = st.selectbox("Pilih Mata Kuliah", list(opts.keys()))
    if not sel: return
    id_mk = opts[sel]
    
    recap = get_recap(id_mk)
    if not recap: st.info("Belum ada data absensi."); return
    
    st.markdown("#### Detail per Mahasiswa")
    th = st.columns(["Nama", "H", "T", "S", "I", "A", "P"])
    headers = ["Nama Mhs", "Hadir", "Telat", "Sakit", "Izin", "Alpa", "Persentase"]
    for l, col in zip(headers, th): col.markdown(f"**{l}**")
    
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
    kw = st.sidebar.text_input("Cari...")
    students = get_all_students(current_id(), kw)
    
    col_add, col_list = st.columns([0.9, 1.3])
    
    with col_add:
        st.markdown("### Tambah Baru")
        with st.form("add_mhs"):
            npm = st.text_input("NPM")
            nm = st.text_input("Nama")
            prodi = st.text_input("Program Studi")
            ang = st.number_input("Angkatan", min_value=2000, max_value=2099, value=date.today().year)
            courses = get_courses(current_id())
            opts = {c['nama_matkul']: c['id_matkul'] for c in courses}
            mk_sel = st.selectbox("Masukkan ke MK", list(opts.keys()) if opts else [])
            if st.form_submit_button("Simpan", type="primary"):
                if mk_sel: st.success("Simulasi: Data ditambahkan!"); st.rerun()

    with col_list:
        st.markdown(f"### Daftar ({len(students)})")
        for s in students:
            av = s['nama_mahasiswa'][0].upper()
            c1,c2,c3,c4 = st.columns([3,2,1,1])
            c1.markdown(f"<div style='display:flex;gap:10px;align-items:center'><div style='width:35px;height:35px;background:#e0e7ff;color:#4338ca;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold'>{av}</div><div><b>{s['nama_mahasiswa']}</b><br><small>{s['npm']}</small></div></div>", unsafe_allow_html=True)
            c2.write(s['program_studi'])
            c3.write(s['angkatan'])
            if c4.button("🗑️", help="Hapus", key=f"del_btn_{s['id_mahasiswa']}"):
                if st.confirm(f"Hapus {s['nama_mahasiswa']}?"):
                    if delete_student(s['id_mahasiswa'], current_id()): st.success("Deleted"); st.rerun()
            st.divider()


# ============================================================================
# MAIN EXECUTION
# ============================================================================
def main():
    if "page" not in st.session_state: st.session_state.page = "login"
    
    if not is_logged_in():
        page_login()
        return

    # --- SIDEBAR NAVIGATION ---
    with st.sidebar:
        st.markdown('<div class="sidebar-header"><div class="brand-logo">🎓 SIAKAD V2</div><div class="brand-subtitle">Absensi Dosen</div></div>', unsafe_allow_html=True)
        st.divider()
        
        nav_items = [
            ("🏠 Dashboard", "dashboard"),
            ("📋 Input Absensi", "absensi"),
            ("📄 Rekap Absensi", "rekap"),
            ("👥 Data Mahasiswa", "mahasiswa"),
        ]
        
        for label, key in nav_items:
            pressed = st.session_state.page == key
            st.button(label, use_container_width=True, type="primary" if pressed else "secondary", key=f"nav_{key}")
            if pressed and key != "dashboard": st.divider()
        
        st.divider()
        
        u = st.session_state.get("user", {})
        st.markdown(f'''
        <div class="user-profile-card">
            <div class="user-avatar-circle">{u.get('nama_lengkap','')[0]}</div>
            <div class="user-info">
                <div style="font-weight: 700; font-size: 0.9rem; color: #0f172a;">{u.get('nama_lengkap','')}</div>
                <div style="font-size: 0.75rem; color: #64748b;">{u.get('nidn','')}</div>
            </div>
            <button onclick="alert('Keluar')" style="margin-left:auto;width:32px;height:32px;border-radius:50%;border:1px solid #cbd5e1;background:white;cursor:pointer;color:#ef4444;display:flex;align-items:center;justify-content:center">↺</button>
        </div>
        ''', unsafe_allow_html=True)

    # --- CONTENT AREA ---
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