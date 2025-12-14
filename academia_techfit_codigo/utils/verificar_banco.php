<?php
/**
 * Script de Verificação do Banco de Dados
 * Verifica se todas as tabelas necessárias existem
 */

require_once __DIR__ . '/../config/Connection.php';

echo "=== VERIFICAÇÃO DO BANCO DE DADOS ===\n\n";

try {
    $conn = Connection::getInstance();
    
    // Tabelas esperadas do sistema
    $tabelasEsperadas = [
        'cliente',
        'funcionario',
        'login',
        'planos',
        'aulas',
        'agendamento',
        'produtos',
        'venda',
        'pedidos',
        'forma_pagamento',
        'pagamento',
        'notificacao',
        'avaliacao',
        'suporte',
        'treinos',
        'exercicio',
        'presenca'
    ];
    
    // Busca tabelas existentes
    $stmt = $conn->query("SHOW TABLES");
    $tabelasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas existentes no banco: " . count($tabelasExistentes) . "\n";
    echo "Tabelas esperadas: " . count($tabelasEsperadas) . "\n\n";
    
    // Verifica cada tabela
    $faltando = [];
    $ok = [];
    
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelasExistentes)) {
            $ok[] = $tabela;
            echo "✓ $tabela - OK\n";
        } else {
            $faltando[] = $tabela;
            echo "✗ $tabela - FALTANDO\n";
        }
    }
    
    echo "\n=== RESUMO ===\n";
    echo "✓ Tabelas OK: " . count($ok) . "\n";
    echo "✗ Tabelas faltando: " . count($faltando) . "\n";
    
    if (!empty($faltando)) {
        echo "\n⚠️ ATENÇÃO: As seguintes tabelas estão faltando:\n";
        foreach ($faltando as $tabela) {
            echo "   - $tabela\n";
        }
        echo "\nExecute o script SQL completo para criar todas as tabelas.\n";
    } else {
        echo "\n✅ Todas as tabelas necessárias estão presentes!\n";
    }
    
    // Verifica contagem de registros
    echo "\n=== CONTAGEM DE REGISTROS ===\n";
    foreach ($ok as $tabela) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM $tabela");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "$tabela: $total registros\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>
