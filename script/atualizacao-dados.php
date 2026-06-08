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

try {
	$pdo = getPdo();
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Erro de conexao com o banco.']);
	exit;
}

$nomeCompleto = isset($_POST['nome_completo']) ? trim($_POST['nome_completo']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';

try {
	$idUsuario = obterIdUsuarioAtual($pdo);

	if ($idUsuario <= 0) {
		http_response_code(401);
		echo json_encode(['success' => false, 'message' => 'Usuario nao autenticado.']);
		exit;
	}

	if ($nomeCompleto === '' || $email === '') {
		http_response_code(400);
		echo json_encode(['success' => false, 'message' => 'Preencha nome e email.']);
		exit;
	}

	$sql = "UPDATE usuario
			SET nome_completo = :nome_completo,
				email = :email,
				telefone = :telefone,
				atualizado_em = NOW()
			WHERE id_usuario = :id_usuario";

	$stmt = $pdo->prepare($sql);
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
} catch (\PDOException $e) {
	http_response_code(500);

	// Log error to file for debugging
	$logDir = __DIR__ . '/logs';
	if (!is_dir($logDir)) {
		@mkdir($logDir, 0755, true);
	}

	$logFile = $logDir . '/errors.log';
	$logEntry = sprintf("[%s] %s in %s on line %d\nSQLSTATE: %s\n\n", date('c'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
	@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

	// Handle duplicate entry (unique constraint) gracefully
	$message = 'Erro ao salvar os dados.';
	$code = (string) $e->getCode();

	if ($code === '23000' || stripos($e->getMessage(), 'Duplicate') !== false) {
		$message = 'O e-mail informado já está em uso.';
	} else {
		// In non-prod, expose message to help debugging (safe because this is local dev)
		$message = $e->getMessage();
	}

	echo json_encode(['success' => false, 'message' => $message]);
}