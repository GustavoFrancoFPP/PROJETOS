<?php
require_once __DIR__ . '/../config/Connection.php';
$conn = Connection::getInstance();
$conn->exec('UPDATE produtos SET quantidade_estoque = 0 WHERE quantidade_estoque IS NULL;');
echo 'Produtos com estoque NULL corrigidos.';
