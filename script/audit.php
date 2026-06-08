<?php

function registrarAuditoria(PDO $pdo, int $idUsuario, string $acaoRealizada): void
{
    if ($idUsuario <= 0) {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $enderecoIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $sql = 'INSERT INTO log_auditoria (id_usuario, acao_realizada, endereco_ip, data_hora)
            VALUES (:id_usuario, :acao_realizada, :endereco_ip, NOW())
            ON DUPLICATE KEY UPDATE
                acao_realizada = VALUES(acao_realizada),
                endereco_ip = VALUES(endereco_ip),
                data_hora = NOW()';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $idUsuario,
        ':acao_realizada' => $acaoRealizada,
        ':endereco_ip' => $enderecoIp,
    ]);
}