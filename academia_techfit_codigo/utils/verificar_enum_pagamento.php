<?php
/**
 * Verifica a estrutura da coluna status_pagamento
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICAÇÃO DA COLUNA status_pagamento ===\n\n";
    
    // Verifica estrutura da coluna
    $stmt = $conn->query("SHOW COLUMNS FROM pagamento WHERE Field = 'status_pagamento'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coluna) {
        echo "Campo: " . $coluna['Field'] . "\n";
        echo "Tipo: " . $coluna['Type'] . "\n";
        echo "Null: " . $coluna['Null'] . "\n";
        echo "Default: " . $coluna['Default'] . "\n\n";
        
        // Extrai valores do ENUM
        if (preg_match("/^enum\((.+)\)$/i", $coluna['Type'], $matches)) {
            $valores = str_getcsv($matches[1], ',', "'");
            echo "Valores permitidos no ENUM:\n";
            foreach ($valores as $valor) {
                echo "  - '$valor'\n";
            }
        }
    } else {
        echo "Coluna status_pagamento não encontrada!\n";
    }
    
    // Verifica valores atuais na tabela
    echo "\n=== VALORES ATUAIS NA TABELA ===\n";
    $stmt = $conn->query("SELECT DISTINCT status_pagamento FROM pagamento");
    $valores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($valores as $valor) {
        echo "  - '$valor'\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
