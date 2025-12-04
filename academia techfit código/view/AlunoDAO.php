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
        $sql = "SELECT c.*, 
                       l.nome_usuario,
                       l.tipo_usuario
                FROM cliente c
                LEFT JOIN login l ON c.id_cliente = l.id_cliente
                WHERE c.id_cliente = :id_aluno
                AND c.status = 'ativo'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar planos do aluno
    public function buscarPlanosDoAluno($idAluno) {
        $sql = "SELECT p.*, 
                       pg.data_pagamento,
                       pg.status_pagamento
                FROM planos p
                LEFT JOIN pagamento pg ON p.id_planos = pg.id_planos
                WHERE p.id_cliente = :id_aluno
                AND p.status = 'ativo'
                ORDER BY pg.data_pagamento DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar produtos comprados pelo aluno
    public function buscarProdutosDoAluno($idAluno) {
        $sql = "SELECT v.*, 
                       p.nome_produto,
                       p.tipo_produto,
                       p.categoria,
                       p.preco
                FROM venda v
                INNER JOIN produtos p ON v.id_produtos = p.id_produtos
                WHERE v.id_cliente = :id_aluno
                ORDER BY v.data_venda DESC
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar agendamentos futuros do aluno
    public function buscarAgendamentosDoAluno($idAluno) {
        $sql = "SELECT a.* 
                FROM agendamento a
                WHERE a.id_cliente = :id_aluno
                AND a.data_agendamento >= NOW()
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
        // Soma dos pagamentos de planos
        $sqlPlanos = "SELECT COALESCE(SUM(pg.valor_pago), 0) as total_planos
                      FROM pagamento pg
                      WHERE pg.id_cliente = :id_aluno
                      AND pg.status_pagamento = 'pago'";
        
        // Soma dos produtos
        $sqlProdutos = "SELECT COALESCE(SUM(v.valor_total), 0) as total_produtos
                        FROM venda v
                        WHERE v.id_cliente = :id_aluno";
        
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

    // Buscar presenças recentes
    public function buscarPresencasDoAluno($idAluno) {
        $sql = "SELECT pr.*, 
                       ag.tipo_aula,
                       ag.data_agendamento
                FROM presenca pr
                INNER JOIN agendamento ag ON pr.id_agendamento = ag.id_agendamento
                WHERE pr.id_cliente = :id_aluno
                ORDER BY pr.data_presenca DESC
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_aluno' => $idAluno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar próximos vencimentos - CORRIGIDO
    public function buscarProximosVencimentos($idAluno) {
        try {
            // Verificar se a tabela fatura existe
            $tabelaFatura = $this->conn->query("SHOW TABLES LIKE 'fatura'")->rowCount();
            
            if ($tabelaFatura > 0) {
                $sql = "SELECT f.*,
                               pl.nome_planos,
                               pl.valor as valor_plano,
                               pl.descricao
                        FROM fatura f
                        INNER JOIN pagamento pg ON f.id_pagamento = pg.id_pagamento
                        INNER JOIN planos pl ON pg.id_planos = pl.id_planos
                        WHERE f.id_cliente = :id_aluno
                        AND (f.status = 'aberta' OR f.status = 'vencida')
                        AND f.vencimento >= CURDATE()
                        ORDER BY f.vencimento ASC
                        LIMIT 3";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':id_aluno' => $idAluno]);
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return $resultados;
            } else {
                // Se não existir tabela fatura, use pagamento como base
                $sql = "SELECT pg.*,
                               pl.nome_planos,
                               pl.valor as valor_plano,
                               pl.descricao,
                               DATE_ADD(pg.data_pagamento, INTERVAL 30 DAY) as vencimento
                        FROM pagamento pg
                        INNER JOIN planos pl ON pg.id_planos = pl.id_planos
                        WHERE pg.id_cliente = :id_aluno
                        AND pg.status_pagamento = 'pago'
                        AND DATE_ADD(pg.data_pagamento, INTERVAL 30 DAY) >= CURDATE()
                        ORDER BY pg.data_pagamento DESC
                        LIMIT 3";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':id_aluno' => $idAluno]);
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Adicionar campos para compatibilidade
                foreach ($resultados as &$resultado) {
                    $resultado['valor_total'] = $resultado['valor_pago'];
                    $resultado['status'] = 'aberta';
                }
                
                return $resultados;
            }
        } catch (PDOException $e) {
            error_log("Erro em buscarProximosVencimentos: " . $e->getMessage());
            return [];
        }
    }

    // Buscar treinos do aluno
    public function buscarTreinosDoAluno($idAluno) {
        try {
            $sql = "SELECT t.*, 
                           f.nome_funcionario as instrutor
                    FROM treinos t
                    LEFT JOIN funcionario f ON t.id_funcionario = f.id_funcionario
                    WHERE t.id_cliente = :id_aluno
                    ORDER BY t.data_inicio DESC
                    LIMIT 3";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id_aluno' => $idAluno]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em buscarTreinosDoAluno: " . $e->getMessage());
            return [];
        }
    }

    // Buscar exercícios do treino
    public function buscarExerciciosDoTreino($idTreino) {
        try {
            $sql = "SELECT * FROM exercicio 
                    WHERE id_treino = :id_treino
                    ORDER BY id_exercicio";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id_treino' => $idTreino]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro em buscarExerciciosDoTreino: " . $e->getMessage());
            return [];
        }
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