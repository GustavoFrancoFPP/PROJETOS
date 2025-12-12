<?php
require 'config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    $stmt = $conn->query('SELECT COUNT(*) as total FROM cliente');
    echo "✓ Clientes: " . $stmt->fetch()['total'] . "\n";
    
    $stmt = $conn->query('SELECT COUNT(*) as total FROM planos');
    echo "✓ Planos: " . $stmt->fetch()['total'] . "\n";
    
    $stmt = $conn->query('SELECT COUNT(*) as total FROM produtos');
    echo "✓ Produtos: " . $stmt->fetch()['total'] . "\n";
    
    $stmt = $conn->query('SELECT COUNT(*) as total FROM funcionario');
    echo "✓ Funcionários: " . $stmt->fetch()['total'] . "\n";
    
    $stmt = $conn->query('SELECT COUNT(*) as total FROM login');
    echo "✓ Logins: " . $stmt->fetch()['total'] . "\n";
    
    echo "\n✅ BANCO DE DADOS FUNCIONANDO PERFEITAMENTE!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>
