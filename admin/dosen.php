<?php
/**
 * ECO-LEARNING - Administrator Dosen (Lecturer) CRUD Management
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

$msg = '';
$err = '';

$editing = false;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$nip = '';
$nama_lengkap = '';
$email = '';
$username = '';
$status = 'aktif';

// Load data for edit mode
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT d.id AS dosen_id, d.user_id, d.nip, u.username, u.nama_lengkap, u.email, u.status
                            FROM dosen d JOIN users u ON d.user_id = u.id WHERE d.id = :id LIMIT 1");
    $stmt->execute(['id' => $editId]);
    $rowEdit = $stmt->fetch();
    if ($rowEdit) {
        $editing = true;
        $nip = $rowEdit['nip'];
        $nama_lengkap = $rowEdit['nama_lengkap'];
        $email = $rowEdit['email'];
        $username = $rowEdit['username'];
        $status = $rowEdit['status'];
    } else {
        $err = 'Data dosen yang akan diedit tidak ditemukan.';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $delId = (int)($_POST['dosen_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM dosen WHERE id = :id");
        $stmt->execute(['id' => $delId]);
        $target = $stmt->fetch();
        if ($target) {
            $del = $pdo->prepare("DELETE FROM users WHERE id = :uid");
            $del->execute(['uid' => $target['user_id']]);
            $msg = 'Data dosen berhasil dihapus.';
        } else {
            $err = 'Data dosen tidak ditemukan.';
        }
    } catch (PDOException $e) {
        $err = 'Gagal menghapus dosen: ' . $e->getMessage();
    }
    header('Location: dosen.php');
    exit();
}

// Handle create / update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'])) {
    $action = $_POST['action'];
    $nip_post = trim($_POST['nip'] ?? '');
    $username_post = trim($_POST['username'] ?? '');
    $nama_post = trim($_POST['nama_lengkap'] ?? '');
    $email_post = trim($_POST['email'] ?? '');
    $status_post = $_POST['status'] ?? 'aktif';
    $password_post = trim($_POST['password'] ?? '');

    if ($nip_post === '' || $username_post === '' || $nama_post === '' || $email_post === '') {
        $err = 'Semua field wajib diisi.';
    } elseif (!filter_var($email_post, FILTER_VALIDATE_EMAIL)) {
        $err = 'Format email tidak valid.';
    } else {
        try {
            if ($action === 'update') {
                $dosenId = (int)($_POST['dosen_id'] ?? 0);
                $stmtCur = $pdo->prepare("SELECT user_id FROM dosen WHERE id = :id");
                $stmtCur->execute(['id' => $dosenId]);
                $cur = $stmtCur->fetch();
                if (!$cur) {
                    throw new Exception('Dosen tidak ditemukan.');
                }
                if ($password_post !== '') {
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n, email = :e, status = :s, password = :p WHERE id = :id");
                    $stmtU->execute(['u' => $username_post, 'n' => $nama_post, 'e' => $email_post, 's' => $status_post, 'p' => $password_post, 'id' => $cur['user_id']]);
                } else {
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n, email = :e, status = :s WHERE id = :id");
                    $stmtU->execute(['u' => $username_post, 'n' => $nama_post, 'e' => $email_post, 's' => $status_post, 'id' => $cur['user_id']]);
                }
                $stmtD = $pdo->prepare("UPDATE dosen SET nip = :nip WHERE id = :id");
                $stmtD->execute(['nip' => $nip_post, 'id' => $dosenId]);
                $msg = 'Data dosen berhasil diperbarui.';
            } else {
                $pass = $password_post !== '' ? $password_post : $nip_post;
                $pdo->beginTransaction();
                $stmtU = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, email, role, status) VALUES (:u, :p, :n, :e, 'dosen', :s)");
                $stmtU->execute(['u' => $username_post, 'p' => $pass, 'n' => $nama_post, 'e' => $email_post, 's' => $status_post]);
                $newUserId = $pdo->lastInsertId();
                $stmtD = $pdo->prepare("INSERT INTO dosen (user_id, nip) VALUES (:uid, :nip)");
                $stmtD->execute(['uid' => $newUserId, 'nip' => $nip_post]);
                $pdo->commit();
                $msg = 'Dosen baru berhasil ditambahkan.';
            }
            header('Location: dosen.php');
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $err = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }

    $nip = $nip_post;
    $username = $username_post;
    $nama_lengkap = $nama_post;
    $email = $email_post;
    $status = $status_post;
}

$rows = $pdo->query("SELECT d.id AS dosen_id, d.nip, u.username, u.nama_lengkap, u.email, u.status FROM dosen d JOIN users u ON d.user_id = u.id ORDER BY u.nama_lengkap")->fetchAll();
$formTitle = $editing ? 'Edit Dosen' : 'Tambah Dosen';
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dosen</title><link rel="stylesheet" href="../assets/css/app.css"><script src="../assets/js/app.js" defer></script></head>
<body><?php include __DIR__ . '/../components/sidebar.php'; ?><div class="main-content"><?php include __DIR__ . '/../components/header.php'; ?><main class="page-container">
<div class="page-header"><div><h1 class="page-title">Dosen</h1><p class="page-subtitle">Lecturer master data.</p></div></div>

<?php if (!empty($msg)): ?><div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if (!empty($err)): ?><div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="two-col-grid">
    <div class="card">
        <h3 class="card-title"><?= $formTitle ?></h3>
        <form class="form-stack" method="POST" action="">
            <?php if ($editing): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="dosen_id" value="<?= (int)$editId ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>

            <div><label class="form-label">NIP</label>
                <input class="form-input" name="nip" value="<?= htmlspecialchars($nip) ?>" required></div>
            <div><label class="form-label">Username</label>
                <input class="form-input" name="username" value="<?= htmlspecialchars($username) ?>" required></div>
            <div><label class="form-label">Nama Lengkap</label>
                <input class="form-input" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap) ?>" required></div>
            <div><label class="form-label">Email</label>
                <input class="form-input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></div>
            <div><label class="form-label">Status</label>
                <select class="form-input" name="status">
                    <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select></div>
            <div><label class="form-label">Password <?= $editing ? '(kosongkan jika tidak diubah)' : '' ?></label>
                <input class="form-input" type="password" name="password" placeholder="<?= $editing ? 'Kosongkan jika tidak diubah' : 'Default: NIP' ?>"></div>

            <button class="btn-primary" type="submit">Save</button>
            <?php if ($editing): ?><a class="btn-secondary" href="dosen.php">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Daftar Dosen</h3>
        <div class="table-wrap"><table><thead><tr><th>NIP</th><th>Nama</th><th>Email</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nip']) ?></td>
                <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><span class="badge badge-emerald"><?= htmlspecialchars($r['status']) ?></span></td>
                <td>
                    <a class="btn-secondary btn-sm" href="?edit=<?= (int)$r['dosen_id'] ?>">Edit</a>
                    <form method="POST" action="" style="display:inline" onsubmit="return confirm('Hapus dosen ini beserta akunnya?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="dosen_id" value="<?= (int)$r['dosen_id'] ?>">
                        <button class="btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </div>
</div>
</main></div></body></html>
