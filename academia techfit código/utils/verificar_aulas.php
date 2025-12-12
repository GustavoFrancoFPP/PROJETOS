<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICANDO TABELAS DE AULAS ===\n\n";
    
    // Verifica se existe tabela 'aula', 'aulas' ou similar
    $stmt = $conn->query("SHOW TABLES LIKE '%aula%'");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tabelas)) {
        echo "❌ Nenhuma tabela relacionada a 'aula' encontrada\n";
    } else {
        foreach ($tabelas as $tabela) {
            echo "Tabela encontrada: $tabela\n";
            $stmt = $conn->query("DESCRIBE $tabela");
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Colunas:\n";
            foreach ($colunas as $coluna) {
                echo "  - {$coluna['Field']} ({$coluna['Type']})\n";
            }
            echo "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
