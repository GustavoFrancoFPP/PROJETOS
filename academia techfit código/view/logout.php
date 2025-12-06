<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroi a sessão
session_destroy();

// Redireciona para a página inicial
header('Location: inicio.html');
exit;
?>
