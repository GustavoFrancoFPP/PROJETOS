<?php
require_once __DIR__ . '/UsuarioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mensagem = '';
$controller = new UsuarioController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nomeUsuario = trim($_POST['nome_usuario']);
    $senha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    if (empty($nomeUsuario) || empty($senha) || empty($confirmarSenha)) {
        $mensagem = "Erro: Por favor, preencha todos os campos.";
    } elseif ($senha !== $confirmarSenha) {
        $mensagem = "Erro: As senhas não coincidem.";
    } else {
        $resultado = $controller->cadastrar($nomeUsuario, $senha);
        
        if (is_numeric($resultado)) {
            $mensagem = 'Sucesso! Usuário cadastrado. Você pode fazer o login agora.';
            // Redireciona para o login após 3 segundos
            header('Refresh: 3; URL=login.php'); 
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
    <link rel="stylesheet" href="login.css">
    </head>
<body>
    <header class="techfit-header">...</header> <main>
        <div class="auth">
            <div class="tabs">
                <a href="login.php" class="tab">LOGIN</a>
                <a href="#" class="tab active">CADASTRO</a> 
            </div>

            <form id="cadastroForm" class="form active" method="POST" action="cadastro.php">
                <input type="hidden" name="acao" value="cadastrar">
                
                <?php if ($mensagem): ?>
                    <p style="color: <?php echo strpos($mensagem, 'Erro') !== false ? 'red' : 'var(--cor-ciano-principal)'; ?>; text-align: center; font-weight: bold; margin-bottom: 15px;">
                        <?php echo $mensagem; ?>
                    </p>
                <?php endif; ?>

                <h1>Cadastro de Aluno</h1>
                
                <div class="form-group">
                    <label for="cadNome">Nome de Usuário/E-mail:</label>
                    <input id="cadNome" name="nome_usuario" type="text" placeholder="Ex: seu.nome@email.com" required value="<?php echo htmlspecialchars($_POST['nome_usuario'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="cadSenha">Senha:</label>
                    <input id="cadSenha" name="senha" type="password" placeholder="Crie uma senha" required>
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
    
    <script src="login.js"></script>
</body>
</html>