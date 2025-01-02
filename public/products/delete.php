<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

$auth = Auth::getInstance();

// Verificar autenticação
if (!$auth->isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Verificar ID
if (!isset($_POST['id'])) {
    $_SESSION['flash_message'] = "Produto não encontrado";
    $_SESSION['flash_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Verificar se produto existe
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Produto não encontrado");
    }

    // Verificar se produto está em uso
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM supplier_products 
        WHERE product_id = ?
    ");
    $stmt->execute([$_POST['id']]);
    $count = $stmt->fetch()['count'];

    if ($count > 0) {
        throw new Exception("Este produto não pode ser excluído pois está vinculado a fornecedores");
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM purchase_order_items 
        WHERE product_id = ?
    ");
    $stmt->execute([$_POST['id']]);
    $count = $stmt->fetch()['count'];

    if ($count > 0) {
        throw new Exception("Este produto não pode ser excluído pois está vinculado a pedidos de compra");
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM invoice_items 
        WHERE product_id = ?
    ");
    $stmt->execute([$_POST['id']]);
    $count = $stmt->fetch()['count'];

    if ($count > 0) {
        throw new Exception("Este produto não pode ser excluído pois está vinculado a notas fiscais");
    }

    // Excluir produto
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    // Registrar log
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description)
        VALUES (?, 'delete_product', ?)
    ");
    $stmt->execute([
        $auth->getCurrentUser()['id'],
        "Excluiu o produto: {$product['name']} (ID: {$product['id']})"
    ]);

    $_SESSION['flash_message'] = "Produto excluído com sucesso!";
    $_SESSION['flash_type'] = "success";
} catch (Exception $e) {
    $_SESSION['flash_message'] = $e->getMessage();
    $_SESSION['flash_type'] = "danger";
}

header('Location: index.php');
exit;
