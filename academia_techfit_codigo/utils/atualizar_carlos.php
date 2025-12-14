<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    $stmt = $conn->prepare("UPDATE login SET nome_usuario = ? WHERE id_cliente = 1");
    $stmt->execute(['carlos_pereira']);
    
    echo "✓ Login do Carlos Pereira (cliente ID 1) atualizado para: carlos_pereira\n";
    
    // Verifica
    $stmt = $conn->query("SELECT c.nome_cliente, l.nome_usuario FROM cliente c LEFT JOIN login l ON c.id_cliente = l.id_cliente WHERE c.id_cliente = 1");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Confirmação: {$resultado['nome_cliente']} | Login: {$resultado['nome_usuario']}\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
