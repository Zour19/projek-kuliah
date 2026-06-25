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

if (!function_exists('is_admin_logged_in')) {
    function is_admin_logged_in(): bool
    {
        return !empty($_SESSION['admin_logged_in']);
    }
}
function navItem(string $slug, string $label, string $current): string
{
    $active = $slug === $current ? ' active' : '';
    return "<a class=\"nav-link{$active}\" href=\"?page={$slug}\">{$label}</a>";
}

function formatPrice(int $value): string
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}
function normalize_upload_files(array $filePost): array
{
    $files = [];
    $fileCount = is_array($filePost['name']) ? count($filePost['name']) : 0;

    for ($i = 0; $i < $fileCount; $i += 1) {
        $files[] = [
            'name' => $filePost['name'][$i],
            'type' => $filePost['type'][$i],
            'tmp_name' => $filePost['tmp_name'][$i],
            'error' => $filePost['error'][$i],
            'size' => $filePost['size'][$i],
        ];
    }

    return $files;
}
function pageTitle(string $page): string
{
    $titles = [
        'home' => 'Matahari Florist',
        'blogs' => 'Blogs',
        'create-account' => 'Create Account',
        'login' => 'Login',
        'reset-password' => 'Reset Password',
        'contact-us' => 'Contact Us',
        'our-story' => 'Our Story',
        'cart' => 'Keranjang Pesanan',
        'checkout' => 'Proses Pembayaran',
        'admin-login' => 'Admin Login',
        'admin-dashboard' => 'Admin Dashboard',
        'add-product' => 'Tambah Produk',
        'admin-products' => 'Daftar Produk',
        'admin-assets' => 'Asset Sorter',
    ];

    if (isset($titles[$page])) {
        return $titles[$page];
    }

    $category = get_category_by_slug($page);
    if ($category) {
        return $category['name'];
    }

    return 'Matahari Florist';
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}
