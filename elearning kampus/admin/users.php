<?php
/**
 * ECO-LEARNING - Administrator User Account CRUD Management
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

$msg = '';
$err = '';

$editing = false;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$username = '';
$nama_lengkap = '';
$email = '';
$role = 'admin';
$status = 'aktif';

if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $editId]);
    $rowEdit = $stmt->fetch();
    if ($rowEdit) {
        $editing = true;
        $username = $rowEdit['username'];
        $nama_lengkap = $rowEdit['nama_lengkap'];
        $email = $rowEdit['email'];
        $role = $rowEdit['role'];
        $status = $rowEdit['status'];
    } else {
        $err = 'User yang akan diedit tidak ditemukan.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $delId = (int)($_POST['user_id'] ?? 0);
    if ($delId === (int)($_SESSION['user_id'] ?? 0)) {
        $err = 'Tidak dapat menghapus akun Anda sendiri.';
    } else {
        try {
            $del = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $del->execute(['id' => $delId]);
            $msg = 'User berhasil dihapus.';
        } catch (PDOException $e) {
            $err = 'Gagal menghapus user: ' . $e->getMessage();
        }
    }
    if (empty($err)) {
        header('Location: users.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'])) {
    $action = $_POST['action'];
    $username_post = trim($_POST['username'] ?? '');
    $nama_post = trim($_POST['nama_lengkap'] ?? '');
    $email_post = trim($_POST['email'] ?? '');
    $role_post = $_POST['role'] ?? 'admin';
    $status_post = $_POST['status'] ?? 'aktif';
    $password_post = trim($_POST['password'] ?? '');
    $allowedRoles = ['admin', 'dosen', 'mahasiswa'];

    if ($username_post === '' || $nama_post === '' || $email_post === '') {
        $err = 'Semua field wajib diisi.';
    } elseif (!filter_var($email_post, FILTER_VALIDATE_EMAIL)) {
        $err = 'Format email tidak valid.';
    } elseif (!in_array($role_post, $allowedRoles)) {
        $err = 'Role tidak valid.';
    } elseif ($action === 'create' && $password_post === '') {
        $err = 'Password wajib diisi untuk user baru.';
    } else {
        try {
            if ($action === 'update') {
                $userId = (int)($_POST['user_id'] ?? 0);
                if ($password_post !== '') {
                    $stmt = $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n, email = :e, role = :r, status = :s, password = :p WHERE id = :id");
                    $stmt->execute(['u' => $username_post, 'n' => $nama_post, 'e' => $email_post, 'r' => $role_post, 's' => $status_post, 'p' => $password_post, 'id' => $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n, email = :e, role = :r, status = :s WHERE id = :id");
                    $stmt->execute(['u' => $username_post, 'n' => $nama_post, 'e' => $email_post, 'r' => $role_post, 's' => $status_post, 'id' => $userId]);
                }
                $msg = 'User berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, email, role, status) VALUES (:u, :p, :n, :e, :r, :s)");
                $stmt->execute(['u' => $username_post, 'p' => $password_post, 'n' => $nama_post, 'e' => $email_post, 'r' => $role_post, 's' => $status_post]);
                $msg = 'User baru berhasil ditambahkan.';
            }
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $err = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }

    $username = $username_post;
    $nama_lengkap = $nama_post;
    $email = $email_post;
    $role = $role_post;
    $status = $status_post;
}

$users = $pdo->query("SELECT id, username, nama_lengkap, role, email, status FROM users ORDER BY id ASC")->fetchAll();
$formTitle = $editing ? 'Edit User' : 'Tambah User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../components/header.php'; ?>
<main class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Manage active Admin, Dosen, and Mahasiswa accounts.</p>
        </div>
    </div>

    <?php if (!empty($msg)): ?><div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if (!empty($err)): ?><div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="two-col-grid">
        <div class="card">
            <h3 class="card-title"><?= $formTitle ?></h3>
            <form class="form-stack" method="POST" action="">
                <?php if ($editing): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?= (int)$editId ?>">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>

                <div><label class="form-label">Username</label>
                    <input class="form-input" name="username" value="<?= htmlspecialchars($username) ?>" required></div>
                <div><label class="form-label">Nama Lengkap</label>
                    <input class="form-input" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap) ?>" required></div>
                <div><label class="form-label">Email</label>
                    <input class="form-input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></div>
                <div><label class="form-label">Role</label>
                    <select class="form-input" name="role">
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="dosen" <?= $role === 'dosen' ? 'selected' : '' ?>>Dosen</option>
                        <option value="mahasiswa" <?= $role === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                    </select></div>
                <div><label class="form-label">Status</label>
                    <select class="form-input" name="status">
                        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select></div>
                <div><label class="form-label">Password <?= $editing ? '(kosongkan jika tidak diubah)' : '' ?></label>
                    <input class="form-input" type="password" name="password" <?= $editing ? '' : 'required' ?>></div>

                <button class="btn-primary" type="submit">Save</button>
                <?php if ($editing): ?><a class="btn-secondary" href="users.php">Batal</a><?php endif; ?>
            </form>
            <p style="font-size:11px;color:#94a3b8;margin-top:8px;">Catatan: menambah user dosen/mahasiswa lewat sini tidak otomatis membuat data NIP/NIM. Gunakan halaman Dosen/Mahasiswa untuk itu.</p>
        </div>

        <div class="card">
            <h3 class="card-title">Account List</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
                            <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($u['role']) ?></span></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['status']) ?></td>
                            <td>
                                <a class="btn-secondary btn-sm" href="?edit=<?= (int)$u['id'] ?>">Edit</a>
                                <form method="POST" action="" style="display:inline" onsubmit="return confirm('Hapus user ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button class="btn-danger btn-sm" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</div>
</body>
</html>
