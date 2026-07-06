"""
Lapisan database untuk Sistem Absensi Dosen versi Streamlit.

Skema ini adalah konversi dari database.sql (MySQL) ke SQLite, supaya
aplikasi bisa langsung jalan tanpa perlu server MySQL terpisah
(cocok untuk deploy ke Streamlit Community Cloud).

Tabel: dosen, mahasiswa, mata_kuliah, kelas_mahasiswa, jadwal_perkuliahan, absensi
"""

import sqlite3
from pathlib import Path
from datetime import date, timedelta

DB_PATH = Path(__file__).parent / "absensi.db"

# Hash bcrypt asli dari database.sql (password: "password")
DEFAULT_DOSEN_HASH = "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"


def get_conn() -> sqlite3.Connection:
    conn = sqlite3.connect(DB_PATH, check_same_thread=False)
    conn.execute("PRAGMA foreign_keys = ON")
    conn.row_factory = sqlite3.Row
    return conn


def init_db():
    """Buat tabel jika belum ada, lalu isi data contoh jika tabel masih kosong."""
    conn = get_conn()
    cur = conn.cursor()
    cur.executescript(
        """
        CREATE TABLE IF NOT EXISTS dosen (
            id_dosen INTEGER PRIMARY KEY AUTOINCREMENT,
            nidn TEXT UNIQUE NOT NULL,
            nama_lengkap TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            foto_profil TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS mahasiswa (
            id_mahasiswa INTEGER PRIMARY KEY AUTOINCREMENT,
            npm TEXT UNIQUE NOT NULL,
            nama_mahasiswa TEXT NOT NULL,
            program_studi TEXT NOT NULL,
            angkatan INTEGER NOT NULL,
            email TEXT UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS mata_kuliah (
            id_matkul INTEGER PRIMARY KEY AUTOINCREMENT,
            kode_matkul TEXT UNIQUE NOT NULL,
            nama_matkul TEXT NOT NULL,
            sks INTEGER NOT NULL,
            semester INTEGER NOT NULL,
            id_dosen INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_dosen) REFERENCES dosen(id_dosen) ON DELETE CASCADE ON UPDATE CASCADE
        );

        CREATE TABLE IF NOT EXISTS kelas_mahasiswa (
            id_kelas_mhs INTEGER PRIMARY KEY AUTOINCREMENT,
            id_mahasiswa INTEGER NOT NULL,
            id_matkul INTEGER NOT NULL,
            tahun_ajaran TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (id_matkul) REFERENCES mata_kuliah(id_matkul) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE (id_mahasiswa, id_matkul, tahun_ajaran)
        );

        CREATE TABLE IF NOT EXISTS jadwal_perkuliahan (
            id_jadwal INTEGER PRIMARY KEY AUTOINCREMENT,
            id_matkul INTEGER NOT NULL,
            hari TEXT NOT NULL,
            jam_mulai TEXT NOT NULL,
            jam_selesai TEXT NOT NULL,
            ruangan TEXT NOT NULL,
            pertemuan_ke INTEGER NOT NULL,
            tanggal_pertemuan DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_matkul) REFERENCES mata_kuliah(id_matkul) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE (id_matkul, pertemuan_ke, tanggal_pertemuan)
        );

        CREATE TABLE IF NOT EXISTS absensi (
            id_absensi INTEGER PRIMARY KEY AUTOINCREMENT,
            id_mahasiswa INTEGER NOT NULL,
            id_jadwal INTEGER NOT NULL,
            status_kehadiran TEXT NOT NULL CHECK (status_kehadiran IN ('Hadir','Sakit','Izin','Alpa','Terlambat')),
            keterangan TEXT,
            waktu_input TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (id_jadwal) REFERENCES jadwal_perkuliahan(id_jadwal) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE (id_mahasiswa, id_jadwal)
        );
        """
    )
    conn.commit()

    cur.execute("SELECT COUNT(*) AS c FROM dosen")
    if cur.fetchone()["c"] == 0:
        _seed(conn)

    conn.close()


