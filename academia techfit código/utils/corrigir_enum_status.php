<?php
/**
 * Atualiza o ENUM da coluna status_pagamento para incluir 'cancelado'
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== ATUALIZANDO ENUM status_pagamento ===\n\n";
    
    // Altera a coluna para incluir 'cancelado'
    $sql = "ALTER TABLE pagamento 
            MODIFY COLUMN status_pagamento 
            ENUM('pago', 'pendente', 'cancelado') 
            DEFAULT 'pendente'";
    
    $conn->exec($sql);
    
    echo "✅ Coluna atualizada com sucesso!\n\n";
    
    // Verifica a alteração
    $stmt = $conn->query("SHOW COLUMNS FROM pagamento WHERE Field = 'status_pagamento'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Novo tipo: " . $coluna['Type'] . "\n";
    echo "\nAgora a coluna aceita: pago, pendente e cancelado\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
