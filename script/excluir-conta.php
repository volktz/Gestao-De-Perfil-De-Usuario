<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';

$host = 'localhost';
$db   = 'perfil_de_usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Erro de conexao com o banco.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $idUsuario = obterIdUsuarioAtual($pdo);

    if ($idUsuario <= 0) {
        throw new RuntimeException('Nenhum usuario encontrado para exclusao.');
    }

    $stmtLog = $pdo->prepare("DELETE FROM log_auditoria WHERE id_usuario = :id_usuario");
    $stmtLog->execute([
        ':id_usuario' => (int) $idUsuario,
    ]);

    $stmtPreferencia = $pdo->prepare("DELETE FROM preferencia_usuario WHERE id_usuario = :id_usuario");
    $stmtPreferencia->execute([
        ':id_usuario' => (int) $idUsuario,
    ]);

    $stmtDeleteUsuario = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = :id_usuario");
    $stmtDeleteUsuario->execute([
        ':id_usuario' => (int) $idUsuario,
    ]);

    $pdo->commit();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
