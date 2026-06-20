<?php
declare(strict_types=1);

require_once __DIR__ . '/db-fallback.php';
session_start();

// Allow running from CLI or from admin session in browser
if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
}

if (PHP_SAPI !== 'cli' && !is_admin_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$unsorted = __DIR__ . '/' . ltrim(UNSORTED_IMAGE_PATH, '/');
if (!is_dir($unsorted)) {
    mkdir($unsorted, 0755, true);
}

$results = [];
$items = array_values(array_diff(scandir($unsorted), ['.', '..']));
foreach ($items as $file) {
    $src = $unsorted . '/' . $file;
    if (!is_file($src)) {
        continue;
    }

    // validate image
    $isImage = @getimagesize($src) !== false;
    if (!$isImage) {
        $results[] = ['name' => $file, 'status' => 'skipped - not an image'];
        continue;
    }

    $categorySlug = detect_category_slug_from_filename($file);
    if ($categorySlug && isset(CATEGORY_IMAGE_PATHS[$categorySlug])) {
        $targetDir = __DIR__ . '/' . ltrim(CATEGORY_IMAGE_PATHS[$categorySlug], '/');
    } else {
        $targetDir = $unsorted . '/unknown';
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $safe = normalize_asset_filename($file);
    $target = $targetDir . '/' . time() . '_' . $safe;
    if (@rename($src, $target)) {
        $results[] = ['name' => $file, 'status' => 'moved', 'target' => str_replace(__DIR__ . '/', '', $target)];
    } else {
        $results[] = ['name' => $file, 'status' => 'failed to move'];
    }
}

if (PHP_SAPI === 'cli') {
    foreach ($results as $r) {
        echo ($r['name'] ?? '') . ' - ' . ($r['status'] ?? '') . PHP_EOL;
    }
    exit;
}

echo json_encode(['success' => true, 'items' => $results]);
