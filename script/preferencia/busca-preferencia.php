<?php 

require_once __DIR__ . '/../auth.php';

$host = 'localhost';
$db   = 'perfil_de_usuario';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

require_once __DIR__ . '/../config.php';

try {
    $pdo = getPdo();
} catch (PDOException $e) {
    // Em produção, salve o erro em um log em vez de dar echo
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$id_usuario_logado = obterIdUsuarioAtual($pdo);

if ($id_usuario_logado <= 0) {
    $preferencias = [];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($preferencias);
    exit;
}

$querySELECT = "SELECT alertas_sistema, emails_seguranca, emails_marketing, pesquisa_opiniao
                FROM preferencia_usuario
                WHERE id_usuario = :id_usuario";

$stmt = $pdo->prepare($querySELECT);
$stmt->execute([
    ':id_usuario' => $id_usuario_logado,
]);

$preferencias = $stmt->fetch();

if ($preferencias === false) {
    $preferencias = [];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($preferencias);

?>