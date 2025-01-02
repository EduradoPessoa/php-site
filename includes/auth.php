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
        // Verificar rate limiting
        if ($this->isRateLimited($email)) {
            throw new Exception('Muitas tentativas de login. Tente novamente em 5 minutos.');
        }
        
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND status = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
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
        
        return $user;
    }
    
    public function register($data) {
        // Validar dados
        $this->validateRegistrationData($data);
        
        // Verificar email único
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            throw new Exception('Este email já está cadastrado.');
        }
        
        // Criar usuário
        $stmt = $this->pdo->prepare('
            INSERT INTO users (name, email, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ');
        
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'user',
            0 // Aguardando aprovação
        ]);
        
        return $this->pdo->lastInsertId();
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
    
    public function requestPasswordReset($email) {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? AND status = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('Email não encontrado.');
        }
        
        $token = generateToken();
        $expiry = date('Y-m-d H:i:s', time() + TOKEN_LIFETIME);
        
        $stmt = $this->pdo->prepare('
            INSERT INTO password_resets (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$user['id'], $token, $expiry]);
        
        // Enviar email (implementar depois)
        return $token;
    }
    
    public function resetPassword($token, $newPassword) {
        $stmt = $this->pdo->prepare('
            SELECT user_id FROM password_resets
            WHERE token = ? AND expires_at > CURRENT_TIMESTAMP
            AND used = 0
        ');
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            throw new Exception('Token inválido ou expirado.');
        }
        
        // Atualizar senha
        $stmt = $this->pdo->prepare('
            UPDATE users SET password = ? WHERE id = ?
        ');
        $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $reset['user_id']
        ]);
        
        // Marcar token como usado
        $stmt = $this->pdo->prepare('
            UPDATE password_resets SET used = 1
            WHERE token = ?
        ');
        $stmt->execute([$token]);
        
        $this->logActivity($reset['user_id'], 'password_reset', 'Senha alterada com sucesso');
    }
    
    private function validateRegistrationData($data) {
        if (empty($data['name']) || strlen($data['name']) < 3) {
            throw new Exception('Nome deve ter pelo menos 3 caracteres.');
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido.');
        }
        
        if (empty($data['password']) || strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            throw new Exception('Senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres.');
        }
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
    
    private function logActivity($userId, $action, $description) {
        $stmt = $this->pdo->prepare('
            INSERT INTO activity_logs (user_id, action, description, ip_address)
            VALUES (?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}