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
if (!isset($_POST['id'])) {
    header('Location: index.php');
    exit;
}

try {
    // Verificar se fornecedor existe
    $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$supplier) {
        $_SESSION['error'] = "Fornecedor não encontrado.";
        header('Location: index.php');
        exit;
    }

    // Verificar se fornecedor está em uso
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM supplier_products 
        WHERE supplier_id = ?
    ");
    $stmt->execute([$_POST['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        $_SESSION['error'] = "Este fornecedor não pode ser excluído pois possui produtos vinculados.";
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM purchase_orders 
        WHERE supplier_id = ?
    ");
    $stmt->execute([$_POST['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        $_SESSION['error'] = "Este fornecedor não pode ser excluído pois possui pedidos de compra.";
        header('Location: index.php');
        exit;
    }

    // Excluir fornecedor
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    // Registrar log
    $auth->logActivity('delete_supplier', "Fornecedor {$supplier['name']} excluído");

    $_SESSION['success'] = "Fornecedor excluído com sucesso!";
} catch (PDOException $e) {
    error_log("Erro ao excluir fornecedor: " . $e->getMessage());
    $_SESSION['error'] = "Erro ao excluir fornecedor. Por favor, tente novamente.";
}

header('Location: index.php');
