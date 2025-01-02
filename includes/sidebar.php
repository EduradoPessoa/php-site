<?php
$currentFile = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Obtém o usuário atual
$user = $auth->getCurrentUser();

function isActive($file, $dir = null) {
    global $currentFile, $currentDir;
    if ($dir) {
        return $currentDir === $dir ? 'active' : '';
    }
    return $currentFile === $file ? 'active' : '';
}

function isExpanded($dirs) {
    global $currentDir;
    return in_array($currentDir, $dirs) ? 'show' : '';
}
?>

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        <div class="user-profile mb-4 p-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle fs-1 me-2"></i>
                <div>
                    <h6 class="mb-0"><?= htmlspecialchars($user['name'] ?? 'Usuário') ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></small>
                </div>
            </div>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= isActive('dashboard.php') ?>" href="/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <!-- Módulo de Compras -->
            <li class="nav-item">
                <a class="nav-link <?= in_array($currentDir, ['products', 'suppliers', 'supplier-products', 'purchase-orders', 'invoices']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#comprasSubmenu" 
                   aria-expanded="<?= in_array($currentDir, ['products', 'suppliers', 'supplier-products', 'purchase-orders', 'invoices']) ? 'true' : 'false' ?>">
                    <i class="bi bi-cart3"></i> Compras <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?= isExpanded(['products', 'suppliers', 'supplier-products', 'purchase-orders', 'invoices']) ?>" id="comprasSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('index.php', 'products') ?>" href="/products/">
                                <i class="bi bi-box-seam"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('index.php', 'suppliers') ?>" href="/suppliers/">
                                <i class="bi bi-building"></i> Fornecedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('index.php', 'supplier-products') ?>" href="/supplier-products/">
                                <i class="bi bi-link-45deg"></i> Produtos x Fornecedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('index.php', 'purchase-orders') ?>" href="/purchase-orders/">
                                <i class="bi bi-clipboard-check"></i> Pedidos de Compra
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('index.php', 'invoices') ?>" href="/invoices/">
                                <i class="bi bi-receipt"></i> Notas Fiscais
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <?php if ($auth->hasRole('admin')): ?>
            <li class="nav-item">
                <a class="nav-link <?= isActive('users.php') ?>" href="/users.php">
                    <i class="bi bi-people"></i> Usuários
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?= isActive('settings.php') ?>" href="/settings.php">
                    <i class="bi bi-gear"></i> Configurações
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </li>
        </ul>
    </div>
</nav>
