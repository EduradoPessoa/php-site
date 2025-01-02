<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = Auth::getInstance();

// Verificar autenticação
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Buscar dados do usuário
$user = $auth->getCurrentUser();

// Buscar estatísticas (apenas para admin)
$stats = [];
if ($auth->hasRole('admin')) {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending_users
        FROM users
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar atividades recentes
    $stmt = $pdo->query("
        SELECT a.*, u.name as user_name
        FROM activity_logs a
        JOIN users u ON u.id = a.user_id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Título da página
$pageTitle = "Dashboard";
require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="user-profile mb-4 p-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-1 me-2"></i>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                        </div>
                    </div>
                </div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <?php if ($auth->hasRole('admin')): ?>
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
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Configurações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-share"></i> Compartilhar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($auth->hasRole('admin')): ?>
            <!-- Admin Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <i class="bi bi-people-fill"></i> Total de Usuários
                            </h5>
                            <h2 class="mt-3 mb-0"><?= $stats['total_users'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-success">
                                <i class="bi bi-person-check-fill"></i> Usuários Ativos
                            </h5>
                            <h2 class="mt-3 mb-0"><?= $stats['active_users'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-warning">
                                <i class="bi bi-person-dash-fill"></i> Aguardando Aprovação
                            </h5>
                            <h2 class="mt-3 mb-0"><?= $stats['pending_users'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity"></i> Atividades Recentes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Descrição</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?= htmlspecialchars($activity['user_name']) ?></td>
                                    <td><?= htmlspecialchars($activity['action']) ?></td>
                                    <td><?= htmlspecialchars($activity['description']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- User Dashboard -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-person-badge"></i> Meu Perfil
                            </h5>
                            <div class="mt-4">
                                <p><strong>Nome:</strong> <?= htmlspecialchars($user['name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                <p><strong>Membro desde:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                            </div>
                            <a href="profile.php" class="btn btn-primary mt-3">
                                <i class="bi bi-pencil"></i> Editar Perfil
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-gear"></i> Configurações Rápidas
                            </h5>
                            <div class="list-group mt-4">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-bell"></i> Notificações
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-shield-lock"></i> Segurança
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <i class="bi bi-palette"></i> Aparência
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>