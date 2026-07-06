"""
Sistem Absensi Dosen - versi Streamlit
Konversi dari aplikasi PHP (ABSENSI-DOSEN) di folder PEMOGRAMAN WEB KELOMPOK 4.

Menjalankan lokal:
    pip install -r requirements.txt
    streamlit run app.py

Login demo:
    Email    : firansyah@univ.ac.id
    Password : password
"""

import bcrypt
import streamlit as st
from datetime import date, datetime

import db

st.set_page_config(
    page_title="Sistem Absensi Dosen",
    page_icon="🎓",
    layout="wide",
)

db.init_db()

VALID_STATUSES = ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"]
STATUS_COLOR = {
    "Hadir": "🟢",
    "Terlambat": "🔵",
    "Sakit": "🟡",
    "Izin": "🔷",
    "Alpa": "🔴",
}

# Palet warna badge status (bg, fg)
STATUS_BADGE = {
    "Hadir": ("#DCFCE7", "#15803D"),
    "Terlambat": ("#DBEAFE", "#1D4ED8"),
    "Sakit": ("#FEF3C7", "#B45309"),
    "Izin": ("#EDE9FE", "#6D28D9"),
    "Alpa": ("#FEE2E2", "#B91C1C"),
}

NAV_ITEMS = [
    ("dashboard", "🏠", "Dashboard"),
    ("absensi", "📋", "Input Absensi"),
    ("mahasiswa", "👥", "Mahasiswa"),
    ("rekap", "📄", "Rekap"),
]


# --------------------------------------------------------------------------
# Auth helpers
# --------------------------------------------------------------------------
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
    st.session_state.pop("user", None)
    st.session_state.page = "dashboard"


def is_logged_in() -> bool:
    return "user" in st.session_state


def current_dosen_id() -> int:
    return st.session_state.user["id_dosen"]


def flash(message, kind="success"):
    st.session_state.flash = (kind, message)


def show_flash():
    if "flash" in st.session_state:
        kind, message = st.session_state.pop("flash")
        getattr(st, kind if kind in ("success", "error", "info", "warning") else "info")(message)


# --------------------------------------------------------------------------
# UI helpers (tema visual)
# --------------------------------------------------------------------------
def inject_css():
    st.markdown(
        """
        <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap');

        html, body, [class*="css"] { font-family: 'Source Sans Pro', sans-serif; }
        h1, h2, h3, h4, .page-header h2 { font-family: 'Plus Jakarta Sans', sans-serif; }

        .stApp { background: #F3F5F9; }
        #MainMenu, footer, header[data-testid="stHeader"] { visibility: hidden; height: 0; }
        .block-container { padding-top: 1.6rem; padding-bottom: 3rem; max-width: 1200px; }

        /* Sidebar */
        section[data-testid="stSidebar"] {
            background: linear-gradient(180deg, #0F172A 0%, #111827 100%);
        }
        section[data-testid="stSidebar"] * { color: #E5E7EB !important; }
        section[data-testid="stSidebar"] hr { border-color: #ffffff22; }

        section[data-testid="stSidebar"] button {
            border-radius: 10px !important;
            border: 1px solid transparent !important;
            text-align: left !important;
            justify-content: flex-start !important;
            font-weight: 600 !important;
            padding: 0.55rem 0.9rem !important;
            margin-bottom: 2px;
        }
        section[data-testid="stSidebar"] button[kind="secondary"] {
            background: transparent !important;
            color: #CBD5E1 !important;
        }
        section[data-testid="stSidebar"] button[kind="secondary"]:hover {
            background: #ffffff14 !important;
            border-color: #ffffff22 !important;
        }
        section[data-testid="stSidebar"] button[kind="primary"] {
            background: #2563EB !important;
            box-shadow: 0 4px 10px rgba(37,99,235,0.35);
        }

        .sidebar-brand {
            display:flex; align-items:center; gap:10px; margin-bottom: 4px;
        }
        .sidebar-brand .logo {
            width:38px; height:38px; border-radius:10px;
            background:linear-gradient(135deg,#3B82F6,#1E3A8A);
            display:flex; align-items:center; justify-content:center; font-size:1.1rem;
        }
        .sidebar-brand .title { font-weight:800; font-size:1.05rem; }
        .sidebar-user {
            display:flex; align-items:center; gap:10px;
            background:#ffffff0d; border:1px solid #ffffff1a;
            border-radius:12px; padding:10px 12px; margin: 10px 0 6px 0;
        }
        .sidebar-user .avatar {
            width:34px; height:34px; border-radius:50%; background:#2563EB;
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:0.85rem; color:white; flex-shrink:0;
        }
        .sidebar-user .meta { line-height:1.2; overflow:hidden; }
        .sidebar-user .meta .name { font-weight:700; font-size:0.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sidebar-user .meta .role { font-size:0.72rem; color:#94A3B8 !important; }

        /* Page header */
        .page-header h2 { margin-bottom:2px; font-weight:800; color:#0F172A; }
        .page-header p { margin-top:0; color:#64748B; font-size:0.95rem; }

        /* KPI cards */
        .kpi-card {
            background:white; border:1px solid #E7EAF0; border-radius:16px;
            padding:18px 18px; display:flex; align-items:center; gap:14px;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
            height:100%;
        }
        .kpi-card .icon {
            width:44px; height:44px; border-radius:12px;
            display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;
        }
        .kpi-card .value { font-size:1.5rem; font-weight:800; color:#0F172A; line-height:1.1; }
        .kpi-card .label { font-size:0.8rem; color:#64748B; font-weight:600; }

        /* Generic white card */
        div[data-testid="stVerticalBlockBorderWrapper"] {
            border-radius: 16px !important;
            border: 1px solid #E7EAF0 !important;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        }
        div[data-testid="stForm"] {
            border-radius: 16px !important;
            border: 1px solid #E7EAF0 !important;
            background: white;
            padding: 1.4rem 1.4rem 0.6rem 1.4rem !important;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        }

        /* Buttons in main area */
        .main .stButton button, .block-container .stButton button {
            border-radius: 10px !important;
            font-weight: 600 !important;
        }
        .main button[kind="primary"], .block-container button[kind="primary"] {
            background: #2563EB !important; border-color:#2563EB !important;
        }

        /* Badges */
        .badge {
            display:inline-block; padding:3px 12px; border-radius:999px;
            font-size:0.78rem; font-weight:700;
        }

        /* Text inputs */
        .stTextInput input, .stNumberInput input, .stSelectbox div[data-baseweb="select"] {
            border-radius:10px !important;
        }
        </style>
        """,
        unsafe_allow_html=True,
    )


