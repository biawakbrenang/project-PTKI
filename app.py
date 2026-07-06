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


# --------------------------------------------------------------------------
# Theme / visual style (mengikuti tampilan referensi SIAKAD V2)
# --------------------------------------------------------------------------
def build_css(dark: bool) -> str:
    if dark:
        bg, sidebar_bg, card_bg = "#0f172a", "#111827", "#1e293b"
        text, muted, border = "#e5e7eb", "#94a3b8", "#334155"
        hover = "#1f2937"
    else:
        bg, sidebar_bg, card_bg = "#f6f8fb", "#ffffff", "#ffffff"
        text, muted, border = "#111827", "#6b7280", "#e5e7eb"
        hover = "#f3f4f6"

    return f"""
    <style>
    html, body, [class*="css"] {{ font-family: 'Segoe UI', Inter, system-ui, sans-serif; }}
    [data-testid="stAppViewContainer"] {{ background: {bg}; }}
    [data-testid="stHeader"] {{ background: transparent; }}
    [data-testid="stMainBlockContainer"] {{ padding-top: 1.5rem; max-width: 1180px; }}
    h1, h2, h3, h4, p, span, label, div {{ color: {text}; }}

    /* Sidebar */
    section[data-testid="stSidebar"] {{
        background: {sidebar_bg}; border-right: 1px solid {border};
    }}
    section[data-testid="stSidebar"] .stButton button {{
        text-align: left; justify-content: flex-start; width: 100%;
        border-radius: 10px; padding: 0.55rem 0.9rem; font-size: 0.95rem;
        border: none !important; box-shadow: none !important; transition: background .15s;
    }}
    section[data-testid="stSidebar"] button[kind="primary"] {{
        background: #eff6ff !important; color: #1d4ed8 !important; font-weight: 700 !important;
    }}
    section[data-testid="stSidebar"] button[kind="secondary"] {{
        background: transparent !important; color: {muted} !important; font-weight: 500 !important;
    }}
    section[data-testid="stSidebar"] button:hover {{ background: {hover} !important; }}

    /* Cards (bordered containers) */
    div[data-testid="stVerticalBlockBorderWrapper"] {{
        border-radius: 16px !important; border: 1px solid {border} !important;
        background: {card_bg} !important; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }}

    /* Generic buttons */
    .stButton button {{ border-radius: 10px; font-weight: 600; }}
    div[data-testid="stMainBlockContainer"] .stButton button[kind="primary"] {{
        background: #2563eb; border-color: #2563eb;
    }}
    div[data-testid="stMainBlockContainer"] .stButton button[kind="primary"]:hover {{
        background: #1d4ed8; border-color: #1d4ed8;
    }}
    .stButton button p {{ white-space: pre-line; line-height: 1.3; }}

    /* Pill-style radio (status kehadiran) */
    div[role="radiogroup"] {{ gap: 0.4rem; }}
    div[role="radiogroup"] label {{
        border: 1px solid {border}; border-radius: 999px; padding: 0.25rem 0.85rem;
        background: {card_bg};
    }}
    div[role="radiogroup"] label:has(input:checked) {{
        background: #2563eb !important; border-color: #2563eb !important;
    }}
    div[role="radiogroup"] label:has(input:checked) p {{ color: white !important; font-weight: 700; }}
    div[role="radiogroup"] input {{ display: none; }}

    /* Progress bar -> green like screenshot */
    div[data-testid="stProgress"] > div > div > div {{ background-color: #16a34a !important; }}

    /* Top bar */
    .page-title {{ font-size: 1.7rem; font-weight: 800; color: {text}; margin-bottom: 0; }}
    .page-date {{ color: {muted}; font-size: 0.9rem; margin-top: -4px; }}

    /* Stat card */
    .stat-card {{
        background: {card_bg}; border: 1px solid {border}; border-radius: 16px;
        padding: 1.1rem 1.2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }}
    .stat-icon {{
        width: 42px; height: 42px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 0.6rem;
    }}
    .stat-label {{ color: {muted}; font-size: 0.88rem; }}
    .stat-value {{ font-size: 1.7rem; font-weight: 800; color: {text}; }}

    /* Avatar circle */
    .avatar-circle {{
        width: 38px; height: 38px; border-radius: 50%; background: #dbeafe; color: #1d4ed8;
        display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;
    }}

    /* Login card */
    .login-hero {{
        background: linear-gradient(135deg, #2563eb, #0d9488); border-radius: 24px;
        padding: 48px; color: white; height: 100%;
    }}
    .login-badge {{
        display: inline-block; background: rgba(255,255,255,0.15); padding: 6px 16px;
        border-radius: 999px; font-size: 0.85rem; font-weight: 600;
    }}
    </style>
    """


