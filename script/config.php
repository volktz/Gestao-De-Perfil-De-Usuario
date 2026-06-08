<?php
declare(strict_types=1);

// Central DB configuration for the legacy `script/` endpoints.
$DB_HOST = 'localhost';
$DB_PORT = 3306;
$DB_NAME = 'perfil_de_usuario';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$DB_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

function getPdo(): PDO
{
    global $DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET, $DB_OPTIONS;

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $DB_HOST, $DB_PORT, $DB_NAME, $DB_CHARSET);

    return new PDO($dsn, $DB_USER, $DB_PASS, $DB_OPTIONS);
}

// Optional helper: create and return PDO with try/catch
function tryGetPdo(): PDO
{
    try {
        return getPdo();
    } catch (PDOException $e) {
        throw $e;
    }
}

// Improve error handling for legacy script endpoints:
// - Disable HTML error display so responses remain JSON.
// - Convert PHP warnings/notices to exceptions.
// - Convert uncaught exceptions to JSON HTTP 500 responses.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

set_error_handler(function (int $severity, string $message, string $file, int $line) {
    // Convert PHP errors to ErrorException so they can be handled uniformly.
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    $payload = [
        'success' => false,
        'message' => $e->getMessage(),
    ];

    // Ensure JSON output even if some output was already produced
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
});
