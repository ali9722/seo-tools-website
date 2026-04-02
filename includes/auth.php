<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setDemoUserFromQuery(): void
{
    if (isset($_GET['user_id']) && ctype_digit((string)$_GET['user_id'])) {
        $_SESSION['user_id'] = (int)$_GET['user_id'];
    }
}

function getCurrentUser(PDO $pdo): ?array
{
    setDemoUserFromQuery();

    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId) || $userId <= 0) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, email, points_balance, subscription_plan FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}
