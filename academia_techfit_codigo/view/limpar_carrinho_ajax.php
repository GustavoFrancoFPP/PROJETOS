<?php
/**
 * Limpa completamente a sessão do carrinho - versão JSON para AJAX
 */

// Inicia sessão se não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Headers para JSON
header('Content-Type: application/json');

// Debug: mostra estado ANTES
$antes = isset($_SESSION['carrinho_planos']) ? $_SESSION['carrinho_planos'] : null;

// Limpa o carrinho
$_SESSION['carrinho_planos'] = [
    'itens' => [],
    'subtotal' => 0
];

// Limpa também outras variáveis relacionadas
unset($_SESSION['order']);
unset($_SESSION['pedido_id']);

// Debug: mostra estado DEPOIS
$depois = $_SESSION['carrinho_planos'];

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Carrinho limpo completamente',
    'antes' => $antes,
    'depois' => $depois,
    'session_id' => session_id()
]);
?>
