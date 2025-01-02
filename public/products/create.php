<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

$auth = Auth::getInstance();

// Verificar autenticação
if (!$auth->isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $unit = trim($_POST['unit']);
    $price = str_replace(',', '.', $_POST['price']);
    $min_stock = str_replace(',', '.', $_POST['min_stock']);
    $max_stock = str_replace(',', '.', $_POST['max_stock']);
    $current_stock = str_replace(',', '.', $_POST['current_stock']);
    $status = isset($_POST['status']) ? 1 : 0;

    // Validar campos obrigatórios
    $errors = [];
    if (empty($code)) $errors[] = "O código é obrigatório";
    if (empty($name)) $errors[] = "O nome é obrigatório";
    if (empty($unit)) $errors[] = "A unidade é obrigatória";
    if (!is_numeric($price)) $errors[] = "O preço deve ser um número válido";
    if (!is_numeric($min_stock)) $errors[] = "O estoque mínimo deve ser um número válido";
    if (!is_numeric($max_stock)) $errors[] = "O estoque máximo deve ser um número válido";
    if (!is_numeric($current_stock)) $errors[] = "O estoque atual deve ser um número válido";

    // Verificar se código já existe
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            $errors[] = "Este código já está em uso";
        }
    }

    // Salvar no banco
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (
                    code, name, description, unit, 
                    price, min_stock, max_stock, current_stock, 
                    status, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?,
                    ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ");

            $stmt->execute([
                $code, $name, $description, $unit,
                $price, $min_stock, $max_stock, $current_stock,
                $status
            ]);

            // Registrar log
            $productId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, description)
                VALUES (?, 'create_product', ?)
            ");
            $stmt->execute([
                $auth->getCurrentUser()['id'],
                "Criou o produto: $name (ID: $productId)"
            ]);

            $_SESSION['flash_message'] = "Produto cadastrado com sucesso!";
            $_SESSION['flash_type'] = "success";
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erro ao salvar o produto: " . $e->getMessage();
        }
    }
}

// Título da página
$pageTitle = "Novo Produto";
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Novo Produto</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Erro!</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="create.php" method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="code" class="form-label">Código *</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       value="<?= isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '' ?>" 
                                       required maxlength="20">
                                <div class="invalid-feedback">Campo obrigatório.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                                       required maxlength="100">
                                <div class="invalid-feedback">Campo obrigatório.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="unit" class="form-label">Unidade *</label>
                                <select class="form-select" id="unit" name="unit" required>
                                    <option value="">Selecione...</option>
                                    <option value="UN" <?= isset($_POST['unit']) && $_POST['unit'] === 'UN' ? 'selected' : '' ?>>Unidade</option>
                                    <option value="CX" <?= isset($_POST['unit']) && $_POST['unit'] === 'CX' ? 'selected' : '' ?>>Caixa</option>
                                    <option value="PC" <?= isset($_POST['unit']) && $_POST['unit'] === 'PC' ? 'selected' : '' ?>>Peça</option>
                                    <option value="KG" <?= isset($_POST['unit']) && $_POST['unit'] === 'KG' ? 'selected' : '' ?>>Quilograma</option>
                                    <option value="LT" <?= isset($_POST['unit']) && $_POST['unit'] === 'LT' ? 'selected' : '' ?>>Litro</option>
                                    <option value="MT" <?= isset($_POST['unit']) && $_POST['unit'] === 'MT' ? 'selected' : '' ?>>Metro</option>
                                </select>
                                <div class="invalid-feedback">Campo obrigatório.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="price" class="form-label">Preço *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" id="price" name="price" 
                                           value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0,00' ?>" 
                                           required>
                                </div>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="min_stock" class="form-label">Estoque Mínimo *</label>
                                <input type="text" class="form-control" id="min_stock" name="min_stock" 
                                       value="<?= isset($_POST['min_stock']) ? htmlspecialchars($_POST['min_stock']) : '0,00' ?>" 
                                       required>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="max_stock" class="form-label">Estoque Máximo *</label>
                                <input type="text" class="form-control" id="max_stock" name="max_stock" 
                                       value="<?= isset($_POST['max_stock']) ? htmlspecialchars($_POST['max_stock']) : '0,00' ?>" 
                                       required>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="current_stock" class="form-label">Estoque Atual *</label>
                                <input type="text" class="form-control" id="current_stock" name="current_stock" 
                                       value="<?= isset($_POST['current_stock']) ? htmlspecialchars($_POST['current_stock']) : '0,00' ?>" 
                                       required>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" 
                                       <?= !isset($_POST['status']) || $_POST['status'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status">Produto Ativo</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Validação do formulário
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Formatação de números
function formatNumber(input) {
    let value = input.value.replace(/\D/g, '');
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = value;
}

// Aplicar formatação nos campos numéricos
document.querySelectorAll('input[type="text"]').forEach(input => {
    if (['price', 'min_stock', 'max_stock', 'current_stock'].includes(input.id)) {
        input.addEventListener('input', () => formatNumber(input));
        input.addEventListener('focus', () => {
            input.value = input.value.replace(/\D/g, '');
            input.value = (parseInt(input.value || 0) / 100).toFixed(2).replace('.', ',');
        });
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
