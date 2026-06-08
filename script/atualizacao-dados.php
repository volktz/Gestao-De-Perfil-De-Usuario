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

$nomeCompleto = isset($_POST['nome_completo']) ? trim($_POST['nome_completo']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';

try {
	$pdo = new PDO($dsn, $user, $pass, $options);
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
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}