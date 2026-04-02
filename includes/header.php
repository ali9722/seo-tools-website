<!DOCTYPE html>
<html lang="en">
<head>
  <?php
  require_once __DIR__ . '/auth.php';
  require_once __DIR__ . '/db.php';
  require_once __DIR__ . '/settings.php';
  $siteSettings = getSiteSettings();
  $headerUser = null;
  try {
      $headerUser = getCurrentUser(getPdo());
  } catch (Throwable $e) {
      $headerUser = null;
  }
  ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SEO Tools Hub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/app.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8'
            }
          },
          boxShadow: {
            soft: '0 14px 35px rgba(37, 99, 235, 0.12)'
          }
        }
      }
    };
  </script>
  <?php if (!empty($siteSettings['header_scripts'])): ?>
    <?php echo $siteSettings['header_scripts']; ?>
  <?php endif; ?>
  <?php if (!empty($siteSettings['google_analytics_code'])): ?>
    <?php echo $siteSettings['google_analytics_code']; ?>
  <?php endif; ?>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
  <header class="sticky top-0 z-50 border-b border-blue-100 bg-white/90 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
      <a href="/index.php" class="flex items-center gap-2 text-xl font-semibold tracking-tight text-slate-900">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">ST</span>
        SEO Tools Hub
      </a>
      <ul class="hidden gap-6 text-sm font-medium text-slate-700 md:flex">
        <li><a href="#tools" class="hover:text-primary-600">Tools</a></li>
        <li><a href="#about" class="hover:text-primary-600">About</a></li>
        <li><a href="#contact" class="hover:text-primary-600">Contact</a></li>
      </ul>
      <div class="flex items-center gap-3">
        <?php if ($headerUser): ?>
          <span class="hidden rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 sm:inline-flex">
            Points: <?php echo (int)$headerUser['points_balance']; ?>
          </span>
        <?php endif; ?>
        <a href="#tools" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700">Explore Tools</a>
      </div>
    </nav>
  </header>
