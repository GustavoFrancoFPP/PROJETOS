<?php
// Arquivo: modelo/AlunoDAO.php

require_once 'Connection.php';

class AlunoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // Buscar informações completas do aluno
    public function buscarAlunoPorId($idAluno) {
        $sql = "SELECT c.*
                FROM cliente c
                WHERE c.id_cliente = :id_aluno";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar planos do aluno
    public function buscarPlanosDoAluno($idAluno) {
        $sql = "SELECT p.*
                FROM planos p
                WHERE p.id_cliente = :id_aluno
                ORDER BY p.id_planos DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar produtos comprados pelo aluno
    public function buscarProdutosDoAluno($idAluno) {
        $sql = "SELECT p.*
                FROM produtos p
                WHERE p.id_cliente = :id_aluno
                ORDER BY p.id_produtos DESC
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar agendamentos futuros do aluno (CORRIGIDO)
    public function buscarAgendamentosDoAluno($idAluno): array {
        $sql = "SELECT * FROM agendamento a
                WHERE a.id_cliente = :id_aluno
                AND a.data_agendamento >= CURDATE()
                ORDER BY a.data_agendamento ASC
                LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar avaliações do aluno
    public function buscarAvaliacoesDoAluno($idAluno) {
        $sql = "SELECT av.*, 
                       f.nome_funcionario as avaliador
                FROM avaliacao av
                LEFT JOIN funcionario f ON av.id_avaliacao = f.id_avaliacao
                INNER JOIN possui p ON av.id_avaliacao = p.id_avaliacao
                WHERE p.id_cliente = :id_aluno
                ORDER BY av.data_avaliacao DESC
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Calcular total gasto pelo aluno
    public function calcularTotalGasto($idAluno) {
        // Soma dos planos
        $sqlPlanos = "SELECT COALESCE(SUM(p.valor), 0) as total_planos
                      FROM planos p
                      WHERE p.id_cliente = :id_aluno";
        
        // Soma dos produtos
        $sqlProdutos = "SELECT COALESCE(SUM(p.preco * p.quantidade), 0) as total_produtos
                        FROM produtos p
                        WHERE p.id_cliente = :id_aluno";
        
        $stmt = $this->conn->prepare($sqlPlanos);
        $stmt->execute([':id_aluno' => $idAluno]);
        $planos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $this->conn->prepare($sqlProdutos);
        $stmt->execute([':id_aluno' => $idAluno]);
        $produtos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array(
            'total_planos' => $planos['total_planos'] ?? 0,
            'total_produtos' => $produtos['total_produtos'] ?? 0,
            'total_geral' => ($planos['total_planos'] ?? 0) + ($produtos['total_produtos'] ?? 0)
        );
    }

    // Buscar presenças recentes (tabela presenca não existe, retorna array vazio)
    public function buscarPresencasDoAluno($idAluno) {
        // A tabela presenca não existe no banco atual
        // Retornando array vazio para evitar erros
        return [];
    }

    // Buscar próximos vencimentos (tabelas fatura/pagamento não existem)
    public function buscarProximosVencimentos($idAluno) {
        // As tabelas fatura e pagamento não existem no banco atual
        // Retornando array vazio para evitar erros
        return [];
    }

    // Buscar treinos do aluno (tabela treinos não existe)
    public function buscarTreinosDoAluno($idAluno) {
        // A tabela treinos não existe no banco atual
        // Retornando array vazio para evitar erros
        return [];
    }

    // Buscar exercícios do treino (tabela exercicio não existe)
    public function buscarExerciciosDoTreino($idTreino) {
        // A tabela exercicio não existe no banco atual
        // Retornando array vazio para evitar erros
        return [];
    }

    // Verificar existência de tabelas (para debug)
    public function verificarTabelas() {
        try {
            $tabelas = ['fatura', 'pagamento', 'planos', 'venda', 'presenca', 'treinos'];
            $resultados = [];
            
            foreach ($tabelas as $tabela) {
                $stmt = $this->conn->query("SHOW TABLES LIKE '$tabela'");
                $resultados[$tabela] = $stmt->rowCount() > 0;
            }
            
            return $resultados;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>