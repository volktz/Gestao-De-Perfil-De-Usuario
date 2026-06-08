<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit.php';

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexao com o banco.']);
    exit;
}

try {
    $idUsuario = obterIdUsuarioAtual($pdo);

    if ($idUsuario <= 0) {
        throw new RuntimeException('Nenhum usuario encontrado.');
    }

    if (isset($_POST['clear']) && $_POST['clear'] === '1') {
        $stmt = $pdo->prepare("UPDATE usuario SET avatar_url = NULL WHERE id_usuario = :id_usuario");
        $stmt->execute([':id_usuario' => (int) $idUsuario]);

        registrarAuditoria($pdo, (int) $idUsuario, 'remocao_avatar');

        echo json_encode(['success' => true, 'avatar_url' => null]);
        exit;
    }

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Nenhuma imagem valida foi enviada.');
    }

    $arquivoTemporario = $_FILES['avatar']['tmp_name'];
    $imagem = file_get_contents($arquivoTemporario);

    if ($imagem === false || $imagem === '') {
        throw new RuntimeException('Nao foi possivel ler a imagem enviada.');
    }

    $imageInfo = getimagesizefromstring($imagem);

    if ($imageInfo === false || empty($imageInfo['mime']) || strpos($imageInfo['mime'], 'image/') !== 0) {
        throw new RuntimeException('O arquivo enviado precisa ser uma imagem.');
    }

    $mimeType = $imageInfo['mime'];

    $stmt = $pdo->prepare("UPDATE usuario SET avatar_url = :avatar_url WHERE id_usuario = :id_usuario");
    $stmt->bindValue(':avatar_url', $imagem, PDO::PARAM_LOB);
    $stmt->bindValue(':id_usuario', (int) $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    registrarAuditoria($pdo, (int) $idUsuario, 'atualizacao_avatar');

    echo json_encode([
        'success' => true,
        'avatar_url' => 'data:' . $mimeType . ';base64,' . base64_encode($imagem),
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