def inject_css():
    st.markdown(build_css(st.session_state.get("dark", False)), unsafe_allow_html=True)


def stat_card(icon, icon_bg, icon_color, label, value):
    st.markdown(
        f"""
        <div class="stat-card">
          <div class="stat-icon" style="background:{icon_bg};color:{icon_color};">{icon}</div>
          <div class="stat-label">{label}</div>
          <div class="stat-value">{value}</div>
        </div>
        """,
        unsafe_allow_html=True,
    )


def avatar_circle(name, size=38):
    initial = (name or "?").strip()[:1].upper()
    return (
        f'<div class="avatar-circle" style="width:{size}px;height:{size}px;'
        f'font-size:{size*0.4}px;">{initial}</div>'
    )


def topbar(title):
    c1, c2 = st.columns([3, 1])
    with c1:
        today_str = date.today().strftime("%d %B %Y")
        st.markdown(
            f'<div class="page-title">{title}</div><div class="page-date">{today_str}</div>',
            unsafe_allow_html=True,
        )
    with c2:
        b1, b2 = st.columns([1, 2.2])
        with b1:
            icon = "☀️" if st.session_state.get("dark") else "🌙"
            if st.button(icon, key="theme_toggle"):
                st.session_state.dark = not st.session_state.get("dark", False)
                st.rerun()
        with b2:
            if st.button("Keluar", key="logout_top", use_container_width=True):
                logout()
                st.rerun()
    st.write("")


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
    inject_css()
    left, right = st.columns([1.1, 0.9])
    with left:
        st.markdown(
            """
            <div class="login-hero">
              <div class="login-badge">🛡️ Sistem akademik dosen</div>
              <h1 style="font-size:2.4rem;font-weight:900;margin-top:24px;line-height:1.2;color:white;">
                Kelola absensi kelas dengan cepat dan rapi.
              </h1>
              <p style="margin-top:16px;color:#dbeafe;font-size:1.05rem;">
                Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan
                sudah terhubung dalam satu alur kerja.
              </p>
            </div>
            """,
            unsafe_allow_html=True,
        )
    with right:
        with st.container(border=True):
            st.markdown(
                '<div class="stat-icon" style="background:#2563eb;color:white;'
                'width:56px;height:56px;font-size:1.6rem;border-radius:16px;">🎓</div>',
                unsafe_allow_html=True,
            )
            st.markdown("### Masuk ke SIAKAD V2")
            st.caption("Gunakan akun dosen untuk mengakses sistem.")
            with st.form("login_form"):
                email = st.text_input("Email", placeholder="firansyah@univ.ac.id")
                password = st.text_input("Password", type="password", placeholder="Masukkan password")
                submitted = st.form_submit_button("🔐 Masuk", use_container_width=True, type="primary")
                if submitted:
                    if login(email.strip(), password):
                        st.session_state.page = "dashboard"
                        st.rerun()
                    else:
                        st.error("Email atau password salah.")

            st.info("**Akun demo**\n\nEmail: firansyah@univ.ac.id\n\nPassword: password")


