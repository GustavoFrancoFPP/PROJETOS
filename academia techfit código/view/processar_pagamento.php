<?php
/**
 * processar_pagamento.php
 * Processa pagamentos de planos e produtos
 */

// Desabilita exibição de erros para evitar HTML na resposta JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Headers para AJAX (antes de qualquer output)
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Connection.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado para fazer uma compra'
    ]);
    exit;
}

// Lê dados da requisição
$input = file_get_contents('php://input');
$dados = json_decode($input, true);

if (!$dados) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos'
    ]);
    exit;
}

try {
    $conn = Connection::getInstance();
    $conn->beginTransaction();
    
    $idCliente = $_SESSION['user_id'];
    $metodoPagamento = $dados['metodoPagamento'] ?? 'cartao';
    $dadosCompra = $dados['dadosCompra'] ?? null;
    $dadosCliente = $dados['dadosCliente'] ?? null;
    
    if (!$dadosCompra || !isset($dadosCompra['itens'])) {
        throw new Exception('Dados da compra inválidos');
    }
    
    // Buscar dados do cliente no banco
    $stmtCliente = $conn->prepare("SELECT nome_cliente, email, cpf, telefone, endereco FROM cliente WHERE id_cliente = ?");
    $stmtCliente->execute([$idCliente]);
    $clienteDB = $stmtCliente->fetch(PDO::FETCH_ASSOC);
    
    if (!$clienteDB) {
        throw new Exception('Dados do cliente não encontrados');
    }
    
    // Usar dados do formulário se fornecidos, senão usar do banco
    $nomeCliente = $dadosCliente['nome'] ?? $clienteDB['nome_cliente'];
    $emailCliente = $dadosCliente['email'] ?? $clienteDB['email'];
    $cpfCliente = $dadosCliente['cpf'] ?? $clienteDB['cpf'];
    $telefoneCliente = $dadosCliente['telefone'] ?? $clienteDB['telefone'];
    
    // Dados de endereço do formulário
    $enderecoCompleto = $clienteDB['endereco'] ?? '';
    if ($dadosCliente && isset($dadosCliente['endereco'])) {
        $end = $dadosCliente['endereco'];
        $enderecoCompleto = ($end['logradouro'] ?? '') . ', ' . ($end['numero'] ?? '');
    }
    
    $itens = $dadosCompra['itens'];
    $total = floatval($dadosCompra['total'] ?? 0);
    
    // Busca ID da forma de pagamento
    $stmtForma = $conn->prepare("SELECT id_forma_pagamento FROM forma_pagamento WHERE tipo = ? LIMIT 1");
    $stmtForma->execute([$metodoPagamento]);
    $formaRow = $stmtForma->fetch(PDO::FETCH_ASSOC);
    $idFormaPagamento = $formaRow ? $formaRow['id_forma_pagamento'] : null;
    
    $resultados = [];
    
    foreach ($itens as $item) {
        $tipo = $item['tipo'] ?? 'produto';
        
        if ($tipo === 'plano') {
            // Verifica se o cliente já tem um plano ativo
            $stmtVerifica = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM pagamento 
                WHERE id_cliente = ? 
                AND status_pagamento = 'pago'
            ");
            $stmtVerifica->execute([$idCliente]);
            $planoExistente = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
            
            if ($planoExistente['total'] > 0) {
                throw new Exception('Você já possui um plano ativo. Cancele o plano atual antes de contratar outro.');
            }
            
            // Processa pagamento de plano
            $idPlanos = $item['id_planos'] ?? null;
            
            if (!$idPlanos) {
                throw new Exception('ID do plano não encontrado');
            }
            
            // Insere na tabela pagamento
            $stmt = $conn->prepare("
                INSERT INTO pagamento (id_cliente, id_planos, data_pagamento, valor_pago, status_pagamento, id_forma_pagamento)
                VALUES (?, ?, NOW(), ?, 'pago', ?)
            ");
            
            $stmt->execute([
                $idCliente,
                $idPlanos,
                $item['preco'],
                $idFormaPagamento
            ]);
            
            $resultados[] = [
                'tipo' => 'plano',
                'id_pagamento' => $conn->lastInsertId(),
                'nome' => $item['nome']
            ];
            
        } else {
            // Processa venda de produto
            $idProduto = $item['id'] ?? null;
            $quantidade = intval($item['quantidade'] ?? 1);
            $valorTotal = floatval($item['subtotal'] ?? ($item['preco'] ?? 0) * $quantidade);
            
            if (!$idProduto || $valorTotal <= 0) {
                continue; // Pula produtos sem ID ou valor inválido
            }
            
            // Insere na tabela venda
            $stmt = $conn->prepare("
                INSERT INTO venda (id_cliente, id_produtos, quantidade, valor_total, data_venda)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $idCliente,
                $idProduto,
                $quantidade,
                $valorTotal
            ]);
            
            $resultados[] = [
                'tipo' => 'produto',
                'id_venda' => $conn->lastInsertId(),
                'nome' => $item['nome'] ?? 'Produto'
            ];
        }
    }
    
    // Salva dados do pedido na sessão para a página de confirmação
    $_SESSION['order'] = [
        'nome' => $nomeCliente,
        'email' => $emailCliente,
        'cpf' => $cpfCliente,
        'telefone' => $telefoneCliente,
        'endereco' => $dadosCliente['endereco']['logradouro'] ?? $enderecoCompleto,
        'numero' => $dadosCliente['endereco']['numero'] ?? '',
        'complemento' => $dadosCliente['endereco']['complemento'] ?? '',
        'bairro' => $dadosCliente['endereco']['bairro'] ?? '',
        'cidade' => $dadosCliente['endereco']['cidade'] ?? '',
        'estado' => $dadosCliente['endereco']['estado'] ?? '',
        'cep' => $dadosCliente['endereco']['cep'] ?? '',
        'metodo_pagamento' => $metodoPagamento,
        'itens' => $itens,
        'total' => $total
    ];
    
    // Limpa carrinho de planos da sessão
    if (isset($_SESSION['carrinho_planos'])) {
        unset($_SESSION['carrinho_planos']);
    }
    
    $conn->commit();
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pagamento processado com sucesso!',
        'resultados' => $resultados,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar pagamento: ' . $e->getMessage()
    ]);
}
?>
