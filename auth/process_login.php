<?php
include '../config/config.php';
session_start();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['message'] = 'Please complete all fields.';
        echo json_encode($response);
        exit;
    }

    // Preparar query segura
    $stmt = $conn->prepare('SELECT id, username, password_hash, user_type, email, profile_pic FROM tbl_users WHERE username = ? LIMIT 1');

    if (!$stmt) {
        $response['message'] = 'Database error occurred: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $response['message'] = 'Invalid username or password.';
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    $stmt->bind_result($id, $user, $password_hash, $user_type, $email, $profile_pic);

    $stmt->fetch();

    // Verificação segura do hash
    if (empty($password_hash)) {
        $response['message'] = 'No password found for this user. Please reset your password.';
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    if (password_verify($password, $password_hash)) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $user;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['profile_image'] = $profile_pic ?? 'uploads/default.png';
        $_SESSION['email'] = $email;
        $_SESSION['user_role'] = $user_type;

        $response['success'] = true;
        $response['message'] = 'Login completed successfully.';
        
        $response['redirect'] = 'profile.php'; // URL para redirecionamento
    } else {
        $response['message'] = 'Invalid username or password.';
    }

    $stmt->close();
}

// Limpa qualquer saída anterior e retorna JSON limpo
echo json_encode($response);
