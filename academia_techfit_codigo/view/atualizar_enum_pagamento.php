<?php
/**
 * Script para adicionar 'cancelado' ao ENUM de status_pagamento
 * Executa apenas uma vez
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "<h2>Atualizando ENUM da coluna status_pagamento...</h2>";
    
    // Alterar o ENUM para incluir 'cancelado'
    $sql = "ALTER TABLE pagamento 
            MODIFY COLUMN status_pagamento ENUM('pago', 'pendente', 'cancelado') DEFAULT 'pendente'";
    
    $conn->exec($sql);
    
    echo "<p style='color: green;'>✓ ENUM atualizado com sucesso!</p>";
    echo "<p>Agora a coluna status_pagamento aceita: 'pago', 'pendente', 'cancelado'</p>";
    
    // Verificar a estrutura
    echo "<h3>Verificando estrutura atual:</h3>";
    $stmt = $conn->query("SHOW COLUMNS FROM pagamento LIKE 'status_pagamento'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($coluna);
    echo "</pre>";
    
    echo "<p><a href='dashboard_aluno.php'>← Voltar ao Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro ao atualizar ENUM: " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Banco de Dados</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h2 { color: #333; }
        pre { background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; }
        a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #00F0E1; color: #000; text-decoration: none; border-radius: 5px; }
        a:hover { background: #00A8CC; }
    </style>
</head>
<body>
</body>
</html>
