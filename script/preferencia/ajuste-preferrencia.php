<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../audit.php';
require_once __DIR__ . '/../config.php';

$preferencias = [
    'alertas_sistema' => isset($_POST['alertas_sistema']) ? 1 : 0,
    'emails_seguranca' => isset($_POST['emails_seguranca']) ? 1 : 0,
    'emails_marketing' => isset($_POST['novidades_ofertas']) ? 1 : 0,
    'pesquisa_opiniao' => isset($_POST['pesquisas_opiniao']) ? 1 : 0,
];

try {
    $pdo = getPdo();
    $idUsuario = obterIdUsuarioAtual($pdo);

    if ($idUsuario <= 0) {
        throw new RuntimeException('Nenhum usuário encontrado para atualizar as preferências.');
    }

    $stmt = $pdo->prepare(
        'UPDATE preferencia_usuario
            SET alertas_sistema = :alertas_sistema,
                emails_seguranca = :emails_seguranca,
                emails_marketing = :emails_marketing,
                pesquisa_opiniao = :pesquisa_opiniao,
                atualizado_em = NOW()
          WHERE id_usuario = :id_usuario'
    );

    $stmt->execute($preferencias + [':id_usuario' => $idUsuario]);
    registrarAuditoria($pdo, $idUsuario, 'atualizacao_preferencias');

    echo "<script>alert('Configurações salvas com sucesso!'); window.location.href = '../../html/gestao-de-perfil.html';</script>";
} catch (Throwable $e) {
    echo "<script>alert('Erro ao salvar preferências: " . addslashes($e->getMessage()) . "'); history.back();</script>";
}

?>