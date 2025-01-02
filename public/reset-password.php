<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = Auth::getInstance();
$error = '';
$success = '';

// Redireciona usuários já logados
if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

// Verifica se tem token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: login.php');
    exit;
}

// Processa a redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !validateToken($_POST['csrf_token'])) {
            throw new Exception('Token de segurança inválido');
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            throw new Exception('A senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres');
        }

        if ($password !== $confirm) {
            throw new Exception('As senhas não conferem');
        }

        $auth->resetPassword($token, $password);
        $success = "Senha alterada com sucesso! Você já pode fazer login.";

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
                    <h1 class="text-center mb-4">Redefinir Senha</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                            <div class="mt-3">
                                <a href="login.php" class="btn btn-primary">Ir para Login</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="post" action="">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                                <div class="form-text">
                                    A senha deve ter pelo menos <?= PASSWORD_MIN_LENGTH ?> caracteres.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Redefinir Senha</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
