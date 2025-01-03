<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    private static $instance = null;
    private $pdo;
    private $loginAttempts = [];
    
    private function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function login($email, $password) {
        try {
            // Verificar rate limiting
            if ($this->isRateLimited($email)) {
                throw new Exception('Muitas tentativas de login. Tente novamente em 5 minutos.');
            }
            
            // Buscar usuário
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND active = 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar senha
            if (!$user || !password_verify($password, $user['password'])) {
                $this->recordLoginAttempt($email);
                throw new Exception('Email ou senha inválidos.');
            }
            
            // Limpar tentativas de login
            unset($this->loginAttempts[$email]);
            
            // Iniciar sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['last_activity'] = time();
            
            return true;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    private function isRateLimited($email) {
        if (!isset($this->loginAttempts[$email])) {
            return false;
        }
        
        $attempts = $this->loginAttempts[$email];
        if (count($attempts) < 5) {
            return false;
        }
        
        // Verificar se já passaram 5 minutos desde a última tentativa
        $lastAttempt = end($attempts);
        return (time() - $lastAttempt) < 300;
    }
    
    private function recordLoginAttempt($email) {
        if (!isset($this->loginAttempts[$email])) {
            $this->loginAttempts[$email] = [];
        }
        $this->loginAttempts[$email][] = time();
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'Logout realizado');
        }
        session_destroy();
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
        $this->checkSessionTimeout();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['user_role'] === $role;
    }
    
    private function checkSessionTimeout() {
        $timeout = 30 * 60; // 30 minutos
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            $this->logout();
            header('Location: /login.php?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
    
    private function logActivity($userId, $action, $description) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO activity_logs (user_id, action, description)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$userId, $action, $description]);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
}

// Inicializar a classe Auth
$auth = Auth::getInstance();