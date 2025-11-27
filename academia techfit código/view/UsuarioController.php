// Arquivo: UsuarioController.php

<?php
require_once 'UsuarioDAO.php'; 
require_once 'Usuario.php';
require_once 'Connection.php'; 

class UsuarioController {
    private $dao;

    public function __construct() {
        // ...
        $this->dao = new UsuarioDAO();
    }

    public function cadastrar($nomeUsuario, $senha) {
        // Verifica se o usuário já existe
        if ($this->dao->buscarPorNomeUsuario($nomeUsuario)) {
            return "Erro: Nome de usuário já cadastrado.";
        }
        
        // Cria o objeto Usuário. O DAO definirá o tipo baseado no nome ('techfit' ou outro)
        $usuario = new Usuario($nomeUsuario, $senha); 
        
        return $this->dao->criarUsuario($usuario);
    }

    public function login($nomeUsuario, $senha) {
        $resultado = $this->dao->autenticarUsuario($nomeUsuario, $senha);
        
        if ($resultado) {
            // Inicia a sessão se o login for válido
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Armazena dados de autenticação
            $_SESSION['logged_in'] = true;
            $_SESSION['user_nome'] = $resultado['nome_usuario'];
            $_SESSION['user_tipo'] = $resultado['tipo_usuario'];
            
            return true; // Login bem-sucedido
        }
        
        return false; // Login falhou
    }
}