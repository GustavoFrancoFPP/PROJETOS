<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    $tabelas = ['cliente', 'funcionario', 'produtos', 'aulas', 'agendamento', 'login', 'planos'];
    
    foreach ($tabelas as $tabela) {
        echo "=== TABELA: $tabela ===\n";
        try {
            $stmt = $conn->query("DESCRIBE $tabela");
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($colunas as $coluna) {
                echo "  - {$coluna['Field']} ({$coluna['Type']})\n";
            }
        } catch (PDOException $e) {
            echo "  ❌ Tabela não existe\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
