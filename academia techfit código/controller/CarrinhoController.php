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
    // Valida dados obrigatórios
    if (!isset($data['id']) || !isset($data['nome']) || !isset($data['preco'])) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Dados do plano incompletos'
        ]);
        return;
    }

    // Verifica se já existe um plano no carrinho (apenas 1 plano permitido)
    if (count($_SESSION['carrinho_planos']['itens']) > 0) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Você já possui um plano no carrinho. Remova-o antes de adicionar outro.',
            'carrinho' => $_SESSION['carrinho_planos']
        ]);
        return;
    }

    // Cria item do plano
    $item = [
        'id' => $data['id'],
        'tipo' => 'plano',
        'nome' => $data['nome'],
        'preco' => floatval($data['preco']),
        'descricao' => $data['descricao'] ?? '',
        'beneficios' => $data['beneficios'] ?? [],
        'quantidade' => 1,
        'subtotal' => floatval($data['preco'])
    ];

    // Adiciona ao carrinho
    $_SESSION['carrinho_planos']['itens'][] = $item;
    $_SESSION['carrinho_planos']['subtotal'] = floatval($data['preco']);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Plano adicionado ao carrinho com sucesso!',
        'carrinho' => $_SESSION['carrinho_planos']
    ]);
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