def page_dashboard():
    topbar("Dashboard")

    name = st.session_state.user["nama_lengkap"].split(",")[0]
    st.markdown(
        f"""
        <div style="background:linear-gradient(135deg,#2563eb,#0d9488);border-radius:20px;
                    padding:32px 36px;color:white;margin-bottom:1.2rem;">
          <div style="font-size:0.78rem;font-weight:700;letter-spacing:1px;color:#dbeafe;">
            SELAMAT DATANG KEMBALI
          </div>
          <div style="font-size:1.9rem;font-weight:800;margin-top:4px;">{name}</div>
          <div style="margin-top:10px;color:#e0f2fe;font-size:0.98rem;max-width:640px;">
            Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.
          </div>
        </div>
        """,
        unsafe_allow_html=True,
    )

    stats = get_dashboard_stats(current_dosen_id())
    c1, c2, c3, c4 = st.columns(4)
    with c1:
        stat_card("📘", "#dbeafe", "#2563eb", "Mata Kuliah", stats["total_matkul"])
    with c2:
        stat_card("🎓", "#dcfce7", "#16a34a", "Mahasiswa", stats["total_mahasiswa"])
    with c3:
        stat_card("📅", "#ffedd5", "#ea580c", "Jadwal Kelas", stats["total_jadwal"])
    with c4:
        stat_card("📈", "#fee2e2", "#dc2626", "Rata Kehadiran", f"{stats['rata_kehadiran']}%")

    st.write("")
    col1, col2 = st.columns([1, 1.1])
    with col1:
        with st.container(border=True):
            st.markdown("#### Aksi Cepat")
            st.caption("Masuk ke pekerjaan utama tanpa banyak klik.")
            a, b, cc = st.columns(3)
            with a:
                if st.button("📋\nInput Absensi", use_container_width=True, key="quick_absensi"):
                    st.session_state.page = "absensi"
                    st.rerun()
                st.caption("Catat kehadiran")
            with b:
                if st.button("📄\nLihat Rekap", use_container_width=True, key="quick_rekap"):
                    st.session_state.page = "rekap"
                    st.rerun()
                st.caption("Monitor persentase")
            with cc:
                if st.button("👥\nMahasiswa", use_container_width=True, key="quick_mhs"):
                    st.session_state.page = "mahasiswa"
                    st.rerun()
                st.caption("Kelola peserta")

    with col2:
        with st.container(border=True):
            st.markdown("#### Jadwal Terdekat")
            st.caption("5 pertemuan berikutnya di sistem.")
            schedules = get_upcoming_schedules(current_dosen_id())
            if not schedules:
                st.info("Belum ada jadwal perkuliahan.")
            for s in schedules:
                with st.container(border=True):
                    sc1, sc2 = st.columns([2, 1])
                    sc1.markdown(f"**{s['nama_matkul']}**  \n{s['kode_matkul']} - Pertemuan {s['pertemuan_ke']}")
                    try:
                        tanggal = datetime.strptime(s["tanggal_pertemuan"], "%Y-%m-%d").strftime("%d %b %Y")
                    except ValueError:
                        tanggal = s["tanggal_pertemuan"]
                    sc2.markdown(
                        f"<div style='text-align:right'><b>{tanggal}</b><br>"
                        f"{s['jam_mulai']} - {s['jam_selesai']}</div>",
                        unsafe_allow_html=True,
                    )


def page_absensi():
    topbar("Input Absensi")

    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    with st.container(border=True):
        course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
        col1, col2, col3 = st.columns([2, 2, 1])
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
        with col3:
            st.write("")
            st.write("")
            if st.button("↺ Reset", use_container_width=True):
                st.rerun()

    if schedule_label == "Pilih jadwal":
        st.info("Pilih pertemuan untuk menampilkan daftar mahasiswa.")
        return

    selected_schedule = schedule_options[schedule_label]
    id_jadwal = selected_schedule["id_jadwal"]

    m1, m2, m3 = st.columns(3)
    with m1:
        stat_card("📘", "#eef2ff", "#4f46e5", "Mata kuliah", course_label.split(" - ")[1])
    with m2:
        stat_card("🔢", "#eef2ff", "#4f46e5", "Pertemuan", selected_schedule["pertemuan_ke"])
    with m3:
        stat_card("📍", "#eef2ff", "#4f46e5", "Ruangan", selected_schedule["ruangan"])

    students = get_students_by_course(id_matkul)
    if not students:
        st.warning("Belum ada mahasiswa di mata kuliah ini.")
        return

    attendance_rows = get_attendance_by_schedule(id_jadwal)

    st.write("")
    with st.container(border=True):
        h1, h2 = st.columns([3, 1])
        h1.markdown(f"#### Daftar Kehadiran\n{len(students)} mahasiswa terdaftar.")
        with h2:
            mark_all = st.button("✅ Tandai Hadir Semua", use_container_width=True)

        with st.form("form_absensi"):
            status_values = {}
            note_values = {}
            for student in students:
                sid = student["id_mahasiswa"]
                current = attendance_rows.get(sid, {}).get("status_kehadiran", "Hadir")
                current_note = attendance_rows.get(sid, {}).get("keterangan", "") or ""
                if mark_all:
                    current = "Hadir"

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
                st.divider()

            submitted = st.form_submit_button("💾 Simpan Absensi", type="primary")
            if submitted:
                for sid, status in status_values.items():
                    save_attendance(sid, id_jadwal, status, note_values[sid].strip())
                st.success("Absensi berhasil disimpan.")
                st.rerun()


