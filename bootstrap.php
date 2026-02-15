<?php

declare(strict_types=1);

session_start();
ob_start();

const APP_NAME = 'Modern Todo List';

function appIsInstalled(): bool
{
    return file_exists(__DIR__ . '/config/database.php');
}

function appConfig(): array
{
    if (!appIsInstalled()) {
        throw new RuntimeException('Aplikasi belum diinstall.');
    }

    return require __DIR__ . '/config/database.php';
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $config = appConfig();
        $db = $config['db'];
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);

        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Error - <?= APP_NAME ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
            <div class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-8 shadow-2xl">
                <div class="flex items-center gap-4 text-rose-500 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h1 class="text-2xl font-bold">Database Connection Error</h1>
                </div>
                
                <div class="space-y-4 text-slate-400">
                    <p>Gagal menyambung ke database. Hal ini bisa disebabkan oleh:</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li>Server MySQL sedang mati/down.</li>
                        <li>Konfigurasi di <code>config/database.php</code> salah.</li>
                        <li>User database tidak memiliki izin yang cukup.</li>
                    </ul>
                    
                    <div class="bg-black/40 rounded-xl p-4 border border-slate-800 mt-6">
                        <p class="text-xs font-mono text-rose-400 break-words"><?= e($e->getMessage()) ?></p>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <a href="javascript:location.reload()" class="flex-1 text-center rounded-lg bg-indigo-500 hover:bg-indigo-400 transition px-4 py-2 font-semibold text-white">Coba Lagi</a>
                        <a href="<?= url('/install.php') ?>" class="flex-1 text-center rounded-lg border border-slate-700 hover:bg-slate-800 transition px-4 py-2 font-semibold">Setup Ulang</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

function url(string $path = ''): string
{
    $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    return $baseUrl . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    if (strpos($path, '/') === 0) {
        $path = url($path);
    }

    header('Location: ' . $path);
    exit;
}

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, role, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function requireAuth(): array
{
    $user = currentUser();
    if (!$user) {
        redirect('/login.php');
    }

    return $user;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
