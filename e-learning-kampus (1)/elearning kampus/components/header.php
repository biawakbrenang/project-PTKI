<?php
/**
 * ECO-LEARNING - Reusable Header Component
 */
require_once __DIR__ . '/../config.php';

$current_role = $_SESSION['role'] ?? 'guest';
$user_display_name = $_SESSION['nama_lengkap'] ?? 'Guest User';
$user_email = $_SESSION['email'] ?? 'guest@unas.ac.id';
$role_badge = '';

switch($current_role) {
    case 'admin':
        $role_badge = 'badge-red';
        break;
    case 'dosen':
        $role_badge = 'badge-blue';
        break;
    case 'mahasiswa':
        $role_badge = 'badge-emerald';
        break;
}
?>
<header class="header">
    <!-- Top left Search bar & Date -->
    <div class="header-left">
        <div class="search-container">
            <input 
                type="text" 
                placeholder="Cari materi atau tugas..." 
                class="search-input"
            >
            <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <div class="server-status">
            <span class="status-indicator"></span>
            Server Utama Online: <?= date('d M Y') ?>
        </div>
    </div>

    <!-- Top right profile details -->
    <div class="header-right">
        <!-- Interactive Clock -->
        <div class="clock-badge">
            <svg class="clock-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="liveClock">00:00:00 WIB</span>
        </div>

        <div class="profile-container">
            <div class="profile-details">
                <span class="profile-name"><?= htmlspecialchars($user_display_name) ?></span>
                <span class="badge <?= $role_badge ?>">
                    <?= htmlspecialchars($current_role) ?>
                </span>
            </div>
            
            <!-- Quick logout with visual safety -->
            <a href="../auth/logout.php" title="Keluar dari sistem" class="btn-logout">
                <svg class="logout-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </div>
</header>

