<?php
require_once 'AlunoDAO.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verifica se é aluno (não funcionário)
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'funcionario') {
    header('Location: painel_administrativo.php');
    exit;
}

// Instancia o DAO do aluno
$alunoDAO = new AlunoDAO();

// Dados do aluno da sessão
$idAluno = $_SESSION['user_id'] ?? '';
$nomeAluno = $_SESSION['user_nome'] ?? 'Aluno';
$emailAluno = $_SESSION['user_email'] ?? '';

// Buscar todas as informações do aluno
$infoAluno = $alunoDAO->buscarAlunoPorId($idAluno);
$planosAluno = $alunoDAO->buscarPlanosDoAluno($idAluno);
$produtosAluno = $alunoDAO->buscarProdutosDoAluno($idAluno);
$agendamentosAluno = $alunoDAO->buscarAgendamentosDoAluno($idAluno);
$avaliacoesAluno = $alunoDAO->buscarAvaliacoesDoAluno($idAluno);
$totalGasto = $alunoDAO->calcularTotalGasto($idAluno);
$presencasAluno = $alunoDAO->buscarPresencasDoAluno($idAluno);
$vencimentos = $alunoDAO->buscarProximosVencimentos($idAluno);
$treinosAluno = $alunoDAO->buscarTreinosDoAluno($idAluno);

// Formatar dados para exibição
$totalProdutos = count($produtosAluno);
$totalAgendamentos = count($agendamentosAluno);
$totalAvaliacoes = count($avaliacoesAluno);
$totalPresencas = count($presencasAluno);
$totalTreinos = count($treinosAluno);

