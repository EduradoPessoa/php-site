<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = Auth::getInstance();
$error = '';

// Redireciona usuários já logados
if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !validateToken($_POST['csrf_token'])) {
            throw new Exception('Token de segurança inválido');
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email) {
            throw new Exception('Email inválido');
        }

        if (empty($password)) {
            throw new Exception('Senha é obrigatória');
        }

        $auth->login($email, $password);
        header('Location: dashboard.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Gera token CSRF
$_SESSION['csrf_token'] = generateToken();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Login</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Lembrar-me</label>
                                </div>
                                <a href="forgot-password.php">Esqueceu a senha?</a>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Não tem uma conta? <a href="register.php">Registre-se</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>