<?php
/**
 * Script de teste para verificar inserção de CPF no banco
 */

require_once __DIR__ . '/../config/Connection.php';

echo "<h1>Teste de Inserção de CPF</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; } pre { background: #f5f5f5; padding: 10px; }</style>";

$conn = Connection::getInstance();

// Teste 1: Verificar estrutura da tabela
echo "<h2>1. Estrutura da Tabela 'cliente':</h2>";
try {
    $stmt = $conn->query("DESCRIBE cliente");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($columns as $col) {
        if ($col['Field'] == 'cpf') {
            echo "<strong style='color: blue;'>CPF Column:</strong>\n";
            print_r($col);
        }
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>Erro: " . $e->getMessage() . "</p>";
}

// Teste 2: Testar limpeza de CPF
echo "<h2>2. Teste de Limpeza de CPF:</h2>";
$cpf_tests = [
    '123.456.789-01',
    '12345678901',
    '123 456 789 01',
    '  123.456.789-01  ',
];

foreach ($cpf_tests as $cpf_original) {
    $cpf_limpo = preg_replace('/[^0-9]/', '', trim($cpf_original));
    echo "<p>Original: '<strong>{$cpf_original}</strong>' (len: " . strlen($cpf_original) . ")<br>";
    echo "Limpo: '<strong>{$cpf_limpo}</strong>' (len: " . strlen($cpf_limpo) . ") ";
    
    if (strlen($cpf_limpo) == 11) {
        echo "<span class='success'>✓ OK</span>";
    } else {
        echo "<span class='error'>✗ ERRO - Deveria ter 11 dígitos!</span>";
    }
    echo "</p>";
}

// Teste 3: Verificar CPFs existentes
echo "<h2>3. CPFs já cadastrados no banco:</h2>";
try {
    $stmt = $conn->query("SELECT id_cliente, nome_cliente, cpf, LENGTH(cpf) as cpf_length FROM cliente LIMIT 5");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "<p>Nenhum cliente cadastrado ainda.</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Tamanho</th></tr>";
        foreach ($clientes as $cli) {
            $color = strlen($cli['cpf']) == 11 ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$cli['id_cliente']}</td>";
            echo "<td>{$cli['nome_cliente']}</td>";
            echo "<td><code>{$cli['cpf']}</code></td>";
            echo "<td style='color: {$color};'><strong>{$cli['cpf_length']} caracteres</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Erro: " . $e->getMessage() . "</p>";
}

// Teste 4: Tentar inserção de teste (será feita rollback)
echo "<h2>4. Teste de Inserção (com Rollback):</h2>";
try {
    $conn->beginTransaction();
    
    $cpf_teste = '12345678901';
    $email_teste = 'teste_' . time() . '@example.com';
    
    echo "<p>Tentando inserir CPF: <strong>{$cpf_teste}</strong> (len: " . strlen($cpf_teste) . ")</p>";
    
    $stmt = $conn->prepare("INSERT INTO cliente (nome_cliente, email, cpf, telefone, endereco, genero, status) VALUES (?, ?, ?, ?, ?, ?, 'ativo')");
    $stmt->execute([
        'Teste Usuario',
        $email_teste,
        $cpf_teste,
        '11999999999',
        'Rua Teste',
        'Outro'
    ]);
    
    echo "<p class='success'>✓ Inserção bem-sucedida! (será desfeita)</p>";
    
    // Rollback para não deixar dados de teste
    $conn->rollBack();
    echo "<p><em>Rollback executado - dados não foram salvos.</em></p>";
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<p class='error'>✗ ERRO na inserção: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Conclusão:</h3>";
echo "<p>Se todos os testes passaram, o problema pode estar em:</p>";
echo "<ul>";
echo "<li>Espaços extras nos dados do formulário</li>";
echo "<li>Caracteres especiais não visíveis</li>";
echo "<li>Codificação de caracteres</li>";
echo "</ul>";
echo "<p><a href='admin.php'>« Voltar para Admin</a> | <a href='debug_post.php'>Testar Formulário »</a></p>";
?>
