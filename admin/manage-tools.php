<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/admin_auth.php';

requireAdminAuth();

$flash = ['type' => '', 'message' => ''];
$errors = [];
$editingTool = null;

$form = [
    'id' => null,
    'title' => '',
    'category' => '',
    'icon' => '',
    'points_required' => '0',
    'status' => 'active',
    'code_snippet' => '',
];

try {
    $pdo = getPdo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Invalid CSRF token. Please refresh and try again.';
        }
        $action = $_POST['action'] ?? 'save';

        if (empty($errors) && $action === 'delete') {
            $deleteId = (int)($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $stmt = $pdo->prepare('DELETE FROM tools WHERE id = :id');
                $stmt->execute(['id' => $deleteId]);
                $flash = ['type' => 'success', 'message' => 'Tool deleted successfully.'];
            }
        } elseif (empty($errors)) {
            $form['id'] = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $form['title'] = trim((string)($_POST['title'] ?? ''));
            $form['category'] = trim((string)($_POST['category'] ?? ''));
            $form['icon'] = trim((string)($_POST['icon'] ?? ''));
            $form['points_required'] = trim((string)($_POST['points_required'] ?? '0'));
            $form['status'] = trim((string)($_POST['status'] ?? 'active'));
            $form['code_snippet'] = trim((string)($_POST['code_snippet'] ?? ''));

            if ($form['title'] === '') {
                $errors[] = 'Title is required.';
            }
            if ($form['category'] === '') {
                $errors[] = 'Category is required.';
            }
            if (!ctype_digit($form['points_required'])) {
                $errors[] = 'Points must be a non-negative integer.';
            }
            if (!in_array($form['status'], ['active', 'inactive', 'draft'], true)) {
                $errors[] = 'Invalid status selected.';
            }
            if ($form['code_snippet'] === '') {
                $errors[] = 'Tool code snippet is required.';
            }

            if (empty($errors)) {
                $payload = [
                    'title' => $form['title'],
                    'category' => $form['category'],
                    'icon' => $form['icon'] !== '' ? $form['icon'] : null,
                    'code_snippet' => $form['code_snippet'],
                    'points_required' => (int)$form['points_required'],
                    'status' => $form['status'],
                ];

                if (!empty($form['id'])) {
                    $payload['id'] = (int)$form['id'];
                    $stmt = $pdo->prepare('UPDATE tools SET title = :title, category = :category, icon = :icon, code_snippet = :code_snippet, points_required = :points_required, status = :status WHERE id = :id');
                    $stmt->execute($payload);
                    $flash = ['type' => 'success', 'message' => 'Tool updated successfully.'];
                } else {
                    $stmt = $pdo->prepare('INSERT INTO tools (title, category, icon, code_snippet, points_required, status) VALUES (:title, :category, :icon, :code_snippet, :points_required, :status)');
                    $stmt->execute($payload);
                    $flash = ['type' => 'success', 'message' => 'Tool added successfully.'];
                }

                $form = [
                    'id' => null,
                    'title' => '',
                    'category' => '',
                    'icon' => '',
                    'points_required' => '0',
                    'status' => 'active',
                    'code_snippet' => '',
                ];
            }
        }
    }

    $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM tools WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $editingTool = $stmt->fetch();

        if ($editingTool) {
            $form = [
                'id' => (int)$editingTool['id'],
                'title' => (string)$editingTool['title'],
                'category' => (string)$editingTool['category'],
                'icon' => (string)($editingTool['icon'] ?? ''),
                'points_required' => (string)$editingTool['points_required'],
                'status' => (string)$editingTool['status'],
                'code_snippet' => (string)$editingTool['code_snippet'],
            ];
        }
    }

    $tools = $pdo->query('SELECT id, title, category, icon, points_required, status, created_at FROM tools ORDER BY id DESC')->fetchAll();
} catch (Throwable $e) {
    $tools = [];
    $errors[] = 'Database connection failed. Configure DB credentials (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS).';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · Manage Tools</title>
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
        <a class="mb-2 block rounded-lg bg-blue-600/20 px-4 py-3 text-sm font-medium text-blue-300" href="manage-tools.php">Manage Tools</a>
        <a class="mb-2 block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800" href="#">Users</a>
        <a class="mb-2 block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800" href="plans-pricing.php">Plans & Pricing</a>
        <a class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800" href="settings.php">Settings</a>
      </nav>
    </aside>

    <main class="flex-1 p-6 lg:p-10">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900">Manage Tools</h2>
          <p class="mt-1 text-sm text-slate-600">Add, edit, and delete tools with custom code snippets and point rules.</p>
        </div>
      </div>

      <?php if (!empty($flash['message'])): ?>
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          <?php echo htmlspecialchars($flash['message']); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <ul class="list-disc pl-5">
            <?php foreach ($errors as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="grid gap-8 xl:grid-cols-5">
        <section class="xl:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900"><?php echo $form['id'] ? 'Edit Tool' : 'Add New Tool'; ?></h3>
            <form method="post" class="mt-5 space-y-4">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
              <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$form['id']); ?>">
              <input type="hidden" name="action" value="save">

              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tool Title</label>
                <input name="title" type="text" value="<?php echo htmlspecialchars($form['title']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                  <input name="category" type="text" value="<?php echo htmlspecialchars($form['category']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700">Icon (optional)</label>
                  <input name="icon" type="text" value="<?php echo htmlspecialchars($form['icon']); ?>" placeholder="e.g. ⚙" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700">Points Required</label>
                  <input name="points_required" type="number" min="0" value="<?php echo htmlspecialchars($form['points_required']); ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                  <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <?php foreach (['active', 'inactive', 'draft'] as $status): ?>
                      <option value="<?php echo $status; ?>" <?php echo $form['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tool HTML/PHP Code</label>
                <textarea name="code_snippet" rows="12" placeholder="Paste the tool PHP/HTML code here..." class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"><?php echo htmlspecialchars($form['code_snippet']); ?></textarea>
              </div>

              <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                  <?php echo $form['id'] ? 'Update Tool' : 'Add Tool'; ?>
                </button>
                <?php if ($form['id']): ?>
                  <a href="manage-tools.php" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel Edit</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </section>

        <section class="xl:col-span-3">
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
              <h3 class="text-lg font-semibold text-slate-900">Existing Tools</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Title</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Category</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Points</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-600">Status</th>
                    <th class="px-6 py-3 text-right font-semibold text-slate-600">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <?php if (empty($tools)): ?>
                    <tr>
                      <td colspan="5" class="px-6 py-8 text-center text-slate-500">No tools found yet.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($tools as $tool): ?>
                      <tr>
                        <td class="px-6 py-4 font-medium text-slate-800">
                          <?php echo htmlspecialchars((string)$tool['title']); ?>
                        </td>
                        <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars((string)$tool['category']); ?></td>
                        <td class="px-6 py-4 text-slate-600"><?php echo (int)$tool['points_required']; ?></td>
                        <td class="px-6 py-4">
                          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                            <?php echo htmlspecialchars((string)$tool['status']); ?>
                          </span>
                        </td>
                        <td class="px-6 py-4">
                          <div class="flex justify-end gap-2">
                            <a href="manage-tools.php?edit=<?php echo (int)$tool['id']; ?>" class="rounded-md border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this tool?');">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                              <input type="hidden" name="action" value="delete">
                              <input type="hidden" name="id" value="<?php echo (int)$tool['id']; ?>">
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
          </div>
        </section>
      </div>
    </main>
  </div>
</body>
</html>
