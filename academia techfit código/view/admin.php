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

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            // ============ CRUD ALUNOS ============
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
                $stmt = $conn->prepare("INSERT INTO aulas (nome_aula, dia_semana, horario, instrutor, vagas_totais, descricao, status) VALUES (?, ?, ?, ?, ?, ?, 'ativa')");
                $stmt->execute([
                    $_POST['nome_aula'],
                    $_POST['dia_semana'],
                    $_POST['horario'],
                    $_POST['instrutor'],
                    $_POST['vagas_totais'],
                    $_POST['descricao'] ?? ''
                ]);
                $mensagem = "Aula cadastrada com sucesso!";
                break;
                
            case 'editar_aula':
                $stmt = $conn->prepare("UPDATE aulas SET nome_aula = ?, dia_semana = ?, horario = ?, instrutor = ?, vagas_totais = ?, descricao = ? WHERE id_aula = ?");
                $stmt->execute([
                    $_POST['nome_aula'],
                    $_POST['dia_semana'],
                    $_POST['horario'],
                    $_POST['instrutor'],
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
                $stmt = $conn->prepare("INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, quantidade, url_imagem, descricao) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nome_produto'],
                    $_POST['tipo_produto'],
                    $_POST['categoria'],
                    $_POST['preco'],
                    $_POST['quantidade'],
                    $_POST['url_imagem'] ?? '',
                    $_POST['descricao'] ?? ''
                ]);
                $mensagem = "Produto cadastrado com sucesso!";
                break;
                
            case 'editar_produto':
                $stmt = $conn->prepare("UPDATE produtos SET nome_produto = ?, tipo_produto = ?, categoria = ?, preco = ?, quantidade = ?, url_imagem = ?, descricao = ? WHERE id_produtos = ?");
                $stmt->execute([
                    $_POST['nome_produto'],
                    $_POST['tipo_produto'],
                    $_POST['categoria'],
                    $_POST['preco'],
                    $_POST['quantidade'],
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
                
                $stmt = $conn->prepare("INSERT INTO funcionario (nome_funcionario, cpf) VALUES (?, ?)");
                $stmt->execute([
                    $_POST['nome_funcionario'],
                    $_POST['cpf']
                ]);
                $id_funcionario = $conn->lastInsertId();
                
                $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO login (id_funcionario, email, senha, tipo_usuario) VALUES (?, ?, ?, 'funcionario')");
                $stmt->execute([$id_funcionario, $_POST['email'], $senha_hash]);
                
                $conn->commit();
                $mensagem = "Funcionário cadastrado com sucesso!";
                break;
                
            case 'desativar_funcionario':
                // Como não existe coluna status, vamos apenas deletar o funcionário
                $stmt = $conn->prepare("DELETE FROM funcionario WHERE id_funcionario = ?");
                $stmt->execute([$_POST['id_funcionario']]);
                $mensagem = "Funcionário removido com sucesso!";
                break;
                
            // ============ NOTIFICAÇÕES ============
            case 'enviar_notificacao':
                // Notificação requer id_cliente, então vamos enviar para todos os alunos
                $alunos = $conn->query("SELECT id_cliente FROM cliente")->fetchAll();
                $stmt = $conn->prepare("INSERT INTO notificacao (id_cliente, titulo, mensagem) VALUES (?, ?, ?)");
                foreach ($alunos as $aluno) {
                    $stmt->execute([
                        $aluno['id_cliente'],
                        $_POST['titulo'],
                        $_POST['mensagem']
                    ]);
                }
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
$totalProdutos = $conn->query("SELECT COUNT(*) as total FROM produtos")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Listas
$alunos = $conn->query("SELECT c.*, COUNT(a.id_agendamento) as total_agendamentos FROM cliente c LEFT JOIN agendamento a ON c.id_cliente = a.id_cliente GROUP BY c.id_cliente ORDER BY c.nome_cliente")->fetchAll(PDO::FETCH_ASSOC);

$aulas = $conn->query("SELECT *, (vagas_totais - vagas_ocupadas) as vagas_disponiveis FROM aulas WHERE status = 'ativa' ORDER BY dia_semana, horario")->fetchAll(PDO::FETCH_ASSOC);

$produtos = $conn->query("SELECT * FROM produtos ORDER BY nome_produto")->fetchAll(PDO::FETCH_ASSOC);

$funcionarios = $conn->query("SELECT * FROM funcionario ORDER BY nome_funcionario")->fetchAll(PDO::FETCH_ASSOC);

$notificacoes = $conn->query("SELECT * FROM notificacao ORDER BY data_envio DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - TECHFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="admin.php" class="logo">
                <i class="fas fa-dumbbell"></i> TECHFIT Admin
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="admin.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
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
                                    <option value="Plano Mensal">Plano Mensal</option>
                                    <option value="Plano Trimestral">Plano Trimestral</option>
                                    <option value="Plano Semestral">Plano Semestral</option>
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
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir este aluno permanentemente?');">
                                                    <input type="hidden" name="action" value="excluir_aluno">
                                                    <input type="hidden" name="id_cliente" value="<?php echo $aluno['id_cliente']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
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
                                <label class="form-label">Instrutor*</label>
                                <input type="text" name="instrutor" class="form-control" required>
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
                                        <th>Instrutor</th>
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
                                            <td><?php echo htmlspecialchars($aula['instrutor'] ?? ''); ?></td>
                                            <td><?php echo ($aula['vagas_totais'] - $aula['vagas_ocupadas']); ?> / <?php echo $aula['vagas_totais']; ?></td>
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
                                <input type="number" name="quantidade" class="form-control" min="0" required>
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
                                                    $estoque = $produto['quantidade'] ?? 0;
                                                    $badgeClass = $estoque < 10 ? 'danger' : ($estoque < 30 ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $estoque; ?> un.
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Desativar este produto?');">
                                                    <input type="hidden" name="action" value="desativar_produto">
                                                    <input type="hidden" name="id_produtos" value="<?php echo $produto['id_produtos']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-ban"></i>
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
                                            <td><?php echo htmlspecialchars($func['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($func['cpf']); ?></td>
                                            <td><?php echo htmlspecialchars($func['telefone'] ?? '-'); ?></td>
                                            <td><span class="badge badge-success"><?php echo strtoupper(str_replace('_', ' ', $func['cargo'] ?? 'funcionario')); ?></span></td>
                                            <td>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Desativar este funcionário?');">
                                                    <input type="hidden" name="action" value="desativar_funcionario">
                                                    <input type="hidden" name="id_funcionario" value="<?php echo $func['id_funcionario']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-ban"></i>
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
                                                    $prioridade = $notif['prioridade'] ?? 'media';
                                                    $badgeClass = $prioridade === 'alta' ? 'danger' : ($prioridade === 'media' ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo strtoupper($prioridade); ?>
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

        // Funções de Edição
        function editarAluno(aluno) {
            const nome = prompt('Nome:', aluno.nome_cliente);
            const email = prompt('Email:', aluno.email);
            const telefone = prompt('Telefone:', aluno.telefone);
            const plano = prompt('Plano (Plano Mensal/Plano Trimestral/Plano Semestral):', aluno.plano);
            
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
