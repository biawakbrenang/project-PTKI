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

# --- CUSTOM STYLES FOR UI/UX ---
# Ini akan membuat tampilan lebih mirip dengan gambar referensi
def set_custom_style():
    st.set_page_config(
        page_title="SIKAD V2",
        page_icon="🎓",
        layout="wide",
        initial_sidebar_state="expanded"
    )
    
    # CSS untuk meniru tampilan dashboard modern
    st.markdown("""
    <style>
        /* Sidebar Styling */
        .css-1d391kg { background-color: #f8fafc !important; }
        .sidebar .main-container { height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        
        /* Menu Buttons */
        .stButton > button {
            border-radius: 8px;
            width: 100%;
            text-align: left;
            padding: 12px 20px;
            margin-bottom: 5px;
            color: #64748b;
            background-color: transparent !important;
            border: none !important;
        }
        .stButton > button:hover { background-color: #e2e8f0; color: #0f172a; }
        .stButton > button.primary { background-color: #dbeafe !important; color: #1e40af !important; }
        
        /* Header dalam kontainer putih */
        div[data-testid="stHorizontalBlock"] {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }
        
        /* Metrics Styling */
        div.data-testid-value { font-size: 2rem !important; font-weight: bold !important; }
        .metric-label { color: #64748b !important; font-size: 1rem; }

        /* Input styling */
        div[data-testid="stSelectbox"], div[data-testid="stTextualiInput"] {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        /* Progress bar */
        div[data-testid="stProgress"] {
            border-radius: 999px;
            background-color: #e2e8f0;
        }
        div[data-testid="stProgress"] > div {
            border-radius: 999px;
        }

        /* User Profile in Sidebar Footer */
        .footer-user-profile {
            display: flex; align-items: center; gap: 15px; padding: 20px;
            background-color: #f1f5f9; border-top: 1px solid #e2e8f0;
            border-radius: 12px; margin-top: auto;
        }
        .user-avatar {
            width: 45px; height: 45px; background-color: #bfdbfe; color: #1e3a8a;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; border-radius: 50%; font-size: 1.2rem;
        }
    </style>
    """, unsafe_allow_html=True)


set_custom_style()
db.init_db()

VALID_STATUSES = ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"]

# --------------------------------------------------------------------------
# Auth helpers & Database Functions (sama seperti sebelumnya)
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

# ... Semua fungsi query dari database (`get_courses_by_dosen`, dll.) 
# TETAP SAMA PERSIS SEPERTI KODE ASLI ANDA DI ATAS ...
# Saya copy-paste semua fungsi query di sini agar lengkap,
# tetapi fungsionalitasnya tidak berubah.
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
    col1, col2 = st.columns([1.2, 0.8])
    with col1:
        st.markdown(
            """
            <div style="padding: 50px 40px; border-radius: 24px; background: linear-gradient(135deg, #2563eb, #0f172a); height: 100%; display:flex; flex-direction:column; justify-content:center;">
                 <h2 style="color:white; font-size: 1.1em; opacity: 0.9; margin-bottom: 30px;">System akademik dosen</h2>
                 <h1 style="color:white; font-size: 3em; font-weight: 800; line-height: 1.1;">Kelola absensi kelas<br>dengan cepat dan rapi.</h1>
                 <p style="color:#cbd5e1; font-size: 1.1em; margin-top:20px; line-height:1.6">Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan sudah terhubung dalam satu alur kerja.</p>
            </div>
            """,
            unsafe_allow_html=True,
        )
    with col2:
        st.markdown("---")
        st.markdown("")
        st.markdown("## 🎓 Masuk ke SIAKAD V2")
        st.caption("Gunakan akun dosen untuk mengakses sistem.")
        with st.form("login_form"):
            email = st.text_input("Email", placeholder="firansyah@univ.ac.id")
            password = st.text_input("Password", type="password", placeholder="Masukkan password")
            submitted = st.form_submit_button("➤ Masuk", use_container_width=True, type="primary")
            if submitted:
                if login(email.strip(), password):
                    st.session_state.page = "dashboard"
                    st.rerun()
                else:
                    st.error("Email atau password salah.")
        st.markdown("""
        <div style="margin-top: 30px; padding: 15px; background-color: #f1f5f9; border-radius: 8px;">
            <span style="font-weight: bold;">Akun demo:</span><br>
            Email: firansyah@univ.ac.id<br>
            Password: password
        </div>
        """, unsafe_allow_html=True)


