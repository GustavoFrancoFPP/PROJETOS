<?php
/**
 * Script para corrigir senhas dos usuários
 */

require_once __DIR__ . '/config/Connection.php';

echo "=== CORRIGINDO SENHAS DOS USUÁRIOS ===\n\n";

try {
    $conn = Connection::getInstance();
    
    // Gerar hashes corretos
    $senhaAdmin = 'admin123';
    $senhaCarlos = '123456';
    
    $hashAdmin = password_hash($senhaAdmin, PASSWORD_DEFAULT);
    $hashCarlos = password_hash($senhaCarlos, PASSWORD_DEFAULT);
    
    echo "Senhas geradas:\n";
    echo "Admin: $senhaAdmin -> hash gerado\n";
    echo "Carlos: $senhaCarlos -> hash gerado\n\n";
    
    // Verificar se os usuários existem
    $stmt = $conn->query("SELECT nome_usuario FROM login WHERE nome_usuario IN ('admin', 'carlos_pereira')");
    $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Usuários encontrados: " . implode(', ', $usuarios) . "\n\n";
    
    // Atualizar senha do admin
    if (in_array('admin', $usuarios)) {
        $stmt = $conn->prepare("UPDATE login SET senha_usuario = ? WHERE nome_usuario = 'admin'");
        $stmt->execute([$hashAdmin]);
        echo "✓ Senha do usuário 'admin' atualizada com sucesso!\n";
    } else {
        echo "⚠ Usuário 'admin' não encontrado\n";
    }
    
    // Atualizar senha do carlos_pereira
    if (in_array('carlos_pereira', $usuarios)) {
        $stmt = $conn->prepare("UPDATE login SET senha_usuario = ? WHERE nome_usuario = 'carlos_pereira'");
        $stmt->execute([$hashCarlos]);
        echo "✓ Senha do usuário 'carlos_pereira' atualizada com sucesso!\n";
    } else {
        echo "⚠ Usuário 'carlos_pereira' não encontrado\n";
    }
    
    echo "\n=== VERIFICANDO ATUALIZAÇÕES ===\n\n";
    
    // Verificar se as senhas estão corretas agora
    $stmt = $conn->prepare("SELECT nome_usuario, senha_usuario FROM login WHERE nome_usuario IN ('admin', 'carlos_pereira')");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usuarios as $usuario) {
        $senha = ($usuario['nome_usuario'] == 'admin') ? $senhaAdmin : $senhaCarlos;
        $verifica = password_verify($senha, $usuario['senha_usuario']);
        
        echo "Usuário: {$usuario['nome_usuario']}\n";
        echo "Senha correta: $senha\n";
        echo "Verificação: " . ($verifica ? "✓ OK" : "✗ FALHOU") . "\n\n";
    }
    
    echo "=== CORREÇÃO CONCLUÍDA ===\n\n";
    echo "Credenciais de login:\n";
    echo "- Admin: usuário = admin | senha = admin123\n";
    echo "- Cliente: usuário = carlos_pereira | senha = 123456\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>
