<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function slugToToolTitle(string $slug): string
{
    return ucwords(str_replace(['-', '_'], ' ', $slug));
}

function requireToolAccess(string $toolSlug): void
{
    try {
        $pdo = getPdo();
        $user = getCurrentUser($pdo);

        if (!$user) {
            header('Location: /pricing.php?error=login_required&tool=' . urlencode($toolSlug));
            exit;
        }

        $title = slugToToolTitle($toolSlug);
        $stmt = $pdo->prepare('SELECT points_required FROM tools WHERE title = :title LIMIT 1');
        $stmt->execute(['title' => $title]);
        $tool = $stmt->fetch();

        $requiredPoints = (int)($tool['points_required'] ?? 0);
        $currentPoints = (int)$user['points_balance'];

        if ($requiredPoints > 0 && $currentPoints < $requiredPoints) {
            $query = http_build_query([
                'error' => 'not_enough_points',
                'tool' => $toolSlug,
                'required' => $requiredPoints,
                'balance' => $currentPoints,
            ]);
            header('Location: /pricing.php?' . $query);
            exit;
        }
    } catch (Throwable $e) {
        header('Location: /pricing.php?error=access_check_failed&tool=' . urlencode($toolSlug));
        exit;
    }
}
