<?php
/**
 * Script de Teste de Login - TechFit
 * Verifica e corrige senhas no banco de dados
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "<h2>🔍 Testando Logins TechFit</h2>";
    echo "<pre>";
    
    // Busca todos os logins
    $stmt = $conn->query("SELECT l.nome_usuario, l.tipo_usuario, l.id_cliente, l.id_funcionario 
                          FROM login l");
    $logins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Logins cadastrados no banco:\n";
    echo str_repeat("=", 60) . "\n";
    
    foreach ($logins as $login) {
        echo "Usuário: " . $login['nome_usuario'] . "\n";
        echo "Tipo: " . $login['tipo_usuario'] . "\n";
        echo "ID Cliente: " . ($login['id_cliente'] ?? 'NULL') . "\n";
        echo "ID Funcionário: " . ($login['id_funcionario'] ?? 'NULL') . "\n";
        echo str_repeat("-", 60) . "\n";
    }
    
    echo "\n🔐 Credenciais para Teste:\n";
    echo str_repeat("=", 60) . "\n";
    echo "ADMIN:\n";
    echo "  Usuário: admin\n";
    echo "  Senha: admin123\n\n";
    
    echo "CLIENTES:\n";
    echo "  Usuário: carlos_pereira | Senha: 123456\n";
    echo "  Usuário: lucas_andrade  | Senha: 123456\n";
    echo "  Usuário: rogerio_souza  | Senha: 123456\n";
    echo str_repeat("=", 60) . "\n";
    
    // Testa senha do admin
    $stmt = $conn->prepare("SELECT senha_usuario FROM login WHERE nome_usuario = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "\n✅ Testando senha do ADMIN...\n";
        $senhaCorreta = password_verify('admin123', $result['senha_usuario']);
        echo "Resultado: " . ($senhaCorreta ? "✓ SENHA CORRETA" : "✗ SENHA INCORRETA") . "\n";
        
        if (!$senhaCorreta) {
            echo "\n⚠️ Corrigindo senha do admin...\n";
            $novoHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE login SET senha_usuario = ? WHERE nome_usuario = 'admin'");
            $stmt->execute([$novoHash]);
            echo "✓ Senha do admin atualizada!\n";
        }
    }
    
    // Testa senha de um cliente
    $stmt = $conn->prepare("SELECT senha_usuario FROM login WHERE nome_usuario = 'carlos_pereira'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "\n✅ Testando senha do CLIENTE (carlos_pereira)...\n";
        $senhaCorreta = password_verify('123456', $result['senha_usuario']);
        echo "Resultado: " . ($senhaCorreta ? "✓ SENHA CORRETA" : "✗ SENHA INCORRETA") . "\n";
        
        if (!$senhaCorreta) {
            echo "\n⚠️ Corrigindo senhas dos clientes...\n";
            $novoHash = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE login SET senha_usuario = ? WHERE tipo_usuario = 'cliente'");
            $stmt->execute([$novoHash]);
            echo "✓ Senhas dos clientes atualizadas!\n";
        }
    }
    
    echo "\n</pre>";
    echo "<p style='color: green; font-weight: bold;'>✓ Teste concluído! Tente fazer login novamente.</p>";
    
} catch (Exception $e) {
    echo "<pre style='color: red;'>";
    echo "❌ Erro: " . $e->getMessage();
    echo "</pre>";
}
?>
