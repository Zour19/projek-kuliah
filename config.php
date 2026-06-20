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
        if (strpos(trim($line), '#') === 0) {
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
    return $GLOBALS['APP_ENV'][$key] ?? $default;
}
