<?php
/**
 * Script para popular a tabela planos com os dados corretos
 * Executa apenas uma vez para criar os planos no banco
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    // Primeiro, vamos verificar a estrutura atual da tabela planos
    echo "<h2>Verificando estrutura da tabela planos...</h2>";
    $stmt = $conn->query("DESCRIBE planos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($colunas);
    echo "</pre>";
    
    // Tabela usa nome_planos (plural) e valor, não nome_plano e preco
    echo "<h2>Estrutura já existe (nome_planos, valor, descricao)</h2>";
    
    // Limpar planos antigos
    echo "<h2>Limpando planos antigos...</h2>";
    $conn->exec("DELETE FROM planos WHERE nome_planos IN ('Plano Sigma', 'Plano Alpha', 'Plano Beta')");
    echo "✓ Planos antigos removidos<br>";
    
    // Inserir os 3 planos corretos
    echo "<h2>Inserindo planos corretos...</h2>";
    
    $planosData = [
        [
            'nome_planos' => 'Plano Sigma',
            'descricao' => 'O melhor que tem, inclui todos os treinos, acesso a todas as aulas e ainda ganha suplementos de graça para potencializar seus resultados.',
            'valor' => 239.90
        ],
        [
            'nome_planos' => 'Plano Alpha',
            'descricao' => 'Ideal para todos da casa, acesso completo à academia para múltiplos membros com benefícios especiais para cada família.',
            'valor' => 139.90
        ],
        [
            'nome_planos' => 'Plano Beta',
            'descricao' => 'Essencial para quem quer manter a forma, com acesso aos equipamentos e treinos básicos sem complicação.',
            'valor' => 89.90
        ]
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO planos (nome_planos, descricao, valor) 
        VALUES (:nome_planos, :descricao, :valor)
    ");
    
    foreach ($planosData as $plano) {
        $stmt->execute($plano);
        echo "✓ Plano '{$plano['nome_planos']}' inserido (ID: " . $conn->lastInsertId() . ")<br>";
    }
    
    // Verificar planos inseridos
    echo "<h2>Planos cadastrados:</h2>";
    $stmt = $conn->query("SELECT * FROM planos ORDER BY valor DESC");
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #00F0E1;'><th>ID</th><th>Nome</th><th>Descrição</th><th>Preço</th></tr>";
    foreach ($planos as $plano) {
        echo "<tr>";
        echo "<td>{$plano['id_planos']}</td>";
        echo "<td>{$plano['nome_planos']}</td>";
        echo "<td>{$plano['descricao']}</td>";
        echo "<td>R$ " . number_format($plano['valor'], 2, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 style='color: green;'>✓ Planos configurados com sucesso!</h2>";
    echo "<p><a href='planos.php' style='padding: 10px 20px; background: #00F0E1; color: #000; text-decoration: none; border-radius: 5px;'>Ver Planos</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Erro: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
