<?php
/**
 * Verifica planos existentes no banco
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    // Verifica estrutura da tabela
    echo "=== ESTRUTURA DA TABELA PLANOS ===\n";
    $stmt = $conn->query("DESCRIBE planos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colunas as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Verifica planos existentes
    echo "\n=== PLANOS CADASTRADOS ===\n";
    $stmt = $conn->query("SELECT * FROM planos");
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total: " . count($planos) . " planos\n\n";
    
    foreach ($planos as $plano) {
        print_r($plano);
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
