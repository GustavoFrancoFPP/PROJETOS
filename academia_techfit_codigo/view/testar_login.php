<?php
/**
 * Script de teste para verificar login e senhas
 */
require_once __DIR__ . '/../config/Connection.php';

echo "<h2>Teste de Login - TechFit</h2>";

// Testa conexão
try {
    $conn = Connection::getInstance();
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na conexão: " . $e->getMessage() . "</p>";
    exit;
}

// Lista todos os usuários
echo "<h3>Usuários no banco:</h3>";
$stmt = $conn->query("SELECT id_login, nome_usuario, tipo_usuario, id_cliente, id_funcionario FROM login");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nome Usuário</th><th>Tipo</th><th>ID Cliente</th><th>ID Funcionario</th></tr>";
foreach ($usuarios as $user) {
    echo "<tr>";
    echo "<td>{$user['id_login']}</td>";
    echo "<td>{$user['nome_usuario']}</td>";
    echo "<td>{$user['tipo_usuario']}</td>";
    echo "<td>{$user['id_cliente']}</td>";
    echo "<td>{$user['id_funcionario']}</td>";
    echo "</tr>";
}
echo "</table>";

// Testa senha específica
echo "<h3>Teste de Senha:</h3>";

$teste_usuario = 'carlos_pereira';
$teste_senha = '123456';

$stmt = $conn->prepare("SELECT * FROM login WHERE nome_usuario = ?");
$stmt->execute([$teste_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<p>Usuário encontrado: <strong>{$teste_usuario}</strong></p>";
    echo "<p>Hash no banco: <code>{$usuario['senha_usuario']}</code></p>";
    
    $senha_correta = password_verify($teste_senha, $usuario['senha_usuario']);
    
    if ($senha_correta) {
        echo "<p style='color: green;'>✓ Senha '{$teste_senha}' está CORRETA</p>";
    } else {
        echo "<p style='color: red;'>✗ Senha '{$teste_senha}' está INCORRETA</p>";
        
        // Gera hash correto
        $novo_hash = password_hash($teste_senha, PASSWORD_DEFAULT);
        echo "<p>Hash correto para '{$teste_senha}': <code>{$novo_hash}</code></p>";
        echo "<p>Execute este SQL para corrigir:</p>";
        echo "<code style='background: #f0f0f0; padding: 10px; display: block;'>";
        echo "UPDATE login SET senha_usuario = '{$novo_hash}' WHERE nome_usuario = '{$teste_usuario}';";
        echo "</code>";
    }
} else {
    echo "<p style='color: red;'>✗ Usuário '{$teste_usuario}' não encontrado</p>";
}

// Gera hashes para todas as senhas comuns
echo "<h3>Gerar novos hashes:</h3>";
$senha_123456 = password_hash('123456', PASSWORD_DEFAULT);
$senha_admin123 = password_hash('admin123', PASSWORD_DEFAULT);

echo "<p><strong>Senha '123456':</strong> <code>{$senha_123456}</code></p>";
echo "<p><strong>Senha 'admin123':</strong> <code>{$senha_admin123}</code></p>";

echo "<h3>SQL para atualizar todas as senhas:</h3>";
echo "<textarea style='width: 100%; height: 150px; font-family: monospace;'>";
echo "UPDATE login SET senha_usuario = '{$senha_123456}' WHERE nome_usuario IN ('carlos_pereira', 'lucas_andrade', 'rogerio_souza');\n";
echo "UPDATE login SET senha_usuario = '{$senha_admin123}' WHERE nome_usuario = 'admin';";
echo "</textarea>";
?>
