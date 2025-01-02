<?php
require_once '../config/config.php';
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
        <?php require_once '../includes/sidebar.php'; ?>

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
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-cart3"></i> Módulo de Compras
                            </h5>
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-box-seam"></i> Produtos
                                            </h6>
                                            <p class="card-text">Gerencie o cadastro de produtos.</p>
                                            <a href="products/" class="btn btn-primary">
                                                <i class="bi bi-arrow-right"></i> Acessar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-building"></i> Fornecedores
                                            </h6>
                                            <p class="card-text">Gerencie seus fornecedores.</p>
                                            <a href="suppliers/" class="btn btn-primary">
                                                <i class="bi bi-arrow-right"></i> Acessar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-clipboard-check"></i> Pedidos
                                            </h6>
                                            <p class="card-text">Gerencie pedidos de compra.</p>
                                            <a href="purchase-orders/" class="btn btn-primary">
                                                <i class="bi bi-arrow-right"></i> Acessar
                                            </a>
                                        </div>
                                    </div>
                                </div>
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