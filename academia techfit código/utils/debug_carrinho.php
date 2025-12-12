<?php
/**
 * Debug do carrinho - Mostra estado atual
 */

session_start();

echo "=== DEBUG DO CARRINHO ===\n\n";
echo "Session ID: " . session_id() . "\n\n";

if (isset($_SESSION['carrinho_planos'])) {
    echo "Carrinho existe na sessão: SIM\n";
    echo "Número de itens: " . count($_SESSION['carrinho_planos']['itens']) . "\n";
    echo "Subtotal: R$ " . number_format($_SESSION['carrinho_planos']['subtotal'], 2, ',', '.') . "\n\n";
    
    if (count($_SESSION['carrinho_planos']['itens']) > 0) {
        echo "=== ITENS NO CARRINHO ===\n\n";
        foreach ($_SESSION['carrinho_planos']['itens'] as $index => $item) {
            echo "Item " . ($index + 1) . ":\n";
            echo "  Tipo: " . ($item['tipo'] ?? 'não definido') . "\n";
            echo "  ID: " . ($item['id'] ?? 'não definido') . "\n";
            echo "  Nome: " . ($item['nome'] ?? 'não definido') . "\n";
            echo "  Preço: R$ " . number_format($item['preco'] ?? 0, 2, ',', '.') . "\n";
            echo "  Quantidade: " . ($item['quantidade'] ?? 1) . "\n";
            echo "  Subtotal: R$ " . number_format($item['subtotal'] ?? 0, 2, ',', '.') . "\n";
            echo "\n";
        }
    } else {
        echo "✓ Carrinho está vazio (isso é normal)\n";
    }
} else {
    echo "Carrinho NÃO existe na sessão\n";
    echo "Inicializando carrinho...\n";
    $_SESSION['carrinho_planos'] = [
        'itens' => [],
        'subtotal' => 0
    ];
    echo "✓ Carrinho inicializado\n";
}

echo "\n=== OUTRAS VARIÁVEIS DE SESSÃO ===\n";
foreach ($_SESSION as $key => $value) {
    if ($key !== 'carrinho_planos') {
        echo "$key: " . (is_array($value) ? 'Array' : $value) . "\n";
    }
}
?>
