<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICANDO ESTRUTURA DA TABELA PRODUTOS ===\n\n";
    
    $stmt = $conn->query("DESCRIBE produtos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Colunas atuais:\n";
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
