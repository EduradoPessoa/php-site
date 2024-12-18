<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Dashboard</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?></h5>
                <p class="card-text">Seu perfil: <?= ucfirst(htmlspecialchars($_SESSION['user_role'])) ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>