def render_header(title: str, subtitle: str = ""):
    st.markdown(
        f"""<div class="page-header"><h2>{title}</h2>{f'<p>{subtitle}</p>' if subtitle else ''}</div>""",
        unsafe_allow_html=True,
    )


def kpi_card(icon, label, value, bg="#EFF6FF", fg="#2563EB"):
    st.markdown(
        f"""
        <div class="kpi-card">
            <div class="icon" style="background:{bg};color:{fg};">{icon}</div>
            <div>
                <div class="value">{value}</div>
                <div class="label">{label}</div>
            </div>
        </div>
        """,
        unsafe_allow_html=True,
    )


def status_badge(status: str) -> str:
    bg, fg = STATUS_BADGE.get(status, ("#F1F5F9", "#334155"))
    return f'<span class="badge" style="background:{bg};color:{fg};">{status}</span>'


def initials(name: str) -> str:
    parts = [p for p in name.replace(",", " ").split() if p.isalpha()]
    if not parts:
        return "?"
    if len(parts) == 1:
        return parts[0][:2].upper()
    return (parts[0][0] + parts[1][0]).upper()


# --------------------------------------------------------------------------
# Data access helpers (mengikuti models/*.php)
# --------------------------------------------------------------------------
def get_courses_by_dosen(id_dosen):
    return db.query(
        """
        SELECT mk.*,
               (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km WHERE km.id_matkul = mk.id_matkul) AS total_mahasiswa,
               (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul = mk.id_matkul) AS total_jadwal
        FROM mata_kuliah mk
        WHERE mk.id_dosen = ?
        ORDER BY mk.semester ASC, mk.nama_matkul ASC
        """,
        (id_dosen,),
    )


def get_course_by_dosen(id_matkul, id_dosen):
    return db.query_one(
        "SELECT * FROM mata_kuliah WHERE id_matkul = ? AND id_dosen = ? LIMIT 1",
        (id_matkul, id_dosen),
    )


def get_schedules_by_course(id_matkul):
    return db.query(
        "SELECT * FROM jadwal_perkuliahan WHERE id_matkul = ? ORDER BY pertemuan_ke ASC",
        (id_matkul,),
    )


