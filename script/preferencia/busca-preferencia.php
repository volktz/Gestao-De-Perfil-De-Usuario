<?php 

$host = 'localhost';
$db   = 'perfil_de_usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4';

// Configurações do DSN (Data Source Name)
$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";

// Opções extras para segurança e performance
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna arrays associativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa Prepared Statements reais
    ];
    
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Em produção, salve o erro em um log em vez de dar echo
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$id_usuario_logado = 1;

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