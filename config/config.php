<?php
$host = 'localhost';
$db = 'salditerra_db';
$user = 'root';
$pass = 'webdesign2025';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
