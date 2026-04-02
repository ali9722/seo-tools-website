<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pricing.php');
    exit;
}

$packageId = (int)($_POST['package_id'] ?? 0);
if ($packageId <= 0) {
    header('Location: /pricing.php?error=invalid_package');
    exit;
}

try {
    $pdo = getPdo();
    $user = getCurrentUser($pdo);

    if (!$user) {
        header('Location: /pricing.php?error=login_required');
        exit;
    }

    $pkgStmt = $pdo->prepare('SELECT id, name, points_amount, price FROM packages WHERE id = :id LIMIT 1');
    $pkgStmt->execute(['id' => $packageId]);
    $package = $pkgStmt->fetch();

    if (!$package) {
        header('Location: /pricing.php?error=invalid_package');
        exit;
    }

    $settings = getSiteSettings();
    $stripeSecret = (string)($settings['stripe_secret_key'] ?? '');

    if ($stripeSecret === '') {
        header('Location: /pricing.php?error=stripe_not_configured');
        exit;
    }

    $amountCents = (int)round(((float)$package['price']) * 100);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $successUrl = "{$scheme}://{$host}/pricing.php?checkout=success&package_id={$packageId}";
    $cancelUrl = "{$scheme}://{$host}/pricing.php?checkout=cancelled";

    $postData = http_build_query([
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'line_items[0][price_data][currency]' => 'usd',
        'line_items[0][price_data][unit_amount]' => $amountCents,
        'line_items[0][price_data][product_data][name]' => (string)$package['name'] . ' (' . (int)$package['points_amount'] . ' points)',
        'line_items[0][quantity]' => 1,
        'metadata[user_id]' => (int)$user['id'],
        'metadata[package_id]' => (int)$package['id'],
        'metadata[points_amount]' => (int)$package['points_amount'],
    ]);

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $stripeSecret,
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    $response = curl_exec($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError !== '' || $statusCode >= 400) {
        header('Location: /pricing.php?error=stripe_checkout_failed');
        exit;
    }

    $json = json_decode($response, true);
    if (!isset($json['url'])) {
        header('Location: /pricing.php?error=stripe_checkout_failed');
        exit;
    }

    header('Location: ' . $json['url']);
    exit;
} catch (Throwable $e) {
    header('Location: /pricing.php?error=stripe_checkout_failed');
    exit;
}
