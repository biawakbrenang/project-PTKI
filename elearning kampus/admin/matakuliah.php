<?php
require_once __DIR__ . '/../auth/check_auth.php';
restrictTo(['admin']);

// Ambil data untuk list
$courses = $pdo->query("SELECT * FROM matakuliah ORDER BY kode_matkul")->fetchAll();

// Mode & data form (tambah / edit)
$editing = false;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Field yang mungkin dipakai tabel
// Asumsi kolom: id (atau bisa saja tidak ada). Jika tidak ada, akan tetap aman karena update tidak berjalan.
$kode_matkul = '';
$nama_matkul = '';
$sks = '';

// Pesan UI
$success = '';
$error = '';

// Jika edit: load data
if ($editId > 0) {
    // PK tabel matakuliah adalah `id`
    $stmt = $pdo->prepare("SELECT * FROM matakuliah WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $editId]);
    $courseEdit = $stmt->fetch();

    if ($courseEdit) {
        $editing = true;
        $kode_matkul = $courseEdit['kode_matkul'] ?? '';
        $nama_matkul = $courseEdit['nama_matkul'] ?? '';
        $sks = $courseEdit['sks'] ?? '';
    } else {
        $error = 'Data yang akan diedit tidak ditemukan.';
    }
}

// Handler delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM matakuliah WHERE id = :id");
        $stmt->execute(['id' => $delId]);
        $success = 'Mata kuliah berhasil dihapus.';
    } catch (PDOException $e) {
        $error = 'Gagal menghapus (mungkin masih dipakai oleh kelas): ' . $e->getMessage();
    }
    if (empty($error)) {
        header('Location: matakuliah.php');
        exit();
    }
}

// Handler submit (tambah / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'])) {
    $action = $_POST['action'] ?? 'create';
    $kode_matkul_post = trim($_POST['kode_matkul'] ?? '');
    $nama_matkul_post = trim($_POST['nama_matkul'] ?? '');
    $sks_post = trim($_POST['sks'] ?? '');

    if ($kode_matkul_post === '' || $nama_matkul_post === '' || $sks_post === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9\-_.]+$/', $kode_matkul_post)) {
        $error = 'Kode mata kuliah tidak valid.';
    } elseif (!ctype_digit((string)$sks_post) || (int)$sks_post <= 0) {
        $error = 'SKS harus angka positif.';
    } else {
        try {
            if ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new Exception('ID tidak valid untuk update.');
                }

                $stmt = $pdo->prepare("UPDATE matakuliah SET kode_matkul = :kode_matkul, nama_matkul = :nama_matkul, sks = :sks WHERE id = :id");
                $stmt->execute([
                    'kode_matkul' => $kode_matkul_post,
                    'nama_matkul' => $nama_matkul_post,
                    'sks' => (int)$sks_post,
                    'id' => $id
                ]);

                $success = 'Mata kuliah berhasil diperbarui.';
                header('Location: matakuliah.php');
                exit();
            } else {
                // Tambah
                $stmt = $pdo->prepare("INSERT INTO matakuliah (kode_matkul, nama_matkul, sks) VALUES (:kode_matkul, :nama_matkul, :sks)");
                $stmt->execute([
                    'kode_matkul' => $kode_matkul_post,
                    'nama_matkul' => $nama_matkul_post,
                    'sks' => (int)$sks_post
                ]);

                $success = 'Mata kuliah berhasil ditambahkan.';
                header('Location: matakuliah.php');
                exit();
            }
        } catch (Exception $e) {
            $error = 'Gagal menyimpan: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Jika gagal, tampilkan kembali input
    $kode_matkul = $kode_matkul_post;
    $nama_matkul = $nama_matkul_post;
    $sks = $sks_post;
}

// Default button text
$formTitle = $editing ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah';

?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mata Kuliah</title><link rel="stylesheet" href="../assets/css/app.css"><script src="../assets/js/app.js" defer></script></head>
<body><?php include __DIR__ . '/../components/sidebar.php'; ?><div class="main-content"><?php include __DIR__ . '/../components/header.php'; ?><main class="page-container">
<div class="page-header"><div><h1 class="page-title">Mata Kuliah</h1><p class="page-subtitle">Course catalogue management.</p></div><a class="btn-primary" href="matakuliah.php">Tambah Mata Kuliah</a></div>
<div class="two-col-grid">
    <div class="card">
        <h3 class="card-title"><?= $formTitle ?></h3>
        <?php if (!empty($success)): ?><div class="alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form class="form-stack" method="POST" action="">
            <?php if ($editing): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$editId ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>

            <div><label class="form-label">Kode Mata Kuliah</label>
                <input class="form-input" name="kode_matkul" value="<?= htmlspecialchars($kode_matkul) ?>" required></div>
            <div><label class="form-label">Nama Mata Kuliah</label>
                <input class="form-input" name="nama_matkul" value="<?= htmlspecialchars($nama_matkul) ?>" required></div>
            <div><label class="form-label">SKS</label>
                <input class="form-input" name="sks" type="number" min="1" value="<?= htmlspecialchars((string)$sks) ?>" required></div>

            <button class="btn-primary" type="submit">Save</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Course List</h3>
        <div class="table-wrap"><table><thead><tr><th>Kode Mata Kuliah</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['kode_matkul']) ?></td>
                    <td><?= htmlspecialchars($c['nama_matkul']) ?></td>
                    <td><?= htmlspecialchars($c['sks']) ?></td>
                    <td>
                        <a class="btn-secondary btn-sm" href="?edit=<?= (int)($c['id'] ?? 0) ?>">Edit</a>
                        <form method="POST" action="" style="display:inline" onsubmit="return confirm('Hapus mata kuliah ini?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
                            <button class="btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody></table></div>
    </div>
</div>
</main></div></body></html>

