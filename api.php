<?php
declare(strict_types=1);

require_once __DIR__ . '/db-fallback.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? ''; 
$method = $_SERVER['REQUEST_METHOD'];

function send_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action !== 'create_account') {
    send_json(400, ['success' => false, 'message' => 'Action tidak dikenali. Gunakan action=create_account.']);
}

if ($method !== 'POST') {
    send_json(405, ['success' => false, 'message' => 'Metode harus POST']);
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    $data = $_POST;
}

$username = trim((string) ($data['username'] ?? ''));
$email = trim((string) ($data['email'] ?? ''));
$password = (string) ($data['password'] ?? '');

if ($username === '' || $email === '' || $password === '') {
    send_json(422, ['success' => false, 'message' => 'Username, email, dan password harus diisi.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(422, ['success' => false, 'message' => 'Email tidak valid.']);
}

$customerCount = get_customer_count();
$maxAccounts = (int) config('MAX_CUSTOMER_ACCOUNTS', 10);
if ($customerCount >= $maxAccounts) {
    send_json(429, ['success' => false, 'message' => 'Batas akun pelanggan telah tercapai (' . $maxAccounts . ' akun).']);
}

$db = get_db();
$existingUsername = $db->prepare('SELECT id FROM users WHERE username = :username');
$existingUsername->bindValue(':username', $username, SQLITE3_TEXT);
if ($existingUsername->execute()->fetchArray(SQLITE3_ASSOC)) {
    send_json(409, ['success' => false, 'message' => 'Username sudah digunakan.']);
}

$existingEmail = $db->prepare('SELECT id FROM users WHERE email = :email');
$existingEmail->bindValue(':email', $email, SQLITE3_TEXT);
if ($existingEmail->execute()->fetchArray(SQLITE3_ASSOC)) {
    send_json(409, ['success' => false, 'message' => 'Email sudah terdaftar.']);
}

$created = create_customer_account($username, $email, $password);
if ($created) {
    send_json(201, ['success' => true, 'message' => 'Akun berhasil dibuat.']);
}

send_json(500, ['success' => false, 'message' => 'Terjadi kesalahan saat membuat akun.']);
