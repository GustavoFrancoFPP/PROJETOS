<?php
$extra_head = '<link rel="stylesheet" href="confirmacao.css">';
require_once __DIR__ . '/inc/header.php';

if (session_status() == PHP_SESSION_NONE) session_start();
$order = $_SESSION['order'] ?? null;
?>

<div class="confirmacao-container">
    <div class="container">
        <div class="confirmacao-header">
            <h1><i class="fas fa-check-circle"></i> Pedido Confirmado</h1>
        </div>

        <div class="confirmacao-content">
            <?php if ($order): ?>
                <div class="resumo-card">
                    <h3>Resumo do Pedido</h3>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($order['nome']); ?></p>
                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($order['cpf']); ?></p>
                    <p><strong>Endereço:</strong> <?php echo htmlspecialchars($order['endereco'] . ' , ' . $order['numero'] . ' - ' . $order['bairro'] . ' - ' . $order['cidade'] . '/' . $order['estado']); ?></p>
                    <p><strong>Método de Pagamento:</strong> <?php echo htmlspecialchars($order['metodo_pagamento']); ?></p>
                    <p>Seu pedido foi registrado com sucesso. Em breve você receberá um e-mail com o comprovante.</p>
                </div>
            <?php else: ?>
                <p>Nenhum pedido encontrado. Por favor, volte ao carrinho e tente novamente.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
