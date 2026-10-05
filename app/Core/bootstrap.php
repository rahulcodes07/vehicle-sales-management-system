<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/../Config/config.php';

require_once __DIR__ . '/helpers.php';

if (!isset($conn) && !defined('VSMS_SKIP_DATABASE')) {
    $database = $config['database'];
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = mysqli_connect(
            $database['host'],
            $database['user'],
            $database['password'],
            $database['name']
        );
        mysqli_set_charset($conn, $database['charset']);
    } catch (mysqli_sql_exception $exception) {
        http_response_code(500);
        $message = $config['environment'] === 'local'
            ? $exception->getMessage()
            : 'The application could not connect to the database.';
        exit('<div class="system-error"><h1>Database connection error</h1><p>' .
            htmlspecialchars($message, ENT_QUOTES, 'UTF-8') .
            '</p><p>Import <code>database/install.sql</code> and verify your MySQL settings.</p></div>');
    }
}
