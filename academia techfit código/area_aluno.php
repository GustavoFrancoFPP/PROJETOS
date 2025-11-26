<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Área do Aluno</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #27ae60; }
        .btn { background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Área do Aluno - TECHFIT</h1>
        <p>Bem-vindo, <strong><?php echo $_SESSION['usuario_nome']; ?></strong>!</p>
        <p>Esta é sua área de aluno.</p>
        <a href="login.php" class="btn">Sair</a>
    </div>
</body>
</html>