<?php
require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== VERIFICANDO PLANOS NA TABELA PAGAMENTO ===\n\n";
    
    $stmt = $conn->query("
        SELECT 
            pg.id_pagamento,
            pg.id_cliente,
            pg.id_planos,
            p.nome_planos,
            p.valor,
            pg.valor_pago,
            pg.status_pagamento,
            pg.data_pagamento
        FROM pagamento pg
        INNER JOIN planos p ON pg.id_planos = p.id_planos
        ORDER BY pg.data_pagamento DESC
        LIMIT 10
    ");
    
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($planos)) {
        echo "❌ Nenhum plano encontrado na tabela pagamento\n";
    } else {
        echo "✅ Total de planos encontrados: " . count($planos) . "\n\n";
        foreach ($planos as $plano) {
            echo "ID Pagamento: " . $plano['id_pagamento'] . "\n";
            echo "ID Cliente: " . $plano['id_cliente'] . "\n";
            echo "Plano: " . $plano['nome_planos'] . "\n";
            echo "Valor: R$ " . number_format($plano['valor_pago'], 2, ',', '.') . "\n";
            echo "Status: " . $plano['status_pagamento'] . "\n";
            echo "Data: " . $plano['data_pagamento'] . "\n";
            echo "---\n";
        }
    }
    
    echo "\n=== VERIFICANDO SESSÃO DO USUÁRIO ===\n\n";
    session_start();
    if (isset($_SESSION['user_id'])) {
        echo "✅ Usuário logado: ID=" . $_SESSION['user_id'] . "\n";
        echo "Nome: " . ($_SESSION['user_nome'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Nenhum usuário logado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
