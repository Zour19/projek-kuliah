<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = [];
    $uploadedFiles = normalize_upload_files($_FILES['asset_upload'] ?? ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []]);
    foreach ($uploadedFiles as $uploadedFile) {
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($uploadedFile['tmp_name'])) {
            $files[] = [
                'name' => $uploadedFile['name'],
                'status' => 'File gagal diunggah atau error.',
            ];
            continue;
        }

        $destination = move_uploaded_asset($uploadedFile);
        $files[] = $destination;
    }

    respond(['items' => $files]);
}

respond(['items' => scan_unsorted_assets()]);
