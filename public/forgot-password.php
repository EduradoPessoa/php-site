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

// Processa a solicitação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !validateToken($_POST['csrf_token'])) {
            throw new Exception('Token de segurança inválido');
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('Email inválido');
        }

        $token = $auth->requestPasswordReset($email);
        
        // TODO: Implementar envio de email
        // Por enquanto, apenas mostra o token (apenas para desenvolvimento)
        if (!isProduction()) {
            $success = "Token gerado (apenas para desenvolvimento): " . $token;
        } else {
            $success = "Se o email existir em nossa base, você receberá as instruções para redefinir sua senha.";
        }

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
                    <h1 class="text-center mb-4">Recuperar Senha</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <div class="form-text">
                                Digite o email associado à sua conta. Você receberá um link para redefinir sua senha.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Enviar Link de Recuperação</button>
                            <a href="login.php" class="btn btn-outline-secondary">Voltar para Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
