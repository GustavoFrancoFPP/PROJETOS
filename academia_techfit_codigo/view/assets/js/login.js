document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab');
  const forms = document.querySelectorAll('.form');
  const adminLoginLink = document.getElementById('adminLoginLink');
  const voltarLoginLink = document.getElementById('voltarLoginLink');

  // Sistema de tabs
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      forms.forEach(f => f.classList.remove('active'));

      tab.classList.add('active');
      document.getElementById(tab.dataset.tab + 'Form').classList.add('active');
    });
  });

  // Link para login admin
  adminLoginLink.addEventListener('click', (e) => {
    e.preventDefault();
    tabs.forEach(t => t.classList.remove('active'));
    forms.forEach(f => f.classList.remove('active'));
    document.getElementById('adminForm').classList.add('active');
  });

  // Voltar para login normal
  voltarLoginLink.addEventListener('click', (e) => {
    e.preventDefault();
    tabs.forEach(t => t.classList.remove('active'));
    forms.forEach(f => f.classList.remove('active'));
    document.getElementById('loginForm').classList.add('active');
    document.querySelector('.tab[data-tab="login"]').classList.add('active');
  });

  // Login normal - VERSÃO CORRIGIDA
  document.getElementById('loginForm').addEventListener('submit', e => {
    const usuario = document.getElementById('logEmail').value;
    const senha = document.getElementById('logSenha').value;
    
    // Validação básica
    if (!usuario || !senha) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos!');
        return;
    }
    // Se estiver tudo ok, deixa o formulário ser submetido normalmente para o PHP
  });

  // Cadastro - COMENTADO pois não existe no PHP atual
  /*
  document.getElementById('cadForm').addEventListener('submit', e => {
    e.preventDefault();
    const usuario = document.getElementById('cadUsuario').value;
    const senha = document.getElementById('cadSenha').value;
    const cpf = document.getElementById('cadCpf').value;
    const endereco = document.getElementById('cadEndereco').value;
    
    if (usuario && senha && cpf && endereco) {
      alert('Cadastro realizado com sucesso!');
      // Mudar para tab de login
      tabs.forEach(t => t.classList.remove('active'));
      forms.forEach(f => f.classList.remove('active'));
      document.getElementById('loginForm').classList.add('active');
      document.querySelector('.tab[data-tab="login"]').classList.add('active');
    }
  });
  */

  // Login admin - COMENTADO pois o PHP já faz essa validação
  /*
  document.getElementById('adminForm').addEventListener('submit', e => {
    e.preventDefault();
    const usuario = document.getElementById('adminUsuario').value;
    const senha = document.getElementById('adminSenha').value;
    
    // Credenciais do admin (em produção, isso deve ser verificado no backend)
    const adminCredentials = {
      usuario: 'admin',
      senha: 'admin123'
    };
    
    if (usuario === adminCredentials.usuario && senha === adminCredentials.senha) {
      alert('Login administrativo efetuado com sucesso!');
      // Redirecionar para painel admin (você pode criar admin.html)
      window.location.href = 'admin.html';
    } else {
      alert('Credenciais administrativas inválidas!');
    }
  });
  */

  // Máscara para CPF - COMENTADO pois não existe campo de cadastro no PHP atual
  /*
  document.getElementById('cadCpf')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      e.target.value = value;
    }
  });
  */
});