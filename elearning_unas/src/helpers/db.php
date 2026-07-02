<?php
/**
 * Database Helper Functions
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Execute query safely with prepared statements
 */
function executeQuery($conn, $sql, $params = [], $types = '') {
  try {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
      throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!empty($params)) {
      if (empty($types)) {
        // Auto-detect types
        $types = '';
        foreach ($params as $param) {
          if (is_int($param)) {
            $types .= 'i';
          } elseif (is_float($param)) {
            $types .= 'd';
          } else {
            $types .= 's';
          }
        }
      }
      
      $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
      throw new Exception("Execute failed: " . $stmt->error);
    }
    
    return $stmt;
  } catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    return false;
  }
}

/**
 * Fetch single row
 */
function fetchRow($conn, $sql, $params = [], $types = '') {
  $stmt = executeQuery($conn, $sql, $params, $types);
  
  if (!$stmt) {
    return null;
  }
  
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
  
  return $row;
}

/**
 * Alias for fetchRow; several pages use fetchOne terminology.
 */
function fetchOne($conn, $sql, $params = [], $types = '') {
  return fetchRow($conn, $sql, $params, $types);
}

/**
 * Fetch all rows
 */
function fetchAll($conn, $sql, $params = [], $types = '') {
  $stmt = executeQuery($conn, $sql, $params, $types);
  
  if (!$stmt) {
    return [];
  }
  
  $result = $stmt->get_result();
  $rows = [];
  
  while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
  }
  
  $stmt->close();
  
  return $rows;
}

/**
 * Insert record
 */
function insertRecord($conn, $table, $data) {
  $columns = array_keys($data);
  $values = array_values($data);
  $placeholders = array_fill(0, count($columns), '?');
  
  $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
  
  $types = '';
  foreach ($values as $value) {
    if (is_int($value)) {
      $types .= 'i';
    } elseif (is_float($value)) {
      $types .= 'd';
    } else {
      $types .= 's';
    }
  }
  
  $stmt = executeQuery($conn, $sql, $values, $types);
  
  if (!$stmt) {
    return false;
  }
  
  $insertId = $conn->insert_id;
  $stmt->close();
  
  return $insertId;
}

/**
 * Update record
 */
function updateRecord($conn, $table, $data, $where, $whereParams = [], $whereTypes = '') {
  $sets = [];
  $values = [];
  
  foreach ($data as $column => $value) {
    $sets[] = "$column = ?";
    $values[] = $value;
  }
  
  $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE $where";
  
  $types = '';
  foreach ($values as $value) {
    if (is_int($value)) {
      $types .= 'i';
    } elseif (is_float($value)) {
      $types .= 'd';
    } else {
      $types .= 's';
    }
  }
  
  $allParams = array_merge($values, $whereParams);
  $allTypes = $types . $whereTypes;
  
  $stmt = executeQuery($conn, $sql, $allParams, $allTypes);
  
  if (!$stmt) {
    return false;
  }
  
  $affectedRows = $conn->affected_rows;
  $stmt->close();
  
  return $affectedRows;
}

/**
 * Delete record
 */
function deleteRecord($conn, $table, $where, $whereParams = [], $whereTypes = '') {
  $sql = "DELETE FROM $table WHERE $where";
  
  $stmt = executeQuery($conn, $sql, $whereParams, $whereTypes);
  
  if (!$stmt) {
    return false;
  }
  
  $affectedRows = $conn->affected_rows;
  $stmt->close();
  
  return $affectedRows;
}

/**
 * Get user by username
 */
function getUserByUsername($conn, $username) {
  return fetchRow($conn, "SELECT * FROM users WHERE username = ?", [$username], 's');
}

/**
 * Get user by ID
 */
function getUserById($conn, $userId) {
  return fetchRow($conn, "SELECT * FROM users WHERE id = ?", [$userId], 'i');
}

/**
 * Get mahasiswa by user ID
 */
