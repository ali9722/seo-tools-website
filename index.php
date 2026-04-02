<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$query = trim((string)($_GET['q'] ?? ''));
$toolsByCategory = [];
$errorMessage = '';

try {
    $pdo = getPdo();

    $sql = 'SELECT id, title, category, icon, points_required
            FROM tools
            WHERE status = :status';
    $params = ['status' => 'active'];

    if ($query !== '') {
        $sql .= ' AND (title LIKE :q OR category LIKE :q)';
        $params['q'] = '%' . $query . '%';
    }

    $sql .= ' ORDER BY category ASC, title ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tools = $stmt->fetchAll();

    foreach ($tools as $tool) {
        $category = (string)$tool['category'];
        $toolsByCategory[$category][] = $tool;
    }
} catch (Throwable $e) {
    $errorMessage = 'Unable to load tools at the moment. Please try again later.';
}

include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 to-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
      <div class="mx-auto max-w-3xl text-center">
        <p class="inline-flex items-center rounded-full bg-white px-4 py-1 text-xs font-semibold uppercase tracking-wide text-primary-700 ring-1 ring-blue-200">User Tools Directory</p>
        <h1 class="mt-6 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">Explore Active SEO Tools</h1>
        <p class="mt-5 text-lg leading-8 text-slate-600">Browse tools by category and check points required before launching.</p>
      </div>

      <div class="mx-auto mt-10 max-w-2xl rounded-2xl border border-blue-100 bg-white p-3 shadow-soft">
        <form class="flex flex-col gap-3 sm:flex-row" action="index.php" method="get">
          <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search by tool name or category..." class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary-500 focus:ring-2 focus:ring-blue-100">
          <button type="submit" class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-700">Search</button>
        </form>
      </div>
    </div>
  </section>

  <section id="tools" class="mx-auto max-w-7xl px-6 pb-20 pt-12 lg:px-8">
    <?php if ($errorMessage): ?>
      <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php elseif (empty($toolsByCategory)): ?>
      <div class="rounded-2xl border border-dashed border-blue-200 bg-white p-10 text-center text-slate-600">
        No active tools found.
      </div>
    <?php else: ?>
      <?php foreach ($toolsByCategory as $category => $categoryTools): ?>
        <div class="mb-10">
          <h2 class="text-2xl font-bold tracking-tight text-slate-900"><?php echo htmlspecialchars($category); ?></h2>
          <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($categoryTools as $tool): ?>
              <?php
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string)$tool['title']), '-'));
                $toolUrl = 'tools/' . $slug . '.php';
                $toolFileExists = file_exists(__DIR__ . '/' . $toolUrl);
              ?>
              <article class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-soft">
                <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-700"><?php echo htmlspecialchars((string)($tool['icon'] ?: '⚙')); ?></div>
                <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars((string)$tool['title']); ?></h3>
                <p class="mt-2 text-sm text-slate-600">Points Cost: <span class="font-semibold text-primary-700"><?php echo (int)$tool['points_required']; ?></span></p>

                <?php if ($toolFileExists): ?>
                  <a href="<?php echo htmlspecialchars($toolUrl); ?>" class="mt-5 inline-flex text-sm font-semibold text-primary-700 hover:text-primary-800">Open tool →</a>
                <?php else: ?>
                  <span class="mt-5 inline-flex text-sm font-medium text-slate-400">Page not available</span>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
