<?php
/**
 * Script para Corrigir Links CSS/JS Incorretos
 * Corrige paths do tipo: assets/css/https:// para https://
 */

$diretorio = __DIR__ . '/../view/';
$arquivos = glob($diretorio . '*.{html,php}', GLOB_BRACE);

$padroes = [
    'assets/css/https://' => 'https://',
    'assets/js/https://' => 'https://',
];

$totalArquivos = 0;
$totalSubstituicoes = 0;

echo "=== CORRIGINDO LINKS CSS/JS ===\n\n";

foreach ($arquivos as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    $conteudoOriginal = $conteudo;
    $substituicoesArquivo = 0;
    
    foreach ($padroes as $errado => $correto) {
        $quantidade = substr_count($conteudo, $errado);
        if ($quantidade > 0) {
            $conteudo = str_replace($errado, $correto, $conteudo);
            $substituicoesArquivo += $quantidade;
        }
    }
    
    if ($conteudoOriginal !== $conteudo) {
        file_put_contents($arquivo, $conteudo);
        $nomeArquivo = basename($arquivo);
        echo "✓ $nomeArquivo - $substituicoesArquivo correções\n";
        $totalArquivos++;
        $totalSubstituicoes += $substituicoesArquivo;
    }
}

echo "\n=== RESUMO ===\n";
echo "Arquivos corrigidos: $totalArquivos\n";
echo "Total de substituições: $totalSubstituicoes\n";

if ($totalSubstituicoes > 0) {
    echo "\n✅ Correções aplicadas com sucesso!\n";
} else {
    echo "\n✅ Nenhuma correção necessária.\n";
}
?>
