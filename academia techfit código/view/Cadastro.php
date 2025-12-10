<?php
require_once __DIR__ . '/UsuarioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mensagem = '';
$controller = new UsuarioController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $genero = $_POST['genero'] ?? '';
    $cpf = trim($_POST['cpf'] ?? '');

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha) || empty($endereco) || empty($telefone) || empty($genero) || empty($cpf)) {
        $mensagem = "Erro: Por favor, preencha todos os campos.";
    } elseif ($senha !== $confirmarSenha) {
        $mensagem = "Erro: As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $mensagem = "Erro: A senha deve ter pelo menos 6 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Erro: E-mail inválido.";
    } else {
        $resultado = $controller->cadastrar($nome, $email, $senha, $endereco, $telefone, $genero, $cpf);
        
        if (strpos($resultado, 'Erro') === false && strpos($resultado, 'já') === false) {
            $mensagem = 'Sucesso! Usuário cadastrado. Você será redirecionado em breve...';
            // Redireciona para fazer login após 2 segundos
            header('Refresh: 2; URL=login.php'); 
        } else {
            $mensagem = $resultado; // Mensagem de erro do Controller/DAO
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>TECHFIT - Cadastro</title>
    <link rel="stylesheet" href="assets/css/login.css">
        <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
</head>
<body>
    <header class="techfit-header">...</header>
    <main>
        <div class="auth">
            <div class="tabs">
                <a href="login.php" class="tab">LOGIN</a>
                <a href="#" class="tab active">CADASTRO</a> 
            </div>

            <form id="cadastroForm" class="form active" method="POST" action="Cadastro.php">
                <input type="hidden" name="acao" value="cadastrar">
                
                <?php if ($mensagem): ?>
                    <p style="color: <?php echo strpos($mensagem, 'Erro') !== false ? 'red' : 'var(--cor-ciano-principal)'; ?>; text-align: center; font-weight: bold; margin-bottom: 15px;">
                        <?php echo $mensagem; ?>
                    </p>
                <?php endif; ?>

                <h1>Cadastro de Aluno</h1>
                
                <div class="form-group">
                    <label for="cadNome">Nome Completo:</label>
                    <input id="cadNome" name="nome" type="text" placeholder="Ex: João Silva" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cadEmail">E-mail:</label>
                    <input id="cadEmail" name="email" type="email" placeholder="Ex: seu.nome@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cadCPF">CPF:</label>
                    <input id="cadCPF" name="cpf" type="text" placeholder="Ex: 123.456.789-00" required value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cadTelefone">Telefone:</label>
                    <input id="cadTelefone" name="telefone" type="text" placeholder="Ex: (11) 99999-9999" required value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cadGenero">Gênero:</label>
                    <select id="cadGenero" name="genero" required>
                        <option value="">Selecione...</option>
                        <option value="Masculino" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Feminino" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                        <option value="Outro" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cadEndereco">Endereço:</label>
                    <input id="cadEndereco" name="endereco" type="text" placeholder="Ex: Rua A, 123" required value="<?php echo isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cadSenha">Senha:</label>
                    <input id="cadSenha" name="senha" type="password" placeholder="Crie uma senha (mín. 6 caracteres)" required>
                </div>

                <div class="form-group">
                    <label for="cadConfirmaSenha">Confirmar Senha:</label>
                    <input id="cadConfirmaSenha" name="confirmar_senha" type="password" placeholder="Confirme sua senha" required>
                </div>
                
                <button type="submit">Cadastrar</button>
            </form>
            
            <p class="form-link">Já tem conta? <a href="login.php">Faça Login</a></p>
        </div>
    </main>
    
    <script src="assets/js/login.js"></script>
</body>
</html>