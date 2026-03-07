<?php
require "../config/config.php"; // conexão ao MySQL

// Lê o JSON
$json = file_get_contents("catalog.json");
$data = json_decode($json, true);

// Verificação
if (!$data || !isset($data['products'])) {
    die("Erro ao ler o JSON ou produtos não encontrados.");
}

// Preparar o INSERT no mySQL
$stmt = $conn->prepare("
    INSERT INTO tbl_products (name, description, price, image, stock)
    VALUES (?, ?, ?, ?, ?)
");

// Loop pelos produtos
foreach ($data['products'] as $p) {

    $name = $p["name"];
    $description = trim($p["description"]);
    $price = floatval(str_replace(',', '.', $p["price"]));
    $image = $p["image"];
    $stock = $p["stock"];

    $stmt->bind_param(
        "ssdsi",
        $name,
        $description,
        $price,
        $image,
        $stock
    );

    $stmt->execute();
}

echo "Importação concluída com sucesso!";
