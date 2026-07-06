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
    page_title="SIAKAD V2 - Absensi Dosen",
    page_icon="🎓",
    layout="wide",
    initial_sidebar_state="expanded",
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

PAGE_META = {
    "dashboard": ("Dashboard", "🏠", "Dashboard"),
    "absensi": ("Input Absensi", "📋", "Input Absensi"),
    "rekap": ("Rekap Absensi", "📄", "Rekap Absensi"),
    "mahasiswa": ("Data Mahasiswa", "👥", "Mahasiswa"),
}


# --------------------------------------------------------------------------
# Theme / styling — replika tampilan "SIAKAD V2"
# --------------------------------------------------------------------------
def inject_css(dark: bool):
    if dark:
        bg, sidebar_bg, card_bg = "#0b1220", "#0f172a", "#111c30"
        text, muted, border = "#e2e8f0", "#94a3b8", "#1f2c42"
        input_bg = "#0f172a"
    else:
        bg, sidebar_bg, card_bg = "#f3f5fb", "#ffffff", "#ffffff"
        text, muted, border = "#0f172a", "#64748b", "#e6e9f2"
        input_bg = "#ffffff"

    st.markdown(
        f"""
        <style>
        :root {{
            --primary:#2563eb;
            --primary-dark:#1d4ed8;
            --bg:{bg};
            --sidebar-bg:{sidebar_bg};
            --card-bg:{card_bg};
            --text:{text};
            --muted:{muted};
            --border:{border};
            --input-bg:{input_bg};
        }}

        html, body, .stApp {{ background:var(--bg) !important; color:var(--text); }}
        [data-testid="stAppViewContainer"] {{ background:var(--bg); }}
        [data-testid="stHeader"] {{ background:transparent; }}
        .block-container {{ padding-top:1.6rem; padding-bottom:2.5rem; max-width:1200px; }}
        * {{ font-family:'Segoe UI', 'Inter', -apple-system, sans-serif; }}

        h1,h2,h3,h4,h5,h6, p, span, label, div {{ color:var(--text); }}
        .stCaption, [data-testid="stCaptionContainer"] p {{ color:var(--muted) !important; }}

        /* ---------- Sidebar ---------- */
        [data-testid="stSidebar"] {{
            background:var(--sidebar-bg);
            border-right:1px solid var(--border);
        }}
        [data-testid="stSidebar"] .block-container {{ padding-top:1.5rem; }}

        .siakad-logo {{
            display:flex; align-items:center; gap:12px; margin-bottom:22px;
        }}
        .siakad-logo .box {{
            width:44px; height:44px; border-radius:12px;
            background:linear-gradient(135deg,#2563eb,#1e3a8a);
            display:flex; align-items:center; justify-content:center;
            font-size:22px; flex-shrink:0;
        }}
        .siakad-logo .title {{ font-weight:800; font-size:1.05rem; line-height:1.1; color:var(--text); }}
        .siakad-logo .subtitle {{ font-size:0.72rem; color:var(--muted); letter-spacing:0.06em; font-weight:600; }}

        .siakad-user {{
            display:flex; align-items:center; gap:10px;
            padding:12px; border-radius:14px; background:var(--bg);
            border:1px solid var(--border); margin-top:10px;
        }}
        .siakad-user .avatar {{
            width:38px; height:38px; border-radius:50%;
            background:linear-gradient(135deg,#2563eb,#1e3a8a);
            color:white; display:flex; align-items:center; justify-content:center;
            font-weight:700; flex-shrink:0;
        }}
        .siakad-user .name {{ font-weight:700; font-size:0.85rem; line-height:1.2; }}
        .siakad-user .nidn {{ font-size:0.75rem; color:var(--muted); }}

        /* Sidebar nav buttons */
        [data-testid="stSidebar"] .stButton>button {{
            justify-content:flex-start; text-align:left;
            background:transparent; border:none; box-shadow:none;
            color:var(--text); font-weight:600; font-size:0.92rem;
            padding:10px 14px; border-radius:12px; margin-bottom:2px;
        }}
        [data-testid="stSidebar"] .stButton>button:hover {{ background:var(--bg); }}
        [data-testid="stSidebar"] .stButton>button[kind="primary"] {{
            background:linear-gradient(135deg,#2563eb,#1e3a8a) !important;
            color:white !important; box-shadow:0 4px 10px rgba(37,99,235,0.25);
        }}

        /* ---------- Generic buttons (main area) ---------- */
        .stButton>button {{
            border-radius:12px; font-weight:600; border:1px solid var(--border);
            padding:0.55rem 1rem;
        }}
        .stButton>button[kind="primary"] {{
            background:var(--primary); border:none; color:white;
        }}
        .stButton>button[kind="primary"]:hover {{ background:var(--primary-dark); }}
        .stFormSubmitButton>button[kind="primary"] {{
            background:var(--primary); border:none; color:white; border-radius:12px;
        }}

        /* ---------- Top bar ---------- */
        .siakad-topbar {{
            display:flex; align-items:flex-start; justify-content:space-between;
            margin-bottom:22px;
        }}
        .siakad-topbar h1 {{ font-size:1.7rem; font-weight:800; margin:0; }}
        .siakad-topbar .date {{ color:var(--muted); font-size:0.85rem; margin-top:2px; }}

        /* ---------- Cards ---------- */
        .siakad-card {{
            background:var(--card-bg); border:1px solid var(--border);
            border-radius:18px; padding:20px 22px;
        }}
        [data-testid="stVerticalBlockBorderWrapper"] {{
            border-radius:16px !important; border-color:var(--border) !important;
            background:var(--card-bg) !important;
        }}

        /* ---------- Welcome banner ---------- */
        .siakad-banner {{
            background:linear-gradient(120deg,#1d4ed8 0%, #0f766e 100%);
            border-radius:22px; padding:34px 38px; color:white; margin-bottom:22px;
        }}
        .siakad-banner .tag {{
            font-size:0.72rem; font-weight:700; letter-spacing:0.08em;
            color:#dbeafe; margin-bottom:8px;
        }}
        .siakad-banner h1 {{ color:white; font-size:2rem; font-weight:800; margin:2px 0 10px; }}
        .siakad-banner p {{ color:#dbeafe; font-size:0.98rem; max-width:640px; margin:0; }}

        /* ---------- Stat cards ---------- */
        .stat-card {{
            background:var(--card-bg); border:1px solid var(--border);
            border-radius:18px; padding:20px; height:100%;
        }}
        .stat-card .icon {{
            width:44px; height:44px; border-radius:12px; display:flex;
            align-items:center; justify-content:center; font-size:20px; margin-bottom:14px;
        }}
        .stat-card .label {{ color:var(--muted); font-size:0.85rem; font-weight:600; margin-bottom:4px; }}
        .stat-card .value {{ font-size:1.7rem; font-weight:800; color:var(--text); }}

        /* ---------- Action / schedule cards ---------- */
        .action-card {{
            background:var(--card-bg); border:1px solid var(--border); border-radius:16px;
            padding:16px; text-align:left; margin-bottom:10px;
        }}
        .action-card .icon {{
            width:36px; height:36px; border-radius:10px; display:flex; align-items:center;
            justify-content:center; font-size:17px; margin-bottom:10px;
        }}
        .action-card .title {{ font-weight:700; font-size:0.95rem; }}
        .action-card .desc {{ color:var(--muted); font-size:0.78rem; margin-top:2px; }}

        /* ---------- Badge pill ---------- */
        .siakad-badge {{
            display:inline-block; padding:6px 16px; border-radius:999px;
            font-size:0.8rem; font-weight:700; background:rgba(37,99,235,0.1); color:var(--primary);
        }}

        /* ---------- Status radio pills (Input Absensi) ---------- */
        div[data-testid="stRadio"] > div {{ gap:8px; flex-wrap:wrap; }}
        div[data-testid="stRadio"] label {{
            border:1.5px solid var(--border); border-radius:10px; padding:6px 14px;
            background:var(--card-bg); margin:0 !important; transition:all 0.15s;
        }}
        div[data-testid="stRadio"] label div:first-child {{ display:none; }}
        div[data-testid="stRadio"] label:has(input:checked) {{
            border-color:var(--primary); background:rgba(37,99,235,0.08);
        }}
        div[data-testid="stRadio"] label:has(input:checked) p {{ color:var(--primary); font-weight:700; }}

        /* ---------- Inputs ---------- */
        .stTextInput input, .stNumberInput input, .stSelectbox [data-baseweb="select"] {{
            background:var(--input-bg) !important; border-radius:10px !important;
            border:1px solid var(--border) !important;
        }}

        /* ---------- Progress bar ---------- */
        div[data-testid="stProgress"] > div > div {{ background:linear-gradient(90deg,#22c55e,#16a34a) !important; border-radius:999px; }}

        /* ---------- Metric fallback (unused mostly) ---------- */
        [data-testid="stMetric"] {{
            background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:14px 16px;
        }}
        </style>
        """,
        unsafe_allow_html=True,
    )


