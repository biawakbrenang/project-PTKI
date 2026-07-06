<?php
class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getStats($id_dosen) {
        $stats = [];
        
        // Total Matkul
        $query = "SELECT COUNT(*) as total FROM mata_kuliah WHERE id_dosen = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_dosen]);
        $stats['total_matkul'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Mahasiswa (Unique)
        $query = "SELECT COUNT(DISTINCT id_mahasiswa) as total FROM kelas_mahasiswa km 
                  JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul 
                  WHERE mk.id_dosen = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_dosen]);
        $stats['total_mahasiswa'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Pertemuan Selesai
        $query = "SELECT COUNT(DISTINCT a.id_jadwal) as total FROM absensi a 
                  JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal 
                  JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul 
                  WHERE mk.id_dosen = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_dosen]);
        $stats['pertemuan_selesai'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $query = "SELECT COUNT(*) as total FROM jadwal_perkuliahan jp
                  JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
                  WHERE mk.id_dosen = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_dosen]);
        $stats['total_jadwal'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $query = "SELECT ROUND(
                    (SUM(CASE WHEN a.status_kehadiran IN ('Hadir', 'Terlambat') THEN 1 ELSE 0 END) / NULLIF(COUNT(a.id_absensi), 0)) * 100
                  ) as total
                  FROM absensi a
                  JOIN jadwal_perkuliahan jp ON a.id_jadwal = jp.id_jadwal
                  JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
                  WHERE mk.id_dosen = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_dosen]);
        $stats['rata_kehadiran'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        return $stats;
    }

    public function getTodaySchedules($id_dosen) {
        $query = "SELECT jp.*, mk.kode_matkul, mk.nama_matkul
                  FROM jadwal_perkuliahan jp
                  JOIN mata_kuliah mk ON jp.id_matkul = mk.id_matkul
                  WHERE mk.id_dosen = ?
                  ORDER BY jp.tanggal_pertemuan ASC, jp.jam_mulai ASC
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_dosen]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
