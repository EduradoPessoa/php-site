<?php
// Configurações do Site
define('SITE_NAME', 'Sistema de Gestão Empresarial');
define('SITE_URL', 'http://localhost:8888');

// Configurações de Email
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'seu-email@gmail.com');
define('MAIL_PASSWORD', 'sua-senha-app');
define('MAIL_FROM', 'seu-email@gmail.com');
define('MAIL_FROM_NAME', 'Sistema de Gestão');

// Configurações de Segurança
define('SESSION_LIFETIME', 3600); // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutos
define('PASSWORD_MIN_LENGTH', 6);
define('TOKEN_LIFETIME', 3600); // 1 hora para tokens de recuperação

// Configurações de Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Configurações de Log
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'debug'); // debug, info, warning, error

// Configurações de Backup
define('BACKUP_PATH', __DIR__ . '/../data/backups/');
define('BACKUP_RETENTION_DAYS', 7);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Inicialização da Sessão
session_start();

// Funções de Utilidade
function isProduction() {
    return false; // Altere para true em produção
}

function getBaseUrl() {
    return SITE_URL;
}

function redirect($path) {
    header('Location: ' . SITE_URL . $path);
    exit;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function validateToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Configuração de Handlers de Erro
if (!isProduction()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Handler de Erros Personalizado
function errorHandler($errno, $errstr, $errfile, $errline) {
    $logMessage = date('Y-m-d H:i:s') . " [$errno] $errstr in $errfile on line $errline\n";
    error_log($logMessage, 3, LOG_PATH . 'error.log');
    
    if (isProduction()) {
        return true;
    }
    return false;
}
set_error_handler('errorHandler');

// Handler de Exceções Não Capturadas
function exceptionHandler($exception) {
    $logMessage = date('Y-m-d H:i:s') . " [Exception] {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}\n";
    error_log($logMessage, 3, LOG_PATH . 'exceptions.log');
    
    if (isProduction()) {
        http_response_code(500);
        include __DIR__ . '/../public/500.php';
        exit;
    }
    throw $exception;
}
set_exception_handler('exceptionHandler');

// Configurações do Banco de Dados
define('BASE_PATH', dirname(__DIR__));
define('DB_PATH', BASE_PATH . '/database/portal.sqlite');