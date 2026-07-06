<?php
$selected_matkul = $_GET['id_matkul'] ?? '';
$selected_jadwal = $_GET['id_jadwal'] ?? '';
$selectedCourse = null;
$selectedSchedule = null;
$schedules = [];
$students = [];
$attendanceRows = [];
$validStatuses = ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa'];

if ($selected_matkul) {
    $selectedCourse = $attendance->getCourseByDosen($selected_matkul, $_SESSION['user_id']);
    if (!$selectedCourse) {
        set_flash('error', 'Mata kuliah tidak ditemukan atau bukan milik akun Anda.');
        redirect('index.php?page=absensi');
    }
    $schedules = $attendance->getSchedulesByCourse($selected_matkul);
}

if ($selected_jadwal && $selected_matkul) {
    $selectedSchedule = $attendance->getScheduleByCourse($selected_jadwal, $selected_matkul);
    if (!$selectedSchedule) {
        set_flash('error', 'Jadwal tidak sesuai dengan mata kuliah yang dipilih.');
        redirect('index.php?page=absensi&id_matkul=' . urlencode($selected_matkul));
    }
    $students = $attendance->getStudentsByCourse($selected_matkul);
    $attendanceRows = $attendance->getAttendanceBySchedule($selected_jadwal);
}

if (isset($_POST['save_absensi'])) {
    if (!$selected_jadwal || !$selected_matkul) {
        set_flash('error', 'Pilih mata kuliah dan jadwal terlebih dahulu.');
        redirect('index.php?page=absensi');
    }

    $statuses = $_POST['status'] ?? [];
    foreach ($statuses as $id_mhs => $status) {
        if (!in_array($status, $validStatuses, true)) {
            continue;
        }
        $ket = trim($_POST['keterangan'][$id_mhs] ?? '');
        $attendance->saveAttendance($id_mhs, $selected_jadwal, $status, $ket);
    }

    set_flash('success', 'Absensi berhasil disimpan.');
    redirect('index.php?page=absensi&id_matkul=' . urlencode($selected_matkul) . '&id_jadwal=' . urlencode($selected_jadwal));
}

include 'header.php';
?>

<section class="space-y-6">
    <div class="panel">
        <form method="GET" action="index.php" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
            <input type="hidden" name="page" value="absensi">
            <div>
                <label class="form-label">Mata Kuliah</label>
                <select name="id_matkul" class="form-input" data-auto-submit>
                    <option value="">Pilih mata kuliah</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e($course['id_matkul']) ?>" <?= (string) $selected_matkul === (string) $course['id_matkul'] ? 'selected' : '' ?>>
                            <?= e($course['kode_matkul']) ?> - <?= e($course['nama_matkul']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Pertemuan</label>
                <select name="id_jadwal" class="form-input" data-auto-submit <?= empty($schedules) ? 'disabled' : '' ?>>
                    <option value="">Pilih jadwal</option>
                    <?php foreach ($schedules as $schedule): ?>
                        <option value="<?= e($schedule['id_jadwal']) ?>" <?= (string) $selected_jadwal === (string) $schedule['id_jadwal'] ? 'selected' : '' ?>>
                            Pertemuan <?= e($schedule['pertemuan_ke']) ?> - <?= e(date('d M Y', strtotime($schedule['tanggal_pertemuan']))) ?>, <?= e(substr($schedule['jam_mulai'], 0, 5)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="index.php?page=absensi" class="btn-muted justify-center">
                <i class="fas fa-rotate-left"></i>
                Reset
            </a>
        </form>
    </div>

    <?php if ($selectedCourse && $selectedSchedule): ?>
        <div class="grid gap-4 sm:grid-cols-3">
            <article class="mini-card">
                <span>Mata kuliah</span>
                <strong><?= e($selectedCourse['nama_matkul']) ?></strong>
            </article>
            <article class="mini-card">
                <span>Pertemuan</span>
                <strong><?= e($selectedSchedule['pertemuan_ke']) ?></strong>
            </article>
            <article class="mini-card">
                <span>Ruangan</span>
                <strong><?= e($selectedSchedule['ruangan']) ?></strong>
            </article>
        </div>
    <?php endif; ?>

    <?php if ($selected_jadwal && !empty($students)): ?>
        <form method="POST" class="panel overflow-hidden p-0">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="panel-title">Daftar Kehadiran</h2>
                    <p class="panel-subtitle"><?= count($students) ?> mahasiswa terdaftar.</p>
                </div>
                <button type="button" class="btn-muted" data-mark-all="Hadir">
                    <i class="fas fa-check-double"></i>
                    Tandai Hadir Semua
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th class="text-center">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php
                                $current = $attendanceRows[$student['id_mahasiswa']]['status_kehadiran'] ?? 'Hadir';
                                $note = $attendanceRows[$student['id_mahasiswa']]['keterangan'] ?? '';
                            ?>
                            <tr>
                                <td>
                                    <p class="font-bold"><?= e($student['nama_mahasiswa']) ?></p>
                                    <p class="text-xs text-slate-500"><?= e($student['npm']) ?> - <?= e($student['program_studi']) ?></p>
                                </td>
                                <td>
                                    <div class="status-grid">
                                        <?php foreach ($validStatuses as $status): ?>
                                            <label class="status-option">
                                                <input type="radio" name="status[<?= e($student['id_mahasiswa']) ?>]" value="<?= e($status) ?>" <?= $current === $status ? 'checked' : '' ?> required>
                                                <span><?= e($status) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <input class="form-input min-w-56" type="text" name="keterangan[<?= e($student['id_mahasiswa']) ?>]" value="<?= e($note) ?>" placeholder="Catatan opsional">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-slate-100 bg-slate-50 p-5">
                <button class="btn-primary" type="submit" name="save_absensi">
                    <i class="fas fa-floppy-disk"></i>
                    Simpan Absensi
                </button>
            </div>
        </form>
    <?php elseif ($selected_jadwal): ?>
        <div class="empty-state panel">
            <i class="fas fa-user-slash"></i>
            <p>Belum ada mahasiswa di mata kuliah ini.</p>
        </div>
    <?php else: ?>
        <div class="empty-state panel">
            <i class="fas fa-clipboard-list"></i>
            <p>Pilih mata kuliah dan pertemuan untuk mulai input absensi.</p>
        </div>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
