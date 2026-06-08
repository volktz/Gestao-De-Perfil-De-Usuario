<?php

// 1. Permite apenas requisições POST para evitar acesso direto via URL.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/config.php';

// 2. Lê os dados do formulário e remove espaços em branco extras.
$nomeCompleto = trim($_POST['nome_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$confirmarSenha = trim($_POST['confirmar_senha'] ?? '');

function redirectWithError(string $message): void
{
    header('Location: ../index.html?error=' . urlencode($message));
    exit;
}

function isValidPassword(string $senha): bool
{
    return (bool) preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#!$%]).{8,}$/', $senha);
}

// 3. Validação dos dados antes de tocar no banco.
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

// 4. Conecta ao banco e inicia transação para manter consistência.
$pdo = getPdo();
$pdo->beginTransaction();

try {
    // 5. Verifica se o e-mail já está cadastrado.
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE email = :email');
    $stmt->execute([':email' => $email]);

    if ((int) $stmt->fetchColumn() > 0) {
        redirectWithError('O e-mail informado já está em uso.');
    }

    // 6. Insere o novo usuário com senha segura.
    $stmt = $pdo->prepare(
        'INSERT INTO usuario (nome_completo, email, telefone, senha_hash, criado_em)
         VALUES (:nome_completo, :email, :telefone, :senha, NOW())'
    );
    $stmt->execute([
        ':nome_completo' => $nomeCompleto,
        ':email' => $email,
        ':telefone' => $telefone,
        ':senha' => password_hash($senha, PASSWORD_DEFAULT),
    ]);

    $idUsuario = (int) $pdo->lastInsertId();

    // 7. Cria a preferência padrão do usuário.
    $stmt = $pdo->prepare('INSERT INTO preferencia_usuario (id_usuario) VALUES (:id_usuario)');
    $stmt->execute([':id_usuario' => $idUsuario]);

    // 8. Registra auditoria e confirma a transação.
    registrarAuditoria($pdo, $idUsuario, 'cadastro');
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

// 9. Redireciona o usuário para login após cadastro bem-sucedido.
header('Location: ../html/login.html');
exit;

?>