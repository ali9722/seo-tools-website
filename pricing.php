<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$packages = [];
$user = null;
$errorMessage = '';

$error = (string)($_GET['error'] ?? '');
$tool = (string)($_GET['tool'] ?? '');
$required = (int)($_GET['required'] ?? 0);
$balance = (int)($_GET['balance'] ?? 0);

if ($error === 'not_enough_points') {
    $errorMessage = "You need {$required} points to access {$tool}. Current balance: {$balance}.";
} elseif ($error === 'login_required') {
    $errorMessage = 'Please sign in first to access tools and purchase points.';
} elseif ($error === 'access_check_failed') {
    $errorMessage = 'Could not verify tool access right now. Please try again.';
}

try {
    $pdo = getPdo();
    $user = getCurrentUser($pdo);
    $packages = $pdo->query('SELECT id, name, points_amount, price FROM packages ORDER BY price ASC')->fetchAll();
} catch (Throwable $e) {
    $errorMessage = 'Unable to load pricing right now. Please check DB settings.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plans & Pricing</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
  <main class="mx-auto max-w-6xl px-6 py-14 lg:px-8">
    <div class="text-center">
      <h1 class="text-4xl font-bold tracking-tight">Plans & Pricing</h1>
      <p class="mt-3 text-slate-600">Buy point packages to unlock premium tools.</p>
      <?php if ($user): ?>
        <p class="mt-3 inline-flex rounded-full bg-blue-50 px-4 py-1 text-sm font-medium text-blue-700">
          Signed in as <?php echo htmlspecialchars((string)$user['email']); ?> · Balance: <?php echo (int)$user['points_balance']; ?> points
        </p>
      <?php endif; ?>
    </div>

    <?php if ($errorMessage): ?>
      <div class="mx-auto mt-6 max-w-3xl rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
      <?php if (empty($packages)): ?>
        <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
          No packages configured yet. Add plans from <code>admin/plans-pricing.php</code>.
        </div>
      <?php else: ?>
        <?php foreach ($packages as $package): ?>
          <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars((string)$package['name']); ?></h2>
            <p class="mt-2 text-sm text-slate-600"><?php echo (int)$package['points_amount']; ?> points</p>
            <p class="mt-4 text-3xl font-bold">$<?php echo number_format((float)$package['price'], 2); ?></p>

            <form class="mt-5" method="post" action="/stripe/create-checkout-session.php">
              <input type="hidden" name="package_id" value="<?php echo (int)$package['id']; ?>">
              <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Buy with Stripe Checkout
              </button>
            </form>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
