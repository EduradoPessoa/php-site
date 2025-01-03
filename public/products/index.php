<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Verifica autenticação
$auth->requireLogin();

$title = "Produtos";
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1><?= $title ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">Novo Produto</a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Unidade</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Almoxarifado</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->prepare("SELECT products.*, units.name as unit_name FROM products LEFT JOIN units ON products.unit_id = units.id ORDER BY products.code");
                            $stmt->execute();

                            while ($product = $stmt->fetch()): ?>
                                <tr>
                                    <td><?= $product['code'] ?? '' ?></td>
                                    <td><?= $product['name'] ?? '' ?></td>
                                    <td><?= $product['unit_name'] ?? 'Não definido' ?></td>
                                    <td>R$ <?= number_format($product['price'] ?? 0, 2, ',', '.') ?></td>
                                    <td>0,00</td>
                                    <td>
                                        <span class="text-muted">Não definido</span>
                                    </td>
                                    <td>
                                        <?php if (($product['active'] ?? 1) == 1): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $product['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } catch (PDOException $e) {
                            file_put_contents(__DIR__ . '/error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
                            echo '<div class="alert alert-danger" role="alert">Erro ao carregar produtos: ' . $e->getMessage() . '</div>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
