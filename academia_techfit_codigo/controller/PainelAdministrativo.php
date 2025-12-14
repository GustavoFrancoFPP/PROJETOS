<?php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$nome_usuario_logado = $_SESSION['user_nome'] ?? '';
$tipo_usuario_logado = $_SESSION['tipo_usuario'] ?? 'cliente';

// Verifica se é funcionário/admin
$is_admin = ($tipo_usuario_logado === 'funcionario');

// Restrição para o painel administrativo: Apenas funcionários podem acessar
if (basename($_SERVER['PHP_SELF']) === 'painel_administrativo.php' && !$is_admin) {
    header('Location: dashboard_aluno.php');
    exit;
}

// Lógica de Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>