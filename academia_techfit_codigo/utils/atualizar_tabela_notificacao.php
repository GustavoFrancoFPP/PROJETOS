<?php
/**
 * Script de Atualização da Tabela de Notificações
 * Adiciona colunas faltantes na tabela notificacao
 * TechFit Academia
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== ATUALIZAÇÃO DA TABELA NOTIFICACAO ===\n\n";
    
    // Verificar estrutura atual
    $stmt = $conn->query("DESCRIBE notificacao");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Colunas existentes:\n";
    foreach ($colunas as $coluna) {
        echo "  - $coluna\n";
    }
    echo "\n";
    
    $alteracoes = [];
    
    // Verificar e adicionar coluna 'tipo'
    if (!in_array('tipo', $colunas)) {
        echo "❌ Coluna 'tipo' NÃO encontrada. Adicionando...\n";
        $conn->exec("ALTER TABLE notificacao ADD COLUMN tipo ENUM('geral', 'individual') DEFAULT 'geral' AFTER mensagem");
        $alteracoes[] = "tipo";
        echo "✅ Coluna 'tipo' adicionada com sucesso!\n\n";
    } else {
        echo "✅ Coluna 'tipo' já existe\n\n";
    }
    
    // Verificar e adicionar coluna 'prioridade'
    if (!in_array('prioridade', $colunas)) {
        echo "❌ Coluna 'prioridade' NÃO encontrada. Adicionando...\n";
        $conn->exec("ALTER TABLE notificacao ADD COLUMN prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media' AFTER status");
        $alteracoes[] = "prioridade";
        echo "✅ Coluna 'prioridade' adicionada com sucesso!\n\n";
    } else {
        echo "✅ Coluna 'prioridade' já existe\n\n";
    }
    
    // Verificar se id_cliente permite NULL
    $stmt = $conn->query("SHOW COLUMNS FROM notificacao LIKE 'id_cliente'");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($info && $info['Null'] === 'NO') {
        echo "⚠️  Coluna 'id_cliente' não permite NULL. Corrigindo...\n";
        $conn->exec("ALTER TABLE notificacao MODIFY COLUMN id_cliente INT NULL");
        $alteracoes[] = "id_cliente (NULL permitido)";
        echo "✅ Coluna 'id_cliente' atualizada!\n\n";
    } else {
        echo "✅ Coluna 'id_cliente' permite NULL\n\n";
    }
    
    // Verificar coluna status
    if (!in_array('status', $colunas)) {
        echo "❌ Coluna 'status' NÃO encontrada. Adicionando...\n";
        $conn->exec("ALTER TABLE notificacao ADD COLUMN status ENUM('lida', 'não lida') DEFAULT 'não lida' AFTER data_envio");
        $alteracoes[] = "status";
        echo "✅ Coluna 'status' adicionada com sucesso!\n\n";
    } else {
        echo "✅ Coluna 'status' já existe\n\n";
    }
    
    // Mostrar estrutura final
    echo "\n=== ESTRUTURA FINAL DA TABELA ===\n\n";
    $stmt = $conn->query("SHOW COLUMNS FROM notificacao");
    $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estrutura as $col) {
        echo sprintf("%-20s %-30s %-10s %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Default'] ?? 'NULL'
        );
    }
    
    echo "\n";
    
    if (!empty($alteracoes)) {
        echo "=== RESUMO ===\n";
        echo "Alterações realizadas:\n";
        foreach ($alteracoes as $alt) {
            echo "  ✅ $alt\n";
        }
    } else {
        echo "=== RESUMO ===\n";
        echo "Nenhuma alteração necessária. Tabela já está atualizada!\n";
    }
    
    echo "\n✅ Atualização concluída com sucesso!\n";
    echo "\nAgora você pode usar o sistema de notificações normalmente.\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nDetalhes do erro:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
