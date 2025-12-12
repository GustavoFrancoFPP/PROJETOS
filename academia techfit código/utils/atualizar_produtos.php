<?php
/**
 * Atualiza produtos com nomes reais
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== ATUALIZANDO PRODUTOS ===\n\n";
    
    // Busca produtos atuais
    $stmt = $conn->query("SELECT id_produtos, preco FROM produtos ORDER BY id_produtos");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Dados dos produtos baseados nos preços
    $dadosProdutos = [
        ['nome' => 'Whey Protein 1kg', 'tipo' => 'Suplemento', 'categoria' => 'Proteína'],
        ['nome' => 'Creatina 300g', 'tipo' => 'Suplemento', 'categoria' => 'Energia'],
        ['nome' => 'BCAA 120 cápsulas', 'tipo' => 'Suplemento', 'categoria' => 'Aminoácidos'],
        ['nome' => 'Barra de Proteína', 'tipo' => 'Suplemento', 'categoria' => 'Snack'],
        ['nome' => 'Squeeze 1L', 'tipo' => 'Acessório', 'categoria' => 'Equipamento']
    ];
    
    foreach ($produtos as $index => $produto) {
        if (isset($dadosProdutos[$index])) {
            $dados = $dadosProdutos[$index];
            
            $update = $conn->prepare("
                UPDATE produtos 
                SET nome_produto = ?, 
                    tipo_produto = ?, 
                    categoria = ?
                WHERE id_produtos = ?
            ");
            
            $update->execute([
                $dados['nome'],
                $dados['tipo'],
                $dados['categoria'],
                $produto['id_produtos']
            ]);
            
            echo "✓ Produto ID " . $produto['id_produtos'] . " atualizado: " . $dados['nome'] . "\n";
        }
    }
    
    echo "\n=== PRODUTOS ATUALIZADOS ===\n";
    $stmt = $conn->query("SELECT id_produtos, nome_produto, tipo_produto, categoria, preco, quantidade FROM produtos ORDER BY preco DESC");
    $produtosAtualizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($produtosAtualizados as $p) {
        echo "\n";
        echo "ID: " . $p['id_produtos'] . "\n";
        echo "Nome: " . $p['nome_produto'] . "\n";
        echo "Tipo: " . $p['tipo_produto'] . " - " . $p['categoria'] . "\n";
        echo "Preço: R$ " . number_format($p['preco'], 2, ',', '.') . "\n";
        echo "Estoque: " . $p['quantidade'] . "\n";
    }
    
    echo "\n✅ Produtos atualizados com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
