<?php
/**
 * CarrinhoController - Gerenciador de Carrinho de Planos e Produtos
 * 
 * Responsável por:
 * - Adicionar planos ao carrinho via AJAX
 * - Gerenciar sessão de carrinho
 * - Integração com produtos via localStorage (frontend)
 */

// Inicia sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuração de headers para AJAX
header('Content-Type: application/json');

// Verifica método de requisição
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
    
    case 'remover_plano':
        removerPlano($data);
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
    // Verifica se o plano já está no carrinho (evitar duplicatas)
    foreach ($_SESSION['carrinho_planos']['itens'] as $item) {
        if (isset($item['id']) && $item['id'] == $data['id']) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Este plano já está no carrinho',
                'carrinho' => $_SESSION['carrinho_planos']
            ]);
            return;
        }
    }

    // Busca dados do plano no banco de dados
    require_once __DIR__ . '/../config/Connection.php';
    
    try {
        $conn = Connection::getInstance();
        
        // Verifica se o usuário já tem um plano ativo (se estiver logado)
        if (isset($_SESSION['user_id'])) {
            $stmtVerifica = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM pagamento 
                WHERE id_cliente = ? 
                AND status_pagamento = 'pago'
            ");
            $stmtVerifica->execute([$_SESSION['user_id']]);
            $planoExistente = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
            
            if ($planoExistente['total'] > 0) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Você já possui um plano ativo. Cancele seu plano atual antes de contratar outro.'
                ]);
                return;
            }
        }
        
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
        $_SESSION['carrinho_planos']['subtotal'] += floatval($planoDB['valor']);

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
