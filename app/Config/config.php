<?php
declare(strict_types=1);

$envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) {
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

return [
    'app_name' => 'VSMS',
    'app_full_name' => 'Vehicle Sales Management System',
    'environment' => getenv('VSMS_ENV') ?: 'local',
    'database' => [
        'host' => getenv('VSMS_DB_HOST') ?: 'localhost',
        'name' => getenv('VSMS_DB_NAME') ?: 'vsms_db',
        'user' => getenv('VSMS_DB_USER') ?: 'root',
        'password' => getenv('VSMS_DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];
