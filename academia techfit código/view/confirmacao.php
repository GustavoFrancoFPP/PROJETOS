<?php
$extra_head = '<link rel="stylesheet" href="assets/css/confirmacao.css">';
require_once __DIR__ . '/inc/header.php';

if (session_status() == PHP_SESSION_NONE) session_start();
$order = $_SESSION['order'] ?? null;
$pedido_id = $_SESSION['pedido_id'] ?? ($_GET['pedido_id'] ?? null);

// Buscar dados do pedido se tiver ID
$numeroPedido = null;
$totalPedido = 0;
$itensPedido = [];

if ($pedido_id) {
    try {
        require_once __DIR__ . '/../config/Connection.php';
        $conn = Connection::getInstance();
        $stmt = $conn->prepare("SELECT numero_pedido, total, itens FROM pedidos WHERE id_pedido = ?");
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido) {
            $numeroPedido = $pedido['numero_pedido'];
            $totalPedido = $pedido['total'];
            $itensPedido = json_decode($pedido['itens'], true) ?? [];
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar pedido: " . $e->getMessage());
    }
}
?>

<div class="confirmacao-container">
    <div class="container">
        <div class="confirmacao-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Pedido Confirmado com Sucesso!</h1>
            <?php if ($numeroPedido): ?>
                <p class="numero-pedido">Número do Pedido: <strong><?php echo htmlspecialchars($numeroPedido); ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="confirmacao-content">
            <?php if ($order): ?>
                <div class="resumo-card">
                    <h3><i class="fas fa-user"></i> Dados do Cliente</h3>
                    <div class="dados-cliente">
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($order['nome']); ?></p>
                        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p><strong>CPF:</strong> <?php echo htmlspecialchars($order['cpf']); ?></p>
                        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($order['telefone'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="resumo-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h3>
                    <div class="endereco">
                        <p><?php echo htmlspecialchars($order['endereco'] . ', ' . $order['numero']); ?></p>
                        <?php if (!empty($order['complemento'])): ?>
                            <p><?php echo htmlspecialchars($order['complemento']); ?></p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($order['bairro'] . ' - ' . $order['cidade'] . '/' . $order['estado']); ?></p>
                        <p>CEP: <?php echo htmlspecialchars($order['cep']); ?></p>
                    </div>
                </div>

                <?php if (!empty($order['itens']) || !empty($itensPedido)): ?>
                <div class="resumo-card">
                    <h3><i class="fas fa-shopping-bag"></i> Itens do Pedido</h3>
                    <div class="itens-pedido">
                        <?php 
                        $itensExibir = !empty($order['itens']) ? $order['itens'] : $itensPedido;
                        $totalExibir = !empty($order['total']) ? $order['total'] : $totalPedido;
                        
                        // Separar planos e produtos
                        $planos = [];
                        $produtos = [];
                        foreach ($itensExibir as $item) {
                            $tipoItem = $item['tipo'] ?? 'produto';
                            if ($tipoItem === 'plano') {
                                $planos[] = $item;
                            } else {
                                $produtos[] = $item;
                            }
                        }
                        
                        // Exibir planos primeiro
                        if (!empty($planos)):
                        ?>
                            <div class="secao-planos">
                                <h4 style="color: #00F0E1; font-size: 1.1rem; margin-bottom: 15px; border-bottom: 2px solid #00F0E1; padding-bottom: 10px;">
                                    <i class="fas fa-crown"></i> Planos Contratados
                                </h4>
                                <?php foreach ($planos as $plano): ?>
                                    <div class="item-pedido plano-item" style="background: rgba(0, 240, 225, 0.1); border-left: 4px solid #00F0E1; padding: 15px; margin-bottom: 10px;">
                                        <span class="item-nome" style="font-weight: 700; color: #00F0E1;">
                                            <i class="fas fa-crown"></i>
                                            <?php echo htmlspecialchars($plano['nome'] ?? 'Plano'); ?>
                                        </span>
                                        <span class="item-qtd">Duração: <?php echo intval($plano['duracao_meses'] ?? 1); ?> mês(es)</span>
                                        <span class="item-preco" style="font-weight: 700; color: #00F0E1;">R$ <?php echo number_format($plano['preco'] ?? 0, 2, ',', '.'); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        // Exibir produtos
                        if (!empty($produtos)):
                        ?>
                            <div class="secao-produtos" style="margin-top: 20px;">
                                <h4 style="color: #fff; font-size: 1.1rem; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px;">
                                    <i class="fas fa-box"></i> Produtos
                                </h4>
                                <?php foreach ($produtos as $produto): ?>
                                    <div class="item-pedido">
                                        <span class="item-nome">
                                            <i class="fas fa-box"></i>
                                            <?php echo htmlspecialchars($produto['nome'] ?? 'Produto'); ?>
                                        </span>
                                        <span class="item-qtd">Qtd: <?php echo intval($produto['quantidade'] ?? 1); ?></span>
                                        <span class="item-preco">R$ <?php echo number_format($produto['preco'] ?? 0, 2, ',', '.'); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="total-pedido" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #00F0E1;">
                            <strong style="font-size: 1.3rem; color: #00F0E1;">Total: R$ <?php echo number_format($totalExibir, 2, ',', '.'); ?></strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="resumo-card">
                    <h3><i class="fas fa-credit-card"></i> Pagamento</h3>
                    <p><strong>Método:</strong> <?php 
                        $metodos = [
                            'cartao' => 'Cartão de Crédito',
                            'pix' => 'PIX',
                            'boleto' => 'Boleto Bancário',
                            'dinheiro' => 'Dinheiro'
                        ];
                        echo $metodos[$order['metodo_pagamento']] ?? ucfirst($order['metodo_pagamento']); 
                    ?></p>
                </div>

                <div class="mensagem-sucesso">
                    <p><i class="fas fa-info-circle"></i> Seu pedido foi registrado com sucesso!</p>
                    <p>Em breve você receberá um e-mail com o comprovante e informações de rastreamento.</p>
                    <p>Guarde o número do seu pedido para acompanhamento.</p>
                </div>

                <div class="acoes">
                    <a href="produtos_loja.php" class="btn-primary"><i class="fas fa-shopping-cart"></i> Continuar Comprando</a>
                    <a href="dashboard_aluno.php" class="btn-secondary"><i class="fas fa-home"></i> Ir para Dashboard</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Nenhum pedido encontrado. Por favor, volte ao <a href="carrinho.php">carrinho</a> e tente novamente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container footer-container">
        <div class="footer-brand">
            <a href="#" class="logo">
                <img src="assets/images/imagens/WhatsApp Image 2025-10-02 at 15.15.22.jpeg" alt="TechFit Logo" class="logo-img">
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
// Limpa o carrinho do localStorage após confirmar o pedido
if (typeof localStorage !== 'undefined') {
    localStorage.removeItem('carrinhoTechFit');
    localStorage.removeItem('pedidoTechFit');
    localStorage.removeItem('dadosCompraTechFit');
    console.log('✓ Carrinho limpo após confirmação do pedido');
}
</script>

<?php 
// Limpa a sessão do pedido após exibir
unset($_SESSION['order']);
unset($_SESSION['pedido_id']);
?>

<script src="assets/js/header-carrinho-simples.js"></script>
</body>
</html>
