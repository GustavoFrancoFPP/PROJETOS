<?php
/**
 * Verifica produtos no banco de dados
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    // Verifica estrutura da tabela produtos
    echo "=== ESTRUTURA DA TABELA PRODUTOS ===\n";
    $stmt = $conn->query("DESCRIBE produtos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colunas as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Busca produtos
    echo "\n=== PRODUTOS CADASTRADOS ===\n";
    $stmt = $conn->query("SELECT * FROM produtos");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total: " . count($produtos) . " produtos\n\n";
    
    foreach ($produtos as $produto) {
        echo "ID: " . $produto['id_produto'] . "\n";
        echo "Nome: " . $produto['nome'] . "\n";
        echo "Preço: R$ " . $produto['preco'] . "\n";
        echo "Estoque: " . $produto['estoque'] . "\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
