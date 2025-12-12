<?php
require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    // Atualiza o plano mais recente do cliente 1 para status "pago"
    $stmt = $conn->prepare("
        UPDATE pagamento 
        SET status_pagamento = 'pago' 
        WHERE id_cliente = 1 
        AND id_pagamento = (
            SELECT id FROM (
                SELECT id_pagamento as id 
                FROM pagamento 
                WHERE id_cliente = 1 
                ORDER BY data_pagamento DESC 
                LIMIT 1
            ) as tmp
        )
    ");
    
    $stmt->execute();
    
    echo "✅ Plano atualizado com sucesso!\n";
    
    // Mostra o plano atualizado
    $stmt = $conn->prepare("
        SELECT 
            pg.id_pagamento,
            p.nome_planos,
            pg.valor_pago,
            pg.status_pagamento,
            pg.data_pagamento
        FROM pagamento pg
        INNER JOIN planos p ON pg.id_planos = p.id_planos
        WHERE pg.id_cliente = 1
        ORDER BY pg.data_pagamento DESC
        LIMIT 1
    ");
    $stmt->execute();
    $plano = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($plano) {
        echo "\nPlano ativo:\n";
        echo "- " . $plano['nome_planos'] . "\n";
        echo "- Valor: R$ " . number_format($plano['valor_pago'], 2, ',', '.') . "\n";
        echo "- Status: " . $plano['status_pagamento'] . "\n";
        echo "- Data: " . $plano['data_pagamento'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
