<?php

declare(strict_types=1);

session_start();

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

    $config = appConfig();
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function redirect(string $path): void
{
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
