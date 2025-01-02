<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

$auth = Auth::getInstance();

// Verificar autenticação
if (!$auth->isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

// Verificar ID
if (!isset($_GET['id'])) {
    $_SESSION['flash_message'] = "Produto não encontrado";
    $_SESSION['flash_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Buscar produto
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['flash_message'] = "Produto não encontrado";
    $_SESSION['flash_type'] = "danger";
    header('Location: index.php');
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
        $stmt = $pdo->prepare("SELECT id FROM products WHERE code = ? AND id != ?");
        $stmt->execute([$code, $product['id']]);
        if ($stmt->fetch()) {
            $errors[] = "Este código já está em uso";
        }
    }

    // Salvar no banco
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE products SET 
                    code = ?,
                    name = ?,
                    description = ?,
                    unit = ?,
                    price = ?,
                    min_stock = ?,
                    max_stock = ?,
                    current_stock = ?,
                    status = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");

            $stmt->execute([
                $code,
                $name,
                $description,
                $unit,
                $price,
                $min_stock,
                $max_stock,
                $current_stock,
                $status,
                $product['id']
            ]);

            // Registrar log
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, description)
                VALUES (?, 'update_product', ?)
            ");
            $stmt->execute([
                $auth->getCurrentUser()['id'],
                "Atualizou o produto: $name (ID: {$product['id']})"
            ]);

            $_SESSION['flash_message'] = "Produto atualizado com sucesso!";
            $_SESSION['flash_type'] = "success";
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erro ao atualizar o produto: " . $e->getMessage();
        }
    }
}

// Título da página
$pageTitle = "Editar Produto";
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Editar Produto</h1>
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
                    <form action="edit.php?id=<?= $product['id'] ?>" method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="code" class="form-label">Código *</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       value="<?= isset($_POST['code']) ? htmlspecialchars($_POST['code']) : htmlspecialchars($product['code']) ?>" 
                                       required maxlength="20">
                                <div class="invalid-feedback">Campo obrigatório.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($product['name']) ?>" 
                                       required maxlength="100">
                                <div class="invalid-feedback">Campo obrigatório.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="unit" class="form-label">Unidade *</label>
                                <select class="form-select" id="unit" name="unit" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $units = ['UN' => 'Unidade', 'CX' => 'Caixa', 'PC' => 'Peça', 
                                            'KG' => 'Quilograma', 'LT' => 'Litro', 'MT' => 'Metro'];
                                    $selectedUnit = isset($_POST['unit']) ? $_POST['unit'] : $product['unit'];
                                    foreach ($units as $value => $label):
                                    ?>
                                        <option value="<?= $value ?>" <?= $selectedUnit === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Campo obrigatório.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($product['description']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="price" class="form-label">Preço *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" id="price" name="price" 
                                           value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : number_format($product['price'], 2, ',', '.') ?>" 
                                           required>
                                </div>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="min_stock" class="form-label">Estoque Mínimo *</label>
                                <input type="text" class="form-control" id="min_stock" name="min_stock" 
                                       value="<?= isset($_POST['min_stock']) ? htmlspecialchars($_POST['min_stock']) : number_format($product['min_stock'], 2, ',', '.') ?>" 
                                       required>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="max_stock" class="form-label">Estoque Máximo *</label>
                                <input type="text" class="form-control" id="max_stock" name="max_stock" 
                                       value="<?= isset($_POST['max_stock']) ? htmlspecialchars($_POST['max_stock']) : number_format($product['max_stock'], 2, ',', '.') ?>" 
                                       required>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="current_stock" class="form-label">Estoque Atual *</label>
                                <input type="text" class="form-control" id="current_stock" name="current_stock" 
                                       value="<?= isset($_POST['current_stock']) ? htmlspecialchars($_POST['current_stock']) : number_format($product['current_stock'], 2, ',', '.') ?>" 
                                       required>
                                <div class="invalid-feedback">Informe um valor válido.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" 
                                       <?= (isset($_POST['status']) ? $_POST['status'] : $product['status']) ? 'checked' : '' ?>>
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