// Verificar se tem plano ativo
$planoAtivo = !empty($planosAluno) ? $planosAluno[0] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TECHFIT - Dashboard do Aluno</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <style>
        :root {
            --cor-ciano-principal: #00F0E1;
            --cor-fundo-escuro: #0f1525;
            --cor-card-escuro: rgba(20, 25, 40, 0.8);
            --cor-texto-primario: #ffffff;
            --cor-texto-secundario: #b0b7d9;
            --cor-verde: #2ecc71;
            --cor-laranja: #e67e22;
            --cor-vermelho: #e74c3c;
            --cor-azul: #3498db;
            --font-principal: 'Poppins', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-principal);
            background: var(--cor-fundo-escuro);
            color: var(--cor-texto-primario);
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            background: var(--cor-card-escuro);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 6px solid var(--cor-ciano-principal);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .header-info h1 {
            color: var(--cor-ciano-principal);
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header-info p {
            color: var(--cor-texto-secundario);
            font-size: 16px;
        }

        .header-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-box {
            background: rgba(0, 240, 225, 0.1);
            border: 1px solid rgba(0, 240, 225, 0.3);
            border-radius: 10px;
            padding: 15px 25px;
            text-align: center;
            min-width: 120px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--cor-ciano-principal);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--cor-texto-secundario);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: var(--cor-card-escuro);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 240, 225, 0.2);
            border-color: var(--cor-ciano-principal);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header h2 {
            color: var(--cor-ciano-principal);
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header i {
            font-size: 24px;
        }

        .card-badge {
            background: var(--cor-verde);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: var(--cor-laranja);
        }

        .badge-danger {
            background: var(--cor-vermelho);
        }

        .badge-info {
            background: var(--cor-azul);
        }

        .item-list {
            list-style: none;
        }

        .item-list li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-list li:last-child {
            border-bottom: none;
        }

        .item-title {
            font-weight: 500;
            color: var(--cor-texto-primario);
        }

        .item-value {
            color: var(--cor-ciano-principal);
            font-weight: 600;
        }

        .item-date {
            font-size: 12px;
            color: var(--cor-texto-secundario);
        }

        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: var(--cor-texto-secundario);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--cor-ciano-principal), #00d4ff);
            color: var(--cor-fundo-escuro);
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-family: var(--font-principal);
            margin-top: 15px;
            width: 100%;
            text-align: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 209, 178, 0.4);
        }

        .btn-secondary {
            background: rgba(0, 240, 225, 0.2);
            border: 1px solid var(--cor-ciano-principal);
            color: var(--cor-ciano-principal);
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-family: var(--font-principal);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-secondary:hover {
            background: var(--cor-ciano-principal);
            color: var(--cor-fundo-escuro);
        }

        .plan-card {
            background: linear-gradient(135deg, rgba(0, 240, 225, 0.1), rgba(0, 240, 225, 0.05));
            border: 1px solid rgba(0, 240, 225, 0.2);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .plan-name {
            font-size: 20px;
            color: var(--cor-ciano-principal);
            margin-bottom: 10px;
        }

        .plan-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--cor-verde);
            margin: 10px 0;
        }

        .plan-desc {
            color: var(--cor-texto-secundario);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .progress-bar {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            height: 8px;
            margin: 15px 0;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(90deg, var(--cor-ciano-principal), #00d4ff);
            height: 100%;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
            }
            
            .header-stats {
                justify-content: center;
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
                    <li><a href="produtos.html" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento2.html" class="nav-link">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>
            </nav>
            <button class="hamburger-menu" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <main>
        <div class="dashboard-container">
            <!-- Header do Dashboard -->
            <div class="dashboard-header">
                <div class="header-info">
                    <h1>Olá, <?php echo htmlspecialchars($nomeAluno); ?>! 👋</h1>
                    <p>Bem-vindo ao seu painel TECHFIT</p>
                    <p style="font-size: 14px; color: #888; margin-top: 5px;">
                        ID: <?php echo htmlspecialchars($idAluno); ?> | 
                        Usuário: <?php echo htmlspecialchars($infoAluno['nome_usuario'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="header-stats">
                    <div class="stat-box">
                        <div class="stat-value">R$ <?php echo number_format($totalGasto['total_geral'], 2, ',', '.'); ?></div>
                        <div class="stat-label">Total Gasto</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $totalProdutos; ?></div>
                        <div class="stat-label">Produtos</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo count($planosAluno); ?></div>
                        <div class="stat-label">Planos</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $totalPresencas; ?></div>
                        <div class="stat-label">Presenças</div>
                    </div>
                </div>
            </div>

            <!-- Grid Principal -->
            <div class="dashboard-grid">
                <!-- Card de Planos Ativos -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-crown"></i> Meus Planos</h2>
                        <?php if (!empty($planosAluno)): ?>
                            <span class="card-badge"><?php echo count($planosAluno); ?> ativo(s)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($planosAluno)): ?>
                        <?php foreach ($planosAluno as $plano): ?>
                            <div class="plan-card">
                                <div class="plan-name"><?php echo htmlspecialchars($plano['nome_planos']); ?></div>
                                <div class="plan-price">R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></div>
                                <div class="plan-desc"><?php echo htmlspecialchars($plano['descricao']); ?></div>
                                
                                <?php if (!empty($plano['status_pagamento'])): ?>
                                    <div style="margin: 10px 0;">
                                        <span class="card-badge <?php echo $plano['status_pagamento'] == 'pago' ? '' : 'badge-warning'; ?>">
                                            <?php echo $plano['status_pagamento'] == 'pago' ? 'Pago' : 'Pendente'; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($plano['data_pagamento'])): ?>
                                    <div style="font-size: 12px; color: var(--cor-texto-secundario);">
                                        Último pagamento: <?php echo date('d/m/Y', strtotime($plano['data_pagamento'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-crown"></i>
                            <p>Você não possui planos ativos</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="planos.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Adquirir Novo Plano
                    </a>
                </div>

                <!-- Card de Produtos Comprados -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-shopping-bag"></i> Últimas Compras</h2>
                        <?php if (!empty($produtosAluno)): ?>
                            <span class="card-badge badge-info"><?php echo $totalProdutos; ?> itens</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($produtosAluno)): ?>
                        <ul class="item-list">
                            <?php foreach ($produtosAluno as $produto): ?>
                                <li>
                                    <div>
                                        <div class="item-title"><?php echo htmlspecialchars($produto['nome_produto']); ?></div>
                                        <div class="item-date">
                                            <?php echo date('d/m/Y', strtotime($produto['data_venda'])); ?>
                                        </div>
                                    </div>
                                    <div class="item-value">
                                        R$ <?php echo number_format($produto['valor_total'], 2, ',', '.'); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>Nenhum produto comprado ainda</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="produtos.html" class="btn-primary">
                        <i class="fas fa-store"></i> Ver Loja
                    </a>
                </div>

                <!-- Card de Agendamentos -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-calendar-alt"></i> Próximas Aulas</h2>
                        <?php if (!empty($agendamentosAluno)): ?>
                            <span class="card-badge"><?php echo $totalAgendamentos; ?> agendada(s)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($agendamentosAluno)): ?>
                        <ul class="item-list">
                            <?php foreach ($agendamentosAluno as $agendamento): ?>
                                <li>
                                    <div>
                                        <div class="item-title"><?php echo htmlspecialchars($agendamento['tipo_aula']); ?></div>
                                        <div class="item-date">
                                            <?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="card-badge">Agendado</span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <p>Nenhuma aula agendada</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="agendamento2.html" class="btn-primary">
                        <i class="fas fa-plus-circle"></i> Agendar Nova Aula
                    </a>
                </div>

                <!-- Card de Avaliações -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-line"></i> Avaliações Físicas</h2>
                        <?php if (!empty($avaliacoesAluno)): ?>
                            <span class="card-badge badge-info"><?php echo $totalAvaliacoes; ?> realizada(s)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($avaliacoesAluno)): ?>
                        <ul class="item-list">
                            <?php foreach ($avaliacoesAluno as $avaliacao): ?>
                                <li>
                                    <div>
                                        <div class="item-title"><?php echo htmlspecialchars($avaliacao['descricao']); ?></div>
                                        <div class="item-date">
                                            <?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?>
                                            <?php if (!empty($avaliacao['avaliador'])): ?>
                                                | Avaliador: <?php echo htmlspecialchars($avaliacao['avaliador']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="card-badge">Concluída</span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <p>Nenhuma avaliação realizada</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="agendamento2.html" class="btn-primary">
                        <i class="fas fa-calendar-check"></i> Agendar Avaliação
                    </a>
                </div>

                <!-- Card de Treinos -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-dumbbell"></i> Meus Treinos</h2>
                        <?php if (!empty($treinosAluno)): ?>
                            <span class="card-badge"><?php echo $totalTreinos; ?> ativo(s)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($treinosAluno)): ?>
                        <ul class="item-list">
                            <?php foreach ($treinosAluno as $treino): ?>
                                <li>
                                    <div>
                                        <div class="item-title"><?php echo htmlspecialchars($treino['nome_treino']); ?></div>
                                        <div class="item-date">
                                            <?php echo date('d/m/Y', strtotime($treino['data_inicio'])); ?> a 
                                            <?php echo date('d/m/Y', strtotime($treino['data_fim'])); ?>
                                            <?php if (!empty($treino['instrutor'])): ?>
                                                <br>Instrutor: <?php echo htmlspecialchars($treino['instrutor']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="card-badge"><?php echo $treino['objetivo'] ?? 'Ativo'; ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-dumbbell"></i>
                            <p>Nenhum treino cadastrado</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="treinos.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Ver Detalhes do Treino
                    </a>
                </div>

                <!-- Card de Vencimentos -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-clock"></i> Próximos Vencimentos</h2>
                        <?php if (!empty($vencimentos)): ?>
                            <span class="card-badge badge-warning"><?php echo count($vencimentos); ?> pendente(s)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($vencimentos)): ?>
                        <ul class="item-list">
                            <?php foreach ($vencimentos as $vencimento): ?>
                                <li>
                                    <div>
                                        <div class="item-title"><?php echo htmlspecialchars($vencimento['nome_planos'] ?? 'Plano'); ?></div>
                                        <div class="item-date">
                                            <?php if (isset($vencimento['vencimento'])): ?>
                                                Vence em: <?php echo date('d/m/Y', strtotime($vencimento['vencimento'])); ?>
                                            <?php endif; ?>
                                            <?php if (isset($vencimento['status']) && $vencimento['status'] == 'vencida'): ?>
                                                <span style="color: #e74c3c; font-size: 12px; margin-left: 10px;">(Vencida)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="item-value">
                                        <?php if (isset($vencimento['valor_total'])): ?>
                                            R$ <?php echo number_format($vencimento['valor_total'], 2, ',', '.'); ?>
                                        <?php elseif (isset($vencimento['valor_plano'])): ?>
                                            R$ <?php echo number_format($vencimento['valor_plano'], 2, ',', '.'); ?>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>Nenhum vencimento pendente</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="pagamentos.php" class="btn-primary">
                        <i class="fas fa-credit-card"></i> Gerenciar Pagamentos
                    </a>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 30px;">
                <a href="editar_perfil.php" class="btn-secondary">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </a>
                <a href="produtos.html" class="btn-secondary">
                    <i class="fas fa-store"></i> Loja de Produtos
                </a>
                <a href="agendamento2.html" class="btn-secondary">
                    <i class="fas fa-calendar-plus"></i> Novo Agendamento
                </a>
                <a href="suporte.html" class="btn-secondary">
                    <i class="fas fa-headset"></i> Suporte
                </a>
                <a href="logout.php" class="btn-secondary" style="background: rgba(231, 76, 60, 0.2); border-color: #e74c3c; color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-brand">
                <div class="logo-text">TECH<span>FIT</span></div>
                <p>Sua jornada fitness começa aqui</p>
            </div>
            <div class="footer-copyright">
                &copy; 2024 TECHFIT. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script>
        // Script para mostrar/esconder menu hamburguer
        document.querySelector('.hamburger-menu').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
            this.classList.toggle('active');
        });
    </script>
</body>
</html>