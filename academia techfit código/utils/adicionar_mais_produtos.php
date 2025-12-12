<?php
/**
 * Adiciona mais produtos à loja
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== ADICIONANDO MAIS PRODUTOS ===\n\n";
    
    // Novos produtos para adicionar
    $novosProdutos = [
        ['nome' => 'Pré-Treino 300g', 'tipo' => 'Suplemento', 'categoria' => 'Energia', 'preco' => 89.90, 'quantidade' => 45],
        ['nome' => 'Glutamina 300g', 'tipo' => 'Suplemento', 'categoria' => 'Recuperação', 'preco' => 69.90, 'quantidade' => 60],
        ['nome' => 'Hipercalórico 3kg', 'tipo' => 'Suplemento', 'categoria' => 'Ganho de Massa', 'preco' => 119.90, 'quantidade' => 35],
        ['nome' => 'Coqueteleira Premium', 'tipo' => 'Acessório', 'categoria' => 'Equipamento', 'preco' => 29.90, 'quantidade' => 100],
        ['nome' => 'Luva de Treino', 'tipo' => 'Acessório', 'categoria' => 'Vestuário', 'preco' => 49.90, 'quantidade' => 75],
        ['nome' => 'Cinto de Musculação', 'tipo' => 'Acessório', 'categoria' => 'Equipamento', 'preco' => 89.90, 'quantidade' => 40],
        ['nome' => 'Ômega 3 120 cáps', 'tipo' => 'Suplemento', 'categoria' => 'Saúde', 'preco' => 54.90, 'quantidade' => 80],
        ['nome' => 'Multivitamínico', 'tipo' => 'Suplemento', 'categoria' => 'Saúde', 'preco' => 44.90, 'quantidade' => 90],
        ['nome' => 'Termogênico 60 cáps', 'tipo' => 'Suplemento', 'categoria' => 'Emagrecimento', 'preco' => 79.90, 'quantidade' => 55],
        ['nome' => 'Albumina 500g', 'tipo' => 'Suplemento', 'categoria' => 'Proteína', 'preco' => 39.90, 'quantidade' => 70],
        ['nome' => 'Camiseta Dry Fit', 'tipo' => 'Vestuário', 'categoria' => 'Roupas', 'preco' => 59.90, 'quantidade' => 120],
        ['nome' => 'Toalha de Academia', 'tipo' => 'Acessório', 'categoria' => 'Equipamento', 'preco' => 24.90, 'quantidade' => 150],
        ['nome' => 'Munhequeira Par', 'tipo' => 'Acessório', 'categoria' => 'Vestuário', 'preco' => 19.90, 'quantidade' => 200],
        ['nome' => 'Colágeno Hidrolisado', 'tipo' => 'Suplemento', 'categoria' => 'Saúde', 'preco' => 64.90, 'quantidade' => 65],
        ['nome' => 'Isotônico 1L', 'tipo' => 'Bebida', 'categoria' => 'Hidratação', 'preco' => 9.90, 'quantidade' => 180]
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, quantidade) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($novosProdutos as $produto) {
        $stmt->execute([
            $produto['nome'],
            $produto['tipo'],
            $produto['categoria'],
            $produto['preco'],
            $produto['quantidade']
        ]);
        echo "✓ " . $produto['nome'] . " - R$ " . number_format($produto['preco'], 2, ',', '.') . "\n";
    }
    
    // Mostra total de produtos
    echo "\n=== RESUMO ===\n";
    $total = $conn->query("SELECT COUNT(*) as total FROM produtos")->fetch();
    echo "Total de produtos no catálogo: " . $total['total'] . "\n";
    
    echo "\n✅ Produtos adicionados com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
