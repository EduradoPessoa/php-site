<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Verificar autenticação
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

// Buscar fornecedores
$query = "SELECT * FROM suppliers ORDER BY name";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT * FROM suppliers WHERE 
              name LIKE :search OR 
              code LIKE :search OR 
              legal_name LIKE :search OR 
              tax_id LIKE :search 
              ORDER BY name";
}

try {
    $stmt = $pdo->prepare($query);
    if (isset($search)) {
        $searchTerm = "%{$search}%";
        $stmt->bindParam(':search', $searchTerm);
    }
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar fornecedores: " . $e->getMessage());
    $error = "Erro ao buscar fornecedores. Por favor, tente novamente.";
}

// Título da página
$pageTitle = "Fornecedores";
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Fornecedores</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Novo Fornecedor
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Search Form -->
            <form class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Buscar por código, nome, razão social ou CNPJ/CPF" 
                               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <?php if (isset($_GET['search'])): ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <!-- Suppliers Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Razão Social</th>
                            <th>CNPJ/CPF</th>
                            <th>Tipo</th>
                            <th>País</th>
                            <th>Status</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        Nenhum fornecedor encontrado.
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?= htmlspecialchars($supplier['code']) ?></td>
                                    <td><?= htmlspecialchars($supplier['name']) ?></td>
                                    <td><?= htmlspecialchars($supplier['legal_name']) ?></td>
                                    <td><?= htmlspecialchars($supplier['tax_id']) ?></td>
                                    <td>
                                        <?php if ($supplier['supplier_type'] == 'national'): ?>
                                            <span class="badge bg-primary">Nacional</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Internacional</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($supplier['country']) ?></td>
                                    <td>
                                        <?php if ($supplier['status']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit.php?id=<?= $supplier['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="view.php?id=<?= $supplier['id'] ?>" 
                                               class="btn btn-outline-info" 
                                               title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    title="Excluir"
                                                    onclick="deleteSupplier(<?= $supplier['id'] ?>, '<?= htmlspecialchars($supplier['name']) ?>')">
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
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o fornecedor <strong id="supplierName"></strong>?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" action="delete.php" method="POST" class="d-inline">
                    <input type="hidden" name="id" id="supplierId">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteSupplier(id, name) {
    document.getElementById('supplierId').value = id;
    document.getElementById('supplierName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>
