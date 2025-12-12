<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
    exit;
}

// Lê dados JSON da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Valida entrada
if (!$data || !isset($data['action'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos']);
    exit;
}

// Inicializa carrinho na sessão se não existir
if (!isset($_SESSION['carrinho_planos'])) {
    $_SESSION['carrinho_planos'] = [
        'itens' => [],
        'subtotal' => 0
    ];
}

// Processa ações
switch ($data['action']) {
    case 'adicionar_plano':
        adicionarPlano($data);
        break;
    
    case 'adicionar_produto':
        adicionarProduto($data);
        break;
    
    case 'remover_plano':
        removerPlano($data);
        break;
    
    case 'remover_item':
        removerItem($data);
        break;
    
    case 'limpar_carrinho':
        limparCarrinho();
        break;
    
    case 'obter_carrinho':
        obterCarrinho();
        break;
    
    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
        break;
}

/**
 * Adiciona um plano ao carrinho
 */
function adicionarPlano($data) {
    // Verifica se já existe um plano no carrinho (apenas 1 plano permitido)
    if (count($_SESSION['carrinho_planos']['itens']) > 0) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Você já possui um plano no carrinho. Remova-o antes de adicionar outro.',
            'carrinho' => $_SESSION['carrinho_planos']
        ]);
        return;
    }
    
    // Busca dados do plano no banco de dados
    require_once __DIR__ . '/../config/Connection.php';
    
    try {
        $conn = Connection::getInstance();
        
        // Extrai o ID numérico do plano (ex: "plano-sigma" -> busca pelo nome)
        $planoNome = '';
        if (isset($data['id'])) {
            $idMap = [
                'plano-sigma' => 'Plano Sigma',
                'plano-alpha' => 'Plano Alpha',
                'plano-beta' => 'Plano Beta'
            ];
            $planoNome = $idMap[$data['id']] ?? '';
        }
        
        if (empty($planoNome)) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Plano inválido'
            ]);
            return;
        }
        
        // Busca o plano no banco (tabela usa nome_planos e valor)
        $stmt = $conn->prepare("SELECT id_planos, nome_planos, descricao, valor FROM planos WHERE nome_planos = ?");
        $stmt->execute([$planoNome]);
        $planoDB = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$planoDB) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Plano não encontrado no banco de dados. Execute setup_planos.php primeiro.'
            ]);
            return;
        }
        
        // Cria item do plano com dados do banco
        $item = [
            'id' => $data['id'], // ID original (plano-sigma, etc)
            'id_planos' => $planoDB['id_planos'], // ID numérico do banco
            'tipo' => 'plano',
            'nome' => $planoDB['nome_planos'],
            'preco' => floatval($planoDB['valor']),
            'descricao' => $planoDB['descricao'] ?? '',
            'duracao_meses' => 1,
            'quantidade' => 1,
            'subtotal' => floatval($planoDB['valor'])
        ];

        // Adiciona ao carrinho
        $_SESSION['carrinho_planos']['itens'][] = $item;
        $_SESSION['carrinho_planos']['subtotal'] = floatval($planoDB['valor']);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Plano adicionado ao carrinho com sucesso!',
            'carrinho' => $_SESSION['carrinho_planos']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao buscar plano: ' . $e->getMessage()
        ]);
    }
}

/**
 * Adiciona um produto ao carrinho
 */
