<?php
/**
 * Cancelar Plano - Atualiza status de pagamento para "cancelado"
 */

// Desabilita exibição de erros para evitar HTML na resposta JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Headers para JSON (antes de qualquer output)
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado']);
    exit;
}

// Verificar método de requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
    exit;
}

// Lê dados JSON da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Valida entrada
if (!$data || !isset($data['id_pagamento'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID de pagamento não fornecido']);
    exit;
}

require_once __DIR__ . '/../config/Connection.php';

try {
    $conn = Connection::getInstance();
    $idCliente = $_SESSION['user_id'];
    $idPagamento = intval($data['id_pagamento']);
    
    // Verificar se o pagamento pertence ao usuário logado
    $stmt = $conn->prepare("
        SELECT id_pagamento, status_pagamento 
        FROM pagamento 
        WHERE id_pagamento = ? AND id_cliente = ?
    ");
    $stmt->execute([$idPagamento, $idCliente]);
    $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pagamento) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Pagamento não encontrado']);
        exit;
    }
    
    if ($pagamento['status_pagamento'] === 'cancelado') {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Este plano já está cancelado']);
        exit;
    }
    
    // Atualizar status para cancelado
    $stmt = $conn->prepare("
        UPDATE pagamento 
        SET status_pagamento = 'cancelado' 
        WHERE id_pagamento = ? AND id_cliente = ?
    ");
    $stmt->execute([$idPagamento, $idCliente]);
    
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => 'Plano cancelado com sucesso!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Erro ao cancelar plano: ' . $e->getMessage()
    ]);
}
