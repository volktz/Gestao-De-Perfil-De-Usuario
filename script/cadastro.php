<?php

require_once __DIR__ . '/audit.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

$nomeCompleto = isset($_POST['nome_completo']) ? trim($_POST['nome_completo']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
$senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
$confirmarSenha = isset($_POST['confirmar_senha']) ? trim($_POST['confirmar_senha']) : '';

require_once __DIR__ . '/config.php';

function redirectWithError(string $message): void
{
    header('Location: ../index.html?error=' . urlencode($message));
    exit;
}

function isValidPassword(string $senha): bool
{
    return (bool) preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#!$%]).{8,}$/', $senha);
}

if ($nomeCompleto === '' || $email === '' || $senha === '' || $confirmarSenha === '') {
    redirectWithError('Preencha nome, e-mail e senha.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('E-mail inválido.');
}

if ($senha !== $confirmarSenha) {
    redirectWithError('As senhas não coincidem.');
}

if (!isValidPassword($senha)) {
    redirectWithError('A senha deve ter pelo menos 8 caracteres, incluindo letra maiúscula, minúscula, número e caractere especial (@, #, !, $, %).');
}

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}

try {
    $pdo->beginTransaction();

    $checkEmailSql = 'SELECT COUNT(*) FROM usuario WHERE email = :email';
    $stmtCheckEmail = $pdo->prepare($checkEmailSql);
    $stmtCheckEmail->execute([':email' => $email]);

    if ((int) $stmtCheckEmail->fetchColumn() > 0) {
        redirectWithError('O e-mail informado já está em uso.');
    }

    $sql = "INSERT INTO usuario
            (nome_completo, email, telefone, senha_hash, criado_em) 
            VALUES 
            (:nome_completo, :email, :telefone, :senha, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome_completo' => (string) $nomeCompleto,
        ':email' => (string) $email,
        ':telefone' => (string) $telefone,
        ':senha' => (string) password_hash($senha, PASSWORD_DEFAULT),
    ]);

    $idUsuario = (int) $pdo->lastInsertId();

    $sqlPreferencia = "INSERT INTO preferencia_usuario (id_usuario)
            VALUES (:id_usuario)";
    $stmtPreferencia = $pdo->prepare($sqlPreferencia);
    $stmtPreferencia->execute([
        ':id_usuario' => $idUsuario,
    ]);

    registrarAuditoria($pdo, $idUsuario, 'cadastro');

    $pdo->commit();
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    throw $e;
}

header('Location: ../html/login.html');
exit;

?>