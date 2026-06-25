<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

if (!defined('CATEGORY_IMAGE_PATHS')) {
    define('CATEGORY_IMAGE_PATHS', [
        'bouquet' => 'assets/images/bouquets',
        'bloom-box' => 'assets/images/bloom-box',
        'flowers' => 'assets/images/flowers',
        'standing-flowers' => 'assets/images/standing-flowers',
        'accessories' => 'assets/images/accessories',
    ]);
}

if (!defined('UNSORTED_IMAGE_PATH')) {
    define('UNSORTED_IMAGE_PATH', 'assets/images/unsorted');
}

if (!defined('CATEGORY_KEYWORDS')) {
    define('CATEGORY_KEYWORDS', [
        'bouquet' => ['bouquet', 'buket', 'rose', 'roses', 'tulip', 'calla', 'love', 'serenata', 'crimson', 'golden', 'sweet'],
        'bloom-box' => ['bloom', 'box', 'parcel', 'gift', 'happiness', 'sweet', 'sunbeam', 'classic', 'romantic'],
        'flowers' => ['flower', 'flowers', 'lily', 'tulip', 'sunflower', 'gypsophila', 'rose', 'cluster'],
        'standing-flowers' => ['standing', 'stand', 'elegant', 'graceful', 'festival', 'pure', 'celebration'],
        'accessories' => ['accessories', 'card', 'kartu', 'pita', 'vas', 'foam', 'wrap', 'wrapping'],
    ]);
}

function get_data_dir(): string
{
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function get_json_file(string $name): string
{
    return get_data_dir() . '/' . $name . '.json';
}

function load_json(string $name, array $default = []): array
{
    $file = get_json_file($name);
    if (!is_file($file)) {
        save_json($name, $default);
        return $default;
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?? $default;
}

function save_json(string $name, array $data): void
{
    $file = get_json_file($name);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function ensure_asset_directories(): void
{
    foreach (CATEGORY_IMAGE_PATHS as $path) {
        $dir = __DIR__ . '/../' . ltrim($path, '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    $unsorted = __DIR__ . '/../' . UNSORTED_IMAGE_PATH;
    if (!is_dir($unsorted)) {
        mkdir($unsorted, 0755, true);
    }
}

function normalize_upload_files(array $filePost): array
{
    $files = [];
    if (!isset($filePost['name'])) {
        return $files;
    }

    if (is_array($filePost['name'])) {
        $count = count($filePost['name']);
        for ($i = 0; $i < $count; $i += 1) {
            $files[] = [
                'name' => $filePost['name'][$i] ?? '',
                'type' => $filePost['type'][$i] ?? '',
                'tmp_name' => $filePost['tmp_name'][$i] ?? '',
                'error' => $filePost['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $filePost['size'][$i] ?? 0,
            ];
        }
    } else {
        $files[] = [
            'name' => $filePost['name'],
            'type' => $filePost['type'] ?? '',
            'tmp_name' => $filePost['tmp_name'] ?? '',
            'error' => $filePost['error'] ?? UPLOAD_ERR_NO_FILE,
            'size' => $filePost['size'] ?? 0,
        ];
    }

    return $files;
}

function normalize_asset_filename(string $filename): string
{
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($filename));
    $name = preg_replace('/_+/', '_', $name);
    return strtolower($name);
}

function detect_category_slug_from_filename(string $filename): ?string
{
    $normalized = strtolower($filename);
    foreach (CATEGORY_KEYWORDS as $slug => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return $slug;
            }
        }
    }

    return null;
}

function move_uploaded_asset(array $uploadedFile): array
{
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($uploadedFile['tmp_name'])) {
        return [
            'name' => $uploadedFile['name'] ?? '',
            'status' => 'File gagal diunggah.',
        ];
    }

    $filename = normalize_asset_filename($uploadedFile['name']);
    $slug = detect_category_slug_from_filename($filename);
    $targetFolder = $slug ? CATEGORY_IMAGE_PATHS[$slug] : UNSORTED_IMAGE_PATH;
    $targetDir = __DIR__ . '/../' . ltrim($targetFolder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $destination = $targetDir . '/' . uniqid('', true) . '-' . $filename;
    if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
        return [
            'name' => $uploadedFile['name'] ?? '',
            'status' => 'Gagal memindahkan file.',
        ];
    }

    return [
        'name' => $uploadedFile['name'],
        'status' => 'Berhasil dipindahkan',
        'target' => $targetFolder,
        'path' => $targetFolder . '/' . basename($destination),
    ];
}

function scan_unsorted_assets(): array
{
    $results = [];
    $source = __DIR__ . '/../' . UNSORTED_IMAGE_PATH;
    if (!is_dir($source)) {
        return $results;
    }

    $iterator = new DirectoryIterator($source);
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDot() || !$fileInfo->isFile()) {
            continue;
        }

        $filename = $fileInfo->getFilename();
        $slug = detect_category_slug_from_filename($filename);
        $targetFolder = $slug ? CATEGORY_IMAGE_PATHS[$slug] : UNSORTED_IMAGE_PATH;
        $targetDir = __DIR__ . '/../' . ltrim($targetFolder, '/');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $destination = $targetDir . '/' . uniqid('', true) . '-' . normalize_asset_filename($filename);
        if (rename($fileInfo->getPathname(), $destination)) {
            $results[] = [
                'name' => $filename,
                'status' => 'Berhasil dipindahkan',
                'target' => $targetFolder,
            ];
        } else {
            $results[] = [
                'name' => $filename,
                'status' => 'Gagal memindahkan file.',
            ];
        }
    }

    return $results;
}

if (!function_exists('is_admin_logged_in')) {
    function is_admin_logged_in(): bool
    {
        return !empty($_SESSION['admin_logged_in']);
    }
}