def get_students_by_course(id_matkul):
    return db.query(
        """
        SELECT m.* FROM mahasiswa m
        JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
        WHERE km.id_matkul = ? ORDER BY m.nama_mahasiswa ASC
        """,
        (id_matkul,),
    )


def get_attendance_by_schedule(id_jadwal):
    rows = db.query(
        "SELECT id_mahasiswa, status_kehadiran, keterangan FROM absensi WHERE id_jadwal = ?",
        (id_jadwal,),
    )
    return {row["id_mahasiswa"]: row for row in rows}


def save_attendance(id_mahasiswa, id_jadwal, status, keterangan):
    existing = db.query_one(
        "SELECT id_absensi FROM absensi WHERE id_mahasiswa = ? AND id_jadwal = ?",
        (id_mahasiswa, id_jadwal),
    )
    if existing:
        db.execute(
            "UPDATE absensi SET status_kehadiran = ?, keterangan = ?, waktu_input = CURRENT_TIMESTAMP WHERE id_absensi = ?",
            (status, keterangan, existing["id_absensi"]),
        )
    else:
        db.execute(
            "INSERT INTO absensi (id_mahasiswa, id_jadwal, status_kehadiran, keterangan) VALUES (?, ?, ?, ?)",
            (id_mahasiswa, id_jadwal, status, keterangan),
        )


def get_attendance_recap(id_matkul):
    return db.query(
        """
        SELECT m.id_mahasiswa, m.npm, m.nama_mahasiswa,
               SUM(CASE WHEN a.status_kehadiran = 'Hadir' THEN 1 ELSE 0 END) as hadir,
               SUM(CASE WHEN a.status_kehadiran = 'Sakit' THEN 1 ELSE 0 END) as sakit,
               SUM(CASE WHEN a.status_kehadiran = 'Izin' THEN 1 ELSE 0 END) as izin,
               SUM(CASE WHEN a.status_kehadiran = 'Alpa' THEN 1 ELSE 0 END) as alpa,
               SUM(CASE WHEN a.status_kehadiran = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
               (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul = ?) as total_jadwal
        FROM mahasiswa m
        JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
        LEFT JOIN jadwal_perkuliahan jp ON km.id_matkul = jp.id_matkul
        LEFT JOIN absensi a ON m.id_mahasiswa = a.id_mahasiswa AND jp.id_jadwal = a.id_jadwal
        WHERE km.id_matkul = ?
        GROUP BY m.id_mahasiswa
        ORDER BY m.nama_mahasiswa ASC
        """,
        (id_matkul, id_matkul),
    )


def get_course_summary(id_matkul):
    return db.query_one(
        """
        SELECT
            (SELECT COUNT(DISTINCT km.id_mahasiswa) FROM kelas_mahasiswa km WHERE km.id_matkul = ?) AS total_mahasiswa,
            (SELECT COUNT(DISTINCT jp.id_jadwal) FROM jadwal_perkuliahan jp WHERE jp.id_matkul = ?) AS total_jadwal,
            (SELECT COUNT(a.id_absensi) FROM absensi a JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal WHERE jp.id_matkul = ?) AS total_absensi
        """,
        (id_matkul, id_matkul, id_matkul),
    )


def get_dashboard_stats(id_dosen):
    stats = {}
    stats["total_matkul"] = db.query_one(
        "SELECT COUNT(*) as total FROM mata_kuliah WHERE id_dosen = ?", (id_dosen,)
    )["total"]

    stats["total_mahasiswa"] = db.query_one(
        """SELECT COUNT(DISTINCT id_mahasiswa) as total FROM kelas_mahasiswa km
           JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul WHERE mk.id_dosen = ?""",
        (id_dosen,),
    )["total"]

    stats["total_jadwal"] = db.query_one(
        """SELECT COUNT(*) as total FROM jadwal_perkuliahan jp
           JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul WHERE mk.id_dosen = ?""",
        (id_dosen,),
    )["total"]

    row = db.query_one(
        """SELECT SUM(CASE WHEN a.status_kehadiran IN ('Hadir','Terlambat') THEN 1 ELSE 0 END) as hadir_total,
                  COUNT(a.id_absensi) as total
           FROM absensi a
           JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal
           JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
           WHERE mk.id_dosen = ?""",
        (id_dosen,),
    )
    if row and row["total"]:
        stats["rata_kehadiran"] = round((row["hadir_total"] or 0) / row["total"] * 100)
    else:
        stats["rata_kehadiran"] = 0

    return stats


