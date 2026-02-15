<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!appIsInstalled()) {
    redirect('/install.php');
}

if (currentUser()) {
    redirect('/');
}

$error = null;
$installedMessage = isset($_GET['installed']) ? 'Install berhasil. Silakan login dengan akun admin.' : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        redirect('/');
    }

    $error = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login <?= e(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-slate-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md rounded-2xl shadow-2xl bg-slate-900/90 border border-slate-800 p-8">
        <h1 class="text-2xl font-bold">Login Admin</h1>
        <p class="text-slate-400 mt-1">Masuk untuk mengelola todo list.</p>

        <?php if ($installedMessage): ?>
            <div class="mt-4 rounded-lg bg-emerald-500/20 border border-emerald-500/40 p-3 text-emerald-200"><?= e($installedMessage) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mt-4 rounded-lg bg-rose-500/20 border border-rose-500/40 p-3 text-rose-200"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="mt-6 space-y-4">
            <div>
                <label class="text-sm text-slate-300">Username</label>
                <input name="username" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm text-slate-300">Password</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <button class="w-full rounded-lg bg-indigo-500 hover:bg-indigo-400 transition px-4 py-2 font-semibold">Masuk</button>
        </form>
    </div>
</body>
</html>
