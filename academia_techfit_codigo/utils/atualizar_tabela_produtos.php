<?php
/**
 * Script de Verificação e Atualização da Tabela Produtos
 * Corrige coluna quantidade para quantidade_estoque
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICAÇÃO DA TABELA PRODUTOS ===\n\n";
    
    // Verificar estrutura atual
    $stmt = $conn->query("DESCRIBE produtos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estrutura atual da tabela:\n";
    foreach ($colunas as $col) {
        echo sprintf("  %-25s %-30s\n", $col['Field'], $col['Type']);
    }
    echo "\n";
    
    $nomesColuna = array_column($colunas, 'Field');
    
    // Verificar se existe 'quantidade' e não existe 'quantidade_estoque'
    if (in_array('quantidade', $nomesColuna) && !in_array('quantidade_estoque', $nomesColuna)) {
        echo "⚠️  Encontrada coluna 'quantidade' - Renomeando para 'quantidade_estoque'...\n";
        $conn->exec("ALTER TABLE produtos CHANGE COLUMN quantidade quantidade_estoque INT NOT NULL DEFAULT 0");
        echo "✅ Coluna renomeada com sucesso!\n\n";
    } elseif (in_array('quantidade_estoque', $nomesColuna)) {
        echo "✅ Coluna 'quantidade_estoque' já existe e está correta!\n\n";
    } else {
        echo "❌ Coluna de quantidade não encontrada. Adicionando 'quantidade_estoque'...\n";
        $conn->exec("ALTER TABLE produtos ADD COLUMN quantidade_estoque INT NOT NULL DEFAULT 0 AFTER preco");
        echo "✅ Coluna 'quantidade_estoque' adicionada!\n\n";
    }
    
    // Verificar outras colunas importantes
    $colunasNecessarias = [
        'url_imagem' => "ALTER TABLE produtos ADD COLUMN url_imagem VARCHAR(500) AFTER quantidade_estoque",
        'descricao' => "ALTER TABLE produtos ADD COLUMN descricao TEXT AFTER url_imagem",
        'status' => "ALTER TABLE produtos ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo' AFTER descricao"
    ];
    
    foreach ($colunasNecessarias as $coluna => $sql) {
        if (!in_array($coluna, $nomesColuna)) {
            echo "⚠️  Coluna '$coluna' não encontrada. Adicionando...\n";
            $conn->exec($sql);
            echo "✅ Coluna '$coluna' adicionada!\n";
        } else {
            echo "✅ Coluna '$coluna' já existe\n";
        }
    }
    
    echo "\n=== ESTRUTURA FINAL ===\n\n";
    $stmt = $conn->query("SHOW COLUMNS FROM produtos");
    $estruturaFinal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estruturaFinal as $col) {
        echo sprintf("%-25s %-30s %-10s %-15s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Default'] ?? 'NULL'
        );
    }
    
    echo "\n✅ Atualização concluída com sucesso!\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
