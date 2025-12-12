<?php
/**
 * Script para limpar carrinho de planos da sessão
 */
session_start();

// Força a destruição completa da sessão e cria uma nova
session_destroy();
session_start();

// Inicializa carrinho limpo
$_SESSION['carrinho_planos'] = [
    'itens' => [],
    'subtotal' => 0
];

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Carrinho Limpo - TechFit</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; background: #0f172a; color: #fff; padding: 40px; text-align: center; }";
echo ".container { max-width: 600px; margin: 0 auto; background: #1a1a2e; padding: 30px; border-radius: 10px; border: 2px solid #00f0e1; }";
echo "h2 { color: #00f0e1; }";
echo ".success { color: #00f0e1; font-size: 18px; margin: 20px 0; }";
echo "a { display: inline-block; margin: 10px; padding: 12px 24px; background: #00f0e1; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; }";
echo "a:hover { background: #00d4c4; }";
echo "pre { background: #000; padding: 15px; border-radius: 5px; text-align: left; overflow-x: auto; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h2>🧹 Limpeza do Carrinho</h2>";
echo "<div class='success'>✅ Sessão completamente renovada!</div>";
echo "<div class='success'>✅ Carrinho de planos limpo com sucesso!</div>";
echo "<p>Session ID: <strong>" . session_id() . "</strong></p>";
echo "<h3>Estado Atual:</h3>";
echo "<pre>";
print_r($_SESSION['carrinho_planos']);
echo "</pre>";
echo "<div style='margin-top: 30px;'>";
echo "<a href='planos.php'>🏋️ Ver Planos</a>";
echo "<a href='carrinho.php'>🛒 Ver Carrinho</a>";
echo "<a href='produtos_loja.php'>🏪 Ver Produtos</a>";
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";
