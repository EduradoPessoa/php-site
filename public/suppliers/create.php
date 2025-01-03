<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Verificar autenticação
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos obrigatórios
        $requiredFields = ['code', 'name', 'supplier_type', 'country'];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst($field) . " é obrigatório.";
            }
        }

        // Validar código único
        if (!empty($_POST['code'])) {
            $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE code = ?");
            $stmt->execute([$_POST['code']]);
            if ($stmt->fetch()) {
                $errors[] = "Este código já está em uso.";
            }
        }

        // Se não houver erros, inserir
        if (empty($errors)) {
            $stmt = $pdo->prepare("
                INSERT INTO suppliers (
                    code, name, legal_name, tax_id, supplier_type,
                    address, city, state, country, postal_code,
                    phone, email, website, contact_name,
                    contact_phone, contact_email, bank_name,
                    bank_account, bank_branch, payment_terms,
                    shipping_terms, notes, status, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, CURRENT_TIMESTAMP
                )
            ");

            $stmt->execute([
                $_POST['code'],
                $_POST['name'],
                $_POST['legal_name'],
                $_POST['tax_id'],
                $_POST['supplier_type'],
                $_POST['address'],
                $_POST['city'],
                $_POST['state'],
                $_POST['country'],
                $_POST['postal_code'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['website'],
                $_POST['contact_name'],
                $_POST['contact_phone'],
                $_POST['contact_email'],
                $_POST['bank_name'],
                $_POST['bank_account'],
                $_POST['bank_branch'],
                $_POST['payment_terms'],
                $_POST['shipping_terms'],
                $_POST['notes'],
                isset($_POST['status']) ? 1 : 0
            ]);

            // Registrar log
            $auth->logActivity($auth->getCurrentUser()['id'], 'create_supplier', "Fornecedor {$_POST['name']} criado");

            $_SESSION['success'] = "Fornecedor cadastrado com sucesso!";
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar fornecedor: " . $e->getMessage());
        $errors[] = "Erro ao cadastrar fornecedor: " . $e->getMessage();
    }
}

// Título da página
$pageTitle = "Novo Fornecedor";
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Novo Fornecedor</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <!-- Informações Básicas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações Básicas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="code" class="form-label">Código *</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       value="<?= isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '' ?>" 
                                       required>
                            </div>
                            <div class="col-md-5">
                                <label for="name" class="form-label">Nome Fantasia *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                                       required>
                            </div>
                            <div class="col-md-5">
                                <label for="legal_name" class="form-label">Razão Social</label>
                                <input type="text" class="form-control" id="legal_name" name="legal_name" 
                                       value="<?= isset($_POST['legal_name']) ? htmlspecialchars($_POST['legal_name']) : '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="tax_id" class="form-label">CNPJ/CPF</label>
                                <input type="text" class="form-control" id="tax_id" name="tax_id" 
                                       value="<?= isset($_POST['tax_id']) ? htmlspecialchars($_POST['tax_id']) : '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="supplier_type" class="form-label">Tipo *</label>
                                <select class="form-select" id="supplier_type" name="supplier_type" required>
                                    <option value="">Selecione...</option>
                                    <option value="national" <?= isset($_POST['supplier_type']) && $_POST['supplier_type'] == 'national' ? 'selected' : '' ?>>
                                        Nacional
                                    </option>
                                    <option value="international" <?= isset($_POST['supplier_type']) && $_POST['supplier_type'] == 'international' ? 'selected' : '' ?>>
                                        Internacional
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" 
                                           <?= !isset($_POST['status']) || $_POST['status'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status">Ativo</label>
                                </div>
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
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="address" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="state" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?= isset($_POST['state']) ? htmlspecialchars($_POST['state']) : '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="country" class="form-label">País *</label>
                                <input type="text" class="form-control" id="country" name="country" 
                                       value="<?= isset($_POST['country']) ? htmlspecialchars($_POST['country']) : '' ?>" 
                                       required>
                            </div>
                            <div class="col-md-3">
                                <label for="postal_code" class="form-label">CEP/Código Postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                       value="<?= isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : '' ?>">
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
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?= isset($_POST['website']) ? htmlspecialchars($_POST['website']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="contact_name" class="form-label">Nome do Contato</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name" 
                                       value="<?= isset($_POST['contact_name']) ? htmlspecialchars($_POST['contact_name']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="contact_phone" class="form-label">Telefone do Contato</label>
                                <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                       value="<?= isset($_POST['contact_phone']) ? htmlspecialchars($_POST['contact_phone']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="contact_email" class="form-label">E-mail do Contato</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                       value="<?= isset($_POST['contact_email']) ? htmlspecialchars($_POST['contact_email']) : '' ?>">
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
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="bank_name" class="form-label">Banco</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                       value="<?= isset($_POST['bank_name']) ? htmlspecialchars($_POST['bank_name']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="bank_branch" class="form-label">Agência</label>
                                <input type="text" class="form-control" id="bank_branch" name="bank_branch" 
                                       value="<?= isset($_POST['bank_branch']) ? htmlspecialchars($_POST['bank_branch']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="bank_account" class="form-label">Conta</label>
                                <input type="text" class="form-control" id="bank_account" name="bank_account" 
                                       value="<?= isset($_POST['bank_account']) ? htmlspecialchars($_POST['bank_account']) : '' ?>">
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
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="payment_terms" class="form-label">Condições de Pagamento</label>
                                <textarea class="form-control" id="payment_terms" name="payment_terms" rows="3"><?= isset($_POST['payment_terms']) ? htmlspecialchars($_POST['payment_terms']) : '' ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_terms" class="form-label">Condições de Entrega</label>
                                <textarea class="form-control" id="shipping_terms" name="shipping_terms" rows="3"><?= isset($_POST['shipping_terms']) ? htmlspecialchars($_POST['shipping_terms']) : '' ?></textarea>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Observações</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                    <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar
                    </button>
                </div>
            </form>
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

// Máscara para CNPJ/CPF
document.getElementById('tax_id').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    e.target.value = value;
});

// Máscara para CEP
document.getElementById('postal_code').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (document.getElementById('supplier_type').value === 'national') {
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
    }
    e.target.value = value;
});

// Máscara para telefone
function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (document.getElementById('supplier_type').value === 'national') {
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
    }
    input.value = value;
}

document.getElementById('phone').addEventListener('input', function(e) {
    maskPhone(e.target);
});

document.getElementById('contact_phone').addEventListener('input', function(e) {
    maskPhone(e.target);
});

// Atualizar máscaras quando o tipo de fornecedor mudar
document.getElementById('supplier_type').addEventListener('change', function(e) {
    const taxId = document.getElementById('tax_id');
    const postalCode = document.getElementById('postal_code');
    const phone = document.getElementById('phone');
    const contactPhone = document.getElementById('contact_phone');

    // Limpar valores
    taxId.value = '';
    postalCode.value = '';
    phone.value = '';
    contactPhone.value = '';

    // Atualizar placeholders
    if (e.target.value === 'national') {
        taxId.placeholder = 'CNPJ/CPF';
        postalCode.placeholder = 'CEP';
    } else {
        taxId.placeholder = 'Tax ID';
        postalCode.placeholder = 'Postal Code';
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