def page_rekap():
    topbar("Rekap Absensi")

    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    with st.container(border=True):
        col1, col2 = st.columns([3, 1])
        course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
        course_label = col1.selectbox("Filter Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))
        with col2:
            st.write("")
            st.write("")
            if st.button("🖨️ Cetak", use_container_width=True):
                st.toast("Fitur cetak akan tersedia pada versi mendatang.")

    if course_label == "Pilih mata kuliah":
        st.info("Pilih mata kuliah untuk melihat rekapitulasi.")
        return

    id_matkul = course_options[course_label]
    summary = get_course_summary(id_matkul)
    c1, c2, c3 = st.columns(3)
    with c1:
        stat_card("🎓", "#dbeafe", "#2563eb", "Mahasiswa", summary["total_mahasiswa"] or 0)
    with c2:
        stat_card("📅", "#dcfce7", "#16a34a", "Total Jadwal", summary["total_jadwal"] or 0)
    with c3:
        stat_card("✅", "#ffedd5", "#ea580c", "Data Terisi", summary["total_absensi"] or 0)

    recap = get_attendance_recap(id_matkul)
    if not recap:
        st.info("Belum ada mahasiswa atau data absensi untuk mata kuliah ini.")
        return

    st.write("")
    with st.container(border=True):
        nama_matkul = course_label.split(" - ")[1]
        st.markdown(f"#### Rekap {nama_matkul}")
        st.caption("Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.")

        header_cols = st.columns([3, 1, 1, 1, 1, 1, 2])
        for col, label in zip(header_cols, ["Mahasiswa", "Hadir", "Terlambat", "Sakit", "Izin", "Alpa", "Persentase"]):
            col.markdown(f"<span style='color:gray;font-size:0.8em;font-weight:700;letter-spacing:0.5px;'>{label.upper()}</span>", unsafe_allow_html=True)

        for row in recap:
            total = max(row["total_jadwal"] or 0, 1)
            present = (row["hadir"] or 0) + (row["terlambat"] or 0)
            percentage = min(100, round(present / total * 100))

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
    topbar("Data Mahasiswa")
    id_dosen = current_dosen_id()
    courses = get_courses_by_dosen(id_dosen)

    col_form, col_list = st.columns([0.9, 1.4])

    editing = st.session_state.get("editing_student")

    with col_form, st.container(border=True):
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
        s1, s2 = st.columns([3, 1])
        keyword = s1.text_input("🔍 Cari NPM, nama, atau program studi", value=st.session_state.get("mhs_keyword", ""), label_visibility="collapsed", placeholder="Cari NPM, nama, atau program studi")
        st.session_state.mhs_keyword = keyword
        s2.button("Cari", use_container_width=True)

        students = student_get_by_lecturer(id_dosen, keyword.strip())

        with st.container(border=True):
            st.markdown(f"#### Daftar Mahasiswa")
            st.caption(f"{len(students)} mahasiswa ditemukan.")

            for student in students:
                c0, c1, c2, c3, c4 = st.columns([0.4, 2.6, 2, 1, 1.4])
                c0.markdown(avatar_circle(student["nama_mahasiswa"]), unsafe_allow_html=True)
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
    if "page" not in st.session_state:
        st.session_state.page = "dashboard"

    if not is_logged_in():
        page_login()
        return

    inject_css()

    with st.sidebar:
        st.markdown(
            """
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.6rem;">
              <div style="background:#2563eb;color:white;width:40px;height:40px;border-radius:10px;
                          display:flex;align-items:center;justify-content:center;font-size:1.3rem;">🎓</div>
              <div>
                <div style="font-weight:800;font-size:1.05rem;line-height:1.1;">SIAKAD V2</div>
                <div style="color:#9ca3af;font-size:0.72rem;letter-spacing:0.5px;">ABSENSI DOSEN</div>
              </div>
            </div>
            """,
            unsafe_allow_html=True,
        )
        st.divider()

        nav = {
            "dashboard": "🏠  Dashboard",
            "absensi": "📋  Input Absensi",
            "rekap": "📄  Rekap Absensi",
            "mahasiswa": "👥  Mahasiswa",
        }
        for key, label in nav.items():
            if st.button(label, use_container_width=True, type="primary" if st.session_state.page == key else "secondary"):
                st.session_state.page = key
                st.rerun()

        st.markdown("<div style='flex-grow:1'></div>", unsafe_allow_html=True)
        st.write("")
        st.write("")
        with st.container(border=True):
            uc1, uc2, uc3 = st.columns([0.8, 3, 0.8])
            nama = st.session_state.user["nama_lengkap"]
            uc1.markdown(avatar_circle(nama, size=34), unsafe_allow_html=True)
            uc2.markdown(
                f"<div style='font-weight:700;font-size:0.9rem;line-height:1.2;'>{nama}</div>"
                f"<div style='color:#9ca3af;font-size:0.78rem;'>{st.session_state.user['nidn']}</div>",
                unsafe_allow_html=True,
            )
            if uc3.button("⏻", key="logout_sidebar", help="Keluar"):
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
