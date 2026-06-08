<?php

require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco.']);
    exit;
}

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
