<?php
require_once __DIR__ . '/UsuarioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['user_nome'] === 'techfit') {
        header('Location: painel_administrativo.php');
        exit;
    } else {
        header('Location: index.php'); 
        exit;
    }
}

$mensagem_erro = '';
$controller = new UsuarioController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeUsuario = trim($_POST['nome_usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($nomeUsuario) || empty($senha)) {
        $mensagem_erro = 'Por favor, preencha todos os campos.';
    } else {
        if ($controller->login($nomeUsuario, $senha)) {
            // Login BEM SUCEDIDO
            
            // Regra: Se o nome de usuário for 'techfit', redireciona para o painel admin
            if ($nomeUsuario === 'techfit') {
                header('Location: painel_administrativo.php');
                exit;
            } else {
                // Outros usuários
                header('Location: index.php');
                exit;
            }
        } else {
            // Login FALHOU
            $mensagem_erro = 'Nome de usuário ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TECHFIT - Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <header class="techfit-header">...</header>

  <main>
    <div class="auth">
      <?php if ($mensagem_erro): ?>
          <p style="color: red; text-align: center; font-weight: bold; padding-bottom: 10px;">
              <?php echo $mensagem_erro; ?>
          </p>
      <?php endif; ?>

      <div class="tabs">
        <a href="#" class="tab active" data-tab="login">LOGIN</a>
        <a href="cadastro.php" class="tab" data-tab="cadastro">CADASTRO</a> 
      </div>

      <form id="loginForm" class="form active" method="POST" action="login.php">
        <h1>Login de Aluno</h1>
        <p>Acesse sua conta para gerenciar seus treinos e planos:</p>

        <div class="form-group">
          <label for="logEmail">Nome de Usuário:</label>
          <input id="logEmail" name="nome_usuario" type="text" placeholder="Digite seu nome de usuário" required>
        </div>

        <div class="form-group">
          <label for="logSenha">Senha:</label>
          <input id="logSenha" name="senha" type="password" placeholder="Digite sua senha" required>
        </div>

        <button type="submit">Entrar</button>

        <div class="admin-link">
          <a href="#" id="adminLoginLink">Acesso Administrativo</a>
        </div>
      </form>
      
      <form id="adminForm" class="form" method="POST" action="login.php">
        <h1>Acesso Administrativo</h1>
        <p>Área restrita para administradores (Usuário: techfit):</p>

        <div class="form-group">
          <label for="adminUsuario">Usuário Admin:</label>
          <input id="adminUsuario" name="nome_usuario" type="text" placeholder="Digite o usuário admin" required>
        </div>

        <div class="form-group">
          <label for="adminSenha">Senha Admin:</label>
          <input id="adminSenha" name="senha" type="password" placeholder="Digite a senha admin" required>
        </div>

        <button type="submit">Acessar Painel Admin</button>

        <div class="admin-link">
          <a href="#" id="voltarLoginLink">← Voltar para Login Normal</a>
        </div>
      </form>

    </div>
  </main>

  <footer class="main-footer">...</footer>
  
  <script src="login.js"></script>
</body>
</html>