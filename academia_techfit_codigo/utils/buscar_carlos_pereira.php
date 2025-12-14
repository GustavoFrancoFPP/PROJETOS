<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== PROCURANDO CARLOS PEREIRA ===\n\n";
    
    // Busca em funcionários
    echo "FUNCIONÁRIOS:\n";
    $stmt = $conn->query("SELECT f.id_funcionario, f.nome_funcionario, l.nome_usuario 
                          FROM funcionario f 
                          LEFT JOIN login l ON f.id_funcionario = l.id_funcionario 
                          WHERE f.nome_funcionario LIKE '%Carlos%' OR f.nome_funcionario LIKE '%Pereira%'");
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($funcionarios)) {
        echo "  Nenhum funcionário encontrado\n\n";
    } else {
        foreach ($funcionarios as $func) {
            echo "  ID: {$func['id_funcionario']} | Nome: {$func['nome_funcionario']} | Login: {$func['nome_usuario']}\n";
        }
        echo "\n";
    }
    
    // Busca em clientes
    echo "CLIENTES:\n";
    $stmt = $conn->query("SELECT c.id_cliente, c.nome_cliente, l.nome_usuario 
                          FROM cliente c 
                          LEFT JOIN login l ON c.id_cliente = l.id_cliente 
                          WHERE c.nome_cliente LIKE '%Carlos%' OR c.nome_cliente LIKE '%Pereira%'");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "  Nenhum cliente encontrado\n\n";
    } else {
        foreach ($clientes as $cliente) {
            echo "  ID: {$cliente['id_cliente']} | Nome: {$cliente['nome_cliente']} | Login: {$cliente['nome_usuario']}\n";
        }
        echo "\n";
    }
    
    // Busca logins que parecem emails (contém @)
    echo "LOGINS QUE SÃO EMAILS:\n";
    $stmt = $conn->query("SELECT l.*, 
                          CASE 
                            WHEN l.id_funcionario IS NOT NULL THEN f.nome_funcionario
                            WHEN l.id_cliente IS NOT NULL THEN c.nome_cliente
                          END as nome
                          FROM login l
                          LEFT JOIN funcionario f ON l.id_funcionario = f.id_funcionario
                          LEFT JOIN cliente c ON l.id_cliente = c.id_cliente
                          WHERE l.nome_usuario LIKE '%@%'");
    $logins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($logins as $login) {
        $tipo = $login['tipo_usuario'];
        echo "  {$login['nome']} ({$tipo}) | Login: {$login['nome_usuario']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