def page_dashboard():
    today_str = date.today().strftime("%d %B %Y").capitalize()
    
    # Header Besar Gradient
    st.markdown(f"""
    <div style="background:linear-gradient(135deg,#2563eb,#0d9488);border-radius:16px;padding:40px;color:white;margin-bottom:20px;">
      <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">SELAMAT DATANG KEMBALI</div>
      <h1 style="font-size:2.8rem;font-weight:900;margin:10px 0;">{st.session_state.user['nama_lengkap'].split(',')[0]}</h1>
      <p style="font-size:1.1rem;opacity:0.9;margin-top:0;">Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.</p>
    </div>
    """, unsafe_allow_html=True)

    stats = get_dashboard_stats(current_dosen_id())
    
    # Kotak Metrik Statistik
    c1, c2, c3, c4 = st.columns(4)
    for col, label, value in [(c1, "Mata Kuliah", stats["total_matkul"]),
                              (c2, "Mahasiswa", stats["total_mahasiswa"]),
                              (c3, "Jadwal Kelas", stats["total_jadwal"]),
                              (c4, "Rata Kehadiran", f"{stats['rata_kehadiran']}%")]:
        with st.container(border=False):
             st.markdown("<div data-testid='stHorizontalBlock'><div class='metric-label' style='display:block; color:#64748b; margin-bottom:5px;'>"+label+"</div><div data-testid='value'>"+str(value)+"</div></div>", unsafe_allow_html=True)

    st.divider()
    col1, col2 = st.columns([1, 1.2])
    with col1:
        st.markdown("### Aksi Cepat")
        st.caption("Masuk ke pekerjaan utama tanpa banyak klik.")
        a, b, cc = st.columns(3)
        for btn, label, page_key in [(a, "📋 Input Absensi", "absensi"),
                                     (b, "📄 Lihat Rekap", "rekap"),
                                     (cc, "👥 Mahasiswa", "mahasiswa")]:
            if btn.button(label, use_container_width=True):
                st.session_state.page = page_key
                st.rerun()

    with col2:
        st.markdown("### Jadwal Terdekat")
        st.caption("5 pertemuan berikutnya di sistem.")
        schedules = get_upcoming_schedules(current_dosen_id())
        if not schedules:
            st.info("Belum ada jadwal perkuliahan.")
        for s in schedules:
            st.markdown(f"**{s['nama_matkul']}** <small>{s['kode_matkul']} - Pertemuan {s['pertemuan_ke']}</small>", unsafe_allow_html=True)
            try:
                tanggal = datetime.strptime(s["tanggal_pertemuan"], "%Y-%m-%d").strftime("%d %b %Y".upper())
            except ValueError:
                tanggal = s["tanggal_pertemuan"]
            st.caption(tanggal + f" | {s['jam_mulai']} - {s['jam_selesai']}")
            st.divider()