def _seed(conn: sqlite3.Connection):
    cur = conn.cursor()

    cur.execute(
        "INSERT INTO dosen (nidn, nama_lengkap, email, password) VALUES (?, ?, ?, ?)",
        ("12345678", "Firansyah, S.Si., MM", "firansyah@univ.ac.id", DEFAULT_DOSEN_HASH),
    )
    id_dosen = cur.lastrowid

    mahasiswa_rows = [
        ("2021001", "Budi Santoso", "Teknik Informatika", 2021, "budi@student.univ.ac.id"),
        ("2021002", "Siti Aminah", "Teknik Informatika", 2021, "siti@student.univ.ac.id"),
        ("2021003", "Andi Wijaya", "Teknik Informatika", 2021, "andi@student.univ.ac.id"),
        ("2021004", "Dewi Lestari", "Sistem Informasi", 2021, "dewi@student.univ.ac.id"),
        ("2021005", "Rian Hidayat", "Sistem Informasi", 2021, "rian@student.univ.ac.id"),
    ]
    cur.executemany(
        "INSERT INTO mahasiswa (npm, nama_mahasiswa, program_studi, angkatan, email) VALUES (?, ?, ?, ?, ?)",
        mahasiswa_rows,
    )

    matkul_rows = [
        ("MK001", "Pemrograman Web", 3, 4, id_dosen),
        ("MK002", "Basis Data", 3, 4, id_dosen),
        ("MK003", "Analisis Algoritma", 3, 4, id_dosen),
    ]
    cur.executemany(
        "INSERT INTO mata_kuliah (kode_matkul, nama_matkul, sks, semester, id_dosen) VALUES (?, ?, ?, ?, ?)",
        matkul_rows,
    )

    kelas_rows = [
        (1, 1, "2023/2024"), (2, 1, "2023/2024"), (3, 1, "2023/2024"),
        (4, 1, "2023/2024"), (5, 1, "2023/2024"),
        (1, 2, "2023/2024"), (2, 2, "2023/2024"), (3, 2, "2023/2024"),
        (4, 3, "2023/2024"), (5, 3, "2023/2024"),
    ]
    cur.executemany(
        "INSERT INTO kelas_mahasiswa (id_mahasiswa, id_matkul, tahun_ajaran) VALUES (?, ?, ?)",
        kelas_rows,
    )

    today = date.today()
    jadwal_rows = [
        (1, "Senin", "08:00", "10:30", "Lab 1", 1, (today - timedelta(days=7)).isoformat()),
        (1, "Senin", "08:00", "10:30", "Lab 1", 2, today.isoformat()),
        (2, "Selasa", "10:00", "12:30", "R. 302", 1, (today - timedelta(days=6)).isoformat()),
        (3, "Rabu", "13:00", "15:30", "R. 405", 1, (today - timedelta(days=5)).isoformat()),
    ]
    cur.executemany(
        """INSERT INTO jadwal_perkuliahan
           (id_matkul, hari, jam_mulai, jam_selesai, ruangan, pertemuan_ke, tanggal_pertemuan)
           VALUES (?, ?, ?, ?, ?, ?, ?)""",
        jadwal_rows,
    )

    conn.commit()


def query(sql, params=()):
    conn = get_conn()
    try:
        cur = conn.execute(sql, params)
        return [dict(row) for row in cur.fetchall()]
    finally:
        conn.close()


def query_one(sql, params=()):
    rows = query(sql, params)
    return rows[0] if rows else None


def execute(sql, params=()):
    conn = get_conn()
    try:
        cur = conn.execute(sql, params)
        conn.commit()
        return cur.lastrowid
    finally:
        conn.close()


def executemany(sql, seq_of_params):
    conn = get_conn()
    try:
        conn.executemany(sql, seq_of_params)
        conn.commit()
    finally:
        conn.close()
