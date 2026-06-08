<?php

require_once __DIR__ . '/auth.php';

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

try {
	$pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
	throw new \PDOException($e->getMessage(), (int) $e->getCode());
}

$idUsuario = obterIdUsuarioAtual($pdo);

if ($idUsuario <= 0) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		'id_usuario' => null,
		'nome_completo' => '',
		'email' => '',
		'telefone' => '',
		'avatar_url' => null,
	]);
	exit;
}

$sql = 'SELECT id_usuario, nome_completo, email, telefone, avatar_url
		FROM usuario
		WHERE id_usuario = :id_usuario';

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $idUsuario]);
$usuario = $stmt->fetch();

if ($usuario === false) {
	$usuario = [
		'id_usuario' => null,
		'nome_completo' => '',
		'email' => '',
		'telefone' => '',
		'avatar_url' => null,
	];
	
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($usuario);
	exit;
}

if (!empty($usuario['avatar_url'])) {
	$avatarBlob = $usuario['avatar_url'];

	if (is_resource($avatarBlob)) {
		$avatarBlob = stream_get_contents($avatarBlob);
	}

	if (is_string($avatarBlob) && $avatarBlob !== '') {
		$mimeType = 'image/jpeg';
		$imageInfo = @getimagesizefromstring($avatarBlob);

		if (is_array($imageInfo) && !empty($imageInfo['mime']) && strpos($imageInfo['mime'], 'image/') === 0) {
			$mimeType = $imageInfo['mime'];
		}

		$usuario['avatar_url'] = 'data:' . $mimeType . ';base64,' . base64_encode($avatarBlob);
	} else {
		$usuario['avatar_url'] = null;
	}
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($usuario);

