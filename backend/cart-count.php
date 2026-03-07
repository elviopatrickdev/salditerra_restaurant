<?php
// cart-count.php
session_start();
header('Content-Type: application/json');

$cart = $_SESSION['cart'] ?? [];
$count = 0;

// Somando as quantidades de cada produto
foreach ($cart as $item) {
    $count += $item['quantity'];
}

echo json_encode(['count' => $count]);
