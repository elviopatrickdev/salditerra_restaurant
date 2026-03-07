<?php
session_start();
require_once '../config/config.php';

// Verifica se o usuário é admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login-register.php');
    exit;
}

// ==========================
// FUNÇÕES DE GESTÃO VIA AJAX
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_item_quantity'])) {
        $item_id = intval($_POST['item_id']);
        $new_quantity = intval($_POST['quantity']);

        // Pega o item atual e o stock do produto
        $stmt = $conn->prepare("
            SELECT product_id, quantity, order_id 
            FROM tbl_order_items 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->bind_result($product_id, $old_quantity, $order_id);
        $stmt->fetch();
        $stmt->close();

        // Pega o stock atual do produto
        $stmt = $conn->prepare("SELECT stock FROM tbl_products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($current_stock);
        $stmt->fetch();
        $stmt->close();

        // Calcula a diferença
        $diff = $new_quantity - $old_quantity;

        // Verifica se há stock suficiente
        if ($diff > 0 && $diff > $current_stock) {
            echo json_encode([
                'success' => false,
                'message' => "Não há stock suficiente! Disponível: $current_stock"
            ]);
            exit;
        }

        // Atualiza a quantidade do item
        $stmt = $conn->prepare("UPDATE tbl_order_items SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $item_id);
        $stmt->execute();
        $stmt->close();

        // Atualiza o stock do produto
        $stmt = $conn->prepare("UPDATE tbl_products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $diff, $product_id);
        $stmt->execute();
        $stmt->close();

        // Recalcula total do pedido
        $stmt = $conn->prepare("
            SELECT IFNULL(SUM(oi.quantity * p.price),0) AS total_price
            FROM tbl_order_items oi
            JOIN tbl_products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($total_price);
        $stmt->fetch();
        $stmt->close();

        // Retorna JSON completo para o JS
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'product_id' => $product_id,
            'new_total' => number_format($total_price, 2),
            'new_stock' => $current_stock - $diff
        ]);
        exit;
    }

    // Recalcular total sem alterar quantidade
    if (isset($_POST['recalc_order_total'])) {
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare("
            SELECT IFNULL(SUM(oi.quantity * p.price),0) AS total_price
            FROM tbl_order_items oi
            JOIN tbl_products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($total_price);
        $stmt->fetch();
        $stmt->close();

        echo json_encode(['success' => true, 'new_total' => number_format($total_price, 2)]);
        exit;
    }
}

// ==========================
// REMOÇÕES VIA GET
// ==========================

if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['delete_order_item'])) {
    $item_id = intval($_GET['delete_order_item']);

    // Pega dados do item
    $stmt = $conn->prepare("SELECT order_id, product_id, quantity FROM tbl_order_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->bind_result($order_id, $product_id, $quantity);
    $stmt->fetch();
    $stmt->close();

    // Atualiza stock
    $stmt = $conn->prepare("UPDATE tbl_products SET stock = stock + ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    // Deleta item
    $stmt = $conn->prepare("DELETE FROM tbl_order_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->close();

    // Recalcula total do pedido
    $stmt = $conn->prepare("
        SELECT IFNULL(SUM(oi.quantity * p.price),0) AS total_price
        FROM tbl_order_items oi
        JOIN tbl_products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->bind_result($total_price);
    $stmt->fetch();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'product_id' => $product_id,
        'quantity' => $quantity,
        'new_total' => number_format($total_price, 2)
    ]);
    exit;
}

if (isset($_GET['delete_order'])) {
    $order_id = intval($_GET['delete_order']);
    $return_data = [];

    // Pega status do pedido
    $stmt = $conn->prepare("SELECT status FROM tbl_orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->bind_result($order_status);
    $stmt->fetch();
    $stmt->close();

    $return_data['order_id'] = $order_id;
    $return_data['updated_stock'] = [];

    if ($order_status === 'pending') {
        // Pega produtos e quantidades do pedido
        $stmt = $conn->prepare("SELECT product_id, quantity FROM tbl_order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Atualiza stock e salva para retornar
        foreach ($items as $item) {
            $stmt = $conn->prepare("UPDATE tbl_products SET stock = stock + ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            $stmt->close();

            // Pega novo stock
            $stmt = $conn->prepare("SELECT stock FROM tbl_products WHERE id = ?");
            $stmt->bind_param("i", $item['product_id']);
            $stmt->execute();
            $stmt->bind_result($new_stock);
            $stmt->fetch();
            $stmt->close();

            $return_data['updated_stock'][$item['product_id']] = $new_stock;
        }
    }

    // Deleta os items do pedido
    $stmt = $conn->prepare("DELETE FROM tbl_order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Deleta o pedido
    $stmt = $conn->prepare("DELETE FROM tbl_orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(array_merge($return_data, ['success' => true]));
    exit;
}

if (isset($_GET['process_order'])) {
    $id = intval($_GET['process_order']);
    $stmt = $conn->prepare("UPDATE tbl_orders SET status='completed' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['delete_reservation'])) {
    $id = intval($_GET['delete_reservation']);
    $stmt = $conn->prepare("DELETE FROM tbl_reservation WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $success = $stmt->affected_rows > 0;
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit;
}

// ============
// BUSCAR DADOS
// ============

$users = $conn->query("SELECT * FROM tbl_users ORDER BY id DESC");
$products = $conn->query("SELECT * FROM tbl_products ORDER BY id DESC");
$reservations = $conn->query("SELECT * FROM tbl_reservation ORDER BY id DESC");
$orders = $conn->query("
    SELECT o.id, o.user_id, o.address, o.status, u.username,
    IFNULL(SUM(oi.quantity * p.price), 0) AS total_price
    FROM tbl_orders o
    JOIN tbl_users u ON o.user_id = u.id
    LEFT JOIN tbl_order_items oi ON oi.order_id = o.id
    LEFT JOIN tbl_products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.id DESC
");

$order_items = $conn->query("
    SELECT 
        i.id AS item_id, 
        i.order_id, 
        i.product_id,
        p.name AS product_name, 
        i.quantity, 
        p.price, 
        u.username
    FROM tbl_order_items i
    JOIN tbl_orders o ON i.order_id = o.id
    JOIN tbl_products p ON i.product_id = p.id
    JOIN tbl_users u ON o.user_id = u.id
    ORDER BY i.order_id DESC, i.id ASC
");

// Totais iniciais
$totals = [
    'users' => $users->num_rows,
    'products' => $products->num_rows,
    'reservations' => $reservations->num_rows,
    'orders' => $orders->num_rows
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Painel</title>

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
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Noto Sans", sans-serif;
            color: #fff;
        }

        html {
            scroll-behavior: smooth;
        }

        /* BODY */

        body {
            background-color: #181A18;
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

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: auto;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: row;
            height: 70px;
            width: 100%;
            background: #1B2428;
            transition: none;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 2000;
        }

        /* LOGO */
        .sidebar .logo-details {
            display: none;
        }

        .sidebar .logo-details img {
            display: none;
        }

        /* LINKS */
        .sidebar .nav-links {
            display: flex;
            flex-direction: row;
            justify-content: left;
            width: 100%;
            height: 100%;
            padding: 0;
        }

        .sidebar .nav-links li {
            display: flex;
            width: 100%;
            list-style: none;
            transition: background 0.3s ease;
        }

        .sidebar .nav-links li:hover {
            background: #354250;
        }

        .sidebar .nav-links li a {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            text-decoration: none;
        }

        .sidebar .nav-links li span {
            font-size: 10px;
        }

        .sidebar .nav-links li i {
            height: auto;
            min-width: unset;
            text-align: center;
            line-height: normal;
            color: #fff;
            font-size: 20px;
            padding-bottom: 4px;
        }

        /* ESCONDE CONTEÚDO POR PADRÃO */
        .sidebar .link_name,
        .sidebar .logo-details .logo2,
        .sidebar .profile-details .user-name {
            visibility: visible;
            opacity: 1;
        }

        /* PROFILE */
        .sidebar .profile-details {
            width: 100%;
            height: 100%;
            background: #2a3540;
            display: flex;
            align-items: center;
        }

        .sidebar .profile-details a {
            height: 50%;
        }


        .sidebar li .profile-details a {
            margin-left: 5px;
            justify-content: center;
            width: 90%;
            height: 90%;
            border: 2px solid #2a3540;
            border-radius: 16px;
            background: #354250;
        }


        .sidebar li .profile-details a:hover {
            background: #3542507c;
            transition: background 0.3s ease-in-out;
        }

        .sidebar li .profile-details a:hover i {
            font-size: 18px;
        }

        .sidebar .profile-content img {
            display: none;
        }

        main {
            padding: 30px;
        }

        /* TYPOGRAFIA */

        h1 {
            color: #fff;
            font-family: "Noto Serif", serif;
        }

        h2 {
            color: darkgoldenrod;
            font-size: 24px;
            font-family: "Noto Serif", serif;
            margin: 40px 20px 10px 20px;
        }

        h5 {
            margin: 0px 20px;
            color: #ccc;
        }

        p {
            color: #fff;
            font-family: "Noto Serif", serif;
        }

        .total .profile-content img {
            display: block;
            height: 60px;
            width: 60px;
            border: 4px solid #151D21;
            border-radius: 16px;
            object-fit: cover;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 3px 6px rgba(0, 0, 0, 0.7);
        }

        .total .profile-content .profile-text {
            display: none;
        }

        .card {
            display: flex;
            flex-direction: row;
            background-color: #1B2428;
            border-radius: 16px;
            width: 100%;
            border: 3px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            font-size: 1.2rem;
            background: #181A18;
            border: 4px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 3px 6px rgba(0, 0, 0, 0.7);
        }

        .icon-box i {
            font-size: 2rem;
        }

        .card-flex {
            flex: 0 0 100%;
        }

        .admin-section {
            background: #1B2428;
            margin: 40px 0px;
            border: 4px solid #151D21;
            border-radius: 16px;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
        }

        /* ================= MOBILE FIRST ================= */

        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
            font-size: 14px;
        }

        /* Esconde o thead no mobile */
        thead {
            display: none;
        }

        /* Cada linha vira um card */
        tbody tr {
            display: block;
            background: #181A18;
            margin: 0px 15px 15px 15px;
            border-radius: 16px;
            padding: 10px 20px;
            border: 4px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
        }

        /* Cada célula vira linha interna */
        tbody td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #222;
            font-size: 12px;
        }

        /* Aqui aparece o "head" de cada produto */
        tbody td::before {
            content: attr(data-label);
            font-weight: bold;
            color: #fff;
        }


        /* ================= BOTÕES ================= */

        .btn-admin {
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .add-product-btn {
            background: darkgoldenrod;
            margin-right: 24px;
            color: #181A18;
            border: 2px solid rgba(255, 255, 255, 0.15);
            box-shadow:
                0 6px 15px rgba(0, 0, 0, 0.7);
        }

        .add-product-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.05);
            margin-right: 24px;
            color: #ccc;
        }

        .btn-delete {
            background: brown;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.15);
            box-shadow:
                0 6px 15px rgba(0, 0, 0, 0.7);
        }

        .btn-delete:hover {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.05);
            color: #ccc;
        }

        .btn-edit {
            background: darkgoldenrod;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.15);
            box-shadow:
                0 6px 15px rgba(0, 0, 0, 0.7);
        }

        .btn-edit:hover {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.05);
            color: #ccc;
        }

        .btn-process {
            background: #047857;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.15);
            box-shadow:
                0 6px 15px rgba(0, 0, 0, 0.7);
        }

        .btn-process:hover {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.05);
            color: #ccc;
        }

        /* ================= STATUS ================= */

        .order-status.pending {
            color: brown;
            font-weight: bold;
        }

        .order-status.completed {
            color: green;
            font-weight: bold;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
        }

        @media (min-width: 768px) {

            .sidebar {
                position: fixed;
                display: flex;
                flex-direction: column;
                top: 0;
                left: 0;
                height: 100%;
                width: 68px;
                background: #1B2428;
                transition: width 0.3s ease;
                border-top: none;
                border-right: 1px solid rgba(255, 255, 255, 0.08);
                overflow: hidden;
                z-index: 2000;
            }

            /* ABRE NO HOVER */
            .sidebar:hover {
                width: 260px;
            }

            /* LOGO */
            .sidebar .logo-details {
                height: 60px;
                display: flex;
                align-items: center;
                padding-left: 9px;
                padding-top: 10px;
            }

            .sidebar .logo-details img {
                display: block;
            }

            /* LINKS */
            .sidebar .nav-links {
                display: flex;
                flex-direction: column;
                justify-content: start;
                height: 100%;
                padding-top: 30px;
                margin-bottom: 0;
                padding-left: 0;
            }

            .sidebar .nav-links li {
                list-style: none;
                transition: background 0.3s ease;
            }

            .sidebar .nav-links li:hover {
                background: #354250;
            }

            .sidebar .nav-links li a {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: left;
                text-decoration: none;
            }

            .sidebar .nav-links li i {
                height: 50px;
                min-width: 68px;
                text-align: center;
                line-height: 50px;
                color: #fff;
                font-size: 18px;
            }

            .sidebar .nav-links li .link_name {
                font-size: 16px;
                color: #fff;
                white-space: nowrap;
            }

            /* ESCONDE CONTEÚDO POR PADRÃO */
            .sidebar .link_name,
            .sidebar .logo-details .logo2,
            .sidebar .profile-details .user-name {
                visibility: hidden;
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            /* MOSTRA NO HOVER */
            .sidebar:hover .link_name,
            .sidebar:hover .logo-details .logo2,
            .sidebar:hover .profile-details .user-name,
            .sidebar:hover .profile-details i {
                visibility: visible;
                opacity: 1;
            }

            .lista {
                position: relative;
            }

            .fixo {
                position: absolute;
                bottom: 0;
            }

            main {
                margin-left: 68px;
                transition: margin-left 0.3s ease;
            }

            .total .profile-content .profile-text {
                display: flex;
                flex-direction: column;
                text-align: end;
                margin-right: 10px;
                margin-top: 20px;
                font-size: 12px;
            }

            .total .profile-content .profile-text p {
                margin: 0;
                color: #ccc;
            }

            .card-flex {
                flex: 0 0 49%;
            }

            .quantity-input {
                width: 60px;
                height: 30px;
            }

            table {
                display: table;
                text-align: center;

                box-sizing: border-box;
                width: 96.5%;

                background: #181A18;
                margin: 15px 15px 35px 15px;
                border-radius: 16px;
                padding: 4px 20px 4px;
                border: 4px solid #151D21;
                box-shadow:
                    inset 0 6px 10px rgba(0, 0, 0, 0.5),
                    0 12px 25px rgba(0, 0, 0, 0.7);

                border-collapse: separate;
                overflow: hidden;
            }

            thead {
                display: table-header-group;
                background: transparent;
            }

            thead th {
                padding: 12px;
                border: none;
                border-bottom: 1px solid #222;
            }

            tbody tr {
                display: table-row;
                border: none;
                margin: 0px;
                border-radius: 0px;
                box-shadow: none;
            }

            tbody td {
                display: table-cell;
                padding: 12px;
                border: none;
                border-bottom: 1px solid #222;
            }

            tbody tr:last-child td {
                border-bottom: none;
            }

            tbody td::before {
                display: none;
            }

        }

        @media (min-width: 1440px) {
            .card-flex {
                flex: 0 0 24%;
            }

            table {
                width: 97.5%;
            }
        }
    </style>

</head>

<body>

<!-- =========================
   NAVIGATION SIDEBAR
   Menu lateral fixo com links de administração
========================= -->
    <nav>
        <div class="sidebar">
            <a href="admin.php">
                <div class="logo-details">
                    <img src="assets/logo1.png" style="width: 50px; margin-right: 10px;"
                        alt="Salditerra Restaurante logo">
                    <img src="assets/logo2.png" style="width: 120px;" class="logo2" alt="Salditerra Restaurante Text">
                    <span class="sr-only">Logo</span>
                </div>
            </a>
            <ul class="nav-links lista">
                <li>
                    <a href="#users">
                        <i class="fa-solid fa-users"></i>
                        <span class="sr-only">Users</span>
                        <span class="link_name">Users</span>
                    </a>
                </li>
                <li>
                    <a href="#products">
                        <i class="fa-solid fa-box-open"></i>
                        <span class="sr-only">Products</span>
                        <span class="link_name">Products</span>
                    </a>
                </li>
                <li>
                    <a href="#reservations">
                        <i class="fa-solid fa-utensils"></i>
                        <span class="sr-only">Reservations</span>
                        <span class="link_name">Reservations</span>
                    </a>
                </li>
                <li>
                    <a href="#orders">
                        <i class="fa-solid fa-dolly"></i>
                        <span class="sr-only">Orders</span>
                        <span class="link_name">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="#order-items">
                        <i class="fa-solid fa-list-check"></i>
                        <span class="sr-only">Order Items</span>
                        <span class="link_name">Order Items</span>
                    </a>
                </li>
                <li class="fixo">
                    <a href="index.php">
                        <i class="fa-solid fa-house"></i>
                        <span class="sr-only">Homepage</span>
                        <span class="link_name">Homepage</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

<!-- =========================
   MAIN CONTENT
   Área principal do dashboard com resumo e seções detalhadas
========================= -->
    <main>
        <section class="total">
            <div class="d-flex justify-content-between">
                <div>
                    <h1>Welcome Back!</h1>
                    <p class="text-secondary">DASHBOARD</p>
                </div>
                <div class="profile-content d-flex">
                    <div class="profile-text">
                        <p>Admin</p>
                        <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                    <img src="<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile">
                </div>
            </div>
            <h2>SUMMARY</h2>
            <div class="all-total d-flex flex-wrap gap-3 mx-0 p-0">
                <div class="card total-users align-items-center justify-content-between p-3 gap-4 card-flex">
                    <!-- Ícone à esquerda -->
                    <div class="icon-box text-primary rounded-3 d-flex align-items-center justify-content-center p-4 ">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <!-- Textos à direita -->
                    <div class="text-end d-flex flex-column align-items-end">
                        <p id="total_users" class="mb-0 fw-semibold fs-2 fs-md-4"><?= $totals['users'] ?></p>
                        <p class="mb-0 text-secondary small">Users</p>
                    </div>
                </div>
                <div class="card total-products d-flex align-items-center justify-content-between p-3 gap-4 card-flex">
                    <!-- Ícone à esquerda -->
                    <div class="icon-box text-primary rounded-3 d-flex align-items-center justify-content-center p-4">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <!-- Textos à direita -->
                    <div class="text-end d-flex flex-column align-items-end">
                        <p id="total_products" class="mb-0 fw-semibold fs-2 fs-md-4"><?= $totals['products'] ?></p>
                        <p class="mb-0 text-secondary small">Products</p>
                    </div>
                </div>
                <div class="card total-reservation d-flex align-items-center justify-content-between p-3 gap-4 card-flex">
                    <!-- Ícone à esquerda -->
                    <div class="icon-box text-primary rounded-3 d-flex align-items-center justify-content-center p-4">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <!-- Textos à direita -->
                    <div class="text-end d-flex flex-column align-items-end">
                        <p id="total_reservations" class="mb-0 fw-semibold fs-2 fs-md-4"><?= $totals['reservations'] ?></p>
                        <p class="mb-0 text-secondary small">Reservations</p>
                    </div>
                </div>
                <div class="card total-orders d-flex align-items-center justify-content-between p-3 gap-4 card-flex">
                    <!-- Ícone à esquerda -->
                    <div class="icon-box text-primary rounded-3 d-flex align-items-center justify-content-center p-4">
                        <i class="fa-solid fa-dolly"></i>
                    </div>
                    <!-- Textos à direita -->
                    <div class="text-end d-flex flex-column align-items-end">
                        <p id="total_orders" class="mb-0 fw-semibold fs-2 fs-md-4"><?= $totals['orders'] ?></p>
                        <p class="mb-0 text-secondary small">Pendent Orders</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Users -->

        <section id="users" class="users">
            <div class="admin-section">
                <h2>USERS</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="padding:15px;">ID</th>
                            <th style="padding:15px;">Photo</th>
                            <th style="padding:15px;">Username</th>
                            <th style="padding:15px;">Email</th>
                            <th style="padding:15px;">Role</th>
                            <th style="padding:15px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr data-user-id="<?= $user['id'] ?>">
                                <td data-label="ID">
                                    <?= $user['id'] ?>
                                </td>
                                <td data-label="Photo">
                                    <img src="<?= htmlspecialchars($user['profile_pic']) ?>"
                                        alt="User"
                                        style="width:40px; height:40px; object-fit:cover; border-radius:12px;">
                                </td>

                                <td data-label="Username">
                                    <?= htmlspecialchars($user['username']) ?>
                                </td>
                                <td data-label="Email">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td data-label="Role">
                                    <?= $user['user_type'] ?>
                                </td>
                                <td data-label="Action">
                                    <div>
                                        <a href="edit_user.php?id=<?= $user['id'] ?>"
                                            class="btn btn-edit btn-admin btn-sm"><i class="fa-regular fa-pen-to-square"></i><span class="sr-only">Edit User</span></a>
                                        <button class="btn btn-delete btn-admin btn-sm delete-user"
                                            data-id="<?= $user['id'] ?>"><i class="fa-solid fa-xmark"></i><span class="sr-only">Delete User</span></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Products -->

        <section id="products" class="products">
            <div class="admin-section">
                <div class="d-flex align-items-center justify-content-between">
                    <h2>PRODUCTS</h2>
                    <a href="add_product.php" class="btn btn-admin add-product-btn mt-4 px-2">
                        Add Product</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="padding:15px;">ID</th>
                            <th style="padding:15px;">Image</th>
                            <th style="padding:15px;">Name</th>
                            <th style="padding:15px;">Price</th>
                            <th style="padding:15px;">Stock</th>
                            <th style="padding:15px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td data-label="ID">
                                    <?= $product['id'] ?>
                                </td>
                                <td data-label="Image">
                                    <img src="<?= htmlspecialchars($product['image']) ?>"
                                        alt="Product"
                                        style="width:40px; height:40px; object-fit:cover; border-radius:12px;">
                                </td>

                                <td data-label="Name">
                                    <?= htmlspecialchars($product['name']) ?>
                                </td>
                                <td data-label="Price">$
                                    <?= number_format($product['price'], 2) ?>
                                </td>
                                <td data-label="Stock" data-product-id="<?= $product['id'] ?>">
                                    <?= $product['stock'] ?>
                                </td>
                                <td data-label="Action">
                                    <div>
                                        <a href="edit_product.php?id=<?= $product['id'] ?>"
                                            class="btn btn-edit btn-admin btn-sm"><i class="fa-regular fa-pen-to-square"></i><span class="sr-only">Edit Product</span></a>
                                        <button class="btn btn-delete btn-admin btn-sm delete-product" data-id="<?= $product['id'] ?>">
                                            <i class="fa-solid fa-xmark"></i>
                                            <span class="sr-only">Delete Product</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Reservations -->

        <section id="reservations" class="reservations">
            <div class="admin-section">
                <h2>RESERVATIONS</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="padding:15px;">ID</th>
                            <th style="padding:15px;">Name</th>
                            <th style="padding:15px;">Phone</th>
                            <th style="padding:15px;">Guests</th>
                            <th style="padding:15px;">Date</th>
                            <th style="padding:15px;">Hour</th>
                            <th style="padding:15px;">Message</th>
                            <th style="padding:15px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reservation = $reservations->fetch_assoc()): ?>
                            <tr>
                                <td data-label="ID">
                                    <?= $reservation['id'] ?>
                                </td>
                                <td data-label="Name">
                                    <?= htmlspecialchars($reservation['name']) ?>
                                </td>
                                <td data-label="Phone">
                                    <?= htmlspecialchars($reservation['phone']) ?>
                                </td>
                                <td data-label="Guest">
                                    <?= $reservation['guest'] ?>
                                </td>
                                <td data-label="Date">
                                    <?= $reservation['date'] ?>
                                </td>
                                <td data-label="Hour">
                                    <?= substr($reservation['hour'], 0, 5) ?>
                                </td>
                                <td data-label="Message">
                                    <?= htmlspecialchars($reservation['message']) ?>
                                </td>
                                <td data-label="Action">
                                    <button class="btn btn-delete btn-admin btn-sm delete-reservation"
                                        data-id="<?= $reservation['id'] ?>">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span class="sr-only">Delete Reservation</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Orders -->

        <section id="orders" class="orders">
            <div class="admin-section">
                <h2>ORDERS</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="padding:15px;">ID</th>
                            <th style="padding:15px;">User</th>
                            <th style="padding:15px;">Address</th>
                            <th style="padding:15px;">Total ($)</th>
                            <th style="padding:15px;">Stats</th>
                            <th style="padding:15px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr data-order-id="<?= $order['id'] ?>">
                                <td data-label="ID">
                                    <?= $order['id'] ?>
                                </td>
                                <td data-label="User">
                                    <?= htmlspecialchars($order['username']) ?>
                                </td>
                                <td data-label="Address">
                                    <?= $order['address'] ?>
                                </td>
                                <td data-label="Total ($)">$
                                    <?= number_format($order['total_price'], 2) ?>
                                </td>
                                <td data-label="Status" class="order-status <?= $order['status'] ?>">
                                    <?= $order['status'] === 'pending' ? 'Pendent' : 'Completed' ?>
                                </td>
                                <td data-label="Action">
                                    <div>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-process btn-admin btn-sm process-order"
                                                data-id="<?= $order['id'] ?>">Proceed<span class="sr-only">Process Order</span></button>
                                        <?php endif; ?>
                                        <button class="btn btn-delete btn-admin btn-sm delete-order"
                                            data-id="<?= $order['id'] ?>"><i class="fa-solid fa-xmark"></i><span class="sr-only">Delete Order</span></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Order Items -->

        <section id="order-items" class="order-items">
            <div class="admin-section">
                <h2>ORDER ITEMS</h2>
                <?php
                $current_order_id = null;
                $bg_toggle = false;
                while ($item = $order_items->fetch_assoc()):
                    if ($current_order_id !== $item['order_id']):
                        if ($current_order_id !== null) echo "</tbody></table>";
                        $bg_toggle = !$bg_toggle;
                        $current_order_id = $item['order_id'];
                ?>
                        <h3
                            style="background-color: <?= $bg_toggle ? '#333' : '#444' ?>; padding:10px; border-radius:5px; margin: 20px 20px 10px; font-size: 18px;">
                            Order #
                            <?= $item['order_id'] ?> -
                            <?= htmlspecialchars($item['username']) ?>
                        </h3>
                        <table data-order-id="<?= $item['order_id'] ?>">
                            <thead>
                                <tr>
                                    <th style="padding:15px; width: 10%;">Product ID</th>
                                    <th style="padding:15px; width: 40%;">Product</th>
                                    <th style="padding:15px; width: 20%;">Price</th>
                                    <th style="padding:15px; width: 20%;">Quantity</th>
                                    <th style="padding:15px; width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php endif; ?>
                            <tr data-item-id="<?= $item['item_id'] ?>">
                                <td data-label="Product ID">
                                    <?= htmlspecialchars($item['product_id']) ?>
                                </td>
                                <td data-label="Product">
                                    <?= htmlspecialchars($item['product_name']) ?>
                                </td>
                                <td data-label="Price">€
                                    <?= number_format($item['price'], 2) ?>
                                </td>
                                <td data-label="Quantity">
                                    <div>
                                        <label for="quantity-<?= $item['item_id'] ?>" class="sr-only">Quantity</label>
                                        <input type="number" id="quantity-<?= $item['item_id'] ?>" class="quantity-input" data-id="<?= $item['item_id'] ?>"
                                            value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>"
                                            style="color: #111; width: 40px; padding-bottom: 3px;">
                                        <button class="btn btn-edit btn-sm update-quantity"
                                            data-id="<?= $item['item_id'] ?>"><i class="fa-solid fa-arrows-rotate"></i><span class="sr-only">Update quantity</span></button>
                                    </div>
                                </td>
                                <td data-label="Action">
                                    <button class="btn btn-delete btn-admin btn-sm delete-item"
                                        data-id="<?= $item['item_id'] ?>"><i class="fa-solid fa-xmark"></i><span class="sr-only">Delete Order Item</span></button>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                        <?php if ($current_order_id !== null) echo "</tbody></table>"; ?>
            </div>
        </section>
    </main>

    <script>
        // AJAX sem recarregar página

        async function atualizarTotais() {
            try {
                const res = await fetch('../backend/totals.php');
                const data = await res.json();
                if (!data.error) {
                    document.getElementById('total_users').textContent = data.users;
                    document.getElementById('total_products').textContent = data.products;
                    document.getElementById('total_reservations').textContent = data.reservations;
                    document.getElementById('total_orders').textContent = data.orders;
                }
            } catch (err) {
                console.error('Error updating totals:', err);
            }
        }

        // Atualiza imediatamente ao carregar
        atualizarTotais();

        // Atualiza a cada 5 segundos
        setInterval(atualizarTotais, 5000);

        // Atualizar quantidade
        document.querySelectorAll('.update-quantity').forEach(btn => {
            btn.addEventListener('click', () => {
                const itemId = btn.dataset.id;
                const qtyInput = document.querySelector(`.quantity-input[data-id='${itemId}']`);
                let newQty = parseInt(qtyInput.value);

                const maxStock = parseInt(qtyInput.getAttribute('max'));
                if (newQty > maxStock) {
                    alert('Quantity exceeds available stock!');
                    qtyInput.value = maxStock;
                    newQty = maxStock;
                } else if (newQty < 1) {
                    qtyInput.value = 1;
                    newQty = 1;
                }

                // Pergunta de confirmação
                if (!confirm('Are you sure you want to update this quantity?')) {
                    qtyInput.value = qtyInput.getAttribute('data-prev') || qtyInput.value;
                    return;
                }

                fetch('admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `update_item_quantity=1&item_id=${itemId}&quantity=${newQty}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Atualiza total do pedido
                            const orderRow = document.querySelector(`.orders tr[data-order-id='${data.order_id}']`);
                            if (orderRow) {
                                orderRow.querySelector('td[data-label="Total ($)"]').textContent = `$${data.new_total}`;
                            }

                            // Atualiza stock dinamicamente
                            const stockCell = document.querySelector(`.products tr td[data-product-id='${data.product_id}']`);
                            if (stockCell) stockCell.textContent = data.new_stock;

                            // Atualiza max do input para não ultrapassar stock
                            qtyInput.setAttribute('max', data.new_stock);

                            atualizarTotais();
                        } else {
                            alert(data.message || 'Error updating quantity');
                        }
                    });
            });
        });

        // Processar pedido
        document.querySelectorAll('.process-order').forEach(btn => {
            btn.addEventListener('click', () => {
                const orderId = btn.dataset.id;

                fetch(`admin.php?process_order=${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Atualiza status na tabela Orders
                            const orderRow = btn.closest('tr');
                            const statusCell = orderRow.querySelector('.order-status');
                            statusCell.textContent = 'Concluída';
                            statusCell.classList.remove('pending');
                            statusCell.classList.add('completed');

                            // Remove botão Proceed
                            btn.remove();

                            // DESABILITA botões update e delete da tabela Order Items correspondente
                            const orderItemsTable = document.querySelector(`.order-items table[data-order-id='${orderId}']`);
                            if (orderItemsTable) {
                                orderItemsTable.querySelectorAll('.update-quantity, .delete-item').forEach(b => {
                                    b.disabled = true;
                                    b.classList.add('btn-disabled'); // opcional: adicionar classe para visual
                                    b.style.opacity = '0.5'; // opcional: visualmente mostrar que está desabilitado
                                    b.style.cursor = 'not-allowed';
                                });

                                // Também desabilita inputs de quantidade
                                orderItemsTable.querySelectorAll('.quantity-input').forEach(input => {
                                    input.disabled = true;
                                    input.style.backgroundColor = '#ccc'; // visual opcional
                                });
                            }
                        }
                    });
            });
        });

        // Remover pedido
        document.querySelectorAll('.delete-order').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Are you sure you want to remove this order and all its items?')) return;

                const orderId = btn.dataset.id;
                const orderRow = btn.closest('tr');

                fetch(`admin.php?delete_order=${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Remove a linha do pedido da tabela Orders
                            orderRow.remove();

                            // Remove a tabela de Order Items correspondente
                            const orderItemsTable = document.querySelector(`.order-items table[data-order-id='${orderId}']`);
                            if (orderItemsTable) {
                                // Remove também o <h5> acima da tabela
                                const h5 = orderItemsTable.previousElementSibling;
                                if (h5 && h5.tagName.toLowerCase() === 'h5') h5.remove();

                                // Remove a tabela
                                orderItemsTable.remove();
                            }

                            // Atualiza o stock dos produtos
                            for (const productId in data.updated_stock) {
                                const stockCell = document.querySelector(`.products td[data-product-id='${productId}']`);
                                if (stockCell) stockCell.textContent = data.updated_stock[productId];
                            }

                            // Atualiza totais gerais
                            atualizarTotais();
                        }
                    });
            });
        });

        // Remover item
        document.querySelectorAll('.delete-item').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Are you sure you want to remove this item from the order?')) return;

                const itemId = btn.dataset.id;
                const row = btn.closest('tr');
                const tbody = row.parentElement;
                const table = tbody.closest('table');
                const orderId = table.dataset.orderId;

                fetch(`admin.php?delete_order_item=${itemId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Remove a linha
                            row.remove();

                            // Atualiza stock do produto dinamicamente
                            const stockCell = document.querySelector(`.products td[data-product-id='${data.product_id}']`);
                            if (stockCell) {
                                let currentStock = parseInt(stockCell.textContent) || 0;
                                stockCell.textContent = currentStock + data.quantity;
                            }

                            // Atualiza total do pedido
                            const orderRow = document.querySelector(`.orders tr[data-order-id='${data.order_id}']`);
                            if (orderRow) orderRow.querySelector('td[data-label="Total ($)"]').textContent = `$${data.new_total}`;

                            // Atualiza totais gerais
                            atualizarTotais();
                        }
                    });
            });
        });

        // Remover usuário
        document.querySelectorAll('.delete-user').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Tem certeza que deseja remover este usuário?')) return;
                const id = btn.dataset.id;
                fetch(`admin.php?delete_user=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) btn.closest('tr').remove();
                    });
            });
        });

        // Remover reserva
        document.querySelectorAll('.delete-reservation').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Tem certeza que deseja remover esta reserva?')) return;
                const id = btn.dataset.id;
                fetch(`admin.php?delete_reservation=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) btn.closest('tr').remove();
                    });
            });
        });

        // Remover produto via AJAX
        document.querySelectorAll('.delete-product').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Are you sure you want to remove this product?')) return;

                const productId = btn.dataset.id;

                fetch('../backend/delete_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${productId}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Remove a linha da tabela do produto
                            btn.closest('tr').remove();
                            // Atualiza os totais (se você tiver função para isso)
                            atualizarTotais();
                        } else {
                            alert(data.msg || 'Erro ao deletar produto');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erro ao deletar produto');
                    });
            });
        });
    </script>

</body>

</html>