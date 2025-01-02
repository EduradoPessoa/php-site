<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

$auth = Auth::getInstance();

// Verificar autenticação
if (!$auth->isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

// Buscar produtos
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        COALESCE(
            (SELECT COUNT(*) FROM supplier_products sp WHERE sp.product_id = p.id),
            0
        ) as supplier_count
    FROM products p
    ORDER BY p.name ASC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Título da página
$pageTitle = "Produtos";
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Produtos</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Novo Produto
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php 
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
                ?>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Unidade</th>
                                    <th>Preço</th>
                                    <th>Estoque</th>
                                    <th>Fornecedores</th>
                                    <th>Status</th>
                                    <th width="100">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                Nenhum produto cadastrado
                                            </div>
                                            <a href="create.php" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-lg"></i> Novo Produto
                                            </a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['code']) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($product['name']) ?></div>
                                                <?php if ($product['description']): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($product['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($product['unit']) ?></td>
                                            <td>R$ <?= number_format($product['price'], 2, ',', '.') ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?= number_format($product['current_stock'], 2, ',', '.') ?>
                                                    <?php if ($product['current_stock'] <= $product['min_stock']): ?>
                                                        <span class="badge bg-danger ms-2">Baixo</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($product['supplier_count'] > 0): ?>
                                                    <a href="../supplier-products/?product_id=<?= $product['id'] ?>" class="text-decoration-none">
                                                        <?= $product['supplier_count'] ?> fornecedor(es)
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Nenhum</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['status']): ?>
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
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o produto <strong id="productName"></strong>?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="delete.php" method="POST" class="d-inline">
                    <input type="hidden" name="id" id="productId">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('productId').value = id;
    document.getElementById('productName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>
