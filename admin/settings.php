<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/admin_auth.php';

requireAdminAuth();

$flash = '';
$error = '';
$headerScripts = '';

try {
    $pdo = getPdo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid CSRF token. Please refresh and try again.';
        } else {
            $headerScripts = (string)($_POST['header_scripts'] ?? '');

            $stmt = $pdo->prepare(
                'INSERT INTO settings (id, header_scripts)
                 VALUES (1, :header_scripts)
                 ON DUPLICATE KEY UPDATE header_scripts = VALUES(header_scripts)'
            );
            $stmt->execute(['header_scripts' => $headerScripts]);
            $flash = 'Settings saved successfully.';
        }
    }

    $row = $pdo->query('SELECT header_scripts FROM settings WHERE id = 1 LIMIT 1')->fetch();
    if ($row) {
        $headerScripts = (string)($row['header_scripts'] ?? '');
    }
} catch (Throwable $e) {
    $error = 'Database connection failed. Configure DB credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · Settings</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
  <div class="min-h-screen lg:flex">
    <aside class="w-full border-b border-slate-200 bg-slate-900 text-slate-200 lg:w-72 lg:border-b-0 lg:border-r">
      <div class="px-6 py-6">
        <h1 class="text-xl font-semibold text-white">SaaS Admin</h1>
        <p class="mt-1 text-sm text-slate-400">Tools Website Panel</p>
      </div>
      <nav class="px-4 pb-6">
        <a class="mb-2 block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800" href="manage-tools.php">Manage Tools</a>
        <a class="mb-2 block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800" href="plans-pricing.php">Plans & Pricing</a>
        <a class="mb-2 block rounded-lg bg-blue-600/20 px-4 py-3 text-sm font-medium text-blue-300" href="settings.php">Settings</a>
      </nav>
    </aside>

    <main class="flex-1 p-6 lg:p-10">
      <h2 class="text-2xl font-bold">Settings</h2>
      <p class="mt-1 text-sm text-slate-600">Manage global header snippets for analytics, verification, and custom CSS.</p>

      <?php if ($flash): ?>
        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo htmlspecialchars($flash); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <section class="mt-8 max-w-5xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Header Scripts (Google Analytics, Search Console, Custom CSS)</label>
            <textarea
              name="header_scripts"
              rows="16"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"
              placeholder="Paste script/meta/style tags here..."
            ><?php echo htmlspecialchars($headerScripts); ?></textarea>
            <p class="mt-2 text-xs text-slate-500">Script tags are allowed and output in the global &lt;head&gt; section.</p>
          </div>

          <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Settings</button>
        </form>
      </section>
    </main>
  </div>
</body>
</html>