function adicionarProduto($data) {
    // LIMPA ITENS INVÁLIDOS antes de adicionar
    if (isset($_SESSION['carrinho_planos']['itens']) && is_array($_SESSION['carrinho_planos']['itens'])) {
        $itensValidos = [];
        foreach ($_SESSION['carrinho_planos']['itens'] as $item) {
            if (is_array($item) && isset($item['tipo']) && isset($item['id']) && isset($item['nome']) && 
                !empty($item['id']) && !empty($item['nome'])) {
                $itensValidos[] = $item;
            }
        }
        $_SESSION['carrinho_planos']['itens'] = $itensValidos;
    }
    
    require_once __DIR__ . '/../config/Connection.php';
    
    try {
        $conn = Connection::getInstance();
        
        // Busca o produto no banco
        $stmt = $conn->prepare("SELECT id_produtos, nome_produto, tipo_produto, categoria, preco, quantidade 
                                FROM produtos 
                                WHERE id_produtos = ?");
        $stmt->execute([$data['id']]);
        $produtoDB = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produtoDB) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado'
            ]);
            return;
        }
        
        // Verifica estoque
        if ($produtoDB['quantidade'] <= 0) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Produto sem estoque'
            ]);
            return;
        }
        
        // Cria item do produto
        $quantidade = isset($data['quantidade']) ? intval($data['quantidade']) : 1;
        $item = [
            'id' => $produtoDB['id_produtos'],
            'tipo' => 'produto',
            'nome' => $produtoDB['nome_produto'],
            'preco' => floatval($produtoDB['preco']),
            'descricao' => $produtoDB['tipo_produto'] . ' - ' . $produtoDB['categoria'],
            'quantidade' => $quantidade,
            'subtotal' => floatval($produtoDB['preco']) * $quantidade
        ];
        
        // Verifica se o produto já está no carrinho
        $encontrado = false;
        foreach ($_SESSION['carrinho_planos']['itens'] as &$itemCarrinho) {
            if (is_array($itemCarrinho) && 
                isset($itemCarrinho['tipo']) && 
                $itemCarrinho['tipo'] === 'produto' && 
                isset($itemCarrinho['id']) &&
                $itemCarrinho['id'] == $produtoDB['id_produtos']) {
                $itemCarrinho['quantidade'] += $quantidade;
                $itemCarrinho['subtotal'] = $itemCarrinho['preco'] * $itemCarrinho['quantidade'];
                $encontrado = true;
                break;
            }
        }
        unset($itemCarrinho); // Importante: libera a referência
        
        // Se não encontrou, adiciona novo item
        if (!$encontrado) {
            $_SESSION['carrinho_planos']['itens'][] = $item;
        }
        
        // Recalcula o subtotal
        $novoSubtotal = 0;
        foreach ($_SESSION['carrinho_planos']['itens'] as $itemCarrinho) {
            $novoSubtotal += floatval($itemCarrinho['subtotal']);
        }
        $_SESSION['carrinho_planos']['subtotal'] = $novoSubtotal;
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Produto adicionado ao carrinho!',
            'carrinho' => $_SESSION['carrinho_planos']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao adicionar produto: ' . $e->getMessage()
        ]);
    }
}

/**
 * Remove um plano do carrinho
 */
function removerPlano($data) {
    if (!isset($data['id'])) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID do plano não informado'
        ]);
        return;
    }

    $idPlano = $data['id'];
    $novaLista = [];
    $novoSubtotal = 0;

    // Reconstrói array sem o plano removido
    foreach ($_SESSION['carrinho_planos']['itens'] as $item) {
        if ($item['id'] != $idPlano) {
            $novaLista[] = $item;
            $novoSubtotal += $item['subtotal'];
        }
    }

    $_SESSION['carrinho_planos']['itens'] = $novaLista;
    $_SESSION['carrinho_planos']['subtotal'] = $novoSubtotal;

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Plano removido do carrinho',
        'carrinho' => $_SESSION['carrinho_planos']
    ]);
}

/**
 * Remove qualquer item (plano ou produto) do carrinho
 */
function removerItem($data) {
    if (!isset($data['id']) || !isset($data['tipo'])) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID ou tipo do item não informado'
        ]);
        return;
    }

    $idItem = $data['id'];
    $tipoItem = $data['tipo'];
    $novaLista = [];
    $novoSubtotal = 0;

    // Reconstrói array sem o item removido
    foreach ($_SESSION['carrinho_planos']['itens'] as $item) {
        if (!($item['id'] == $idItem && $item['tipo'] == $tipoItem)) {
            $novaLista[] = $item;
            $novoSubtotal += $item['subtotal'];
        }
    }

    $_SESSION['carrinho_planos']['itens'] = $novaLista;
    $_SESSION['carrinho_planos']['subtotal'] = $novoSubtotal;

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Item removido do carrinho',
        'carrinho' => $_SESSION['carrinho_planos']
    ]);
}

/**
 * Limpa todos os planos do carrinho
 */
function limparCarrinho() {
    $_SESSION['carrinho_planos'] = [
        'itens' => [],
        'subtotal' => 0
    ];

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Carrinho limpo com sucesso',
        'carrinho' => $_SESSION['carrinho_planos']
    ]);
}

/**
 * Retorna o carrinho atual
 */
function obterCarrinho() {
    echo json_encode([
        'sucesso' => true,
        'carrinho' => $_SESSION['carrinho_planos']
    ]);
}
