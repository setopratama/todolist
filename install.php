<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (appIsInstalled()) {
    redirect('/login.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? '127.0.0.1');
    $port = trim($_POST['port'] ?? '3306');
    $name = trim($_POST['name'] ?? 'todo_app');
    $user = trim($_POST['user'] ?? 'root');
    $pass = (string) ($_POST['pass'] ?? '');
    $adminUsername = trim($_POST['admin_username'] ?? 'admin');
    $adminPassword = (string) ($_POST['admin_password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        $error = 'Nama database hanya boleh huruf, angka, dan underscore.';
    } elseif ($adminUsername === '' || strlen($adminPassword) < 6) {
        $error = 'Username admin wajib diisi dan password minimal 6 karakter.';
    } else {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");

            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            if ($schema === false) {
                throw new RuntimeException('Gagal membaca file schema.sql.');
            }

            $pdo->exec($schema);

            $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
            
            // Cek apakah user sudah ada
            $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
            $checkStmt->execute(['username' => $adminUsername]);
            $existingUser = $checkStmt->fetch();

            if ($existingUser) {
                // Update password jika user sudah ada
                $stmt = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
                $stmt->execute([
                    'password_hash' => $hash,
                    'id' => $existingUser['id'],
                ]);
                $adminId = (int) $existingUser['id'];
            } else {
                // Insert baru jika belum ada
                $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)');
                $stmt->execute([
                    'username' => $adminUsername,
                    'password_hash' => $hash,
                    'role' => 'admin',
                ]);
                $adminId = (int) $pdo->lastInsertId();
            }

            $configArray = [
                'db' => [
                    'host' => $host,
                    'port' => $port,
                    'name' => $name,
                    'user' => $user,
                    'pass' => $pass,
                    'charset' => 'utf8mb4',
                ],
            ];
            $config = "<?php\nreturn " . var_export($configArray, true) . ";\n";
            
            $configDir = __DIR__ . '/config';
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            file_put_contents($configDir . '/database.php', $config);
            redirect('/login.php?installed=1');
        } catch (Throwable $th) {
            $error = 'Install gagal: ' . $th->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install <?= e(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl rounded-2xl shadow-2xl bg-slate-900/80 border border-slate-800 p-8">
        <h1 class="text-3xl font-bold">Setup <?= e(APP_NAME) ?></h1>
        <p class="text-slate-400 mt-2">Langkah pertama: konfigurasi database MySQL dan buat akun admin.</p>

        <?php if ($error): ?>
            <div class="mt-4 rounded-lg bg-rose-500/20 border border-rose-500/40 p-3 text-rose-200"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-slate-300">DB Host</label>
                <input name="host" value="<?= e($_POST['host'] ?? '127.0.0.1') ?>" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm text-slate-300">DB Port</label>
                <input name="port" value="<?= e($_POST['port'] ?? '3306') ?>" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm text-slate-300">DB Name</label>
                <input name="name" value="<?= e($_POST['name'] ?? 'todo_app') ?>" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm text-slate-300">DB User</label>
                <input name="user" value="<?= e($_POST['user'] ?? 'root') ?>" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm text-slate-300">DB Password</label>
                <input type="password" name="pass" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2">
            </div>
            <div>
                <label class="text-sm text-slate-300">Admin Username</label>
                <input name="admin_username" value="<?= e($_POST['admin_username'] ?? 'admin') ?>" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
            </div>
            <div>
                <label class="text-sm text-slate-300">Admin Password</label>
                <input type="password" name="admin_password" class="mt-1 w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required minlength="6">
            </div>
            <div class="md:col-span-2 pt-2">
                <button class="w-full rounded-lg bg-indigo-500 hover:bg-indigo-400 transition px-4 py-2 font-semibold">Install Sekarang</button>
            </div>
        </form>
    </div>
</body>
</html>
