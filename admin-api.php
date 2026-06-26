<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function send_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_request_payload(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    return is_array($data) ? $data : [];
}

function is_admin_authenticated(): bool
{
    return isset($_SESSION['admin_user']) && $_SESSION['admin_user'] === true;
}

function normalize_image_path(string $path): string
{
    return preg_replace('#^\.*/*#', '', trim($path));
}

if (!is_admin_authenticated()) {
    send_json(401, ['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai admin terlebih dahulu.']);
}

if ($method === 'GET') {
    if ($action === 'get_products') {
        $products = get_all_products();
        send_json(200, ['success' => true, 'data' => $products]);
    }

    if ($action === 'get_product') {
        $id = (int) ($_GET['id'] ?? 0);
        $product = get_product_by_id($id);
        if (!$product) {
            send_json(404, ['success' => false, 'message' => 'Produk tidak ditemukan']);
        }
        $category = get_category_by_id((int)($product['category_id'] ?? 0));
        if ($category) {
            $product['category_slug'] = $category['slug'];
            $product['category_name'] = $category['name'];
        }
        send_json(200, ['success' => true, 'data' => $product]);
    }

    send_json(400, ['success' => false, 'message' => 'Action tidak dikenali']);
}

if ($method === 'POST') {
    $data = read_request_payload();

    if ($action === 'create_product') {
        $name = trim((string) ($data['name'] ?? ''));
        $price = (int) ($data['price'] ?? 0);
        $category_id = (int) ($data['category_id'] ?? 0);
        $image = normalize_image_path((string) ($data['image'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $stock = max(0, (int) ($data['stock'] ?? 0));
        $isFeatured = !empty($data['is_featured']) ? 1 : 0;

        if ($name === '' || $price <= 0 || $category_id <= 0 || $image === '') {
            send_json(422, ['success' => false, 'message' => 'Nama produk, kategori, harga, dan gambar harus diisi.']);
        }

        if (!add_product($category_id, $name, $price, $image, $description, $stock, $isFeatured)) {
            send_json(500, ['success' => false, 'message' => 'Gagal menambahkan produk.']);
        }

        send_json(201, ['success' => true, 'message' => 'Produk berhasil ditambahkan']);
    }

    if ($action === 'update_product') {
        $id = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $price = (int) ($data['price'] ?? 0);
        $category_id = (int) ($data['category_id'] ?? 0);
        $image = trim((string) ($data['image'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $stock = max(0, (int) ($data['stock'] ?? 0));
        $isFeatured = !empty($data['is_featured']) ? 1 : 0;

        if ($id <= 0 || $name === '' || $price <= 0 || $category_id <= 0) {
            send_json(422, ['success' => false, 'message' => 'Data produk tidak lengkap.']);
        }

        $existing = get_product_by_id($id);
        if (!$existing) {
            send_json(404, ['success' => false, 'message' => 'Produk tidak ditemukan']);
        }

        $imagePath = $image !== '' ? normalize_image_path($image) : $existing['image'];
        $description = $description !== '' ? $description : ($existing['description'] ?? '');
        $stock = max(0, (int) ($data['stock'] ?? $existing['stock'] ?? 0));
        $isFeatured = array_key_exists('is_featured', $data)
            ? (!empty($data['is_featured']) ? 1 : 0)
            : (int) ($existing['is_featured'] ?? 0);

        if (!update_product($id, $category_id, $name, $price, $imagePath, $description, $stock, $isFeatured)) {
            send_json(500, ['success' => false, 'message' => 'Gagal memperbarui produk']);
        }

        send_json(200, ['success' => true, 'message' => 'Produk berhasil diperbarui']);
    }

    if ($action === 'delete_product') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            send_json(422, ['success' => false, 'message' => 'ID produk harus diisi']);
        }

        if (!delete_product($id)) {
            send_json(404, ['success' => false, 'message' => 'Produk tidak ditemukan atau gagal dihapus']);
        }

        send_json(200, ['success' => true, 'message' => 'Produk berhasil dihapus']);
    }

    if ($action === 'upload_image') {
        if (!isset($_FILES['image'])) {
            send_json(422, ['success' => false, 'message' => 'Gambar tidak ditemukan']);
        }

        $file = $_FILES['image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed, true)) {
            send_json(422, ['success' => false, 'message' => 'Tipe file tidak diizinkan']);
        }
        if ($file['size'] > 5000000) {
            send_json(422, ['success' => false, 'message' => 'Ukuran file terlalu besar (maks 5MB)']);
        }

        $uploadDir = __DIR__ . '/assets/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'product-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = 'assets/images/' . $filename;
            send_json(200, ['success' => true, 'message' => 'Gambar berhasil diupload', 'path' => $relativePath]);
        }

        send_json(500, ['success' => false, 'message' => 'Gagal mengupload gambar']);
    }

    send_json(400, ['success' => false, 'message' => 'Action tidak dikenali']);
}

send_json(405, ['success' => false, 'message' => 'Metode HTTP tidak diizinkan']);
