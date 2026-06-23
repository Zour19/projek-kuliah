<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

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

function create_customer_account(string $username, string $email, string $password): bool
{
    $users = load_json('users', []);

    foreach ($users as $u) {
        if ($u['username'] === $username) {
            return false;
        }
        if ($u['email'] === $email) {
            return false;
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
    return true;
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

// normalize_asset_filename and detect_category_slug_from_filename are provided by includes/helpers.php

ensure_asset_directories();
ensure_admin_exists();

if (!function_exists('is_admin_logged_in')) {
    function is_admin_logged_in(): bool
    {
        return !empty($_SESSION['admin_logged_in']);
    }
}
