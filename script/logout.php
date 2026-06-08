<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo nao permitido.']);
    exit;
}

$host = 'localhost';
$db   = 'perfil_de_usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $idUsuario = !empty($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;

    if ($idUsuario > 0) {
        registrarAuditoria($pdo, $idUsuario, 'logout');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();

    echo json_encode(['success' => true, 'redirect' => '../html/login.html']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Nao foi possivel sair.']);
}