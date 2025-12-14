<?php
// Arquivo: modelo/UsuarioDAO.php

// Inclusões DENTRO DA MESMA PASTA
require_once 'Usuario.php'; 
require_once 'Connection.php';

// ATENÇÃO: O NOME DA CLASSE DEVE SER EXATAMENTE ESSE
class UsuarioDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
        
        // Cria a tabela 'login' se ela não existir
        $this->conn->exec("CREATE TABLE IF NOT EXISTS login(
            nome_usuario VARCHAR(100) NOT NULL UNIQUE,
            senha_usuario VARCHAR(56) NOT NULL,
            tipo_usuario VARCHAR(20) NOT NULL DEFAULT 'cliente',
            id_cliente INT,
            id_funcionario INT,
            PRIMARY KEY (nome_usuario)
        )");
    }

    // CREATE (Criação de novo usuário)
    public function criarUsuario(Usuario $usuario) {
        $tipoUsuario = ($usuario->getNomeUsuario() === 'techfit') ? 'funcionario' : 'cliente';
        $sql = "INSERT INTO login (nome_usuario, senha_usuario, tipo_usuario, id_cliente, id_funcionario) 
                VALUES (:nome_usuario, SHA2(:senha_usuario, 224), :tipo_usuario, :id_cliente, :id_funcionario)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute([
            ':nome_usuario' => $usuario->getNomeUsuario(),
            ':senha_usuario' => $usuario->getSenhaUsuario(),
            ':tipo_usuario' => $tipoUsuario,
            ':id_cliente' => $usuario->getIdCliente(), 
            ':id_funcionario' => $usuario->getIdFuncionario()
        ]);
        
        return $this->conn->lastInsertId();
    }

    // READ (Busca para LOGIN)
    public function autenticarUsuario($nomeUsuario, $senha) {
        $stmt = $this->conn->prepare("SELECT nome_usuario, tipo_usuario FROM login 
                                   WHERE nome_usuario = :nome AND senha_usuario = SHA2(:senha, 224) LIMIT 1");
        $stmt->execute([':nome' => $nomeUsuario, ':senha' => $senha]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // READ (Busca para checar se o nome de usuário já existe)
    public function buscarPorNomeUsuario($nomeUsuario) {
        $stmt = $this->conn->prepare("SELECT nome_usuario FROM login WHERE nome_usuario = :nome_usuario LIMIT 1");
        $stmt->execute([':nome_usuario' => $nomeUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}