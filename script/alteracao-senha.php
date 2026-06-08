<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Metodo nao permitido.']);
	exit;
}

$host = 'localhost';
$db   = 'perfil_de_usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";

$options = [
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false,
];

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

if (strlen($senhaNova) < 6) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres.']);
	exit;
}

try {
	$pdo = new PDO($dsn, $user, $pass, $options);

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

	registrarAuditoria($pdo, (int) $usuario['id_usuario'], 'alteracao_senha');

	echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
} catch (\PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}