<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Return system settings from DB with safe defaults.
 *
 * @return array{header_scripts:string,google_analytics_code:string,rapidapi_key:string,stripe_secret_key:string,stripe_public_key:string}
 */
function getSiteSettings(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $defaults = [
        'header_scripts' => '',
        'google_analytics_code' => '',
        'rapidapi_key' => '',
        'stripe_secret_key' => '',
        'stripe_public_key' => '',
    ];

    try {
        $pdo = getPdo();
        $stmt = $pdo->query('SELECT header_scripts, google_analytics_code, rapidapi_key, stripe_secret_key, stripe_public_key FROM settings WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();

        if (!$row) {
            $cache = $defaults;
            return $cache;
        }

        $cache = [
            'header_scripts' => (string)($row['header_scripts'] ?? ''),
            'google_analytics_code' => (string)($row['google_analytics_code'] ?? ''),
            'rapidapi_key' => (string)($row['rapidapi_key'] ?? ''),
            'stripe_secret_key' => (string)($row['stripe_secret_key'] ?? ''),
            'stripe_public_key' => (string)($row['stripe_public_key'] ?? ''),
        ];

        return $cache;
    } catch (Throwable $e) {
        $cache = $defaults;
        return $cache;
    }
}
