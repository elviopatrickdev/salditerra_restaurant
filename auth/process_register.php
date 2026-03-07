<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include '../config/config.php';
session_start(); // Inicia sessão

$response = ['success' => false];
$profilePicPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ====== PEGANDO DADOS DO FORMULÁRIO ======
    $username = trim($_POST['register-username'] ?? '');
    $email = trim($_POST['register-email'] ?? '');
    $password = $_POST['register-password'] ?? '';
    $confirmPassword = $_POST['register-confirm-password'] ?? '';
    $userType = ($_POST['register-user_type'] ?? 'user') === 'admin' ? 'admin' : 'user';

    // ====== VALIDAÇÕES ======
    if (strlen($username) < 3) {
        $response['message'] = 'The username must be at least 3 characters long.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email.';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 6) {
        $response['message'] = 'The password must be at least 6 characters long.';
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirmPassword) {
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response);
        exit;
    }

    // ====== VERIFICA USUÁRIO/EMAIL EXISTENTE ======
    $stmt = $conn->prepare('SELECT COUNT(*) FROM tbl_users WHERE username=? OR email=?');
    if (!$stmt) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $response['message'] = 'Username or email is already in use.';
        echo json_encode($response);
        exit;
    }

    // ====== UPLOAD DE IMAGEM ======
    $profilePicPath = null;

    if (isset($_FILES['register-profile_pic']) && $_FILES['register-profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['register-profile_pic'];

        // Verifica erros de upload básicos
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Image upload error.';
            echo json_encode($response);
            exit;
        }

        // Verifica tipo permitido pelo mime e extensão
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Mime type via finfo (somente se a extensão estiver ativa)
        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
        }

        if (!in_array($ext, $allowedExt) || ($mime && !in_array($mime, $allowedMimeTypes))) {
            $response['message'] = 'The image must be JPG, PNG, or WEBP format.';
            echo json_encode($response);
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'The photo must be no larger than 2MB.';
            echo json_encode($response);
            exit;
        }

        // Cria pasta uploads se não existir
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $response['message'] = 'Unable to create the uploads folder.';
                echo json_encode($response);
                exit;
            }
        }

        // Nome único para a imagem
        $filename = uniqid('img_') . '.' . $ext;
        $path = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            $response['message'] = 'Error moving the image to the uploads folder.';
            echo json_encode($response);
            exit;
        }

        // Caminho relativo que será salvo no DB (para usar em src de <img>)
        $profilePicPath = 'uploads/' . $filename;
    }

    // ====== HASH DA SENHA ======
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ====== INSERT NO DB ======
    $stmt = $conn->prepare('INSERT INTO tbl_users (username,email,password_hash,profile_pic,user_type) VALUES (?,?,?,?,?)');
    if (!$stmt) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param('sssss', $username, $email, $hashedPassword, $profilePicPath, $userType);

    if ($stmt->execute()) {
        // Opcional: salva usuário logado na sessão
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $username;

        $response['success'] = true;
        $response['message'] = 'Successfully registered!';
    } else {
        $response['message'] = 'Error registering user: ' . $stmt->error;
    }

    $stmt->close();
}

// Retorna JSON limpo
header('Content-Type: application/json');
echo json_encode($response);
