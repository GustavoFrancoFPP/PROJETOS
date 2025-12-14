<?php
try {
    $conn = new PDO('mysql:host=localhost;dbname=academia', 'root', '');
    $stmt = $conn->query('DESCRIBE venda');
    echo "Tabela venda:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>
