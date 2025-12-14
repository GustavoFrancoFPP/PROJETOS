<?php
/**
 * Script para adicionar favicon em todos os arquivos HTML/PHP
 */

$diretorio = __DIR__ . '/../view/';
$arquivos = glob($diretorio . '*.{html,php}', GLOB_BRACE);

$faviconTags = [
    '<link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">',
    '<link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico" />',
    '<link rel="shortcut icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">',
    '<link rel="shortcut icon" type="image/x-icon" href="assets/images/imagens/favicon.ico" />'
];

$novoFavicon = '    <link rel="icon" type="image/x-icon" href="assets/images/imagens/favicon.ico">';

$totalArquivos = 0;
$totalAdicoes = 0;
$jaExistentes = 0;

echo "=== ADICIONANDO FAVICON ===\n\n";

foreach ($arquivos as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    $nomeArquivo = basename($arquivo);
    
    // Verifica se já existe alguma tag de favicon
    $jaTemFavicon = false;
    foreach ($faviconTags as $tag) {
        if (stripos($conteudo, 'favicon') !== false) {
            $jaTemFavicon = true;
            break;
        }
    }
    
    if ($jaTemFavicon) {
        echo "⊙ $nomeArquivo - Já possui favicon\n";
        $jaExistentes++;
        continue;
    }
    
    // Procura a tag </head> ou <head> para adicionar o favicon
    if (preg_match('/<head>.*?<\/head>/is', $conteudo)) {
        // Adiciona antes do </head>
        $conteudo = preg_replace(
            '/(<\/head>)/i',
            "$novoFavicon\n$1",
            $conteudo,
            1
        );
        
        file_put_contents($arquivo, $conteudo);
        echo "✓ $nomeArquivo - Favicon adicionado\n";
        $totalArquivos++;
        $totalAdicoes++;
    } else {
        echo "✗ $nomeArquivo - Tag <head> não encontrada\n";
    }
}

echo "\n=== RESUMO ===\n";
echo "Arquivos com favicon adicionado: $totalAdicoes\n";
echo "Arquivos que já tinham favicon: $jaExistentes\n";
echo "Total processado: " . ($totalAdicoes + $jaExistentes) . "\n";

if ($totalAdicoes > 0) {
    echo "\n✅ Favicon configurado com sucesso!\n";
    echo "📍 Localização: assets/images/imagens/favicon.ico\n";
} else if ($jaExistentes > 0) {
    echo "\n✅ Todos os arquivos já possuem favicon!\n";
}
?>
