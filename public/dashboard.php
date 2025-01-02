<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = Auth::getInstance();

// Verifica autenticação
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Obtém dados do usuário
$user = $auth->getCurrentUser();
if (!$user) {
    $auth->logout();
    header('Location: login.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <?php if ($auth->hasRole('admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/">
                            <i class="bi bi-gear"></i> Administração
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/users.php">
                            <i class="bi bi-people"></i> Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> Meu Perfil
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>

            <!-- Cards de Estatísticas -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Perfil</h5>
                            <p class="card-text">
                                <strong>Nome:</strong> <?= htmlspecialchars($user['name']) ?><br>
                                <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?><br>
                                <strong>Função:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?><br>
                                <strong>Desde:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </p>
                            <a href="profile.php" class="btn btn-primary">Editar Perfil</a>
                        </div>
                    </div>
                </div>

                <?php if ($auth->hasRole('admin')): ?>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <h3 class="card-title">Usuários</h3>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                                    $total = $stmt->fetch()['total'];
                                    ?>
                                    <p class="display-4"><?= $total ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <h3 class="card-title">Pendentes</h3>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 0");
                                    $pending = $stmt->fetch()['total'];
                                    ?>
                                    <p class="display-4"><?= $pending ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <h3 class="card-title">Ativos</h3>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 1");
                                    $active = $stmt->fetch()['total'];
                                    ?>
                                    <p class="display-4"><?= $active ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tabela de Atividades -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Atividades Recentes</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Ação</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT * FROM activity_logs 
                                    WHERE user_id = ? 
                                    ORDER BY created_at DESC 
                                    LIMIT 5
                                ");
                                $stmt->execute([$user['id']]);
                                while ($log = $stmt->fetch()): 
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td><?= htmlspecialchars($log['description']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>