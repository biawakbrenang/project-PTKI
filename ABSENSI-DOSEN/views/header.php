<?php
$flash = get_flash();
$pageTitles = [
    'dashboard' => 'Dashboard',
    'absensi' => 'Input Absensi',
    'rekap' => 'Rekap Absensi',
    'mahasiswa' => 'Data Mahasiswa',
];
$currentTitle = $pageTitles[$page] ?? 'Halaman';
$navItems = [
    ['page' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-chart-pie'],
    ['page' => 'absensi', 'label' => 'Input Absensi', 'icon' => 'fa-clipboard-check'],
    ['page' => 'rekap', 'label' => 'Rekap Absensi', 'icon' => 'fa-file-lines'],
    ['page' => 'mahasiswa', 'label' => 'Mahasiswa', 'icon' => 'fa-users'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($currentTitle) ?> - Sistem Absensi Dosen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/app.css" rel="stylesheet">
</head>
<body class="bg-slate-100 text-slate-800">
    <div id="mobileOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden"></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0">
        <div class="flex h-full flex-col">
            <div class="border-b border-slate-100 p-6">
                <a href="index.php?page=dashboard" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                        <i class="fas fa-graduation-cap text-lg"></i>
                    </span>
                    <span>
                        <span class="block text-xl font-black tracking-tight">SIAKAD V2</span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Absensi Dosen</span>
                    </span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                <?php foreach ($navItems as $item): ?>
                    <?php $active = $page === $item['page']; ?>
                    <a href="index.php?page=<?= e($item['page']) ?>" class="nav-link <?= $active ? 'nav-link-active' : '' ?>">
                        <i class="fas <?= e($item['icon']) ?> w-5"></i>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="border-t border-slate-100 p-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                            <?= e(substr($_SESSION['nama_lengkap'] ?? 'D', 0, 1)) ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold"><?= e($_SESSION['nama_lengkap'] ?? 'Dosen') ?></p>
                            <p class="truncate text-xs text-slate-500"><?= e($_SESSION['nidn'] ?? '-') ?></p>
                        </div>
                        <a href="index.php?page=logout" class="icon-button text-slate-400 hover:text-red-600" title="Keluar">
                            <i class="fas fa-right-from-bracket"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button id="sidebarToggle" class="icon-button lg:hidden" type="button" aria-label="Buka menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="text-lg font-black sm:text-xl"><?= e($currentTitle) ?></h1>
                        <p class="hidden text-xs text-slate-500 sm:block"><?= e(date('d F Y')) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button id="themeToggle" class="icon-button" type="button" title="Ganti mode tampilan">
                        <i class="fas fa-moon"></i>
                    </button>
                    <a href="index.php?page=logout" class="hidden rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-700 sm:inline-flex">
                        Keluar
                    </a>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            <?php if ($flash): ?>
                <?php $flashClass = $flash['type'] === 'success' ? 'flash-success' : 'flash-error'; ?>
                <div class="mb-6 flash-message <?= e($flashClass) ?>">
                    <i class="fas <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endif; ?>
