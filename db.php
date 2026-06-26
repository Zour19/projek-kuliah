<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

function use_sqlite(): bool
{
    return config('DB_CONNECTION', 'sqlite') === 'sqlite' && class_exists('SQLite3');
}

function get_db_file(): string
{
    $path = config('DB_DATABASE', config('DB_PATH', 'data/florist.db'));
    return __DIR__ . '/' . ltrim($path, '/');
}

function get_db_dir(): string
{
    return dirname(get_db_file());
}

function get_db(): ?SQLite3
{
    if (!use_sqlite()) {
        return null;
    }

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
        description TEXT DEFAULT "",
        stock INTEGER DEFAULT 0,
        is_featured INTEGER DEFAULT 0,
        created_at TEXT NOT NULL,
        FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE CASCADE
    )');
    ensure_products_schema($db);

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
    import_products_from_json($db, load_json('products', []));
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

    $jsonProducts = load_json('products', []);
    if (!empty($jsonProducts)) {
        import_products_from_json($db, $jsonProducts);
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
    $productStmt = $db->prepare('INSERT INTO products (category_id, name, price, image, description, stock, is_featured, created_at) VALUES (:category_id, :name, :price, :image, :description, :stock, :is_featured, :created_at)');

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
        $productStmt->bindValue(':description', '', SQLITE3_TEXT);
        $productStmt->bindValue(':stock', 0, SQLITE3_INTEGER);
        $productStmt->bindValue(':is_featured', 0, SQLITE3_INTEGER);
        $productStmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $productStmt->execute();
    }
}

function import_products_from_json(SQLite3 $db, array $jsonProducts): void
{
    $checkStmt = $db->prepare('SELECT id FROM products WHERE name = :name AND image = :image LIMIT 1');
    $insertStmt = $db->prepare('INSERT INTO products (category_id, name, price, image, description, stock, is_featured, created_at) VALUES (:category_id, :name, :price, :image, :description, :stock, :is_featured, :created_at)');
    $updateStmt = $db->prepare('UPDATE products SET category_id = :category_id, price = :price, image = :image, description = :description, stock = :stock, is_featured = :is_featured WHERE id = :id');

    foreach ($jsonProducts as $product) {
        if (!is_array($product)) {
            continue;
        }

        $name = trim((string) ($product['name'] ?? ''));
        $image = trim((string) ($product['image'] ?? ''));
        $categoryId = (int) ($product['category_id'] ?? 0);
        $price = max(0, (int) ($product['price'] ?? 0));
        $description = trim((string) ($product['description'] ?? ''));
        $stock = max(0, (int) ($product['stock'] ?? 0));
        $isFeatured = !empty($product['is_featured']) ? 1 : 0;

        if ($name === '' || $price <= 0 || $categoryId <= 0 || $image === '') {
            continue;
        }

        $checkStmt->bindValue(':name', $name, SQLITE3_TEXT);
        $checkStmt->bindValue(':image', $image, SQLITE3_TEXT);
        $existing = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($existing) {
            $updateStmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
            $updateStmt->bindValue(':price', $price, SQLITE3_INTEGER);
            $updateStmt->bindValue(':image', $image, SQLITE3_TEXT);
            $updateStmt->bindValue(':description', $description, SQLITE3_TEXT);
            $updateStmt->bindValue(':stock', $stock, SQLITE3_INTEGER);
            $updateStmt->bindValue(':is_featured', $isFeatured, SQLITE3_INTEGER);
            $updateStmt->bindValue(':id', (int) $existing['id'], SQLITE3_INTEGER);
            $updateStmt->execute();
            continue;
        }

        $insertStmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $insertStmt->bindValue(':name', $name, SQLITE3_TEXT);
        $insertStmt->bindValue(':price', $price, SQLITE3_INTEGER);
        $insertStmt->bindValue(':image', $image, SQLITE3_TEXT);
        $insertStmt->bindValue(':description', $description, SQLITE3_TEXT);
        $insertStmt->bindValue(':stock', $stock, SQLITE3_INTEGER);
        $insertStmt->bindValue(':is_featured', $isFeatured, SQLITE3_INTEGER);
        $insertStmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $insertStmt->execute();
    }
}

