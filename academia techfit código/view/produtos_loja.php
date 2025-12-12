<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Connection.php';

// Buscar produtos do banco de dados
try {
    $conn = Connection::getInstance();
    $stmt = $conn->prepare("SELECT id_produtos, nome_produto, tipo_produto, categoria, preco, quantidade 
                            FROM produtos 
                            WHERE quantidade > 0
                            ORDER BY nome_produto");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $produtos = [];
    error_log("Erro ao buscar produtos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja TECHFIT - Sua Academia do Futuro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="assets/css/produto.css">
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
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
                    <li><a href="produtos_loja.php" class="nav-link active">Produtos</a></li>
                    <li><a href="agendamento.php" class="nav-link">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>

                <!-- Botão de Ação -->
                <div class="header-cta">
                    <!-- Botão do Carrinho (será adicionado pelo JavaScript) -->
                    <a href="login.php" class="cta-button">Área do Aluno</a>
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
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Loja TECHFIT</h1>
                    <p>Equipamentos e Suplementos de Alta Performance</p>
                </div>
            </div>
        </section>

        <section class="products">
            <div class="container">
                <?php if (empty($produtos)): ?>
                    <div style="text-align: center; padding: 50px; color: #fff;">
                        <h2>Nenhum produto disponível no momento</h2>
                        <p>Volte em breve para conferir nossas novidades!</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($produtos as $produto): ?>
                            <div class="product-card" data-id="<?php echo htmlspecialchars($produto['id_produtos']); ?>">
                                <img src="assets/images/imagens/remove_watermark_image_20251112_134903.png" 
                                     alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>" 
                                     class="product-image">
                                <div class="product-info">
                                    <div class="product-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <h3 class="product-name"><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                    <p class="product-description"><?php echo htmlspecialchars($produto['tipo_produto'] . ' - ' . $produto['categoria']); ?></p>
                                    <p class="product-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                    <?php if ($produto['quantidade'] > 0): ?>
                                        <button class="btn-add-to-cart">Adicionar ao carrinho</button>
                                        <?php if ($produto['quantidade'] < 10): ?>
                                            <p style="color: #f39c12; font-size: 0.85rem; margin-top: 5px;">
                                                Apenas <?php echo $produto['quantidade']; ?> em estoque!
                                            </p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn-add-to-cart" disabled style="background: #666;">Esgotado</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-brand">
                <a href="#" class="logo">
                     <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" alt="TechFit Logo" class="logo-img">
                     <span>TECHFIT</span>
                </a>
                <p>Sua Academia do Futuro!</p>
            </div>
            <div class="footer-contact">
                 <a href="#" class="contact-link">
                    <i class="fab fa-instagram"></i>
                    <span>TECHFIT_OFC</span>
                </a>
                <a href="#" class="contact-link">
                    <i class="fab fa-whatsapp"></i>
                    <span>(19) 99936 - 4328</span>
                </a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TECHFIT. Todos os direitos reservados</p>
        </div>
    </footer>

    <script src="assets/js/produto.js"></script>
    <script src="assets/js/header-carrinho-simples.js"></script>
</body>
</html>
