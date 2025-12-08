<?php
/**
 * ========================================
 * ARQUIVO DE CONFIGURAÇÃO GLOBAL - TECHFIT
 * Define caminhos e configurações do sistema
 * ========================================
 */

// Define o caminho raiz do projeto
define('ROOT_PATH', dirname(__FILE__) . '/');

// Define caminhos das pastas
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('MODEL_PATH', ROOT_PATH . 'model/');
define('CONTROLLER_PATH', ROOT_PATH . 'controller/');
define('VIEW_PATH', ROOT_PATH . 'view/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('SQL_PATH', ROOT_PATH . 'sql/');
define('UTILS_PATH', ROOT_PATH . 'utils/');

// Define URLs base
define('BASE_URL', '/academia%20techfit%20código/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');

// Configurações do sistema
define('SITE_NAME', 'TechFit');
define('SITE_DESCRIPTION', 'Sistema de Gestão de Academia');
define('SITE_VERSION', '2.0 - MVC');

// Função auxiliar para incluir arquivos
function require_config($file) {
    require_once CONFIG_PATH . $file;
}

function require_model($file) {
    require_once MODEL_PATH . $file;
}

function require_controller($file) {
    require_once CONTROLLER_PATH . $file;
}

// Inicializa sessão se não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
