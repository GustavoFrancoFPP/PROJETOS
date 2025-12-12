<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    // Atualiza o login do funcionário ID 4
    $stmt = $conn->prepare("UPDATE login SET nome_usuario = 'carlos_pereira' WHERE id_funcionario = 4");
    $stmt->execute();
    
    echo "✓ Login do funcionário ID 4 atualizado para: carlos_pereira\n";
    
    // Atualiza também o nome do funcionário se quiser
    $resposta = readline("Deseja atualizar o nome do funcionário para 'Carlos Pereira'? (s/n): ");
    if (strtolower($resposta) === 's') {
        $stmt = $conn->prepare("UPDATE funcionario SET nome_funcionario = 'Carlos Pereira' WHERE id_funcionario = 4");
        $stmt->execute();
        echo "✓ Nome atualizado para: Carlos Pereira\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
