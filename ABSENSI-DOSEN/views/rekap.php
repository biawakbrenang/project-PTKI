<?php
$selected_matkul = $_GET['id_matkul'] ?? '';
$recap_data = [];
$summary = null;
$selectedCourse = null;

if ($selected_matkul) {
    $selectedCourse = $attendance->getCourseByDosen($selected_matkul, $_SESSION['user_id']);
    if (!$selectedCourse) {
        set_flash('error', 'Mata kuliah tidak ditemukan atau bukan milik akun Anda.');
        redirect('index.php?page=rekap');
    }
    $recap_data = $attendance->getAttendanceRecap($selected_matkul);
    $summary = $attendance->getCourseSummary($selected_matkul);
}

include 'header.php';
?>

<section class="space-y-6">
    <div class="panel">
        <form method="GET" action="index.php" class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
            <input type="hidden" name="page" value="rekap">
            <div>
                <label class="form-label">Filter Mata Kuliah</label>
                <select name="id_matkul" class="form-input" data-auto-submit>
                    <option value="">Pilih mata kuliah</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e($course['id_matkul']) ?>" <?= (string) $selected_matkul === (string) $course['id_matkul'] ? 'selected' : '' ?>>
                            <?= e($course['kode_matkul']) ?> - <?= e($course['nama_matkul']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selected_matkul): ?>
                <button type="button" class="btn-muted justify-center" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Cetak
                </button>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($summary): ?>
        <div class="grid gap-4 sm:grid-cols-3">
            <article class="mini-card">
                <span>Mahasiswa</span>
                <strong><?= e($summary['total_mahasiswa'] ?? 0) ?></strong>
            </article>
            <article class="mini-card">
                <span>Total Jadwal</span>
                <strong><?= e($summary['total_jadwal'] ?? 0) ?></strong>
            </article>
            <article class="mini-card">
                <span>Data Terisi</span>
                <strong><?= e($summary['total_absensi'] ?? 0) ?></strong>
            </article>
        </div>
    <?php endif; ?>

    <?php if ($selected_matkul && !empty($recap_data)): ?>
        <div class="panel overflow-hidden p-0">
            <div class="border-b border-slate-100 p-5">
                <h2 class="panel-title">Rekap <?= e($selectedCourse['nama_matkul']) ?></h2>
                <p class="panel-subtitle">Persentase dihitung dari status Hadir dan Terlambat terhadap total jadwal.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Terlambat</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Alpa</th>
                            <th class="text-center">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recap_data as $row): ?>
                            <?php
                                $total = max((int) $row['total_jadwal'], 1);
                                $present = (int) $row['hadir'] + (int) $row['terlambat'];
                                $percentage = min(100, round(($present / $total) * 100));
                                $good = $percentage >= 75;
                            ?>
                            <tr>
                                <td>
                                    <p class="font-bold"><?= e($row['nama_mahasiswa']) ?></p>
                                    <p class="text-xs text-slate-500"><?= e($row['npm']) ?></p>
                                </td>
                                <td class="text-center font-bold text-emerald-600"><?= e($row['hadir']) ?></td>
                                <td class="text-center font-bold text-blue-600"><?= e($row['terlambat']) ?></td>
                                <td class="text-center font-bold text-amber-600"><?= e($row['sakit']) ?></td>
                                <td class="text-center font-bold text-sky-600"><?= e($row['izin']) ?></td>
                                <td class="text-center font-bold text-rose-600"><?= e($row['alpa']) ?></td>
                                <td>
                                    <div class="flex min-w-40 items-center justify-center gap-3">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full <?= $good ? 'bg-emerald-500' : 'bg-rose-500' ?>" style="width: <?= e($percentage) ?>%"></div>
                                        </div>
                                        <span class="font-black <?= $good ? 'text-emerald-600' : 'text-rose-600' ?>"><?= e($percentage) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($selected_matkul): ?>
        <div class="empty-state panel">
            <i class="fas fa-file-circle-question"></i>
            <p>Belum ada mahasiswa atau data absensi untuk mata kuliah ini.</p>
        </div>
    <?php else: ?>
        <div class="empty-state panel">
            <i class="fas fa-filter"></i>
            <p>Pilih mata kuliah untuk melihat rekapitulasi.</p>
        </div>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
