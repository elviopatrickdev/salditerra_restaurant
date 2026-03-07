<?php
// Inicia a sessão para acesso às variáveis globais $_SESSION
session_start();

// Verifica se o utilizador está autenticado
// Caso não esteja, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login-register.php");
    exit; // Impede execução adicional após o redirecionamento
}

// Define valores padrão caso alguma variável de sessão não exista
$profileImage = $_SESSION['profile_image'] ?? 'uploads/default.png';
$username     = $_SESSION['username'] ?? 'Default';
$email        = $_SESSION['email'] ?? 'Default';
$userRole     = $_SESSION['user_role'] ?? 'Default';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>

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

    <!-- jQuery (necessário para AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <style>
        /* Reset global: margens, paddings e box-sizing */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body: layout centralizado e background */
        body {
            background-image: url('assets/pattern-food.png');
            background-repeat: repeat;
            background-size: 110%;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            height: 100vh;
        }

        /* Botão de voltar circular */
        .back-index-btn a {
            position: absolute;
            top: -45px;
            right: -50px;
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
            top: 59px;
        }

        .back-index-btn a:hover {
            background-color: #333;
            color: #ccc;
        }

        /* Container principal (card) */
        .container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: start;
            justify-content: end;

            padding: 30px;
            width: 100%;
            max-width: 400px;
            height: 100%;
            max-height: 660px;

            background-color: #181A18;
            border: 8px solid #151D21;
            border-radius: 30px;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
            overflow: hidden;
        }

        .container h1 {
            font-family: "Noto Serif", serif;
            font-weight: 600;
            color: darkgoldenrod;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
            font-size: 28px;
        }

        /* Wrapper da foto de perfil rotacionada */
        .profile-pic-wrapper {
            position: absolute;
            top: -55%;
            right: -94%;
            width: 700px;
            height: 700px;
            border-radius: 50px;
            border: 4px solid darkgoldenrod;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 8px 12px rgba(0, 0, 0, 0.7);
            transform: rotate(45deg);
            overflow: hidden;
        }

        .profile-pic {
            position: absolute;
            bottom: -80%;
            left: 0%;
            width: 150%;
            height: 150%;
            object-fit: fill;
            transform: rotate(-45deg);
        }

        /* Textos e links */
        p {
            margin: 5px 0;
            font-size: 16px;
            color: #ccc;
        }

        a {
            color: darkgoldenrod;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Botão Painel Admin */
        .btn-admin-panel {
            display: block;
            padding: 10px 20px;
            background-color: darkgoldenrod;
            border-radius: 8px;
            border: 3px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #1B2428;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.7);
        }

        .btn-admin-panel:hover {
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.05);
            color: #ccc;
        }

        /* Responsividade */
        @media (min-width: 768px) {
            body {
                background-size: 50%;
                background-position: -350px 0;
            }
        }

        @media (min-width: 1440px) {
            body {
                background-size: 30%;
                background-position: -380px 0;
            }
        }
    </style>
</head>

<body>

    <!-- Card Principal -->
    <div class="container">

        <!-- Botão de voltar para a página inicial -->
        <div class="back-index-btn">
            <a href="index.php" class="icon">
                <i class="fa-solid fa-house-chimney"></i>
            </a>
        </div>

        <!-- Wrapper da imagem de perfil rotacionada -->
        <div class="profile-pic-wrapper">
            <!-- Escapa os dados para evitar XSS -->
            <img src="<?php echo htmlspecialchars($profileImage); ?>" class="profile-pic" alt="perfil">
        </div>

        <!-- Título de boas-vindas -->
        <h1>WELCOME!</h1>

        <!-- Informações do utilizador -->
        <p>
            <strong style="font-size: 16px;">USERNAME:</strong>
            <?php echo htmlspecialchars($username); ?>
        </p>

        <p>
            <strong style="font-size: 16px;">EMAIL:</strong>
            <?php echo htmlspecialchars($email); ?>
        </p>

        <p>
            <strong style="font-size: 16px;">USER TYPE:</strong>
            <?php echo htmlspecialchars($userRole); ?>
        </p>

        <!-- Rodapé do container: Logout e botão Admin se aplicável -->
        <div class="d-flex align-items-center justify-content-between w-100">

            <!-- Link para logout -->
            <p>
                <a href="../auth/logout.php">Logout</a>
            </p>

            <!-- Botão Admin visível apenas para utilizadores com role 'admin' -->
            <?php if ($userRole === 'admin'): ?>
                <button class="btn-admin-panel" onclick="window.location.href='../backend/admin.php'">
                    <i class="fa-solid fa-gear"></i> Admin Panel
                </button>
            <?php endif; ?>

        </div>

    </div>
</body>

</html>