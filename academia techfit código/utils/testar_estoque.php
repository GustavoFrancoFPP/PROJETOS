<?php
require_once '../config/Connection.php';

echo "=== TESTE DE ATUALIZAÇÃO DE ESTOQUE ===\n\n";

try {
    $conn = Connection::getInstance();
    
    // 1. Lista produtos atuais
    echo "PRODUTOS ATUAIS:\n";
    $stmt = $conn->query("SELECT id_produtos, nome_produto, quantidade FROM produtos ORDER BY id_produtos");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($produtos as $prod) {
        echo "  ID: {$prod['id_produtos']} | {$prod['nome_produto']} | Estoque: {$prod['quantidade']}\n";
    }
    
    // 2. Simula uma venda (diminui estoque do produto ID 1)
    echo "\n=== TESTE: Diminuindo 1 unidade do produto ID 1 ===\n";
    $stmt = $conn->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id_produtos = ? AND quantidade >= ?");
    $quantidade = 1;
    $id_produto = 1;
    $stmt->execute([$quantidade, $id_produto, $quantidade]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Estoque atualizado com sucesso!\n";
    } else {
        echo "❌ ERRO: Não foi possível atualizar (estoque insuficiente ou produto não encontrado)\n";
    }
    
    // 3. Mostra estoque atualizado
    echo "\nPRODUTOS APÓS ATUALIZAÇÃO:\n";
    $stmt = $conn->query("SELECT id_produtos, nome_produto, quantidade FROM produtos ORDER BY id_produtos");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($produtos as $prod) {
        echo "  ID: {$prod['id_produtos']} | {$prod['nome_produto']} | Estoque: {$prod['quantidade']}\n";
    }
    
    // 4. Reverte a mudança
    echo "\n=== REVERTENDO (adicionando 1 unidade de volta) ===\n";
    $conn->exec("UPDATE produtos SET quantidade = quantidade + 1 WHERE id_produtos = 1");
    echo "✓ Revertido\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
