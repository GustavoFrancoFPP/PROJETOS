<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== LISTA DE FUNCIONÁRIOS ===\n\n";
    $stmt = $conn->query("SELECT f.id_funcionario, f.nome_funcionario, l.nome_usuario 
                          FROM funcionario f 
                          LEFT JOIN login l ON f.id_funcionario = l.id_funcionario 
                          ORDER BY f.id_funcionario");
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($funcionarios as $func) {
        echo "ID: {$func['id_funcionario']} | Nome: {$func['nome_funcionario']} | Login: {$func['nome_usuario']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