def page_absensi():
    st.markdown("## 📋 Input Absensi")
    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    col1, col2 = st.columns(2)
    course_label = col1.selectbox("Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))

    if course_label == "Pilih mata kuliah":
        st.stop()

    id_matkul = course_options[course_label]
    schedules = get_schedules_by_course(id_matkul)
    schedule_options = {}
    for s in schedules:
        try:
            tanggal = datetime.strptime(s["tanggal_pertemuan"], "%Y-%m-%d").strftime("%d %b %Y".upper())
        except ValueError:
            tanggal = s["tanggal_pertemuan"]
        label = f"Pertemuan {s['pertemuan_ke']} - {tanggal}, {s['jam_mulai']}"
        schedule_options[label] = s

    schedule_label = col2.selectbox("Pertemuan", ["Pilih jadwal"] + list(schedule_options.keys()))

    if schedule_label == "Pilih jadwal":
        st.stop()

    selected_schedule = schedule_options[schedule_label]
    id_jadwal = selected_schedule["id_jadwal"]

    # Info ringkas
    m1, m2, m3 = st.columns(3)
    m1.markdown("**Mata kuliah:** " + course_label.split(" - ")[1])
    m2.markdown("**Pertemuan:** " + str(selected_schedule["pertemuan_ke"]))
    m3.markdown("**Ruangan:** " + selected_schedule["ruangan"])

    students = get_students_by_course(id_matkul)
    if not students:
        st.warning("Belum ada mahasiswa di mata kuliah ini.")
        return

    attendance_rows = get_attendance_by_schedule(id_jadwal)
    mark_all = st.button("✓ Tandai Hadir Semua")

    st.markdown("### Daftar Kehadiran")
    
    with st.form("form_absensi"):
        status_values = {}
        note_values = {}
        
        # Tabel Header
        th_cols = st.columns([2.5, 4, 1.5])
        th_cols[0].markdown("**MAHASISWA**")
        th_cols[1].markdown("**STATUS**")
        th_cols[2].markdown("**KETERANGAN**")
        st.divider()

        for student in students:
            sid = student["id_mahasiswa"]
            current = attendance_rows.get(sid, {}).get("status_kehadiran", "Hadir")
            current_note = attendance_rows.get(sid, {}).get("keterangan", "") or ""
            if mark_all:
                current = "Hadir"

            r_cols = st.columns([2.5, 4, 1.5])
            
            r_cols[0].markdown(f"**{student['nama_mahasiswa']}**<br><small>{student['npm']} - {student['program_studi']}</small>", unsafe_allow_html=True)
            
            status_values[sid] = r_cols[1].radio(
                "Status",
                VALID_STATUSES,
                index=VALID_STATUSES.index(current) if current in VALID_STATUSES else 0,
                key=f"status_{id_jadwal}_{sid}",
                horizontal=True,
                label_visibility="collapsed",
            )
            
            note_values[sid] = r_cols[2].text_input(
                "Catatan", value=current_note, key=f"note_{id_jadwal}_{sid}",
                placeholder="Catatan opsional", label_visibility="collapsed",
            )

        st.divider()
        submitted = st.form_submit_button("💾 Simpan Absensi", type="primary", use_container_width=True)
        if submitted:
            for sid, status in status_values.items():
                save_attendance(sid, id_jadwal, status, note_values[sid].strip())
            st.success("Absensi berhasil disimpan.")
            st.rerun()


def page_rekap():
    st.markdown("## 📄 Rekap Kehadiran")
    courses = get_courses_by_dosen(current_dosen_id())
    if not courses:
        st.info("Belum ada mata kuliah yang diampu.")
        return

    course_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
    course_label = st.selectbox("Filter Mata Kuliah", ["Pilih mata kuliah"] + list(course_options.keys()))
    if course_label == "Pilih mata kuliah": st.stop()

    id_matkul = course_options[course_label]
    summary = get_course_summary(id_matkul)
    c1, c2, c3 = st.columns(3)
    c1.metric("Mahasiswa", summary["total_mahasiswa"] or 0)
    c2.metric("Total Jadwal", summary["total_jadwal"] or 0)
    c3.metric("Data Terisi", summary["total_absensi"] or 0)

    recap = get_attendance_recap(id_matkul)
    if not recap:
        st.info("Belum ada mahasiswa atau data absensi untuk mata kuliah ini.")
        return

    st.markdown(f"### Rekap {course_label.split(' - ')[1]}")
    st.caption("Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.")

    # Tabel Header Rekap
    th_cols = st.columns(["Nama", "Hadir", "Telat", "Sakit", "Izin", "Alpa", "Persen"])
    for col, label in zip(th_cols, ["Mahasiswa", "Hadir", "Terlambat", "Sakit", "Izin", "Alpa", "Persentase"]):
        col.markdown(f"**{label}**")
    st.divider()

    for row in recap:
        total = max(row["total_jadwal"] or 0, 1)
        present = (row["hadir"] or 0) + (row["terlambat"] or 0)
        percentage = min(100, round(present / total * 100))

        cols = st.columns(["Nama", "Hadir", "Telat", "Sakit", "Izin", "Alpa", "Persen"])
        cols[0].markdown(f"**{row['nama_mahasiswa']}**<br><small>{row['npm']}</small>", unsafe_allow_html=True)
        cols[1].write(row["hadir"] or 0)
        cols[2].write(row["terlambat"] or 0)
        cols[3].write(row["sakit"] or 0)
        cols[4].write(row["izin"] or 0)
        cols[5].write(row["alpa"] or 0)
        
        with cols[6]:
            st.progress(percentage / 100)
            st.write(f"**{percentage}%**")
        st.divider()


def page_mahasiswa():
    st.markdown("## 👥 Data Mahasiswa")
    id_dosen = current_dosen_id()
    courses = get_courses_by_dosen(id_dosen)
    editing = st.session_state.get("editing_student")

    col_form, col_list = st.columns([0.9, 1.4])

    with col_form:
        st.markdown(f"### {'Edit Mahasiswa' if editing else 'Tambah Mahasiswa'}")
        with st.form("form_mahasiswa", clear_on_submit=not editing):
            c1, c2 = st.columns(2)
            npm = c1.text_input("NPM", value=editing["npm"] if editing else "")
            angkatan = c2.number_input("Angkatan", min_value=2000, max_value=2099, value=int(editing["angkatan"]) if editing else date.today().year)
            nama = st.text_input("Nama Mahasiswa", value=editing["nama_mahasiswa"] if editing else "")
            prodi = st.text_input("Program Studi", value=editing["program_studi"] if editing else "")
            email = st.text_input("Email", value=(editing["email"] or "") if editing else "")

            id_matkul_baru = None
            tahun_ajaran = None
            if not editing:
                matkul_options = {f"{c['kode_matkul']} - {c['nama_matkul']}": c["id_matkul"] for c in courses}
                matkul_label = st.selectbox("Mata Kuliah", ["Pilih mata kuliah"] + list(matkul_options.keys()))
                id_matkul_baru = matkul_options.get(matkul_label)
                default_tahun = f"{date.today().year}/{date.today().year + 1}"
                tahun_ajaran = st.text_input("Tahun Ajaran", value=default_tahun)

            submitted = st.form_submit_button("💾 Simpan", type="primary", use_container_width=True)
            if submitted:
                data = {
                    "npm": npm.strip(), "nama_mahasiswa": nama.strip(), "program_studi": prodi.strip(),
                    "angkatan": int(angkatan), "email": email.strip(), "tahun_ajaran": tahun_ajaran,
                }
                try:
                    if not data["npm"] or not data["nama_mahasiswa"] or not data["program_studi"]:
                        raise ValueError("Lengkapi NPM, nama, dan program studi.")
                    if editing:
                        student_update(editing["id_mahasiswa"], data)
                        st.session_state.editing_student = None
                        st.success("Data berhasil diperbarui.")
                    else:
                        if not id_matkul_baru:
                            raise ValueError("Pilih mata kuliah.")
                        student_create(data, id_matkul_baru)
                        st.success("Mahasiswa berhasil ditambahkan.")
                    st.rerun()
                except Exception as e:
                    st.error(f"Gagal menyimpan: {e}")

        if editing and st.button("✖ Batal Edit"):
            st.session_state.editing_student = None
            st.rerun()

    with col_list:
        keyword = st.text_input("🔍 Cari NPM, nama, atau program studi")
        students = student_get_by_lecturer(id_dosen, keyword)
        st.markdown(f"### Daftar Mahasiswa ({len(students)})")
        
        # Header Tabel
        th_cols = st.columns([3, 2.5, 1.5, 1.5])
        for l in ["Mahasiswa", "Prodi", "Angkatan", "Aksi"]:
             th_cols[list(["Mahasiswa", "Prodi", "Angkatan", "Aksi"]).index(l)].markdown(f"**{l}**")
        st.divider()

        for student in students:
            av_letter = student['nama_mahasiswa'][0].upper()
            r_cols = st.columns([3, 2.5, 1.5, 1.5])
            r_cols[0].markdown(f"<div style='display:flex;gap:10px;align-items:center'><div style='width:30px;height:30px;background:#bfdbfe;color:#1e3a8a;border-radius:50%;text-align:center;line-height:30px;font-weight:bold'>{av_letter}</div><div><b>{student['nama_mahasiswa']}</b><br><small>{student['npm']}</small></div></div>", unsafe_allow_html=True)
            r_cols[1].write(student["program_studi"])
            r_cols[2].write(student["angkatan"])
            with r_cols[3]:
                b1, b2 = st.columns(2)
                if b1.button("✏️", key=f"edit_{student['id_mahasiswa']}"):
                    st.session_state.editing_student = student
                    st.rerun()
                if b2.button("🗑️", key=f"del_{student['id_mahasiswa']}"):
                    if st.button(f"Hapus {student['nama_mahasiswa']}?", key=f"confirm_del_{student['id_mahasiswa']}"):
                         if student_find(student["id_mahasiswa"], id_dosen):
                            student_delete_for_lecturer(student["id_mahasiswa"], id_dosen)
                            st.success("Berhasil dihapus.")
                         st.rerun()
            st.divider()


# --------------------------------------------------------------------------
# Layout / navigation
# --------------------------------------------------------------------------
def main():
    if "page" not in st.session_state:
        st.session_state.page = "dashboard"

    if not is_logged_in():
        page_login()
        return

    with st.sidebar:
        # Logo Area
        st.markdown("### 🎓 SIAKAD V2")
        st.caption("ABSensi DOSEN")
        st.divider()

        nav = {
            "dashboard": "🏠 Dashboard",
            "absensi": "📋 Input Absensi",
            "mahasiswa": "👥 Mahasiswa",
            "rekap": "📄 Rekap",
        }
        for key, label in nav.items():
             # Logic untuk highlight menu aktif
             button_type = "primary" if st.session_state.page == key else "secondary"
             if st.button(label, use_container_width=True, type=button_type):
                 st.session_state.page = key
                 st.rerun()
        
        st.divider()
        if st.button("🚪 Logout", use_container_width=True):
            logout()
            st.rerun()
        
        # Footer user profile di sidebar
        user_name_short = st.session_state.user['nama_lengkap'].split(',')[0]
        nidn_short = st.session_state.user['nidn'][:6] + "..."
        st.markdown(f"""
        <div class='footer-user-profile'>
            <div class='user-avatar'>{user_name_short[0].upper()}</div>
            <div>
                <div style='font-weight:bold;font-size:0.9rem'>{user_name_short}</div>
                <div style='font-size:0.8rem;color:#64748b'>{nidn_short}</div>
            </div>
        </div>
        """, unsafe_allow_html=True)

    pages = {
        "dashboard": page_dashboard,
        "absensi": page_absensi,
        "rekap": page_rekap,
        "mahasiswa": page_mahasiswa,
    }
    pages.get(st.session_state.page, page_dashboard)()


if __name__ == "__main__":
    main()