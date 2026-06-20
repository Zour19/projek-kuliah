<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

const CATEGORY_IMAGE_PATHS = [
    'bouquet' => 'assets/images/bouquets',
    'bloom-box' => 'assets/images/bloom-box',
    'flowers' => 'assets/images/flowers',
    'standing-flowers' => 'assets/images/standing-flowers',
    'accessories' => 'assets/images/accessories',
];

const UNSORTED_IMAGE_PATH = 'assets/images/unsorted';
const CATEGORY_KEYWORDS = [
    'bouquet' => ['bouquet', 'buket', 'rose', 'roses', 'tulip', 'calla', 'love', 'serenata', 'crimson', 'golden', 'sweet'],
    'bloom-box' => ['bloom', 'box', 'parcel', 'gift', 'happiness', 'sweet', 'sunbeam', 'classic', 'romantic'],
    'flowers' => ['flower', 'flowers', 'lily', 'tulip', 'sunflower', 'gypsophila', 'rose', 'cluster'],
    'standing-flowers' => ['standing', 'stand', 'elegant', 'graceful', 'festival', 'pure', 'celebration'],
    'accessories' => ['accessories', 'card', 'kartu', 'pita', 'vas', 'foam', 'wrap', 'wrapping'],
];

function get_data_dir(): string
{
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function ensure_asset_directories(): void
{
    foreach (CATEGORY_IMAGE_PATHS as $path) {
        $dir = __DIR__ . '/' . ltrim($path, '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    $unsorted = __DIR__ . '/' . UNSORTED_IMAGE_PATH;
    if (!is_dir($unsorted)) {
        mkdir($unsorted, 0755, true);
    }
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

function get_all_categories(): array
{
    $cats = load_json('categories', []);
    if (empty($cats)) {
        $cats = [
            ['id' => 1, 'slug' => 'bouquet', 'name' => 'Bouquets', 'image_folder' => CATEGORY_IMAGE_PATHS['bouquet']],
            ['id' => 2, 'slug' => 'bloom-box', 'name' => 'Bloom Boxes', 'image_folder' => CATEGORY_IMAGE_PATHS['bloom-box']],
            ['id' => 3, 'slug' => 'flowers', 'name' => 'Flowers', 'image_folder' => CATEGORY_IMAGE_PATHS['flowers']],
            ['id' => 4, 'slug' => 'standing-flowers', 'name' => 'Standing Flowers', 'image_folder' => CATEGORY_IMAGE_PATHS['standing-flowers']],
            ['id' => 5, 'slug' => 'accessories', 'name' => 'Accessories', 'image_folder' => CATEGORY_IMAGE_PATHS['accessories']],
        ];
        save_json('categories', $cats);
    }
    return $cats;
}

function get_category_by_slug(string $slug): ?array
{
    $cats = get_all_categories();
    foreach ($cats as $cat) {
        if ($cat['slug'] === $slug) {
            return $cat;
        }
    }
    return null;
}

function get_all_products(): array
{
    return load_json('products', []);
}

function get_products_by_category_id(int $categoryId): array
{
    $products = get_all_products();
    return array_filter($products, fn($p) => (int)$p['category_id'] === $categoryId);
}

function get_user_by_username(string $username): ?array
{
    $users = load_json('users', []);
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

function add_product(int $categoryId, string $name, int $price, string $image): bool
{
    $products = get_all_products();
    $ids = array_map(fn($p) => (int)($p['id'] ?? 0), $products);
    $maxId = empty($ids) ? 1 : (max($ids) + 1);
    
    $products[] = [
        'id' => $maxId,
        'category_id' => $categoryId,
        'name' => $name,
        'price' => $price,
        'image' => $image,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    
    save_json('products', $products);
    return true;
}

function get_customer_count(): int
{
    $users = load_json('users', []);
    return count(array_filter($users, fn($u) => $u['role'] === 'customer'));
}

function create_customer_account(string $username, string $email, string $password): array
{
    $users = load_json('users', []);
    
    foreach ($users as $u) {
        if ($u['username'] === $username) {
            return ['success' => false, 'message' => 'Username sudah terdaftar.'];
        }
        if ($u['email'] === $email) {
            return ['success' => false, 'message' => 'Email sudah terdaftar.'];
        }
    }
    
    $uIds = array_map(fn($u) => (int)($u['id'] ?? 0), $users);
    $maxId = empty($uIds) ? 1 : (max($uIds) + 1);
    $users[] = [
        'id' => $maxId,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'role' => 'customer',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    
    save_json('users', $users);
    return ['success' => true, 'message' => 'Akun berhasil dibuat. Silakan login.'];
}

function ensure_admin_exists(): void
{
    $users = load_json('users', []);
    $adminUsername = config('ADMIN_USERNAME', 'admin');
    $admin = get_user_by_username($adminUsername);
    
    if (!$admin) {
        $users[] = [
            'id' => 1,
            'username' => $adminUsername,
            'email' => config('ADMIN_EMAIL', 'admin@florist.local'),
            'password' => password_hash(config('ADMIN_PASSWORD', 'admin123'), PASSWORD_BCRYPT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        save_json('users', $users);
    }
}

function normalize_asset_filename(string $filename): string
{
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($filename));
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

ensure_asset_directories();
ensure_admin_exists();
