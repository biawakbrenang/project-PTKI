<?php
/**
 * ECO-LEARNING - Administrator Mahasiswa (Student) CRUD Management
 */
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

$msg = '';
$err = '';

$editing = false;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$nim = '';
$username = '';
$nama_lengkap = '';
$email = '';
$jurusan_id = '21';
$semester_saat_ini = '1';
$status = 'aktif';

if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT m.id AS mhs_id, m.user_id, m.nim, m.jurusan_id, m.semester_saat_ini, u.username, u.nama_lengkap, u.email, u.status
                            FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE m.id = :id LIMIT 1");
    $stmt->execute(['id' => $editId]);
    $rowEdit = $stmt->fetch();
    if ($rowEdit) {
        $editing = true;
        $nim = $rowEdit['nim'];
        $username = $rowEdit['username'];
        $nama_lengkap = $rowEdit['nama_lengkap'];
        $email = $rowEdit['email'];
        $jurusan_id = $rowEdit['jurusan_id'];
        $semester_saat_ini = $rowEdit['semester_saat_ini'];
        $status = $rowEdit['status'];
    } else {
        $err = 'Data mahasiswa yang akan diedit tidak ditemukan.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $delId = (int)($_POST['mhs_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM mahasiswa WHERE id = :id");
        $stmt->execute(['id' => $delId]);
        $target = $stmt->fetch();
        if ($target) {
            $del = $pdo->prepare("DELETE FROM users WHERE id = :uid");
            $del->execute(['uid' => $target['user_id']]);
            $msg = 'Data mahasiswa berhasil dihapus.';
        } else {
            $err = 'Data mahasiswa tidak ditemukan.';
        }
    } catch (PDOException $e) {
        $err = 'Gagal menghapus mahasiswa: ' . $e->getMessage();
    }
    header('Location: mahasiswa.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'])) {
    $action = $_POST['action'];
    $nim_post = trim($_POST['nim'] ?? '');
    $username_post = trim($_POST['username'] ?? '');
    $nama_post = trim($_POST['nama_lengkap'] ?? '');
    $email_post = trim($_POST['email'] ?? '');
    $jurusan_post = (int)($_POST['jurusan_id'] ?? 21);
    $semester_post = (int)($_POST['semester_saat_ini'] ?? 1);
    $status_post = $_POST['status'] ?? 'aktif';
    $password_post = trim($_POST['password'] ?? '');

    if ($nim_post === '' || $username_post === '' || $nama_post === '' || $email_post === '') {
        $err = 'Semua field wajib diisi.';
    } elseif (!filter_var($email_post, FILTER_VALIDATE_EMAIL)) {
        $err = 'Format email tidak valid.';
    } elseif ($semester_post < 1 || $semester_post > 14) {
        $err = 'Semester tidak valid.';
    } else {
        try {
            if ($action === 'update') {
                $mhsId = (int)($_POST['mhs_id'] ?? 0);
                $stmtCur = $pdo->prepare("SELECT user_id FROM mahasiswa WHERE id = :id");
                $stmtCur->execute(['id' => $mhsId]);
                $cur = $stmtCur->fetch();
                if (!$cur) {
                    throw new Exception('Mahasiswa tidak ditemukan.');
                }
                if ($password_post !== '') {
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n, email = :e, status = :s, password = :p WHERE id = :id");
                    $stmtU->execute(['u' => $username_post, 'n' => $nama_post, 'e' => $email_post, 's' => $status_post, 'p' => $password_post, 'id' => $cur['user_id']]);
                } else {
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n, email = :e, status = :s WHERE id = :id");
                    $stmtU->execute(['u' => $username_post, 'n' => $nama_post, 'e' => $email_post, 's' => $status_post, 'id' => $cur['user_id']]);
                }
                $stmtM = $pdo->prepare("UPDATE mahasiswa SET nim = :nim, jurusan_id = :jur, semester_saat_ini = :sem WHERE id = :id");
                $stmtM->execute(['nim' => $nim_post, 'jur' => $jurusan_post, 'sem' => $semester_post, 'id' => $mhsId]);
                $msg = 'Data mahasiswa berhasil diperbarui.';
            } else {
                $pass = $password_post !== '' ? $password_post : $nim_post;
                $pdo->beginTransaction();
                $stmtU = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, email, role, status) VALUES (:u, :p, :n, :e, 'mahasiswa', :s)");
                $stmtU->execute(['u' => $username_post, 'p' => $pass, 'n' => $nama_post, 'e' => $email_post, 's' => $status_post]);
                $newUserId = $pdo->lastInsertId();
                $stmtM = $pdo->prepare("INSERT INTO mahasiswa (user_id, jurusan_id, nim, semester_saat_ini) VALUES (:uid, :jur, :nim, :sem)");
                $stmtM->execute(['uid' => $newUserId, 'jur' => $jurusan_post, 'nim' => $nim_post, 'sem' => $semester_post]);
                $pdo->commit();
                $msg = 'Mahasiswa baru berhasil ditambahkan.';
            }
            header('Location: mahasiswa.php');
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $err = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }

    $nim = $nim_post;
    $username = $username_post;
    $nama_lengkap = $nama_post;
    $email = $email_post;
    $jurusan_id = $jurusan_post;
    $semester_saat_ini = $semester_post;
    $status = $status_post;
}