def render_topbar(page_key: str):
    title, icon, _ = PAGE_META.get(page_key, ("Dashboard", "🏠", ""))
    today_label = datetime.now().strftime("%d %B %Y")

    left, right = st.columns([3, 1.4])
    with left:
        st.markdown(
            f"""
            <div class="siakad-topbar">
              <div>
                <h1>{icon} {title}</h1>
                <div class="date">{today_label}</div>
              </div>
            </div>
            """,
            unsafe_allow_html=True,
        )
    with right:
        b1, b2 = st.columns([1, 1.4])
        with b1:
            if st.button("🌙" if not st.session_state.get("dark_mode") else "☀️", key="toggle_dark", help="Ubah tampilan"):
                st.session_state.dark_mode = not st.session_state.get("dark_mode", False)
                st.rerun()
        with b2:
            if st.button("🚪 Keluar", key="btn_logout_top", use_container_width=True):
                logout()
                st.rerun()


def stat_card(icon, label, value, color_bg, color_fg):
    st.markdown(
        f"""
        <div class="stat-card">
          <div class="icon" style="background:{color_bg};color:{color_fg};">{icon}</div>
          <div class="label">{label}</div>
          <div class="value">{value}</div>
        </div>
        """,
        unsafe_allow_html=True,
    )


def action_card(icon, title, desc, color_bg, color_fg):
    st.markdown(
        f"""
        <div class="action-card">
          <div class="icon" style="background:{color_bg};color:{color_fg};">{icon}</div>
          <div class="title">{title}</div>
          <div class="desc">{desc}</div>
        </div>
        """,
        unsafe_allow_html=True,
    )


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
    inject_css(st.session_state.get("dark_mode", False))
    st.markdown("<div style='height:24px'></div>", unsafe_allow_html=True)
    left, right = st.columns([1.15, 0.85], gap="large")
    with left:
        st.markdown(
            """
            <div style="background:linear-gradient(135deg,#2563eb,#0f766e);border-radius:24px;
                        padding:52px;color:white;height:100%;min-height:520px;">
              <div style="display:inline-block;background:rgba(255,255,255,0.15);
                          padding:6px 16px;border-radius:999px;font-size:0.85rem;font-weight:600;">
                🛡️ Sistem akademik dosen
              </div>
              <h1 style="font-size:2.6rem;font-weight:900;margin-top:28px;line-height:1.2;color:white;">
                Kelola absensi kelas<br>dengan cepat dan rapi.
              </h1>
              <p style="margin-top:18px;color:#dbeafe;font-size:1.05rem;max-width:420px;">
                Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan
                sudah terhubung dalam satu alur kerja.
              </p>
            </div>
            """,
            unsafe_allow_html=True,
        )
    with right:
        st.markdown(
            """
            <div class="siakad-card" style="margin-bottom:18px;">
              <div class="siakad-logo" style="margin-bottom:6px;">
                <div class="box">🎓</div>
                <div>
                  <div class="title">Masuk ke SIAKAD V2</div>
                  <div class="subtitle" style="text-transform:none;letter-spacing:0;">Gunakan akun dosen untuk mengakses sistem.</div>
                </div>
              </div>
            </div>
            """,
            unsafe_allow_html=True,
        )
        with st.form("login_form"):
            email = st.text_input("Email", placeholder="firansyah@univ.ac.id")
            password = st.text_input("Password", type="password", placeholder="Masukkan password")
            submitted = st.form_submit_button("➡️ Masuk", use_container_width=True, type="primary")
            if submitted:
                if login(email.strip(), password):
                    st.session_state.page = "dashboard"
                    st.rerun()
                else:
                    st.error("Email atau password salah.")

        st.markdown(
            """
            <div class="siakad-card" style="margin-top:14px;">
              <b>Akun demo</b><br>
              <span style="color:var(--muted)">Email: firansyah@univ.ac.id</span><br>
              <span style="color:var(--muted)">Password: password</span>
            </div>
            """,
            unsafe_allow_html=True,
        )


