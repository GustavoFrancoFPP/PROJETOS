<?php
require_once '../config/Connection.php';

try {
    $conn = Connection::getInstance();
    
    echo "=== AULAS CADASTRADAS NO BANCO ===\n\n";
    $stmt = $conn->query("SELECT id_aula, nome_aula, horario, dia_semana, instrutor, vagas_totais, vagas_ocupadas, status FROM aulas ORDER BY horario");
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($aulas)) {
        echo "❌ Nenhuma aula cadastrada!\n";
    } else {
        foreach ($aulas as $aula) {
            $vagas_disp = $aula['vagas_totais'] - $aula['vagas_ocupadas'];
            echo "ID: {$aula['id_aula']}\n";
            echo "  Nome: '{$aula['nome_aula']}'\n";
            echo "  Horário: {$aula['horario']}\n";
            echo "  Dia: {$aula['dia_semana']}\n";
            echo "  Instrutor: {$aula['instrutor']}\n";
            echo "  Vagas: {$vagas_disp}/{$aula['vagas_totais']}\n";
            echo "  Status: {$aula['status']}\n\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
