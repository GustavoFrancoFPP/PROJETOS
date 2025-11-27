<?php
// Apenas usuários 'techfit' podem acessar, garantido pelo autenticacao.php
require_once 'autenticacao.php'; 
require_once __DIR__ . '/modelo/Connection.php'; 

$conn = Connection::getInstance();

// Funções de busca no banco de dados
function listarUsuarios($conn) {
    $stmt = $conn->query("SELECT nome_usuario, tipo_usuario FROM login ORDER BY nome_usuario");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarVendas($conn) {
    // Compras realizadas ou em andamento (vendas de produtos)
    $sql = "SELECT 
                v.id_venda, 
                c.nome_cliente, 
                p.nome_produto, 
                v.quantidade, 
                v.valor_total, 
                v.data_venda
            FROM venda v
            JOIN cliente c ON v.id_cliente = c.id_cliente
            JOIN produtos p ON v.id_produtos = p.id_produtos
            ORDER BY v.data_venda DESC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarPagamentos($conn) {
    // Pagamentos de planos (Status pago/pendente)
    $sql = "SELECT 
                p.id_pagamento, 
                c.nome_cliente, 
                pl.nome_planos, 
                p.valor_pago, 
                p.status_pagamento, 
                p.data_pagamento
            FROM pagamento p
            JOIN cliente c ON p.id_cliente = c.id_cliente
            JOIN planos pl ON p.id_planos = pl.id_planos
            ORDER BY p.data_pagamento DESC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$usuarios = listarUsuarios($conn);
$vendas = listarVendas($conn);
$pagamentos = listarPagamentos($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - TechFit</title>
    <link rel="stylesheet" href="login.css"> <style>
        body { padding-top: 100px; } /* Ajuste para o header fixo */
        .container-admin { max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: var(--cor-ciano-principal); border-bottom: 2px solid #333; padding-bottom: 10px; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background-color: var(--cor-card-escuro); color: var(--cor-texto-primario); }
        th, td { border: 1px solid #333; padding: 12px; text-align: left; }
        th { background-color: #222; }
        .pago { color: var(--cor-ciano-principal); font-weight: bold; }
        .pendente { color: orange; font-weight: bold; }
        .actions { margin-bottom: 20px; text-align: right; }
        .actions a { color: var(--cor-ciano-principal); margin-left: 15px; text-decoration: none; }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header class="techfit-header">...</header>

    <div class="container-admin">
        <h1>🛠️ Painel Administrativo - Bem-vindo, <?php echo $nome_usuario_logado; ?></h1>
        <div class="actions">
            <a href="index.php">Voltar para a Área do Cliente</a> | 
            <a href="?logout=true">Sair</a>
        </div>

        <hr>

        <h2>👥 Usuários Cadastrados (Tabela `login`)</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome de Usuário</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nome_usuario']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($usuario['tipo_usuario'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>🛒 Compras/Vendas de Produtos (Tabela `venda`)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Venda</th>
                    <th>Cliente</th>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Valor Total</th>
                    <th>Data da Venda</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendas as $venda): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($venda['id_venda']); ?></td>
                        <td><?php echo htmlspecialchars($venda['nome_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($venda['nome_produto']); ?></td>
                        <td>R$ <?php echo number_format($venda['quantidade'], 0, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>💰 Status dos Pagamentos de Planos (Tabela `pagamento`)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Pag.</th>
                    <th>Cliente</th>
                    <th>Plano</th>
                    <th>Valor Pago</th>
                    <th>Status</th>
                    <th>Data Pagamento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $pagamento): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pagamento['id_pagamento']); ?></td>
                        <td><?php echo htmlspecialchars($pagamento['nome_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($pagamento['nome_planos']); ?></td>
                        <td>R$ <?php echo number_format($pagamento['valor_pago'], 2, ',', '.'); ?></td>
                        <td class="<?php echo $pagamento['status_pagamento']; ?>">
                            <?php echo htmlspecialchars(ucfirst($pagamento['status_pagamento'])); ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer class="main-footer">...</footer>
</body>
</html>