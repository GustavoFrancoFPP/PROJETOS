<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICANDO ESTRUTURA DA TABELA VENDA ===\n\n";
    
    // Mostra a estrutura atual
    $stmt = $conn->query("DESCRIBE venda");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Colunas atuais:\n";
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})\n";
    }
    
    // Verifica se a tabela tem a estrutura correta
    $colunasNecessarias = ['id_venda', 'id_cliente', 'id_produtos', 'quantidade', 'data_venda', 'valor_total'];
    $colunasExistentes = array_column($colunas, 'Field');
    
    $faltando = array_diff($colunasNecessarias, $colunasExistentes);
    $extras = array_diff($colunasExistentes, $colunasNecessarias);
    
    if (!empty($faltando)) {
        echo "\n❌ COLUNAS FALTANDO: " . implode(', ', $faltando) . "\n";
    }
    
    if (!empty($extras)) {
        echo "\n⚠️  COLUNAS EXTRAS: " . implode(', ', $extras) . "\n";
        
        // Se houver coluna 'email' extra, remove
        if (in_array('email', $extras)) {
            echo "\n🔧 Removendo coluna 'email' indevida...\n";
            $conn->exec("ALTER TABLE venda DROP COLUMN email");
            echo "✓ Coluna 'email' removida com sucesso!\n";
        }
    }
    
    if (empty($faltando) && empty($extras)) {
        echo "\n✓ Estrutura da tabela está correta!\n";
    }
    
    echo "\n=== ESTRUTURA FINAL ===\n";
    $stmt = $conn->query("DESCRIBE venda");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
