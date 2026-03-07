<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$totals = [
    "users" => $conn->query("SELECT COUNT(*) AS total FROM tbl_users")->fetch_assoc()['total'],
    "products" => $conn->query("SELECT COUNT(*) AS total FROM tbl_products")->fetch_assoc()['total'],
    "reservations" => $conn->query("SELECT COUNT(*) AS total FROM tbl_reservation")->fetch_assoc()['total'],
    "orders" => $conn->query("SELECT COUNT(*) AS total FROM tbl_orders WHERE status='pending'")->fetch_assoc()['total']
];

echo json_encode($totals);
