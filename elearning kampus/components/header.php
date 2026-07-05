<?php
require_once __DIR__ . '/../config.php';
$current_role = $_SESSION['role'] ?? 'guest';
$user_display_name = $_SESSION['nama_lengkap'] ?? 'Guest User';

// Student notification badge: recent announcements from their lecturers
$notif_count = 0;
if ($current_role === 'mahasiswa') {
    $notif_mhs_id = $_SESSION['mahasiswa_id'] ?? 0;
    try {
        $stmtNotif = $pdo->prepare("
            SELECT COUNT(DISTINCT p.id)
            FROM pengumuman p
            WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              AND ((p.kelas_id IN (SELECT kelas_id FROM krs WHERE mahasiswa_id = :mhs1))
               OR (p.kelas_id IS NULL AND p.dosen_id IN (
                      SELECT kls.dosen_id FROM krs
                      JOIN kelas kls ON krs.kelas_id = kls.id
                      WHERE krs.mahasiswa_id = :mhs2 AND kls.dosen_id IS NOT NULL)))
        ");
        $stmtNotif->execute(['mhs1' => $notif_mhs_id, 'mhs2' => $notif_mhs_id]);
        $notif_count = (int)$stmtNotif->fetchColumn();
    } catch (PDOException $e) {
        $notif_count = 0;
    }
}
?>
<header class="header">
    <div class="header-left">
        <input type="text" class="search-input" placeholder="Search class, assignment, user...">
    </div>
    <div class="header-right">
        <?php if ($current_role === 'mahasiswa'): ?>
            <a href="../mahasiswa/pengumuman.php" title="Notifikasi Pengumuman" style="position:relative;display:inline-flex;align-items:center;text-decoration:none;">
                <svg style="width:20px;height:20px;color:#475569;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <?php if ($notif_count > 0): ?>
                    <span style="position:absolute;top:-6px;right:-8px;background:#ef4444;color:#fff;border-radius:9999px;font-size:10px;font-weight:700;padding:1px 5px;line-height:1.4;"><?= $notif_count ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        <span class="clock-badge" id="liveClock">00:00:00 WIB</span>
        <span class="badge badge-blue"><?= htmlspecialchars(ucfirst($current_role)) ?></span>
        <span class="profile-name"><?= htmlspecialchars($user_display_name) ?></span>
        <a href="../auth/logout.php" class="btn-secondary">Logout</a>
    </div>
</header>
