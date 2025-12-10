<?php
/**
 * ========================================
 * PAINEL ADMINISTRATIVO COMPLETO - TECHFIT
 * CRUD: Alunos | Aulas | Produtos | Funcionários | Notificações
 * ========================================
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// SEGURANÇA: Apenas funcionários
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['tipo_usuario'] !== 'funcionario') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/Connection.php';
$conn = Connection::getInstance();

$mensagem = '';
$tipo_mensagem = 'success';

// ========================================
// PROCESSAMENTO DE AÇÕES POST
// ========================================

// ============ CRUD ALUNOS ============
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'cadastrar_aluno':
                $conn->beginTransaction();
                
                $stmt = $conn->prepare("INSERT INTO cliente (nome_cliente, email, cpf, telefone, plano, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['email'],
                    $_POST['cpf'],
                    $_POST['telefone'],
                    $_POST['plano']
                ]);
                $id_cliente = $conn->lastInsertId();
                
                $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO login (id_cliente, email, senha, tipo_usuario) VALUES (?, ?, ?, 'cliente')");
                $stmt->execute([$id_cliente, $_POST['email'], $senha_hash]);
                
                $conn->commit();
                $mensagem = "Aluno cadastrado com sucesso!";
                break;
                
            case 'editar_aluno':
                $stmt = $conn->prepare("UPDATE cliente SET nome_cliente = ?, email = ?, telefone = ?, plano = ? WHERE id_cliente = ?");
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['email'],
                    $_POST['telefone'],
                    $_POST['plano'],
                    $_POST['id_cliente']
                ]);
                
                $stmt = $conn->prepare("UPDATE login SET email = ? WHERE id_cliente = ?");
                $stmt->execute([$_POST['email'], $_POST['id_cliente']]);
                
                $mensagem = "Aluno atualizado com sucesso!";
                break;
                
            case 'excluir_aluno':
                $conn->beginTransaction();
                $id = $_POST['id_cliente'];
                
                $conn->exec("DELETE FROM agendamento WHERE id_cliente = $id");
                $conn->exec("DELETE FROM venda WHERE id_cliente = $id");
                $conn->exec("DELETE FROM pagamento WHERE id_cliente = $id");
                $conn->exec("DELETE FROM planos WHERE id_cliente = $id");
                $conn->exec("DELETE FROM notificacao WHERE id_cliente = $id");
                $conn->exec("DELETE FROM presenca WHERE id_cliente = $id");
                $conn->exec("DELETE FROM login WHERE id_cliente = $id");
                $conn->exec("DELETE FROM cliente WHERE id_cliente = $id");
                
                $conn->commit();
                $mensagem = "Aluno excluído com sucesso!";
                break;
                
            // ============ CRUD AULAS ============
            case 'cadastrar_aula':
                $stmt = $conn->prepare("INSERT INTO aulas (nome_aula, dia_semana, horario, professor, vagas_totais, descricao, status) VALUES (?, ?, ?, ?, ?, ?, 'ativa')");
                $stmt->execute([
                    $_POST['nome_aula'],
                    $_POST['dia_semana'],
                    $_POST['horario'],
                    $_POST['professor'],
                    $_POST['vagas_totais'],
                    $_POST['descricao'] ?? ''
                ]);
                $mensagem = "Aula cadastrada com sucesso!";
                break;
                
            case 'editar_aula':
                $stmt = $conn->prepare("UPDATE aulas SET nome_aula = ?, dia_semana = ?, horario = ?, professor = ?, vagas_totais = ?, descricao = ? WHERE id_aula = ?");
                $stmt->execute([
                    $_POST['nome_aula'],
                    $_POST['dia_semana'],
                    $_POST['horario'],
                    $_POST['professor'],
                    $_POST['vagas_totais'],
                    $_POST['descricao'] ?? '',
                    $_POST['id_aula']
                ]);
                $mensagem = "Aula atualizada com sucesso!";
                break;
                
            case 'cancelar_aula':
                $stmt = $conn->prepare("UPDATE aulas SET status = 'cancelada' WHERE id_aula = ?");
                $stmt->execute([$_POST['id_aula']]);
                $mensagem = "Aula cancelada com sucesso!";
                break;
                
            // ============ CRUD PRODUTOS ============
            case 'cadastrar_produto':
                $stmt = $conn->prepare("INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, quantidade_estoque, url_imagem, descricao, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'ativo')");
                $stmt->execute([
                    $_POST['nome_produto'],
                    $_POST['tipo_produto'],
                    $_POST['categoria'],
                    $_POST['preco'],
                    $_POST['quantidade_estoque'],
                    $_POST['url_imagem'] ?? '',
                    $_POST['descricao'] ?? ''
                ]);
                $mensagem = "Produto cadastrado com sucesso!";
                break;
                
            case 'editar_produto':
                $stmt = $conn->prepare("UPDATE produtos SET nome_produto = ?, tipo_produto = ?, categoria = ?, preco = ?, quantidade_estoque = ?, url_imagem = ?, descricao = ? WHERE id_produtos = ?");
                $stmt->execute([
                    $_POST['nome_produto'],
                    $_POST['tipo_produto'],
                    $_POST['categoria'],
                    $_POST['preco'],
                    $_POST['quantidade_estoque'],
                    $_POST['url_imagem'] ?? '',
                    $_POST['descricao'] ?? '',
                    $_POST['id_produtos']
                ]);
                $mensagem = "Produto atualizado com sucesso!";
                break;
                
            case 'desativar_produto':
                $stmt = $conn->prepare("UPDATE produtos SET status = 'inativo' WHERE id_produtos = ?");
                $stmt->execute([$_POST['id_produtos']]);
                $mensagem = "Produto desativado com sucesso!";
                break;
                
            // ============ CRUD FUNCIONÁRIOS ============
            case 'cadastrar_funcionario':
                $conn->beginTransaction();
                
                $stmt = $conn->prepare("INSERT INTO funcionario (nome_funcionario, email, cpf, telefone, cargo, data_admissao, status) VALUES (?, ?, ?, ?, ?, NOW(), 'ativo')");
                $stmt->execute([
                    $_POST['nome_funcionario'],
                    $_POST['email'],
                    $_POST['cpf'],
                    $_POST['telefone'],
                    $_POST['cargo']
                ]);
                $id_funcionario = $conn->lastInsertId();
                
                $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO login (id_funcionario, email, senha, tipo_usuario) VALUES (?, ?, ?, 'funcionario')");
                $stmt->execute([$id_funcionario, $_POST['email'], $senha_hash]);
                
                $conn->commit();
                $mensagem = "Funcionário cadastrado com sucesso!";
                break;
                
            case 'desativar_funcionario':
                $stmt = $conn->prepare("UPDATE funcionario SET status = 'inativo' WHERE id_funcionario = ?");
                $stmt->execute([$_POST['id_funcionario']]);
                $mensagem = "Funcionário desativado com sucesso!";
                break;
                
            // ============ NOTIFICAÇÕES ============
            case 'enviar_notificacao':
                $stmt = $conn->prepare("INSERT INTO notificacao (titulo, mensagem, tipo, prioridade) VALUES (?, ?, 'geral', ?)");
                $stmt->execute([
                    $_POST['titulo'],
                    $_POST['mensagem'],
                    $_POST['prioridade']
                ]);
                $mensagem = "Notificação enviada com sucesso para todos os alunos!";
                break;
        }
    } catch (PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        $mensagem = "Erro: " . $e->getMessage();
        $tipo_mensagem = 'error';
    }
}

// ========================================
// BUSCA DE DADOS
// ========================================

// Estatísticas
$totalAlunos = $conn->query("SELECT COUNT(*) as total FROM cliente")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$faturamento = $conn->query("SELECT COALESCE(SUM(valor_pago), 0) as total FROM pagamento")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$totalAulasAtivas = $conn->query("SELECT COUNT(*) as total FROM aulas WHERE status = 'ativa'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$totalProdutos = $conn->query("SELECT COUNT(*) as total FROM produtos WHERE status = 'ativo'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Listas
$alunos = $conn->query("SELECT c.*, COUNT(a.id_agendamento) as total_agendamentos FROM cliente c LEFT JOIN agendamento a ON c.id_cliente = a.id_cliente GROUP BY c.id_cliente ORDER BY c.nome_cliente")->fetchAll(PDO::FETCH_ASSOC);

$aulas = $conn->query("SELECT *, (vagas_totais - vagas_ocupadas) as vagas_disponiveis FROM aulas WHERE status = 'ativa' ORDER BY dia_semana, horario")->fetchAll(PDO::FETCH_ASSOC);

$produtos = $conn->query("SELECT * FROM produtos WHERE status = 'ativo' ORDER BY nome_produto")->fetchAll(PDO::FETCH_ASSOC);

$funcionarios = $conn->query("SELECT * FROM funcionario WHERE status = 'ativo' ORDER BY nome_funcionario")->fetchAll(PDO::FETCH_ASSOC);

$notificacoes = $conn->query("SELECT * FROM notificacao WHERE tipo = 'geral' ORDER BY data_envio DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - TECHFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%); color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Header */
        .header { background: rgba(10, 14, 39, 0.95); padding: 15px 0; border-bottom: 2px solid rgba(0,240,225,0.3); position: sticky; top: 0; z-index: 100; }
        .header-container { max-width: 1600px; margin: 0 auto; padding: 0 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: bold; color: #00F0E1; text-decoration: none; }
        .nav-links { display: flex; gap: 30px; list-style: none; align-items: center; }
        .nav-link { color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; }
        .nav-link:hover { background: rgba(0,240,225,0.1); color: #00F0E1; }
        .btn-logout { background: #ff4444; padding: 10px 20px; border-radius: 8px; }
        
        /* Container */
        .container { max-width: 1600px; margin: 0 auto; padding: 30px; }
        .page-title { font-size: 2.5rem; color: #00F0E1; margin-bottom: 30px; text-align: center; text-transform: uppercase; }
        
        /* Alertas */
        .alert { padding: 15px 25px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; animation: slideIn 0.3s; }
        .alert-success { background: rgba(0,240,129,0.2); border: 2px solid rgba(0,240,129,0.5); color: #00F081; }
        .alert-error { background: rgba(255,68,68,0.2); border: 2px solid rgba(255,68,68,0.5); color: #ff4444; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: linear-gradient(135deg, rgba(30, 35, 50, 0.9) 0%, rgba(20, 25, 40, 0.9) 100%); padding: 30px; border-radius: 20px; border-left: 5px solid #00F0E1; box-shadow: 0 8px 32px rgba(0,0,0,0.3); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h4 { color: #aaa; font-size: 0.95rem; margin-bottom: 15px; text-transform: uppercase; }
        .stat-card .value { font-size: 3rem; font-weight: bold; color: #00F0E1; }
        
        /* Tabs */
        .tabs-container { margin-bottom: 40px; }
        .tabs { display: flex; gap: 10px; border-bottom: 2px solid rgba(255,255,255,0.1); margin-bottom: 30px; flex-wrap: wrap; }
        .tab { padding: 15px 30px; background: rgba(30, 35, 50, 0.5); border: none; color: #aaa; font-size: 1rem; cursor: pointer; border-radius: 12px 12px 0 0; transition: all 0.3s; }
        .tab.active { background: rgba(0,240,225,0.2); color: #00F0E1; border-bottom: 3px solid #00F0E1; }
        .tab:hover { background: rgba(0,240,225,0.1); color: #00F0E1; }
        
        /* Tab Content */
        .tab-content { display: none; animation: fadeIn 0.3s; }
        .tab-content.active { display: block; }
        
        /* Panel */
        .panel { background: rgba(30, 35, 50, 0.9); padding: 35px; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .panel h2 { color: #00F0E1; margin-bottom: 25px; font-size: 1.8rem; display: flex; align-items: center; gap: 15px; }
        
        /* Formulários */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; color: #aaa; font-weight: 600; }
        .form-control, .form-select { width: 100%; padding: 14px 18px; background: rgba(255,255,255,0.05); border: 2px solid rgba(255,255,255,0.2); border-radius: 12px; color: #fff; font-size: 1rem; transition: all 0.3s; }
        .form-control:focus, .form-select:focus { outline: none; border-color: #00F0E1; background: rgba(255,255,255,0.08); }
        textarea.form-control { resize: vertical; min-height: 100px; }
        
        /* Tabelas */
        .table-container { overflow-x: auto; margin-top: 25px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: rgba(0,240,225,0.15); }
        .data-table th { padding: 18px; text-align: left; color: #00F0E1; font-weight: 600; text-transform: uppercase; font-size: 0.9rem; }
        .data-table td { padding: 18px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .data-table tbody tr { transition: background 0.3s; }
        .data-table tbody tr:hover { background: rgba(0,240,225,0.05); }
        
        /* Botões */
        .btn { padding: 12px 24px; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-size: 0.95rem; }
        .btn-primary { background: linear-gradient(135deg, #00F0E1 0%, #00A8CC 100%); color: #0a0e27; }
        .btn-primary:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(0,240,225,0.4); }
        .btn-danger { background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); color: #fff; }
        .btn-danger:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(255,68,68,0.4); }
        .btn-warning { background: linear-gradient(135deg, #ffaa00 0%, #ff8800 100%); color: #000; }
        .btn-warning:hover { transform: scale(1.05); }
        .btn-success { background: linear-gradient(135deg, #00F081 0%, #00cc66 100%); color: #000; }
        .btn-sm { padding: 8px 16px; font-size: 0.85rem; }
        
        /* Badges */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .badge-success { background: rgba(0,240,129,0.2); color: #00F081; }
        .badge-danger { background: rgba(255,68,68,0.2); color: #ff4444; }
        .badge-warning { background: rgba(255,170,0,0.2); color: #ffaa00; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; }
        
        /* Animations */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateX(-20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
            .tabs { flex-direction: column; }
        }
    </style>
    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="admin_full.php" class="logo">
                <i class="fas fa-dumbbell"></i> TECHFIT Admin
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="admin_full.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="inicio.html" class="nav-link"><i class="fas fa-globe"></i> Site</a></li>
                    <li><a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 class="page-title"><i class="fas fa-crown"></i> Painel Administrativo</h1>
        
        <!-- Mensagens -->
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <span><?php echo $mensagem; ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Estatísticas -->
        <section class="stats-grid">
            <div class="stat-card">
                <h4><i class="fas fa-dollar-sign"></i> Faturamento Total</h4>
                <p class="value">R$ <?php echo number_format($faturamento, 2, ',', '.'); ?></p>
            </div>
            <div class="stat-card">
                <h4><i class="fas fa-users"></i> Total de Alunos</h4>
                <p class="value"><?php echo $totalAlunos; ?></p>
            </div>
            <div class="stat-card">
                <h4><i class="fas fa-calendar-alt"></i> Aulas Ativas</h4>
                <p class="value"><?php echo $totalAulasAtivas; ?></p>
            </div>
            <div class="stat-card">
                <h4><i class="fas fa-box"></i> Produtos Ativos</h4>
                <p class="value"><?php echo $totalProdutos; ?></p>
            </div>
        </section>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="openTab(event, 'tab-alunos')">
                    <i class="fas fa-users"></i> Alunos
                </button>
                <button class="tab" onclick="openTab(event, 'tab-aulas')">
                    <i class="fas fa-calendar-check"></i> Aulas
                </button>
                <button class="tab" onclick="openTab(event, 'tab-produtos')">
                    <i class="fas fa-shopping-cart"></i> Produtos
                </button>
                <button class="tab" onclick="openTab(event, 'tab-funcionarios')">
                    <i class="fas fa-user-tie"></i> Funcionários
                </button>
                <button class="tab" onclick="openTab(event, 'tab-notificacoes')">
                    <i class="fas fa-bell"></i> Notificações
                </button>
            </div>

            <!-- TAB: ALUNOS -->
            <div id="tab-alunos" class="tab-content active">
                <div class="panel">
                    <h2><i class="fas fa-user-plus"></i> Cadastrar Novo Aluno</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="cadastrar_aluno">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome Completo*</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email*</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">CPF*</label>
                                <input type="text" name="cpf" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Plano</label>
                                <select name="plano" class="form-select">
                                    <option value="basico">Básico</option>
                                    <option value="premium">Premium</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Senha Inicial*</label>
                                <input type="password" name="senha" class="form-control" value="123456" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cadastrar Aluno
                        </button>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-users"></i> Lista de Alunos</h2>
                    <?php if (!empty($alunos)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Agendamentos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <tr>
                                            <td><?php echo $aluno['id_cliente']; ?></td>
                                            <td><?php echo htmlspecialchars($aluno['nome_cliente']); ?></td>
                                            <td><?php echo htmlspecialchars($aluno['email']); ?></td>
                                            <td><?php echo htmlspecialchars($aluno['telefone'] ?? '-'); ?></td>
                                            <td><span class="badge badge-success"><?php echo strtoupper($aluno['plano'] ?? 'BÁSICO'); ?></span></td>
                                            <td>
                                                <span class="badge badge-<?php echo $aluno['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                                                    <?php echo strtoupper($aluno['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $aluno['total_agendamentos']; ?></td>
                                            <td>
                                                <button onclick="editarAluno(<?php echo htmlspecialchars(json_encode($aluno)); ?>)" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir este aluno permanentemente?');">
                                                    <input type="hidden" name="action" value="excluir_aluno">
                                                    <input type="hidden" name="id_cliente" value="<?php echo $aluno['id_cliente']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <p>Nenhum aluno cadastrado</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB: AULAS -->
            <div id="tab-aulas" class="tab-content">
                <div class="panel">
                    <h2><i class="fas fa-calendar-plus"></i> Cadastrar Nova Aula</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="cadastrar_aula">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome da Aula*</label>
                                <input type="text" name="nome_aula" class="form-control" placeholder="Ex: Yoga Matinal" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Dia da Semana*</label>
                                <select name="dia_semana" class="form-select" required>
                                    <option value="Segunda">Segunda-feira</option>
                                    <option value="Terça">Terça-feira</option>
                                    <option value="Quarta">Quarta-feira</option>
                                    <option value="Quinta">Quinta-feira</option>
                                    <option value="Sexta">Sexta-feira</option>
                                    <option value="Sábado">Sábado</option>
                                    <option value="Domingo">Domingo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Horário*</label>
                                <input type="time" name="horario" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Professor*</label>
                                <input type="text" name="professor" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Vagas Totais*</label>
                                <input type="number" name="vagas_totais" class="form-control" min="1" value="20" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descrição</label>
                                <textarea name="descricao" class="form-control"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cadastrar Aula
                        </button>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-calendar-check"></i> Aulas Cadastradas</h2>
                    <?php if (!empty($aulas)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Dia</th>
                                        <th>Horário</th>
                                        <th>Professor</th>
                                        <th>Vagas</th>
                                        <th>Ocupação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aulas as $aula): ?>
                                        <tr>
                                            <td><?php echo $aula['id_aula']; ?></td>
                                            <td><?php echo htmlspecialchars($aula['nome_aula']); ?></td>
                                            <td><?php echo $aula['dia_semana']; ?></td>
                                            <td><?php echo date('H:i', strtotime($aula['horario'])); ?></td>
                                            <td><?php echo htmlspecialchars($aula['professor']); ?></td>
                                            <td><?php echo $aula['vagas_disponiveis']; ?> / <?php echo $aula['vagas_totais']; ?></td>
                                            <td>
                                                <?php 
                                                    $ocupacao = ($aula['vagas_ocupadas'] / $aula['vagas_totais']) * 100;
                                                    $badgeClass = $ocupacao > 80 ? 'danger' : ($ocupacao > 50 ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo round($ocupacao); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <button onclick="editarAula(<?php echo htmlspecialchars(json_encode($aula)); ?>)" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Cancelar esta aula?');">
                                                    <input type="hidden" name="action" value="cancelar_aula">
                                                    <input type="hidden" name="id_aula" value="<?php echo $aula['id_aula']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-ban"></i> Cancelar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Nenhuma aula cadastrada</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB: PRODUTOS -->
            <div id="tab-produtos" class="tab-content">
                <div class="panel">
                    <h2><i class="fas fa-box-open"></i> Cadastrar Novo Produto</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="cadastrar_produto">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome do Produto*</label>
                                <input type="text" name="nome_produto" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipo*</label>
                                <select name="tipo_produto" class="form-select" required>
                                    <option value="suplemento">Suplemento</option>
                                    <option value="roupa">Roupa</option>
                                    <option value="acessorio">Acessório</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Categoria</label>
                                <input type="text" name="categoria" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Preço (R$)*</label>
                                <input type="number" step="0.01" name="preco" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quantidade em Estoque*</label>
                                <input type="number" name="quantidade_estoque" class="form-control" min="0" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">URL da Imagem</label>
                                <input type="text" name="url_imagem" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cadastrar Produto
                        </button>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-shopping-cart"></i> Produtos Cadastrados</h2>
                    <?php if (!empty($produtos)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos as $produto): ?>
                                        <tr>
                                            <td><?php echo $produto['id_produtos']; ?></td>
                                            <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                                            <td><span class="badge badge-success"><?php echo strtoupper($produto['tipo_produto']); ?></span></td>
                                            <td><?php echo htmlspecialchars($produto['categoria'] ?? '-'); ?></td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                            <td>
                                                <?php 
                                                    $estoque = $produto['quantidade_estoque'];
                                                    $badgeClass = $estoque < 10 ? 'danger' : ($estoque < 30 ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $estoque; ?> un.
                                                </span>
                                            </td>
                                            <td>
                                                <button onclick="editarProduto(<?php echo htmlspecialchars(json_encode($produto)); ?>)" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Desativar este produto?');">
                                                    <input type="hidden" name="action" value="desativar_produto">
                                                    <input type="hidden" name="id_produtos" value="<?php echo $produto['id_produtos']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-ban"></i> Desativar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>Nenhum produto cadastrado</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB: FUNCIONÁRIOS -->
            <div id="tab-funcionarios" class="tab-content">
                <div class="panel">
                    <h2><i class="fas fa-user-plus"></i> Cadastrar Novo Funcionário</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="cadastrar_funcionario">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome Completo*</label>
                                <input type="text" name="nome_funcionario" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email*</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">CPF*</label>
                                <input type="text" name="cpf" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cargo*</label>
                                <select name="cargo" class="form-select" required>
                                    <option value="admin">Administrador</option>
                                    <option value="personal_trainer">Personal Trainer</option>
                                    <option value="recepcionista">Recepcionista</option>
                                    <option value="gerente">Gerente</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Senha Inicial*</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cadastrar Funcionário
                        </button>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-user-tie"></i> Funcionários Ativos</h2>
                    <?php if (!empty($funcionarios)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>CPF</th>
                                        <th>Telefone</th>
                                        <th>Cargo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($funcionarios as $func): ?>
                                        <tr>
                                            <td><?php echo $func['id_funcionario']; ?></td>
                                            <td><?php echo htmlspecialchars($func['nome_funcionario']); ?></td>
                                            <td><?php echo htmlspecialchars($func['email']); ?></td>
                                            <td><?php echo htmlspecialchars($func['cpf']); ?></td>
                                            <td><?php echo htmlspecialchars($func['telefone'] ?? '-'); ?></td>
                                            <td><span class="badge badge-success"><?php echo strtoupper(str_replace('_', ' ', $func['cargo'])); ?></span></td>
                                            <td>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Desativar este funcionário?');">
                                                    <input type="hidden" name="action" value="desativar_funcionario">
                                                    <input type="hidden" name="id_funcionario" value="<?php echo $func['id_funcionario']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-ban"></i> Desativar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>Nenhum funcionário cadastrado</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB: NOTIFICAÇÕES -->
            <div id="tab-notificacoes" class="tab-content">
                <div class="panel">
                    <h2><i class="fas fa-bell"></i> Enviar Notificação Geral</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="enviar_notificacao">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Título*</label>
                                <input type="text" name="titulo" class="form-control" placeholder="Ex: Manutenção Programada" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Prioridade*</label>
                                <select name="prioridade" class="form-select" required>
                                    <option value="baixa">Baixa</option>
                                    <option value="media" selected>Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mensagem*</label>
                            <textarea name="mensagem" class="form-control" rows="5" placeholder="Digite a mensagem que será enviada para TODOS os alunos..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar Notificação para Todos
                        </button>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-history"></i> Histórico de Notificações</h2>
                    <?php if (!empty($notificacoes)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Mensagem</th>
                                        <th>Prioridade</th>
                                        <th>Data de Envio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notificacoes as $notif): ?>
                                        <tr>
                                            <td><?php echo $notif['id_notificacao']; ?></td>
                                            <td><?php echo htmlspecialchars($notif['titulo']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($notif['mensagem'], 0, 50)) . '...'; ?></td>
                                            <td>
                                                <?php 
                                                    $badgeClass = $notif['prioridade'] === 'alta' ? 'danger' : ($notif['prioridade'] === 'media' ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo strtoupper($notif['prioridade']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($notif['data_envio'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>Nenhuma notificação enviada</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Gerenciamento de Tabs
        function openTab(evt, tabId) {
            const tabContents = document.querySelectorAll('.tab-content');
            const tabs = document.querySelectorAll('.tab');
            
            tabContents.forEach(content => content.classList.remove('active'));
            tabs.forEach(tab => tab.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        // Funções de Edição (com modais simples)
        function editarAluno(aluno) {
            const nome = prompt('Nome:', aluno.nome_cliente);
            const email = prompt('Email:', aluno.email);
            const telefone = prompt('Telefone:', aluno.telefone);
            const plano = prompt('Plano (basico/premium/vip):', aluno.plano);
            
            if (nome && email) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="editar_aluno">
                    <input type="hidden" name="id_cliente" value="${aluno.id_cliente}">
                    <input type="hidden" name="nome" value="${nome}">
                    <input type="hidden" name="email" value="${email}">
                    <input type="hidden" name="telefone" value="${telefone}">
                    <input type="hidden" name="plano" value="${plano}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editarAula(aula) {
            const nome = prompt('Nome da Aula:', aula.nome_aula);
            const professor = prompt('Professor:', aula.professor);
            const vagas = prompt('Vagas Totais:', aula.vagas_totais);
            
            if (nome && professor && vagas) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="editar_aula">
                    <input type="hidden" name="id_aula" value="${aula.id_aula}">
                    <input type="hidden" name="nome_aula" value="${nome}">
                    <input type="hidden" name="dia_semana" value="${aula.dia_semana}">
                    <input type="hidden" name="horario" value="${aula.horario}">
                    <input type="hidden" name="professor" value="${professor}">
                    <input type="hidden" name="vagas_totais" value="${vagas}">
                    <input type="hidden" name="descricao" value="${aula.descricao || ''}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editarProduto(produto) {
            const nome = prompt('Nome do Produto:', produto.nome_produto);
            const preco = prompt('Preço (R$):', produto.preco);
            const estoque = prompt('Quantidade em Estoque:', produto.quantidade_estoque);
            
            if (nome && preco && estoque) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="editar_produto">
                    <input type="hidden" name="id_produtos" value="${produto.id_produtos}">
                    <input type="hidden" name="nome_produto" value="${nome}">
                    <input type="hidden" name="tipo_produto" value="${produto.tipo_produto}">
                    <input type="hidden" name="categoria" value="${produto.categoria || ''}">
                    <input type="hidden" name="preco" value="${preco}">
                    <input type="hidden" name="quantidade_estoque" value="${estoque}">
                    <input type="hidden" name="url_imagem" value="${produto.url_imagem || ''}">
                    <input type="hidden" name="descricao" value="${produto.descricao || ''}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-hide alerts após 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
