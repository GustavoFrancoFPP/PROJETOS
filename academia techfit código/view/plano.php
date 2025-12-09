<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Connection.php';

// Verifica se o usuário está logado
$usuarioLogado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$idCliente = $_SESSION['user_id'] ?? null;
$nomeUsuario = $_SESSION['user_nome'] ?? 'Visitante';

// Buscar planos disponíveis
$conn = Connection::getInstance();
$planosDisponiveis = $conn->query("SELECT * FROM planos ORDER BY valor")->fetchAll(PDO::FETCH_ASSOC);

// Buscar planos do aluno se estiver logado
$planosAluno = [];
if ($usuarioLogado && $idCliente) {
    $stmt = $conn->prepare("
        SELECT p.*, pg.data_pagamento, pg.status_pagamento, pg.valor_pago
        FROM planos p
        INNER JOIN pagamento pg ON p.id_planos = pg.id_planos
        WHERE pg.id_cliente = ?
        ORDER BY pg.data_pagamento DESC
    ");
    $stmt->execute([$idCliente]);
    $planosAluno = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - TECHFIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #00f0e1;
            --background-dark: #121212;
            --background-light: #1e1e1e;
            --text-color: #ffffff;
            --text-muted: #a0a0a0;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background-dark);
            color: var(--text-color);
            line-height: 1.6;
            padding-top: 80px;
        }
        
        /* Header */
        .techfit-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(18, 18, 18, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 240, 225, 0.2);
            z-index: 1000;
            padding: 15px 0;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }
        
        .logo-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
        }
        
        .logo-text span {
            color: var(--primary-color);
        }
        
        .main-navigation {
            display: flex;
            align-items: center;
            gap: 40px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }
        
        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-color);
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
        }
        
        .header-cta {
            display: flex;
            gap: 15px;
        }
        
        .cta-button {
            padding: 10px 25px;
            background: linear-gradient(135deg, var(--primary-color), #00d4c7);
            color: var(--background-dark);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 240, 225, 0.3);
        }
        
        .hamburger-menu {
            display: none;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, rgba(0, 240, 225, 0.1), rgba(0, 212, 199, 0.05));
            border-radius: 20px;
            margin-bottom: 60px;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Plans Grid */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .plan-card {
            background: var(--background-light);
            border-radius: 20px;
            padding: 40px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), #00d4c7);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .plan-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-color);
            box-shadow: 0 20px 40px rgba(0, 240, 225, 0.2);
        }
        
        .plan-card:hover::before {
            transform: scaleX(1);
        }
        
        .plan-card.popular {
            border-color: var(--warning-color);
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), var(--background-light));
        }
        
        .plan-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--warning-color);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .plan-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), #00d4c7);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: var(--background-dark);
        }
        
        .plan-name {
            font-size: 26px;
            color: var(--text-color);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .plan-price {
            font-size: 42px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .plan-price span {
            font-size: 18px;
            color: var(--text-muted);
            font-weight: 400;
        }
        
        .plan-desc {
            color: var(--text-muted);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .plan-features {
            list-style: none;
            margin: 30px 0;
            padding: 0;
        }
        
        .plan-features li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features i {
            color: var(--success-color);
            font-size: 16px;
            width: 20px;
        }
        
        .plan-button {
            background: linear-gradient(135deg, var(--primary-color), #00d4c7);
            color: var(--background-dark);
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .plan-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 240, 225, 0.4);
        }
        
        .plan-button.disabled {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            cursor: not-allowed;
        }
        
        .plan-button.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Current Plans Section */
        .current-plans {
            margin-top: 80px;
            padding: 40px;
            background: rgba(0, 240, 225, 0.05);
            border-radius: 20px;
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 30px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .section-title::before {
            content: '';
            width: 4px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .plan-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin: 15px 0;
        }
        
        .plan-status.active {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .plan-status.pending {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }
        
        .plan-date {
            color: var(--text-muted);
            font-size: 14px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Footer */
        .footer {
            margin-top: 80px;
            padding: 40px 30px;
            background: var(--background-light);
            text-align: center;
            border-top: 1px solid rgba(0, 240, 225, 0.2);
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .footer-link {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: var(--primary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }
            
            .hamburger-menu {
                display: flex;
                flex-direction: column;
                gap: 5px;
                background: none;
                border: none;
                cursor: pointer;
                padding: 10px;
            }
            
            .hamburger-line {
                width: 25px;
                height: 3px;
                background: var(--text-color);
                border-radius: 2px;
                transition: all 0.3s ease;
            }
            
            .main-navigation {
                position: fixed;
                top: 80px;
                left: 0;
                width: 100%;
                background: rgba(18, 18, 18, 0.98);
                flex-direction: column;
                padding: 20px;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .main-navigation.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-links {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .plan-card.popular {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <header class="techfit-header">
        <div class="header-container">
            <a href="inicio.html" class="header-logo">
                <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" 
                     alt="TechFit - Academia Inteligente" 
                     class="logo-image">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>
            
            <nav class="main-navigation">
                <ul class="nav-links">
                    <li><a href="inicio.html" class="nav-link">Início</a></li>
                    <li><a href="pagina_1.html" class="nav-link">Academias</a></li>
                    <li><a href="produtos_loja.php" class="nav-link">Produtos</a></li>
                    <li><a href="plano.php" class="nav-link active">Planos</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>
                
                <div class="header-cta">
                    <?php if ($usuarioLogado): ?>
                        <a href="dashboard_aluno.php" class="cta-button">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($nomeUsuario); ?>
                        </a>
                        <a href="logout.php" class="cta-button" style="background: rgba(244, 67, 54, 0.2); color: #f44336;">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="cta-button">Área do Aluno</a>
                    <?php endif; ?>
                </div>
            </nav>
            
            <button class="hamburger-menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>
    <div class="container">
        <div class="hero-section">
            <h1><i class="fas fa-crown"></i> Nossos Planos</h1>
            <p>Escolha o plano ideal para sua jornada fitness e alcance seus objetivos com a TECHFIT</p>
        </div>
        
        <div class="plans-grid">
            <?php 
            $iconMap = [
                'Básico' => 'fa-dumbbell',
                'Premium' => 'fa-crown',
                'Mensal' => 'fa-calendar-alt',
                'Trimestral' => 'fa-calendar-check',
                'Semestral' => 'fa-calendar',
                'Anual' => 'fa-star'
            ];
            
            foreach ($planosDisponiveis as $index => $plano): 
                $isPopular = $plano['nome_planos'] == 'Plano Premium' || $index == 1;
                $icon = $iconMap[$plano['nome_planos']] ?? 'fa-dumbbell';
            ?>
                <div class="plan-card <?php echo $isPopular ? 'popular' : ''; ?>">
                    <?php if ($isPopular): ?>
                        <div class="plan-badge">🔥 Mais Popular</div>
                    <?php endif; ?>
                    
                    <div class="plan-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    
                    <div class="plan-name"><?php echo htmlspecialchars($plano['nome_planos']); ?></div>
                    <div class="plan-price">
                        R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?>
                        <span>/mês</span>
                    </div>
                    
                    <div class="plan-desc">
                        <?php echo htmlspecialchars($plano['descricao']); ?>
                    </div>
                    
                    <ul class="plan-features">
                        <li><i class="fas fa-check-circle"></i> Acesso ilimitado à academia</li>
                        <li><i class="fas fa-check-circle"></i> Todas as modalidades inclusas</li>
                        <li><i class="fas fa-check-circle"></i> Avaliação física gratuita</li>
                        <li><i class="fas fa-check-circle"></i> App mobile TechFit</li>
                        <?php if ($isPopular): ?>
                            <li><i class="fas fa-check-circle"></i> Personal trainer incluso</li>
                            <li><i class="fas fa-check-circle"></i> Acompanhamento nutricional</li>
                            <li><i class="fas fa-check-circle"></i> Acesso a todas unidades</li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if ($usuarioLogado): ?>
                        <button class="plan-button" onclick="contratarPlano(<?php echo $plano['id_planos']; ?>)">
                            <i class="fas fa-check"></i> Contratar Agora
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="plan-button" style="text-decoration: none;">
                            <i class="fas fa-sign-in-alt"></i> Faça login para contratar
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($planosAluno)): ?>
            <div class="current-plans">
                <h2 class="section-title">
                    <i class="fas fa-star"></i> Meus Planos Ativos
                </h2>
                <div class="plans-grid">
                    <?php foreach ($planosAluno as $plano): ?>
                        <div class="plan-card">
                            <div class="plan-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            
                            <div class="plan-name"><?php echo htmlspecialchars($plano['nome_planos']); ?></div>
                            <div class="plan-price">
                                R$ <?php echo number_format($plano['valor_pago'], 2, ',', '.'); ?>
                                <span>pago</span>
                            </div>
                            
                            <div class="plan-status <?php echo $plano['status_pagamento'] == 'pago' ? 'active' : 'pending'; ?>">
                                <i class="fas <?php echo $plano['status_pagamento'] == 'pago' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                                <?php echo $plano['status_pagamento'] == 'pago' ? 'Ativo' : 'Pagamento Pendente'; ?>
                            </div>
                            
                            <?php if (!empty($plano['data_pagamento'])): ?>
                                <div class="plan-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Último pagamento: <?php echo date('d/m/Y', strtotime($plano['data_pagamento'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <button class="plan-button" onclick="renovarPlano(<?php echo $plano['id_planos']; ?>)">
                                <i class="fas fa-sync-alt"></i> Renovar Plano
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="footer-links">
            <a href="inicio.html" class="footer-link">Início</a>
            <a href="pagina_1.html" class="footer-link">Academias</a>
            <a href="produtos_loja.php" class="footer-link">Produtos</a>
            <a href="suporte.html" class="footer-link">Suporte</a>
            <?php if ($usuarioLogado): ?>
                <a href="dashboard_aluno.php" class="footer-link">Dashboard</a>
            <?php endif; ?>
        </div>
        <p style="color: var(--text-muted); margin-top: 20px;">&copy; 2025 TECHFIT. Todos os direitos reservados.</p>
    </footer>
    
    <script>
        // Menu mobile
        const hamburger = document.querySelector('.hamburger-menu');
        const navigation = document.querySelector('.main-navigation');
        
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                navigation.classList.toggle('active');
                hamburger.classList.toggle('active');
            });
        }
        
        // Contratar plano
        function contratarPlano(idPlano) {
            if (confirm('Deseja contratar este plano?')) {
                window.location.href = 'pagamento.php?plano=' + idPlano + '&tipo=plano';
            }
        }
        
        // Renovar plano
        function renovarPlano(idPlano) {
            if (confirm('Deseja renovar este plano?')) {
                window.location.href = 'pagamento.php?plano=' + idPlano + '&tipo=renovacao';
            }
        }
        
        // Animação ao scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.plan-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>