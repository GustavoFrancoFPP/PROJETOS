<?php
// Script para corrigir coluna id_produtos na tabela venda
require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "Verificando estrutura atual de venda...\n";
    $result = $conn->query("DESCRIBE venda");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nColunas atuais:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
    
    echo "Removendo constraint de foreign key...\n";
    try {
        $conn->exec("ALTER TABLE venda DROP FOREIGN KEY venda_ibfk_2");
        echo "✓ Foreign key removida.\n";
    } catch (Exception $e) {
        echo "⚠ Foreign key não encontrada (OK).\n";
    }
    
    echo "Modificando coluna id_produtos para VARCHAR(50)...\n";
    $conn->exec("ALTER TABLE venda MODIFY id_produtos VARCHAR(50) NOT NULL");
    
    echo "✓ Coluna modificada com sucesso!\n";
    
    echo "\nNova estrutura de venda:\n";
    $result = $conn->query("DESCRIBE venda");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
    
    echo "\n✓ Pronto! A tabela agora aceita planos (strings) e produtos (números).\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
