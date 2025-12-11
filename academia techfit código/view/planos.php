<?php
session_start();
require_once __DIR__ . '/../config/Connection.php';

// Dados dos planos (exatamente como na imagem)
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
        body {
            background: #000;
            margin: 0;
            padding: 0;
        }

        .planos-section {
            padding: 60px 20px 100px;
            background: #000;
            min-height: 100vh;
        }

        .planos-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .planos-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .planos-header h2 {
            font-size: 3rem;
            color: #fff;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .planos-header p {
            font-size: 1.2rem;
            color: #888;
        }

        .planos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .plano-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 40px 30px;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .plano-card:hover {
            transform: translateY(-5px);
            border-color: #00F0E1;
        }

        .plano-card.destaque {
            border: 2px solid #00F0E1;
            background: #1a1a1a;
            box-shadow: 0 0 30px rgba(0, 240, 225, 0.3);
        }

        .plano-nome {
            font-size: 1.5rem;
            color: #fff;
            margin: 0 0 15px 0;
            font-weight: 600;
            text-align: center;
        }

        .plano-descricao {
            color: #aaa;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 30px;
            text-align: center;
            min-height: 80px;
        }

        .plano-preco {
            font-size: 2.8rem;
            color: #fff;
            font-weight: 700;
            margin-bottom: 5px;
            text-align: center;
        }

        .plano-periodo {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .btn-contratar {
            background: #00F0E1;
            color: #000;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
            width: 100%;
        }

        .btn-contratar:hover {
            background: #00d9cc;
            transform: scale(1.05);
        }

        .plano-beneficios {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .plano-beneficios li {
            color: #ccc;
            font-size: 0.9rem;
            padding: 12px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-bottom: 1px solid #2a2a2a;
        }

        .plano-beneficios li:last-child {
            border-bottom: none;
        }

        .plano-beneficios li::before {
            content: '✓';
            color: #00F0E1;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .plano-beneficios li.nao-incluso::before {
            content: '✕';
            color: #ff4444;
        }

        .plano-beneficios li.nao-incluso {
            color: #666;
        }

        @media (max-width: 1024px) {
            .planos-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                max-width: 400px;
            }

            .plano-card.destaque {
                order: -1;
            }
        }
    </style>
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
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

                            <button class="btn-contratar" onclick="adicionarAoCarrinho('<?php echo $plano['id']; ?>', '<?php echo htmlspecialchars($plano['nome']); ?>', <?php echo $plano['preco']; ?>, '<?php echo htmlspecialchars($plano['descricao']); ?>')">
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
</body>
</html>
