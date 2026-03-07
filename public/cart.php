<?php
session_start();
require_once '../config/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login-register.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = null;

// =============================
// RECUPERA CARRINHO ATUALIZADO
// =============================
$cart = $_SESSION['cart'] ?? [];

// Calcula total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// =============================
// PROCESSAR PEDIDO
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {

    // 🔹 Recarrega carrinho atualizado da sessão
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        $error_message = "Your cart is empty.";
    } else {

        $address = trim($_POST['address'] ?? '');
        $birth_date = $_POST['birth_date'] ?? null;

        // 🔹 Validação básica
        if (empty($address) || empty($birth_date)) {
            $error_message = "All fields are required.";
        } else {

            // 🔹 Validação de idade mínima (18 anos)
            $today = new DateTime();
            $birth = new DateTime($birth_date);
            $age = $today->diff($birth)->y;

            if ($age < 18) {
                $error_message = "You must be at least 18 years old.";
            }
        }

        // 🔹 Se não houver erro, processa pedido
        if (!$error_message) {

            $conn->begin_transaction();

            try {

                // Criar pedido com endereço e data de nascimento
                $stmt = $conn->prepare("INSERT INTO tbl_orders (user_id, address, birth_date) VALUES (?, ?, ?)");
                if (!$stmt) throw new Exception($conn->error);

                $stmt->bind_param("iss", $user_id, $address, $birth_date);
                $stmt->execute();
                $order_id = $conn->insert_id;
                $stmt->close();

                // Inserir itens do pedido
                $stmt = $conn->prepare("INSERT INTO tbl_order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
                if (!$stmt) throw new Exception($conn->error);

                foreach ($cart as $item) {

                    $product_id = $item['id'];
                    $quantity = $item['quantity'];

                    // 🔹 Verificar stock atual (bloqueia a linha para evitar conflito)
                    $checkStock = $conn->prepare("SELECT stock FROM tbl_products WHERE id = ? FOR UPDATE");
                    $checkStock->bind_param("i", $product_id);
                    $checkStock->execute();
                    $checkStock->bind_result($current_stock);
                    $checkStock->fetch();
                    $checkStock->close();

                    if ($current_stock < $quantity) {
                        throw new Exception("Quantity exceeded for product: " . $item['name']);
                    }

                    // 🔹 Subtrair stock
                    $updateStock = $conn->prepare("UPDATE tbl_products SET stock = stock - ? WHERE id = ?");
                    $updateStock->bind_param("ii", $quantity, $product_id);
                    $updateStock->execute();
                    $updateStock->close();

                    // 🔹 Inserir item do pedido
                    $stmt->bind_param("iii", $order_id, $product_id, $quantity);
                    $stmt->execute();
                }

                $stmt->close();

                $conn->commit();

                // 🔹 Limpa carrinho
                unset($_SESSION['cart']);
                $_SESSION['order_success'] = true;

                header("Location: index.php");
                exit;
            } catch (Exception $e) {

                $conn->rollback();
                $error_message = "" . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>

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
    <link rel="icon" href="public/assets/favicon.png" type="image/png">

    <!-- jQuery (necessário para AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <style>
        body {
            background-image: url('assets/pattern-food.png');
            background-repeat: repeat;
            background-size: 110%;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        body::-webkit-scrollbar {
            width: 12px;
        }

        body::-webkit-scrollbar-track {
            background: #151D21;
        }

        body::-webkit-scrollbar-thumb {
            background-color: darkgoldenrod;
            border-radius: 10px;
            border: 2px solid #151D21;
        }

        body::-webkit-scrollbar-thumb:hover {
            background-color: darkgoldenrod;
        }

        /* Typography */

        h1 {
            font-family: "Noto Serif", serif;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: darkgoldenrod;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
        }

        h2,
        h3 {
            font-family: "Noto Serif", serif;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #ccc;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
        }

        body,
        p {
            font-family: "Noto Sans", sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.7;
            color: #f8f8f6;
            text-align: justify;
        }

        /* ===========
        CONTAINER CART
        ========= */

        .container {
            display: flex;
            position: relative;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 1100px;
            padding: 10px;
            background-color: #181A18;
            border: 8px solid #151D21;
            border-radius: 30px;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
            overflow: hidden;
        }

        .cart-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .cart-total {
            font-weight: 600;
        }

        .cart-box {
            display: flex;
            flex-direction: column;
            justify-content: start;
            border-radius: 16px;
            align-items: center;
            min-width: 376px;
            width: 100%;
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .cart-box::-webkit-scrollbar {
            width: 8px;
        }

        .cart-box::-webkit-scrollbar-track {
            background: #151D21;
            border-radius: 10px;
        }

        .cart-box::-webkit-scrollbar-thumb {
            background-color: darkgoldenrod;
            border-radius: 10px;
            border: 2px solid #151D21;
        }

        .cart-box::-webkit-scrollbar-thumb:hover {
            background-color: darkgoldenrod;
        }

        .cart-card {
            display: flex;
            background-color: #1B2428;
            border: 4px solid #151D21;
            border-radius: 30px;
            width: 100%;
            box-shadow:
                inset 0 4px 8px rgba(0, 0, 0, 0.5),
                inset 0 -4px 6px rgba(0, 0, 0, 0.5),
                0 8px 20px rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 10px;
            align-items: center;
        }

        .cart-card img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #181818;
        }

        .cart-details {
            flex: 1;
            margin: 10px 20px;
        }

        .cart-details h5 {
            font-size: 13px;
            text-align: start;
            color: darkgoldenrod;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .cart-details p {
            font-size: 13px;
            margin: 0;
            color: #ffffff;
        }

        .cart-actions {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .input-quantity {
            position: relative;
            bottom: -24px;
            width: 50px;
            height: 35px;
            text-align: center;
            font-weight: 600;
            color: darkgoldenrod;
            border: 2px solid darkgoldenrod;
            border-radius: 8px;
            padding: 10px 4px;
            background: transparent;
            transition: all 0.3s;
        }

        .input-quantity:focus {
            outline: none;
        }

        .btn-remove {
            position: absolute;
            top: -28px;
            right: 0px;

            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 3px solid brown;

            background: linear-gradient(145deg, #b03a2e, #7b241c);

            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.3);

            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-remove i {
            color: white;
            font-size: 16px;
            pointer-events: none;
        }

        .btn-remove:hover {
            opacity: .7;
        }

        .checkout {
            display: flex;
            align-items: center;
            max-width: 340px;
            width: 100%;
            justify-content: center;
            padding: 30px 20px 40px;
            margin: 20px;
            text-align: center;
            background-color: #151515;
            border: 4px solid #1A1A1A;
            border-radius: 30px;

            box-shadow:
                inset 0 4px 8px rgba(0, 0, 0, 0.5),
                inset 0 -4px 6px rgba(0, 0, 0, 0.5),
                0 8px 20px rgba(0, 0, 0, 0.7);
            z-index: 1;
        }

        .checkout h2 {
            color: darkgoldenrod;
        }

        .input-box {
            position: relative;
            margin: 4px 0;
        }

        .input-box label {
            width: 100%;
            text-align: left;
            font-size: 12px;
            color: #ccc;
        }

        .input-box input {
            width: 100%;
            padding: 8px 50px 8px 20px;
            background: #ccc;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        input#birth-date {
            color: #333;
            padding: 8px 12px 8px 20px;
        }

        #birth-date::-webkit-calendar-picker-indicator {
            filter: invert(.5);
            font-size: 16px;
            cursor: pointer;
        }

        .input-box i {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #888;
        }


        h3 {
            margin-top: 20px;
        }

        .btn {
            width: 100%;
            height: 40px;
            background-color: darkgoldenrod;
            border-radius: 8px;
            border: 3px solid darkgoldenrod;
            cursor: pointer;
            font-size: 14px;
            color: #1B2428;
            font-weight: 600;
        }

        .btn:hover {
            background-color: transparent;
            border: 3px solid darkgoldenrod;
            color: darkgoldenrod;
        }

        .empty-cart {
            width: 394px;
            height: 350px;
        }

        .fa-cart-shopping {
            font-size: 120px;
            margin: 20px;
        }

        .back-index-btn a {
            position: absolute;
            right: -50px;
            top: -45px;
            width: 120px;
            height: 120px;
            border: 3px solid darkgoldenrod;
            border-radius: 50%;
            box-shadow:
                inset 0 4px 8px rgba(0, 0, 0, 0.5),
                inset 0 -4px 6px rgba(0, 0, 0, 0.5),
                0 8px 20px rgba(0, 0, 0, 0.7);

            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 26px;
            color: #333;
            text-decoration: none;
            background-color: darkgoldenrod;
            z-index: 3000;
        }

        .back-index-btn a i {
            position: absolute;
            left: 25px;
            top: 60px;
        }

        .back-index-btn a:hover {
            background-color: #333;
            color: #ccc;
        }

        .alert {
            background-color: brown;
            color: #1B2428;
            border: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            opacity: 1;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .alert.hide {
            opacity: 0;
            transform: translateY(-10px);
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
        }

        /* =========================
   IPAD & DESKTOP (>= 768px)
========================= */

        @media (min-width: 768px) {

            body {
                background-size: 50%;
                background-position: -350px 0;
                padding: 0;
                align-items: center;
            }

            .container {
                display: flex;
                flex-direction: column;
                width: auto;
            }

            .cart-content {
                display: flex;
                flex-direction: row;
                gap: 20px;
            }

            .cart-box {
                justify-content: flex-start;
                align-items: center;
                width: 100%;
                max-height: 480px;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 20px;
            }

            .checkout {
                flex: 1;
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

<body>

    <?php if ($error_message): ?>
        <div class="alert alert-danger" style="position: fixed; top: 110px; left: 50%; transform: translateX(-50%); z-index: 1050; width: auto; max-width: 400px;">
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="container" id="container">
        <div class="d-flex flex-column justify-content-between align-items-center m-3">
            <h1>CART</h1>
            <h2>TOTAL: $<span class="cart-total"><?= number_format($total, 2) ?></span></h2>
            <div class="back-index-btn">
                <a href="index.php" class="icon"><i class="fa-solid fa-arrow-rotate-left"></i></a>
                <span class="sr-only">Homepage</span>
            </div>
        </div>
        <div class="cart-content">
            <div class="cart-section">
                <div class="cart-box">
                    <?php if (empty($cart)): ?>
                        <div class="empty-cart d-flex flex-column justify-content-center align-items-center">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <p>Your Cart is empty.</p>
                        </div>
                    <?php else: ?>

                        <div class="cart-items">
                            <?php foreach ($cart as $id => $item):
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                                <div class="cart-card">

                                    <img src="<?= htmlspecialchars($item['image'] ? $item['image'] : 'default.png') ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>">

                                    <div class="cart-details">
                                        <h5><?= htmlspecialchars($item['name']) ?></h5>
                                        <p>Unit price: $<?= number_format($item['price'], 2) ?></p>
                                        <p>Subtotal: $<span class="item-subtotal"><?= number_format($subtotal, 2) ?></span></p>
                                    </div>

                                    <div class="cart-actions">

                                        <a href="../backend/remove_from_cart.php?id=<?= $id ?>" class="btn-remove"><i class="fa-solid fa-xmark"></i></a>
                                        <input type="number" min="1" value="<?= $item['quantity'] ?>" class="input-quantity" data-id="<?= $id ?>">
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </div>
            </div>

            <div class="checkout">
                <form method="POST">
                    <h2 class="mb-3">CHECKOUT</h2>
                    <div class="input-box">
                        <label for="Full-name">Full Name:</label>
                        <input type="text" id="Full-name" name="Full-name" required>
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="input-box">
                        <label for="birth-date">Date of Birth:</label>
                        <input type="date" id="birth-date" name="birth_date" required>
                    </div>
                    <div class="input-box">
                        <label for="phone-number">Phone Number:</label>
                        <input type="tel" id="phone-number" name="phone-number" required>
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div class="input-box">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" autocomplete="street-address" required>
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3>Total: $<span class="cart-total"><?= number_format($total, 2) ?></span></h3>
                    <button button type="submit" name="confirm_order" class="btn mt-2">CONFIRM ORDER</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ==============================
            // Atualizar quantidade, remover, add-cart
            // ==============================
            function updateCartCount() {
                const countEl = document.getElementById('cart-count');
                if (!countEl) return;
                const count = document.querySelectorAll('.cart-card').length;
                countEl.textContent = count;
            }

            function disableAddCartButton(productId) {
                const btn = document.querySelector(`.btn-add-cart[data-id="${productId}"]`);
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Added.';
                    btn.classList.remove('btn-warning');
                    btn.classList.add('btn-secondary');
                }
            }

            const cartItems = <?php echo json_encode(array_keys($_SESSION['cart'] ?? [])); ?>;
            cartItems.forEach(id => disableAddCartButton(id));

            // Atualizar quantidade
            document.addEventListener('change', function(e) {
                if (!e.target.classList.contains('input-quantity')) return;

                const input = e.target;
                const productId = input.dataset.id;

                let quantity = parseInt(input.value);
                if (isNaN(quantity) || quantity < 1) quantity = 1; // garante mínimo 1

                fetch('../backend/update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `product_id=${productId}&quantity=${quantity}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            input.value = data.quantity; // garante atualização visual
                            const card = input.closest('.cart-card');
                            card.querySelector('.item-subtotal').textContent = data.item_subtotal;
                            document.querySelectorAll('.cart-total').forEach(el => el.textContent = data.total);
                        }
                    })
                    .catch(err => console.error(err));
            });

            // Remover produto
            document.addEventListener('click', e => {
                const removeBtn = e.target.closest('.btn-remove');
                if (!removeBtn) return;

                e.preventDefault();
                fetch(removeBtn.href)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const card = removeBtn.closest('.cart-card');
                            if (card) card.remove();

                            let total = 0;
                            document.querySelectorAll('.item-subtotal').forEach(el => total += parseFloat(el.textContent));
                            document.querySelectorAll('.cart-total').forEach(el => el.textContent = total.toFixed(2));
                            updateCartCount();

                            const cartBox = document.querySelector('.cart-box');
                            if (!document.querySelectorAll('.cart-card').length) {
                                cartBox.innerHTML = `<div class="empty-cart d-flex flex-column justify-content-center align-items-center">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <p>Your Cart is empty.</p>
                    </div>`;
                            }
                        }
                    });
            });

            // ==============================
            // Esconder alerts automaticamente
            // ==============================
            document.querySelectorAll(".alert").forEach(alert => {
                setTimeout(() => {
                    alert.classList.add("hide");
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

        });
    </script>

</body>

</html>