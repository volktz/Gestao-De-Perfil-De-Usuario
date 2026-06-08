<?php
$preferencias = [
     isset($_POST['alertas_sistema']),
     isset($_POST['emails_seguranca']),
     isset($_POST['novidades_ofertas']),
     isset($_POST['pesquisas_opiniao']),
];

for ($i = 0, $total = count($preferencias); $i < $total; $i++) {
     $preferencias[$i] = $preferencias[$i] ? 1 : 0;
}

[$pref1, $pref2, $pref3, $pref4] = $preferencias;


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

// 2. Os valores que você quer inserir (geralmente vêm de um formulário $_POST)
$alertas_sistema = $pref1;
$emails_seguranca = $pref2;
$emails_marketing = $pref3;
$pesquisa_opiniao = $pref4;

try {
    // 3. O comando SQL incluindo todas as colunas obrigatórias
    // O id_preferencia não entra aqui pois é AUTO_INCREMENT
    $sql = "UPDATE preferencia_usuario 
                SET
                alertas_sistema = :alertas_sistema, 
                emails_seguranca = :emails_seguranca, 
                emails_marketing = :emails_marketing, 
                pesquisa_opiniao = :pesquisa_opiniao, 
                atualizado_em = NOW()
            WHERE id_usuario = :id_usuario";

    $stmt = $pdo->prepare($sql);

    // 4. Executa passando os valores na ordem correta
    $stmt->execute([
        ':id_usuario' => $id_usuario_logado,
        ':alertas_sistema' => $alertas_sistema,
        ':emails_seguranca' => $emails_seguranca,
        ':emails_marketing' => $emails_marketing,
        ':pesquisa_opiniao' => $pesquisa_opiniao
    ]);

    echo "<script>alert('Configurações salvas com sucesso!'); window.location.href = '../../html/gestao-de-perfil.html';</script>";

} catch (PDOException $e) {
    // Caso ocorra erro (ex: id_usuario não existe na tabela pai)
    echo "<script>alert('Erro ao salvar preferências: " . addslashes($e->getMessage()) . "'); history.back();</script>";
}

?>