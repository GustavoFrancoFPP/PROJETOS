<?php
session_start();
require_once __DIR__ . '/../config/Connection.php';

// Dados dos planos
$planos = [
    [
        'id' => 'plano-sigma',
        'nome' => 'Plano Sigma',
        'preco' => 239.90,
        'descricao' => 'O melhor que tem, inclui todos os treinos, acesso a todas as aulas e ainda ganha suplementos de graça para potencializar seus resultados.',
        'beneficios' => [
            'Acesso a todas as academias TechFit',
            'Todos os tipos de aulas disponíveis',
            'Suplementos disponíveis',
            'Avaliação física disponível',
            'Acompanhamento Nutricional'
        ],
        'destaque' => false
    ],
    [
        'id' => 'plano-alpha',
        'nome' => 'Plano Alpha',
        'preco' => 139.90,
        'descricao' => 'Ideal para todos da casa, acesso completo a academia para múltiplos membros com benefícios especiais para cada família.',
        'beneficios' => [
            'Acesso a todas as academias TechFit',
            'Todos os tipos de aulas disponíveis',
            'Suplementos disponíveis',
            'Avaliação física disponível',
            'Acompanhamento Nutricional'
        ],
        'destaque' => true
    ],
    [
        'id' => 'plano-beta',
        'nome' => 'Plano Beta',
        'preco' => 89.90,
        'descricao' => 'Essencial para quem quer manter a forma, com acesso dos equipamentos e treinos básicos sem complicação.',
        'beneficios' => [
            'Acesso a todas as academias TechFit',
            'Todos os tipos de aulas disponíveis',
            'Suplementos disponíveis (não incluído)',
            'Avaliação física disponível (não incluído)',
            'Acompanhamento Nutricional (não incluído)'
        ],
        'destaque' => false
    ]
];

// Dados de usuário se logado
$user_logado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - TECHFIT</title>
    <link rel="stylesheet" href="assets/css/inicio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .planos-section {
            padding: 80px 20px;
            background: linear-gradient(135deg, #0f172a 0%, #1a1a2e 100%);
            position: relative;
            overflow: hidden;
        }

        .planos-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .planos-header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInDown 0.8s ease;
        }

        .planos-header h2 {
            font-size: 3rem;
            color: #fff;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .planos-header p {
            font-size: 1.2rem;
            color: #b0b7d9;
        }

        .planos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .plano-card {
            background: rgba(30, 30, 30, 0.6);
            border: 2px solid rgba(0, 240, 225, 0.15);
            border-radius: 15px;
            padding: 40px 30px;
            position: relative;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
        }

        .plano-card:hover {
            transform: translateY(-10px);
            border-color: rgba(0, 240, 225, 0.4);
            box-shadow: 0 20px 40px rgba(0, 240, 225, 0.2);
        }

        .plano-card.destaque {
            border: 2px solid #00F0E1;
            background: rgba(0, 240, 225, 0.08);
            transform: scale(1.05);
        }

        .plano-card.destaque:hover {
            box-shadow: 0 30px 60px rgba(0, 240, 225, 0.3);
        }

        .plano-destaque-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #00F0E1;
            color: #0f172a;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .plano-nome {
            font-size: 1.8rem;
            color: #fff;
            margin: 20px 0 10px;
            font-weight: 700;
        }

        .plano-descricao {
            color: #b0b7d9;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .plano-preco {
            font-size: 2.5rem;
            color: #00F0E1;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .plano-periodo {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .btn-contratar {
            background: linear-gradient(135deg, #00F0E1 0%, #00d4b4 100%);
            color: #0f172a;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 25px;
        }

        .btn-contratar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 240, 225, 0.3);
        }

        .btn-contratar:active {
            transform: translateY(0);
        }

        .plano-beneficios {
            list-style: none;
            padding: 0;
        }

        .plano-beneficios li {
            color: #b0b7d9;
            font-size: 0.95rem;
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .plano-beneficios li::before {
            content: '✓';
            color: #00F0E1;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .plano-beneficios li.nao-incluso::before {
            content: '✕';
            color: #ff6b6b;
        }

        .plano-beneficios li.nao-incluso {
            color: #888;
            opacity: 0.7;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .planos-header h2 {
                font-size: 2rem;
            }

            .plano-card.destaque {
                transform: scale(1.02);
            }

            .planos-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <header class="techfit-header">
        <div class="header-container">
            <a href="inicio.html" class="header-logo">
                <div class="logo-text">TECH<span>FIT</span></div>
            </a>

            <nav class="main-navigation">
                <ul class="nav-links">
                    <li><a href="inicio.html" class="nav-link">Início</a></li>
                    <li><a href="pagina_1.html" class="nav-link">Academias</a></li>
                    <li><a href="produtos_loja.php" class="nav-link">Produtos</a></li>
                    <li><a href="planos.php" class="nav-link active">Planos</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>

                <div class="header-cta">
                    <?php if ($user_logado): ?>
                        <a href="dashboard_aluno.php" class="cta-button">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="cta-button">Área do Aluno</a>
                    <?php endif; ?>
                </div>
            </nav>

            <button class="hamburger-menu" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <main>
        <section class="planos-section">
            <div class="planos-container">
                <div class="planos-header">
                    <h2>Nossos Planos</h2>
                    <p>Escolha o plano perfeito para seus objetivos</p>
                </div>

                <div class="planos-grid">
                    <?php foreach ($planos as $plano): ?>
                        <div class="plano-card <?php echo $plano['destaque'] ? 'destaque' : ''; ?>">
                            <?php if ($plano['destaque']): ?>
                                <span class="plano-destaque-badge">Mais Popular</span>
                            <?php endif; ?>

                            <h3 class="plano-nome"><?php echo htmlspecialchars($plano['nome']); ?></h3>
                            <p class="plano-descricao"><?php echo htmlspecialchars($plano['descricao']); ?></p>

                            <div class="plano-preco">R$<?php echo number_format($plano['preco'], 2, ',', '.'); ?></div>
                            <p class="plano-periodo">por mês</p>

                            <button class="btn-contratar" onclick="adicionarAoCarrinho('<?php echo $plano['id']; ?>', '<?php echo htmlspecialchars($plano['nome']); ?>', <?php echo $plano['preco']; ?>)">
                                SAIBA MAIS!
                            </button>

                            <ul class="plano-beneficios">
                                <?php foreach ($plano['beneficios'] as $beneficio): 
                                    $naoIncluso = strpos($beneficio, '(não incluído)') !== false;
                                    $textoLimpo = str_replace(' (não incluído)', '', $beneficio);
                                ?>
                                    <li class="<?php echo $naoIncluso ? 'nao-incluso' : ''; ?>">
                                        <?php echo htmlspecialchars($textoLimpo); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-brand">
                <a href="#" class="logo">
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

    <script>
        function adicionarAoCarrinho(planoId, planoNome, planoPreco) {
            // Recupera carrinho atual
            let carrinho = JSON.parse(localStorage.getItem('carrinho') || '{"itens": [], "subtotal": 0}');

            // Adiciona o plano como item
            const item = {
                id: planoId,
                tipo: 'plano',
                nome: planoNome,
                preco: planoPreco,
                quantidade: 1,
                subtotal: planoPreco
            };

            carrinho.itens.push(item);
            carrinho.subtotal += planoPreco;

            // Salva carrinho
            localStorage.setItem('carrinho', JSON.stringify(carrinho));

            // Feedback ao usuário
            alert(`${planoNome} adicionado ao carrinho!`);

            // Redireciona para carrinho
            window.location.href = 'carrinho.html';
        }
    </script>
</body>
</html>
