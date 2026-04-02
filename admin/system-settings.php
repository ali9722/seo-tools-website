<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/admin_auth.php';

requireAdminAuth();

$flash = '';
$errors = [];
$form = [
    'google_analytics_code' => '',
    'rapidapi_key' => '',
    'stripe_secret_key' => '',
    'stripe_public_key' => '',
];

try {
    $pdo = getPdo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Invalid CSRF token. Please refresh and try again.';
        }
        $form['google_analytics_code'] = trim((string)($_POST['google_analytics_code'] ?? ''));
        $form['rapidapi_key'] = trim((string)($_POST['rapidapi_key'] ?? ''));
        $form['stripe_secret_key'] = trim((string)($_POST['stripe_secret_key'] ?? ''));
        $form['stripe_public_key'] = trim((string)($_POST['stripe_public_key'] ?? ''));

        if (empty($errors)) {
            $stmt = $pdo->prepare(
            'INSERT INTO settings (id, google_analytics_code, rapidapi_key, stripe_secret_key, stripe_public_key)
             VALUES (1, :google_analytics_code, :rapidapi_key, :stripe_secret_key, :stripe_public_key)
             ON DUPLICATE KEY UPDATE
               google_analytics_code = VALUES(google_analytics_code),
               rapidapi_key = VALUES(rapidapi_key),
               stripe_secret_key = VALUES(stripe_secret_key),
               stripe_public_key = VALUES(stripe_public_key)'
            );

            $stmt->execute([
                'google_analytics_code' => $form['google_analytics_code'],
                'rapidapi_key' => $form['rapidapi_key'],
                'stripe_secret_key' => $form['stripe_secret_key'],
                'stripe_public_key' => $form['stripe_public_key'],
            ]);

            $flash = 'System settings saved successfully.';
        }
    }

    $row = $pdo->query('SELECT google_analytics_code, rapidapi_key, stripe_secret_key, stripe_public_key FROM settings WHERE id = 1 LIMIT 1')->fetch();
    if ($row) {
        $form = [
            'google_analytics_code' => (string)($row['google_analytics_code'] ?? ''),
            'rapidapi_key' => (string)($row['rapidapi_key'] ?? ''),
            'stripe_secret_key' => (string)($row['stripe_secret_key'] ?? ''),
            'stripe_public_key' => (string)($row['stripe_public_key'] ?? ''),
        ];
    }
} catch (Throwable $e) {
    $errors[] = 'Database connection failed. Configure DB credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · System Settings</title>
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
      <h2 class="text-2xl font-bold">System Settings</h2>
      <p class="mt-1 text-sm text-slate-600">Configure global analytics and API credentials used site-wide.</p>

      <?php if ($flash): ?>
        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo htmlspecialchars($flash); ?></div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <ul class="list-disc pl-5">
            <?php foreach ($errors as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <section class="mt-8 max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" class="space-y-5">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Google Analytics / Search Console Code</label>
            <textarea name="google_analytics_code" rows="7" placeholder="Paste GA or Search Console script/meta snippet..." class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"><?php echo htmlspecialchars($form['google_analytics_code']); ?></textarea>
            <p class="mt-1 text-xs text-slate-500">This code snippet will be injected in the website header.</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">RapidAPI Key</label>
            <input type="text" name="rapidapi_key" value="<?php echo htmlspecialchars($form['rapidapi_key']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Stripe Secret Key</label>
              <input type="text" name="stripe_secret_key" value="<?php echo htmlspecialchars($form['stripe_secret_key']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="sk_live_...">
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Stripe Public Key</label>
              <input type="text" name="stripe_public_key" value="<?php echo htmlspecialchars($form['stripe_public_key']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="pk_live_...">
            </div>
          </div>

          <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Settings</button>
        </form>
      </section>
    </main>
  </div>
</body>
</html>
