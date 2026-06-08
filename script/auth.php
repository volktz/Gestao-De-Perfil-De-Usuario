<?php

function invalidarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function obterIdUsuarioAtual(PDO $pdo): int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['id_usuario'])) {
        return 0;
    }

    $idUsuario = (int) $_SESSION['id_usuario'];
    $stmtUsuario = $pdo->prepare('SELECT atualizado_em FROM usuario WHERE id_usuario = :id_usuario LIMIT 1');
    $stmtUsuario->execute([':id_usuario' => $idUsuario]);
    $atualizadoEm = $stmtUsuario->fetchColumn();

    $senhaAtualizadaEm = $_SESSION['senha_atualizada_em'] ?? null;

    if ($atualizadoEm !== $senhaAtualizadaEm) {
        invalidarSessao();
        return 0;
    }

    return $idUsuario;
}