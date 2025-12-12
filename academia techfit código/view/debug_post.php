<?php
/**
 * Arquivo de debug para verificar o que está sendo enviado no POST
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Dados Recebidos via POST:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_POST['cpf'])) {
        echo "<h3>Análise do CPF:</h3>";
        echo "CPF Original: '" . $_POST['cpf'] . "'<br>";
        echo "Tamanho: " . strlen($_POST['cpf']) . " caracteres<br>";
        
        $cpf_limpo = preg_replace('/[^0-9]/', '', trim($_POST['cpf']));
        echo "CPF Limpo: '" . $cpf_limpo . "'<br>";
        echo "Tamanho Limpo: " . strlen($cpf_limpo) . " caracteres<br>";
        
        // Análise byte a byte
        echo "<h4>Análise byte a byte do CPF original:</h4>";
        for ($i = 0; $i < strlen($_POST['cpf']); $i++) {
            $char = $_POST['cpf'][$i];
            echo "Pos $i: '$char' (ASCII: " . ord($char) . ")<br>";
        }
    }
    
    if (isset($_POST['telefone'])) {
        echo "<h3>Análise do Telefone:</h3>";
        echo "Telefone Original: '" . $_POST['telefone'] . "'<br>";
        echo "Tamanho: " . strlen($_POST['telefone']) . " caracteres<br>";
        
        $telefone_limpo = preg_replace('/[^0-9]/', '', trim($_POST['telefone']));
        echo "Telefone Limpo: '" . $telefone_limpo . "'<br>";
        echo "Tamanho Limpo: " . strlen($telefone_limpo) . " caracteres<br>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Debug POST - TechFit</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        pre { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        h2, h3, h4 { color: #00D1B2; }
    </style>
</head>
<body>
    <h1>Teste de Cadastro</h1>
    <form method="POST" action="">
        <label>CPF: <input type="text" name="cpf" class="cpf-mask" maxlength="14" placeholder="000.000.000-00"></label><br><br>
        <label>Telefone: <input type="text" name="telefone" class="telefone-mask" maxlength="15" placeholder="(00) 00000-0000"></label><br><br>
        <button type="submit">Testar</button>
    </form>
    
    <script>
        // Máscara de CPF
        document.querySelectorAll('.cpf-mask').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                }
            });
        });

        // Máscara de Telefone
        document.querySelectorAll('.telefone-mask').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    e.target.value = value;
                }
            });
        });
    </script>
</body>
</html>
