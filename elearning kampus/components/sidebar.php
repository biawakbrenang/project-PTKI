<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_role = $_SESSION['role'] ?? 'guest';

$menus = [
    'admin' => [
        ['Dashboard', '../admin/dashboard.php', 'dashboard.php'],
        ['User Management', '../admin/users.php', 'users.php'],
        ['Dosen', '../admin/dosen.php', 'dosen.php'],
        ['Mahasiswa', '../admin/mahasiswa.php', 'mahasiswa.php'],
        ['Mata Kuliah', '../admin/matakuliah.php', 'matakuliah.php'],
        ['Kelas', '../admin/kelas.php', 'kelas.php'],
        ['Reports', '../admin/reports.php', 'reports.php'],
    ],
    'dosen' => [
        ['Dashboard', '../dosen/dashboard.php', 'dashboard.php'],
        ['Kelas', '../dosen/kelas.php', 'kelas.php'],
        ['Materi & Tugas', '../dosen/materi_tugas.php', 'materi_tugas.php'],
        ['Submission', '../dosen/koreksi.php', 'koreksi.php'],
        ['Absensi', '../dosen/absensi.php', 'absensi.php'],
        ['Pengumuman', '../dosen/pengumuman.php', 'pengumuman.php'],
        ['Input Nilai', '../dosen/nilai.php', 'nilai.php'],
    ],
    'mahasiswa' => [
        ['Dashboard', '../mahasiswa/dashboard.php', 'dashboard.php'],
        ['KRS', '../mahasiswa/krs.php', 'krs.php'],
        ['Kelas', '../mahasiswa/kelas.php', 'kelas.php'],
        ['Pengumuman', '../mahasiswa/pengumuman.php', 'pengumuman.php'],
    ],
];
?>
<aside class="sidebar-aside">
    <div class="sidebar-brand">
        <div class="brand-logo">A</div>
        <div>
            <h2 class="brand-title">Academic LMS</h2>
            <span class="brand-subtitle"><?= strtoupper(htmlspecialchars($current_role)) ?> PANEL</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach (($menus[$current_role] ?? []) as $item): ?>
            <a href="<?= $item[1] ?>" class="nav-link <?= $current_page === $item[2] ? 'active' : '' ?>">
                <span class="nav-dot"></span>
                <?= htmlspecialchars($item[0]) ?>
            </a>
        <?php endforeach; ?>
        <a href="../auth/logout.php" class="nav-link logout-link">
            <span class="nav-dot"></span>
            Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="profile-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)) ?></div>
        <div>
            <p class="footer-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></p>
            <span class="footer-role"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
        </div>
    </div>
</aside>
