<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    echo json_encode([
        'id_usuario' => null,
        'nome_completo' => '',
        'email' => '',
        'telefone' => '',
        'avatar_url' => null,
    ]);
    exit;
}

$idUsuario = obterIdUsuarioAtual($pdo);
if ($idUsuario <= 0) {
    echo json_encode([
        'id_usuario' => null,
        'nome_completo' => '',
        'email' => '',
        'telefone' => '',
        'avatar_url' => null,
    ]);
    exit;
}

$stmt = $pdo->prepare('SELECT id_usuario, nome_completo, email, telefone, avatar_url FROM usuario WHERE id_usuario = :id_usuario');
$stmt->execute([':id_usuario' => $idUsuario]);
$usuario = $stmt->fetch() ?: [
    'id_usuario' => null,
    'nome_completo' => '',
    'email' => '',
    'telefone' => '',
    'avatar_url' => null,
];

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

echo json_encode($usuario);

