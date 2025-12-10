<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Carrinho - TECHFIT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #121212;
            color: #fff;
            padding: 20px;
        }
        .debug-box {
            background: #1e1e1e;
            border: 2px solid #00f0e1;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        h2 { color: #00f0e1; }
        pre {
            background: #0a0a0a;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            background: #00f0e1;
            color: #121212;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>🔧 Debug do Sistema de Carrinho</h1>

    <?php
    session_start();
    
    echo '<div class="debug-box">';
    echo '<h2>📊 Session ID</h2>';
    echo '<p>' . session_id() . '</p>';
    echo '</div>';

    echo '<div class="debug-box">';
    echo '<h2>🛒 Carrinho de Planos ($_SESSION)</h2>';
    echo '<pre>';
    print_r($_SESSION['carrinho_planos'] ?? 'NÃO EXISTE');
    echo '</pre>';
    
    if (isset($_SESSION['carrinho_planos'])) {
        $count = count($_SESSION['carrinho_planos']['itens']);
        echo "<p><strong>Quantidade de itens:</strong> $count</p>";
        echo "<p><strong>Subtotal:</strong> R$ " . number_format($_SESSION['carrinho_planos']['subtotal'], 2, ',', '.') . "</p>";
    }
    echo '</div>';

    echo '<div class="debug-box">';
    echo '<h2>📦 Sessão Completa</h2>';
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
    echo '</div>';
    ?>

    <div class="debug-box">
        <h2>🧪 Ações de Teste</h2>
        <a href="limpar_carrinho.php" class="btn">🗑️ Limpar Carrinho</a>
        <a href="planos.php" class="btn">📋 Ir para Planos</a>
        <a href="carrinho.php" class="btn">🛒 Ir para Carrinho</a>
        <a href="debug_carrinho.php" class="btn">🔄 Recarregar Debug</a>
    </div>

    <script>
        console.log('🔍 Debug JavaScript:');
        console.log('localStorage carrinhoTechFit:', localStorage.getItem('carrinhoTechFit'));
        console.log('Session Storage:', sessionStorage);
    </script>
</body>
</html>
