<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    // Busca o Carlos Pereira
    $stmt = $conn->query("SELECT f.id_funcionario, f.nome_funcionario, l.nome_usuario 
                          FROM funcionario f 
                          LEFT JOIN login l ON f.id_funcionario = l.id_funcionario 
                          WHERE f.nome_funcionario LIKE '%Carlos%Pereira%'");
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($funcionario) {
        echo "Funcionário encontrado:\n";
        echo "ID: {$funcionario['id_funcionario']}\n";
        echo "Nome: {$funcionario['nome_funcionario']}\n";
        echo "Login atual: {$funcionario['nome_usuario']}\n\n";
        
        // Atualiza o login para carlos_pereira
        $stmt = $conn->prepare("UPDATE login SET nome_usuario = 'carlos_pereira' WHERE id_funcionario = ?");
        $stmt->execute([$funcionario['id_funcionario']]);
        
        echo "✓ Login atualizado para: carlos_pereira\n";
    } else {
        echo "❌ Funcionário Carlos Pereira não encontrado\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