def page_dashboard():
    render_topbar("dashboard")

    nama_depan = st.session_state.user["nama_lengkap"].split(",")[0]
    st.markdown(
        f"""
        <div class="siakad-banner">
          <div class="tag">SELAMAT DATANG KEMBALI</div>
          <h1>{nama_depan}</h1>
          <p>Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.</p>
        </div>
        """,
        unsafe_allow_html=True,
    )

    stats = get_dashboard_stats(current_dosen_id())
    c1, c2, c3, c4 = st.columns(4)
    with c1:
        stat_card("📘", "Mata Kuliah", stats["total_matkul"], "#eff6ff", "#2563eb")
    with c2:
        stat_card("🎓", "Mahasiswa", stats["total_mahasiswa"], "#ecfdf5", "#059669")
    with c3:
        stat_card("🗓️", "Jadwal Kelas", stats["total_jadwal"], "#fff7ed", "#d97706")
    with c4:
        stat_card("📈", "Rata Kehadiran", f"{stats['rata_kehadiran']}%", "#fdf2f8", "#db2777")

    st.markdown("<div style='height:22px'></div>", unsafe_allow_html=True)
    col1, col2 = st.columns([1, 1.1], gap="large")
    with col1:
        st.markdown("#### Aksi Cepat")
        st.caption("Masuk ke pekerjaan utama tanpa banyak klik.")
        action_card("📋", "Input Absensi", "Catat kehadiran", "#eff6ff", "#2563eb")
        if st.button("Buka Input Absensi", key="go_absensi", use_container_width=True):
            st.session_state.page = "absensi"
            st.rerun()
        action_card("📄", "Lihat Rekap", "Monitor persentase", "#ecfdf5", "#059669")
        if st.button("Buka Rekap", key="go_rekap", use_container_width=True):
            st.session_state.page = "rekap"
            st.rerun()
        action_card("👥", "Mahasiswa", "Kelola peserta kelas", "#fff7ed", "#d97706")
        if st.button("Buka Mahasiswa", key="go_mahasiswa", use_container_width=True):
            st.session_state.page = "mahasiswa"
            st.rerun()

    with col2:
        st.markdown("#### Jadwal Terdekat")
        st.caption("5 pertemuan berikutnya di sistem.")
        schedules = get_upcoming_schedules(current_dosen_id())
        if not schedules:
            st.info("Belum ada jadwal perkuliahan.")
        for s in schedules:
            try:
                tanggal = datetime.strptime(s["tanggal_pertemuan"], "%Y-%m-%d").strftime("%d %b %Y")
            except ValueError:
                tanggal = s["tanggal_pertemuan"]
            st.markdown(
                f"""
                <div class="siakad-card" style="margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                  <div>
                    <div style="font-weight:700;">{s['nama_matkul']}</div>
                    <div style="color:var(--muted);font-size:0.85rem;">{s['kode_matkul']} - Pertemuan {s['pertemuan_ke']}</div>
                  </div>
                  <div style="text-align:right;">
                    <div style="font-weight:700;">{tanggal}</div>
                    <div style="color:var(--muted);font-size:0.85rem;">{s['jam_mulai']} - {s['jam_selesai']}</div>
                  </div>
                </div>
                """,
                unsafe_allow_html=True,
            )


