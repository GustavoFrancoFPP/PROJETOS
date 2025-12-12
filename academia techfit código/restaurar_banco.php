<?php
/**
 * Script para restaurar banco de dados completo
 * Execute: php restaurar_banco.php
 */

echo "=== RESTAURAÇÃO DO BANCO DE DADOS TECHFIT ===\n\n";

try {
    // Conecta ao MySQL sem selecionar banco
    $conn = new PDO('mysql:host=localhost', 'root', 'senaisp', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✓ Conectado ao MySQL\n";
    
    // Cria o banco de dados
    $conn->exec("DROP DATABASE IF EXISTS academia");
    echo "✓ Banco 'academia' removido (se existia)\n";
    
    $conn->exec("CREATE DATABASE academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Banco 'academia' criado\n";
    
    // Seleciona o banco
    $conn->exec("USE academia");
    echo "✓ Banco selecionado\n\n";
    
    // Lê o arquivo SQL
    $sqlFile = __DIR__ . '/sql/techfit_completo.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo SQL não encontrado: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "✓ Arquivo SQL carregado (" . strlen($sql) . " bytes)\n";
    
    // Divide em comandos individuais
    $comandos = array_filter(
        array_map('trim', explode(';', $sql)),
        function($cmd) {
            return !empty($cmd) && !preg_match('/^--/', $cmd);
        }
    );
    
    echo "✓ " . count($comandos) . " comandos SQL encontrados\n\n";
    echo "Executando comandos...\n";
    
    $executados = 0;
    foreach ($comandos as $comando) {
        if (trim($comando)) {
            try {
                $conn->exec($comando);
                $executados++;
                
                // Mostra progresso
                if ($executados % 10 == 0) {
                    echo "  → $executados comandos executados...\n";
                }
            } catch (PDOException $e) {
                echo "  ⚠ Erro no comando " . substr($comando, 0, 50) . "...\n";
                echo "  Erro: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ Total: $executados comandos executados com sucesso!\n\n";
    
    // Verifica tabelas criadas
    $stmt = $conn->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "=== TABELAS CRIADAS ===\n";
    foreach ($tabelas as $tabela) {
        echo "  ✓ $tabela\n";
    }
    
    echo "\n=== VERIFICAÇÃO DE DADOS ===\n";
    
    // Conta registros em tabelas principais
    $tabelasVerificar = ['cliente', 'funcionario', 'login', 'planos', 'aulas', 'produtos'];
    foreach ($tabelasVerificar as $tabela) {
        if (in_array($tabela, $tabelas)) {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM $tabela");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "  → $tabela: $total registros\n";
        }
    }
    
    echo "\n✅ BANCO DE DADOS RESTAURADO COM SUCESSO!\n";
    echo "\nCredenciais padrão:\n";
    echo "  Admin: login = admin | senha = admin123\n";
    echo "  Cliente: login = carlos_pereira | senha = 123456\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRO PDO: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
?>
