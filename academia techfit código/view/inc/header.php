<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Conectar ao banco se necessário
// require_once __DIR__ . '/../Connection.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="assets/css/inicio.css">
    <?php echo $extra_head ?? ''; ?>
    <title>TECHFIT</title>
</head>
<body>
    <header class="techfit-header">
        <div class="header-container">
            <!-- Logo -->
            <a href="inicio.html" class="header-logo">
                <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" 
                     alt="TechFit - Academia Inteligente" 
                     class="logo-image">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>

            <!-- Navegação -->
            <nav class="main-navigation">
                <ul class="nav-links">
                    <li><a href="inicio.html" class="nav-link">Início</a></li>
                    <li><a href="pagina_1.html" class="nav-link">Academias</a></li>
                    <li><a href="produtos_loja.php" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento.php" class="nav-link">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>

                <!-- Botão de Ação -->
                <div class="header-cta">
                    <!-- Botão do Carrinho (será adicionado pelo JavaScript) -->
                    <?php if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <a href="dashboard_aluno.php" class="cta-button">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="cta-button">Área do Aluno</a>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Menu Hambúrguer (Mobile) -->
            <button class="hamburger-menu" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>
    <main>
