<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Redireciona usuários já logados para o dashboard
if (isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12 text-center">
        <h1 class="mb-4">Bem-vindo ao <?= SITE_NAME ?></h1>
        <p class="lead">Sistema de gerenciamento empresarial</p>
        <div class="mb-5">
            <a href="login.php" class="btn btn-primary btn-lg">Acessar o Sistema</a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Área do Cliente</h5>
                <p class="card-text">Acesse sua área exclusiva.</p>
                <a href="login.php" class="btn btn-outline-primary">Entrar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Fornecedores</h5>
                <p class="card-text">Portal para fornecedores.</p>
                <a href="login.php" class="btn btn-outline-primary">Entrar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Funcionários</h5>
                <p class="card-text">Área restrita funcionários.</p>
                <a href="login.php" class="btn btn-outline-primary">Entrar</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>