def page_absensi():
    render_topbar("absensi")
    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    st.markdown('<div class="siakad-card">', unsafe_allow_html=True)
    course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    col1, col2, col3 = st.columns([2, 2, 0.7])
    course_label = col1.selectbox("Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))

    if course_label == "Pilih mata kuliah":
        st.markdown('</div>', unsafe_allow_html=True)
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
    with col3:
        st.markdown("<div style='height:28px'></div>", unsafe_allow_html=True)
        st.button("↺ Reset", use_container_width=True, key="reset_absensi")
    st.markdown('</div>', unsafe_allow_html=True)

    if schedule_label == "Pilih jadwal":
        st.info("Pilih pertemuan untuk menampilkan daftar mahasiswa.")
        return

    selected_schedule = schedule_options[schedule_label]
    id_jadwal = selected_schedule["id_jadwal"]

    st.markdown("<div style='height:16px'></div>", unsafe_allow_html=True)
    m1, m2, m3 = st.columns(3)
    with m1:
        stat_card("📗", "Mata kuliah", course_label.split(" - ")[1], "#eff6ff", "#2563eb")
    with m2:
        stat_card("🔢", "Pertemuan", selected_schedule["pertemuan_ke"], "#ecfdf5", "#059669")
    with m3:
        stat_card("🏫", "Ruangan", selected_schedule["ruangan"], "#fff7ed", "#d97706")
    st.markdown("<div style='height:16px'></div>", unsafe_allow_html=True)

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
    render_topbar("rekap")
    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    st.markdown('<div class="siakad-card">', unsafe_allow_html=True)
    col_sel, col_btn = st.columns([3, 1])
    course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    course_label = col_sel.selectbox("Filter Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))
    with col_btn:
        st.markdown("<div style='height:28px'></div>", unsafe_allow_html=True)
        st.button("🖨️ Cetak", use_container_width=True, key="btn_cetak")
    st.markdown('</div>', unsafe_allow_html=True)

    if course_label == "Pilih mata kuliah":
        st.info("Pilih mata kuliah untuk melihat rekapitulasi.")
        return

    id_matkul = course_options[course_label]
    summary = get_course_summary(id_matkul)
    st.markdown("<div style='height:16px'></div>", unsafe_allow_html=True)
    c1, c2, c3 = st.columns(3)
    with c1:
        stat_card("🎓", "Mahasiswa", summary["total_mahasiswa"] or 0, "#eff6ff", "#2563eb")
    with c2:
        stat_card("🗓️", "Total Jadwal", summary["total_jadwal"] or 0, "#ecfdf5", "#059669")
    with c3:
        stat_card("✅", "Data Terisi", summary["total_absensi"] or 0, "#fff7ed", "#d97706")
    st.markdown("<div style='height:16px'></div>", unsafe_allow_html=True)

    recap = get_attendance_recap(id_matkul)
    if not recap:
        st.info("Belum ada mahasiswa atau data absensi untuk mata kuliah ini.")
        return

    st.markdown(f"#### Rekap {course_label.split(' - ')[1]}")
    st.caption("Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.")

    header_cols = st.columns([3, 1, 1, 1, 1, 1, 2])
    for col, label in zip(header_cols, ["Mahasiswa", "Hadir", "Terlambat", "Sakit", "Izin", "Alpa", "Persentase"]):
        col.markdown(f"**{label}**")

    for row in recap:
        total = max(row["total_jadwal"] or 0, 1)
        present = (row["hadir"] or 0) + (row["terlambat"] or 0)
        percentage = min(100, round(present / total * 100))
        good = percentage >= 75

        cols = st.columns([3, 1, 1, 1, 1, 1, 2])
        cols[0].markdown(f"**{row['nama_mahasiswa']}**  \n<span style='color:gray;font-size:0.85em'>{row['npm']}</span>", unsafe_allow_html=True)
        cols[1].markdown(f":green[**{row['hadir'] or 0}**]")
        cols[2].markdown(f":blue[**{row['terlambat'] or 0}**]")
        cols[3].markdown(f":orange[**{row['sakit'] or 0}**]")
        cols[4].markdown(f":violet[**{row['izin'] or 0}**]")
        cols[5].markdown(f":red[**{row['alpa'] or 0}**]")
        with cols[6]:
            st.progress(percentage / 100, text=f"{percentage}%")


def page_mahasiswa():
    render_topbar("mahasiswa")
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
                initial = student["nama_mahasiswa"][:1].upper()
                c1.markdown(
                    f"<div style='display:flex;align-items:center;gap:10px;'>"
                    f"<div style='width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1e3a8a);"
                    f"color:white;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;'>{initial}</div>"
                    f"<div><b>{student['nama_mahasiswa']}</b><br>"
                    f"<span style='color:gray;font-size:0.85em'>{student['npm']}"
                    f"{' - ' + student['email'] if student['email'] else ''}</span></div></div>",
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
    if "page" not in st.session_state:
        st.session_state.page = "dashboard"
    st.session_state.setdefault("dark_mode", False)

    if not is_logged_in():
        page_login()
        return

    inject_css(st.session_state.dark_mode)

    with st.sidebar:
        st.markdown(
            """
            <div class="siakad-logo">
              <div class="box">🎓</div>
              <div>
                <div class="title">SIAKAD V2</div>
                <div class="subtitle">ABSENSI DOSEN</div>
              </div>
            </div>
            """,
            unsafe_allow_html=True,
        )

        nav = {
            "dashboard": "🏠  Dashboard",
            "absensi": "📋  Input Absensi",
            "rekap": "📄  Rekap Absensi",
            "mahasiswa": "👥  Mahasiswa",
        }
        for key, label in nav.items():
            if st.button(label, use_container_width=True, type="primary" if st.session_state.page == key else "secondary", key=f"nav_{key}"):
                st.session_state.page = key
                st.rerun()

        st.markdown("<div style='flex:1;'></div>", unsafe_allow_html=True)
        st.markdown("<div style='height:24px'></div>", unsafe_allow_html=True)

        nama = st.session_state.user["nama_lengkap"]
        initial = nama[:1].upper()
        nidn = st.session_state.user["nidn"]
        st.markdown(
            f"""
            <div class="siakad-user">
              <div class="avatar">{initial}</div>
              <div>
                <div class="name">{nama[:14] + ('...' if len(nama) > 14 else '')}</div>
                <div class="nidn">{nidn}</div>
              </div>
            </div>
            """,
            unsafe_allow_html=True,
        )

    pages = {
        "dashboard": page_dashboard,
        "absensi": page_absensi,
        "rekap": page_rekap,
        "mahasiswa": page_mahasiswa,
    }
    pages.get(st.session_state.page, page_dashboard)()


if __name__ == "__main__":
    main()
