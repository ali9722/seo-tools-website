<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/admin_auth.php';

if (isAdminLoggedIn()) {
    header('Location: /admin/manage-tools.php');
    exit;
}

$error = '';

try {
    $pdo = getPdo();

    $seedEmail = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
    $seedPass = getenv('ADMIN_PASSWORD') ?: 'admin12345';

    $seedCheck = $pdo->prepare('SELECT id FROM admin_users WHERE email = :email LIMIT 1');
    $seedCheck->execute(['email' => $seedEmail]);
    if (!$seedCheck->fetch()) {
        $hash = password_hash($seedPass, PASSWORD_DEFAULT);
        $seedInsert = $pdo->prepare('INSERT INTO admin_users (email, password_hash) VALUES (:email, :password_hash)');
        $seedInsert->execute(['email' => $seedEmail, 'password_hash' => $hash]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $stmt = $pdo->prepare('SELECT id, email, password_hash FROM admin_users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, (string)$admin['password_hash'])) {
            $_SESSION['admin_user_id'] = (int)$admin['id'];
            $_SESSION['admin_user_email'] = (string)$admin['email'];
            header('Location: /admin/manage-tools.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
} catch (Throwable $e) {
    $error = 'Unable to connect to database.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-6">
  <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-bold text-slate-900">Admin Login</h1>
    <p class="mt-1 text-sm text-slate-600">Sign in to access the dashboard.</p>

    <?php if ($error): ?>
      <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4">
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
        <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
      </div>
      <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Sign In</button>
    </form>
  </div>
</body>
</html>
