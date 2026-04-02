<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_user_id']) && (int)$_SESSION['admin_user_id'] > 0;
}

function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: /admin/admin_login.php');
        exit;
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    return is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
}