function getMahasiswaByUserId($conn, $userId) {
  return fetchRow($conn, "
    SELECT m.*, j.nama_jurusan, u.nama_lengkap, u.email
    FROM mahasiswa m
    JOIN jurusan j ON m.jurusan_id = j.id
    JOIN users u ON m.user_id = u.id
    WHERE m.user_id = ?
  ", [$userId], 'i');
}

/**
 * Get dosen by user ID
 */
function getDosenByUserId($conn, $userId) {
  return fetchRow($conn, "
    SELECT d.*, j.nama_jurusan, u.nama_lengkap, u.email
    FROM dosen d
    JOIN jurusan j ON d.jurusan_id = j.id
    JOIN users u ON d.user_id = u.id
    WHERE d.user_id = ?
  ", [$userId], 'i');
}

/**
 * Get KRS by mahasiswa ID
 */
function getKRSByMahasiswaId($conn, $mahasiswaId, $tahunAkademik = null) {
  $sql = "
    SELECT k.*, m.nama_matkul, m.sks, k.kelas_id, kl.hari, kl.jam_mulai, kl.jam_selesai, kl.ruangan
    FROM krs k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    JOIN kelas kl ON k.kelas_id = kl.id
    WHERE k.mahasiswa_id = ?
  ";
  
  $params = [$mahasiswaId];
  $types = 'i';
  
  if ($tahunAkademik) {
    $sql .= " AND k.tahun_akademik = ?";
    $params[] = $tahunAkademik;
    $types .= 's';
  }
  
  $sql .= " ORDER BY m.semester, m.nama_matkul";
  
  return fetchAll($conn, $sql, $params, $types);
}

/**
 * Get kelas by dosen ID
 */
function getKelasByDosenId($conn, $dosenId, $tahunAkademik = null) {
  $sql = "
    SELECT k.*, m.nama_matkul, m.sks, COUNT(krs.id) as jumlah_mahasiswa
    FROM kelas k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    LEFT JOIN krs ON k.id = krs.kelas_id AND krs.status_krs = 'diambil'
    WHERE k.dosen_id = ?
  ";
  
  $params = [$dosenId];
  $types = 'i';
  
  if ($tahunAkademik) {
    $sql .= " AND k.tahun_akademik = ?";
    $params[] = $tahunAkademik;
    $types .= 's';
  }
  
  $sql .= " GROUP BY k.id ORDER BY k.hari, k.jam_mulai";
  
  return fetchAll($conn, $sql, $params, $types);
}

/**
 * Get mahasiswa in kelas
 */
function getMahasiswaInKelas($conn, $kelasId) {
  return fetchAll($conn, "
    SELECT u.id, u.nama_lengkap, u.username, m.nim, krs.id as krs_id, krs.nilai_akhir, krs.grade
    FROM krs
    JOIN mahasiswa m ON krs.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE krs.kelas_id = ? AND krs.status_krs = 'diambil'
    ORDER BY u.nama_lengkap
  ", [$kelasId], 'i');
}

/**
 * Get nilai by KRS ID
 */
function getNilaiByKrsId($conn, $krsId) {
  return fetchAll($conn, "
    SELECT * FROM nilai
    WHERE krs_id = ?
    ORDER BY jenis_nilai
  ", [$krsId], 'i');
}

/**
 * Get absensi by KRS ID
 */
function getAbsensiByKrsId($conn, $krsId) {
  return fetchAll($conn, "
    SELECT * FROM absensi
    WHERE krs_id = ?
    ORDER BY pertemuan
  ", [$krsId], 'i');
}

/**
 * Get materi by kelas ID
 */
function getMateriByKelasId($conn, $kelasId) {
  return fetchAll($conn, "
    SELECT * FROM materi
    WHERE kelas_id = ?
    ORDER BY pertemuan, created_at DESC
  ", [$kelasId], 'i');
}

/**
 * Get pengumuman by kelas ID
 */
function getPengumumanByKelasId($conn, $kelasId) {
  return fetchAll($conn, "
    SELECT p.*, u.nama_lengkap
    FROM pengumuman p
    JOIN dosen d ON p.dosen_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE p.kelas_id = ?
    ORDER BY p.created_at DESC
  ", [$kelasId], 'i');
}

/**
 * Calculate grade from nilai akhir
 */
function calculateGrade($nilaiAkhir) {
  if ($nilaiAkhir >= 85) return 'A';
  if ($nilaiAkhir >= 80) return 'A-';
  if ($nilaiAkhir >= 75) return 'B+';
  if ($nilaiAkhir >= 70) return 'B';
  if ($nilaiAkhir >= 65) return 'B-';
  if ($nilaiAkhir >= 60) return 'C+';
  if ($nilaiAkhir >= 55) return 'C';
  if ($nilaiAkhir >= 50) return 'D';
  return 'E';
}

/**
 * Calculate GPA from grade
 */
function calculateGPA($grade) {
  $gradePoints = [
    'A' => 4.0,
    'A-' => 3.7,
    'B+' => 3.3,
    'B' => 3.0,
    'B-' => 2.7,
    'C+' => 2.3,
    'C' => 2.0,
    'D' => 1.0,
    'E' => 0.0
  ];
  
  return $gradePoints[$grade] ?? 0.0;
}

/**
 * Get activity log
 */
function getActivityLog($conn, $limit = 50) {
  return fetchAll($conn, "
    SELECT al.*, u.nama_lengkap
    FROM activity_log al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT ?
  ", [$limit], 'i');
}

/**
 * Log activity
 */
function logActivity($conn, $userId, $action, $description = null, $ipAddress = null) {
  if (!$ipAddress) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
  }
  
  return insertRecord($conn, 'activity_log', [
    'user_id' => $userId,
    'action' => $action,
    'description' => $description,
    'ip_address' => $ipAddress
  ]);
}

/**
 * Get Semester Info
 */
function getSemesterInfo($conn, $mahasiswaId) {
  return fetchOne($conn, "
    SELECT * FROM mahasiswa WHERE id = ?
  ", [$mahasiswaId], 'i');
}

/**
 * Calculate IPK
 */
function calculateIPK($conn, $mahasiswaId) {
  $result = fetchOne($conn, "
    SELECT AVG(CASE 
      WHEN grade = 'A' THEN 4.0
      WHEN grade = 'A-' THEN 3.7
      WHEN grade = 'B+' THEN 3.3
      WHEN grade = 'B' THEN 3.0
      WHEN grade = 'B-' THEN 2.7
      WHEN grade = 'C+' THEN 2.3
      WHEN grade = 'C' THEN 2.0
      WHEN grade = 'D' THEN 1.0
      ELSE 0.0
    END) as ipk
    FROM krs
    WHERE mahasiswa_id = ? AND grade IS NOT NULL
  ", [$mahasiswaId], 'i');
  
  return $result['ipk'] ?? 0;
}

/**
 * Get Transkrip
 */
function getTranskrip($conn, $mahasiswaId) {
  return fetchAll($conn, "
    SELECT k.*, m.kode_matkul, m.nama_matkul, m.sks, m.semester
    FROM krs k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.mahasiswa_id = ?
    ORDER BY m.semester ASC, m.kode_matkul ASC
  ", [$mahasiswaId], 'i');
}

/**
 * Get Academic History
 */
function getAcademicHistory($conn, $mahasiswaId) {
  return fetchAll($conn, "
    SELECT 
      m.semester,
      COUNT(*) as jumlah_matkul,
      SUM(m.sks) as sks_diambil,
      AVG(CASE 
        WHEN k.grade = 'A' THEN 4.0
        WHEN k.grade = 'A-' THEN 3.7
        WHEN k.grade = 'B+' THEN 3.3
        WHEN k.grade = 'B' THEN 3.0
        WHEN k.grade = 'B-' THEN 2.7
        WHEN k.grade = 'C+' THEN 2.3
        WHEN k.grade = 'C' THEN 2.0
        WHEN k.grade = 'D' THEN 1.0
        ELSE 0.0
      END) as ipk_semester,
      'selesai' as status
    FROM krs k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.mahasiswa_id = ? AND k.grade IS NOT NULL
    GROUP BY m.semester
    ORDER BY m.semester ASC
  ", [$mahasiswaId], 'i');
}

/**
 * Get Total SKS Completed
 */
function getTotalSKSCompleted($conn, $mahasiswaId) {
  $result = fetchOne($conn, "
    SELECT SUM(m.sks) as total_sks
    FROM krs k
    JOIN matakuliah m ON k.matakuliah_id = m.id
    WHERE k.mahasiswa_id = ? AND k.grade IS NOT NULL
  ", [$mahasiswaId], 'i');
  
  return $result['total_sks'] ?? 0;
}

/**
 * Get Grade Distribution
 */
function getGradeDistribution($conn, $mahasiswaId) {
  $result = fetchAll($conn, "
    SELECT grade, COUNT(*) as count
    FROM krs
    WHERE mahasiswa_id = ? AND grade IS NOT NULL
    GROUP BY grade
  ", [$mahasiswaId], 'i');
  
  $distribution = [];
  foreach ($result as $r) {
    $distribution[$r['grade']] = $r['count'];
  }
  return $distribution;
}

/**
 * Get All Users
 */
function getAllUsers($conn) {
  return fetchAll($conn, "
    SELECT * FROM users ORDER BY created_at DESC
  ");
}
