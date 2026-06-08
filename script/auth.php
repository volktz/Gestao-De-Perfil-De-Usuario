<?php

function obterIdUsuarioAtual(PDO $pdo): int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['id_usuario'])) {
        return (int) $_SESSION['id_usuario'];
    }

    $stmtUsuario = $pdo->query('SELECT id_usuario FROM usuario ORDER BY id_usuario DESC LIMIT 1');
    $idUsuario = $stmtUsuario->fetchColumn();

    return $idUsuario === false ? 0 : (int) $idUsuario;
}