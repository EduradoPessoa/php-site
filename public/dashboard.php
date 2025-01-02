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

require_once '../includes/header.php';
?>

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

    <div class="col-md-8">
        <div class="row dashboard-stats">
            <?php if ($auth->hasRole('admin')): ?>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3>Usuários</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                    $total = $stmt->fetch()['total'];
                    ?>
                    <p><?= $total ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3>Pendentes</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 0");
                    $pending = $stmt->fetch()['total'];
                    ?>
                    <p><?= $pending ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3>Ativos</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 1");
                    $active = $stmt->fetch()['total'];
                    ?>
                    <p><?= $active ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Atividades Recentes</h5>
                <div class="table-responsive">
                    <table class="table">
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>