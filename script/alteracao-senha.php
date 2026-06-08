<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Aceita apenas requisições POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
	exit;
}

// 2. Lê os dados enviados pelo formulário.
$senhaAtual = trim($_POST['current-password'] ?? '');
$senhaNova = trim($_POST['new-password'] ?? '');
$confirmarSenha = trim($_POST['confirm-new-password'] ?? '');

function isValidPassword(string $senha): bool
{
	return (bool) preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#!$%]).{8,}$/', $senha);
}

function jsonError(string $message, int $code = 400): void
{
	http_response_code($code);
	echo json_encode(['success' => false, 'message' => $message]);
	exit;
}

// 3. Valida os campos e a força da nova senha.
if ($senhaAtual === '' || $senhaNova === '' || $confirmarSenha === '') {
	jsonError('Preencha todos os campos.');
}

if ($senhaNova !== $confirmarSenha) {
	jsonError('As senhas não coincidem.');
}

if ($senhaNova === $senhaAtual) {
	jsonError('A nova senha não pode ser igual à senha atual.');
}

if (!isValidPassword($senhaNova)) {
	jsonError('A nova senha deve ter pelo menos 8 caracteres, incluindo letra maiúscula, letra minúscula, número e caractere especial (@, #, !, $, %).');
}

// 4. Conecta ao banco.
try {
	$pdo = getPdo();
} catch (PDOException $e) {
	jsonError('Erro de conexão com o banco.', 500);
}

// 5. Verifica o usuário autenticado.
$idUsuario = obterIdUsuarioAtual($pdo);
if ($idUsuario <= 0) {
	jsonError('Usuário não autenticado.', 401);
}

// 6. Confirma que a senha atual está correta.
$stmt = $pdo->prepare('SELECT senha_hash FROM usuario WHERE id_usuario = :id_usuario LIMIT 1');
$stmt->execute([':id_usuario' => $idUsuario]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senhaAtual, $usuario['senha_hash'])) {
	jsonError('Senha atual incorreta.');
}

// 7. Atualiza a senha do usuário.
$novaSenhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE usuario SET senha_hash = :senha_hash, atualizado_em = NOW() WHERE id_usuario = :id_usuario');
$stmt->execute([
	':senha_hash' => $novaSenhaHash,
	':id_usuario' => $idUsuario,
]);

// 8. Atualiza a sessão atual para refletir a mudança e registra auditoria.
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$_SESSION['senha_atualizada_em'] = date('Y-m-d H:i:s');
session_regenerate_id(true);
registrarAuditoria($pdo, $idUsuario, 'alteracao_senha');

// 9. Retorna JSON de sucesso.
echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
