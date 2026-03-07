<?php
session_start();
require_once '../config/config.php';

// Apenas admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login-register.php');
    exit;
}

// Verifica se ID foi passado
if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$id = intval($_GET['id']);

// Busca dados do produto 
$stmt = $conn->prepare("SELECT * FROM tbl_products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: admin.php');
    exit;
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? 0;

    if ($name === '' || $price === '') {
        die('Name and price are required.');
    }

    $price = str_replace(',', '.', $price);
    if (!is_numeric($price)) {
        die('Invalid price.');
    }

    $price = floatval($price);
    $stock = intval($stock);

    // Mantém imagem atual se não enviar nova
    $imagePath = $product['image'] ?? '';

    // Cria pasta uploads se não existir
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    // Upload imagem principal
    if (!empty($_FILES['image']['name'])) {

        $file = $_FILES['image'];
        $file_name = time() . '_' . basename($file['name']);
        $target_file = "uploads/" . $file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $imagePath = $target_file;
            }
        }
    }

    // Atualiza banco de dados
    $stmt = $conn->prepare("UPDATE tbl_products 
        SET name=?, description=?, price=?, stock=?, image=? 
        WHERE id=?");

    $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $imagePath, $id);
    $stmt->execute();
    $stmt->close();

    // Atualiza catalog.json
    $catalogFile = __DIR__ . '/catalog.json';

    if (file_exists($catalogFile)) {

        $catalog = json_decode(file_get_contents($catalogFile), true);

        foreach ($catalog as $key => $item) {

            if ($item['id'] == $id) {

                $catalog[$key]['name'] = $name;
                $catalog[$key]['description'] = $description;
                $catalog[$key]['price'] = number_format($price, 2, '.', '');
                $catalog[$key]['image'] = $imagePath;
                $catalog[$key]['stock'] = (string)$stock;

                break;
            }
        }

        file_put_contents(
            $catalogFile,
            json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    header('Location: admin.php');
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>

    <!-- Meta Tags -->

    <meta name="description"
        content="Salditerra Restaurant in Abuja, Nigeria, offers authentic Cape Verdean cuisine with traditional dishes made from fresh ingredients and rich island flavors.">
    <meta name="keywords"
        content="Salditerra Restaurant Abuja, Cape Verdean food, Cape Verdean restaurant in Abuja, traditional Cape Verde cuisine, African cuisine Abuja, cachupa, Cape Verde food Nigeria">
    <meta name="category" content="Restaurant / Cape Verdean Cuisine">
    <meta name="author" content="Elvio Patrick">

    <!-- Custom Style Links -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;600;700&family=Noto+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body::-webkit-scrollbar {
            width: 12px;
        }

        body::-webkit-scrollbar-track {
            background: #1B2428;
        }

        body::-webkit-scrollbar-thumb {
            background-color: darkgoldenrod;
            border-radius: 10px;
            border: 2px solid #151D21;
        }

        body::-webkit-scrollbar-thumb:hover {
            background-color: darkgoldenrod;
        }

        body {
            background-image: url('assets/pattern-food.png');
            background-repeat: repeat;
            background-size: 110%;
            color: #ffffff;
            font-family: "Noto Sans", sans-serif;

            margin: 0;
            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
            overflow-y: hidden;
        }

        .card {
            width: 100%;
            max-width: 480px;
            background-color: #1B2428;
            margin: 20px auto;
            padding: 30px;
            border-radius: 16px;
            border: 4px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);

            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .layout-section {
            margin-top: 10px;
            background: #181A18;
            border-radius: 16px;
            padding: 30px 20px;
            border: 4px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
        }

        h1 {
            color: darkgoldenrod;
            font-size: 26px;
            font-family: "Noto Serif", serif;
            text-align: center;
            text-transform: uppercase;
        }

        p {
            color: #fff;
            font-family: "Noto Serif", serif;
        }

        label {
            width: 100%;
            text-align: left;
            font-size: 12px;
            color: #ccc;
        }

        input,
        textarea,
        select {
            width: 100%;
            margin-bottom: 10px;
            background: #ccc;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
            color: #333;
            font-weight: 500;
            padding: 8px 12px 8px 20px;
        }

        .btn-save {
            width: 100%;
            height: 40px;
            margin-top: 10px;
            background-color: darkgoldenrod;
            border-radius: 8px;
            border: 3px solid darkgoldenrod;
            cursor: pointer;
            font-size: 14px;
            color: #1B2428;
            font-weight: 600;
        }

        .btn-save:hover {
            background-color: transparent;
            border: 3px solid darkgoldenrod;
            color: darkgoldenrod;
        }

        .back-link {
            text-align: center;
            margin-top: 14px;
            margin-bottom: 0;
        }

        .back-link a {
            color: darkgoldenrod;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* =========================
   IPAD & DESKTOP (>= 768px)
========================= */

        @media (min-width: 768px) {

            body {
                background-size: 50%;
                background-position: -350px 0;
                padding: 0;
            }
        }

        /* =========================
   LAPTOP & DESKTOP (>= 1440px)
========================= */

        @media (min-width: 1440px) {
            body {
                background-size: 30%;
                background-position: -380px 0;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>EDIT PRODUCT #<?= htmlspecialchars($id) ?></h1>
        <form method="post" enctype="multipart/form-data">
            <div class="layout-section">

                <!-- Nome do produto -->
                <label for="name">Name</label>
                <input type="text" id="name" name="name"
                    value="<?= htmlspecialchars($product['name']) ?>"
                    required autocomplete="name">

                <!-- Descrição -->
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" required><?= htmlspecialchars($product['description']) ?></textarea>

                <!-- Preço -->
                <label for="price">Price ($)</label>
                <input type="text" id="price" name="price"
                    value="<?= number_format($product['price'], 2, '.', '') ?>"
                    required autocomplete="off">

                <!-- Stock -->
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock"
                    value="<?= intval($product['stock']) ?>"
                    required autocomplete="off">

                <!-- Imagem do produto -->
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">

                <!-- Botão de envio -->
                <button type="submit" class="btn-save">Save Changes</button>

                <!-- Link de voltar -->
                <p class="back-link">
                    <a href="admin.php">Back</a>
                </p>

            </div>
        </form>
    </div>
</body>

</html>