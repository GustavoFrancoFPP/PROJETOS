<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICANDO ESTRUTURA DA TABELA FUNCIONARIO ===\n\n";
    
    // Mostra a estrutura atual
    $stmt = $conn->query("DESCRIBE funcionario");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Colunas atuais:\n";
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