def get_upcoming_schedules(id_dosen, limit=5):
    return db.query(
        """
        SELECT jp.*, mk.kode_matkul, mk.nama_matkul
        FROM jadwal_perkuliahan jp
        JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
        WHERE mk.id_dosen = ?
        ORDER BY jp.tanggal_pertemuan ASC, jp.jam_mulai ASC
        LIMIT ?
        """,
        (id_dosen, limit),
    )


# Student model
def student_find(id_mahasiswa, id_dosen):
    return db.query_one(
        """
        SELECT DISTINCT m.* FROM mahasiswa m
        JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
        JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
        WHERE m.id_mahasiswa = ? AND mk.id_dosen = ? LIMIT 1
        """,
        (id_mahasiswa, id_dosen),
    )


def student_get_by_lecturer(id_dosen, keyword=""):
    sql = """
        SELECT DISTINCT m.* FROM mahasiswa m
        JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
        JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
        WHERE mk.id_dosen = ?
    """
    params = [id_dosen]
    if keyword:
        sql += " AND (m.npm LIKE ? OR m.nama_mahasiswa LIKE ? OR m.program_studi LIKE ?)"
        like = f"%{keyword}%"
        params += [like, like, like]
    sql += " ORDER BY m.nama_mahasiswa ASC"
    return db.query(sql, tuple(params))


def student_create(data, id_matkul):
    conn = db.get_conn()
    try:
        cur = conn.execute(
            """INSERT INTO mahasiswa (npm, nama_mahasiswa, program_studi, angkatan, email)
               VALUES (?, ?, ?, ?, ?)""",
            (
                data["npm"],
                data["nama_mahasiswa"],
                data["program_studi"],
                data["angkatan"],
                data["email"] or None,
            ),
        )
        id_mahasiswa = cur.lastrowid
        conn.execute(
            "INSERT OR IGNORE INTO kelas_mahasiswa (id_mahasiswa, id_matkul, tahun_ajaran) VALUES (?, ?, ?)",
            (id_mahasiswa, id_matkul, data["tahun_ajaran"]),
        )
        conn.commit()
    finally:
        conn.close()


def student_update(id_mahasiswa, data):
    db.execute(
        """UPDATE mahasiswa SET npm = ?, nama_mahasiswa = ?, program_studi = ?, angkatan = ?, email = ?
           WHERE id_mahasiswa = ?""",
        (
            data["npm"],
            data["nama_mahasiswa"],
            data["program_studi"],
            data["angkatan"],
            data["email"] or None,
            id_mahasiswa,
        ),
    )


def student_delete_for_lecturer(id_mahasiswa, id_dosen):
    conn = db.get_conn()
    try:
        conn.execute(
            """DELETE FROM kelas_mahasiswa WHERE id_mahasiswa = ? AND id_matkul IN
               (SELECT id_matkul FROM mata_kuliah WHERE id_dosen = ?)""",
            (id_mahasiswa, id_dosen),
        )
        conn.execute(
            """DELETE FROM mahasiswa WHERE id_mahasiswa = ? AND id_mahasiswa NOT IN
               (SELECT id_mahasiswa FROM kelas_mahasiswa)""",
            (id_mahasiswa,),
        )
        conn.commit()
    finally:
        conn.close()


