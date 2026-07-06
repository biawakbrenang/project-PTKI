<?php
class Auth {
    private $conn;
    private $table_name = "dosen";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT id_dosen, nidn, nama_lengkap, email, password, foto_profil FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id_dosen'];
                $_SESSION['nidn'] = $row['nidn'];
                $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['foto_profil'] = $row['foto_profil'];
                return true;
            }
        }
        return false;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>
