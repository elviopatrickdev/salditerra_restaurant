<?php
// remove_from_cart.php
session_start();
header('Content-Type: application/json');

// Verifica se existe ID do produto
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$id = (int)$_GET['id'];

// Verifica se o carrinho existe e se o item está no carrinho
if (isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
    echo json_encode([
        'status' => 'success',
        'product_id' => $id
    ]);
    exit;
}

// Caso o produto não exista no carrinho
echo json_encode(['status' => 'error']);