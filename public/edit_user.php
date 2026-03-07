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

// Busca dados do usuário
$stmt = $conn->prepare("SELECT * FROM tbl_users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: ../backend/admin.php');
    exit;
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    // Mantém a imagem antiga por padrão
    $profile_pic_path = $user['profile_pic'];

    // Se enviou nova imagem
    if (!empty($_FILES['profile_pic']['name'])) {
        $file = $_FILES['profile_pic'];
        $file_name = time() . '_' . basename($file['name']); // evita conflito
        $target_file = "uploads/" . $file_name;

        // Verifica extensão e tamanho
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_types) && $file['size'] <= 5 * 1024 * 1024) { // 5MB max
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $profile_pic_path = $target_file;
            }
        }
    }

    // Atualiza o banco de dados
    $stmt = $conn->prepare("UPDATE tbl_users SET username=?, email=?, user_type=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("ssssi", $username, $email, $user_type, $profile_pic_path, $id);
    $stmt->execute();

    header('Location: ../backend/admin.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>

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
            padding: 20px;
            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            width: 100%;
            max-width: 450px;
            margin: 20px auto;
            background-color: #1B2428;
            padding: 20px;
            border-radius: 16px;
            border: 4px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);

            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            margin-top: 10px;
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

        form {
            margin-top: 2rem;
            background: #181A18;
            border-radius: 16px;
            padding: 30px 20px;
            border: 4px solid #151D21;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
        }

        label {
            width: 100%;
            text-align: left;
            font-size: 12px;
            color: #ccc;
        }

        input,
        select {
            width: 100%;
            margin-bottom: 14px;
            background: #ccc;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
            color: #333;
            font-weight: 500;
            padding: 8px 12px 8px 20px;
        }

        .form-control {
            background: #ccc;
            color: #333;
            font-size: 14px;
        }

        select {
            cursor: pointer;
        }

        button.btn-edit {
            width: 100%;
            height: 40px;
            margin-top: 20px;
            background-color: darkgoldenrod;
            border-radius: 8px;
            border: 3px solid darkgoldenrod;
            cursor: pointer;
            font-size: 14px;
            color: #1B2428;
            font-weight: 600;
        }

        button.btn-edit:hover {
            background-color: transparent;
            border: 3px solid darkgoldenrod;
            color: darkgoldenrod;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
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

    <div class="form-container">
        <h1>EDIT USER</h1>
        <form method="POST" enctype="multipart/form-data">

            <!-- Username -->
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control"
                value="<?= htmlspecialchars($user['username']) ?>"
                required autocomplete="username">

            <!-- Email -->
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control"
                value="<?= htmlspecialchars($user['email']) ?>"
                required autocomplete="email">

            <!-- Role -->
            <label for="user_type">Role</label>
            <select id="user_type" name="user_type" class="form-control" required>
                <option value="user" <?= $user['user_type'] == 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['user_type'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>

            <!-- Profile Image -->
            <label for="profile_pic">Profile Image</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">

            <!-- Submit -->
            <button type="submit" class="btn btn-edit">
                Save Changes
            </button>

            <!-- Back link -->
            <p class="back-link">
                <a href="admin.php">Back</a>
            </p>

        </form>
    </div>

</body>

</html>