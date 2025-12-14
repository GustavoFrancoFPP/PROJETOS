<?php
/**
 * Classe de Conexão com o Banco de Dados - Padrão Singleton
 * TechFit - Sistema de Gestão de Academia
 */

class Connection {
    private static $instance = null;
    
    // Configurações do banco
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'academia';
    private const DB_USER = 'root';
    private const DB_PASS = 'senaisp';
    private const DB_CHARSET = 'utf8mb4';
    
    /**
     * Construtor privado para impedir instanciação direta
     */
    private function __construct() {}
    
    /**
     * Previne clonagem do objeto
     */
    private function __clone() {}
    
    /**
     * Previne unserialize
     */
    public function __wakeup() {
        throw new Exception("Não é possível unserialize singleton");
    }
    
    /**
     * Retorna a instância única da conexão (Singleton)
     * @return PDO
     */
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    self::DB_HOST,
                    self::DB_NAME,
                    self::DB_CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
                
                // Define charset após a conexão (evita warning de deprecação)
                self::$instance->exec("SET NAMES utf8mb4");
                
            } catch (PDOException $e) {
                error_log("ERRO DE CONEXÃO: " . $e->getMessage());
                die("Erro ao conectar ao banco de dados. Contate o administrador.");
            }
        }
        
        return self::$instance;
    }
}