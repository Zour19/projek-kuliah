<?php
// Load local environment config if present.
function load_dotenv(string $path): void {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (($value !== '') && ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || (substr($value, 0, 1) === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false && !isset($_ENV[$name])) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

load_dotenv(__DIR__ . '/../.env');

$dbFilePath = getenv('DB_FILE');
if ($dbFilePath === false || trim($dbFilePath) === '') {
    $dbFile = __DIR__ . '/../data/database.sqlite';
} else {
    $dbFilePath = trim($dbFilePath);
    if (preg_match('#^([a-zA-Z]:\\\\|/|\\\\)#', $dbFilePath)) {
        $dbFile = $dbFilePath;
    } else {
        $dbFile = __DIR__ . '/../' . ltrim($dbFilePath, '/\\');
    }
}
$shouldSeedData = !file_exists($dbFile);

$conn = new SQLite3($dbFile);
$conn->exec('PRAGMA foreign_keys = ON');

if (!$conn) {
    die("Koneksi Database Gagal: " . db_connect_error());
}

$envBaseUrl = getenv('APP_BASE_URL');
if ($envBaseUrl !== false) {
    $baseUrl = rtrim((string) $envBaseUrl, '/');
} else {
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = rtrim(dirname($scriptPath), '/');
    if (in_array(basename($baseUrl), ['admin', 'auth'], true)) {
        $baseUrl = dirname($baseUrl);
    }
}
if ($baseUrl === '/' || $baseUrl === '\\') {
    $baseUrl = '';
}
define('APP_BASE_URL', $baseUrl);

class SQLiteResultWrapper {
    public array $rows;
    private int $position = 0;

    public function __construct(array $rows) {
        $this->rows = $rows;
    }

    public function fetch_assoc() {
        if ($this->position < count($this->rows)) {
            return $this->rows[$this->position++];
        }
        return false;
    }

    public function num_rows() {
        return count($this->rows);
    }
}

function db_connect() {
    global $conn;
    return $conn;
}

function db_connect_error() {
    global $conn;
    return $conn ? $conn->lastErrorMsg() : 'Unknown SQLite error';
}

function db_error($conn) {
    return $conn->lastErrorMsg();
}

function normalize_image_name(string $imagePath): string {
    $imagePath = trim(str_replace('\\', '/', $imagePath));
    if ($imagePath === '') {
        return '';
    }

    if (strpos($imagePath, 'assets/images/') === 0) {
        $imagePath = substr($imagePath, strlen('assets/images/'));
    } elseif (strpos($imagePath, 'assets/img/') === 0) {
        $imagePath = substr($imagePath, strlen('assets/img/'));
    } elseif (strpos($imagePath, 'assets/') === 0) {
        $imagePath = substr($imagePath, strlen('assets/'));
    }

    return ltrim($imagePath, '/');
}

function image_path(string $imagePath): string {
    $image = normalize_image_name($imagePath);
    if ($image === '') {
        return 'assets/img/default.png';
    }

    $candidate = 'assets/img/' . $image;
    $realPath = __DIR__ . '/../' . $candidate;
    if (file_exists($realPath)) {
        return $candidate;
    }

    $pathInfo = pathinfo($candidate);
    $ext = strtolower($pathInfo['extension'] ?? '');
    $extensionMap = [
        'jpeg' => ['jpg', 'png'],
        'jpg' => ['jpeg', 'png'],
        'png' => ['jpg', 'jpeg'],
    ];

    if (isset($extensionMap[$ext])) {
        foreach ($extensionMap[$ext] as $altExt) {
            $alternate = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $altExt;
            if (file_exists(__DIR__ . '/../' . $alternate)) {
                return $alternate;
            }
        }
    }

    return $candidate;
}

function db_escape($conn, $value) {
    return SQLite3::escapeString((string) $value);
}

function db_query($conn, $query) {
    $trimmed = ltrim($query);
    if (preg_match('/^(SELECT|PRAGMA|WITH)/i', $trimmed)) {
        $result = $conn->query($query);
        if ($result === false) {
            return false;
        }
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return new SQLiteResultWrapper($rows);
    }

    return $conn->exec($query);
}

function db_num_rows($result) {
    if ($result instanceof SQLiteResultWrapper) {
        return $result->num_rows();
    }
    return 0;
}

function db_fetch_assoc($result) {
    if ($result instanceof SQLiteResultWrapper) {
        return $result->fetch_assoc();
    }
    return false;
}

function db_init_schema($conn) {
    $conn->exec(
        'CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kategori TEXT,
            nama_produk TEXT,
            deskripsi TEXT,
            harga INTEGER,
            gambar TEXT
        )'
    );

    $conn->exec(
        'CREATE TABLE IF NOT EXISTS orders (
            id_order TEXT PRIMARY KEY,
            nama_pembeli TEXT,
            no_wa TEXT,
            alamat TEXT,
            tanggal_kirim TEXT,
            waktu_kirim TEXT,
            catatan TEXT,
            metode_pengiriman TEXT,
            total_belanja INTEGER,
            status_pesanan TEXT,
            tanggal_pesan TEXT
        )'
    );

    $conn->exec(
        'CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_order TEXT,
            id_produk INTEGER,
            jumlah INTEGER,
            harga_satuan INTEGER,
            subtotal INTEGER,
            FOREIGN KEY(id_order) REFERENCES orders(id_order)
        )'
    );

    $conn->exec(
        'CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_lengkap TEXT,
            email TEXT UNIQUE,
            password TEXT,
            no_wa TEXT
        )'
    );

    $conn->exec(
        'CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_pelanggan TEXT,
            rating INTEGER,
            ulasan TEXT,
            status TEXT
        )'
    );

    $conn->exec(
        'CREATE TABLE IF NOT EXISTS subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE
        )'
    );

    $conn->exec(
        'CREATE TABLE IF NOT EXISTS admin (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            email TEXT,
            password TEXT,
            role TEXT,
            created_at TEXT
        )'
    );
}

