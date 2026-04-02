<?php

declare(strict_types=1);

/**
 * Build a tools list by scanning the tools directory for PHP files.
 *
 * @param string $toolsDir Absolute path to the tools folder.
 * @return array<int, array{title: string, slug: string, url: string, description: string}>
 */
function getTools(string $toolsDir): array
{
    if (!is_dir($toolsDir)) {
        return [];
    }

    $files = glob(rtrim($toolsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');
    if ($files === false) {
        return [];
    }

    $tools = [];

    foreach ($files as $filePath) {
        $fileName = basename($filePath);
        $slug = pathinfo($fileName, PATHINFO_FILENAME);

        if ($slug === '' || str_starts_with($slug, '_')) {
            continue;
        }

        $tools[] = [
            'title' => ucwords(str_replace(['-', '_'], ' ', $slug)),
            'slug' => $slug,
            'url' => 'tools/' . $fileName,
            'description' => 'Open the ' . ucwords(str_replace(['-', '_'], ' ', $slug)) . ' utility tool.',
        ];
    }

    usort(
        $tools,
        static fn(array $a, array $b): int => strcmp($a['title'], $b['title'])
    );

    return $tools;
}
