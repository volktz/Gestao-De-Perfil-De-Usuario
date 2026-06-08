<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/login.html');
    exit;
}

session_start();

require_once __DIR__ . '/audit.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';

header('Content-Type: application/json; charset=utf-8');

if ($email === '' || $senha === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Preencha e-mail e senha.'
    ]);
    exit;
}

$host = 'localhost';
$db = 'perfil_de_usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=3307;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->prepare('SELECT id_usuario, nome_completo, email, senha_hash FROM usuario WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'E-mail ou senha inválidos.'
        ]);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
    $_SESSION['nome_completo'] = $usuario['nome_completo'];
    $_SESSION['email'] = $usuario['email'];

    registrarAuditoria($pdo, (int) $usuario['id_usuario'], 'login');

    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso.',
        'redirect' => '../html/gestao-de-perfil.html'
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao autenticar. Tente novamente.'
    ]);
    exit;
}