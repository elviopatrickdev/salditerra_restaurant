<?php
session_start();
header('Content-Type: application/json');

// Se não estiver logado, bloqueia tudo
if (!isset($_SESSION['user_type'])) {
    echo json_encode([
        'status' => 'redirect',
        'message' => 'Login required',
        'redirect' => 'login-register.php'
    ]);
    exit;
}

// ------------------------
// 1️⃣ Receber dados do POST
// ------------------------
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$product_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// ------------------------
// 2️⃣ Carregar catálogo
// ------------------------
$catalog_file = "catalog.json"; // caminho absoluto

if (!file_exists($catalog_file)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Catalog not found'
    ]);
    exit;
}

$catalog_content = file_get_contents($catalog_file);
$catalog = json_decode($catalog_content, true);

// Verifica se JSON está correto
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Catalog JSON error: ' . json_last_error_msg()
    ]);
    exit;
}

if (!isset($catalog) || !is_array($catalog)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid catalog'
    ]);
    exit;
}

// ----------------------------
// 3️⃣ Procurar produto pelo ID
// ----------------------------
$product = null;
foreach ($catalog as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found'
    ]);
    exit;
}

// ------------------------
// 4️⃣ Inicializar carrinho
// ------------------------
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ----------------------------------
// 5️⃣ Adicionar ou atualizar produto
// ----------------------------------
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'] ?? 'default.png',
        'quantity' => $quantity
    ];
}

// ------------------------
// 6️⃣ Retornar resposta JSON
// ------------------------
echo json_encode([
    'status' => 'success',
    'message' => 'Product added to cart',
    'cart' => $_SESSION['cart']
]);
exit;
