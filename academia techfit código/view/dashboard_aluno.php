<?php
/**
 * Dashboard do Aluno - TechFit
 * Painel com dados dinâmicos do banco de dados
 */

// Inicia sessão e verifica autenticação
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verifica se é aluno (não funcionário)
if ($_SESSION['tipo_usuario'] === 'funcionario') {
    header('Location: admin.php');
    exit;
}

require_once __DIR__ . '/../config/Connection.php';

$conn = Connection::getInstance();
$idAluno = $_SESSION['user_id'];
$nomeAluno = $_SESSION['user_nome'] ?? 'Aluno';

// ===== BUSCA DADOS DO ALUNO =====
try {
    // Informações do cliente
    $stmt = $conn->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
    $stmt->execute([$idAluno]);
    $infoAluno = $stmt->fetch();
    
    if ($infoAluno) {
        $nomeAluno = $infoAluno['nome_cliente'];
    }

    // Planos ativos do aluno (corrigido para buscar pela tabela pagamento)
    $stmt = $conn->prepare("
        SELECT 
            pg.id_pagamento,
            p.nome_planos,
            p.descricao,
            p.valor,
            pg.valor_pago,
            pg.status_pagamento,
            DATE_FORMAT(pg.data_pagamento, '%d/%m/%Y') as data_contratacao,
            DATE_ADD(pg.data_pagamento, INTERVAL 1 MONTH) as data_vencimento,
            DATEDIFF(DATE_ADD(pg.data_pagamento, INTERVAL 1 MONTH), NOW()) as dias_restantes
        FROM pagamento pg
        INNER JOIN planos p ON pg.id_planos = p.id_planos
        WHERE pg.id_cliente = ? 
        AND pg.status_pagamento = 'pago'
        ORDER BY pg.data_pagamento DESC
    ");
    $stmt->execute([$idAluno]);
    $planosAluno = $stmt->fetchAll();

    // Agendamentos futuros
    $stmt = $conn->prepare("
        SELECT 
            a.tipo_aula,
            DATE_FORMAT(a.data_agendamento, '%d/%m/%Y') as data_aula,
            DATE_FORMAT(a.data_agendamento, '%H:%i') as horario_aula,
            a.data_agendamento
        FROM agendamento a
        WHERE a.id_cliente = ? 
        AND a.data_agendamento >= NOW()
        ORDER BY a.data_agendamento ASC
        LIMIT 5
    ");
    $stmt->execute([$idAluno]);
    $agendamentosAluno = $stmt->fetchAll();

    // Histórico de compras (produtos)
    $stmt = $conn->prepare("
        SELECT 
            v.id_venda,
            p.nome_produto,
            p.categoria,
            v.quantidade,
            v.valor_total,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y %H:%i') as data_compra
        FROM venda v
        INNER JOIN produtos p ON v.id_produtos = p.id_produtos
        WHERE v.id_cliente = ?
        ORDER BY v.data_venda DESC
        LIMIT 5
    ");
    $stmt->execute([$idAluno]);
    $historicoCompras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total gasto
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(valor_total), 0) as total_gasto
        FROM venda
        WHERE id_cliente = ?
    ");
    $stmt->execute([$idAluno]);
    $totalGasto = $stmt->fetch()['total_gasto'] ?? 0;

    // Notificações não lidas
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_notif
        FROM notificacao
        WHERE id_cliente = ? AND status = 'não lida'
    ");
    $stmt->execute([$idAluno]);
    $totalNotificacoes = $stmt->fetch()['total_notif'] ?? 0;

    // Buscar notificações recentes
    $stmt = $conn->prepare("
        SELECT 
            titulo,
            mensagem,
            DATE_FORMAT(data_envio, '%d/%m/%Y %H:%i') as data_formatada,
            status
        FROM notificacao
        WHERE id_cliente = ?
        ORDER BY data_envio DESC
        LIMIT 5
    ");
    $stmt->execute([$idAluno]);
    $notificacoesRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    $planosAluno = [];
    $agendamentosAluno = [];
    $historicoCompras = [];
    $totalGasto = 0;
    $totalNotificacoes = 0;
    $notificacoesRecentes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TECHFIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/inicio.css">
    <style>
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .welcome-section { background: linear-gradient(135deg, #00F0E1 0%, #00A8CC 100%); padding: 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,240,225,0.3); }
        .welcome-section h1 { margin: 0; font-size: 2rem; color: #0a0e27; }
        .welcome-section p { margin: 5px 0 0 0; color: #0a0e27; opacity: 0.8; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: rgba(20, 25, 40, 0.8); padding: 20px; border-radius: 15px; border: 1px solid rgba(0,240,225,0.3); text-align: center; }
        .stat-card .icon { font-size: 2rem; color: #00F0E1; margin-bottom: 10px; }
        .stat-card .value { font-size: 1.8rem; font-weight: bold; color: #fff; margin: 10px 0; }
        .stat-card .label { color: #aaa; font-size: 0.9rem; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .dashboard-card { background: rgba(20, 25, 40, 0.9); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid rgba(0,240,225,0.3); }
        .card-header h2 { color: #00F0E1; font-size: 1.3rem; margin: 0; display: flex; align-items: center; gap: 10px; }
        .item-list { list-style: none; padding: 0; margin: 0; }
        .item-list li { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .item-list li:last-child { border-bottom: none; }
        .item-info { flex: 1; }
        .item-info .name { display: block; color: #fff; font-weight: 500; margin-bottom: 5px; }
        .item-info .detail { display: block; color: #888; font-size: 0.85rem; }
        .item-value { color: #00F0E1; font-weight: bold; }
        .empty-state { text-align: center; padding: 40px 20px; color: #666; }
        .empty-state i { font-size: 3rem; margin-bottom: 15px; opacity: 0.5; }
        .empty-state p { margin: 0; }
        .btn-action { display: inline-block; background: #00F0E1; color: #0a0e27; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: 600; margin-top: 15px; transition: all 0.3s; }
        .btn-action:hover { background: #00A8CC; transform: translateY(-2px); }
        .btn-cancelar-plano { background: #ff4444; color: #fff; border: none; padding: 8px 15px; border-radius: 8px; font-size: 0.85rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 5px; }
        .btn-cancelar-plano:hover { background: #cc0000; transform: translateY(-2px); }
        .btn-cancelar-plano i { font-size: 0.9rem; }
    </style>
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
</head>
<body>
    <!-- Header -->
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
                    <li><a href="dashboard_aluno.php" class="nav-link active">Dashboard</a></li>
                    <li><a href="produtos_loja.php" class="nav-link">Produtos</a></li>
                    <li><a href="agendamento.php" class="nav-link">Agendamento</a></li>
                    <li><a href="suporte.html" class="nav-link">Suporte</a></li>
                </ul>
                
                <div class="header-cta">
                    <a href="editar_perfil.php" class="cta-button"><i class="fas fa-user"></i> Perfil</a>
                    <a href="logout.php" class="cta-button" style="background: #ff4444;"><i class="fas fa-sign-out-alt"></i> Sair</a>
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

    <!-- Main Content -->
    <main class="dashboard-container">
        <!-- Boas-vindas -->
        <div class="welcome-section">
            <h1><i class="fas fa-dumbbell"></i> Olá, <?php echo htmlspecialchars($nomeAluno); ?>!</h1>
            <p>Seja bem-vindo ao seu painel personalizado da TechFit</p>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <div class="value"><?php echo count($agendamentosAluno); ?></div>
                <div class="label">Aulas Agendadas</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-crown"></i></div>
                <div class="value"><?php echo count($planosAluno); ?></div>
                <div class="label">Planos Ativos</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="value"><?php echo count($historicoCompras); ?></div>
                <div class="label">Compras Realizadas</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-bell"></i></div>
                <div class="value"><?php echo $totalNotificacoes; ?></div>
                <div class="label">Notificações</div>
            </div>
        </div>

        <!-- Cards Principais -->
        <div class="dashboard-grid">
            <!-- Meus Planos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-crown"></i> Meus Planos</h2>
                </div>
                <?php if (!empty($planosAluno)): ?>
                    <ul class="item-list">
                        <?php foreach ($planosAluno as $plano): ?>
                            <li>
                                <div class="item-info">
                                    <span class="name"><?php echo htmlspecialchars($plano['nome_planos']); ?></span>
                                    <span class="detail"><?php echo htmlspecialchars($plano['descricao']); ?></span>
                                    <?php if (isset($plano['data_contratacao'])): ?>
                                        <span class="detail">Desde: <?php echo $plano['data_contratacao']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                                    <span class="item-value">R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></span>
                                    <button class="btn-cancelar-plano" data-id="<?php echo $plano['id_pagamento']; ?>">
                                        <i class="fas fa-times-circle"></i> Cancelar
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>Você ainda não possui planos ativos</p>
                        <a href="planos.php" class="btn-action">Contratar Plano</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Próximas Aulas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Próximas Aulas</h2>
                </div>
                <?php if (!empty($agendamentosAluno)): ?>
                    <ul class="item-list">
                        <?php foreach ($agendamentosAluno as $agendamento): ?>
                            <li>
                                <div class="item-info">
                                    <span class="name"><?php echo htmlspecialchars($agendamento['tipo_aula']); ?></span>
                                    <span class="detail">
                                        <i class="far fa-calendar"></i> <?php echo $agendamento['data_aula']; ?>
                                        <i class="far fa-clock"></i> <?php echo $agendamento['horario_aula']; ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="agendamento.php" class="btn-action" style="width: 100%; text-align: center;">Ver Todas</a>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>Você não tem aulas agendadas</p>
                        <a href="agendamento.php" class="btn-action">Agendar Aula</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Histórico de Compras -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-bag"></i> Histórico de Compras</h2>
                </div>
                <?php if (!empty($historicoCompras)): ?>
                    <ul class="item-list">
                        <?php foreach ($historicoCompras as $compra): ?>
                            <li>
                                <div class="item-info">
                                    <span class="name"><?php echo htmlspecialchars($compra['nome_produto']); ?></span>
                                    <span class="detail">
                                        <?php echo $compra['data_compra']; ?> • 
                                        Qtd: <?php echo $compra['quantidade']; ?>
                                    </span>
                                </div>
                                <span class="item-value">R$ <?php echo number_format($compra['valor_total'], 2, ',', '.'); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <strong>Total Gasto:</strong> 
                        <span class="item-value" style="font-size: 1.2rem;">R$ <?php echo number_format($totalGasto, 2, ',', '.'); ?></span>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Você ainda não realizou compras</p>
                        <a href="produtos_loja.php" class="btn-action">Ver Produtos</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Notificações -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-bell"></i> Notificações</h2>
                    <?php if ($totalNotificacoes > 0): ?>
                        <span style="background: #ff4444; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                            <?php echo $totalNotificacoes; ?> nova(s)
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($notificacoesRecentes)): ?>
                    <ul class="item-list">
                        <?php foreach ($notificacoesRecentes as $notif): ?>
                            <li style="flex-direction: column; align-items: flex-start; gap: 8px;">
                                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                                    <span class="name" style="color: #00F0E1; font-weight: 600;">
                                        <?php echo htmlspecialchars($notif['titulo']); ?>
                                    </span>
                                    <?php if ($notif['status'] === 'não lida'): ?>
                                        <span style="background: #ff4444; color: #fff; padding: 3px 8px; border-radius: 10px; font-size: 0.75rem;">
                                            Nova
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p style="margin: 0; color: #ccc; font-size: 0.9rem; line-height: 1.5;">
                                    <?php echo htmlspecialchars($notif['mensagem']); ?>
                                </p>
                                <span class="detail" style="color: #888; font-size: 0.85rem;">
                                    <i class="fas fa-clock"></i> <?php echo $notif['data_formatada']; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>Nenhuma notificação</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Atalhos Rápidos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
                </div>
                <div style="display: grid; gap: 15px;">
                    <a href="agendamento.php" class="btn-action" style="width: 100%; text-align: center;">
                        <i class="fas fa-calendar-plus"></i> Agendar Aula
                    </a>
                    <a href="editar_perfil.php" class="btn-action" style="width: 100%; text-align: center; background: #667eea;">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                    <a href="suporte.html" class="btn-action" style="width: 100%; text-align: center; background: #f093fb;">
                        <i class="fas fa-headset"></i> Suporte
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <script src="assets/js/inicio.js"></script>
    <script>
        // Função para cancelar plano
        document.querySelectorAll('.btn-cancelar-plano').forEach(btn => {
            btn.addEventListener('click', async function() {
                const idPagamento = this.getAttribute('data-id');
                
                if (!confirm('Tem certeza que deseja cancelar este plano? Esta ação não pode ser desfeita.')) {
                    return;
                }
                
                try {
                    const response = await fetch('cancelar_plano.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_pagamento: idPagamento
                        })
                    });
                    
                    const resultado = await response.json();
                    
                    if (resultado.sucesso) {
                        alert(resultado.mensagem);
                        location.reload(); // Recarrega a página para atualizar a lista
                    } else {
                        alert('Erro: ' + resultado.mensagem);
                    }
                } catch (erro) {
                    console.error('Erro ao cancelar plano:', erro);
                    alert('Erro ao processar solicitação. Tente novamente.');
                }
            });
        });
    </script>
</body>
</html>
