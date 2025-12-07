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

    // Adicionar ao Carrinho (Tabela compras)
    public function adicionarAoCarrinho($idCliente, $produto) {
        // Verifica se tem imagem, senão usa padrão
        $imagem = !empty($produto['imagem']) ? $produto['imagem'] : 'padrao.jpg';

        // ATENÇÃO: Verifique se sua tabela se chama 'compras' ou 'carrinho' no banco
        $sql = "INSERT INTO compras (id_cliente, nome_produto, preco, quantidade, imagem_url, status, data_compra) 
                VALUES (:cliente, :nome, :preco, 1, :imagem, 'carrinho', NOW())";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':cliente' => $idCliente,
            ':nome'    => $produto['nome_produto'],
            ':preco'   => $produto['preco'],
            ':imagem'  => $imagem
        ]);
    }

    // Buscar Carrinho
    public function buscarCarrinho($idCliente) {
        $sql = "SELECT * FROM compras WHERE id_cliente = :id AND status = 'carrinho'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar Histórico
    public function buscarHistorico($idCliente) {
        $sql = "SELECT * FROM compras WHERE id_cliente = :id AND status = 'concluido' ORDER BY data_compra DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Finalizar Compra
    public function finalizarCompra($idCliente) {
        $sql = "UPDATE compras SET status = 'concluido', data_compra = NOW() 
                WHERE id_cliente = :id AND status = 'carrinho'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idCliente]);
    }
    
    // Remover do Carrinho
    public function removerDoCarrinho($idCompra) {
        $sql = "DELETE FROM compras WHERE id = :id AND status = 'carrinho'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idCompra]);
    }
}
?>