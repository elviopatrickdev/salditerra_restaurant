<?php
session_start();
require_once '../config/config.php';

// Verifica se o usuário é admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'msg' => 'Permissão negada']);
    exit;
}

// Verifica se o ID foi passado
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'msg' => 'ID de produto inválido']);
    exit;
}

$product_id = intval($_POST['id']);

// =====================
// Remover do banco de dados
// =====================
$stmt = $conn->prepare("DELETE FROM tbl_products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$success_db = $stmt->affected_rows > 0;
$stmt->close();

// =====================
// Remover do catalog.json
// =====================
$catalog_file = 'catalog.json';
$success_json = false;

if (file_exists($catalog_file)) {
    $catalog_data = json_decode(file_get_contents($catalog_file), true);

    if ($catalog_data !== null) {
        // Filtra produtos removendo o produto deletado
        $new_catalog = array_filter($catalog_data, function ($product) use ($product_id) {
            return ($product['id'] ?? 0) != $product_id;
        });

        // Reindexa o array
        $new_catalog = array_values($new_catalog);

        // Salva de volta no JSON
        $success_json = file_put_contents($catalog_file, json_encode($new_catalog, JSON_PRETTY_PRINT)) !== false;
    }
}

// Retorna JSON para o AJAX
if ($success_db) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Erro ao deletar produto']);
}
exit;
