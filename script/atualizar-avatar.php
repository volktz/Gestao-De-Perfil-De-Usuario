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

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    jsonError('Erro de conexão com o banco.', 500);
}

$idUsuario = obterIdUsuarioAtual($pdo);
if ($idUsuario <= 0) {
    jsonError('Nenhum usuário encontrado.', 401);
}

if (isset($_POST['clear']) && $_POST['clear'] === '1') {
    $stmt = $pdo->prepare('UPDATE usuario SET avatar_url = NULL WHERE id_usuario = :id_usuario');
    $stmt->execute([':id_usuario' => $idUsuario]);
    registrarAuditoria($pdo, $idUsuario, 'remocao_avatar');
    echo json_encode(['success' => true, 'avatar_url' => null]);
    exit;
}

if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    jsonError('Nenhuma imagem válida foi enviada.');
}

$imagem = file_get_contents($_FILES['avatar']['tmp_name']);
if ($imagem === false || $imagem === '') {
    jsonError('Não foi possível ler a imagem enviada.');
}

$imageInfo = getimagesizefromstring($imagem);
if ($imageInfo === false || empty($imageInfo['mime']) || strpos($imageInfo['mime'], 'image/') !== 0) {
    jsonError('O arquivo enviado precisa ser uma imagem.');
}

$stmt = $pdo->prepare('UPDATE usuario SET avatar_url = :avatar_url WHERE id_usuario = :id_usuario');
$stmt->bindValue(':avatar_url', $imagem, PDO::PARAM_LOB);
$stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
$stmt->execute();

registrarAuditoria($pdo, $idUsuario, 'atualizacao_avatar');

echo json_encode([
    'success' => true,
    'avatar_url' => 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imagem),
]);
