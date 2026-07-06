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
# Tema visual - meniru tampilan aslinya (Tailwind: putih, biru, gradient)
# --------------------------------------------------------------------------
CUSTOM_CSS = """
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

html, body, [class*="css"] { font-family: 'Inter', sans-serif; }
#MainMenu {visibility: hidden;}
footer {visibility: hidden;}
header[data-testid="stHeader"] { background: transparent; }

.block-container { padding-top: 1.5rem; padding-bottom: 3rem; max-width: 1200px; }

/* Sidebar */
section[data-testid="stSidebar"] {
    background: #ffffff;
    border-right: 1px solid #eef0f4;
}
section[data-testid="stSidebar"] .block-container { padding-top: 1.5rem; }

/* Sidebar nav buttons */
section[data-testid="stSidebar"] div[data-testid="stButton"] button {
    text-align: left;
    justify-content: flex-start;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.65rem 1rem;
    box-shadow: none;
}
section[data-testid="stSidebar"] div[data-testid="stButton"] button[kind="primary"] {
    background: #eff6ff;
    color: #2563eb;
}
section[data-testid="stSidebar"] div[data-testid="stButton"] button[kind="secondary"] {
    background: transparent;
    color: #475569;
}
section[data-testid="stSidebar"] div[data-testid="stButton"] button[kind="secondary"]:hover {
    background: #f8fafc;
    color: #1e293b;
    border: none;
}

/* Generic card container */
div[data-testid="stVerticalBlockBorderWrapper"] {
    border-radius: 16px !important;
}

/* Top bar */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.topbar h1 { font-size: 1.7rem; font-weight: 800; color: #0f172a; margin: 0; }
.topbar p { color: #94a3b8; margin: 0; font-size: 0.9rem; }

/* Hero banner */
.hero-banner {
    background: linear-gradient(120deg, #2563eb 0%, #0d9488 100%);
    border-radius: 24px;
    padding: 2.2rem 2.5rem;
    color: white;
    margin-bottom: 1.5rem;
    box-shadow: 0 20px 40px -20px rgba(37,99,235,0.45);
}
.hero-banner .eyebrow {
    text-transform: uppercase;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: #dbeafe;
}
.hero-banner h2 { font-size: 2.1rem; font-weight: 900; margin: 0.4rem 0; }
.hero-banner p { color: #e0f2fe; margin: 0; max-width: 640px; }

/* Stat cards */
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
.stat-card {
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 18px;
    padding: 1.4rem;
    box-shadow: 0 8px 24px -18px rgba(15,23,42,0.25);
}
.stat-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; margin-bottom: 0.9rem;
}
.stat-label { color: #64748b; font-size: 0.85rem; font-weight: 600; margin: 0 0 0.2rem 0; }
.stat-value { color: #0f172a; font-size: 1.9rem; font-weight: 900; margin: 0; }

/* Panels */
.panel-title { font-weight: 800; font-size: 1.1rem; color: #0f172a; margin-bottom: 0.15rem; }
.panel-subtitle { color: #94a3b8; font-size: 0.85rem; margin-bottom: 1rem; }

/* Schedule row */
.schedule-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.85rem 1rem; border-radius: 14px; background: #f8fafc;
    margin-bottom: 0.6rem;
}
.schedule-row .course { font-weight: 700; color: #0f172a; }
.schedule-row .meta { color: #94a3b8; font-size: 0.82rem; }
.schedule-row .date { font-weight: 700; color: #334155; text-align: right; }
.schedule-row .time { color: #94a3b8; font-size: 0.82rem; text-align: right; }

/* Quick action card content */
.qa-icon { font-size: 1.4rem; margin-bottom: 0.4rem; }
.qa-title { font-weight: 700; color: #0f172a; }
.qa-desc { color: #94a3b8; font-size: 0.82rem; }

/* Sidebar profile card */
.profile-card {
    display: flex; align-items: center; gap: 0.7rem;
    background: #f8fafc; border-radius: 14px; padding: 0.7rem 0.9rem;
    margin-top: 0.5rem;
}
.profile-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: #2563eb; color: white; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.profile-name { font-weight: 700; font-size: 0.88rem; color: #0f172a; }
.profile-sub { color: #94a3b8; font-size: 0.78rem; }

/* Sidebar logo */
.sidebar-logo { display: flex; align-items: center; gap: 0.7rem; margin-bottom: 1.6rem; }
.sidebar-logo .badge {
    width: 44px; height: 44px; border-radius: 13px;
    background: #2563eb; color: white; display: flex;
    align-items: center; justify-content: center; font-size: 1.3rem;
}
.sidebar-logo .title { font-weight: 900; color: #0f172a; font-size: 1.05rem; line-height: 1.1; }
.sidebar-logo .subtitle { color: #94a3b8; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em; }

/* Keluar button (top right) */
div[data-testid="stButton"] button[kind="primary"].keluar-btn,
.keluar-wrap button {
    background: #0f172a !important;
    color: white !important;
    border-radius: 999px !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 0.4rem 1.2rem !important;
}
</style>
"""
st.markdown(CUSTOM_CSS, unsafe_allow_html=True)


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

