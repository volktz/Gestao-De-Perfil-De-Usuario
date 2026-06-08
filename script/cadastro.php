<?php
// Captura dos campos do formulário em variáveis correspondentes
$nomeCompleto = isset($_POST['nome_completo'])
$email = isset($_POST['email'])
$telefone = isset($_POST['telefone'])
$senha = isset($_POST['senha'])
$confirmarSenha = isset($_POST['confirmar_senha'])


$host = 'localhost';
$db   = 'perfil_de_usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4';

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

// $sql = "UPDATE preferencia_usuario 
//                 SET
//                 alertas_sistema = :alertas_sistema, 
//                 emails_seguranca = :emails_seguranca, 
//                 emails_marketing = :emails_marketing, 
//                 pesquisa_opiniao = :pesquisa_opiniao, 
//                 atualizado_em = NOW()
//             WHERE id_usuario = :id_usuario";

//     $stmt = $pdo->prepare($sql);

//     // 4. Executa passando os valores na ordem correta
//     $stmt->execute([
//         ':id_usuario' => $id_usuario_logado,
//         ':alertas_sistema' => $alertas_sistema,
//         ':emails_seguranca' => $emails_seguranca,
//         ':emails_marketing' => $emails_marketing,
//         ':pesquisa_opiniao' => $pesquisa_opiniao
//     ]);

?>