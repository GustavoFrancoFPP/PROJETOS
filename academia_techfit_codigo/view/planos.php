<?php
session_start();
require_once __DIR__ . '/../config/Connection.php';

// Busca planos do banco de dados
$planos = [];
try {
    $conn = Connection::getInstance();
    $stmt = $conn->query("SELECT * FROM planos WHERE nome_planos IN ('Plano Sigma', 'Plano Alpha', 'Plano Beta') ORDER BY valor DESC");
    $planosDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Define benefícios para cada plano
    $beneficiosPorPlano = [
        'Plano Sigma' => [
            'Acesso a todas as academias TechFit',
            'Todos os tipos de aulas disponíveis',
            'Suplementos disponíveis',
            'Avaliação física disponível',
            'Acompanhamento Nutricional'
        ],
        'Plano Alpha' => [
            'Acesso a todas as academias TechFit',
            'Todos os tipos de aulas disponíveis',
            'Suplementos disponíveis',
            'Avaliação física disponível',
            'Acompanhamento Nutricional (não incluído)'
        ],
        'Plano Beta' => [
            'Acesso a todas as academias TechFit',
            'Todos os tipos de aulas disponíveis',
            'Suplementos disponíveis (não incluído)',
            'Avaliação física disponível (não incluído)',
            'Acompanhamento Nutricional (não incluído)'
        ]
    ];
    
    foreach ($planosDB as $planoDB) {
        $planos[] = [
            'id' => strtolower(str_replace(' ', '-', $planoDB['nome_planos'])),
            'nome' => $planoDB['nome_planos'],
            'preco' => floatval($planoDB['valor']),
            'descricao' => $planoDB['descricao'],
            'beneficios' => $beneficiosPorPlano[$planoDB['nome_planos']] ?? [],
            'destaque' => ($planoDB['nome_planos'] === 'Plano Alpha')
        ];
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar planos: " . $e->getMessage());
    // Se falhar, usa dados padrão
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
            'descricao' => 'Ideal para todos da casa, acesso completo à academia para múltiplos membros com benefícios especiais para cada família.',
            'beneficios' => [
                'Acesso a todas as academias TechFit',
                'Todos os tipos de aulas disponíveis',
                'Suplementos disponíveis',
                'Avaliação física disponível',
                'Acompanhamento Nutricional (não incluído)'
            ],
            'destaque' => true
        ],
        [
            'id' => 'plano-beta',
            'nome' => 'Plano Beta',
            'preco' => 89.90,
            'descricao' => 'Essencial para quem quer manter a forma, com acesso aos equipamentos e treinos básicos sem complicação.',
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
}

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
        .planos-wrapper {
            padding: 80px 20px;
            background: linear-gradient(135deg, #0f172a 0%, #1a1a2e 100%);
            min-height: 100vh;
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

        /* Usar os mesmos estilos do inicio.html */
        .pricing-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .plan-card {
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 35px 30px;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
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
            background: linear-gradient(90deg, #00F0E1, #00d4ff);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .plan-card:hover::before {
            transform: scaleX(1);
        }

        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .plan-card.featured {
            transform: scale(1.05);
            border-color: #00F0E1;
            box-shadow: 0 12px 30px rgba(0, 209, 178, 0.2);
        }

        .plan-card.featured:hover {
            transform: scale(1.05) translateY(-8px);
        }

        .plan-card h3 {
            font-size: 1.6rem;
            margin-bottom: 15px;
            color: #fff;
            text-align: center;
        }

        .plan-card > p {
            font-size: 1rem;
            color: #b0b7d9;
            flex-grow: 1;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.6;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 25px 0;
            color: #fff;
            text-align: center;
            position: relative;
        }

        .price::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: #00F0E1;
        }

        .price span {
            font-size: 1rem;
            font-weight: 400;
            color: #b0b7d9;
            display: block;
            margin-top: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00F0E1, #00d4ff);
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
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 240, 225, 0.3);
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .features-list li {
            padding: 10px 0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .features-list li.included {
            color: #b0b7d9;
        }

        .features-list li.included::before {
            content: '✓';
            color: #00F0E1;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .features-list li.excluded {
            color: #888;
            opacity: 0.7;
        }

        .features-list li.excluded::before {
            content: '✕';
            color: #ff6b6b;
            font-weight: 700;
            font-size: 1.2rem;
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

            .pricing-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .plan-card.featured {
                transform: scale(1.02);
            }
        }
    </style>
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
</head>
<body>
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
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
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
                    <li><a href="planos.php" class="nav-link active">Planos</a></li>
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
        <div class="planos-wrapper">
            <div class="container">
                <div class="planos-header">
                    <h2>Nossos Planos</h2>
                    <p>Escolha o plano perfeito para seus objetivos</p>
                </div>

                <section class="pricing-section">
                    <?php foreach ($planos as $plano): ?>
                        <article class="plan-card <?php echo $plano['destaque'] ? 'featured' : ''; ?>">
                            <h3><?php echo htmlspecialchars($plano['nome']); ?></h3>
                            <p><?php echo htmlspecialchars($plano['descricao']); ?></p>
                            <div class="price">R$<?php echo number_format($plano['preco'], 2, ',', '.'); ?> <span>por mês</span></div>
                            <a href="#" class="btn-primary" onclick="event.preventDefault(); adicionarAoCarrinho('<?php echo $plano['id']; ?>', '<?php echo htmlspecialchars($plano['nome']); ?>', <?php echo $plano['preco']; ?>, '<?php echo htmlspecialchars($plano['descricao']); ?>')">Saiba mais!</a>
                            <ul class="features-list">
                                <?php foreach ($plano['beneficios'] as $beneficio): 
                                    $naoIncluso = strpos($beneficio, '(não incluído)') !== false;
                                    $textoLimpo = str_replace(' (não incluído)', '', $beneficio);
                                    $classe = $naoIncluso ? 'excluded' : 'included';
                                ?>
                                    <li class="<?php echo $classe; ?>">
                                        <?php echo htmlspecialchars($textoLimpo); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </section>
            </div>
        </div>
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
        /**
         * Adiciona plano ao carrinho usando AJAX e redireciona imediatamente
         */
        async function adicionarAoCarrinho(planoId, planoNome, planoPreco, planoDescricao) {
            try {
                // Prepara dados do plano
                const planoData = {
                    action: 'adicionar_plano',
                    id: planoId,
                    nome: planoNome,
                    preco: parseFloat(planoPreco),
                    descricao: planoDescricao || ''
                };

                // Envia requisição AJAX para o controller
                const response = await fetch('CarrinhoController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(planoData)
                });

                const result = await response.json();

                if (result.sucesso) {
                    // Redireciona diretamente para o carrinho
                    window.location.href = 'carrinho.php';
                } else {
                    // Mostra mensagem de erro
                    alert(result.mensagem || 'Erro ao adicionar plano ao carrinho');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                alert('Erro ao adicionar plano ao carrinho. Tente novamente.');
            }
        }
    </script>
    <script src="assets/js/header-carrinho-simples.js"></script>
</body>
</html>