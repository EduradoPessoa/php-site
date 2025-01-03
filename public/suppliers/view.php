<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Verificar autenticação
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

// Verificar se ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Buscar fornecedor
try {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$supplier) {
        $_SESSION['error'] = "Fornecedor não encontrado.";
        header('Location: index.php');
        exit;
    }

    // Buscar produtos do fornecedor
    $stmt = $pdo->prepare("
        SELECT sp.*, p.name as product_name, p.code as product_code
        FROM supplier_products sp
        JOIN products p ON p.id = sp.product_id
        WHERE sp.supplier_id = ?
        ORDER BY p.name
    ");
    $stmt->execute([$_GET['id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar últimos pedidos
    $stmt = $pdo->prepare("
        SELECT po.*, u.name as user_name
        FROM purchase_orders po
        JOIN users u ON u.id = po.created_by
        WHERE po.supplier_id = ?
        ORDER BY po.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$_GET['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar fornecedor: " . $e->getMessage());
    $_SESSION['error'] = "Erro ao buscar fornecedor. Por favor, tente novamente.";
    header('Location: index.php');
    exit;
}

// Título da página
$pageTitle = "Detalhes do Fornecedor";
require_once '../../includes/header.php';

// Formatar data
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Formatar valor
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalhes do Fornecedor</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <!-- Informações Básicas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações Básicas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Código</label>
                            <p><?= htmlspecialchars($supplier['code']) ?></p>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Nome Fantasia</label>
                            <p><?= htmlspecialchars($supplier['name']) ?></p>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Razão Social</label>
                            <p><?= htmlspecialchars($supplier['legal_name']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">CNPJ/CPF</label>
                            <p><?= htmlspecialchars($supplier['tax_id']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo</label>
                            <p>
                                <?php if ($supplier['supplier_type'] == 'national'): ?>
                                    <span class="badge bg-primary">Nacional</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Internacional</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <p>
                                <?php if ($supplier['status']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inativo</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Endereço</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Endereço</label>
                            <p><?= htmlspecialchars($supplier['address']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Cidade</label>
                            <p><?= htmlspecialchars($supplier['city']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Estado</label>
                            <p><?= htmlspecialchars($supplier['state']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">País</label>
                            <p><?= htmlspecialchars($supplier['country']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">CEP/Código Postal</label>
                            <p><?= htmlspecialchars($supplier['postal_code']) ?: '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Contato</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Telefone</label>
                            <p><?= htmlspecialchars($supplier['phone']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">E-mail</label>
                            <p>
                                <?php if ($supplier['email']): ?>
                                    <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>">
                                        <?= htmlspecialchars($supplier['email']) ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Website</label>
                            <p>
                                <?php if ($supplier['website']): ?>
                                    <a href="<?= htmlspecialchars($supplier['website']) ?>" target="_blank">
                                        <?= htmlspecialchars($supplier['website']) ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nome do Contato</label>
                            <p><?= htmlspecialchars($supplier['contact_name']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Telefone do Contato</label>
                            <p><?= htmlspecialchars($supplier['contact_phone']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">E-mail do Contato</label>
                            <p>
                                <?php if ($supplier['contact_email']): ?>
                                    <a href="mailto:<?= htmlspecialchars($supplier['contact_email']) ?>">
                                        <?= htmlspecialchars($supplier['contact_email']) ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Bancárias -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações Bancárias</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Banco</label>
                            <p><?= htmlspecialchars($supplier['bank_name']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Agência</label>
                            <p><?= htmlspecialchars($supplier['bank_branch']) ?: '-' ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Conta</label>
                            <p><?= htmlspecialchars($supplier['bank_account']) ?: '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Termos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Termos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Condições de Pagamento</label>
                            <p><?= nl2br(htmlspecialchars($supplier['payment_terms'])) ?: '-' ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Condições de Entrega</label>
                            <p><?= nl2br(htmlspecialchars($supplier['shipping_terms'])) ?: '-' ?></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Observações</label>
                            <p><?= nl2br(htmlspecialchars($supplier['notes'])) ?: '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produtos -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Produtos</h5>
                    <a href="../products/create.php?supplier_id=<?= $supplier['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Novo Produto
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            Nenhum produto cadastrado para este fornecedor.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nome</th>
                                        <th>Preço</th>
                                        <th>Última Compra</th>
                                        <th>Valor Última Compra</th>
                                        <th width="100">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['product_code']) ?></td>
                                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                                            <td><?= formatMoney($product['price']) ?></td>
                                            <td><?= $product['last_purchase_date'] ? formatDate($product['last_purchase_date']) : '-' ?></td>
                                            <td><?= $product['last_purchase_price'] ? formatMoney($product['last_purchase_price']) : '-' ?></td>
                                            <td>
                                                <a href="../products/edit.php?id=<?= $product['product_id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="../products/view.php?id=<?= $product['product_id'] ?>" 
                                                   class="btn btn-outline-info btn-sm" 
                                                   title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Últimos Pedidos -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimos Pedidos</h5>
                    <a href="../purchase-orders/create.php?supplier_id=<?= $supplier['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Novo Pedido
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            Nenhum pedido encontrado para este fornecedor.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Data</th>
                                        <th>Valor Total</th>
                                        <th>Status</th>
                                        <th>Criado por</th>
                                        <th width="100">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($order['number']) ?></td>
                                            <td><?= formatDate($order['created_at']) ?></td>
                                            <td><?= formatMoney($order['total_amount']) ?></td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'draft' => 'bg-secondary',
                                                    'pending' => 'bg-warning text-dark',
                                                    'approved' => 'bg-primary',
                                                    'rejected' => 'bg-danger',
                                                    'cancelled' => 'bg-danger',
                                                    'completed' => 'bg-success'
                                                ];
                                                $statusLabels = [
                                                    'draft' => 'Rascunho',
                                                    'pending' => 'Pendente',
                                                    'approved' => 'Aprovado',
                                                    'rejected' => 'Rejeitado',
                                                    'cancelled' => 'Cancelado',
                                                    'completed' => 'Concluído'
                                                ];
                                                ?>
                                                <span class="badge <?= $statusClasses[$order['status']] ?>">
                                                    <?= $statusLabels[$order['status']] ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($order['user_name']) ?></td>
                                            <td>
                                                <a href="../purchase-orders/edit.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="../purchase-orders/view.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-outline-info btn-sm" 
                                                   title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($orders) >= 5): ?>
                            <div class="text-center mt-3">
                                <a href="../purchase-orders/index.php?supplier_id=<?= $supplier['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    Ver todos os pedidos
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadados -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Metadados</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Criado em</label>
                            <p><?= formatDate($supplier['created_at']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Última atualização</label>
                            <p><?= $supplier['updated_at'] ? formatDate($supplier['updated_at']) : '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
