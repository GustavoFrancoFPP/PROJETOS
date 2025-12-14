<?php
require_once __DIR__ . '/../controller/UsuarioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['tipo_usuario'] === 'funcionario') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard_aluno.php');
    }
    exit;
}

$mensagem_erro = '';
$mensagem_sucesso = '';
$controller = new UsuarioController();

// PROCESSAR CADASTRO
if (isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $genero = $_POST['genero'] ?? '';
    $cpf = trim($_POST['cpf'] ?? '');

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($endereco) || empty($telefone) || empty($genero) || empty($cpf)) {
        $mensagem_erro = 'Por favor, preencha todos os campos.';
    } elseif ($senha !== $confirmarSenha) {
        $mensagem_erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $mensagem_erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $resultado = $controller->cadastrar($nome, $email, $senha, $endereco, $telefone, $genero, $cpf);
        
        if (is_string($resultado) && strpos($resultado, 'Erro') === false) {
            $mensagem_sucesso = 'Cadastro realizado com sucesso! Seu usuário é: <strong>' . $resultado . '</strong>';
            $_POST = array();
        } else {
            $mensagem_erro = $resultado;
        }
    }
}

// PROCESSAR LOGIN
if (isset($_POST['logar'])) {
    $nomeUsuario = trim($_POST['nome_usuario'] ?? '');
    $senha = $_POST['senha_login'] ?? '';

    if (empty($nomeUsuario) || empty($senha)) {
        $mensagem_erro = 'Por favor, preencha todos os campos.';
    } else {
        $loginResult = $controller->login($nomeUsuario, $senha);
        
        if ($loginResult) {
            // Login bem-sucedido - redireciona baseado no tipo de usuário
            if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'funcionario') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard_aluno.php');
            }
            exit;
        } else {
            $mensagem_erro = 'Nome de usuário ou senha inválidos. Verifique suas credenciais.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TECHFIT - Login e Cadastro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/login.css">
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
</head>
<body>
  <header class="techfit-header">
    <div class="header-container">
      <a href="inicio.html" class="header-logo">
        <div class="logo-text">TECH<span>FIT</span></div>
      </a>
    </div>
  </header>

  <main>
    <div class="auth-container">
      <div class="auth">
        <?php if ($mensagem_sucesso): ?>
            <div style="color: green; text-align: center; font-weight: bold; padding: 10px; background: rgba(0,255,0,0.1); border-radius: 5px; margin-bottom: 15px;">
                <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem_erro): ?>
            <div style="color: red; text-align: center; font-weight: bold; padding: 10px; background: rgba(255,0,0,0.1); border-radius: 5px; margin-bottom: 15px;">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
          <button type="button" class="tab active" data-tab="login">LOGIN</button>
          <button type="button" class="tab" data-tab="cadastro">CADASTRO</button>
        </div>

        <!-- FORMULÁRIO DE LOGIN -->
        <form id="loginForm" class="form active" method="POST" action="login.php">
          <h1>Login</h1>
          <p>Acesse sua conta TECHFIT:</p>

          <div class="form-group">
            <label for="nome_usuario">Nome de Usuário:</label>
            <input id="nome_usuario" name="nome_usuario" type="text" placeholder="Digite seu nome de usuário" required>
          </div>

          <div class="form-group">
            <label for="senha_login">Senha:</label>
            <input id="senha_login" name="senha_login" type="password" placeholder="Digite sua senha" required>
          </div>

          <button type="submit" name="logar">Entrar</button>
        </form>

        <!-- FORMULÁRIO DE CADASTRO -->
        <form id="cadastroForm" class="form" method="POST" action="login.php">
          <h1>Cadastro</h1>
          <p>Crie sua conta TECHFIT:</p>

          <div class="form-group">
            <label for="nome">Nome Completo:</label>
            <input id="nome" name="nome" type="text" placeholder="Digite seu nome completo" value="<?php echo $_POST['nome'] ?? ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email:</label>
            <input id="email" name="email" type="email" placeholder="Digite seu email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="cpf">CPF:</label>
            <input id="cpf" name="cpf" type="text" placeholder="000.000.000-00" value="<?php echo $_POST['cpf'] ?? ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input id="telefone" name="telefone" type="text" placeholder="(00) 00000-0000" value="<?php echo $_POST['telefone'] ?? ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="endereco">Endereço:</label>
            <input id="endereco" name="endereco" type="text" placeholder="Digite seu endereço completo" value="<?php echo $_POST['endereco'] ?? ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="genero">Gênero:</label>
            <select id="genero" name="genero" required>
              <option value="">Selecione</option>
              <option value="Masculino" <?php echo ($_POST['genero'] ?? '') === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
              <option value="Feminino" <?php echo ($_POST['genero'] ?? '') === 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
              <option value="Outro" <?php echo ($_POST['genero'] ?? '') === 'Outro' ? 'selected' : ''; ?>>Outro</option>
            </select>
          </div>

          <div class="form-group">
            <label for="senha">Senha:</label>
            <input id="senha" name="senha" type="password" placeholder="Digite sua senha (mín. 6 caracteres)" required>
          </div>

          <div class="form-group">
            <label for="confirmar_senha">Confirmar Senha:</label>
            <input id="confirmar_senha" name="confirmar_senha" type="password" placeholder="Confirme sua senha" required>
          </div>

          <button type="submit" name="cadastrar">Cadastrar</button>
        </form>
      </div>
    </div>
    <form id="cadForm" class="form" method="POST" action="login.php">
    <h2 class="title">Criar Conta</h2>
    
    <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="nome" id="cadNome" placeholder="Nome Completo" required>
    </div>

    <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" id="cadEmail" placeholder="Email" required>
    </div>

    <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="senha" id="cadSenha" placeholder="Senha" required>
    </div>
    
    <button type="submit" name="cadastrar" class="btn">Cadastrar</button>
</form>

<script src="assets/js/js/login.js"></script>
  </main>

  <footer class="main-footer">
    <div class="container">
      <div class="footer-brand">
        <div class="logo-text">TECH<span>FIT</span></div>
        <p>Sua jornada fitness começa aqui</p>
      </div>
      <div class="footer-copyright">
        &copy; 2024 TECHFIT. Todos os direitos reservados.
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tabs = document.querySelectorAll('.tab');
      const forms = document.querySelectorAll('.form');

      // Sistema de tabs
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          forms.forEach(f => f.classList.remove('active'));

          tab.classList.add('active');
          document.getElementById(tab.dataset.tab + 'Form').classList.add('active');
        });
      });

      // Máscara para CPF
      document.getElementById('cpf')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substring(0, 11);
        
        if (value.length > 9) {
          value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        } else if (value.length > 6) {
          value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
        } else if (value.length > 3) {
          value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
        }
        
        e.target.value = value;
      });

      // Máscara para telefone
      document.getElementById('telefone')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substring(0, 11);
        
        if (value.length > 10) {
          value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 6) {
          value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length > 2) {
          value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length > 0) {
          value = value.replace(/(\d{0,2})/, '($1');
        }
        
        e.target.value = value;
      });
    });
  </script>
</body>
</html>