$rows = $pdo->query("SELECT m.id AS mhs_id, m.nim, m.jurusan_id, m.semester_saat_ini, u.nama_lengkap, u.email, u.status FROM mahasiswa m JOIN users u ON m.user_id = u.id ORDER BY m.nim LIMIT 300")->fetchAll();
$formTitle = $editing ? 'Edit Mahasiswa' : 'Tambah Mahasiswa';
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mahasiswa</title><link rel="stylesheet" href="../assets/css/app.css"><script src="../assets/js/app.js" defer></script></head>
<body><?php include __DIR__ . '/../components/sidebar.php'; ?><div class="main-content"><?php include __DIR__ . '/../components/header.php'; ?><main class="page-container">
<div class="page-header"><div><h1 class="page-title">Mahasiswa</h1><p class="page-subtitle">Student master data and academic status.</p></div></div>

<?php if (!empty($msg)): ?><div class="alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if (!empty($err)): ?><div class="alert-danger">⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="two-col-grid">
    <div class="card">
        <h3 class="card-title"><?= $formTitle ?></h3>
        <form class="form-stack" method="POST" action="">
            <?php if ($editing): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="mhs_id" value="<?= (int)$editId ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>

            <div><label class="form-label">NIM</label>
                <input class="form-input" name="nim" value="<?= htmlspecialchars($nim) ?>" required></div>
            <div><label class="form-label">Username</label>
                <input class="form-input" name="username" value="<?= htmlspecialchars($username) ?>" required></div>
            <div><label class="form-label">Nama Lengkap</label>
                <input class="form-input" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap) ?>" required></div>
            <div><label class="form-label">Email</label>
                <input class="form-input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></div>
            <div><label class="form-label">Jurusan</label>
                <select class="form-input" name="jurusan_id">
                    <option value="21" <?= (int)$jurusan_id === 21 ? 'selected' : '' ?>>Sistem Informasi</option>
                    <option value="22" <?= (int)$jurusan_id === 22 ? 'selected' : '' ?>>Teknik Informatika</option>
                </select></div>
            <div><label class="form-label">Semester Saat Ini</label>
                <input class="form-input" type="number" min="1" max="14" name="semester_saat_ini" value="<?= htmlspecialchars((string)$semester_saat_ini) ?>" required></div>
            <div><label class="form-label">Status</label>
                <select class="form-input" name="status">
                    <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select></div>
            <div><label class="form-label">Password <?= $editing ? '(kosongkan jika tidak diubah)' : '' ?></label>
                <input class="form-input" type="password" name="password" placeholder="<?= $editing ? 'Kosongkan jika tidak diubah' : 'Default: NIM' ?>"></div>

            <button class="btn-primary" type="submit">Save</button>
            <?php if ($editing): ?><a class="btn-secondary" href="mahasiswa.php">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Daftar Mahasiswa</h3>
        <div class="table-wrap"><table><thead><tr><th>NIM</th><th>Nama</th><th>Jurusan</th><th>Semester</th><th>Email</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nim']) ?></td>
                <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                <td><?= (int)$r['jurusan_id'] === 21 ? 'Sistem Informasi' : 'Teknik Informatika' ?></td>
                <td><?= (int)$r['semester_saat_ini'] ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><span class="badge badge-emerald"><?= htmlspecialchars($r['status']) ?></span></td>
                <td>
                    <a class="btn-secondary btn-sm" href="?edit=<?= (int)$r['mhs_id'] ?>">Edit</a>
                    <form method="POST" action="" style="display:inline" onsubmit="return confirm('Hapus mahasiswa ini beserta akunnya?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="mhs_id" value="<?= (int)$r['mhs_id'] ?>">
                        <button class="btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </div>
</div>
</main></div></body></html>
