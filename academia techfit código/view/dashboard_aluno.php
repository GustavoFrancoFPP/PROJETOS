<?php
// MANTIVE SUA LÓGICA ORIGINAL E ADICIONEI O PRODUTO DAO
require_once 'Connection.php';
require_once 'AlunoDAO.php';
require_once 'ProdutoDAO.php'; // Adicionado

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Seus DAOs originais
$alunoDAO = new AlunoDAO();
$idAluno = $_SESSION['user_id'];
$infoAluno = $alunoDAO->buscarAlunoPorId($idAluno);
$nomeAluno = $infoAluno['nome_cliente'] ?? 'Aluno';

// --- LÓGICA DO CARRINHO (NOVO) ---
$prodDAO = new ProdutoDAO();
$meuCarrinho = $prodDAO->buscarCarrinho($idAluno);
$meusComprados = $prodDAO->buscarHistorico($idAluno);

// Calcula total
$totalCarrinho = 0;
foreach($meuCarrinho as $item) $totalCarrinho += $item['preco'];
// ---------------------------------

// Suas outras buscas (Planos, Agendamentos, etc)
$planosAluno = $alunoDAO->buscarPlanosDoAluno($idAluno);
$agendamentosAluno = $alunoDAO->buscarAgendamentosDoAluno($idAluno);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TECHFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <style>
        /* CSS EXTRA PARA MANTER O PADRÃO NO DASHBOARD */
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .dashboard-card { background: rgba(20, 25, 40, 0.9); padding: 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .card-header h2 { color: #00F0E1; font-size: 1.2rem; }
        .item-list { list-style: none; padding: 0; }
        .item-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #fff; }
        .btn-primary { background: #00F0E1; color: #000; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; }
        .empty-state { text-align: center; color: #888; padding: 20px; }
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
                    <li><a href="produtos.php" class="nav-link">Produtos</a></li>
                    <li><a href="logout.php" class="nav-link" style="color: #ff4444;">Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">
        <div style="margin-bottom: 30px; border-left: 5px solid #00F0E1; padding-left: 15px;">
            <h1 style="color: #fff;">Olá, <?php echo htmlspecialchars($nomeAluno); ?></h1>
            <p style="color: #aaa;">Bem-vindo ao seu painel.</p>
        </div>

        <div class="dashboard-grid">
            
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-crown"></i> Meus Planos</h2>
                </div>
                <?php if (!empty($planosAluno)): ?>
                    <ul class="item-list">
                        <?php foreach ($planosAluno as $plano): ?>
                            <li><?php echo $plano['nome_planos']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">Sem planos ativos</div>
                <?php endif; ?>
            </div>

            <div class="dashboard-card" style="border-color: #00F0E1;">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-cart"></i> Carrinho</h2>
                    <span style="background: #e67e22; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 12px;">Em andamento</span>
                </div>
                
                <?php if (!empty($meuCarrinho)): ?>
                    <ul class="item-list">
                        <?php foreach ($meuCarrinho as $item): ?>
                            <li>
                                <span><?php echo $item['nome_produto']; ?></span>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="color: #00F0E1;">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                    <form action="ProdutoController.php" method="POST">
                                        <input type="hidden" name="id_compra" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="remover" style="background:none; border:none; color:#e74c3c; cursor:pointer;"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="margin-top: 15px; border-top: 1px solid #333; padding-top: 10px;">
                        <div style="color: #fff; display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <strong>Total:</strong>
                            <strong style="color: #00F0E1;">R$ <?php echo number_format($totalCarrinho, 2, ',', '.'); ?></strong>
                        </div>
                        <form action="ProdutoController.php" method="POST">
                            <button type="submit" name="finalizar" class="btn-primary">Finalizar Compra</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-cart-arrow-down" style="font-size: 30px; margin-bottom: 10px;"></i>
                        <p>Carrinho vazio</p>
                        <a href="produtos.php" style="color: #00F0E1; text-decoration: none;">Ir para Loja</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Compras Realizadas</h2>
                </div>
                <?php if (!empty($meusComprados)): ?>
                    <ul class="item-list">
                        <?php foreach ($meusComprados as $compra): ?>
                            <li>
                                <div>
                                    <div style="font-weight:bold;"><?php echo $compra['nome_produto']; ?></div>
                                    <div style="font-size: 12px; color: #888;"><?php echo date('d/m/Y', strtotime($compra['data_compra'])); ?></div>
                                </div>
                                <div style="color: #2ecc71;"><i class="fas fa-check"></i> Pago</div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">Nenhuma compra anterior</div>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Aulas</h2>
                </div>
                <?php if (!empty($agendamentosAluno)): ?>
                    <ul class="item-list">
                        <?php foreach ($agendamentosAluno as $agenda): ?>
                            <li><?php echo $agenda['tipo_aula']; ?> - <?php echo date('d/m', strtotime($agenda['data_agendamento'])); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">Sem agendamentos</div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</body>
</html>