<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function jsonError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método não permitido.', 405);
}

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    jsonError('Erro de conexão com o banco.', 500);
}

$nomeCompleto = trim($_POST['nome_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

if ($nomeCompleto === '' || $email === '') {
    jsonError('Preencha nome e email.');
}

try {
    $idUsuario = obterIdUsuarioAtual($pdo);
    if ($idUsuario <= 0) {
        jsonError('Usuário não autenticado.', 401);
    }

    $stmt = $pdo->prepare(
        'UPDATE usuario
            SET nome_completo = :nome_completo,
                email = :email,
                telefone = :telefone,
                atualizado_em = NOW()
          WHERE id_usuario = :id_usuario'
    );

    $stmt->execute([
        ':nome_completo' => $nomeCompleto,
        ':email' => $email,
        ':telefone' => $telefone,
        ':id_usuario' => $idUsuario,
    ]);

    registrarAuditoria($pdo, $idUsuario, 'atualizacao_dados');

    echo json_encode([
        'success' => true,
        'usuario' => [
            'id_usuario' => $idUsuario,
            'nome_completo' => $nomeCompleto,
            'email' => $email,
            'telefone' => $telefone,
        ],
    ]);
} catch (PDOException $e) {
    $message = 'Erro ao salvar os dados.';
    if ((string) $e->getCode() === '23000' || stripos($e->getMessage(), 'Duplicate') !== false) {
        $message = 'O e-mail informado já está em uso.';
    }
    jsonError($message, 500);
}
