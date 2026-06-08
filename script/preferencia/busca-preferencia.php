<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPdo();
    $idUsuario = obterIdUsuarioAtual($pdo);

    if ($idUsuario <= 0) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT alertas_sistema, emails_seguranca, emails_marketing, pesquisa_opiniao
           FROM preferencia_usuario
          WHERE id_usuario = :id_usuario'
    );
    $stmt->execute([':id_usuario' => $idUsuario]);
    $preferencias = $stmt->fetch() ?: [];

    echo json_encode($preferencias);
} catch (Throwable $e) {
    echo json_encode([]);
}

?>