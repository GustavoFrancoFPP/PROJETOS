<?php 

class Connection{
    private static $instance = null;
    public static function getInstance(){
        if (!self::$instance) {
            try{
                $host = 'localhost';
                // ATUALIZADO: Usar o banco de dados 'academia'
                $dbname = 'academia'; 
                $user = 'root';
                $pass = 'senaisp'; // Assuma a sua senha de banco

                $dsn = "mysql:host=$host;charset=utf8mb4";
                
                self::$instance = new PDO($dsn, $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                // Garante que a conexão use o DB 'academia'
                self::$instance->exec("USE `$dbname`");
            }catch(PDOException $e){
                die("erro ao conectar ao mysql:" . $e->getMessage());
            }
        }
        return self::$instance;
    }
}