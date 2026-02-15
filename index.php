<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!appIsInstalled()) {
    redirect('/install.php');
}

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $stmt = db()->prepare('INSERT INTO todos (user_id, title) VALUES (:user_id, :title)');
            $stmt->execute([
                'user_id' => $user['id'],
                'title' => $title,
            ]);
        }
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('UPDATE todos SET is_done = NOT is_done WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $user['id'],
        ]);
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('DELETE FROM todos WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $user['id'],
        ]);
    }

    redirect('/');
}

$stmt = db()->prepare('SELECT id, title, is_done, created_at FROM todos WHERE user_id = :user_id ORDER BY is_done ASC, created_at DESC');
$stmt->execute(['user_id' => $user['id']]);
$todos = $stmt->fetchAll();

$total = count($todos);
$done = count(array_filter($todos, static fn (array $todo): bool => (bool) $todo['is_done']));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="max-w-3xl mx-auto p-6">
        <header class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-slate-400 text-sm">Selamat datang, <?= e($user['username']) ?></p>
                    <h1 class="text-3xl font-bold mt-1">Modern Todo List</h1>
                </div>
                <a href="/logout.php" class="rounded-lg border border-slate-700 px-4 py-2 hover:bg-slate-800 transition">Logout</a>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-slate-800 p-3 border border-slate-700">Total Task: <span class="font-semibold"><?= $total ?></span></div>
                <div class="rounded-xl bg-emerald-500/10 p-3 border border-emerald-500/20">Selesai: <span class="font-semibold"><?= $done ?></span></div>
            </div>
        </header>

        <section class="mt-6 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <form method="post" class="flex gap-3">
                <input type="hidden" name="action" value="add">
                <input name="title" placeholder="Contoh: Kirim laporan mingguan" class="flex-1 rounded-lg bg-slate-800 border border-slate-700 px-3 py-2" required>
                <button class="rounded-lg bg-indigo-500 hover:bg-indigo-400 px-4 py-2 font-semibold">Tambah</button>
            </form>

            <div class="mt-5 space-y-3">
                <?php if (!$todos): ?>
                    <p class="text-slate-400">Belum ada task. Tambahkan task pertama kamu 🚀</p>
                <?php endif; ?>

                <?php foreach ($todos as $todo): ?>
                    <article class="rounded-xl border border-slate-800 bg-slate-800/60 p-4 flex items-center justify-between gap-3">
                        <form method="post" class="flex items-center gap-3 flex-1">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                            <button class="w-5 h-5 rounded-full border-2 <?= (int) $todo['is_done'] === 1 ? 'bg-emerald-400 border-emerald-300' : 'border-slate-500' ?>" title="Toggle"></button>
                            <span class="<?= (int) $todo['is_done'] === 1 ? 'line-through text-slate-500' : '' ?>"><?= e($todo['title']) ?></span>
                        </form>

                        <form method="post">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                            <button class="rounded-lg border border-rose-500/40 px-3 py-1.5 text-rose-300 hover:bg-rose-500/10">Hapus</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
