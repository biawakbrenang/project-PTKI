<?php
class Attendance {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCoursesByDosen($id_dosen) {
        $query = "SELECT mk.*,
                         COUNT(DISTINCT km.id_mahasiswa) AS total_mahasiswa,
                         COUNT(DISTINCT jp.id_jadwal) AS total_jadwal
                  FROM mata_kuliah mk
                  LEFT JOIN kelas_mahasiswa km ON mk.id_matkul = km.id_matkul
                  LEFT JOIN jadwal_perkuliahan jp ON mk.id_matkul = jp.id_matkul
                  WHERE mk.id_dosen = ?
                  GROUP BY mk.id_matkul
                  ORDER BY mk.semester ASC, mk.nama_matkul ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_dosen);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseByDosen($id_matkul, $id_dosen) {
        $query = "SELECT * FROM mata_kuliah WHERE id_matkul = ? AND id_dosen = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_matkul, $id_dosen]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSchedulesByCourse($id_matkul) {
        $query = "SELECT * FROM jadwal_perkuliahan WHERE id_matkul = ? ORDER BY pertemuan_ke ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_matkul);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScheduleByCourse($id_jadwal, $id_matkul) {
        $query = "SELECT * FROM jadwal_perkuliahan WHERE id_jadwal = ? AND id_matkul = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_jadwal, $id_matkul]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentsByCourse($id_matkul) {
        $query = "SELECT m.* FROM mahasiswa m 
                  JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa 
                  WHERE km.id_matkul = ? ORDER BY m.nama_mahasiswa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_matkul);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAttendance($id_mahasiswa, $id_jadwal, $status, $keterangan) {
        $query = "INSERT INTO absensi (id_mahasiswa, id_jadwal, status_kehadiran, keterangan) 
                  VALUES (?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE status_kehadiran = VALUES(status_kehadiran), keterangan = VALUES(keterangan), waktu_input = CURRENT_TIMESTAMP";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_mahasiswa);
        $stmt->bindParam(2, $id_jadwal);
        $stmt->bindParam(3, $status);
        $stmt->bindParam(4, $keterangan);
        return $stmt->execute();
    }

    public function getAttendanceBySchedule($id_jadwal) {
        $query = "SELECT id_mahasiswa, status_kehadiran, keterangan
                  FROM absensi
                  WHERE id_jadwal = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_jadwal]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $attendance = [];

        foreach ($rows as $row) {
            $attendance[$row['id_mahasiswa']] = $row;
        }

        return $attendance;
    }

    public function getAttendanceRecap($id_matkul) {
        $query = "SELECT m.npm, m.nama_mahasiswa, 
                  SUM(CASE WHEN a.status_kehadiran = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                  SUM(CASE WHEN a.status_kehadiran = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                  SUM(CASE WHEN a.status_kehadiran = 'Izin' THEN 1 ELSE 0 END) as izin,
                  SUM(CASE WHEN a.status_kehadiran = 'Alpa' THEN 1 ELSE 0 END) as alpa,
                  SUM(CASE WHEN a.status_kehadiran = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
                  COUNT(DISTINCT jp.id_jadwal) as total_jadwal,
                  COUNT(a.id_absensi) as total_terisi
                  FROM mahasiswa m
                  JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
                  LEFT JOIN jadwal_perkuliahan jp ON km.id_matkul = jp.id_matkul
                  LEFT JOIN absensi a ON m.id_mahasiswa = a.id_mahasiswa AND jp.id_jadwal = a.id_jadwal
                  WHERE km.id_matkul = ?
                  GROUP BY m.id_mahasiswa
                  ORDER BY m.nama_mahasiswa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_matkul);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseSummary($id_matkul) {
        $query = "SELECT
                    COUNT(DISTINCT km.id_mahasiswa) AS total_mahasiswa,
                    COUNT(DISTINCT jp.id_jadwal) AS total_jadwal,
                    COUNT(a.id_absensi) AS total_absensi
                  FROM mata_kuliah mk
                  LEFT JOIN kelas_mahasiswa km ON mk.id_matkul = km.id_matkul
                  LEFT JOIN jadwal_perkuliahan jp ON mk.id_matkul = jp.id_matkul
                  LEFT JOIN absensi a ON jp.id_jadwal = a.id_jadwal
                  WHERE mk.id_matkul = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_matkul]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
