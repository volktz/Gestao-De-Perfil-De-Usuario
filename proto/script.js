/* ===== FUNÇÕES DO FORMULÁRIO DE PERFIL ===== */

/**
 * Função para editar a foto de perfil
 * Esta função é chamada quando o usuário clica no ícone de editar (lápis)
 */
function editPhoto() {
  // Cria um elemento input do tipo file (selecionador de arquivos)
  const fileInput = document.createElement('input');
  // Define o tipo como file (arquivo)
  fileInput.type = 'file';
  // Define que o input só aceita imagens
  fileInput.accept = 'image/*';
  
  // Quando o usuário selecionar uma arquivo
  fileInput.onchange = (e) => {
    // Obtém o arquivo selecionado
    const file = e.target.files[0];
    // Verifica se um arquivo foi selecionado
    if (file) {
      // Cria um leitor de arquivo
      const reader = new FileReader();
      
      // Quando o arquivo for lido
      reader.onload = (event) => {
        // Obtém a imagem de perfil
        const photoElement = document.querySelector('.profile-photo');
        // Define o src da imagem como o arquivo selecionado
        photoElement.src = event.target.result;
        // Mostra uma mensagem de sucesso
        alert('Foto atualizada com sucesso!');
      };
      
      // Lê o arquivo como uma URL de dados
      reader.readAsDataURL(file);
    }
  };
  
  // Simula um clique no input file para abrir o selecionador de arquivo
  fileInput.click();
}

/**
 * Função para remover a foto de perfil
 * Esta função é chamada quando o usuário clica em "Remover Foto Atual"
 */
function removePhoto() {
  // Pergunta ao usuário se tem certeza
  const confirmacao = confirm('Tem certeza que deseja remover a foto?');
  
  // Se o usuário confirmou
  if (confirmacao) {
    // Obtém a imagem de perfil
    const photoElement = document.querySelector('.profile-photo');
    // Define a imagem como uma placeholder padrão
    photoElement.src = 'https://via.placeholder.com/150?text=Sem+Foto';
    // Mostra uma mensagem de sucesso
    alert('Foto removida com sucesso!');
  }
}

/**
 * Função para alterar a senha de acesso
 * Esta função é chamada quando o usuário clica em "Alterar Senha de Acesso"
 */
function alterarSenha() {
  // Solicita a senha atual ao usuário
  const senhaAtual = prompt('Digite sua senha atual:');
  
  // Verifica se o usuário cancelou ou deixou em branco
  if (senhaAtual === null || senhaAtual.trim() === '') {
    // Cancela a operação
    return;
  }
  
  // Solicita a nova senha
  const novaSenha = prompt('Digite a nova senha:');
  
  // Verifica se o usuário cancelou ou deixou em branco
  if (novaSenha === null || novaSenha.trim() === '') {
    // Cancela a operação
    return;
  }
  
  // Verifica se a senha tem pelo menos 6 caracteres
  if (novaSenha.length < 6) {
    // Mostra erro de validação
    alert('A senha deve ter pelo menos 6 caracteres!');
    // Cancela a operação
    return;
  }
  
  // Solicita a confirmação da nova senha
  const confirmaSenha = prompt('Confirme a nova senha:');
  
  // Verifica se as senhas coincidem
  if (novaSenha !== confirmaSenha) {
    // Mostra erro
    alert('As senhas não coincidem!');
    // Cancela a operação
    return;
  }
  
  // Se tudo deu certo, mostra mensagem de sucesso
  alert('Senha alterada com sucesso!');
}

/**
 * Função para abrir as preferências e notificações
 * Esta função é chamada quando o usuário clica em "Preferências e Notificações"
 */
function abrirPreferencias() {
  // Obtém o container do perfil
  const profileContainer = document.querySelector('.profile-container:not(#preferences-container)');
  // Obtém o container de preferências
  const preferencesContainer = document.getElementById('preferences-container');
  
  // Oculta o container do perfil
  profileContainer.style.display = 'none';
  // Mostra o container de preferências
  preferencesContainer.style.display = 'block';
}

/**
 * Função para voltar do container de preferências para o perfil
 * Esta função é chamada quando o usuário clica no botão de voltar nas preferências
 */
function voltarDoPreferencias() {
  // Obtém o container do perfil
  const profileContainer = document.querySelector('.profile-container:not(#preferences-container)');
  // Obtém o container de preferências
  const preferencesContainer = document.getElementById('preferences-container');
  
  // Mostra o container do perfil
  profileContainer.style.display = 'block';
  // Oculta o container de preferências
  preferencesContainer.style.display = 'none';
}

/**
 * Função para salvar as alterações do formulário
 * Esta função é chamada quando o usuário clica em "Salvar Alterações"
 */
function salvarAlteracoes() {
  // Obtém o valor do campo de nome
  const nome = document.getElementById('nome').value;
  // Obtém o valor do campo de email
  const email = document.getElementById('email').value;
  // Obtém o valor do campo de telefone
  const telefone = document.getElementById('telefone').value;
  
  // Valida se o nome está preenchido
  if (!nome.trim()) {
    // Mostra erro
    alert('Por favor, preencha o nome!');
    // Para a execução da função
    return;
  }
  
  // Valida se o email está preenchido
  if (!email.trim()) {
    // Mostra erro
    alert('Por favor, preencha o email!');
    // Para a execução da função
    return;
  }
  
  // Expresão regular para validar o formato do email
  const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  // Valida se o email tem um formato válido
  if (!regexEmail.test(email)) {
    // Mostra erro
    alert('Por favor, insira um email válido!');
    // Para a execução da função
    return;
  }
  
  // Valida se o telefone está preenchido
  if (!telefone.trim()) {
    // Mostra erro
    alert('Por favor, preencha o telefone!');
    // Para a execução da função
    return;
  }
  
  // Se todas as validações passaram, salva os dados
  // Aqui você enviaria os dados para um servidor
  console.log('Dados a salvar:', {
    // Exibe o nome no console
    nome: nome,
    // Exibe o email no console
    email: email,
    // Exibe o telefone no console
    telefone: telefone
  });
  
  // Mostra mensagem de sucesso
  alert('Alterações salvas com sucesso!');
  
  // Aqui você poderia enviar uma requisição POST para um servidor
  // Exemplo:
  // fetch('/api/perfil', {
  //   method: 'POST',
  //   headers: {
  //     'Content-Type': 'application/json'
  //   },
  //   body: JSON.stringify({
  //     nome: nome,
  //     email: email,
  //     telefone: telefone
  //   })
  // })
}

/**
 * Função para inicializar a página quando ela carregar
 * É executada automaticamente quando a página carrega
 */
document.addEventListener('DOMContentLoaded', function() {
  // Obtém o formulário
  const form = document.querySelector('.profile-form');
  
  // Adiciona um event listener para quando o formulário for enviado
  form.addEventListener('submit', function(event) {
    // Previne o comportamento padrão do formulário (recarregar a página)
    event.preventDefault();
    // Chama a função para salvar as alterações
    salvarAlteracoes();
  });
});
