<?php
/**
 * Limpa o carrinho da sessão
 */

session_start();

echo "=== LIMPANDO CARRINHO ===\n\n";

if (isset($_SESSION['carrinho_planos'])) {
    echo "Carrinho antes de limpar:\n";
    print_r($_SESSION['carrinho_planos']);
    echo "\n";
}

// Limpa o carrinho
$_SESSION['carrinho_planos'] = [
    'itens' => [],
    'subtotal' => 0
];

echo "✅ Carrinho limpo com sucesso!\n";
echo "\nCarrinho depois de limpar:\n";
print_r($_SESSION['carrinho_planos']);
?>
