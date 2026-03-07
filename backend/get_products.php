<?php
header('Content-Type: application/json; charset=utf-8');

require "../config/config.php"; // conexão ao MySQL
$conn->set_charset("utf8mb4");

$sql = "SELECT id, name, description, price, stock, image FROM tbl_products";
$result = $conn->query($sql);

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'price' => $row['price'],
        'image' => $row['image'],
        'stock' => intval($row['stock'])
    ];
}

// Atualizar catalog.json
$catalogFile = "catalog.json";
file_put_contents($catalogFile, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Retornar JSON para o JS
$conn->close();
echo json_encode($products);