function ensure_products_schema(SQLite3 $db): void
{
    $existingColumns = [];
    $result = $db->query('PRAGMA table_info(products)');
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $existingColumns[$row['name']] = true;
    }

    $schemaUpdates = [
        'description' => 'TEXT DEFAULT ""',
        'stock' => 'INTEGER DEFAULT 0',
        'is_featured' => 'INTEGER DEFAULT 0',
    ];

    foreach ($schemaUpdates as $column => $definition) {
        if (!isset($existingColumns[$column])) {
            $db->exec(sprintf('ALTER TABLE products ADD COLUMN %s %s', $column, $definition));
        }
    }
}

function get_category_by_id(int $id): ?array
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    foreach (get_all_categories() as $category) {
        if ((int)($category['id'] ?? 0) === $id) {
            return $category;
        }
    }

    return null;
}

function get_all_categories(): array
{
    $db = get_db();
    if ($db) {
        $result = $db->query('SELECT * FROM categories ORDER BY name ASC');
        $categories = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }

    $categories = load_json('categories', []);
    if (empty($categories)) {
        $categories = [
            ['id' => 1, 'slug' => 'bouquet', 'name' => 'Bouquets', 'image_folder' => CATEGORY_IMAGE_PATHS['bouquet']],
            ['id' => 2, 'slug' => 'bloom-box', 'name' => 'Bloom Boxes', 'image_folder' => CATEGORY_IMAGE_PATHS['bloom-box']],
            ['id' => 3, 'slug' => 'flowers', 'name' => 'Flowers', 'image_folder' => CATEGORY_IMAGE_PATHS['flowers']],
            ['id' => 4, 'slug' => 'standing-flowers', 'name' => 'Standing Flowers', 'image_folder' => CATEGORY_IMAGE_PATHS['standing-flowers']],
            ['id' => 5, 'slug' => 'accessories', 'name' => 'Accessories', 'image_folder' => CATEGORY_IMAGE_PATHS['accessories']],
        ];
        save_json('categories', $categories);
    }

    return $categories;
}

function get_category_by_slug(string $slug): ?array
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT * FROM categories WHERE slug = :slug');
        $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    foreach (get_all_categories() as $category) {
        if ($category['slug'] === $slug) {
            return $category;
        }
    }

    return null;
}

function get_products_by_category_id(int $categoryId): array
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT * FROM products WHERE category_id = :category_id ORDER BY created_at DESC');
        $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $products = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $products[] = $row;
        }
        return $products;
    }

    $products = load_json('products', []);
    return array_values(array_filter($products, fn($product) => (int)($product['category_id'] ?? 0) === $categoryId));
}

function get_all_products(): array
{
    $db = get_db();
    if ($db) {
        $result = $db->query('SELECT p.*, c.slug AS category_slug, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
        $products = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $products[] = $row;
        }
        return $products;
    }

    $categories = get_all_categories();
    $categoryIndex = [];
    foreach ($categories as $category) {
        $categoryIndex[(int)($category['id'] ?? 0)] = $category;
    }

    $products = load_json('products', []);
    return array_map(function ($product) use ($categoryIndex) {
        $categoryId = (int)($product['category_id'] ?? 0);
        $category = $categoryIndex[$categoryId] ?? null;
        return array_merge($product, [
            'category_slug' => $category['slug'] ?? '',
            'category_name' => $category['name'] ?? '',
        ]);
    }, $products);
}

