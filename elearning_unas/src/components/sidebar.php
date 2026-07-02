<?php
require_once __DIR__ . '/../auth/check_auth.php';

requireLogin();

$current_page = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();

$menus = [
  'mahasiswa' => [
    ['file' => 'dashboard.php', 'href' => '/elearning_unas/src/mahasiswa/dashboard.php', 'label' => 'Dashboard', 'icon' => 'DB'],
    ['file' => 'profil.php', 'href' => '/elearning_unas/src/mahasiswa/profil.php', 'label' => 'Profil', 'icon' => 'PR'],
    ['file' => 'transkrip.php', 'href' => '/elearning_unas/src/mahasiswa/transkrip.php', 'label' => 'Transkrip', 'icon' => 'TR'],
    ['file' => 'krs.php', 'href' => '/elearning_unas/src/mahasiswa/krs.php', 'label' => 'KRS', 'icon' => 'KR'],
    ['file' => 'jadwal.php', 'href' => '/elearning_unas/src/mahasiswa/jadwal.php', 'label' => 'Jadwal', 'icon' => 'JD'],
    ['file' => 'nilai.php', 'href' => '/elearning_unas/src/mahasiswa/nilai.php', 'label' => 'Nilai', 'icon' => 'NL'],
    ['file' => 'absensi.php', 'href' => '/elearning_unas/src/mahasiswa/absensi.php', 'label' => 'Absensi', 'icon' => 'AB'],
    ['file' => 'materi.php', 'href' => '/elearning_unas/src/mahasiswa/materi.php', 'label' => 'Materi', 'icon' => 'MT'],
    ['file' => 'pertemuan.php', 'href' => '/elearning_unas/src/mahasiswa/pertemuan.php', 'label' => 'Pertemuan', 'icon' => 'PT'],
    ['file' => 'tugas.php', 'href' => '/elearning_unas/src/mahasiswa/tugas.php', 'label' => 'Tugas', 'icon' => 'TG'],
  ],
  'dosen' => [
    ['file' => 'dashboard.php', 'href' => '/elearning_unas/src/dosen/dashboard.php', 'label' => 'Dashboard', 'icon' => 'DB'],
    ['file' => 'kelas.php', 'href' => '/elearning_unas/src/dosen/kelas.php', 'label' => 'Kelas', 'icon' => 'KL'],
    ['file' => 'nilai.php', 'href' => '/elearning_unas/src/dosen/nilai.php', 'label' => 'Input Nilai', 'icon' => 'NL'],
    ['file' => 'absensi.php', 'href' => '/elearning_unas/src/dosen/absensi.php', 'label' => 'Input Absensi', 'icon' => 'AB'],
    ['file' => 'materi.php', 'href' => '/elearning_unas/src/dosen/materi.php', 'label' => 'Upload Materi', 'icon' => 'MT'],
    ['file' => 'pengumuman.php', 'href' => '/elearning_unas/src/dosen/pengumuman.php', 'label' => 'Pengumuman', 'icon' => 'PG'],
    ['file' => 'pertemuan.php', 'href' => '/elearning_unas/src/dosen/pertemuan.php', 'label' => 'Pertemuan', 'icon' => 'PT'],
    ['file' => 'tugas.php', 'href' => '/elearning_unas/src/dosen/tugas.php', 'label' => 'Tugas', 'icon' => 'TG'],
  ],
  'admin' => [
    ['file' => 'dashboard.php', 'href' => '/elearning_unas/src/admin/dashboard.php', 'label' => 'Dashboard', 'icon' => 'DB'],
    ['file' => 'user.php', 'href' => '/elearning_unas/src/admin/user.php', 'label' => 'Kelola User', 'icon' => 'US'],
    ['file' => 'matakuliah.php', 'href' => '/elearning_unas/src/admin/matakuliah.php', 'label' => 'Kelola Matakuliah', 'icon' => 'MK'],
    ['file' => 'kelas.php', 'href' => '/elearning_unas/src/admin/kelas.php', 'label' => 'Kelola Kelas', 'icon' => 'KL'],
    ['file' => 'laporan.php', 'href' => '/elearning_unas/src/admin/laporan.php', 'label' => 'Laporan', 'icon' => 'LP'],
  ],
];

$roleMenus = $menus[$user['role']] ?? [];
$roleDisplay = [
  'mahasiswa' => 'Mahasiswa',
  'dosen' => 'Dosen',
  'admin' => 'Administrator'
][$user['role']] ?? 'User';
$initials = strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1));
?>
<button type="button" class="sidebar-fab" data-toggle="sidebar" aria-label="Buka menu">
  <span></span>
  <span></span>
  <span></span>
</button>

<aside class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-brand">
      <div class="brand-mark">EL</div>
      <div>
        <h3>E-Learning</h3>
        <span>Akademik UNAS</span>
      </div>
    </div>
    <button type="button" class="sidebar-close" data-toggle="sidebar" aria-label="Tutup menu">x</button>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
    <div class="user-meta">
      <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong>
      <span><?php echo htmlspecialchars($roleDisplay); ?></span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($roleMenus as $item): ?>
      <a href="<?php echo htmlspecialchars($item['href']); ?>" class="nav-item <?php echo $current_page === $item['file'] ? 'active' : ''; ?>">
        <span class="nav-icon"><?php echo htmlspecialchars($item['icon']); ?></span>
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-footnote">
      <strong><?php echo htmlspecialchars($user['username']); ?></strong>
      <span>Session aktif</span>
    </div>
    <a href="/elearning_unas/src/auth/logout.php" class="btn btn-danger btn-block">Logout</a>
  </div>
</aside>
