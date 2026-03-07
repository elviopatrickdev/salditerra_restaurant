<?php
session_start();
require "../config/config.php"; // conexão MySQL

header('Content-Type: application/json'); // retorno em JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = $_POST['name'];
    $phone   = $_POST['phone'];
    $guest   = $_POST['guest'];
    $date    = $_POST['date'];
    $hour    = $_POST['hour'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO tbl_reservation (name, phone, guest, date, hour, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $name, $phone, $guest, $date, $hour, $message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Reservation successfully submitted!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error submitting reservation: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
