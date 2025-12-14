<?php
// Script para testar se os hashes de senha estão corretos

// Senhas do banco de dados
$hashAdmin = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhkO';
$hashCarlos = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFt6nqXqZ3qJdJGqVJnLVFqp1xEOyGay';

// Senhas esperadas (conforme documentação no SQL)
$senhaAdmin = 'admin123';
$senhaCarlos = '123456';

echo "=== TESTE DE VERIFICAÇÃO DE SENHAS ===\n\n";

// Teste Admin
echo "1. ADMIN\n";
echo "   Hash no banco: $hashAdmin\n";
echo "   Senha testada: $senhaAdmin\n";
$resultAdmin = password_verify($senhaAdmin, $hashAdmin);
echo "   Resultado: " . ($resultAdmin ? "✓ SENHA CORRETA" : "✗ SENHA INCORRETA") . "\n\n";

// Teste Carlos
echo "2. CARLOS_PEREIRA\n";
echo "   Hash no banco: $hashCarlos\n";
echo "   Senha testada: $senhaCarlos\n";
$resultCarlos = password_verify($senhaCarlos, $hashCarlos);
echo "   Resultado: " . ($resultCarlos ? "✓ SENHA CORRETA" : "✗ SENHA INCORRETA") . "\n\n";

// Testar outras possibilidades
echo "=== TESTANDO OUTRAS POSSIBILIDADES ===\n\n";

$senhasPossiveis = ['admin', 'admin123', 'Admin123', '123456', 'senha', 'techfit'];

echo "Para ADMIN:\n";
foreach ($senhasPossiveis as $tentativa) {
    $result = password_verify($tentativa, $hashAdmin);
    if ($result) {
        echo "   ✓ ENCONTRADA: '$tentativa'\n";
    }
}

echo "\nPara CARLOS_PEREIRA:\n";
foreach ($senhasPossiveis as $tentativa) {
    $result = password_verify($tentativa, $hashCarlos);
    if ($result) {
        echo "   ✓ ENCONTRADA: '$tentativa'\n";
    }
}

echo "\n=== GERANDO NOVOS HASHES ===\n\n";
echo "Novo hash para 'admin123': " . password_hash('admin123', PASSWORD_DEFAULT) . "\n";
echo "Novo hash para '123456': " . password_hash('123456', PASSWORD_DEFAULT) . "\n";
?>