# --------------------------------------------------------------------------
# Pages
# --------------------------------------------------------------------------
def page_login():
    inject_css()
    st.markdown("<div style='height:2.2rem'></div>", unsafe_allow_html=True)
    left, right = st.columns([1.15, 0.85], gap="large")
    with left:
        st.markdown(
            """
            <div style="background:linear-gradient(135deg,#2563eb,#1e3a8a);border-radius:24px;
                        padding:48px;color:white;height:100%;">
              <div style="display:inline-block;background:rgba(255,255,255,0.15);
                          padding:6px 16px;border-radius:999px;font-size:0.85rem;font-weight:600;">
                🛡️ Sistem akademik dosen
              </div>
              <h1 style="font-size:2.4rem;font-weight:900;margin-top:24px;line-height:1.2;">
                Kelola absensi kelas dengan cepat dan rapi.
              </h1>
              <p style="margin-top:16px;color:#dbeafe;font-size:1.05rem;">
                Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan
                sudah terhubung dalam satu alur kerja.
              </p>
              <div style="margin-top:36px;display:flex;gap:28px;">
                <div><div style="font-size:1.4rem;font-weight:800;">100%</div>
                  <div style="color:#bfdbfe;font-size:0.8rem;">Tersimpan di database</div></div>
                <div><div style="font-size:1.4rem;font-weight:800;">Real-time</div>
                  <div style="color:#bfdbfe;font-size:0.8rem;">Update rekap otomatis</div></div>
              </div>
            </div>
            """,
            unsafe_allow_html=True,
        )
    with right:
        with st.container(border=True):
            st.markdown(
                """<div class="sidebar-brand" style="margin-bottom:6px;">
                    <div class="logo" style="background:linear-gradient(135deg,#3B82F6,#1E3A8A);">🎓</div>
                    <div class="title" style="color:#0F172A;font-size:1.15rem;">Masuk ke SIAKAD V2</div>
                </div>""",
                unsafe_allow_html=True,
            )
            st.caption("Gunakan akun dosen untuk mengakses sistem.")
            with st.form("login_form"):
                email = st.text_input("Email", placeholder="firansyah@univ.ac.id")
                password = st.text_input("Password", type="password", placeholder="Masukkan password")
                submitted = st.form_submit_button("Masuk", use_container_width=True, type="primary")
                if submitted:
                    if login(email.strip(), password):
                        st.session_state.page = "dashboard"
                        st.rerun()
                    else:
                        st.error("Email atau password salah.")

            st.info("**Akun demo**\n\nEmail: firansyah@univ.ac.id\n\nPassword: password")


def page_dashboard():
    nama_depan = st.session_state.user['nama_lengkap'].split(',')[0]
    render_header(
        f"Selamat datang kembali, {nama_depan} 👋",
        "Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.",
    )

    stats = get_dashboard_stats(current_dosen_id())
    c1, c2, c3, c4 = st.columns(4)
    with c1:
        kpi_card("📚", "Mata Kuliah", stats["total_matkul"], "#EFF6FF", "#2563EB")
    with c2:
        kpi_card("🎓", "Mahasiswa", stats["total_mahasiswa"], "#ECFDF5", "#059669")
    with c3:
        kpi_card("🗓️", "Jadwal Kelas", stats["total_jadwal"], "#FEF3C7", "#B45309")
    with c4:
        kpi_card("📈", "Rata Kehadiran", f"{stats['rata_kehadiran']}%", "#EDE9FE", "#6D28D9")

    st.write("")
    st.divider()
    col1, col2 = st.columns([1, 1])
    with col1:
        st.markdown("#### ⚡ Aksi Cepat")
        st.caption("Masuk ke pekerjaan utama tanpa banyak klik.")
        a, b, cc = st.columns(3)
        if a.button("📋 Input Absensi", use_container_width=True):
            st.session_state.page = "absensi"
            st.rerun()
        if b.button("📄 Lihat Rekap", use_container_width=True):
            st.session_state.page = "rekap"
            st.rerun()
        if cc.button("👥 Mahasiswa", use_container_width=True):
            st.session_state.page = "mahasiswa"
            st.rerun()

    with col2:
        st.markdown("#### 🗓️ Jadwal Terdekat")
        st.caption("5 pertemuan berikutnya di sistem.")
        schedules = get_upcoming_schedules(current_dosen_id())
        if not schedules:
            st.info("Belum ada jadwal perkuliahan.")
        for s in schedules:
            with st.container(border=True):
                sc1, sc2 = st.columns([2, 1])
                sc1.markdown(f"**{s['nama_matkul']}**  \n"
                             f"<span style='color:#64748B;font-size:0.85em'>{s['kode_matkul']} · Pertemuan {s['pertemuan_ke']}</span>",
                             unsafe_allow_html=True)
                try:
                    tanggal = datetime.strptime(s["tanggal_pertemuan"], "%Y-%m-%d").strftime("%d %b %Y")
                except ValueError:
                    tanggal = s["tanggal_pertemuan"]
                sc2.markdown(
                    f"<div style='text-align:right'><b>{tanggal}</b><br>"
                    f"<span style='color:#64748B'>{s['jam_mulai']} - {s['jam_selesai']}</span></div>",
                    unsafe_allow_html=True,
                )


