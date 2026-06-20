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

if (!class_exists('SQLite3')) {
    die('PHP SQLite3 extension diperlukan. Silakan install php-sqlite3 dan aktifkan extension tersebut.');
}

function get_db_file(): string
{
    $path = config('DB_PATH', 'data/florist.db');
    return __DIR__ . '/' . ltrim($path, '/');
}

function get_db_dir(): string
{
    return dirname(get_db_file());
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

function get_db(): SQLite3
{
    if (!is_dir(get_db_dir())) {
        mkdir(get_db_dir(), 0755, true);
    }

    $db = new SQLite3(get_db_file());
    $db->exec('PRAGMA foreign_keys = ON');
    init_db($db);
    ensure_asset_directories();
    return $db;
}

function init_db(SQLite3 $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        image_folder TEXT NOT NULL
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        price INTEGER NOT NULL,
        image TEXT DEFAULT NULL,
        created_at TEXT NOT NULL,
        FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE CASCADE
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        role TEXT NOT NULL,
        created_at TEXT NOT NULL
    )');

    seed_categories($db);
    seed_admin($db);
    seed_products($db);
}

function seed_categories(SQLite3 $db): void
{
    $categories = [
        ['slug' => 'bouquet', 'name' => 'Bouquets', 'image_folder' => CATEGORY_IMAGE_PATHS['bouquet']],
        ['slug' => 'bloom-box', 'name' => 'Bloom Boxes', 'image_folder' => CATEGORY_IMAGE_PATHS['bloom-box']],
        ['slug' => 'flowers', 'name' => 'Flowers', 'image_folder' => CATEGORY_IMAGE_PATHS['flowers']],
        ['slug' => 'standing-flowers', 'name' => 'Standing Flowers', 'image_folder' => CATEGORY_IMAGE_PATHS['standing-flowers']],
        ['slug' => 'accessories', 'name' => 'Accessories', 'image_folder' => CATEGORY_IMAGE_PATHS['accessories']],
    ];

    $stmt = $db->prepare('INSERT OR IGNORE INTO categories (slug, name, image_folder) VALUES (:slug, :name, :folder)');
    foreach ($categories as $category) {
        $stmt->bindValue(':slug', $category['slug'], SQLITE3_TEXT);
        $stmt->bindValue(':name', $category['name'], SQLITE3_TEXT);
        $stmt->bindValue(':folder', $category['image_folder'], SQLITE3_TEXT);
        $stmt->execute();
    }
}

function seed_admin(SQLite3 $db): void
{
    $username = (string) config('ADMIN_USERNAME', 'admin');
    $password = (string) config('ADMIN_PASSWORD', 'admin123');
    $email = (string) config('ADMIN_EMAIL', 'admin@matahariflorist.com');

    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result->fetchArray(SQLITE3_ASSOC)) {
        $insert = $db->prepare('INSERT INTO users (username, password, email, role, created_at) VALUES (:username, :password, :email, :role, :created_at)');
        $insert->bindValue(':username', $username, SQLITE3_TEXT);
        $insert->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
        $insert->bindValue(':email', $email, SQLITE3_TEXT);
        $insert->bindValue(':role', 'admin', SQLITE3_TEXT);
        $insert->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $insert->execute();
    }
}

function seed_products(SQLite3 $db): void
{
    $count = $db->querySingle('SELECT COUNT(*) FROM products');
    if ($count > 0) {
        return;
    }

    $samples = [
        ['category' => 'bouquet', 'name' => 'Golden Rays Bouquet', 'price' => 469000, 'image' => 'assets/images/bouquets/golden-rays.jpg'],
        ['category' => 'bouquet', 'name' => 'Serenata Bouquet', 'price' => 349000, 'image' => 'assets/images/bouquets/serenata.jpg'],
        ['category' => 'bloom-box', 'name' => 'Bloom Box Classic', 'price' => 289000, 'image' => 'assets/images/bloom-box/classic.jpg'],
        ['category' => 'flowers', 'name' => 'Tulip Arrangement', 'price' => 225000, 'image' => 'assets/images/flowers/tulip-arrangement.jpg'],
        ['category' => 'standing-flowers', 'name' => 'Elegant Standing', 'price' => 750000, 'image' => 'assets/images/standing-flowers/elegant-standing.jpg'],
        ['category' => 'accessories', 'name' => 'Kartu Ucapan Cantik', 'price' => 25000, 'image' => 'assets/images/accessories/kartu-ucapan.jpg'],
    ];

    $categoryStmt = $db->prepare('SELECT id FROM categories WHERE slug = :slug');
    $productStmt = $db->prepare('INSERT INTO products (category_id, name, price, image, created_at) VALUES (:category_id, :name, :price, :image, :created_at)');

    foreach ($samples as $sample) {
        $categoryStmt->bindValue(':slug', $sample['category'], SQLITE3_TEXT);
        $categoryId = $categoryStmt->execute()->fetchArray(SQLITE3_NUM)[0] ?? null;
        if (!$categoryId) {
            continue;
        }
        $productStmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $productStmt->bindValue(':name', $sample['name'], SQLITE3_TEXT);
        $productStmt->bindValue(':price', $sample['price'], SQLITE3_INTEGER);
        $productStmt->bindValue(':image', $sample['image'], SQLITE3_TEXT);
        $productStmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $productStmt->execute();
    }
}

function get_category_by_slug(string $slug): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM categories WHERE slug = :slug');
    $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ?: null;
}

function get_products_by_category_id(int $categoryId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM products WHERE category_id = :category_id ORDER BY created_at DESC');
    $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $products = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $products[] = $row;
    }
    return $products;
}

function get_all_products(): array
{
    $db = get_db();
    $result = $db->query('SELECT p.*, c.slug AS category_slug, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
    $products = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $products[] = $row;
    }
    return $products;
}

function get_customer_count(): int
{
    $db = get_db();
    return (int) $db->querySingle("SELECT COUNT(*) FROM users WHERE role = 'customer'");
}

function create_customer_account(string $username, string $email, string $password): bool
{
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO users (username, password, email, role, created_at) VALUES (:username, :password, :email, :role, :created_at)');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':role', 'customer', SQLITE3_TEXT);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    return (bool) $stmt->execute();
}

function get_user_by_username(string $username): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ?: null;
}

function add_product(int $categoryId, string $name, int $price, string $imagePath): bool
{
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO products (category_id, name, price, image, created_at) VALUES (:category_id, :name, :price, :image, :created_at)');
    $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':price', $price, SQLITE3_INTEGER);
    $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    return (bool) $stmt->execute();
}

function get_all_categories(): array
{
    $db = get_db();
    $result = $db->query('SELECT * FROM categories ORDER BY name ASC');
    $categories = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $categories[] = $row;
    }
    return $categories;
}
