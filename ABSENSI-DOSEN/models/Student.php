<?php
class Student {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByLecturer($id_dosen, $keyword = '') {
        $params = [$id_dosen];
        $where = '';

        if ($keyword !== '') {
            $where = " AND (m.npm LIKE ? OR m.nama_mahasiswa LIKE ? OR m.program_studi LIKE ?)";
            $like = '%' . $keyword . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $query = "SELECT DISTINCT m.*
                  FROM mahasiswa m
                  JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
                  JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
                  WHERE mk.id_dosen = ? {$where}
                  ORDER BY m.nama_mahasiswa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id_mahasiswa, $id_dosen) {
        $query = "SELECT DISTINCT m.*
                  FROM mahasiswa m
                  JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
                  JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
                  WHERE m.id_mahasiswa = ? AND mk.id_dosen = ?
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_mahasiswa, $id_dosen]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data, $id_matkul) {
        $this->conn->beginTransaction();
        try {
            $query = "INSERT INTO mahasiswa (npm, nama_mahasiswa, program_studi, angkatan, email)
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['npm'],
                $data['nama_mahasiswa'],
                $data['program_studi'],
                $data['angkatan'],
                $data['email'] ?: null
            ]);

            $id_mahasiswa = $this->conn->lastInsertId();
            $this->attachToCourse($id_mahasiswa, $id_matkul, $data['tahun_ajaran']);
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function update($id_mahasiswa, $data) {
        $query = "UPDATE mahasiswa
                  SET npm = ?, nama_mahasiswa = ?, program_studi = ?, angkatan = ?, email = ?
                  WHERE id_mahasiswa = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['npm'],
            $data['nama_mahasiswa'],
            $data['program_studi'],
            $data['angkatan'],
            $data['email'] ?: null,
            $id_mahasiswa
        ]);
    }

    public function deleteForLecturer($id_mahasiswa, $id_dosen) {
        $query = "DELETE km FROM kelas_mahasiswa km
                  JOIN mata_kuliah mk ON km.id_matkul = mk.id_matkul
                  WHERE km.id_mahasiswa = ? AND mk.id_dosen = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_mahasiswa, $id_dosen]);

        $cleanup = "DELETE m FROM mahasiswa m
                    LEFT JOIN kelas_mahasiswa km ON m.id_mahasiswa = km.id_mahasiswa
                    WHERE m.id_mahasiswa = ? AND km.id_kelas_mhs IS NULL";
        $stmt = $this->conn->prepare($cleanup);
        return $stmt->execute([$id_mahasiswa]);
    }

    public function attachToCourse($id_mahasiswa, $id_matkul, $tahun_ajaran) {
        $query = "INSERT IGNORE INTO kelas_mahasiswa (id_mahasiswa, id_matkul, tahun_ajaran)
                  VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_mahasiswa, $id_matkul, $tahun_ajaran]);
    }
}
?>
