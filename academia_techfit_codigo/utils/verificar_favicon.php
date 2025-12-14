<?php
/**
 * Script de Verificação de Favicon
 * Verifica quais arquivos HTML/PHP têm o favicon configurado
 */

$diretorio = __DIR__ . '/../view/';
$arquivos = glob($diretorio . '*.{html,php}', GLOB_BRACE);

$comFavicon = [];
$semFavicon = [];
$semHead = [];

echo "=== VERIFICAÇÃO DE FAVICON ===\n\n";

foreach ($arquivos as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    $nomeArquivo = basename($arquivo);
    
    // Verifica se tem tag <head>
    if (!preg_match('/<head>/i', $conteudo)) {
        $semHead[] = $nomeArquivo;
        continue;
    }
    
    // Verifica se tem favicon
    if (stripos($conteudo, 'favicon') !== false) {
        $comFavicon[] = $nomeArquivo;
        echo "✓ $nomeArquivo\n";
    } else {
        $semFavicon[] = $nomeArquivo;
        echo "✗ $nomeArquivo - SEM FAVICON\n";
    }
}

echo "\n=== RESUMO ===\n";
echo "✓ Com favicon: " . count($comFavicon) . " arquivos\n";
echo "✗ Sem favicon: " . count($semFavicon) . " arquivos\n";
echo "⊙ Sem <head>: " . count($semHead) . " arquivos (classes/scripts PHP)\n";

if (!empty($semFavicon)) {
    echo "\n⚠️ Arquivos que precisam de favicon:\n";
    foreach ($semFavicon as $arquivo) {
        echo "   - $arquivo\n";
    }
}

echo "\n📍 Localização do favicon: assets/images/imagens/favicon.ico\n";
echo "\n✅ Verificação concluída!\n";
?>
