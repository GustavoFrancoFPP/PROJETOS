<?php
session_start();

// Conexão com banco de dados
$servidor = "localhost";
$usuario = "root";
$senha = "senaisp";
$banco = "academia";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}
$conexao->set_charset("utf8");

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if ($action == 'login') {
        // LOGIN
        $sql = "SELECT * FROM login WHERE nome_usuario = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Verificar senha (usando SHA256 para compatibilidade)
            $senha_hash = hash('sha256', $senha);
            
            if (hash_equals($usuario['senha_usuario'], $senha_hash)) {
                $_SESSION['usuario_id'] = $usuario['id_cliente'] ?? $usuario['id_funcionario'];
                $_SESSION['usuario_nome'] = $email;
                $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
                
                // Redirecionar baseado no tipo de usuário
                if ($usuario['tipo_usuario'] == 'funcionario') {
                    header('Location: painel_admin.php');
                } else {
                    header('Location: inicio.html');
                }
                exit();
            } else {
                $erro = "Senha incorreta!";
            }
        } else {
            $erro = "Usuário não encontrado!";
        }
        
    } elseif ($action == 'cadastro') {
        // CADASTRO
        $cpf = $_POST['cpf'];
        $endereco = $_POST['endereco'];
        
        // Verificar se email já existe
        $sql_check = "SELECT * FROM login WHERE nome_usuario = ?";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $erro = "Email já cadastrado!";
        } else {
            // Inserir cliente
            $sql_cliente = "INSERT INTO cliente (nome_cliente, email, endereco, telefone, genero, cpf) 
                           VALUES (?, ?, ?, '00000000000', 'Outro', ?)";
            $stmt_cliente = $conexao->prepare($sql_cliente);
            $nome_cliente = explode('@', $email)[0];
            $stmt_cliente->bind_param("ssss", $nome_cliente, $email, $endereco, $cpf);
            
            if ($stmt_cliente->execute()) {
                $id_cliente = $conexao->insert_id;
                
                // Inserir login
                $senha_hash = hash('sha256', $senha);
                
                // Definir tipo de usuário baseado no email
                $tipo_usuario = 'cliente';
                if (strpos($email, '@techfit.com') !== false || stripos($email, 'admin') !== false) {
                    $tipo_usuario = 'funcionario';
                }
                
                $sql_login = "INSERT INTO login (nome_usuario, senha_usuario, tipo_usuario, id_cliente) 
                             VALUES (?, ?, ?, ?)";
                $stmt_login = $conexao->prepare($sql_login);
                $stmt_login->bind_param("sssi", $email, $senha_hash, $tipo_usuario, $id_cliente);
                
                if ($stmt_login->execute()) {
                    $_SESSION['usuario_id'] = $id_cliente;
                    $_SESSION['usuario_nome'] = $email;
                    $_SESSION['tipo_usuario'] = $tipo_usuario;
                    
                    if ($tipo_usuario == 'funcionario') {
                        header('Location: painel_admin.php');
                    } else {
                        header('Location: inicio.html');
                    }
                    exit();
                } else {
                    $erro = "Erro ao criar usuário!";
                }
            } else {
                $erro = "Erro ao cadastrar cliente!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>TECHFIT - Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      background: #f7f7f7;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #333;
    }

    .auth {
      background: #fff;
      width: 400px;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .tabs {
      display: flex;
      margin-bottom: 20px;
      border-bottom: 2px solid #eee;
    }

    .tab {
      flex: 1;
      padding: 12px;
      background: none;
      border: none;
      cursor: pointer;
      font-weight: bold;
      font-size: 16px;
      color: #666;
      transition: 0.3s;
    }

    .tab.active {
      color: #000;
      border-bottom: 3px solid #000;
    }

    .form {
      display: none;
      flex-direction: column;
      gap: 12px;
    }

    .form.active {
      display: flex;
    }

    h1 {
      font-size: 22px;
      margin-bottom: 8px;
    }

    p {
      font-size: 14px;
      margin-bottom: 15px;
      color: #555;
    }

    label {
      font-size: 14px;
      font-weight: 500;
    }

    input {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
    }

    input:focus {
      border-color: #000;
    }

    button[type="submit"] {
      background: #000;
      color: #fff;
      padding: 12px;
      border-radius: 4px;
      font-size: 15px;
      margin-top: 10px;
      cursor: pointer;
      transition: 0.3s;
    }

    button[type="submit"]:hover {
      background: #333;
    }

    .admin-info {
      background: #e8f4fd;
      padding: 10px;
      border-radius: 4px;
      border-left: 4px solid #3182ce;
      font-size: 12px !important;
      margin-top: 15px !important;
    }

    .admin-info code {
      background: #d1e7ff;
      padding: 2px 4px;
      border-radius: 3px;
      font-family: monospace;
    }

    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
      text-align: center;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="auth">
    <?php if (isset($erro)): ?>
      <div class="error-message">
        <?php echo $erro; ?>
      </div>
    <?php endif; ?>

    <div class="tabs">
      <button class="tab active" data-tab="login">Login</button>
      <button class="tab" data-tab="cad">Cadastro</button>
    </div>

    <form id="loginForm" class="form active" method="POST">
      <h1>Login</h1>
      <p>Digite seus dados de acesso:</p>

      <input type="hidden" name="action" value="login">

      <label for="login_email">Email:</label>
      <input id="login_email" name="email" type="email" placeholder="Digite seu email" required>

      <label for="login_senha">Senha:</label>
      <input id="login_senha" name="senha" type="password" placeholder="Digite sua senha" required>

      <button type="submit">Acessar</button>
    </form>

    <form id="cadForm" class="form" method="POST">
      <h1>Cadastro</h1>
      <p>Preencha seus dados para se cadastrar:</p>

      <input type="hidden" name="action" value="cadastro">

      <label for="cad_email">Email:</label>
      <input id="cad_email" name="email" type="email" placeholder="Digite seu email" required>

      <label for="cad_senha">Senha:</label>
      <input id="cad_senha" name="senha" type="password" placeholder="Digite sua senha" required>
      
      <label for="cad_cpf">CPF:</label>
      <input id="cad_cpf" name="cpf" type="text" placeholder="Digite seu CPF" required>

      <label for="cad_endereco">Endereço:</label>
      <input id="cad_endereco" name="endereco" type="text" placeholder="Digite seu endereço" required>

      <button type="submit">Cadastrar</button>
      
      <p class="admin-info">
        <strong>💡 Dica:</strong> Use email com <code>@techfit.com</code> ou contendo "admin" para acesso administrativo
      </p>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab');
        const forms = document.querySelectorAll('.form');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Remove active de todas as tabs e forms
                tabs.forEach(t => t.classList.remove('active'));
                forms.forEach(f => f.classList.remove('active'));
                
                // Adiciona active na tab e form clicados
                this.classList.add('active');
                document.getElementById(tabName + 'Form').classList.add('active');
            });
        });

        // Formatação do CPF
        const cpfInput = document.getElementById('cad_cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2')
                               .replace(/(\d{3})(\d)/, '$1.$2')
                               .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                }
                e.target.value = value;
            });
        }
    });
  </script>
</body>
</html>