function get_customer_count(): int
{
    $db = get_db();
    if ($db) {
        return (int) $db->querySingle("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    }

    $users = load_json('users', []);
    return count(array_filter($users, fn($user) => ($user['role'] ?? '') === 'customer'));
}

function create_customer_account(string $username, string $email, string $password): bool
{
    if (is_username_taken($username) || is_email_taken($email)) {
        return false;
    }

    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('INSERT INTO users (username, password, email, role, created_at) VALUES (:username, :password, :email, :role, :created_at)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':role', 'customer', SQLITE3_TEXT);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        return (bool) $stmt->execute();
    }

    $users = load_json('users', []);
    $ids = array_map(fn($user) => (int)($user['id'] ?? 0), $users);
    $nextId = empty($ids) ? 1 : max($ids) + 1;
    $users[] = [
        'id' => $nextId,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'role' => 'customer',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    save_json('users', $users);
    return true;
}

function get_user_by_username(string $username): ?array
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    $users = load_json('users', []);
    foreach ($users as $user) {
        if (($user['username'] ?? '') === $username) {
            return $user;
        }
    }

    return null;
}

function get_user_by_email(string $email): ?array
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    $users = load_json('users', []);
    foreach ($users as $user) {
        if (($user['email'] ?? '') === $email) {
            return $user;
        }
    }

    return null;
}

function authenticate_user(string $identifier, string $password): ?array
{
    $user = get_user_by_username($identifier);
    if (!$user) {
        $user = get_user_by_email($identifier);
    }

    if (!$user) {
        return null;
    }

    if (!password_verify($password, (string) ($user['password'] ?? ''))) {
        return null;
    }

    return $user;
}

function is_username_taken(string $username): bool
{
    return get_user_by_username($username) !== null;
}

function is_email_taken(string $email): bool
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        return (bool) $result->fetchArray(SQLITE3_ASSOC);
    }

    $users = load_json('users', []);
    foreach ($users as $user) {
        if (($user['email'] ?? '') === $email) {
            return true;
        }
    }

    return false;
}

function add_product(int $categoryId, string $name, int $price, string $imagePath, string $description = '', int $stock = 0, int $isFeatured = 0): bool
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('INSERT INTO products (category_id, name, price, image, description, stock, is_featured, created_at) VALUES (:category_id, :name, :price, :image, :description, :stock, :is_featured, :created_at)');
        $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':price', $price, SQLITE3_INTEGER);
        $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':stock', $stock, SQLITE3_INTEGER);
        $stmt->bindValue(':is_featured', $isFeatured, SQLITE3_INTEGER);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        return (bool) $stmt->execute();
    }

    $products = load_json('products', []);
    $ids = array_map(fn($product) => (int)($product['id'] ?? 0), $products);
    $nextId = empty($ids) ? 1 : max($ids) + 1;
    $products[] = [
        'id' => $nextId,
        'category_id' => $categoryId,
        'name' => $name,
        'price' => $price,
        'image' => $imagePath,
        'description' => $description,
        'stock' => $stock,
        'is_featured' => (bool) $isFeatured,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    save_json('products', $products);
    return true;
}

function get_product_by_id(int $id): ?array
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    $products = load_json('products', []);
    foreach ($products as $product) {
        if ((int)($product['id'] ?? 0) === $id) {
            return $product;
        }
    }

    return null;
}

function update_product(int $id, int $categoryId, string $name, int $price, string $imagePath, string $description = '', int $stock = 0, int $isFeatured = 0): bool
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('UPDATE products SET category_id = :category_id, name = :name, price = :price, image = :image, description = :description, stock = :stock, is_featured = :is_featured WHERE id = :id');
        $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':price', $price, SQLITE3_INTEGER);
        $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':stock', $stock, SQLITE3_INTEGER);
        $stmt->bindValue(':is_featured', $isFeatured, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute() !== false;
    }

    $products = load_json('products', []);
    foreach ($products as &$product) {
        if ((int)($product['id'] ?? 0) === $id) {
            $product['category_id'] = $categoryId;
            $product['name'] = $name;
            $product['price'] = $price;
            $product['image'] = $imagePath;
            $product['description'] = $description;
            $product['stock'] = $stock;
            $product['is_featured'] = (bool) $isFeatured;
            save_json('products', $products);
            return true;
        }
    }

    return false;
}

function delete_product(int $id): bool
{
    $db = get_db();
    if ($db) {
        $stmt = $db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute() !== false;
    }

    $products = load_json('products', []);
    $updated = array_values(array_filter($products, fn($product) => (int)($product['id'] ?? 0) !== $id));
    if (count($updated) === count($products)) {
        return false;
    }
    save_json('products', $updated);
    return true;
}
