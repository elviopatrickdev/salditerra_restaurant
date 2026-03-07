<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['product_id'], $_POST['quantity'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$product_id = (int) $_POST['product_id'];
$quantity = (int) $_POST['quantity'];

if ($quantity < 1) {
    $quantity = 1;
}

if (isset($_SESSION['cart'][$product_id])) {
    // Atualiza quantidade
    $_SESSION['cart'][$product_id]['quantity'] = $quantity;

    // Calcula subtotal do item
    $item_subtotal = $_SESSION['cart'][$product_id]['price'] * $quantity;

    // Calcula total geral
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    echo json_encode([
        'status' => 'success',
        'quantity' => $quantity,
        'item_subtotal' => number_format($item_subtotal, 2),
        'total' => number_format($total, 2)
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
