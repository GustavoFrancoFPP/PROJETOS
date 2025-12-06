<?php
require_once 'modelo/AlunoDAO.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$idAluno = $_SESSION['user_id'] ?? '';
$alunoDAO = new AlunoDAO();

// Buscar planos disponíveis
$conn = Connection::getInstance();
$planosDisponiveis = $conn->query("SELECT * FROM planos WHERE status = 'ativo' ORDER BY valor")->fetchAll(PDO::FETCH_ASSOC);
$planosAluno = $alunoDAO->buscarPlanosDoAluno($idAluno);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - TECHFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --cor-ciano-principal: #00F0E1;
            --cor-fundo-escuro: #0f1525;
            --cor-card-escuro: rgba(20, 25, 40, 0.8);
            --cor-texto-primario: #ffffff;
            --cor-texto-secundario: #b0b7d9;
            --cor-verde: #2ecc71;
            --cor-laranja: #e67e22;
            --cor-azul: #3498db;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--cor-fundo-escuro);
            color: var(--cor-texto-primario);
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: var(--cor-ciano-principal);
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: var(--cor-texto-secundario);
            font-size: 18px;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .plan-card {
            background: var(--cor-card-escuro);
            border-radius: 15px;
            padding: 30px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .plan-card:hover {
            transform: translateY(-10px);
            border-color: var(--cor-ciano-principal);
            box-shadow: 0 15px 35px rgba(0, 240, 225, 0.2);
        }
        
        .plan-card.popular {
            border-color: var(--cor-laranja);
            transform: scale(1.05);
        }
        
        .plan-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--cor-laranja);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .plan-name {
            font-size: 24px;
            color: var(--cor-ciano-principal);
            margin-bottom: 15px;
        }
        
        .plan-price {
            font-size: 36px;
            font-weight: 700;
            color: var(--cor-verde);
            margin-bottom: 20px;
        }
        
        .plan-price span {
            font-size: 16px;
            color: var(--cor-texto-secundario);
        }
        
        .plan-features {
            list-style: none;
            margin: 25px 0;
        }
        
        .plan-features li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features i {
            color: var(--cor-verde);
            font-size: 14px;
        }
        
        .plan-button {
            background: linear-gradient(135deg, var(--cor-ciano-principal), #00d4ff);
            color: var(--cor-fundo-escuro);
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
        }
        
        .plan-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 209, 178, 0.4);
        }
        
        .plan-button.disabled {
            background: rgba(255, 255, 255, 0.1);
            color: var(--cor-texto-secundario);
            cursor: not-allowed;
        }
        
        .current-plans {
            margin-top: 50px;
        }
        
        .section-title {
            color: var(--cor-ciano-principal);
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(0, 240, 225, 0.3);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--cor-ciano-principal);
            text-decoration: none;
            font-size: 16px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
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
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-crown"></i> Nossos Planos</h1>
            <p>Escolha o plano ideal para sua jornada fitness</p>
        </div>
        
        <div class="plans-grid">
            <?php foreach ($planosDisponiveis as $plano): ?>
                <div class="plan-card <?php echo $plano['nome_planos'] == 'Plano Premium' ? 'popular' : ''; ?>">
                    <?php if ($plano['nome_planos'] == 'Plano Premium'): ?>
                        <div class="plan-badge">MAIS POPULAR</div>
                    <?php endif; ?>
                    
                    <div class="plan-name"><?php echo htmlspecialchars($plano['nome_planos']); ?></div>
                    <div class="plan-price">R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></div>
                    
                    <div class="plan-desc" style="color: var(--cor-texto-secundario); margin-bottom: 20px;">
                        <?php echo htmlspecialchars($plano['descricao']); ?>
                    </div>
                    
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Acesso ilimitado à academia</li>
                        <li><i class="fas fa-check"></i> Todas as modalidades inclusas</li>
                        <li><i class="fas fa-check"></i> Avaliação física gratuita</li>
                        <?php if ($plano['nome_planos'] == 'Plano Premium'): ?>
                            <li><i class="fas fa-check"></i> Personal trainer incluso</li>
                            <li><i class="fas fa-check"></i> Acompanhamento nutricional</li>
                        <?php endif; ?>
                    </ul>
                    
                    <button class="plan-button">
                        <i class="fas fa-shopping-cart"></i> Contratar Agora
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($planosAluno)): ?>
            <div class="current-plans">
                <h2 class="section-title">Meus Planos Ativos</h2>
                <div class="plans-grid">
                    <?php foreach ($planosAluno as $plano): ?>
                        <div class="plan-card">
                            <div class="plan-name"><?php echo htmlspecialchars($plano['nome_planos']); ?></div>
                            <div class="plan-price">R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></div>
                            
                            <div style="margin: 20px 0;">
                                <span class="plan-badge" style="position: static; background: <?php echo $plano['status_pagamento'] == 'pago' ? 'var(--cor-verde)' : 'var(--cor-laranja)'; ?>;">
                                    <?php echo $plano['status_pagamento'] == 'pago' ? 'Ativo' : 'Pagamento Pendente'; ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($plano['data_pagamento'])): ?>
                                <div style="color: var(--cor-texto-secundario); font-size: 14px; margin: 10px 0;">
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
        
        <a href="dashboard_aluno.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar para o Dashboard
        </a>
    </div>
    
    <script>
        function renovarPlano(idPlano) {
            if (confirm('Deseja realmente renovar este plano?')) {
                // Aqui você implementaria a lógica de renovação
                alert('Redirecionando para a página de pagamento...');
                window.location.href = 'pagamento.php?plano=' + idPlano;
            }
        }
    </script>
</body>
</html>