<?php
declare(strict_types=1);

function load_dotenv(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $result = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $result[$key] = trim($value, " \t\n\r\0\x0B\"'");
    }

    return $result;
}

$GLOBALS['APP_ENV'] = load_dotenv(__DIR__ . '/.env');

function config(string $key, $default = null)
{
    $env = $GLOBALS['APP_ENV'];

    if (array_key_exists($key, $env)) {
        return $env[$key];
    }

    $aliases = [
        'DB_PATH' => 'DB_DATABASE',
        'DB_DATABASE' => 'DB_DATABASE',
        'DB_CONNECTION' => 'DB_CONNECTION',
    ];

    if (isset($aliases[$key]) && array_key_exists($aliases[$key], $env)) {
        return $env[$aliases[$key]];
    }

    return $default;
}
