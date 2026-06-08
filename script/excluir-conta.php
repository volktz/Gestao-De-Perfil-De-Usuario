<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function jsonError(string $message, int $code = 500): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    jsonError('Erro de conexão com o banco.');
}

try {
    $pdo->beginTransaction();
    $idUsuario = obterIdUsuarioAtual($pdo);

    if ($idUsuario <= 0) {
        throw new RuntimeException('Nenhum usuário encontrado para exclusão.');
    }

    foreach (['log_auditoria', 'preferencia_usuario', 'usuario'] as $table) {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id_usuario = :id_usuario");
        $stmt->execute([':id_usuario' => $idUsuario]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonError($e->getMessage());
}

