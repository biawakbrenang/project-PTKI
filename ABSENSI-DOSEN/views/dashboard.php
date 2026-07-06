<?php include 'header.php'; ?>

<section class="space-y-6">
    <div class="hero-band rounded-3xl p-6 text-white shadow-xl shadow-blue-100 sm:p-8">
        <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-wider text-blue-100">Selamat datang kembali</p>
            <h2 class="mt-2 text-3xl font-black sm:text-4xl"><?= e(explode(',', $_SESSION['nama_lengkap'])[0]) ?></h2>
            <p class="mt-3 text-blue-50">Pantau jadwal, kelola presensi mahasiswa, dan cek kualitas kehadiran kelas dari satu dashboard.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="stat-card">
            <span class="stat-icon bg-blue-50 text-blue-600"><i class="fas fa-book"></i></span>
            <p class="stat-label">Mata Kuliah</p>
            <p class="stat-value"><?= e($stats['total_matkul'] ?? 0) ?></p>
        </article>
        <article class="stat-card">
            <span class="stat-icon bg-emerald-50 text-emerald-600"><i class="fas fa-user-graduate"></i></span>
            <p class="stat-label">Mahasiswa</p>
            <p class="stat-value"><?= e($stats['total_mahasiswa'] ?? 0) ?></p>
        </article>
        <article class="stat-card">
            <span class="stat-icon bg-amber-50 text-amber-600"><i class="fas fa-calendar-check"></i></span>
            <p class="stat-label">Jadwal Kelas</p>
            <p class="stat-value"><?= e($stats['total_jadwal'] ?? 0) ?></p>
        </article>
        <article class="stat-card">
            <span class="stat-icon bg-rose-50 text-rose-600"><i class="fas fa-chart-line"></i></span>
            <p class="stat-label">Rata Kehadiran</p>
            <p class="stat-value"><?= e($stats['rata_kehadiran'] ?? 0) ?>%</p>
        </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Aksi Cepat</h3>
                    <p class="panel-subtitle">Masuk ke pekerjaan utama tanpa banyak klik.</p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <a href="index.php?page=absensi" class="quick-action">
                    <i class="fas fa-clipboard-check text-blue-600"></i>
                    <span>Input Absensi</span>
                    <small>Catat kehadiran kelas</small>
                </a>
                <a href="index.php?page=rekap" class="quick-action">
                    <i class="fas fa-file-lines text-emerald-600"></i>
                    <span>Lihat Rekap</span>
                    <small>Monitor persentase</small>
                </a>
                <a href="index.php?page=mahasiswa" class="quick-action">
                    <i class="fas fa-users text-amber-600"></i>
                    <span>Mahasiswa</span>
                    <small>Kelola peserta kelas</small>
                </a>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Jadwal Terdekat</h3>
                    <p class="panel-subtitle">5 pertemuan berikutnya di sistem.</p>
                </div>
            </div>
            <div class="space-y-3">
                <?php foreach ($todaySchedules as $schedule): ?>
                    <div class="schedule-row">
                        <div>
                            <p class="font-bold"><?= e($schedule['nama_matkul']) ?></p>
                            <p class="text-sm text-slate-500"><?= e($schedule['kode_matkul']) ?> - Pertemuan <?= e($schedule['pertemuan_ke']) ?></p>
                        </div>
                        <div class="text-right text-sm">
                            <p class="font-bold text-slate-700"><?= e(date('d M Y', strtotime($schedule['tanggal_pertemuan']))) ?></p>
                            <p class="text-slate-500"><?= e(substr($schedule['jam_mulai'], 0, 5)) ?> - <?= e(substr($schedule['jam_selesai'], 0, 5)) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($todaySchedules)): ?>
                    <div class="empty-state py-8">
                        <i class="far fa-calendar"></i>
                        <p>Belum ada jadwal perkuliahan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<?php include 'footer.php'; ?>
