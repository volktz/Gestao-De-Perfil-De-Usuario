<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Metodo nao permitido.']);
	exit;
}

require_once __DIR__ . '/config.php';

function isValidPassword(string $senha): bool
{
	return (bool) preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#!$%]).{8,}$/', $senha);
}

try {
	$pdo = getPdo();
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Erro de conexao com o banco.']);
	exit;
}

$senhaAtual = isset($_POST['current-password']) ? trim($_POST['current-password']) : '';
$senhaNova = isset($_POST['new-password']) ? trim($_POST['new-password']) : '';
$confirmarSenha = isset($_POST['confirm-new-password']) ? trim($_POST['confirm-new-password']) : '';

if ($senhaAtual === '' || $senhaNova === '' || $confirmarSenha === '') {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
	exit;
}

if ($senhaNova !== $confirmarSenha) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'As senhas nao coincidem.']);
	exit;
}

if ($senhaNova === $senhaAtual) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'A nova senha nao pode ser igual à senha atual.']);
	exit;
}

if (!isValidPassword($senhaNova)) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 8 caracteres, incluindo letra maiúscula, letra minúscula, número e caractere especial (@, #, !, $, %).']);
	exit;
}

try {
	$idUsuario = obterIdUsuarioAtual($pdo);

	if ($idUsuario <= 0) {
		http_response_code(401);
		echo json_encode(['success' => false, 'message' => 'Usuario nao autenticado.']);
		exit;
	}

	$stmtUsuario = $pdo->prepare('SELECT id_usuario, senha_hash FROM usuario WHERE id_usuario = :id_usuario LIMIT 1');
	$stmtUsuario->execute([':id_usuario' => $idUsuario]);
	$usuario = $stmtUsuario->fetch();

	if ($usuario === false) {
		http_response_code(404);
		echo json_encode(['success' => false, 'message' => 'Nenhum usuario encontrado.']);
		exit;
	}

	if (!password_verify($senhaAtual, $usuario['senha_hash'])) {
		http_response_code(400);
		echo json_encode(['success' => false, 'message' => 'Senha atual incorreta.']);
		exit;
	}

	$novaSenhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);

	$stmtUpdate = $pdo->prepare("UPDATE usuario SET senha_hash = :senha_hash, atualizado_em = NOW() WHERE id_usuario = :id_usuario");
	$stmtUpdate->execute([
		':senha_hash' => $novaSenhaHash,
		':id_usuario' => (int) $usuario['id_usuario'],
	]);

	$stmtAtualizado = $pdo->prepare('SELECT atualizado_em FROM usuario WHERE id_usuario = :id_usuario LIMIT 1');
	$stmtAtualizado->execute([':id_usuario' => (int) $usuario['id_usuario']]);
	$atualizadoEm = $stmtAtualizado->fetchColumn();

	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (!empty($_SESSION['id_usuario']) && (int) $_SESSION['id_usuario'] === (int) $usuario['id_usuario']) {
		$_SESSION['senha_atualizada_em'] = $atualizadoEm;
		session_regenerate_id(true);
	}

	registrarAuditoria($pdo, (int) $usuario['id_usuario'], 'alteracao_senha');

	echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
} catch (\PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}