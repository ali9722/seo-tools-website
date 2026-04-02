<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$sampleTools = [
    'Word Counter',
    'Character Counter',
    'Case Converter',
    'Text Reverser',
    'Remove Extra Spaces',
    'Lorem Ipsum Generator',
    'IP Checker',
    'DNS Lookup',
    'Whois Lookup',
    'URL Encoder',
    'URL Decoder',
    'Meta Tag Analyzer',
    'Keyword Density Checker',
    'Robots.txt Generator',
    'Sitemap Generator',
    'PDF Merge',
    'PDF Split',
    'Image to Base64',
    'Plagiarism Checker',
    'SERP Preview Tool',
];

try {
    $pdo = getPdo();

    $stmt = $pdo->prepare(
        'INSERT INTO tools (title, category, icon, code_snippet, points_required, status)
         VALUES (:title, :category, :icon, :code_snippet, :points_required, :status)'
    );

    $inserted = 0;
    foreach ($sampleTools as $title) {
        $stmt->execute([
            'title' => $title,
            'category' => 'General',
            'icon' => '🧰',
            'code_snippet' => '<!-- Placeholder tool content -->',
            'points_required' => 0,
            'status' => 'active',
        ]);
        $inserted++;
    }

    echo "Inserted {$inserted} tools successfully." . PHP_EOL;
} catch (Throwable $e) {
    echo 'Bulk insert failed: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
