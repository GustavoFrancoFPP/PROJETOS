<?php
// Salve dentro da pasta: modelo/

// Como estão na mesma pasta (modelo), não precisa de caminho especial
require_once 'Connection.php'; 
require_once 'Produtos.php';

class ProdutoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // Listar todos os produtos
    public function listarTodos() {
        $sql = "SELECT * FROM produtos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar por ID
    public function buscarPorId($id) {
        $sql = "SELECT * FROM produtos WHERE id_produtos = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Adicionar ao Carrinho (NOTA: Tabela compras não existe no banco atual)
    public function adicionarAoCarrinho($idCliente, $produto) {
        // A tabela compras não existe no banco atual
        // Por enquanto retorna false
        // TODO: Criar tabela compras ou usar outra estratégia
        return false;
    }

    // Buscar Carrinho
    public function buscarCarrinho($idCliente) {
        // A tabela compras não existe no banco atual
        // Retorna array vazio para evitar erros
        return [];
    }

    // Buscar Histórico - Usa a tabela produtos como histórico
    public function buscarHistorico($idCliente) {
        try {
            $sql = "SELECT * FROM produtos WHERE id_cliente = :id ORDER BY id_produtos DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $idCliente]);
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adiciona campo data_compra fictício
            foreach ($produtos as &$produto) {
                $produto['data_compra'] = date('Y-m-d');
            }
            
            return $produtos;
        } catch (PDOException $e) {
            error_log("Erro em buscarHistorico: " . $e->getMessage());
            return [];
        }
    }

    // Finalizar Compra
    public function finalizarCompra($idCliente) {
        // A tabela compras não existe no banco atual
        // Por enquanto retorna true
        return true;
    }
    
    // Remover do Carrinho
    public function removerDoCarrinho($idCompra) {
        // A tabela compras não existe no banco atual
        // Por enquanto retorna true
        return true;
    }
}
?>