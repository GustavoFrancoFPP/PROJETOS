<?php
// Salve na RAIZ (fora da pasta modelo)
session_start();

// O Controller está na raiz, então ele precisa entrar na pasta 'modelo' para achar o DAO
require_once 'ProdutoDAO.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$dao = new ProdutoDAO();
$idCliente = $_SESSION['user_id'];

// 1. ADICIONAR
if (isset($_POST['add_carrinho'])) {
    $idProduto = $_POST['id_produto'];
    
    $produto = $dao->buscarPorId($idProduto);
    
    if ($produto) {
        $dao->adicionarAoCarrinho($idCliente, $produto);
    }
    
    header('Location: produtos.php?msg=adicionado');
    exit;
}

// 2. FINALIZAR
if (isset($_POST['finalizar'])) {
    $dao->finalizarCompra($idCliente);
    header('Location: confirmacao.php');
    exit;
}

// 3. REMOVER
if (isset($_POST['remover'])) {
    $idCompra = $_POST['id_compra'];
    $dao->removerDoCarrinho($idCompra);
    header('Location: dashboard_aluno.php');
    exit;
}
?>