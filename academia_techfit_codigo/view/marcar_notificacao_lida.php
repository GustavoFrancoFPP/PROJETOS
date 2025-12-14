<?php
/**
 * Marca uma notificação como lida
 * TechFit - Sistema de Academia
 */

// Inicia sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define header para JSON
header('Content-Type: application/json');

// Verifica se está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado'
    ]);
    exit;
}

require_once __DIR__ . '/../config/Connection.php';

try {
    // Recebe dados JSON
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($dados['id_notificacao'])) {
        throw new Exception('ID da notificação não fornecido');
    }
    
    $idNotificacao = $dados['id_notificacao'];
    $idCliente = $_SESSION['user_id'];
    
    $conn = Connection::getInstance();
    
    // Verifica se a notificação pertence ao cliente
    $stmt = $conn->prepare("
        SELECT id_notificacao 
        FROM notificacao 
        WHERE id_notificacao = ? AND id_cliente = ?
    ");
    $stmt->execute([$idNotificacao, $idCliente]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Notificação não encontrada ou não pertence ao usuário');
    }
    
    // Marca como lida
    $stmt = $conn->prepare("
        UPDATE notificacao 
        SET status = 'lida' 
        WHERE id_notificacao = ? AND id_cliente = ?
    ");
    $stmt->execute([$idNotificacao, $idCliente]);
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Notificação marcada como lida'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao marcar notificação como lida: " . $e->getMessage());
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
