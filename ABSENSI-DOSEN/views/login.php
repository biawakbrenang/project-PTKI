<?php
$error = "";
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($auth->login($email, $password)) {
        redirect("index.php?page=dashboard");
    }

    $error = "Email atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Dosen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/app.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100">
    <div class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
        <section class="login-panel hidden items-center justify-center p-12 text-white lg:flex">
            <div class="max-w-xl">
                <div class="mb-8 inline-flex items-center gap-3 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">
                    <i class="fas fa-shield-halved"></i>
                    Sistem akademik dosen
                </div>
                <h1 class="text-5xl font-black leading-tight">Kelola absensi kelas dengan cepat dan rapi.</h1>
                <p class="mt-5 text-lg text-blue-100">Dashboard, input kehadiran, data mahasiswa, dan rekap perkuliahan sudah terhubung dalam satu alur kerja.</p>
            </div>
        </section>

        <section class="flex items-center justify-center p-6">
            <div class="w-full max-w-md rounded-3xl border border-white bg-white p-8 shadow-xl shadow-slate-200 sm:p-10">
                <div class="mb-8">
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">Masuk ke SIAKAD V2</h2>
                    <p class="mt-2 text-sm text-slate-500">Gunakan akun dosen untuk mengakses sistem.</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 flash-message flash-error">
                        <i class="fas fa-circle-exclamation"></i>
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div>
                        <label class="form-label">Email</label>
                        <div class="form-icon-wrap">
                            <i class="far fa-envelope"></i>
                            <input class="form-input pl-11" type="email" name="email" required placeholder="firansyah@univ.ac.id" autocomplete="email">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <div class="form-icon-wrap">
                            <i class="fas fa-lock"></i>
                            <input id="passwordInput" class="form-input px-11" type="password" name="password" required placeholder="Masukkan password" autocomplete="current-password">
                            <button class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700" type="button" data-toggle-password="#passwordInput" aria-label="Tampilkan password">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn-primary w-full" type="submit" name="login">
                        <i class="fas fa-right-to-bracket"></i>
                        Masuk
                    </button>
                </form>

                <div class="mt-8 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-bold text-slate-800">Akun demo</p>
                    <p>Email: firansyah@univ.ac.id</p>
                    <p>Password: password</p>
                </div>
            </div>
        </section>
    </div>
    <script src="js/app.js"></script>
</body>
</html>
