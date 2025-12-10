<?php
/**
 * Script para limpar carrinho de planos da sessão
 */
session_start();

echo "<h2>Estado da Sessão ANTES de limpar:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Limpa carrinho de planos
$_SESSION['carrinho_planos'] = [
    'itens' => [],
    'subtotal' => 0
];

echo "<h2>Estado da Sessão DEPOIS de limpar:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>✅ Carrinho de planos limpo com sucesso!</h3>";
echo '<a href="planos.php">Voltar para Planos</a> | ';
echo '<a href="carrinho.php">Ver Carrinho</a>';
