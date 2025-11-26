<?php
session_start();
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'funcionario') {
    header('Location: login.php');
    exit();
}

// Conexão com banco de dados
$servidor = "localhost";
$usuario = "root";
$senha = "senaisp";
$banco = "academia";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}
$conexao->set_charset("utf8");

// Buscar dados do banco
$clientes = $conexao->query("SELECT * FROM cliente ORDER BY id_cliente DESC");
$agendamentos = $conexao->query("SELECT a.*, c.nome_cliente FROM agendamento a LEFT JOIN cliente c ON a.id_cliente = c.id_cliente ORDER BY a.data_agendamento DESC");
$produtos = $conexao->query("SELECT p.*, c.nome_cliente FROM produtos p LEFT JOIN cliente c ON p.id_cliente = c.id_cliente ORDER BY p.id_produtos DESC");
$planos = $conexao->query("SELECT pl.*, c.nome_cliente, f.nome_funcionario FROM planos pl LEFT JOIN cliente c ON pl.id_cliente = c.id_cliente LEFT JOIN funcionario f ON pl.id_funcionario = f.id_funcionario ORDER BY pl.id_planos DESC");
$pagamentos = $conexao->query("SELECT pg.*, c.nome_cliente, pl.nome_planos FROM pagamento pg LEFT JOIN cliente c ON pg.id_cliente = c.id_cliente LEFT JOIN planos pl ON pg.id_planos = pl.id_planos ORDER BY pg.data_pagamento DESC");
$suportes = $conexao->query("SELECT s.*, c.nome_cliente FROM suporte s LEFT JOIN cliente c ON s.id_cliente = c.id_cliente ORDER BY s.id_suporte DESC");
$funcionarios = $conexao->query("SELECT * FROM funcionario ORDER BY id_funcionario DESC");
$avaliacoes = $conexao->query("SELECT a.*, c.nome_cliente FROM avaliacao a LEFT JOIN agendamento ag ON a.id_avaliacao = ag.id_avaliacao LEFT JOIN cliente c ON ag.id_cliente = c.id_cliente ORDER BY a.data_avaliacao DESC");
$mensagens = $conexao->query("SELECT * FROM mensagem ORDER BY data_envio DESC");
$log_acessos = $conexao->query("SELECT * FROM log_acesso ORDER BY data_login DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - TECHFIT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #333; }
        
        .header { 
            background: #2c3e50; 
            color: white; 
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header h1 { font-size: 1.5rem; }
        
        .user-info { display: flex; align-items: center; gap: 1rem; }
        
        .btn { 
            background: #3498db; 
            color: white; 
            padding: 8px 15px; 
            text-decoration: none; 
            border-radius: 5px; 
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn.logout { background: #e74c3c; }
        .btn.success { background: #27ae60; }
        .btn.warning { background: #f39c12; }
        
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 20px; }
        
        .stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 2rem;
        }
        
        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number { 
            font-size: 2rem; 
            font-weight: bold; 
            color: #2c3e50; 
            margin: 10px 0;
        }
        
        .section { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section h2 { 
            color: #2c3e50; 
            margin-bottom: 1rem; 
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #34495e;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-pago { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; }
        .status-pendente { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; }
        .status-ativo { background: #d1ecf1; color: #0c5460; padding: 4px 8px; border-radius: 4px; }
        .status-inativo { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .tab {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .search-box {
            margin-bottom: 1rem;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👨‍💼 Painel Administrativo - TECHFIT</h1>
        <div class="user-info">
            <span>Bem-vindo, <strong><?php echo $_SESSION['usuario_nome']; ?></strong></span>
            <a href="inicio.html" class="btn">Ir para Site</a>
            <a href="logout.php" class="btn logout">Sair</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Estatísticas -->
        <div class="stats">
            <div class="stat-card">
                <div>👥 Total Clientes</div>
                <div class="stat-number"><?php echo $clientes->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <div>📅 Agendamentos</div>
                <div class="stat-number"><?php echo $agendamentos->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <div>💰 Pagamentos</div>
                <div class="stat-number"><?php echo $pagamentos->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <div>🛒 Produtos Vendidos</div>
                <div class="stat-number"><?php echo $produtos->num_rows; ?></div>
            </div>
        </div>

        <!-- Tabs de Navegação -->
        <div class="tabs">
            <button class="tab active" onclick="openTab('clientes')">👥 Clientes</button>
            <button class="tab" onclick="openTab('agendamentos')">📅 Agendamentos</button>
            <button class="tab" onclick="openTab('pagamentos')">💰 Pagamentos</button>
            <button class="tab" onclick="openTab('produtos')">🛒 Produtos</button>
            <button class="tab" onclick="openTab('planos')">💎 Planos</button>
            <button class="tab" onclick="openTab('suporte')">🔧 Suporte</button>
            <button class="tab" onclick="openTab('logs')">📊 Logs de Acesso</button>
        </div>

        <!-- Tab: Clientes -->
        <div id="clientes" class="tab-content active">
            <div class="section">
                <h2>👥 Clientes Cadastrados</h2>
                <input type="text" class="search-box" placeholder="🔍 Buscar cliente..." onkeyup="filterTable('clientesTable', this.value)">
                <table id="clientesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>CPF</th>
                            <th>Endereço</th>
                            <th>Status</th>
                            <th>Data Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($cliente = $clientes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $cliente['id_cliente']; ?></td>
                            <td><strong><?php echo $cliente['nome_cliente']; ?></strong></td>
                            <td><?php echo $cliente['email']; ?></td>
                            <td><?php echo $cliente['telefone']; ?></td>
                            <td><?php echo $cliente['cpf']; ?></td>
                            <td><?php echo $cliente['endereco']; ?></td>
                            <td><span class="status-ativo"><?php echo $cliente['status'] ?? 'ativo'; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'] ?? '2024-01-01')); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Agendamentos -->
        <div id="agendamentos" class="tab-content">
            <div class="section">
                <h2>📅 Agendamentos de Aulas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Aula</th>
                            <th>Data Agendamento</th>
                            <th>Avaliação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($agendamento = $agendamentos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $agendamento['id_agendamento']; ?></td>
                            <td><strong><?php echo $agendamento['nome_cliente'] ?? 'N/A'; ?></strong></td>
                            <td><?php echo $agendamento['tipo_aula']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?></td>
                            <td><?php echo $agendamento['id_avaliacao'] ? '✅' : '❌'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Pagamentos -->
        <div id="pagamentos" class="tab-content">
            <div class="section">
                <h2>💰 Pagamentos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Plano</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($pagamento = $pagamentos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $pagamento['id_pagamento']; ?></td>
                            <td><strong><?php echo $pagamento['nome_cliente'] ?? 'N/A'; ?></strong></td>
                            <td><?php echo $pagamento['nome_planos'] ?? 'N/A'; ?></td>
                            <td>R$ <?php echo number_format($pagamento['valor_pago'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])); ?></td>
                            <td><span class="status-<?php echo $pagamento['status_pagamento'] == 'pago' ? 'pago' : 'pendente'; ?>">
                                <?php echo $pagamento['status_pagamento']; ?>
                            </span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Produtos -->
        <div id="produtos" class="tab-content">
            <div class="section">
                <h2>🛒 Produtos Vendidos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Cliente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($produto = $produtos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $produto['id_produtos']; ?></td>
                            <td><strong><?php echo $produto['nome_produto']; ?></strong></td>
                            <td><?php echo $produto['tipo_produto']; ?></td>
                            <td><?php echo $produto['categoria']; ?></td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo $produto['quantidade']; ?></td>
                            <td><?php echo $produto['nome_cliente'] ?? 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Planos -->
        <div id="planos" class="tab-content">
            <div class="section">
                <h2>💎 Planos Contratados</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Plano</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Cliente</th>
                            <th>Funcionário</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($plano = $planos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $plano['id_planos']; ?></td>
                            <td><strong><?php echo $plano['nome_planos']; ?></strong></td>
                            <td><?php echo $plano['descricao']; ?></td>
                            <td>R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></td>
                            <td><?php echo $plano['nome_cliente'] ?? 'N/A'; ?></td>
                            <td><?php echo $plano['nome_funcionario'] ?? 'N/A'; ?></td>
                            <td><span class="status-ativo"><?php echo $plano['status'] ?? 'ativo'; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Suporte -->
        <div id="suporte" class="tab-content">
            <div class="section">
                <h2>🔧 Tickets de Suporte</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($suporte = $suportes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $suporte['id_suporte']; ?></td>
                            <td><strong><?php echo $suporte['nome_cliente'] ?? 'N/A'; ?></strong></td>
                            <td><?php echo $suporte['descricao']; ?></td>
                            <td><?php echo $suporte['categoria_suporte']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Logs -->
        <div id="logs" class="tab-content">
            <div class="section">
                <h2>📊 Últimos Acessos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário ID</th>
                            <th>Tipo</th>
                            <th>Data/Hora</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($log = $log_acessos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['id_log']; ?></td>
                            <td><?php echo $log['id_usuario']; ?></td>
                            <td><?php echo $log['tipo_usuario']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($log['data_login'])); ?></td>
                            <td><?php echo $log['ip_usuario']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            // Esconde todas as tabs
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Remove active de todas as tabs
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            // Mostra a tab selecionada
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        function filterTable(tableId, searchText) {
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchText.toLowerCase())) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }
        
        // Auto-refresh a cada 30 segundos
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>