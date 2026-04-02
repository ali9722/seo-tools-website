<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/admin_auth.php';

requireAdminAuth();

$flash = '';
$errors = [];
$form = [
    'id' => null,
    'name' => '',
    'points_amount' => '100',
    'price' => '10.00',
];

try {
    $pdo = getPdo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Invalid CSRF token. Please refresh and try again.';
        }
        $action = $_POST['action'] ?? 'save';

        if (empty($errors) && $action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM packages WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $flash = 'Package deleted successfully.';
            }
        } elseif (empty($errors)) {
            $form['id'] = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $form['name'] = trim((string)($_POST['name'] ?? ''));
            $form['points_amount'] = trim((string)($_POST['points_amount'] ?? '0'));
            $form['price'] = trim((string)($_POST['price'] ?? '0.00'));

            if ($form['name'] === '') {
                $errors[] = 'Package name is required.';
            }
            if (!ctype_digit($form['points_amount']) || (int)$form['points_amount'] <= 0) {
                $errors[] = 'Points amount must be a positive integer.';
            }
            if (!is_numeric($form['price']) || (float)$form['price'] <= 0) {
                $errors[] = 'Price must be a positive number.';
            }

            if (empty($errors)) {
                $payload = [
                    'name' => $form['name'],
                    'points_amount' => (int)$form['points_amount'],
                    'price' => number_format((float)$form['price'], 2, '.', ''),
                ];

                if (!empty($form['id'])) {
                    $payload['id'] = (int)$form['id'];
                    $stmt = $pdo->prepare('UPDATE packages SET name = :name, points_amount = :points_amount, price = :price WHERE id = :id');
                    $stmt->execute($payload);
                    $flash = 'Package updated successfully.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO packages (name, points_amount, price) VALUES (:name, :points_amount, :price)');
                    $stmt->execute($payload);
                    $flash = 'Package created successfully.';
                }

                $form = ['id' => null, 'name' => '', 'points_amount' => '100', 'price' => '10.00'];
            }
        }
    }

    $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM packages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $row = $stmt->fetch();
        if ($row) {
            $form = [
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
                'points_amount' => (string)$row['points_amount'],
                'price' => (string)$row['price'],
            ];
        }
    }

    $packages = $pdo->query('SELECT id, name, points_amount, price FROM packages ORDER BY id DESC')->fetchAll();
} catch (Throwable $e) {
    $packages = [];
    $errors[] = 'Database connection failed. Configure DB credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · Plans & Pricing</title>
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
        <a class="mb-2 block rounded-lg bg-blue-600/20 px-4 py-3 text-sm font-medium text-blue-300" href="plans-pricing.php">Plans & Pricing</a>
        <a class="mb-2 block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800" href="settings.php">Settings</a>
      </nav>
    </aside>

    <main class="flex-1 p-6 lg:p-10">
      <h2 class="text-2xl font-bold">Plans & Pricing</h2>
      <p class="mt-1 text-sm text-slate-600">Create and maintain point packages for Stripe checkout.</p>

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

      <div class="mt-8 grid gap-8 xl:grid-cols-5">
        <section class="xl:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold"><?php echo $form['id'] ? 'Edit Package' : 'Add Package'; ?></h3>
            <form method="post" class="mt-5 space-y-4">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
              <input type="hidden" name="action" value="save">
              <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$form['id']); ?>">
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Package Name</label>
                <input name="name" value="<?php echo htmlspecialchars($form['name']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Points Amount</label>
                <input type="number" min="1" name="points_amount" value="<?php echo htmlspecialchars($form['points_amount']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Price (USD)</label>
                <input type="number" min="0.01" step="0.01" name="price" value="<?php echo htmlspecialchars($form['price']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              </div>
              <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" type="submit"><?php echo $form['id'] ? 'Update Package' : 'Create Package'; ?></button>
            </form>
          </div>
        </section>

        <section class="xl:col-span-3">
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4 font-semibold">Existing Packages</div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left">Name</th>
                  <th class="px-6 py-3 text-left">Points</th>
                  <th class="px-6 py-3 text-left">Price</th>
                  <th class="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php if (empty($packages)): ?>
                  <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">No packages found.</td></tr>
                <?php else: ?>
                  <?php foreach ($packages as $package): ?>
                    <tr>
                      <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars((string)$package['name']); ?></td>
                      <td class="px-6 py-4"><?php echo (int)$package['points_amount']; ?></td>
                      <td class="px-6 py-4">$<?php echo number_format((float)$package['price'], 2); ?></td>
                      <td class="px-6 py-4">
                        <div class="flex justify-end gap-2">
                          <a href="plans-pricing.php?edit=<?php echo (int)$package['id']; ?>" class="rounded-md border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Edit</a>
                          <form method="post" onsubmit="return confirm('Delete this package?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$package['id']; ?>">
                            <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Delete</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>
</body>
</html>