def page_absensi():
    render_header("📋 Input Absensi", "Catat kehadiran mahasiswa per pertemuan, tersimpan langsung ke database.")
    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    col1, col2 = st.columns(2)
    course_label = col1.selectbox("Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))

    if course_label == "Pilih mata kuliah":
        st.info("Pilih mata kuliah dan pertemuan untuk mulai input absensi.")
        return

    id_matkul = course_options[course_label]
    schedules = get_schedules_by_course(id_matkul)
    schedule_options = {}
    for s in schedules:
        try:
            tanggal = datetime.strptime(s["tanggal_pertemuan"], "%Y-%m-%d").strftime("%d %b %Y")
        except ValueError:
            tanggal = s["tanggal_pertemuan"]
        label = f"Pertemuan {s['pertemuan_ke']} - {tanggal}, {s['jam_mulai']}"
        schedule_options[label] = s

    schedule_label = col2.selectbox("Pertemuan", ["Pilih jadwal"] + list(schedule_options.keys()))

    if schedule_label == "Pilih jadwal":
        st.info("Pilih pertemuan untuk menampilkan daftar mahasiswa.")
        return

    selected_schedule = schedule_options[schedule_label]
    id_jadwal = selected_schedule["id_jadwal"]

    m1, m2, m3 = st.columns(3)
    with m1:
        kpi_card("📚", "Mata Kuliah", course_label.split(" - ")[1], "#EFF6FF", "#2563EB")
    with m2:
        kpi_card("🔢", "Pertemuan", selected_schedule["pertemuan_ke"], "#ECFDF5", "#059669")
    with m3:
        kpi_card("🏫", "Ruangan", selected_schedule["ruangan"], "#FEF3C7", "#B45309")
    st.write("")

    students = get_students_by_course(id_matkul)
    if not students:
        st.warning("Belum ada mahasiswa di mata kuliah ini.")
        return

    attendance_rows = get_attendance_by_schedule(id_jadwal)

    st.markdown(f"**Daftar Kehadiran** — {len(students)} mahasiswa terdaftar.")

    mark_all = st.button("✅ Tandai Hadir Semua")

    with st.form("form_absensi"):
        status_values = {}
        note_values = {}
        for student in students:
            sid = student["id_mahasiswa"]
            current = attendance_rows.get(sid, {}).get("status_kehadiran", "Hadir")
            current_note = attendance_rows.get(sid, {}).get("keterangan", "") or ""
            if mark_all:
                current = "Hadir"

            with st.container(border=True):
                c1, c2, c3 = st.columns([2, 2, 2])
                c1.markdown(f"**{student['nama_mahasiswa']}**  \n"
                            f"<span style='color:gray;font-size:0.85em'>{student['npm']} - {student['program_studi']}</span>",
                            unsafe_allow_html=True)
                status_values[sid] = c2.radio(
                    "Status",
                    VALID_STATUSES,
                    index=VALID_STATUSES.index(current) if current in VALID_STATUSES else 0,
                    key=f"status_{id_jadwal}_{sid}",
                    horizontal=True,
                    label_visibility="collapsed",
                )
                note_values[sid] = c3.text_input(
                    "Keterangan", value=current_note, key=f"note_{id_jadwal}_{sid}",
                    placeholder="Catatan opsional", label_visibility="collapsed",
                )

        submitted = st.form_submit_button("💾 Simpan Absensi", type="primary")
        if submitted:
            for sid, status in status_values.items():
                save_attendance(sid, id_jadwal, status, note_values[sid].strip())
            st.success("Absensi berhasil disimpan.")
            st.rerun()


def page_rekap():
    render_header("📄 Rekap Kehadiran", "Rekapitulasi kehadiran mahasiswa per mata kuliah, dihitung langsung dari database.")
    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    course_label = st.selectbox("Filter Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))

    if course_label == "Pilih mata kuliah":
        st.info("Pilih mata kuliah untuk melihat rekapitulasi.")
        return

    id_matkul = course_options[course_label]
    summary = get_course_summary(id_matkul)
    c1, c2, c3 = st.columns(3)
    with c1:
        kpi_card("🎓", "Mahasiswa", summary["total_mahasiswa"] or 0, "#EFF6FF", "#2563EB")
    with c2:
        kpi_card("🗓️", "Total Jadwal", summary["total_jadwal"] or 0, "#FEF3C7", "#B45309")
    with c3:
        kpi_card("✅", "Data Terisi", summary["total_absensi"] or 0, "#ECFDF5", "#059669")
    st.write("")

    recap = get_attendance_recap(id_matkul)
    if not recap:
        st.info("Belum ada mahasiswa atau data absensi untuk mata kuliah ini.")
        return

    st.caption("Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.")

    with st.container(border=True):
        header_cols = st.columns([3, 1, 1, 1, 1, 1, 2])
        for col, label in zip(header_cols, ["Mahasiswa", "Hadir", "Terlambat", "Sakit", "Izin", "Alpa", "Persentase"]):
            col.markdown(f"**{label}**")
        st.markdown("<hr style='margin:4px 0 10px 0;border-color:#E7EAF0'>", unsafe_allow_html=True)

        for row in recap:
            total = max(row["total_jadwal"] or 0, 1)
            present = (row["hadir"] or 0) + (row["terlambat"] or 0)
            percentage = min(100, round(present / total * 100))

            cols = st.columns([3, 1, 1, 1, 1, 1, 2])
            cols[0].markdown(
                f"**{row['nama_mahasiswa']}**  \n<span style='color:gray;font-size:0.85em'>{row['npm']}</span>",
                unsafe_allow_html=True,
            )
            cols[1].markdown(status_badge("Hadir").replace("Hadir", str(row["hadir"] or 0)), unsafe_allow_html=True)
            cols[2].markdown(status_badge("Terlambat").replace("Terlambat", str(row["terlambat"] or 0)), unsafe_allow_html=True)
            cols[3].markdown(status_badge("Sakit").replace("Sakit", str(row["sakit"] or 0)), unsafe_allow_html=True)
            cols[4].markdown(status_badge("Izin").replace("Izin", str(row["izin"] or 0)), unsafe_allow_html=True)
            cols[5].markdown(status_badge("Alpa").replace("Alpa", str(row["alpa"] or 0)), unsafe_allow_html=True)
            with cols[6]:
                st.progress(percentage / 100, text=f"{percentage}%")


def page_mahasiswa():
    render_header("👥 Data Mahasiswa", "Kelola data mahasiswa per kelas — tambah, ubah, dan hapus langsung ke database.")
    id_dosen = current_dosen_id()
    courses = get_courses_by_dosen(id_dosen)

    col_form, col_list = st.columns([0.9, 1.4])

    editing = st.session_state.get("editing_student")

    with col_form:
        st.markdown(f"#### {'Edit Mahasiswa' if editing else 'Tambah Mahasiswa'}")
        st.caption("Mahasiswa baru otomatis masuk ke mata kuliah yang dipilih.")

        with st.form("form_mahasiswa", clear_on_submit=not editing):
            c1, c2 = st.columns(2)
            npm = c1.text_input("NPM", value=editing["npm"] if editing else "", placeholder="2021001")
            angkatan = c2.number_input(
                "Angkatan", min_value=2000, max_value=2099,
                value=int(editing["angkatan"]) if editing else date.today().year,
            )
            nama = st.text_input("Nama Mahasiswa", value=editing["nama_mahasiswa"] if editing else "", placeholder="Nama lengkap")
            prodi = st.text_input("Program Studi", value=editing["program_studi"] if editing else "", placeholder="Teknik Informatika")
            email = st.text_input("Email", value=(editing["email"] or "") if editing else "", placeholder="nama@student.univ.ac.id")

            id_matkul_baru = None
            tahun_ajaran = None
            if not editing:
                matkul_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
                if matkul_options:
                    matkul_label = st.selectbox("Mata Kuliah", ["Pilih mata kuliah"] + list(matkul_options.keys()))
                    id_matkul_baru = matkul_options.get(matkul_label)
                default_tahun = f"{date.today().year}/{date.today().year + 1}"
                tahun_ajaran = st.text_input("Tahun Ajaran", value=default_tahun)

            submitted = st.form_submit_button("💾 Simpan", type="primary")
            if submitted:
                data = {
                    "npm": npm.strip(),
                    "nama_mahasiswa": nama.strip(),
                    "program_studi": prodi.strip(),
                    "angkatan": int(angkatan),
                    "email": email.strip(),
                    "tahun_ajaran": tahun_ajaran,
                }
                try:
                    if not data["npm"] or not data["nama_mahasiswa"] or not data["program_studi"]:
                        raise ValueError("Lengkapi NPM, nama, dan program studi.")
                    if editing:
                        student_update(editing["id_mahasiswa"], data)
                        st.session_state.editing_student = None
                        st.success("Data mahasiswa berhasil diperbarui.")
                    else:
                        if not id_matkul_baru:
                            raise ValueError("Pilih mata kuliah untuk menambahkan mahasiswa.")
                        student_create(data, id_matkul_baru)
                        st.success("Mahasiswa berhasil ditambahkan.")
                    st.rerun()
                except Exception:
                    st.error("Gagal menyimpan data. Pastikan NPM/email belum digunakan dan isian sudah lengkap.")

        if editing:
            if st.button("✖ Batal Edit"):
                st.session_state.editing_student = None
                st.rerun()

    with col_list:
        keyword = st.text_input("🔍 Cari NPM, nama, atau program studi", value=st.session_state.get("mhs_keyword", ""))
        st.session_state.mhs_keyword = keyword

        students = student_get_by_lecturer(id_dosen, keyword.strip())
        st.markdown(f"#### Daftar Mahasiswa")
        st.caption(f"{len(students)} mahasiswa ditemukan.")

        for student in students:
            with st.container(border=True):
                c1, c2, c3, c4 = st.columns([3, 2, 1, 1.4])
                c1.markdown(
                    f"**{student['nama_mahasiswa']}**  \n"
                    f"<span style='color:gray;font-size:0.85em'>{student['npm']}"
                    f"{' - ' + student['email'] if student['email'] else ''}</span>",
                    unsafe_allow_html=True,
                )
                c2.write(student["program_studi"])
                c3.write(student["angkatan"])
                with c4:
                    b1, b2 = st.columns(2)
                    if b1.button("✏️", key=f"edit_{student['id_mahasiswa']}", help="Edit"):
                        st.session_state.editing_student = student
                        st.rerun()
                    if b2.button("🗑️", key=f"del_{student['id_mahasiswa']}", help="Hapus"):
                        st.session_state[f"confirm_del_{student['id_mahasiswa']}"] = True
                        st.rerun()

                if st.session_state.get(f"confirm_del_{student['id_mahasiswa']}"):
                    st.warning(f"Hapus **{student['nama_mahasiswa']}** dari kelas Anda?")
                    yes, no = st.columns(2)
                    if yes.button("Ya, hapus", key=f"yes_{student['id_mahasiswa']}"):
                        if student_find(student["id_mahasiswa"], id_dosen):
                            student_delete_for_lecturer(student["id_mahasiswa"], id_dosen)
                            st.success("Mahasiswa berhasil dihapus dari kelas Anda.")
                        st.session_state[f"confirm_del_{student['id_mahasiswa']}"] = False
                        st.rerun()
                    if no.button("Batal", key=f"no_{student['id_mahasiswa']}"):
                        st.session_state[f"confirm_del_{student['id_mahasiswa']}"] = False
                        st.rerun()

        if not students:
            st.info("Belum ada mahasiswa yang cocok.")


# --------------------------------------------------------------------------
# Layout / navigation
# --------------------------------------------------------------------------
def main():
    inject_css()

    if "page" not in st.session_state:
        st.session_state.page = "dashboard"

    if not is_logged_in():
        page_login()
        return

    user = st.session_state.user
    with st.sidebar:
        st.markdown(
            """<div class="sidebar-brand"><div class="logo">🎓</div>
            <div class="title">SIAKAD V2</div></div>""",
            unsafe_allow_html=True,
        )
        st.markdown(
            f"""<div class="sidebar-user">
                <div class="avatar">{initials(user['nama_lengkap'])}</div>
                <div class="meta">
                    <div class="name">{user['nama_lengkap']}</div>
                    <div class="role">Dosen Pengampu</div>
                </div>
            </div>""",
            unsafe_allow_html=True,
        )
        st.write("")

        for key, icon, label in NAV_ITEMS:
            if st.button(f"{icon}  {label}", use_container_width=True,
                         type="primary" if st.session_state.page == key else "secondary",
                         key=f"nav_{key}"):
                st.session_state.page = key
                st.rerun()

        st.divider()
        if st.button("🚪  Logout", use_container_width=True, key="nav_logout"):
            logout()
            st.rerun()

    pages = {
        "dashboard": page_dashboard,
        "absensi": page_absensi,
        "rekap": page_rekap,
        "mahasiswa": page_mahasiswa,
    }
    pages.get(st.session_state.page, page_dashboard)()


if __name__ == "__main__":
    main()
