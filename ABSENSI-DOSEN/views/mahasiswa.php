<?php
$keyword = trim($_GET['q'] ?? '');
$editId = $_GET['edit'] ?? '';
$editing = null;

if (isset($_POST['save_student'])) {
    $data = [
        'npm' => trim($_POST['npm'] ?? ''),
        'nama_mahasiswa' => trim($_POST['nama_mahasiswa'] ?? ''),
        'program_studi' => trim($_POST['program_studi'] ?? ''),
        'angkatan' => trim($_POST['angkatan'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'tahun_ajaran' => trim($_POST['tahun_ajaran'] ?? date('Y') . '/' . ((int) date('Y') + 1)),
    ];
    $id_matkul = $_POST['id_matkul'] ?? '';
    $id_mahasiswa = $_POST['id_mahasiswa'] ?? '';

    try {
        if ($id_mahasiswa) {
            if (!$studentModel->find($id_mahasiswa, $_SESSION['user_id'])) {
                throw new Exception('Data mahasiswa tidak ditemukan.');
            }
            $studentModel->update($id_mahasiswa, $data);
            set_flash('success', 'Data mahasiswa berhasil diperbarui.');
        } else {
            if (!$id_matkul) {
                throw new Exception('Pilih mata kuliah untuk menambahkan mahasiswa.');
            }
            $studentModel->create($data, $id_matkul);
            set_flash('success', 'Mahasiswa berhasil ditambahkan.');
        }
    } catch (Exception $e) {
        set_flash('error', 'Gagal menyimpan data. Pastikan NPM/email belum digunakan dan isian sudah lengkap.');
    }

    redirect('index.php?page=mahasiswa');
}

if (isset($_POST['delete_student'])) {
    $id_mahasiswa = $_POST['id_mahasiswa'] ?? '';
    try {
        if (!$studentModel->find($id_mahasiswa, $_SESSION['user_id'])) {
            throw new Exception('Data mahasiswa tidak ditemukan.');
        }
        $studentModel->deleteForLecturer($id_mahasiswa, $_SESSION['user_id']);
        set_flash('success', 'Mahasiswa berhasil dihapus dari kelas Anda.');
    } catch (Exception $e) {
        set_flash('error', 'Gagal menghapus mahasiswa.');
    }
    redirect('index.php?page=mahasiswa');
}

if ($editId) {
    $editing = $studentModel->find($editId, $_SESSION['user_id']);
    if (!$editing) {
        set_flash('error', 'Data mahasiswa tidak ditemukan.');
        redirect('index.php?page=mahasiswa');
    }
}

$students = $studentModel->getByLecturer($_SESSION['user_id'], $keyword);

include 'header.php';
?>

<section class="grid gap-6 xl:grid-cols-[0.9fr_1.4fr]">
    <div class="panel h-fit">
        <div class="panel-header">
            <div>
                <h2 class="panel-title"><?= $editing ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>
                <p class="panel-subtitle">Mahasiswa baru otomatis masuk ke mata kuliah yang dipilih.</p>
            </div>
        </div>

        <form method="POST" class="space-y-4">
            <?php if ($editing): ?>
                <input type="hidden" name="id_mahasiswa" value="<?= e($editing['id_mahasiswa']) ?>">
            <?php endif; ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">NPM</label>
                    <input class="form-input" type="text" name="npm" required value="<?= e($editing['npm'] ?? '') ?>" placeholder="2021001">
                </div>
                <div>
                    <label class="form-label">Angkatan</label>
                    <input class="form-input" type="number" name="angkatan" required min="2000" max="2099" value="<?= e($editing['angkatan'] ?? date('Y')) ?>">
                </div>
            </div>
            <div>
                <label class="form-label">Nama Mahasiswa</label>
                <input class="form-input" type="text" name="nama_mahasiswa" required value="<?= e($editing['nama_mahasiswa'] ?? '') ?>" placeholder="Nama lengkap">
            </div>
            <div>
                <label class="form-label">Program Studi</label>
                <input class="form-input" type="text" name="program_studi" required value="<?= e($editing['program_studi'] ?? '') ?>" placeholder="Teknik Informatika">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input class="form-input" type="email" name="email" value="<?= e($editing['email'] ?? '') ?>" placeholder="nama@student.univ.ac.id">
            </div>

            <?php if (!$editing): ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Mata Kuliah</label>
                        <select class="form-input" name="id_matkul" required>
                            <option value="">Pilih mata kuliah</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= e($course['id_matkul']) ?>"><?= e($course['kode_matkul']) ?> - <?= e($course['nama_matkul']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tahun Ajaran</label>
                        <input class="form-input" type="text" name="tahun_ajaran" required value="<?= e(date('Y') . '/' . ((int) date('Y') + 1)) ?>">
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button class="btn-primary flex-1" type="submit" name="save_student">
                    <i class="fas fa-floppy-disk"></i>
                    Simpan
                </button>
                <?php if ($editing): ?>
                    <a class="btn-muted justify-center" href="index.php?page=mahasiswa">
                        <i class="fas fa-xmark"></i>
                        Batal
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        <div class="panel">
            <form method="GET" action="index.php" class="flex flex-col gap-3 sm:flex-row">
                <input type="hidden" name="page" value="mahasiswa">
                <div class="form-icon-wrap flex-1">
                    <i class="fas fa-search"></i>
                    <input class="form-input pl-11" type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari NPM, nama, atau program studi">
                </div>
                <button class="btn-muted justify-center" type="submit">
                    <i class="fas fa-magnifying-glass"></i>
                    Cari
                </button>
            </form>
        </div>

        <div class="panel overflow-hidden p-0">
            <div class="border-b border-slate-100 p-5">
                <h2 class="panel-title">Daftar Mahasiswa</h2>
                <p class="panel-subtitle"><?= count($students) ?> mahasiswa ditemukan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar"><?= e(substr($student['nama_mahasiswa'], 0, 1)) ?></div>
                                        <div>
                                            <p class="font-bold"><?= e($student['nama_mahasiswa']) ?></p>
                                            <p class="text-xs text-slate-500"><?= e($student['npm']) ?><?= $student['email'] ? ' - ' . e($student['email']) : '' ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-medium text-slate-600"><?= e($student['program_studi']) ?></td>
                                <td><?= e($student['angkatan']) ?></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a class="icon-button text-blue-600 hover:bg-blue-50" href="index.php?page=mahasiswa&edit=<?= e($student['id_mahasiswa']) ?>" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" data-confirm="Hapus mahasiswa ini dari kelas Anda?">
                                            <input type="hidden" name="id_mahasiswa" value="<?= e($student['id_mahasiswa']) ?>">
                                            <button class="icon-button text-rose-600 hover:bg-rose-50" type="submit" name="delete_student" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($students)): ?>
                <div class="empty-state p-10">
                    <i class="fas fa-user-graduate"></i>
                    <p>Belum ada mahasiswa yang cocok.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
