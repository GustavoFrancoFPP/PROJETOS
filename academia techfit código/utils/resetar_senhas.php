<?php
/**
 * Script para Resetar e Corrigir Senhas - TechFit
 * Garante que todas as senhas estejam com o hash correto
 */

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "🔐 Corrigindo Senhas do TechFit\n";
    echo str_repeat("=", 70) . "\n\n";
    
    // Senhas que queremos usar
    $credenciais = [
        ['usuario' => 'admin', 'senha' => 'admin123', 'tipo' => 'funcionario'],
        ['usuario' => 'carlos_pereira', 'senha' => '123456', 'tipo' => 'cliente'],
        ['usuario' => 'lucas_andrade', 'senha' => '123456', 'tipo' => 'cliente'],
        ['usuario' => 'rogerio_souza', 'senha' => '123456', 'tipo' => 'cliente']
    ];
    
    foreach ($credenciais as $cred) {
        $usuario = $cred['usuario'];
        $senha = $cred['senha'];
        
        // Gera novo hash
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Atualiza no banco
        $stmt = $conn->prepare("UPDATE login SET senha_usuario = ? WHERE nome_usuario = ?");
        $stmt->execute([$hash, $usuario]);
        
        // Testa se funciona
        $stmt = $conn->prepare("SELECT senha_usuario FROM login WHERE nome_usuario = ?");
        $stmt->execute([$usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && password_verify($senha, $result['senha_usuario'])) {
            echo "✅ $usuario - Senha corrigida com sucesso!\n";
            echo "   Usuário: $usuario\n";
            echo "   Senha: $senha\n";
            echo "   Hash: " . substr($hash, 0, 50) . "...\n\n";
        } else {
            echo "❌ ERRO ao corrigir $usuario\n\n";
        }
    }
    
    echo str_repeat("=", 70) . "\n";
    echo "✓ Processo concluído!\n\n";
    echo "CREDENCIAIS FINAIS PARA TESTE:\n";
    echo str_repeat("=", 70) . "\n";
    echo "ADMIN:\n";
    echo "  Usuário: admin\n";
    echo "  Senha: admin123\n\n";
    echo "CLIENTES:\n";
    echo "  Usuário: carlos_pereira | Senha: 123456\n";
    echo "  Usuário: lucas_andrade  | Senha: 123456\n";
    echo "  Usuário: rogerio_souza  | Senha: 123456\n";
    echo str_repeat("=", 70) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
