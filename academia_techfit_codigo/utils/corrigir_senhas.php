<?php
/**
 * Script para corrigir senhas no banco de dados
 */
require_once 'Connection.php';

echo "<h2>Correção de Senhas - TechFit</h2>";

try {
    $conn = Connection::getInstance();
    echo "<p style='color: green;'>✓ Conexão estabelecida</p>";
    
    // Gera os hashes corretos
    $hash_123456 = password_hash('123456', PASSWORD_DEFAULT);
    $hash_admin123 = password_hash('admin123', PASSWORD_DEFAULT);
    
    echo "<p>Hash gerado para '123456': <code>{$hash_123456}</code></p>";
    echo "<p>Hash gerado para 'admin123': <code>{$hash_admin123}</code></p>";
    
    // Atualiza clientes
    $stmt = $conn->prepare("UPDATE login SET senha_usuario = ? WHERE nome_usuario = ?");
    
    $usuarios_cliente = ['carlos_pereira', 'lucas_andrade', 'rogerio_souza'];
    foreach ($usuarios_cliente as $usuario) {
        $stmt->execute([$hash_123456, $usuario]);
        echo "<p style='color: blue;'>✓ Senha atualizada para usuário: <strong>{$usuario}</strong></p>";
    }
    
    // Atualiza admin
    $stmt->execute([$hash_admin123, 'admin']);
    echo "<p style='color: blue;'>✓ Senha atualizada para usuário: <strong>admin</strong></p>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ SENHAS ATUALIZADAS COM SUCESSO!</h3>";
    echo "<p><strong>Credenciais para teste:</strong></p>";
    echo "<ul>";
    echo "<li>Usuário: <code>carlos_pereira</code> | Senha: <code>123456</code></li>";
    echo "<li>Usuário: <code>lucas_andrade</code> | Senha: <code>123456</code></li>";
    echo "<li>Usuário: <code>rogerio_souza</code> | Senha: <code>123456</code></li>";
    echo "<li>Usuário: <code>admin</code> | Senha: <code>admin123</code></li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #00D1B2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir para Login</a></p>";
    
    // Verifica se realmente funcionou
    echo "<hr>";
    echo "<h3>Verificação:</h3>";
    
    $stmt = $conn->prepare("SELECT nome_usuario, senha_usuario FROM login WHERE nome_usuario = 'carlos_pereira'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify('123456', $user['senha_usuario'])) {
        echo "<p style='color: green; font-size: 18px;'><strong>✓ TESTE PASSOU! A senha '123456' funciona para carlos_pereira</strong></p>";
    } else {
        echo "<p style='color: red; font-size: 18px;'><strong>✗ ERRO! A senha ainda não funciona</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}
?>