function db_seed_default_admin($conn) {
    $check = db_query($conn, "SELECT * FROM admin LIMIT 1");
    if (db_num_rows($check) === 0) {
        $username = db_escape($conn, 'admin');
        $email = db_escape($conn, 'admin@florist.local');
        $password = '$2y$12$YikyFRBsvN1gEUTiPs5.V.tNkpWfFwBK7KYr2DRW0xSSUFAxAZZ9O';
        $created_at = date('Y-m-d H:i:s');
        db_query($conn, "INSERT INTO admin (username, email, password, role, created_at) VALUES ('$username', '$email', '$password', 'admin', '$created_at')");
    }
}

function normalize_existing_product_images($conn) {
    $results = db_query($conn, "SELECT id, gambar FROM products");
    if (!$results) {
        return;
    }
    while ($row = db_fetch_assoc($results)) {
        $normalized = normalize_image_name($row['gambar'] ?? '');
        if ($normalized !== ($row['gambar'] ?? '')) {
            $escaped = db_escape($conn, $normalized);
            db_query($conn, "UPDATE products SET gambar = '$escaped' WHERE id = " . (int)$row['id']);
        }
    }
}

function db_seed_products_from_json($conn) {
    $productsPath = __DIR__ . '/../data/products.json';
    $categoriesPath = __DIR__ . '/../data/categories.json';

    if (!file_exists($productsPath) || !file_exists($categoriesPath)) {
        return;
    }

    $count = db_query($conn, 'SELECT COUNT(id) AS total FROM products');
    $total = db_fetch_assoc($count)['total'] ?? 0;
    if ($total > 0) {
        return;
    }

    $productsData = json_decode(file_get_contents($productsPath), true);
    $categoriesData = json_decode(file_get_contents($categoriesPath), true);
    $categoryNames = [];
    foreach ($categoriesData as $category) {
        $categoryNames[$category['id']] = $category['name'];
    }

    foreach ($productsData as $product) {
        $kategori = $categoryNames[$product['category_id']] ?? 'Bouquets';
        $nama_produk = db_escape($conn, $product['name'] ?? $product['nama_produk'] ?? 'Produk');
        $deskripsi = db_escape($conn, $product['description'] ?? $product['deskripsi'] ?? '');
        $harga = isset($product['price']) ? (int)$product['price'] : ((int)($product['harga'] ?? 0));
        $gambar = normalize_image_name($product['image'] ?? $product['gambar'] ?? '');
        $gambar = db_escape($conn, $gambar);

        db_query(
            $conn,
            "INSERT INTO products (kategori, nama_produk, deskripsi, harga, gambar) VALUES ('$kategori', '$nama_produk', '$deskripsi', '$harga', '$gambar')"
        );
    }
}

db_init_schema($conn);
db_seed_default_admin($conn);
normalize_existing_product_images($conn);
if ($shouldSeedData) {
    db_seed_products_from_json($conn);
}
?>