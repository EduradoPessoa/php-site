<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

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
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND status = 1');
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
            
            // Registrar log
            $this->logActivity($user['id'], 'login', 'Login bem-sucedido');
            
            return true;
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'Logout realizado');
        }
        
        session_destroy();
        session_start();
    }
    
    public function isAuthenticated() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar timeout da sessão
        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function hasRole($role) {
        return $this->isAuthenticated() && $_SESSION['user_role'] === $role;
    }
    
    private function isRateLimited($email) {
        if (!isset($this->loginAttempts[$email])) {
            return false;
        }
        
        $attempts = array_filter($this->loginAttempts[$email], function($time) {
            return $time > time() - LOGIN_TIMEOUT;
        });
        
        return count($attempts) >= MAX_LOGIN_ATTEMPTS;
    }
    
    private function recordLoginAttempt($email) {
        if (!isset($this->loginAttempts[$email])) {
            $this->loginAttempts[$email] = [];
        }
        
        $this->loginAttempts[$email][] = time();
    }
    
    private function logActivity($userId, $action, $description = '') {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO activity_logs (user_id, action, description)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$userId, $action, $description]);
        } catch (Exception $e) {
            error_log("Erro ao registrar atividade: " . $e->getMessage());
        }
    }
}